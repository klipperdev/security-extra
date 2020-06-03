<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\SecurityExtra\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Reserved name constraint.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @Annotation
 */
class IsReservedName extends Constraint
{
    public string $message = 'This value is already used.';

    public function validatedBy(): string
    {
        return 'klipper_security_extra.validator.reserved_name';
    }
}
