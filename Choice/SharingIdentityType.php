<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\SecurityExtra\Choice;

use Klipper\Component\Choice\ChoiceInterface;

/**
 * Sharing Identity Type.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
final class SharingIdentityType implements ChoiceInterface
{
    /**
     * @var string[]
     */
    public const RESTRICTED = [
        'user',
    ];

    public static function listIdentifiers(): array
    {
        return [
            'user' => 'sharing.identity_type.user',
            'role' => 'sharing.identity_type.role',
            'group' => 'sharing.identity_type.group',
        ];
    }

    public static function getValues(): array
    {
        return array_keys(static::listIdentifiers());
    }

    public static function getTranslationDomain(): string
    {
        return 'choices';
    }
}
