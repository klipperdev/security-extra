<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\SecurityExtra\Helper;

use Doctrine\Persistence\ManagerRegistry;
use Klipper\Component\DoctrineExtra\Util\RepositoryUtils;
use Klipper\Component\Security\Model\OrganizationUserInterface;
use Klipper\Component\Security\Model\UserInterface;
use Klipper\Component\Security\Organizational\OrganizationalContextInterface;
use Klipper\Component\Security\Permission\PermissionManagerInterface;
use Klipper\Component\SecurityExtra\Entity\Repository\OrganizationUserRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Helper service for security organizational context.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class OrganizationalContextHelper
{
    /**
     * @var string
     */
    public const TYPE_USER = 'user';

    /**
     * @var string
     */
    public const TYPE_ORGANIZATION = 'org';

    /**
     * @var string
     */
    public const TYPE_BOTH = 'both';

    protected TokenStorageInterface $tokenStorage;

    protected PermissionManagerInterface $permissionManager;

    protected OrganizationUserRepositoryInterface $orgUserRepository;

    protected OrganizationalContextInterface $context;

    protected ?string $routeParameterName = null;

    /**
     * @param TokenStorageInterface          $tokenStorage      The token storage
     * @param PermissionManagerInterface     $permissionManager The permission manager
     * @param ManagerRegistry                $doctrine          The doctrine
     * @param OrganizationalContextInterface $context           The security organizational context
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        PermissionManagerInterface $permissionManager,
        ManagerRegistry $doctrine,
        OrganizationalContextInterface $context
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->permissionManager = $permissionManager;
        $this->orgUserRepository = RepositoryUtils::getRepository($doctrine, OrganizationUserInterface::class, OrganizationUserRepositoryInterface::class);
        $this->context = $context;
    }

    /**
     * Set the route parameter name.
     *
     * @param string $name The route parameter name
     */
    public function setRouteParameterName(string $name): void
    {
        $this->routeParameterName = $name;
    }

    /**
     * Inject the organization context.
     *
     * @param Request $request The request
     */
    public function injectContext(Request $request): void
    {
        if (null === $this->routeParameterName) {
            return;
        }

        $attr = $request->attributes;
        $org = $attr->get($this->routeParameterName.'_name');
        $type = $attr->get($this->routeParameterName.'_type', static::TYPE_BOTH);
        $organizational = $attr->get($this->routeParameterName, $org);
        $org = \in_array($organizational, [false, null], true) ? 'user' : $org;

        if (null === $org) {
            $routeParams = $attr->get('_route_params', []);
            $org = $routeParams[$organizational] ?? false;
        }

        $this->validateOrganizationWithType($org, $type);

        if ('user' === $org) {
            if (null !== $this->tokenStorage->getToken()) {
                $this->context->setCurrentOrganization($organizational);
            }

            return;
        }

        if (false !== $org && null !== $this->tokenStorage->getToken()) {
            $this->setCurrentOrganizationUser($org);
        }

        if (0 !== strpos(($attr->get('_route', '')), '_')
            && null !== $this->tokenStorage->getToken()
            && null === $this->context->getCurrentOrganizationUser()
        ) {
            throw new NotFoundHttpException();
        }
    }

    /**
     * Get the current organization user.
     *
     * @param string $organizationName The current organization name
     */
    public function getCurrentOrganizationUser(?string $organizationName): ?OrganizationUserInterface
    {
        $orgUser = null;

        if (null !== $organizationName) {
            $pmEnabled = $this->permissionManager->isEnabled();
            $this->permissionManager->setEnabled(false);
            $orgUser = $this->orgUserRepository->findCurrentOrganizationUserByOrganizationName(
                $organizationName,
                $this->getUser()
            );
            $this->permissionManager->setEnabled($pmEnabled);
        }

        return $orgUser;
    }

    /**
     * Set the current organization user defined by organization name,
     * in security organizational context.
     *
     * @param string $organizationName The current organization name
     */
    public function setCurrentOrganizationUser(string $organizationName): void
    {
        $this->context->setCurrentOrganizationUser($this->getCurrentOrganizationUser($organizationName));
    }

    /**
     * Get a user from the Security Token Storage.
     *
     * @see TokenInterface::getUser()
     */
    protected function getUser(): ?UserInterface
    {
        $token = $this->tokenStorage->getToken();
        $user = null;

        if (null !== $token) {
            $tUser = $token->getUser();
            $user = $tUser instanceof UserInterface ? $tUser : null;
        }

        return $user;
    }

    /**
     * Validate the organization type.
     *
     * @param null|false|string $organization The organization name
     * @param string            $type         The context type
     */
    protected function validateOrganizationWithType($organization, string $type): void
    {
        if ('user' === $organization) {
            if (!\in_array($type, [static::TYPE_USER, static::TYPE_BOTH], true)) {
                throw new NotFoundHttpException();
            }
        } elseif (\is_string($organization) && !empty($organization)) {
            if (!\in_array($type, [static::TYPE_ORGANIZATION, static::TYPE_BOTH], true)) {
                throw new NotFoundHttpException();
            }
        }
    }
}
