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

use Doctrine\Persistence\ManagerRegistry;
use Klipper\Component\DoctrineExtra\Util\ManagerUtils;
use Klipper\Component\Object\Util\ClassUtil;
use Klipper\Component\Security\Model\OrganizationUserInterface;
use Klipper\Component\Security\Model\SharingInterface;
use Klipper\Component\Security\Model\Traits\OnlyOrganizationInterface;
use Klipper\Component\Security\Model\Traits\OrganizationalInterface;
use Klipper\Component\Security\Model\Traits\RoleableInterface;
use Klipper\Component\Security\Organizational\OrganizationalContextInterface;
use Klipper\Component\Security\Permission\PermVote;
use Klipper\Component\SecurityExtra\Exception\UnexpectedTypeException;
use Klipper\Component\SecurityExtra\Model\Traits\OwnerableInterface;
use Klipper\Component\SecurityExtra\Model\Traits\OwnerableOptionalInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Sharing validator.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class SharingValidator extends ConstraintValidator
{
    protected AuthorizationCheckerInterface $authChecker;

    protected TokenStorageInterface $tokenStorage;

    protected ManagerRegistry $registry;

    protected ?OrganizationalContextInterface $orgContext;

    protected bool $superAdmin;

    protected string $permissionName;

    /**
     * @param AuthorizationCheckerInterface       $authChecker    The authorization checker
     * @param TokenStorageInterface               $tokenStorage   The token storage
     * @param ManagerRegistry                     $registry       The doctrine
     * @param null|OrganizationalContextInterface $orgContext     The organizational context
     * @param bool                                $superAdmin     Check if the super admin user can skip this validator
     * @param string                              $permissionName The name of permission to check the authorization to manage the sharings
     */
    public function __construct(
        AuthorizationCheckerInterface $authChecker,
        TokenStorageInterface $tokenStorage,
        ManagerRegistry $registry,
        ?OrganizationalContextInterface $orgContext = null,
        bool $superAdmin = true,
        string $permissionName = 'manage-sharings'
    ) {
        $this->authChecker = $authChecker;
        $this->tokenStorage = $tokenStorage;
        $this->registry = $registry;
        $this->orgContext = $orgContext;
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
        if (!$constraint instanceof Sharing) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\Sharing');
        }

        if (null !== $value && !$value instanceof SharingInterface) {
            throw new UnexpectedTypeException($value, SharingInterface::class);
        }

        $token = $this->tokenStorage->getToken();
        $user = $token ? $token->getUser() : null;

        // console or super admin user or empty fields
        if (null === $token
                || null === $value->getSubjectClass()
                || null === $value->getSubjectId()
                || null === $value->getIdentityClass()
                || null === $value->getIdentityName()
                || ($this->superAdmin && $user instanceof RoleableInterface && $user->hasRole('ROLE_SUPER_ADMIN'))) {
            return;
        }

        $manager = ManagerUtils::getRequiredManager($this->registry, $value->getSubjectClass());
        $entity = $manager->find($value->getSubjectClass(), $value->getSubjectId());

        if ($this->validateOwner($entity) || $this->isOrganizationAdmin($value)) {
            return;
        }

        if (!$this->validateIdentity($value)
                || !$this->authChecker->isGranted(new PermVote($this->permissionName), $value->getSubjectClass())) {
            $this->context->buildViolation($constraint->message)
                ->addViolation()
            ;
        }
    }

    /**
     * Check if the subject is organizational and if the current organization user is an admin.
     *
     * @param SharingInterface $sharing The sharing instance
     */
    public function isOrganizationAdmin(SharingInterface $sharing): bool
    {
        if (null === $this->orgContext) {
            return false;
        }

        $orgUser = $this->orgContext->getCurrentOrganizationUser();

        return ClassUtil::isInstanceOf($sharing->getSubjectClass(), OrganizationalInterface::class)
            && $orgUser instanceof OrganizationUserInterface
            && $orgUser instanceof RoleableInterface
            && $orgUser->hasRole('ROLE_ADMIN');
    }

    /**
     * Validate the identity.
     *
     * @param SharingInterface $sharing The sharing instance
     */
    protected function validateIdentity(SharingInterface $sharing): bool
    {
        if ($this->isRequiredOrganizationContext($sharing->getIdentityClass())) {
            if (null !== $this->orgContext
                    && null !== $this->orgContext->getCurrentOrganization()
                    && !$this->orgContext->getCurrentOrganization()->isUserOrganization()) {
                $suffix = '__'.strtoupper($this->orgContext->getCurrentOrganization()->getName());
                $name = $sharing->getIdentityName();

                return strrpos($name, $suffix) === (\strlen($name) - \strlen($suffix));
            }

            return false;
        }

        return true;
    }

    /**
     * Check if the identity required the organization suffix.
     *
     * @param string $identityClass The identity classname
     */
    protected function isRequiredOrganizationContext(string $identityClass): bool
    {
        return ClassUtil::isInstanceOf($identityClass, OnlyOrganizationInterface::class);
    }

    /**
     * Owner validation.
     *
     * @param object|OwnerableInterface|OwnerableOptionalInterface $subject The subject instance
     */
    protected function validateOwner($subject): bool
    {
        $token = $this->tokenStorage->getToken();
        $user = $token ? $token->getUser() : null;

        return ($subject instanceof OwnerableInterface || $subject instanceof OwnerableOptionalInterface)
            && null !== $user
            && null !== $subject->getOwner()
            && $subject->getOwner()->getUserIdentifier() === $user->getUserIdentifier();
    }
}
