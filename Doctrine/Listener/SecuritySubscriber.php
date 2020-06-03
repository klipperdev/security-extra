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
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\PersistentCollection;
use Klipper\Component\DoctrineExtensionsExtra\Util\ListenerUtil;
use Klipper\Component\Security\Model\Traits\RoleableInterface;
use Klipper\Component\Security\Model\UserInterface;
use Klipper\Component\SecurityExtra\Model\Traits\GroupableInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class SecuritySubscriber implements EventSubscriber
{
    public ?ContainerInterface $container = null;

    protected ?TokenStorageInterface $tokenStorage = null;

    protected ?TranslatorInterface $translator = null;

    public function getSubscribedEvents(): array
    {
        return [
            Events::onFlush,
        ];
    }

    /**
     * On flush action.
     */
    public function onFlush(OnFlushEventArgs $args): void
    {
        $this->init();
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();
        $errors = [];

        /** @var PersistentCollection $collection */
        foreach ($uow->getScheduledCollectionUpdates() as $collection) {
            $map = $collection->getMapping();
            $owner = $collection->getOwner();

            if ($owner instanceof UserInterface && $owner instanceof GroupableInterface && 'groups' === $map['fieldName']) {
                if (!$this->isValidUser() && ($collection->getInsertDiff() > 0 || $collection->getDeleteDiff() > 0)) {
                    $msg = $this->translator->trans('field.no_edit_authorization', [], 'validators');
                    $errors[] = ListenerUtil::createViolation($msg, $collection->getOwner(), 'groups');
                }
            }
        }

        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            $this->validateEditAuthorization($em, $entity, $errors);
        }

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            $this->validateEditAuthorization($em, $entity, $errors);
        }

        if (\count($errors) > 0) {
            ListenerUtil::thrownErrors($errors);
        }
    }

    /**
     * Validate the edit authorization.
     *
     * @param EntityManagerInterface         $em     The entity manager
     * @param object                         $entity The entity
     * @param ConstraintViolationInterface[] $errors The errors by reference
     */
    protected function validateEditAuthorization(EntityManagerInterface $em, object $entity, array &$errors): void
    {
        if ($entity instanceof UserInterface) {
            $changeSet = $em->getUnitOfWork()->getEntityChangeSet($entity);

            if (isset($changeSet['roles']) && null !== $changeSet['roles'][0] && !$this->isValidUser()) {
                $msg = $this->translator->trans('field.no_edit_authorization', [], 'validators');
                $errors[] = ListenerUtil::createViolation($msg, $entity, 'roles');
            }
        }
    }

    /**
     * Check if the current user is a super admin.
     */
    protected function isValidUser(): bool
    {
        if (null === $this->tokenStorage->getToken()
                || !$this->tokenStorage->getToken()->getUser() instanceof UserInterface) {
            return null === $this->tokenStorage->getToken();
        }

        $user = $this->tokenStorage->getToken()->getUser();

        return $user instanceof RoleableInterface && $user->hasRole('ROLE_SUPER_ADMIN');
    }

    /**
     * Init the dependencies.
     */
    private function init(): void
    {
        if (null !== $this->container) {
            $this->tokenStorage = $this->container->get('security.token_storage');
            $this->translator = $this->container->get('translator');
            $this->container = null;
        }
    }
}
