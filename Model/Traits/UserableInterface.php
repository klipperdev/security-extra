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

use Klipper\Component\Security\Model\UserInterface;

/**
 * Interface of add dependency entity with an user.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface UserableInterface
{
    /**
     * Get the user.
     */
    public function getUser(): ?UserInterface;

    /**
     * Get the user id.
     *
     * @return null|int|string
     */
    public function getUserId();
}
