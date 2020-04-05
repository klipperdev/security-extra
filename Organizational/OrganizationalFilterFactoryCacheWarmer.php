<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\SecurityExtra\Organizational;

use Klipper\Component\Security\CacheWarmer\AbstractCacheWarmer;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class OrganizationalFilterFactoryCacheWarmer extends AbstractCacheWarmer
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices(): array
    {
        return [
            'klipper_security_extra.organizational_filter_factory' => OrganizationalFilterFactoryInterface::class,
        ];
    }
}
