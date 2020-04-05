<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\SecurityExtra\Factory;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Provider for the form permission checker.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class FormPermissionCheckerFactory implements SecurityFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function create(ContainerBuilder $container, $id, $config, $userProvider, $defaultEntryPoint): array
    {
        $providerId = 'klipper_security_extra.authentication.provider.permission_checker.'.$id;
        $container
            ->setDefinition($providerId, new ChildDefinition('klipper_security_extra.permission_checker.authentication.provider'))
        ;

        $listenerId = 'klipper_security_extra.authentication.listener.permission_checker.'.$id;
        $container
            ->setDefinition($listenerId, new ChildDefinition('klipper_security_extra.permission_checker.authentication.listener'))
            ->replaceArgument(1, $config)
        ;

        return [$providerId, $listenerId, $defaultEntryPoint];
    }

    /**
     * {@inheritdoc}
     */
    public function getPosition(): string
    {
        return 'http';
    }

    /**
     * {@inheritdoc}
     */
    public function getKey(): string
    {
        return 'form_permission_checker';
    }

    /**
     * {@inheritdoc}
     */
    public function addConfiguration(NodeDefinition $builder)
    {
        /* @var ArrayNodeDefinition $builder */
        $builder
            ->canBeEnabled()
        ;

        return $builder;
    }
}
