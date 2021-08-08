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
use Klipper\Component\Security\Model\UserInterface;

/**
 * User repository class.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface UserRepositoryInterface extends ObjectRepository, InsensitiveRepositoryInterface
{
    /**
     * Get the existing user identifiers.
     *
     * @param string[] $userIdentifiers The user identifiers
     *
     * @return string[]
     */
    public function getExistingUserIdentifiers(array $userIdentifiers): array;

    /**
     * Find entities by user identifiers.
     *
     * @param string[] $userIdentifiers The user identifiers
     *
     * @return UserInterface[]
     */
    public function findByUserIdentifiers(array $userIdentifiers): array;

    /**
     * Find the user by user identifier of having emails.
     *
     * @param string[] $userIdentifiers The user identifier of emails
     *
     * @return UserInterface[]
     */
    public function findByUserIdentifierOrHavingEmails(array $userIdentifiers): array;
}
