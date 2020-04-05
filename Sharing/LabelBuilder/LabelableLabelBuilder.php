<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\SecurityExtra\Sharing\LabelBuilder;

use Klipper\Component\Model\Traits\LabelableInterface;
use Klipper\Component\Security\Model\SharingInterface;
use Klipper\Component\SecurityExtra\Sharing\SharingEntryLabelBuilderInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class LabelableLabelBuilder implements SharingEntryLabelBuilderInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports(object $identity, SharingInterface $sharing): bool
    {
        return $identity instanceof LabelableInterface;
    }

    /**
     * {@inheritdoc}
     *
     * @param LabelableInterface $identity
     */
    public function buildLabel(object $identity, SharingInterface $sharing): string
    {
        return $identity->getLabel();
    }
}
