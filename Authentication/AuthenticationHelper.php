<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\SecurityExtra\Authentication;

use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolverInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * Authentication helper.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class AuthenticationHelper
{
    /**
     * @var AuthenticationTrustResolverInterface
     */
    protected $trustResolver;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @param AuthenticationTrustResolverInterface $trustResolver The authentication trust resolver
     * @param TokenStorageInterface                $tokenStorage  The token storage
     */
    public function __construct(
        AuthenticationTrustResolverInterface $trustResolver,
        TokenStorageInterface $tokenStorage
    ) {
        $this->trustResolver = $trustResolver;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Check if the basic auth is used.
     */
    public function isBasicAuthentication(): bool
    {
        $token = $this->tokenStorage->getToken();

        return $token instanceof UsernamePasswordToken && !$this->trustResolver->isAnonymous($token);
    }
}
