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
use Doctrine\ORM\Events;
use Klipper\Component\DoctrineExtensionsExtra\Util\ListenerUtil;
use Klipper\Component\Model\Traits\LabelableInterface;
use Klipper\Component\Resource\Domain\DomainInterface;
use Klipper\Component\Resource\Object\ObjectFactoryInterface;
use Klipper\Component\Security\Model\OrganizationInterface;
use Klipper\Component\Security\Model\OrganizationUserInterface;
use Klipper\Component\Security\Model\Traits\RoleableInterface;
use Klipper\Component\Security\Model\UserInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class OrganizationSubscriber implements EventSubscriber
{
    protected TokenStorageInterface $tokenStorage;

    protected TranslatorInterface $translator;

    protected ValidatorInterface $validator;

    protected ObjectFactoryInterface $objectFactory;

    protected ?DomainInterface $domainRole = null;

    protected ?DomainInterface $domainOrgUser = null;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        TranslatorInterface $translator,
        ValidatorInterface $validator,
        ObjectFactoryInterface $objectFactory
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->translator = $translator;
        $this->validator = $validator;
        $this->objectFactory = $objectFactory;
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::prePersist,
            Events::preUpdate,
        ];
    }

    /**
     * On pre persist action.
     */
    public function prePersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getEntity();

        if ($entity instanceof OrganizationInterface) {
            $this->updateNameLabelField($entity);

            if (!$entity->isUserOrganization()) {
                $this->createOrganizationUser($entity);
            }
        }
    }

    /**
     * On pre update action.
     */
    public function preUpdate(LifecycleEventArgs $args): void
    {
        $entity = $args->getEntity();
        $uow = $args->getEntityManager()->getUnitOfWork();

        if ($entity instanceof OrganizationInterface) {
            $this->updateNameLabelField($entity);

            $changeSet = $uow->getEntityChangeSet($entity);

            if (isset($changeSet['name']) && null !== $entity->getUser()) {
                if ($entity->getUser()->getUserIdentifier() !== $entity->getName()) {
                    $message = 'The field "name" of the user organization does not be edited';
                    ListenerUtil::thrownError($message, $entity, 'name');
                } elseif (method_exists($entity, 'setLabel') && !isset($changeSet['label'])) {
                    $entity->setLabel($entity->getName());
                }
            }
        }
    }

    /**
     * Update the name and label fields of org entity.
     *
     * @param OrganizationInterface $entity The org entity
     *
     * @throws
     */
    protected function updateNameLabelField(OrganizationInterface $entity): void
    {
        if (null === $entity->getName()) {
            $entity->setName(Uuid::uuid4()->toString());
        }

        if ($entity instanceof LabelableInterface && null === $entity->getLabel()) {
            $entity->setLabel($entity->getName());
        }
    }

    /**
     * Create the organization user of organization.
     *
     * @param OrganizationInterface $org The organization
     */
    protected function createOrganizationUser(OrganizationInterface $org): void
    {
        $user = $this->getTokenUser();

        if (null === $user || $org->getOrganizationUsers()->count() > 0) {
            return;
        }

        /** @var OrganizationUserInterface $orgUser */
        $orgUser = $this->objectFactory->create(OrganizationUserInterface::class);
        $orgUser->setOrganization($org);
        $orgUser->setUser($this->getTokenUser());

        if ($orgUser instanceof RoleableInterface) {
            $orgUser->addRole('ROLE_ADMIN');
        }

        ListenerUtil::validateEntity($this->validator, $orgUser);
        $org->addOrganizationUser($orgUser);
    }

    protected function getTokenUser(): ?UserInterface
    {
        $token = $this->tokenStorage->getToken();
        $user = null !== $token ? $token->getUser() : null;

        return $user instanceof UserInterface ? $user : null;
    }

    /**
     * Persist the entity.
     *
     * @param EntityManagerInterface $em     The entity manager
     * @param object                 $entity The entity instance
     */
    protected function persistEntity(EntityManagerInterface $em, object $entity): void
    {
        ListenerUtil::validateEntity($this->validator, $entity);

        $em->persist($entity);
    }
}
