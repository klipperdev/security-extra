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

use Klipper\Component\Security\Model\SharingInterface;

/**
 * Sharing entry manager Interface.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface SharingEntryManagerInterface
{
    /**
     * Set the config.
     *
     * @param SharingEntryConfigInterface $config The config
     *
     * @return static
     */
    public function setConfig(SharingEntryConfigInterface $config);

    /**
     * Get the sharing entries of the sharing list.
     *
     * @param SharingInterface[] $sharings The sharing instances
     *
     * @return SharingEntryInterface[]
     */
    public function getSharingEntries(array $sharings): array;
}
