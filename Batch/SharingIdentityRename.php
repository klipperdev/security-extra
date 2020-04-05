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

use Klipper\Component\Batch\JobResult;
use Klipper\Component\Batch\JobResultInterface;
use Klipper\Component\DoctrineExtensions\Util\SqlFilterUtil;
use Klipper\Component\Object\Util\ClassUtil;
use Klipper\Component\Resource\Domain\DomainInterface;
use Klipper\Component\Resource\Domain\DomainManagerInterface;
use Klipper\Component\Security\Model\OrganizationInterface;
use Klipper\Component\Security\Model\SharingInterface;
use Klipper\Component\Security\Permission\PermissionManagerInterface;
use Klipper\Component\Security\Sharing\SharingManagerInterface;

/**
 * Batch service to rename the sharing identity.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class SharingIdentityRename
{
    /**
     * @var PermissionManagerInterface
     */
    protected $permissionManager;

    /**
     * @var SharingManagerInterface
     */
    protected $sharingManager;

    /**
     * @var DomainInterface
     */
    protected $domainSharing;

    /**
     * @var int
     */
    protected $batchSize;

    /**
     * Constructor.
     *
     * @param DomainManagerInterface     $domainManager     The domain manager
     * @param PermissionManagerInterface $permissionManager The permission manager
     * @param SharingManagerInterface    $sharingManager    The sharing manager
     * @param int                        $batchSize         The batch size
     */
    public function __construct(
        DomainManagerInterface $domainManager,
        PermissionManagerInterface $permissionManager,
        SharingManagerInterface $sharingManager,
        int $batchSize = 20
    ) {
        $this->permissionManager = $permissionManager;
        $this->sharingManager = $sharingManager;
        $this->domainSharing = $domainManager->get(SharingInterface::class);
        $this->batchSize = $batchSize;
    }

    /**
     * Rename the role in users, groups and organization users.
     *
     * @param string   $identityClass The identity class
     * @param string   $oldName       The old name of identity
     * @param string   $newName       The new name of identity
     * @param null|int $batchSize     The batch size
     */
    public function rename(string $identityClass, string $oldName, string $newName, ?int $batchSize = null): JobResultInterface
    {
        $res = new JobResult();
        $this->sharingManager->renameIdentity($identityClass, $oldName, $newName);

        if (ClassUtil::isInstanceOf($identityClass, OrganizationInterface::class)) {
            $this->doRenameIdentities($res, $oldName, $newName, $batchSize);
        }

        return $res;
    }

    protected function doRenameIdentities(JobResultInterface $res, string $oldName, string $newName, ?int $batchSize = null): void
    {
        $query = $this->domainSharing->createQueryBuilder('s')
            ->where('s.identityName LIKE :name')
            ->setParameter('name', sprintf('%%__%s', $oldName))
            ->setMaxResults($this->getBatchSize($batchSize))
            ->getQuery()
        ;

        $pmEnabled = $this->permissionManager->isEnabled();
        $this->permissionManager->setEnabled(false);

        $filters = SqlFilterUtil::findFilters($query->getEntityManager(), [], true);
        SqlFilterUtil::disableFilters($query->getEntityManager(), $filters);

        try {
            while (true) {
                /** @var SharingInterface[] $entities */
                $entities = $query->getResult();

                if (0 === \count($entities)) {
                    break;
                }

                foreach ($entities as $entity) {
                    $entity->setIdentityName(str_replace(
                        '__'.$oldName,
                        '__'.$newName,
                        $entity->getIdentityName()
                    ));
                }

                $resBatch = $this->domainSharing->updates($entities);
                $res->getConstraintViolationList()->addAll($resBatch->getErrors());
            }
        } catch (\Exception $e) {
            $res->setException($e);
        }

        $this->permissionManager->setEnabled($pmEnabled);
        SqlFilterUtil::enableFilters($query->getEntityManager(), $filters);
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
