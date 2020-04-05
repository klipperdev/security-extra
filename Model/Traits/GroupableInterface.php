<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\SecurityExtra\Model\Traits;

use Klipper\Component\Security\Model\Traits\GroupableInterface as BaseGroupableInterface;

/**
 * Groupable interface.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface GroupableInterface extends BaseGroupableInterface
{
    /**
     * Gets the name of the groups.
     */
    public function getGroupNames(): array;
}
