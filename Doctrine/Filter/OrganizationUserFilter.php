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
use Klipper\Component\Security\Model\OrganizationUserInterface;

/**
 * Doctrine Organization User Filter.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class OrganizationUserFilter extends AbstractFilter
{
    /**
     * Define if the sql filter must filter the organization users by organization
     * or by the current user.
     *
     * @param bool $isCurrentOrganizations The value
     */
    public function setCurrentOrganizations(bool $isCurrentOrganizations): void
    {
        $this->setParameter('is_current_organizations', (bool) $isCurrentOrganizations, 'boolean');
    }

    /**
     * @throws
     */
    protected function supports(ClassMetadata $targetEntity): bool
    {
        return ClassUtil::isInstanceOf($targetEntity->reflClass, OrganizationUserInterface::class)
            && $this->hasParameter('is_current_organizations')
            && $this->hasParameter('organization_id')
            && $this->hasParameter('is_user_organization')
            && $this->hasParameter('has_organization')
            && $this->getRealParameter('has_organization');
    }

    /**
     * @throws
     */
    protected function doAddFilterConstraint(ClassMetadata $targetEntity, string $targetTableAlias): string
    {
        $isUserOrg = $this->getRealParameter('is_user_organization');
        $isCurrentOrgs = $this->getRealParameter('is_current_organizations');

        if ($isUserOrg || $isCurrentOrgs) {
            $conn = $this->getConnection();
            $platform = $conn->getDatabasePlatform();
            $column = $targetEntity->getColumnName('user_id');
            $columnToken = $targetEntity->getColumnName('invitation_email');
            $addCondSql = "{$targetTableAlias}.{$column} = {$this->getParameter('user_id')}";
            $addCondSql = "{$addCondSql} AND {$platform->getIsNullExpression($targetTableAlias.'.'.$columnToken)}";
        } else {
            $column = $targetEntity->getColumnName('organization_id');
            $addCondSql = "{$targetTableAlias}.{$column} = {$this->getParameter('organization_id')}";
        }

        return $addCondSql;
    }
}
