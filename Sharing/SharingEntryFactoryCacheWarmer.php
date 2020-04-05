<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\SecurityExtra\Sharing;

use Klipper\Component\Security\CacheWarmer\AbstractCacheWarmer;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class SharingEntryFactoryCacheWarmer extends AbstractCacheWarmer
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices(): array
    {
        return [
            'klipper_security_extra.sharing_entry_factory' => SharingEntryFactoryInterface::class,
        ];
    }
}
