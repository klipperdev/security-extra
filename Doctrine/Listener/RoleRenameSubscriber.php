<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\SecurityExtra\Doctrine\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Klipper\Component\Model\Traits\OrganizationalInterface;
use Klipper\Component\Security\Model\RoleInterface;
use Klipper\Component\SecurityExtra\Message\RoleRenameMessage;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class RoleRenameSubscriber implements EventSubscriber
{
    private MessageBusInterface $messageBus;

    /**
     * @var RoleRenameMessage[]
     */
    private array $renameMessages = [];

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::onFlush,
            Events::postFlush,
        ];
    }

    public function onFlush(OnFlushEventArgs $args): void
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if ($entity instanceof RoleInterface) {
                $changeSet = $uow->getEntityChangeSet($entity);

                if (isset($changeSet['name'])) {
                    $this->renameMessages[] = new RoleRenameMessage(
                        $changeSet['name'][0],
                        $changeSet['name'][1],
                        $entity instanceof OrganizationalInterface ? $entity->getOrganizationId() : null
                    );
                }
            }
        }
    }

    public function postFlush(): void
    {
        foreach ($this->renameMessages as $message) {
            $this->messageBus->dispatch($message);
        }

        $this->renameMessages = [];
    }
}
