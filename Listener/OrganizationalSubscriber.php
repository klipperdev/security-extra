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

use Klipper\Component\SecurityExtra\Helper\OrganizationalContextHelper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\RememberMeToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class OrganizationalSubscriber implements EventSubscriberInterface
{
    protected OrganizationalContextHelper $helper;

    /**
     * @param OrganizationalContextHelper $helper The helper of organizational context
     */
    public function __construct(OrganizationalContextHelper $helper)
    {
        $this->helper = $helper;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SecurityEvents::INTERACTIVE_LOGIN => [
                ['onInteractiveLogin', 255],
            ],
        ];
    }

    /**
     * Inject the organizational context on the interactive login (remember me).
     *
     * @param InteractiveLoginEvent $event The event
     */
    public function onInteractiveLogin(InteractiveLoginEvent $event): void
    {
        if ($event->getAuthenticationToken() instanceof RememberMeToken) {
            $this->helper->injectContext($event->getRequest());
        }
    }
}
