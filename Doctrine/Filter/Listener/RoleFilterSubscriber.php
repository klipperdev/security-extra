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

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Filter\SQLFilter;
use Klipper\Component\DoctrineExtensionsExtra\Filter\Listener\AbstractFilterSubscriber;
use Klipper\Component\SecurityExtra\Doctrine\Filter\RoleFilter;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class RoleFilterSubscriber extends AbstractFilterSubscriber
{
    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var string[]
     */
    protected $excludedRoles;

    /**
     * Constructor.
     *
     * @param EntityManagerInterface $entityManager The entity manager
     * @param RequestStack           $requestStack  The token storage
     * @param string[]               $excludedRoles The excluded roles
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        RequestStack $requestStack,
        array $excludedRoles = []
    ) {
        parent::__construct($entityManager);

        $this->requestStack = $requestStack;
        $this->excludedRoles = $excludedRoles;
    }

    /**
     * {@inheritdoc}
     */
    protected function supports(): string
    {
        return RoleFilter::class;
    }

    /**
     * {@inheritdoc}
     */
    protected function injectParameters(SQLFilter $filter): void
    {
        $filter->setParameter('excluded_roles', $this->excludedRoles, 'array');
        $filter->setParameter('is_admin_section', $this->isAdminSection(), 'boolean');
    }

    /**
     * Check if in admin section.
     */
    private function isAdminSection(): bool
    {
        $request = $this->requestStack->getCurrentRequest();

        return null === $request || (null !== $request && $request->attributes->get('_admin_section', false));
    }
}
