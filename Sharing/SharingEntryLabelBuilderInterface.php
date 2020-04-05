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
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface SharingEntryLabelBuilderInterface
{
    /**
     * Check if the identity is supported by the label builder.
     *
     * @param object           $identity The identity instance
     * @param SharingInterface $sharing  The sharing
     */
    public function supports(object $identity, SharingInterface $sharing): bool;

    /**
     * Build the sharing entry label.
     *
     * @param object           $identity The identity instance
     * @param SharingInterface $sharing  The sharing
     */
    public function buildLabel(object $identity, SharingInterface $sharing): string;
}
