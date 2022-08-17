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
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Klipper\Component\DoctrineExtensionsExtra\Util\ListenerUtil;
use Klipper\Component\Security\Model\Traits\OnlyOrganizationInterface;
use Klipper\Component\Security\Model\Traits\OrganizationalInterface;
use Klipper\Component\Security\Model\Traits\OrganizationalOptionalInterface;
use Klipper\Component\Security\Model\Traits\OrganizationalRequiredInterface;
use Klipper\Component\Security\Model\UserInterface;
use Klipper\Component\Security\Organizational\OrganizationalContextInterface;
use Klipper\Component\Security\OrganizationalTypes;
use Klipper\Contracts\Model\IdInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class OrganizationalSubscriber implements EventSubscriber
{
    protected OrganizationalContextInterface $context;

    protected TranslatorInterface $translator;

    /**
     * @param OrganizationalContextInterface $context    The security organizational context
     * @param TranslatorInterface            $translator The translator
     */
    public function __construct(
        OrganizationalContextInterface $context,
        TranslatorInterface $translator
    ) {
        $this->context = $context;
        $this->translator = $translator;
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::prePersist,
            Events::preUpdate,
            Events::onFlush,
        ];
    }

    public function prePersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        $currentOrg = $this->context->getCurrentOrganization();

        if ($entity instanceof OrganizationalRequiredInterface
                || $this->isInjectableOrgInOptionalEntity($entity)) {
            /** @var OrganizationalOptionalInterface|OrganizationalRequiredInterface $entity */
            if (null !== $currentOrg && null === $entity->getOrganization()) {
                if (!$entity instanceof UserInterface
                        || ($entity instanceof UserInterface && $currentOrg->isUserOrganization())) {
                    $entity->setOrganization($currentOrg);
                }
            }
        }
    }

    public function preUpdate(LifecycleEventArgs $args): void
    {
        $this->prePersist($args);
    }

    /**
     * On flush action.
     *
     * @param OnFlushEventArgs $args The event
     */
    public function onFlush(OnFlushEventArgs $args): void
    {
        $em = $args->getObjectManager();
        $uow = $em->getUnitOfWork();
        $errors = [];

        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            $this->validateOrganizationalEntity($entity, $errors);
        }

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            $this->validateOrganizationalEntity($entity, $errors);
        }

        if (\count($errors) > 0) {
            ListenerUtil::thrownErrors($errors);
        }
    }

    /**
     * Validate the organizational entity.
     *
     * @param object                         $entity The entity
     * @param ConstraintViolationInterface[] $errors The errors by reference
     */
    protected function validateOrganizationalEntity($entity, array &$errors): void
    {
        /** @var OrganizationalInterface $entity */
        if ($entity instanceof OrganizationalInterface) {
            if ($entity instanceof OrganizationalRequiredInterface && !$entity instanceof UserInterface
                    && null === $entity->getOrganization()) {
                $msg = $this->translator->trans('This value should not be blank.', [], 'validators');
                $errors[] = ListenerUtil::createViolation($msg, $entity, 'organization');
            }

            if ($entity instanceof OnlyOrganizationInterface && null !== $entity->getOrganization()
                    && $entity->getOrganization()->isUserOrganization()) {
                $params = ['%name%' => ListenerUtil::getEntityName($entity)];
                $msg = $this->translator->trans('organizational.only_used_in_organization', $params, 'validators');
                $errors[] = ListenerUtil::createViolation($msg, $entity, 'organization');
            }
        }
    }

    /**
     * Check if the entity is a organizational optional entity and the context allow to
     * set the current organization in entity.
     *
     * @param mixed $entity The entity
     */
    protected function isInjectableOrgInOptionalEntity($entity): bool
    {
        $isUpdate = $entity instanceof IdInterface && null !== $entity->getId();

        return $entity instanceof OrganizationalOptionalInterface
            && !$isUpdate
            && !$this->context->isOptionalFilterType(OrganizationalTypes::OPTIONAL_FILTER_WITHOUT_ORG);
    }
}
