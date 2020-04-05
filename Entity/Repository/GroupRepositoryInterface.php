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
use Klipper\Component\DoctrineExtensionsExtra\Entity\Repository\Traits\InsensitiveRepositoryInterface;
use Klipper\Component\Security\Model\GroupInterface;

/**
 * Group repository class.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface GroupRepositoryInterface extends ObjectRepository, InsensitiveRepositoryInterface
{
    /**
     * Find entities by group names.
     *
     * @param string[] $names The group names
     *
     * @return GroupInterface[]
     */
    public function findByNames(array $names): array;
}
