<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\SecurityExtra\Doctrine\Validator\Constraints;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Klipper\Component\DoctrineExtensions\Util\SqlFilterUtil;
use Klipper\Component\DoctrineExtra\Util\ManagerUtils;
use Klipper\Component\Security\Model\OrganizationInterface;
use Klipper\Component\Security\Model\UserInterface;
use Klipper\Component\SecurityExtra\Entity\Repository\OrganizationRepositoryInterface;
use Klipper\Component\SecurityExtra\Exception\InvalidArgumentException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class UsernameSameOrgNameValidator extends ConstraintValidator
{
    protected string $orgClass;

    protected ObjectManager $om;

    protected OrganizationRepositoryInterface $repo;

    /**
     * @param ManagerRegistry $registry          The doctrine registry
     * @param string          $organizationClass The class name of organization entity
     */
    public function __construct(ManagerRegistry $registry, string $organizationClass = OrganizationInterface::class)
    {
        $this->orgClass = $organizationClass;
        $this->om = ManagerUtils::getRequiredManager($registry, $organizationClass);
        $this->repo = $this->om->getRepository($organizationClass);

        if (!$this->repo instanceof OrganizationRepositoryInterface) {
            throw new InvalidArgumentException(sprintf('The repository for the "%s" class must be an instance of "%s"', $organizationClass, OrganizationRepositoryInterface::class));
        }
    }

    /**
     * Checks if the passed value is valid.
     *
     * @param mixed|UserInterface            $value      The value that should be validated
     * @param Constraint|UsernameSameOrgName $constraint The constraint for the validation
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof UserInterface) {
            return;
        }

        $filters = SqlFilterUtil::findFilters($this->om, (array) $constraint->filters, $constraint->allFilters);

        SqlFilterUtil::disableFilters($this->om, $filters);
        $res = $this->repo->findByInsensitive(['name' => $value->getUserIdentifier(), 'user' => null], null, 1);
        SqlFilterUtil::enableFilters($this->om, $filters);

        if (\count($res) > 0) {
            $this->context->buildViolation($constraint->message)
                ->atPath('username')
                ->setInvalidValue($value->getUserIdentifier())
                ->addViolation()
            ;
        }
    }
}
