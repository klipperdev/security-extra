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

use Klipper\Component\Security\Organizational\OrganizationalContextInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class OrganizationalOptionalFilterSubscriber implements EventSubscriberInterface
{
    protected OrganizationalContextInterface $context;

    protected string $parameter;

    /**
     * @param OrganizationalContextInterface $context   The organizational context
     * @param string                         $parameter The parameter name in request
     */
    public function __construct(OrganizationalContextInterface $context, $parameter = '_organizational_optional_filter_type')
    {
        $this->context = $context;
        $this->parameter = $parameter;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [
                ['onKernelRequest', 0],
            ],
        ];
    }

    /**
     * Disable the doctrine filter of organization.
     *
     * @param RequestEvent $event The event
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        $attr = $event->getRequest()->attributes;
        $type = $attr->get($this->parameter);

        if (null !== $type) {
            $this->context->setOptionalFilterType($type);
        }
    }
}
