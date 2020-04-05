<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\SecurityExtra\Http\Firewall;

use Klipper\Component\SecurityExtra\Form\Extension\PermissionCheckerTypeExtension;
use Symfony\Component\HttpKernel\Event\RequestEvent;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class FormPermissionCheckerListener
{
    /**
     * @var PermissionCheckerTypeExtension
     */
    protected $typeExtension;

    /**
     * @var array
     */
    protected $config;

    /**
     * Constructor.
     *
     * @param PermissionCheckerTypeExtension $typeExtension The form type extension
     * @param array                          $config        The config defined in firewall
     */
    public function __construct(PermissionCheckerTypeExtension $typeExtension, array $config)
    {
        $this->typeExtension = $typeExtension;
        $this->config = $config;
    }

    public function __invoke(RequestEvent $event): void
    {
        $this->typeExtension->setEnabled($this->config['enabled']);
    }
}
