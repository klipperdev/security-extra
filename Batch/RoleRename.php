<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\SecurityExtra\Batch;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Klipper\Component\Batch\JobResult;
use Klipper\Component\Batch\JobResultInterface;
use Klipper\Component\DoctrineExtensions\Util\SqlFilterUtil;
use Klipper\Component\Resource\Domain\DomainInterface;
use Klipper\Component\Resource\Domain\DomainManagerInterface;
use Klipper\Component\Security\Model\GroupInterface;
use Klipper\Component\Security\Model\OrganizationUserInterface;
use Klipper\Component\Security\Model\UserInterface;
use Klipper\Component\Security\Permission\PermissionManagerInterface;

/**
 * Batch service for rename the role in users, groups and organization users.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class RoleRename
{
    protected PermissionManagerInterface $permissionManager;

    protected DomainInterface $domainUser;

    protected DomainInterface $domainGroup;

    protected DomainInterface $domainOrgUser;

    protected int $batchSize;

    /**
     * @param DomainManagerInterface     $domainManager     The domain manager
     * @param PermissionManagerInterface $permissionManager The permission manager
     * @param int                        $batchSize         The batch size
     */
    public function __construct(
        DomainManagerInterface $domainManager,
        PermissionManagerInterface $permissionManager,
        int $batchSize = 20
    ) {
        $this->permissionManager = $permissionManager;
        $this->domainUser = $domainManager->get(UserInterface::class);
        $this->domainGroup = $domainManager->get(GroupInterface::class);
        $this->domainOrgUser = $domainManager->get(OrganizationUserInterface::class);
        $this->batchSize = $batchSize;
    }

    /**
     * Rename the role in users, groups and organization users.
     *
     * @param string          $oldName   The old name of role
     * @param string          $newName   The new name of role
     * @param null|int|string $orgId     The organization id
     * @param null|int        $batchSize The batch size
     */
    public function rename(string $oldName, string $newName, $orgId = null, ?int $batchSize = null): JobResultInterface
    {
        if (null === $orgId) {
            $res = $this->doRename($this->getQueryUsers($oldName, $batchSize), $oldName, $newName);

            if (!$res->isValid()) {
                return $res;
            }
        }

        $res = $this->doRename($this->getQueryGroups($oldName, $orgId, $batchSize), $oldName, $newName);

        if (null !== $orgId && $res->isValid()) {
            $query = $this->getQueryOrganizationUsers($oldName, $orgId, $batchSize);
            $res = $this->doRename($query, $oldName, $newName);
        }

        return $res;
    }

    /**
     * Do rename the role.
     *
     * @param Query  $query   The query
     * @param string $oldName The old name of role
     * @param string $newName The new name of role
     */
    protected function doRename(Query $query, string $oldName, string $newName): JobResultInterface
    {
        $res = new JobResult();
        $pmEnabled = $this->permissionManager->isEnabled();
        $this->permissionManager->setEnabled(false);
        $filters = SqlFilterUtil::findFilters($query->getEntityManager(), [], true);
        SqlFilterUtil::disableFilters($query->getEntityManager(), $filters);

        try {
            while (true) {
                $entities = $query->getResult();

                if (0 === \count($entities)) {
                    break;
                }

                $users = [];
                $groups = [];
                $orgUsers = [];

                foreach ($entities as $entity) {
                    if ($entity instanceof UserInterface ||
                            $entity instanceof GroupInterface ||
                            $entity instanceof OrganizationUserInterface) {
                        $entity->removeRole($oldName);
                        $entity->addRole($newName);
                    }

                    if ($entity instanceof UserInterface) {
                        $users[] = $entity;
                    } elseif ($entity instanceof GroupInterface) {
                        $groups[] = $entity;
                    } elseif ($entity instanceof OrganizationUserInterface) {
                        $orgUsers[] = $entity;
                    }
                }

                if (\count($users) > 0) {
                    $resBatch = $this->domainUser->updates($users);
                    $res->getConstraintViolationList()->addAll($resBatch->getErrors());
                } elseif (\count($groups) > 0) {
                    $resBatch = $this->domainGroup->updates($groups);
                    $res->getConstraintViolationList()->addAll($resBatch->getErrors());
                } elseif (\count($orgUsers) > 0) {
                    $resBatch = $this->domainOrgUser->updates($orgUsers);
                    $res->getConstraintViolationList()->addAll($resBatch->getErrors());
                }
            }
        } catch (\Throwable $e) {
            $res->setThrowable($e);
        }

        $this->permissionManager->setEnabled($pmEnabled);
        SqlFilterUtil::enableFilters($query->getEntityManager(), $filters);

        return $res;
    }

    /**
     * Get the query for users.
     *
     * @param string   $oldName   The old name of role
     * @param null|int $batchSize The batch size
     */
    protected function getQueryUsers(string $oldName, ?int $batchSize = null): Query
    {
        $qb = $this->domainUser->createQueryBuilder('uo');
        $this->injectRole($qb, $oldName, $batchSize);

        return $qb->getQuery();
    }

    /**
     * Get the query for groups.
     *
     * @param string          $oldName   The old name of role
     * @param null|int|string $orgId     The organization id
     * @param null|int        $batchSize The batch size
     */
    protected function getQueryGroups(string $oldName, $orgId = null, ?int $batchSize = null): Query
    {
        $qb = $this->domainGroup->createQueryBuilder('uo');
        $this->injectRole($qb, $oldName, $batchSize);
        $this->injectOrg($qb, $orgId);

        return $qb->getQuery();
    }

    /**
     * Get the query for organization users.
     *
     * @param string     $oldName   The old name of role
     * @param int|string $orgId     The organization id
     * @param null|int   $batchSize The batch size
     */
    protected function getQueryOrganizationUsers(string $oldName, $orgId, ?int $batchSize = null): Query
    {
        $qb = $this->domainOrgUser->createQueryBuilder('uo');
        $this->injectRole($qb, $oldName, $batchSize);
        $this->injectOrg($qb, $orgId);

        return $qb->getQuery();
    }

    /**
     * Inject the where condition in query builder.
     *
     * @param QueryBuilder $qb        The query builder
     * @param string       $roleName  The role name
     * @param null|int     $batchSize The batch size
     *
     * @throws
     */
    protected function injectRole(QueryBuilder $qb, string $roleName, ?int $batchSize = null): void
    {
        $platform = $qb->getEntityManager()->getConnection()->getDatabasePlatform();

        $qb
            ->where('uo.roles LIKE :role')
            ->setParameter('role', '%'.$platform->quoteIdentifier($roleName).'%')
            ->setMaxResults($this->getBatchSize($batchSize))
        ;
    }

    /**
     * Inject the where condition for select organization in query builder.
     *
     * @param QueryBuilder    $qb    The query builder
     * @param null|int|string $orgId The organization id
     */
    protected function injectOrg(QueryBuilder $qb, $orgId = null): void
    {
        if (null !== $orgId) {
            $qb
                ->andWhere('uo.organization = :orgId')
                ->setParameter('orgId', $orgId, \is_string($orgId) ? Type::GUID : null)
            ;
        }
    }

    /**
     * Get th batch size.
     *
     * @param null|int $batchSize The batch size
     */
    protected function getBatchSize(?int $batchSize = null): int
    {
        return $batchSize ?? $this->batchSize;
    }
}
