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

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Klipper\Component\DoctrineExtensions\Util\SqlFilterUtil;
use Klipper\Component\Security\Doctrine\DoctrineUtils;
use Klipper\Component\Security\Model\GroupInterface;
use Klipper\Component\Security\Model\OrganizationInterface;
use Klipper\Component\Security\Model\OrganizationUserInterface;
use Klipper\Component\Security\Model\UserInterface;
use Klipper\Component\SecurityExtra\Entity\Repository\OrganizationUserRepositoryInterface;

/**
 * Organization User repository class.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @see OrganizationUserRepositoryInterface
 *
 * @method QueryBuilder  createQueryBuilder(string $alias)
 * @method ClassMetadata getClassMetadata()
 */
trait OrganizationUserRepositoryTrait
{
    /**
     * @see OrganizationUserRepositoryInterface::findCurrentOrganizationUserByOrganizationName
     */
    public function findCurrentOrganizationUserByOrganizationName(string $organizationName, ?UserInterface $user): ?OrganizationUserInterface
    {
        $userOrg = null;

        if ($user instanceof UserInterface) {
            $result = $this->createQueryBuilder('uo')
                ->addSelect('o, u, g')
                ->where('uo.user = :userId')
                ->andWhere('o.name = :organizationName')
                ->leftJoin('uo.organization', 'o', Join::WITH, 'o.id = uo.organization')
                ->leftJoin('uo.user', 'u', Join::WITH, 'u.id = uo.user')
                ->leftJoin('uo.groups', 'g', Join::WITH, 'g.organization = uo.organization')
                ->setParameter('userId', $user->getId())
                ->setParameter('organizationName', $organizationName)
                ->getQuery()
                //->setCacheable(true)
                //->setCacheMode(Cache::MODE_GET)
                //->setResultCacheId(sha1('user_org_'.$user->getId().'__'.$organizationName))
                ->getResult()
            ;

            $userOrg = \count($result) > 0 ? $result[0] : null;
        }

        return $userOrg;
    }

    /**
     * @see OrganizationUserRepositoryInterface::createQueryForOrgUsersByOrg
     */
    public function createQueryForOrgUsersByOrg(?OrganizationInterface $org): QueryBuilder
    {
        $orgId = null !== $org ? $org->getId() : DoctrineUtils::getMockZeroId($this->getClassMetadata());

        return $this->createQueryBuilder('uo')
            ->addSelect('uo, u, o, p')
            ->where('uo.organization = :orgId')
            ->andWhere('o.user IS null')
            ->leftJoin('uo.user', 'u')
            ->leftJoin('uo.organization', 'o')
            ->leftJoin('u.profile', 'p')
            ->leftJoin('uo.groups', 'g')
            ->setParameter('orgId', $orgId, \is_string($orgId) && !is_numeric($orgId) ? Types::GUID : null)
        ;
    }

    /**
     * @see OrganizationUserRepositoryInterface::getOrderedOrganizations
     */
    public function getOrderedOrganizations(): array
    {
        $qb = $this->createQueryBuilder('uo')
            ->addSelect('uo')
            ->leftJoin('uo.user', 'u')
            ->leftJoin('uo.organization', 'o')
            ->leftJoin('u.profile', 'p')
            ->orderBy('o.label')
            ->getQuery()
        ;

        return $qb->getResult();
    }

    /**
     * @see OrganizationUserRepositoryInterface::findAdminByOrganizationIds
     */
    public function findAdminByOrganizationIds(array $organizationsIds): array
    {
        $em = $this->getEntityManager();
        $filters = SqlFilterUtil::findFilters($em, [], true);

        SqlFilterUtil::disableFilters($em, $filters);
        $result = $this->createQueryBuilder('uo')
            ->where('uo.organization IN (:orgIds)')
            ->andWhere('uo.roles LIKE :role')
            ->setParameter('orgIds', $organizationsIds)
            ->setParameter('role', '%"'.'ROLE_ADMIN'.'"%')
            ->getQuery()->getResult()
        ;
        SqlFilterUtil::enableFilters($em, $filters);

        return $result;
    }

    /**
     * @see OrganizationUserRepositoryInterface::findOrganizationUserById
     *
     * @param mixed $id
     */
    public function findOrganizationUserById($id): ?OrganizationUserInterface
    {
        $em = $this->getEntityManager();
        $filters = SqlFilterUtil::findFilters($em, [], true);
        SqlFilterUtil::disableFilters($em, $filters);

        $result = $this->createQueryBuilder('uo')
            ->addSelect('o, u, g')
            ->where('uo.id = :id')
            ->leftJoin(OrganizationInterface::class, 'o', Join::WITH, 'o.id = uo.organization')
            ->leftJoin(UserInterface::class, 'u', Join::WITH, 'u.id = uo.user')
            ->leftJoin(GroupInterface::class, 'g', Join::WITH, 'g.organization = uo.organization')
            ->setMaxResults(1)
            ->setParameter('id', $id, \is_string($id) && !is_numeric($id) ? Types::GUID : null)
            ->getQuery()
            ->getResult()
        ;

        SqlFilterUtil::enableFilters($em, $filters);

        return \count($result) > 0 ? $result[0] : null;
    }
}
