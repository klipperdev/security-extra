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

use Doctrine\Persistence\ObjectRepository;
use Klipper\Component\DoctrineExtensionsExtra\Entity\Repository\Traits\InsensitiveRepositoryInterface;
use Klipper\Component\Security\Model\OrganizationInterface;

/**
 * Organization repository class.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface OrganizationRepositoryInterface extends ObjectRepository, InsensitiveRepositoryInterface
{
    /**
     * Find entities by organization names.
     *
     * @param string[] $names The organization names
     *
     * @return OrganizationInterface[]
     */
    public function findByNames(array $names): array;
}
