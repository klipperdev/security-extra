<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\SecurityExtra\Doctrine\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Query\Expr\Join;
use Klipper\Component\DoctrineExtensions\Util\SqlFilterUtil;
use Klipper\Component\DoctrineExtensionsExtra\Util\ListenerUtil;
use Klipper\Component\Model\Traits\EnableInterface;
use Klipper\Component\Security\Model\OrganizationInterface;
use Klipper\Component\Security\Model\OrganizationUserInterface;
use Klipper\Component\Security\Model\Traits\RoleableInterface;
use Klipper\Component\Security\Permission\PermissionManagerInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class OrganizationUserSubscriber implements EventSubscriber
{
    protected TranslatorInterface $translator;

    protected PermissionManagerInterface $permissionManager;

    public function __construct(
        TranslatorInterface $translator,
        PermissionManagerInterface $permissionManager
    ) {
        $this->translator = $translator;
        $this->permissionManager = $permissionManager;
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::onFlush,
        ];
    }

    /**
     * On flush action.
     *
     * @param OnFlushEventArgs $args The event
     */
    public function onFlush(OnFlushEventArgs $args): void
    {
        $errors = [];

        $this->addRequiredAdminRole($args);
        $this->validateUpdates($args, $errors);
        $this->validateDeletions($args, $errors);

        if (\count($errors) > 0) {
            ListenerUtil::thrownErrors($errors);
        }
    }

    /**
     * Add admin role in organization user if any user is an admin.
     *
     * @param OnFlushEventArgs $args The on flush event
     */
    protected function addRequiredAdminRole(OnFlushEventArgs $args): void
    {
        /** @var OrganizationUserInterface[] $uos */
        $uos = [];
        $uoIds = [];
        $em = $args->getObjectManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            if ($entity instanceof OrganizationUserInterface && null !== $entity->getOrganization()) {
                $uoIds[$entity->getId()] = $entity->getOrganization()->getId();
                $uos[$entity->getId()] = $entity;
            }
        }

        if (\count($uoIds) > 0) {
            $qb = $em->createQueryBuilder();
            $qb
                ->select('o.id as oid, COUNT(uo) as ucount')
                ->from(OrganizationUserInterface::class, 'uo')
                ->leftJoin(OrganizationInterface::class, 'o', Join::WITH, 'o.id = uo.organization')
                ->where('uo.roles LIKE :role')
                ->andWhere('o.id IS NOT NULL')
                ->setParameter('role', '%"'.'ROLE_ADMIN'.'"%')
                ->groupBy('o.id')
            ;

            $res = $qb->getQuery()->getResult();
            $orgRoleAdmins = [];

            foreach ($res as $val) {
                $orgRoleAdmins[$val['oid']] = (int) $val['ucount'];
            }

            foreach ($uos as $uo) {
                if ($uo instanceof RoleableInterface && null !== $uo->getOrganization()
                        && !isset($orgRoleAdmins[$uo->getOrganization()->getId()])) {
                    $uo->addRole('ROLE_ADMIN');
                }
            }
        }
    }

    /**
     * Not delete organization users if the user is only in org or he is a last admin.
     *
     * @param OnFlushEventArgs               $args   The on flush event
     * @param ConstraintViolationInterface[] $errors The errors by reference
     */
    protected function validateUpdates(OnFlushEventArgs $args, array &$errors): void
    {
        $em = $args->getObjectManager();
        $uow = $em->getUnitOfWork();
        $demoteAdmins = [];
        $disabledAdmins = [];
        $orgIds = [];

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if ($entity instanceof OrganizationUserInterface && null !== $entity->getOrganization()) {
                $change = $uow->getEntityChangeSet($entity);

                if ($entity instanceof RoleableInterface
                        && isset($change['roles'])
                        && \in_array('ROLE_ADMIN', $change['roles'][0], true)
                        && !\in_array('ROLE_ADMIN', $change['roles'][1], true)) {
                    $demoteAdmins[$entity->getId()] = $entity;
                    $orgIds[] = $entity->getOrganization()->getId();
                }

                if ($entity instanceof EnableInterface
                        && isset($change['enabled'])
                        && true === $change['enabled'][0]
                        && false === $change['enabled'][1]) {
                    $disabledAdmins[$entity->getId()] = $entity;
                    $orgIds[] = $entity->getOrganization()->getId();
                }
            }
        }

        if (\count($demoteAdmins) > 0) {
            $this->validateDemoteAdmin($em, $errors, $demoteAdmins, $orgIds);
        }

        if (\count($disabledAdmins) > 0) {
            $this->validateDisabledAdmin($em, $errors, $disabledAdmins, $orgIds);
        }
    }

    /**
     * Validate demote role admin of organization user.
     *
     * @param EntityManagerInterface         $em       The entity manager
     * @param ConstraintViolationInterface[] $errors   The errors by reference
     * @param OrganizationUserInterface[]    $orgUsers The map of organization users
     * @param int[]|string[]                 $orgIds   The organization ids
     */
    protected function validateDemoteAdmin(EntityManagerInterface $em, array &$errors, array $orgUsers, array $orgIds): void
    {
        $orgAdmins = [];
        $qb = $em->createQueryBuilder();
        $qb
            ->select('o.id as oid, COUNT(uo) as ucount')
            ->from(OrganizationUserInterface::class, 'uo')
            ->leftJoin(OrganizationInterface::class, 'o', Join::WITH, 'o.id = uo.organization')
            ->where('uo.id NOT IN(:ids)')
            ->andWhere('uo.enabled = true')
            ->andWhere('uo.roles LIKE :role')
            ->andWhere('o.id IS NOT NULL')
            ->andWhere('o.id IN(:oids)')
            ->setParameter('ids', array_keys($orgUsers))
            ->setParameter('role', '%"'.'ROLE_ADMIN'.'"%')
            ->setParameter('oids', $orgIds)
            ->groupBy('o.id')
        ;

        $res = $qb->getQuery()->getResult();

        foreach ($res as $val) {
            $orgAdmins[$val['oid']] = (int) $val['ucount'];
        }

        foreach ($orgUsers as $orgUser) {
            if (null !== $orgUser->getOrganization()
                    && null !== $orgUser->getUser()
                    && !isset($orgAdmins[$orgUser->getOrganization()->getId()])) {
                $msg = $this->translator->trans('organization_user.not_demote_last_admin', [
                    '{{ username }}' => $orgUser->getUser()->getUserIdentifier(),
                    '{{ role }}' => 'ROLE_ADMIN',
                    '{{ organization }}' => $orgUser->getOrganization()->getName(),
                ], 'validators');
                $errors[] = ListenerUtil::createViolation($msg, $orgUser);
            }
        }
    }

    /**
     * Validate disabled user admin of organization user.
     *
     * @param EntityManagerInterface         $em       The entity manager
     * @param ConstraintViolationInterface[] $errors   The errors by reference
     * @param OrganizationUserInterface[]    $orgUsers The map of organization users
     * @param int[]|string[]                 $orgIds   The organization ids
     */
    protected function validateDisabledAdmin(EntityManagerInterface $em, array &$errors, array $orgUsers, array $orgIds): void
    {
        $orgAdmins = [];
        $qb = $em->createQueryBuilder();
        $qb
            ->select('o.id as oid, COUNT(uo) as ucount')
            ->from(OrganizationUserInterface::class, 'uo')
            ->leftJoin(OrganizationInterface::class, 'o', Join::WITH, 'o.id = uo.organization')
            ->where('uo.id NOT IN(:ids)')
            ->andWhere('uo.enabled = true')
            ->andWhere('uo.roles LIKE :role')
            ->andWhere('o.id IS NOT NULL')
            ->andWhere('o.id IN(:oids)')
            ->setParameter('ids', array_keys($orgUsers))
            ->setParameter('role', '%"'.'ROLE_ADMIN'.'"%')
            ->setParameter('oids', $orgIds)
            ->groupBy('o.id')
        ;

        $res = $qb->getQuery()->getResult();

        foreach ($res as $val) {
            $orgAdmins[$val['oid']] = (int) $val['ucount'];
        }

        foreach ($orgUsers as $orgUser) {
            if (null !== $orgUser->getOrganization()
                    && null !== $orgUser->getUser()
                    && !isset($orgAdmins[$orgUser->getOrganization()->getId()])) {
                $msg = $this->translator->trans('organization_user.not_disable_last_admin', [
                    '{{ username }}' => $orgUser->getUser()->getUserIdentifier(),
                    '{{ organization }}' => $orgUser->getOrganization()->getName(),
                ], 'validators');
                $errors[] = ListenerUtil::createViolation($msg, $orgUser);
            }
        }
    }

    /**
     * Not delete organization users if the user is only in org or he is a last admin.
     *
     * @param OnFlushEventArgs               $args   The on flush event
     * @param ConstraintViolationInterface[] $errors The errors by reference
     */
    protected function validateDeletions(OnFlushEventArgs $args, array &$errors): void
    {
        $em = $args->getObjectManager();
        $uow = $em->getUnitOfWork();

        /** @var OrganizationUserInterface[] $deleted */
        $deleted = [];
        $deletedOrgIds = [];

        $pmEnabled = $this->permissionManager->isEnabled();
        $this->permissionManager->setEnabled(false);
        $filters = SqlFilterUtil::findFilters($em, [], true);
        SqlFilterUtil::disableFilters($em, $filters);

        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            if ($entity instanceof OrganizationUserInterface && (null === $entity->getOrganization()
                    || !$uow->isScheduledForDelete($entity->getOrganization()))) {
                $deleted[$entity->getId()] = $entity;
                $oid = $entity->getOrganization()->getId();
                $deletedOrgIds[$oid] = $oid;
            }
        }

        if (\count($deleted) > 0) {
            $qb = $em->createQueryBuilder();
            $qb
                ->select('o.id as oid, COUNT(uo) as ucount')
                ->from(OrganizationUserInterface::class, 'uo')
                ->leftJoin('uo.organization', 'o')
                ->where('uo.id NOT IN(:ids)')
                ->andWhere('uo.roles LIKE :role')
                ->andWhere('uo.enabled = true')
                ->andWhere('o.id IS NOT NULL')
                ->andWhere('o.id IN(:oids)')
                ->setParameter('ids', array_keys($deleted))
                ->setParameter('oids', array_keys($deletedOrgIds))
                ->setParameter('role', '%"'.'ROLE_ADMIN'.'"%')
                ->groupBy('o.id')
            ;

            $res = $qb->getQuery()->getResult();
            $orgAdmins = [];

            foreach ($res as $val) {
                $orgAdmins[$val['oid']] = (int) $val['ucount'];
            }

            foreach ($deleted as $uoDeleted) {
                if (null !== $uoDeleted->getOrganization()
                        && null !== $uoDeleted->getUser()
                        && !isset($orgAdmins[$uoDeleted->getOrganization()->getId()])) {
                    $msg = $this->translator->trans('organization_user.not_leave_organization', [
                        '{{ organization }}' => $uoDeleted->getOrganization()->getName(),
                    ], 'validators');
                    $errors[] = ListenerUtil::createViolation($msg, $uoDeleted);
                }
            }
        }

        $this->permissionManager->setEnabled($pmEnabled);
        SqlFilterUtil::enableFilters($em, $filters);
    }
}
