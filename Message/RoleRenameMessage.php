<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\SecurityExtra\Message;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class RoleRenameMessage
{
    private string $oldRoleName;

    private string $newRoleName;

    /**
     * @var null|int|string
     */
    private $organizationId;

    /**
     * @param null|int|string $organizationId
     */
    public function __construct(string $oldRoleName, string $newRoleName, $organizationId)
    {
        $this->oldRoleName = $oldRoleName;
        $this->newRoleName = $newRoleName;
        $this->organizationId = $organizationId;
    }

    public function getOldRoleName(): string
    {
        return $this->oldRoleName;
    }

    public function getNewRoleName(): string
    {
        return $this->newRoleName;
    }

    /**
     * @return null|int|string
     */
    public function getOrganizationId()
    {
        return $this->organizationId;
    }
}
