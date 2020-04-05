<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\SecurityExtra\Doctrine\Validator\Constraints;

use Klipper\Component\DoctrineExtensions\Validator\Constraints\UniqueEntity;

/**
 * Constraint for the Role Unique Entity validator with disable sql filter option.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @Annotation
 * @Target({"CLASS", "ANNOTATION"})
 */
class RoleUniqueEntity extends UniqueEntity
{
    public $service = 'klipper_security_extra.doctrine.orm.validator.role_unique';
}
