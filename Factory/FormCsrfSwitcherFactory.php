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
 * Provider for organizational context.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class FormCsrfSwitcherFactory implements SecurityFactoryInterface
{
    public function create(ContainerBuilder $container, string $id, array $config, string $userProvider, ?string $defaultEntryPoint): array
    {
        $providerId = 'klipper_security_extra.authentication.provider.form_csrf_switcher.'.$id;
        $container
            ->setDefinition($providerId, new ChildDefinition('klipper_security_extra.form_csrf_switcher.authentication.provider'))
        ;

        $listenerId = 'klipper_security_extra.authentication.listener.form_csrf_switcher.'.$id;
        $container
            ->setDefinition($listenerId, new ChildDefinition('klipper_security_extra.form_csrf_switcher.authentication.listener'))
            ->replaceArgument(1, $config)
        ;

        return [$providerId, $listenerId, $defaultEntryPoint];
    }

    public function getPosition(): string
    {
        return 'http';
    }

    public function getKey(): string
    {
        return 'form_csrf';
    }

    public function addConfiguration(NodeDefinition $builder)
    {
        /* @var ArrayNodeDefinition $builder */
        $builder
            ->canBeDisabled()
        ;

        return $builder;
    }
}
