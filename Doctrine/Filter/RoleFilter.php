<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\SecurityExtra\Doctrine\Filter;

use Doctrine\ORM\Mapping\ClassMetadata;
use Klipper\Component\DoctrineExtensions\Filter\AbstractFilter;
use Klipper\Component\Object\Util\ClassUtil;
use Klipper\Component\Security\Model\RoleInterface;

/**
 * Doctrine Role Filter.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class RoleFilter extends AbstractFilter
{
    /**
     * @throws
     */
    protected function supports(ClassMetadata $targetEntity): bool
    {
        return ClassUtil::isInstanceOf($targetEntity->reflClass, RoleInterface::class)
            && $this->hasParameter('is_admin_section')
            && !$this->getRealParameter('is_admin_section')
            && $this->hasParameter('excluded_roles')
            && !empty($this->getRealParameter('excluded_roles'));
    }

    protected function doAddFilterConstraint(ClassMetadata $targetEntity, string $targetTableAlias): string
    {
        $excludedRoles = $this->buildRoleNamesQuery();
        $this->setParameter('excludedRoles', $excludedRoles);

        return "{$targetTableAlias}.{$targetEntity->getColumnName('name')} NOT IN ({$excludedRoles})";
    }

    /**
     * Build the query of role names.
     *
     * @throws
     */
    protected function buildRoleNamesQuery(): string
    {
        $roles = '';

        foreach ($this->getRealParameter('excluded_roles') as $role) {
            $roles .= $this->getConnection()->quote($role).',';
        }

        return rtrim($roles, ',');
    }
}
