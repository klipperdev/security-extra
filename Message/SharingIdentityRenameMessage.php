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
class SharingIdentityRenameMessage
{
    private string $identityClass;

    private string $oldName;

    private string $newName;

    public function __construct(string $identityClass, string $oldRoleName, string $newRoleName)
    {
        $this->identityClass = $identityClass;
        $this->oldName = $oldRoleName;
        $this->newName = $newRoleName;
    }

    public function getIdentityClass(): string
    {
        return $this->identityClass;
    }

    public function getOldName(): string
    {
        return $this->oldName;
    }

    public function getNewName(): string
    {
        return $this->newName;
    }
}
