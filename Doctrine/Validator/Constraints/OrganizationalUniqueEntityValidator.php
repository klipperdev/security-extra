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

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Persistence\ManagerRegistry;
use Klipper\Component\DoctrineExtensions\Validator\Constraints\UniqueEntityValidator;
use Klipper\Component\Security\Organizational\OrganizationalContextInterface;
use Symfony\Component\Validator\Constraint;

/**
 * Constraint for the Unique Entity validator with disable sql filter option.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class OrganizationalUniqueEntityValidator extends UniqueEntityValidator
{
    /**
     * @var OrganizationalContextInterface
     */
    protected $orgContext;

    /**
     * Constructor.
     */
    public function __construct(ManagerRegistry $registry, OrganizationalContextInterface $orgContext)
    {
        parent::__construct($registry);

        $this->orgContext = $orgContext;
    }

    /**
     * {@inheritdoc}
     */
    protected function getCriteria($entity, Constraint $constraint, ObjectManager $em): array
    {
        $criteria = parent::getCriteria($entity, $constraint, $em);

        if (\array_key_exists('organization', $criteria)
                && null === $criteria['organization']
                && null !== $this->orgContext->getCurrentOrganization()) {
            $criteria['organization'] = $this->orgContext->getCurrentOrganization()->getId();
        }

        return $criteria;
    }
}
