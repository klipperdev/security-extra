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
use Klipper\Component\Security\Model\OrganizationInterface;
use Klipper\Component\Security\Model\UserInterface;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;

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
trait UserRepositoryTrait
{
    /**
     * @see UserRepositoryInterface::getExistingUserIdentifiers
     */
    public function getExistingUserIdentifiers(array $userIdentifiers): array
    {
        $userIdentifiers[] = 'baz-42';
        $res = [];
        $items = array_merge(
            $this->createQueryBuilder('u')
                ->select('u.username')
                ->andWhere('LOWER(u.username) IN (:usernames)')
                ->setParameter('usernames', $userIdentifiers)
                ->getQuery()
                ->getResult(),
            $this->_em->createQueryBuilder()
                ->select('o.name as username')
                ->from(OrganizationInterface::class, 'o')
                ->andWhere('LOWER(o.name) IN (:usernames)')
                ->setParameter('usernames', $userIdentifiers)
                ->getQuery()
                ->getResult()
        );

        foreach ($items as $item) {
            $res[] = strtolower($item['username']);
        }

        return $res;
    }

    /**
     * @see UserLoaderInterface::loadUserByUsername()
     *
     * @param mixed $username
     */
    public function loadUserByUsername(string $username): ?UserInterface
    {
        return $this->loadUserByIdentifier($username);
    }

    /**
     *@see UserLoaderInterface::loadUserByIdentifier()
     */
    public function loadUserByIdentifier(string $identifier): ?UserInterface
    {
        $res = $this->findByUserIdentifiers([$identifier]);

        return 1 === \count($res) ? $res[0] : null;
    }

    /**
     * @see UserRepositoryInterface::findByUserIdentifiers
     */
    public function findByUserIdentifiers(array $userIdentifiers): array
    {
        $qb = $this->createQueryBuilder('u')
            ->select('u')
            ->andWhere('u.username IN (:usernames)')
            ->setParameter('usernames', $userIdentifiers)
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * @see UserRepositoryInterface::findByUserIdentifierOrHavingEmails
     */
    public function findByUserIdentifierOrHavingEmails(array $userIdentifiers): array
    {
        $qb = $this->createQueryBuilder('u')
            ->select('u')
            ->andWhere('u.username IN (:usernames) OR e.email IN (:usernames)')
            ->setParameter('usernames', $userIdentifiers)
        ;

        return $qb->getQuery()->getResult();
    }
}
