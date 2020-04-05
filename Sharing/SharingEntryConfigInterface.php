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

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface SharingEntryConfigInterface
{
    /**
     * Get the type. Typically, this is the PHP class name.
     */
    public function getType(): string;

    /**
     * Get the field name to be using to find the instances.
     */
    public function getField(): string;

    /**
     * Get the name of repository method.
     */
    public function getRepositoryMethod(): ?string;

    /**
     * Merge the new sharing entry config.
     *
     * @param SharingEntryConfigInterface $newConfig The new sharing entry config
     */
    public function merge(SharingEntryConfigInterface $newConfig): void;
}
