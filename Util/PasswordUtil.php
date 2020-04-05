<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\SecurityExtra\Util;

/**
 * Password Utils.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
abstract class PasswordUtil
{
    /**
     * Generate a random password.
     *
     * @param int $length The length of password
     *
     * @throws
     */
    public static function generate(int $length = 8): string
    {
        $password = '';
        $availables = 'abcdefghjkmnopqrstuvwxyzABCDEFGHJKLMNOPQRSTUVWXYZ023456789+@!$%?&';
        $sizeAvailables = \strlen($availables);

        for ($i = 0; $i < $length; ++$i) {
            $random = random_int(0, $sizeAvailables - 1);
            $password .= $availables[$random];
        }

        return $password;
    }
}
