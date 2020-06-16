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

/**
 * Doctrine Organization Only Filter.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class OrganizationOnlyFilter extends AbstractFilter
{
    /**
     * @throws
     */
    protected function supports(ClassMetadata $targetEntity): bool
    {
        return ClassUtil::isInstanceOf($targetEntity->reflClass, OrganizationInterface::class);
    }

    /**
     * @throws
     */
    protected function doAddFilterConstraint(ClassMetadata $targetEntity, string $targetTableAlias): string
    {
        $userColumnName = $targetEntity->getColumnName('user_id');
        $userColumn = $targetTableAlias.'.'.$userColumnName;

        return $userColumn.' is null';
    }
}
