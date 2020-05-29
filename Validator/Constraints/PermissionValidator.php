<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\SecurityExtra\Validator\Constraints;

use Klipper\Component\Security\Model\PermissionInterface;
use Klipper\Component\Security\Model\Traits\RoleableInterface;
use Klipper\Component\SecurityExtra\Exception\UnexpectedTypeException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Permission validator.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class PermissionValidator extends ConstraintValidator
{
    /**
     * @var AuthorizationCheckerInterface
     */
    protected $authChecker;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var bool
     */
    protected $superAdmin;

    /**
     * @var string
     */
    protected $permissionName;

    /**
     * Constructor.
     *
     * @param AuthorizationCheckerInterface $authChecker    The authorization checker
     * @param TokenStorageInterface         $tokenStorage   The token storage
     * @param bool                          $superAdmin     Check if the super admin user can skip this validator
     * @param string                        $permissionName The name of permission to check the authorization to manage the permissions
     */
    public function __construct(
        AuthorizationCheckerInterface $authChecker,
        TokenStorageInterface $tokenStorage,
        bool $superAdmin = true,
        string $permissionName = 'manage-permissions'
    ) {
        $this->authChecker = $authChecker;
        $this->tokenStorage = $tokenStorage;
        $this->superAdmin = $superAdmin;
        $this->permissionName = $permissionName;
    }

    /**
     * Checks if the passed value is valid.
     *
     * @param mixed      $value      The value that should be validated
     * @param Constraint $constraint The constraint for the validation
     *
     * @throws UnexpectedTypeException
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof Permission) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\Permission');
        }

        if (null !== $value && !$value instanceof PermissionInterface) {
            throw new UnexpectedTypeException($value, PermissionInterface::class);
        }

        $token = $this->tokenStorage->getToken();
        $user = $token ? $token->getUser() : null;

        // console or super admin user
        if (null === $token
                || ($this->superAdmin && $user instanceof RoleableInterface && $user->hasRole('ROLE_SUPER_ADMIN'))) {
            return;
        }

        if (!$this->authChecker->isGranted('perm:'.$this->permissionName)) {
            $this->context->buildViolation($constraint->message)
                ->addViolation()
            ;
        }
    }
}
