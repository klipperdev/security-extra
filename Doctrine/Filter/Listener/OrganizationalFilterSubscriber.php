<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\SecurityExtra\Doctrine\Filter\Listener;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Filter\SQLFilter;
use Klipper\Component\DoctrineExtensionsExtra\Filter\Listener\AbstractFilterSubscriber;
use Klipper\Component\Security\Doctrine\DoctrineUtils;
use Klipper\Component\Security\Model\OrganizationInterface;
use Klipper\Component\Security\Model\UserInterface;
use Klipper\Component\Security\Organizational\OrganizationalContextInterface;
use Klipper\Component\SecurityExtra\Doctrine\Filter\OrganizationalFilter;
use Klipper\Component\SecurityExtra\Organizational\OrganizationalFilterFactoryInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class OrganizationalFilterSubscriber extends AbstractFilterSubscriber
{
    /**
     * @var OrganizationalContextInterface
     */
    private $orgContext;

    /**
     * @var null|OrganizationalFilterFactoryInterface
     */
    private $factory;

    /**
     * @var string[]
     */
    private $excludedClasses = [];

    /**
     * @var string[]
     */
    private $userExcludedOrgsClasses = [];

    /**
     * @var string[]
     */
    private $optionalFilterAllClasses = [];

    /**
     * @var bool
     */
    private $initialized = false;

    /**
     * Constructor.
     *
     * @param EntityManagerInterface                    $entityManager The entity manager
     * @param OrganizationalContextInterface            $orgContext    The security organizational context
     * @param null|OrganizationalFilterFactoryInterface $factory       The organizational filter factory
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        OrganizationalContextInterface $orgContext,
        ?OrganizationalFilterFactoryInterface $factory
    ) {
        parent::__construct($entityManager);

        $this->orgContext = $orgContext;
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    protected function supports(): string
    {
        return OrganizationalFilter::class;
    }

    /**
     * {@inheritdoc}
     */
    protected function injectParameters(SQLFilter $filter): void
    {
        $this->init();
        $org = $this->orgContext->getCurrentOrganization();
        $orgId = null !== $org
            ? $org->getId()
            : DoctrineUtils::getMockZeroId($this->entityManager->getClassMetadata(OrganizationInterface::class));
        $orgUserId = null !== $org && null !== $org->getUser()
            ? $org->getUser()->getId()
            : DoctrineUtils::getMockZeroId($this->entityManager->getClassMetadata(UserInterface::class));

        $filter->setParameter('excluded_entities', [], 'array');
        $filter->setParameter('excluded_classes', $this->excludedClasses, 'array');
        $filter->setParameter('optional_filter_all_classes', $this->optionalFilterAllClasses, 'array');
        $filter->setParameter('user_excluded_orgs_classes', $this->userExcludedOrgsClasses, 'array');
        $filter->setParameter('has_organization', null !== $org, 'boolean');
        $filter->setParameter('is_user_organization', null !== $org && $org->isUserOrganization(), 'boolean');
        $filter->setParameter('organization_id', $orgId, \is_string($orgId) ? Type::GUID : null);
        $filter->setParameter('organization_user_id', $orgUserId, \is_string($orgUserId) ? Type::GUID : null);
        $filter->setParameter('context_optional_filter_type', $this->orgContext->getOptionalFilterType());
    }

    /**
     * Initialize the configurations.
     */
    private function init(): void
    {
        if (!$this->initialized) {
            $this->initialized = true;

            if (null !== $this->factory) {
                $this->excludedClasses = $this->factory->createExcludedClasses()->all();
                $this->userExcludedOrgsClasses = $this->factory->createUserExcludedOrgsClasses()->all();
                $this->optionalFilterAllClasses = $this->factory->createOptionalAllFilterClasses()->all();
            }
        }
    }
}
