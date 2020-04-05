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

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Validator\Constraint;

/**
 * Constraint for the Role Unique Entity validator with disable sql filter option.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class RoleUniqueEntityValidator extends OrganizationalUniqueEntityValidator
{
    /**
     * {@inheritdoc}
     */
    protected function getCriteria($entity, Constraint $constraint, ObjectManager $em): array
    {
        $criteria = parent::getCriteria($entity, $constraint, $em);

        if (isset($criteria['name']) && 0 !== stripos($criteria['name'], 'ROLE_')) {
            $criteria['name'] = 'ROLE_'.$criteria['name'];
        }

        return $criteria;
    }
}
