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
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Klipper\Component\DoctrineExtensions\Util\SqlFilterUtil;
use Klipper\Component\DoctrineExtensionsExtra\Util\ListenerUtil;
use Klipper\Component\Security\Model\GroupInterface;
use Klipper\Component\Security\Model\OrganizationInterface;
use Klipper\Component\Security\Model\OrganizationUserInterface;
use Klipper\Component\Security\Model\RoleInterface;
use Klipper\Component\Security\Model\Traits\OrganizationalInterface;
use Klipper\Component\Security\Model\UserInterface;
use Klipper\Component\Security\Permission\PermissionManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class RoleSubscriber implements EventSubscriber
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
            Events::prePersist,
            Events::preUpdate,
            Events::onFlush,
        ];
    }

    public function prePersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getEntity();

        if (!$entity instanceof RoleInterface) {
            return;
        }

        $entity->setName(strtoupper($entity->getName()));

        if (0 !== strpos($entity->getName(), 'ROLE_')) {
            $entity->setName('ROLE_'.$entity->getName());
        }

        if (method_exists($entity, 'getLabel')
            && method_exists($entity, 'setLabel')
            && null === $entity->getLabel()
        ) {
            $entity->setLabel($entity->getName());
        }
    }

    public function preUpdate(LifecycleEventArgs $args): void
    {
        $this->prePersist($args);
    }

    public function onFlush(OnFlushEventArgs $args): void
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();
        $errors = [];

        $pmEnabled = $this->permissionManager->isEnabled();
        $this->permissionManager->setEnabled(false);
        $filters = SqlFilterUtil::findFilters($em, [], true);
        SqlFilterUtil::disableFilters($em, $filters);

        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            if ($entity instanceof RoleInterface && $entity instanceof OrganizationalInterface) {
                if (null === $entity->getOrganization()
                        || !$uow->isScheduledForDelete($entity->getOrganization())) {
                    $this->validateRoleDeletion($em, $entity, $errors);
                }
            }
        }

        $this->permissionManager->setEnabled($pmEnabled);
        SqlFilterUtil::enableFilters($em, $filters);

        if (\count($errors) > 0) {
            ListenerUtil::thrownErrors($errors);
        }
    }

    /**
     * Validate the deletion of roles.
     *
     * @param EntityManagerInterface $em     The entity manager
     * @param RoleInterface          $role   The role entity
     * @param array                  $errors The errors by reference
     */
    protected function validateRoleDeletion(EntityManagerInterface $em, RoleInterface $role, array &$errors): void
    {
        if (($count = $this->getCountUsersForRoleDeletion($em, $role)) > 0) {
            $msg = $this->translator->trans('role.no_delete_used_in_user', ['%name%' => $role->getName(), '%count%' => $count], 'validators');
            $errors[] = ListenerUtil::createViolation($msg, $role);
        }

        if (($count = $this->getCountGroupsForRoleDeletion($em, $role)) > 0) {
            $msg = $this->translator->trans('role.no_delete_used_in_group', ['%name%' => $role->getName(), '%count%' => $count], 'validators');
            $errors[] = ListenerUtil::createViolation($msg, $role);
        }

        if (($count = $this->getCountOrgUsersForRoleDeletion($em, $role)) > 0) {
            $msg = $this->translator->trans('role.no_delete_used_in_org_user', ['%name%' => $role->getName(), '%count%' => $count], 'validators');
            $errors[] = ListenerUtil::createViolation($msg, $role);
        }
    }

    /**
     * Get the count of users using the role that must be deleted.
     *
     * @param EntityManagerInterface $em   The entity manager
     * @param RoleInterface          $role The role entity
     *
     * @throws
     */
    protected function getCountUsersForRoleDeletion(EntityManagerInterface $em, RoleInterface $role): int
    {
        $pf = $em->getConnection()->getDatabasePlatform();
        $qb = $em->createQueryBuilder()
            ->select('COUNT(u)')
            ->from(UserInterface::class, 'u')
            ->where('u.roles LIKE :role')
            ->setParameter('role', '%'.$pf->quoteIdentifier($role->getName()).'%')
        ;

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Get the count of groups using the role that must be deleted.
     *
     * @param EntityManagerInterface $em   The entity manager
     * @param RoleInterface          $role The role entity
     *
     * @throws
     */
    protected function getCountGroupsForRoleDeletion(EntityManagerInterface $em, RoleInterface $role): int
    {
        $qb = $em->createQueryBuilder()
            ->select('COUNT(g)')
            ->from(GroupInterface::class, 'g')
        ;

        $this->addWhereCondition($qb, 'g', $role);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Get the organization users using the role that must be deleted.
     *
     * @param EntityManagerInterface $em   The entity manager
     * @param RoleInterface          $role The role entity
     *
     * @throws
     */
    protected function getCountOrgUsersForRoleDeletion(EntityManagerInterface $em, RoleInterface $role): int
    {
        $qb = $em->createQueryBuilder()
            ->select('COUNT(uo)')
            ->from(OrganizationUserInterface::class, 'uo')
            ->leftJoin(OrganizationInterface::class, 'o', Join::WITH, 'o.id = uo.organization')
            ->leftJoin(UserInterface::class, 'u', Join::WITH, 'u.id = uo.user')
        ;

        $this->addWhereCondition($qb, 'uo', $role);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Add where condition.
     *
     * @throws
     */
    protected function addWhereCondition(QueryBuilder $qb, string $alias, RoleInterface $role): void
    {
        $pf = $qb->getEntityManager()->getConnection()->getDatabasePlatform();

        if ($role instanceof OrganizationalInterface) {
            if (null === $role->getOrganization()) {
                $qb->orWhere($alias.'.roles LIKE :role AND '.$alias.'.organization is null');
            } else {
                $qb->orWhere($alias.'.roles LIKE :role AND '.$alias.'.organization = :org')
                    ->setParameter('org', $role->getOrganization()->getId())
                ;
            }
        }

        $qb->setParameter('role', '%'.$pf->quoteIdentifier($role->getName()).'%');
    }
}
