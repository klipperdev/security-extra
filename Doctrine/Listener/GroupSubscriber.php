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

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Klipper\Component\DoctrineExtensions\Util\SqlFilterUtil;
use Klipper\Component\DoctrineExtensionsExtra\Util\ListenerUtil;
use Klipper\Component\Model\Traits\LabelableInterface;
use Klipper\Component\Security\Model\GroupInterface;
use Klipper\Component\Security\Model\Traits\OrganizationalInterface;
use Klipper\Component\Security\Permission\PermissionManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class GroupSubscriber implements EventSubscriber
{
    public ?ContainerInterface $container = null;

    protected TranslatorInterface $translator;

    protected ?PermissionManagerInterface $permissionManager = null;

    /**
     * @param TranslatorInterface $translator The translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
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

        if (!$entity instanceof GroupInterface) {
            return;
        }

        $entity->setName(strtoupper($entity->getName()));

        if ($entity instanceof LabelableInterface && null === $entity->getLabel()) {
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

        $pmEnabled = $this->getPermissionManager()->isEnabled();
        $this->getPermissionManager()->setEnabled(false);
        $filters = SqlFilterUtil::findFilters($em, [], true);
        SqlFilterUtil::disableFilters($em, $filters);

        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            if ($entity instanceof GroupInterface) {
                $this->validateGroupDeletion($entity, $errors);
            }
        }

        $this->getPermissionManager()->setEnabled($pmEnabled);
        SqlFilterUtil::enableFilters($em, $filters);

        if (\count($errors) > 0) {
            ListenerUtil::thrownErrors($errors);
        }
    }

    /**
     * Validate the deletion of groups.
     *
     * @param GroupInterface $group  The group entity
     * @param array          $errors The errors by reference
     */
    protected function validateGroupDeletion(GroupInterface $group, array &$errors): void
    {
        if (method_exists($group, 'getUsers') && $group->getUsers() instanceof Collection
                && ($count = $group->getUsers()->count()) > 0) {
            $msg = $this->translator->trans('group.no_delete_used_in_user', ['%name%' => $group->getName(), '%count%' => $count], 'validators');
            $errors[] = ListenerUtil::createViolation($msg, $group);
        }

        if ($group instanceof OrganizationalInterface && method_exists($group, 'getOrganizationUsers')
                && $group->getOrganizationUsers() instanceof Collection
                && null !== $group->getOrganization() && ($count = $group->getOrganizationUsers()->count()) > 0) {
            $msg = $this->translator->trans('group.no_delete_used_in_org_user', ['%name%' => $group->getName(), '%count%' => $count], 'validators');
            $errors[] = ListenerUtil::createViolation($msg, $group);
        }
    }

    /**
     * Get the permission manager.
     */
    protected function getPermissionManager(): PermissionManagerInterface
    {
        if (null !== $this->container) {
            $this->permissionManager = $this->container->get('klipper_security.permission_manager');
            $this->container = null;
        }

        return $this->permissionManager;
    }
}
