<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\SecurityExtra\Listener;

use Klipper\Component\Resource\Event\PostCreatesEvent;
use Klipper\Component\Resource\Event\PostDeletesEvent;
use Klipper\Component\Resource\Event\PreCreatesEvent;
use Klipper\Component\Resource\Event\PreDeletesEvent;
use Klipper\Component\Security\Model\OrganizationInterface;
use Klipper\Component\Security\Permission\PermissionManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class DomainOrganizationSubscriber implements EventSubscriberInterface
{
    protected PermissionManagerInterface $permissionManager;

    protected bool $pmEnabled = true;

    /**
     * @param PermissionManagerInterface $permissionManager The permission manager
     */
    public function __construct(PermissionManagerInterface $permissionManager)
    {
        $this->permissionManager = $permissionManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PreCreatesEvent::class => [
                ['onPreCreate', 0],
            ],
            PostCreatesEvent::class => [
                ['onPostCreate', 0],
            ],
            PreDeletesEvent::class => [
                ['onPreDelete', 0],
            ],
            PostDeletesEvent::class => [
                ['onPostDelete', 0],
            ],
        ];
    }

    /**
     * Disable the permission manager before creates.
     */
    public function onPreCreate(PreCreatesEvent $event): void
    {
        if ($event->is(OrganizationInterface::class)) {
            $this->pmEnabled = $this->permissionManager->isEnabled();
            $this->permissionManager->setEnabled(false);
        }
    }

    /**
     * Disable the permission manager before creates.
     */
    public function onPostCreate(PostCreatesEvent $event): void
    {
        if ($event->is(OrganizationInterface::class)) {
            $this->permissionManager->setEnabled($this->pmEnabled);
        }
    }

    /**
     * Disable the permission manager before deletes.
     */
    public function onPreDelete(PreDeletesEvent $event): void
    {
        if ($event->is(OrganizationInterface::class)) {
            $this->pmEnabled = $this->permissionManager->isEnabled();
            $this->permissionManager->setEnabled(false);
        }
    }

    /**
     * Disable the permission manager before deletes.
     */
    public function onPostDelete(PostDeletesEvent $event): void
    {
        if ($event->is(OrganizationInterface::class)) {
            $this->permissionManager->setEnabled($this->pmEnabled);
        }
    }
}
