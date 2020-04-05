<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\SecurityExtra\Entity\Repository;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\QueryBuilder;
use Klipper\Component\Security\Model\OrganizationInterface;
use Klipper\Component\Security\Model\OrganizationUserInterface;
use Klipper\Component\Security\Model\UserInterface;

/**
 * Organization User repository class.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface OrganizationUserRepositoryInterface extends ObjectRepository
{
    /**
     * @param string             $organizationName The organization name
     * @param null|UserInterface $user             The user
     */
    public function findCurrentOrganizationUserByOrganizationName(string $organizationName, ?UserInterface $user): ?OrganizationUserInterface;

    public function createQueryForOrgUsersByOrg(?OrganizationInterface $org): QueryBuilder;

    /**
     * @return OrganizationUserInterface[]
     */
    public function getOrderedOrganizations(): array;

    /**
     * @param int[]|string[] $organizationsIds The organization ids
     *
     * @return OrganizationUserInterface[]
     */
    public function findAdminByOrganizationIds(array $organizationsIds): array;

    /**
     * @param int|string $id The user id
     */
    public function findOrganizationUserById($id): ?OrganizationUserInterface;
}
