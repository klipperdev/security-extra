<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\SecurityExtra\Firewall;

use Klipper\Component\SecurityExtra\Helper\OrganizationalContextHelper;
use Symfony\Component\HttpKernel\Event\RequestEvent;

/**
 * Inject the organization defined in request path into the organizational context.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class OrganizationalContextFirewallListener
{
    protected OrganizationalContextHelper $helper;

    protected array $config;

    /**
     * @param OrganizationalContextHelper $helper The helper of organizational context
     * @param array                       $config The config defined in firewall
     */
    public function __construct(OrganizationalContextHelper $helper, array $config)
    {
        $this->helper = $helper;
        $this->config = $config;
    }

    public function __invoke(RequestEvent $event): void
    {
        $this->helper->setRouteParameterName($this->config['route_parameter_name']);
        $this->helper->injectContext($event->getRequest());
    }
}
