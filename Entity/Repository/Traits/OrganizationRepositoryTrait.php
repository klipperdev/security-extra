<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\SecurityExtra\Entity\Repository\Traits;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use Klipper\Component\SecurityExtra\Entity\Repository\OrganizationRepositoryInterface;

/**
 * User repository class.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @see UserRepositoryInterface
 *
 * @property EntityManagerInterface $_em
 *
 * @method QueryBuilder  createQueryBuilder(string $alias)
 * @method ClassMetadata getClassMetadata()
 */
trait OrganizationRepositoryTrait
{
    /**
     * @see OrganizationRepositoryInterface::findByNames()
     */
    public function findByNames(array $names): array
    {
        $qb = $this->createQueryBuilder('o');
        $alias = current($qb->getRootAliases());

        $qb
            ->andWhere("{$alias}.name IN (:names)")
            ->setParameter('names', $names)
        ;

        return $qb->getQuery()->getResult();
    }
}
