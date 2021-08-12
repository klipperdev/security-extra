<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\SecurityExtra\MessageHandler;

use Klipper\Component\SecurityExtra\Batch\RoleRename;
use Klipper\Component\SecurityExtra\Message\RoleRenameMessage;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class RoleRenameHandler implements MessageHandlerInterface
{
    private RoleRename $roleRename;

    public function __construct(RoleRename $roleRename)
    {
        $this->roleRename = $roleRename;
    }

    public function __invoke(RoleRenameMessage $message): void
    {
        $this->roleRename->rename(
            $message->getOldRoleName(),
            $message->getNewRoleName(),
            $message->getOrganizationId()
        );
    }
}
