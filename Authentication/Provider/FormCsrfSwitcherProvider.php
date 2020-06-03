<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\SecurityExtra\Authentication\Provider;

use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Provider for the disable form CSRF security.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class FormCsrfSwitcherProvider implements AuthenticationProviderInterface
{
    public function authenticate(TokenInterface $token): TokenInterface
    {
        return $token;
    }

    public function supports(TokenInterface $token): bool
    {
        return false;
    }
}
