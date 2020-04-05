<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\SecurityExtra\Sharing;

use Klipper\Component\Security\Model\PermissionInterface;
use Klipper\Component\Security\Model\RoleInterface;
use Klipper\Component\Security\Model\SharingInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface SharingEntryInterface
{
    /**
     * Get the id.
     *
     * @return int|string
     */
    public function getId();

    /**
     * Get the sharing.
     */
    public function getSharing(): SharingInterface;

    /**
     * Get the subject instance.
     */
    public function getSubject(): object;

    /**
     * Get the identity instance.
     */
    public function getIdentity(): object;

    /**
     * Get the class name of object.
     */
    public function getIdentityClass(): string;

    /**
     * Get the label.
     */
    public function getLabel(): string;

    /**
     * Get the sharing type.
     */
    public function getType(): string;

    /**
     * Check if the sharing entry is enabled.
     */
    public function isEnabled(): bool;

    /**
     * Get the permissions.
     *
     * @return PermissionInterface[]
     */
    public function getPermissions(): array;

    /**
     * Get the roles.
     *
     * @return RoleInterface[]
     */
    public function getRoles(): array;

    /**
     * Get the role names.
     *
     * @return string[]
     */
    public function getRoleNames(): array;

    /**
     * Get the date when the sharing entry must start.
     */
    public function getStartedAt(): ?\DateTime;

    /**
     * Get the date when the sharing entry must end.
     */
    public function getEndedAt(): ?\DateTime;

    /**
     * Check if the sharing entry is active.
     */
    public function isActive(): bool;
}
