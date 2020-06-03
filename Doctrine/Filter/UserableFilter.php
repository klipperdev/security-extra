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
use Klipper\Component\SecurityExtra\Model\Traits\UserableInterface;

/**
 * Doctrine Userable Filter.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class UserableFilter extends AbstractFilter
{
    /**
     * @throws
     */
    protected function supports(ClassMetadata $targetEntity): bool
    {
        $hasAssociationUser = isset($targetEntity->associationMappings['user'])
            && $targetEntity->associationMappings['user']['isOwningSide'];

        return ClassUtil::isInstanceOf($targetEntity->reflClass, UserableInterface::class)
            && $hasAssociationUser
            && $this->hasParameter('user_id')
            && null !== $this->getRealParameter('user_id');
    }

    protected function doAddFilterConstraint(ClassMetadata $targetEntity, string $targetTableAlias): string
    {
        $column = $targetEntity->getColumnName('user_id');

        return "{$targetTableAlias}.{$column} = {$this->getParameter('user_id')}";
    }
}
