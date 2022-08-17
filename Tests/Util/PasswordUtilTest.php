<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\SecurityExtra\Tests\Util;

use Klipper\Component\SecurityExtra\Util\PasswordUtil;
use PHPUnit\Framework\TestCase;

/**
 * Password util tests.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class PasswordUtilTest extends TestCase
{
    public function testGenerateDefault(): void
    {
        static::assertEquals(8, \strlen(PasswordUtil::generate()));
    }

    public function testGenerateCustomLength(): void
    {
        static::assertEquals(12, \strlen(PasswordUtil::generate(12)));
    }
}
