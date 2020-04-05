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
use Klipper\Component\Security\Model\OrganizationInterface;
use Klipper\Component\Security\Model\OrganizationUserInterface;
use Klipper\Component\Security\Model\RoleInterface;
use Klipper\Component\Security\Model\Traits\RoleableInterface;
use Klipper\Component\Security\Model\UserInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class OrganizationSubscriber implements EventSubscriber
{
    /**
     * @var ContainerInterface
     */
    public $container;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var string
     */
    protected $orgClass;

    /**
     * @var string
     */
    protected $settingClass;

    /**
     * @var null|DomainInterface
     */
    protected $domainRole;

    /**
     * @var null|DomainInterface
     */
    protected $domainOrgUser;

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * Constructor.
     *
     * @param TokenStorageInterface $tokenStorage The token storage
     * @param TranslatorInterface   $translator   The translator
     * @param string                $orgClass     The organization class name
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        TranslatorInterface $translator,
        $orgClass = OrganizationInterface::class
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->translator = $translator;
        $this->orgClass = $orgClass;
    }

    /**
     * {@inheritdoc}
     */
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
                if ($entity->getUser()->getUsername() !== $entity->getName()) {
                    $message = 'The field "name" of the user organization does not be edited';
                    ListenerUtil::thrownError($message, $entity, 'name');
                } elseif (!isset($changeSet['label'])) {
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

        $domain = $this->getDomainOrgUser();
        /** @var OrganizationUserInterface $orgUser */
        $orgUser = $domain->newInstance();
        $orgUser->setOrganization($org);
        $orgUser->setUser($this->getTokenUser());

        if ($orgUser instanceof RoleableInterface) {
            $orgUser->addRole('ROLE_ADMIN');
        }

        ListenerUtil::validateEntity($this->getValidator(), $orgUser);
        $org->addOrganizationUser($orgUser);
    }

    protected function getTokenUser(): ?UserInterface
    {
        $token = $this->tokenStorage->getToken();
        $user = null !== $token ? $token->getUser() : null;

        return $user instanceof UserInterface ? $user : null;
    }

    /**
     * Get the role domain.
     */
    protected function getDomainRole(): DomainInterface
    {
        $this->init();

        return $this->domainRole;
    }

    /**
     * Get the organization user domain.
     */
    protected function getDomainOrgUser(): DomainInterface
    {
        $this->init();

        return $this->domainOrgUser;
    }

    /**
     * Get the validator.
     */
    protected function getValidator(): ValidatorInterface
    {
        $this->init();

        return $this->validator;
    }

    /**
     * Init the domains.
     */
    protected function init(): void
    {
        if (null !== $this->container) {
            $this->domainRole = $this->container->get('klipper_resource.domain_manager')->get(RoleInterface::class);
            $this->domainOrgUser = $this->container->get('klipper_resource.domain_manager')->get(OrganizationUserInterface::class);
            $this->validator = $this->container->get('validator');
            $this->container = null;
        }
    }

    /**
     * Persist the entity.
     *
     * @param EntityManagerInterface $em     The entity manager
     * @param object                 $entity The entity instance
     */
    protected function persistEntity(EntityManagerInterface $em, object $entity): void
    {
        ListenerUtil::validateEntity($this->getValidator(), $entity);

        $em->persist($entity);
    }
}
