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

use Symfony\Component\Validator\Constraint;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @Annotation
 * @Target({"CLASS", "ANNOTATION"})
 */
class UsernameSameOrgName extends Constraint
{
    public $message = 'This value is already used.';
    public $service = 'klipper_security_extra.doctrine.orm.validator.unique_username_organization_name';
    public $filters = [];
    public $allFilters = true;

    /**
     * The validator must be defined as a service with this name.
     */
    public function validatedBy(): string
    {
        return $this->service;
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
