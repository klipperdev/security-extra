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
 * Sharing constraint.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @Annotation
 */
class Sharing extends Constraint
{
    public string $message = 'sharing.manipulation.access_deny';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
