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
use Klipper\Component\Security\PermissionContexts;

/**
 * Permission Context.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
final class PermissionContext implements ChoiceInterface
{
    public static function listIdentifiers(): array
    {
        return [
            PermissionContexts::ROLE => 'permission.context.role',
            PermissionContexts::ORGANIZATION_ROLE => 'permission.context.organization_role',
            PermissionContexts::SHARING => 'permission.context.sharing',
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
