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
use Klipper\Component\Security\Model\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
abstract class AbstractTokenUserFilterSubscriber extends AbstractFilterSubscriber
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * Constructor.
     *
     * @param EntityManagerInterface $entityManager The entity manager
     * @param TokenStorageInterface  $tokenStorage  The token storage
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage
    ) {
        parent::__construct($entityManager);

        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    protected function injectParameters(SQLFilter $filter): void
    {
        $filter->setParameter('user_id', $this->getTokenUserId());
    }

    /**
     * Get the user id in token.
     *
     * @return null|int|string
     */
    protected function getTokenUserId()
    {
        $token = $this->tokenStorage->getToken();

        return null !== $token && $token->getUser() instanceof UserInterface
            ? $token->getUser()->getId()
            : null;
    }
}
