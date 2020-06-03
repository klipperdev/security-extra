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

use Klipper\Component\Security\Model\SharingInterface;
use Klipper\Component\Security\Model\UserInterface;
use Klipper\Component\SecurityExtra\Sharing\SharingEntryLabelBuilderInterface;
use Klipper\Component\Uuid\Util\UuidUtil;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class UsernameLabelBuilder implements SharingEntryLabelBuilderInterface
{
    public function supports(object $identity, SharingInterface $sharing): bool
    {
        return $identity instanceof UserInterface && !UuidUtil::isV4($identity->getUsername());
    }

    /**
     * @param UserInterface $identity
     */
    public function buildLabel(object $identity, SharingInterface $sharing): string
    {
        return $identity->getUsername();
    }
}
