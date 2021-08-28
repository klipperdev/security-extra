<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\SecurityExtra\Twig\Extension;

use Doctrine\ORM\EntityManagerInterface;
use Klipper\Component\DoctrineExtensions\Util\SqlFilterUtil;
use Klipper\Component\Security\Model\OrganizationInterface;
use Klipper\Component\Security\Model\OrganizationUserInterface;
use Klipper\Component\Security\Organizational\OrganizationalContextInterface;
use Klipper\Component\SecurityExtra\Doctrine\Filter\OrganizationUserFilter;
use Klipper\Component\SecurityExtra\Entity\Repository\Traits\OrganizationUserRepositoryTrait;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class OrganizationalContextExtension extends AbstractExtension
{
    /**
     * @var null|OrganizationUserInterface[]
     */
    protected ?array $cacheOrganizations = null;
    private EntityManagerInterface $em;

    private ?OrganizationalContextInterface $orgContext;

    public function __construct(
        EntityManagerInterface $em,
        ?OrganizationalContextInterface $orgContext
    ) {
        $this->em = $em;
        $this->orgContext = $orgContext;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('current_organization', [$this, 'getCurrentOrganization']),
            new TwigFunction('current_organization_user', [$this, 'getCurrentOrganizationUser']),
            new TwigFunction('current_organization_name', [$this, 'getCurrentOrganizationName']),
            new TwigFunction('current_organization_unique_name', [$this, 'getCurrentOrganizationUniqueName']),
            new TwigFunction('available_organizations', [$this, 'getAvailableOrganizations']),
        ];
    }

    public function getCurrentOrganization(): ?OrganizationInterface
    {
        return null !== $this->orgContext ? $this->orgContext->getCurrentOrganization() : null;
    }

    public function getCurrentOrganizationUser(): ?OrganizationUserInterface
    {
        return null !== $this->orgContext ? $this->orgContext->getCurrentOrganizationUser() : null;
    }

    public function getCurrentOrganizationName(): ?string
    {
        if (null === $this->orgContext) {
            return null;
        }

        $org = $this->orgContext->getCurrentOrganization();

        if (\is_object($org) && method_exists($org, 'getLabel')) {
            return $org->getLabel();
        }

        return null !== $org ? $org->getName() : null;
    }

    public function getCurrentOrganizationUniqueName(): ?string
    {
        if (null === $this->orgContext) {
            return null;
        }

        $org = $this->orgContext->getCurrentOrganization();

        return null !== $org ? $org->getName() : null;
    }

    /**
     * Get the organization users of current user.
     *
     * @return OrganizationUserInterface[]
     */
    public function getAvailableOrganizations(): array
    {
        if (null === $this->cacheOrganizations) {
            $this->cacheOrganizations = [];
            $org = $this->orgContext->getCurrentOrganization();

            if (null !== $org) {
                $isEnabled = SqlFilterUtil::isEnabled($this->em, 'organization_user');

                if (!$isEnabled) {
                    SqlFilterUtil::enableFilters($this->em, ['organization_user']);
                }

                /** @var OrganizationUserFilter $orgUserFilter */
                $orgUserFilter = $this->em->getFilters()->getFilter('organization_user');
                $orgUserFilter->setCurrentOrganizations(true);

                /** @var OrganizationUserRepositoryTrait $repo */
                $repo = $this->em->getRepository(OrganizationUserInterface::class);
                $this->cacheOrganizations = $repo->getOrderedOrganizations();

                $orgUserFilter->setCurrentOrganizations(false);

                if (!$isEnabled) {
                    SqlFilterUtil::disableFilters($this->em, ['organization_user']);
                }
            }
        }

        return $this->cacheOrganizations;
    }
}
