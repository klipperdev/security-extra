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
     * Get the existing usernames.
     *
     * @param string[] $usernames The usernames
     *
     * @return string[]
     */
    public function getExistingUsernames(array $usernames): array;

    /**
     * Find entities by usernames.
     *
     * @param string[] $usernames The usernames
     *
     * @return UserInterface[]
     */
    public function findByUsernames(array $usernames): array;

    /**
     * Find the user by username of having emails.
     *
     * @param string[] $usernames The username of emails
     *
     * @return UserInterface[]
     */
    public function findByUsernameOrHavingEmails(array $usernames): array;
}
