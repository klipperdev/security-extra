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
use Klipper\Component\Security\Doctrine\DoctrineUtils;
use Klipper\Component\Security\Model\OrganizationInterface;
use Klipper\Component\Security\Model\UserInterface;
use Klipper\Component\Security\Organizational\OrganizationalContextInterface;
use Klipper\Component\SecurityExtra\Doctrine\Filter\OrganizationUserFilter;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class OrganizationUserFilterSubscriber extends AbstractTokenUserFilterSubscriber
{
    private OrganizationalContextInterface $orgContext;

    /**
     * @param EntityManagerInterface         $entityManager The entity manager
     * @param TokenStorageInterface          $tokenStorage  The token storage
     * @param OrganizationalContextInterface $orgContext    The organizational context
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage,
        OrganizationalContextInterface $orgContext
    ) {
        parent::__construct($entityManager, $tokenStorage);

        $this->orgContext = $orgContext;
    }

    protected function supports(): string
    {
        return OrganizationUserFilter::class;
    }

    protected function injectParameters(SQLFilter $filter): void
    {
        parent::injectParameters($filter);

        $org = $this->orgContext->getCurrentOrganization();
        $orgId = null !== $org ? $org->getId() : DoctrineUtils::getMockZeroId($this->entityManager->getClassMetadata(OrganizationInterface::class));

        $filter->setParameter('is_current_organizations', false, 'boolean');
        $filter->setParameter('has_organization', null !== $org, 'boolean');
        $filter->setParameter('is_user_organization', null !== $org && $org->isUserOrganization(), 'boolean');
        $filter->setParameter('organization_id', $orgId, \is_string($orgId) ? Type::GUID : null);
    }

    protected function getTokenUserId()
    {
        $userId = parent::getTokenUserId();

        return $userId ?? DoctrineUtils::getMockZeroId($this->entityManager->getClassMetadata(UserInterface::class));
    }
}
