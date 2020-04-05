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
use Klipper\Component\Security\Model\Traits\OrganizationalInterface;
use Klipper\Component\Security\Model\Traits\OrganizationalRequiredInterface;
use Klipper\Component\Security\OrganizationalTypes;

/**
 * Doctrine Organizational Filter.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class OrganizationalFilter extends AbstractFilter
{
    /**
     * Defined the excluded entities for this filter.
     *
     * @param string[] $classNames The list of classname
     */
    public function setExcludedEntities(array $classNames): void
    {
        $this->setParameter('excluded_entities', array_values($classNames), 'array');
    }

    /**
     * Reset the excluded entities.
     */
    public function resetExcludedEntities(): void
    {
        $this->setParameter('excluded_entities', [], 'array');
    }

    /**
     * {@inheritdoc}
     *
     * @throws
     */
    protected function supports(ClassMetadata $targetEntity): bool
    {
        return $this->hasParameter('excluded_entities')
            && $this->hasParameter('excluded_classes')
            && $this->hasParameter('organization_id')
            && $this->hasParameter('organization_user_id')
            && $this->hasParameter('is_user_organization')
            && $this->hasParameter('context_optional_filter_type')
            && $this->hasParameter('optional_filter_all_classes')
            && $this->hasParameter('user_excluded_orgs_classes')
            && $this->isOrganizationalEntity($targetEntity)
            && !ClassUtil::isOneInstancesOf($targetEntity->getName(), $this->getRealParameter('excluded_entities'));
    }

    /**
     * {@inheritdoc}
     *
     * @throws
     */
    protected function doAddFilterConstraint(ClassMetadata $targetEntity, string $targetTableAlias): string
    {
        $columnMapping = $targetEntity->getAssociationMapping('organization');
        $column = $columnMapping['joinColumns'][0]['name'];
        $addCondSql = "{$targetTableAlias}.{$column} = {$this->getParameter('organization_id')}";

        if (!$this->isRequiredOrganizationalEntity($targetEntity)) {
            $addCondSql = $this->filterOptionalOrganizationEntity($targetEntity, $targetTableAlias, $addCondSql);
        } elseif ($this->getRealParameter('is_user_organization') && $this->isUserExcludedOrgsClass($targetEntity)) {
            $orgIds = $this->buildUserOrganizationsQuery();
            $addCondSql = "({$addCondSql}) OR {$targetTableAlias}.{$column} NOT IN ({$orgIds})";
        }

        return $addCondSql;
    }

    /**
     * Build the sql query for select the organization ids of current user.
     *
     * @throws
     */
    protected function buildUserOrganizationsQuery(): string
    {
        $uoMeta = $this->getEntityManager()->getClassMetadata(OrganizationUserInterface::class);
        $tbName = $uoMeta->getTableName();
        $orgMapping = $uoMeta->getAssociationMapping('organization');
        $id = $orgMapping['joinColumns'][0]['name'];
        $userMapping = $uoMeta->getAssociationMapping('user');
        $tbUserName = $userMapping['joinColumns'][0]['name'];

        return sprintf('SELECT %s FROM %s uo WHERE uo.%s = %s', $id, $tbName, $tbUserName, $this->getParameter('organization_user_id'));
    }

    /**
     * Filter the optional organization entity.
     *
     * @param ClassMetadata $targetEntity     The target entity
     * @param string        $targetTableAlias The target table alias
     * @param string        $addCondSql       The sql filter
     *
     * @throws
     */
    protected function filterOptionalOrganizationEntity(ClassMetadata $targetEntity, string $targetTableAlias, string $addCondSql): string
    {
        $optionalFilterType = $this->getRealParameter('context_optional_filter_type');
        $conn = $this->getConnection();
        $platform = $conn->getDatabasePlatform();
        $column = $targetEntity->getColumnName('organization_id');

        if (OrganizationalTypes::OPTIONAL_FILTER_ALL === $optionalFilterType
                || $this->isOptionalFilterAllClass($targetEntity)) {
            $addCondSql = "{$addCondSql} OR {$platform->getIsNullExpression($targetTableAlias.'.'.$column)}";
        } elseif (OrganizationalTypes::OPTIONAL_FILTER_WITHOUT_ORG === $optionalFilterType) {
            $addCondSql = (string) $platform->getIsNullExpression($targetTableAlias.'.'.$column);
        }

        return $addCondSql;
    }

    /**
     * Check if the target entity has the forced filter all option.
     *
     * @throws
     */
    protected function isOptionalFilterAllClass(ClassMetadata $targetEntity): bool
    {
        $ref = $targetEntity->reflClass;
        $optionalFilterAllClasses = $this->getRealParameter('optional_filter_all_classes');

        foreach ($optionalFilterAllClasses as $class) {
            if (ClassUtil::isInstanceOf($ref, $class)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the entity is a organizational entity.
     *
     * @param ClassMetadata $targetEntity The metadata of entity
     *
     * @throws
     */
    protected function isOrganizationalEntity(ClassMetadata $targetEntity): bool
    {
        $ref = $targetEntity->reflClass;
        $hasAssociationOrg = isset($targetEntity->associationMappings['organization'])
            && $targetEntity->associationMappings['organization']['isOwningSide'];

        foreach ($this->getRealParameter('excluded_classes') as $excludedClass) {
            if (ClassUtil::isInstanceOf($ref, $excludedClass)) {
                return false;
            }
        }

        return !(!$hasAssociationOrg
            || !$this->getRealParameter('has_organization')
            || !ClassUtil::isInstanceOf($ref, OrganizationalInterface::class));
    }

    /**
     * Check if the organizational is required or not.
     *
     * @param ClassMetadata $targetEntity The metadata of entity
     */
    protected function isRequiredOrganizationalEntity(ClassMetadata $targetEntity): bool
    {
        return ClassUtil::isInstanceOf($targetEntity->reflClass, OrganizationalRequiredInterface::class);
    }

    /**
     * Check if the target entity must exclude all entities attached in the all
     * organizations of current user.
     *
     * @param ClassMetadata $targetEntity The target entity
     *
     * @throws
     */
    protected function isUserExcludedOrgsClass(ClassMetadata $targetEntity): bool
    {
        $ref = $targetEntity->reflClass;
        $classes = $this->getRealParameter('user_excluded_orgs_classes');

        foreach ($classes as $class) {
            if (ClassUtil::isInstanceOf($ref, $class)) {
                return true;
            }
        }

        return false;
    }
}
