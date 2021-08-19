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

use Klipper\Component\Security\Model\OrganizationInterface;
use Klipper\Component\Security\Model\OrganizationUserInterface;
use Klipper\Component\Security\Organizational\OrganizationalContextInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class OrganizationalContextExtension extends AbstractExtension
{
    private ?OrganizationalContextInterface $orgContext;

    public function __construct(
        ?OrganizationalContextInterface $orgContext
    ) {
        $this->orgContext = $orgContext;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('current_organization', [$this, 'getCurrentOrganization']),
            new TwigFunction('current_organization_user', [$this, 'getCurrentOrganizationUser']),
            new TwigFunction('current_organization_name', [$this, 'getCurrentOrganizationName']),
            new TwigFunction('current_organization_unique_name', [$this, 'getCurrentOrganizationUniqueName']),
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

        $portal = $this->orgContext->getCurrentOrganization();

        return null !== $portal ? $portal->getName() : null;
    }
}
