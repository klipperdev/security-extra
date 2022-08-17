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
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\UnitOfWork;
use Klipper\Component\DoctrineExtensionsExtra\Util\ListenerUtil;
use Klipper\Component\DoctrineExtra\Util\ClassUtils;
use Klipper\Component\Model\Traits\LabelableInterface;
use Klipper\Component\Resource\Object\ObjectFactoryInterface;
use Klipper\Component\Security\Model\OrganizationInterface;
use Klipper\Component\Security\Model\Traits\OrganizationalInterface;
use Klipper\Component\Security\Model\Traits\OrganizationalOptionalInterface;
use Klipper\Component\Security\Model\Traits\OrganizationalRequiredInterface;
use Klipper\Component\Security\Model\Traits\RoleableInterface;
use Klipper\Component\Security\Model\UserInterface;
use Klipper\Component\SecurityExtra\Entity\Repository\UserRepositoryInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class UserSubscriber implements EventSubscriber
{
    protected TranslatorInterface $translator;

    protected ValidatorInterface $validator;

    protected ObjectFactoryInterface $objectFactory;

    /**
     * @var string[]
     */
    protected array $organizationDefaultRoles;

    /**
     * @param string[] $organizationDefaultRoles
     */
    public function __construct(
        TranslatorInterface $translator,
        ValidatorInterface $validator,
        ObjectFactoryInterface $objectFactory,
        array $organizationDefaultRoles = []
    ) {
        $this->translator = $translator;
        $this->validator = $validator;
        $this->objectFactory = $objectFactory;
        $this->organizationDefaultRoles = $organizationDefaultRoles;
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::prePersist,
            Events::preUpdate,
            Events::onFlush,
        ];
    }

    /**
     * On pre persist action.
     */
    public function prePersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof UserInterface) {
            $this->createOrganization($entity);
        }
    }

    /**
     * On pre update action.
     */
    public function preUpdate(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        $em = $args->getObjectManager();
        $uow = $em->getUnitOfWork();

        if ($entity instanceof UserInterface) {
            $this->updateUser($entity, $uow);
        }
    }

    /**
     * On flush action.
     */
    public function onFlush(OnFlushEventArgs $args): void
    {
        $em = $args->getObjectManager();
        $uow = $em->getUnitOfWork();

        /** @var UserInterface[] $generateUsernames */
        $generateUsernames = [];
        $existingOrgNames = [];
        $errors = [];

        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            if ($entity instanceof UserInterface && null === $entity->getUserIdentifier()) {
                $generateUsernames[] = $entity;
            } elseif ($entity instanceof OrganizationInterface) {
                $existingOrgNames[] = $entity->getName();
            }
        }

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if ($entity instanceof UserInterface) {
                $this->updateOrganization($entity, $em, $uow);
            }
        }

        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            $this->validateDeletions($entity, $errors);
        }

        if (!empty($generateUsernames)) {
            $this->generateUsernames($em, $uow, $generateUsernames, $existingOrgNames);
        }

        if (\count($errors) > 0) {
            ListenerUtil::thrownErrors($errors);
        }
    }

    /**
     * Generate the usernames if they are empty.
     *
     * @param UnitOfWork      $uow              The unit of work
     * @param UserInterface[] $users            The user entities
     * @param string[]        $existingOrgNames The existing organization names
     */
    protected function generateUsernames(EntityManagerInterface $em, UnitOfWork $uow, array $users, array $existingOrgNames): void
    {
        $emails = [];

        foreach ($users as $user) {
            $email = strtolower($user->getEmail());
            $emails[$email] = strtolower(str_replace(strrchr($email, '@'), '', $email));
        }

        /** @var UserRepositoryInterface $userRepo */
        $userRepo = $em->getRepository(UserInterface::class);
        $existingUserIdentifiers = $userRepo->getExistingUserIdentifiers(array_values($emails));
        $existingUserIdentifiers = array_merge($existingUserIdentifiers, $existingOrgNames);

        foreach ($users as $user) {
            $oldUsername = $user->getUserIdentifier();
            $username = $emails[strtolower($user->getEmail())];

            if (\in_array($username, $existingUserIdentifiers, true)) {
                $username = uniqid($username, false);
            }

            $user->setUserIdentifier($username);
            $uow->propertyChanged($user, 'username', $oldUsername, $user->getUserIdentifier());

            /** @var null|OrganizationInterface $userOrg */
            if (null !== $userOrg = $user->getOrganization()) {
                $oldOrgName = $userOrg->getName();
                $userOrg->setName($username);
                $uow->propertyChanged($user, 'name', $oldOrgName, $userOrg->getName());

                if ($userOrg instanceof LabelableInterface) {
                    $oldOrgLabel = $userOrg->getLabel();
                    $userOrg->setLabel($username);
                    $uow->propertyChanged($user, 'label', $oldOrgLabel, $userOrg->getLabel());
                }

                $meta = $em->getClassMetadata(ClassUtils::getClass($userOrg));
                $uow->recomputeSingleEntityChangeSet($meta, $userOrg);
            }
        }
    }

    /**
     * Create the organization of user.
     *
     * @param UserInterface $entity The user entity
     */
    protected function createOrganization(UserInterface $entity): void
    {
        if ($entity instanceof OrganizationalInterface && null === $entity->getOrganization()) {
            /** @var OrganizationInterface $org */
            $org = $this->objectFactory->create(OrganizationInterface::class);
            $org->setName($entity->getUserIdentifier());
            $org->setUser($entity);

            if ($org instanceof RoleableInterface) {
                foreach ($this->organizationDefaultRoles as $defaultRole) {
                    $org->addRole($defaultRole);
                }
            }

            ListenerUtil::validateEntity($this->validator, $entity);

            if ($entity instanceof OrganizationalOptionalInterface || $entity instanceof OrganizationalRequiredInterface) {
                $entity->setOrganization($org);
            }
        }
    }

    /**
     * Update the user.
     *
     * @param UserInterface $entity The user entity
     * @param UnitOfWork    $uow    The unit of work
     */
    protected function updateUser(UserInterface $entity, UnitOfWork $uow): void
    {
        $changeSet = $uow->getEntityChangeSet($entity);

        if (isset($changeSet['password'])
                && method_exists($entity, 'hasChangePasswordRequired')
                && method_exists($entity, 'setChangePasswordRequired')
                && $entity->hasChangePasswordRequired()) {
            $entity->setChangePasswordRequired(false);
        }
    }

    /**
     * Update the organization of user.
     *
     * @param UserInterface          $entity The user entity
     * @param EntityManagerInterface $em     The entity manager
     * @param UnitOfWork             $uow    The unit of work
     */
    protected function updateOrganization(UserInterface $entity, EntityManagerInterface $em, UnitOfWork $uow): void
    {
        if ($entity instanceof OrganizationalInterface && null !== $entity->getOrganization()) {
            $changeSet = $uow->getEntityChangeSet($entity);

            if (isset($changeSet['username'])) {
                $org = $entity->getOrganization();

                if (null !== $org) {
                    $org->setName($changeSet['username'][1]);

                    if ($uow::STATE_MANAGED === $uow->getEntityState($org)) {
                        $meta = $em->getClassMetadata(ClassUtils::getClass($org));
                        $uow->recomputeSingleEntityChangeSet($meta, $org);
                    }
                }
            }
        }
    }

    /**
     * Validate the deletion of user.
     *
     * @param object                         $entity The entity
     * @param ConstraintViolationInterface[] $errors The errors by reference
     */
    protected function validateDeletions(object $entity, array &$errors): void
    {
        if ($entity instanceof UserInterface && $entity->getUserOrganizations()->count() > 0) {
            $msg = $this->translator->trans('user.not_delete_account', ['{{ username }}' => $entity->getUserIdentifier()], 'validators');
            $errors[] = ListenerUtil::createViolation($msg, $entity);
        }
    }
}
