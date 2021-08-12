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

use Klipper\Component\SecurityExtra\Batch\SharingIdentityRename;
use Klipper\Component\SecurityExtra\Message\SharingIdentityRenameMessage;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class SharingIdentityRenameHandler implements MessageHandlerInterface
{
    private SharingIdentityRename $identityRenameBatch;

    public function __construct(SharingIdentityRename $identityRenameBatch)
    {
        $this->identityRenameBatch = $identityRenameBatch;
    }

    public function __invoke(SharingIdentityRenameMessage $message): void
    {
        $this->identityRenameBatch->rename(
            $message->getIdentityClass(),
            $message->getOldName(),
            $message->getNewName(),
        );
    }
}
