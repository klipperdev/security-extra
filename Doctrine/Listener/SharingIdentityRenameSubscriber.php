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
use Klipper\Component\DoctrineExtra\Util\ClassUtils;
use Klipper\Component\Model\Traits\OrganizationalInterface;
use Klipper\Component\Security\Model\SharingInterface;
use Klipper\Component\Security\Sharing\SharingManagerInterface;
use Klipper\Component\SecurityExtra\Message\SharingIdentityRenameMessage;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class SharingIdentityRenameSubscriber implements EventSubscriber
{
    private SharingManagerInterface $sharingManager;

    private MessageBusInterface $messageBus;

    /**
     * @var SharingIdentityRenameMessage[]
     */
    private array $renameMessages = [];

    public function __construct(
        SharingManagerInterface $sharingManager,
        MessageBusInterface $messageBus
    ) {
        $this->sharingManager = $sharingManager;
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
            if ($entity instanceof SharingInterface) {
                $entityClass = ClassUtils::getClass($entity);

                if ($this->sharingManager->hasIdentityConfig($entityClass)) {
                    $changeSet = $uow->getEntityChangeSet($entity);
                    $suffix = $this->getOrgSuffix($entity);

                    foreach (['username', 'name'] as $field) {
                        if (isset($changeSet[$field])) {
                            $this->renameMessages[] = new SharingIdentityRenameMessage(
                                $entityClass,
                                $changeSet[$field][0].$suffix,
                                $changeSet[$field][1].$suffix
                            );
                        }
                    }
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

    private function getOrgSuffix(object $entity): string
    {
        return $entity instanceof OrganizationalInterface && null !== $entity->getOrganization()
            ? '__'.$entity->getOrganization()->getName()
            : '';
    }
}
