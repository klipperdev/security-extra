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

use Klipper\Component\Security\Event\CheckPermissionEvent;
use Klipper\Component\Security\Identity\SubjectIdentityInterface;
use Klipper\Component\Security\Model\Traits\OrganizationalOptionalInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class PermissionSubscriber implements EventSubscriberInterface
{
    protected RequestStack $requestStack;

    /**
     * @param RequestStack $requestStack The request stack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CheckPermissionEvent::class => [
                ['onCheckPermission', 0],
            ],
        ];
    }

    /**
     * Disable the doctrine filter of organization.
     *
     * @param CheckPermissionEvent $event The event
     */
    public function onCheckPermission(CheckPermissionEvent $event): void
    {
        if (!$this->isAdminSection()
                && $this->isSystemSubject($event->getSubject())
                && !\in_array($event->getOperation(), ['view', 'read'], true)) {
            $event->setGranted(false);
        }
    }

    /**
     * Check if in admin section.
     */
    private function isAdminSection(): bool
    {
        $request = $this->requestStack->getCurrentRequest();

        return null === $request || $request->attributes->get('_admin_section', false);
    }

    /**
     * Check fi the subject is a system subject.
     *
     * @param null|SubjectIdentityInterface $subject The subject
     */
    private function isSystemSubject(?SubjectIdentityInterface $subject): bool
    {
        if (null !== $subject) {
            $object = $subject->getObject();

            return $object instanceof OrganizationalOptionalInterface && null === $object->getOrganization();
        }

        return false;
    }
}
