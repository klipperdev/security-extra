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
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Klipper\Component\Security\Model\Traits\OrganizationalInterface;
use Klipper\Component\Security\Model\Traits\RoleableInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class RoleableSubscriber implements EventSubscriber
{
    public function getSubscribedEvents(): array
    {
        return [
            Events::prePersist,
            Events::preUpdate,
        ];
    }

    /**
     * On pre persist action.
     */
    public function prePersist(LifecycleEventArgs $args): void
    {
        $this->formatOrganizationRoleNames($args->getEntity());
    }

    /**
     * On pre update action.
     */
    public function preUpdate(LifecycleEventArgs $args): void
    {
        $this->formatOrganizationRoleNames($args->getEntity());
    }

    /**
     * Format the organization role names.
     *
     * @param object $entity The roleable entity
     */
    protected function formatOrganizationRoleNames(object $entity): void
    {
        if ($entity instanceof RoleableInterface
                && $entity instanceof OrganizationalInterface
                && null !== $entity->getOrganization()) {
            foreach ($entity->getRoles() as $role) {
                if (false !== ($pos = strrpos($role, '__'))) {
                    $entity->removeRole($role);
                    $entity->addRole(substr($role, 0, $pos));
                }
            }
        }
    }
}
