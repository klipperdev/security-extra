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
use Klipper\Component\Security\Model\OrganizationInterface;
use Klipper\Component\Security\Model\OrganizationUserInterface;

/**
 * Doctrine Organization Filter.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class OrganizationFilter extends AbstractFilter
{
    /**
     * @throws
     */
    protected function supports(ClassMetadata $targetEntity): bool
    {
        return $this->hasParameter('user_id')
            && null !== $this->getRealParameter('user_id')
            && ClassUtil::isInstanceOf($targetEntity->reflClass, OrganizationInterface::class);
    }

    /**
     * @throws
     */
    protected function doAddFilterConstraint(ClassMetadata $targetEntity, string $targetTableAlias): string
    {
        $conn = $this->getConnection();
        $platform = $conn->getDatabasePlatform();
        $userColumnName = $targetEntity->getColumnName('user_id');
        $userColumn = $targetTableAlias.'.'.$userColumnName;

        $ouMeta = $this->getClassMetadata(OrganizationUserInterface::class);
        $ouTable = $ouMeta->getTableName();
        $ouOrgColumnName = $ouMeta->getColumnName('organization_id');
        $ouUserColumnName = $ouMeta->getColumnName('user_id');
        $ouEnabledColumnName = $ouMeta->getColumnName('enabled');

        return $userColumn.' = '.$this->getParameter('user_id').' OR ('
            .$targetTableAlias.'.id IN ('
                .'SELECT ou.'.$ouOrgColumnName.' FROM '.$ouTable.' ou'
                .' WHERE ou.'.$ouUserColumnName.' = '.$this->getParameter('user_id')
                    .' AND ou.'.$ouEnabledColumnName.' = '.$platform->convertBooleans(true)
            .'))';
    }
}
