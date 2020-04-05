<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\SecurityExtra\Organizational\Loader;

use Klipper\Component\Config\ConfigCollectionInterface;
use Klipper\Component\Config\Loader\AbstractArrayResourceLoader;
use Klipper\Component\SecurityExtra\Organizational\UserExcludedOrgsClassesConfigCollection;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class UserExcludedOrgsClassesArrayResourceLoader extends AbstractArrayResourceLoader
{
    /**
     * {@inheritdoc}
     *
     * @return ConfigCollectionInterface|UserExcludedOrgsClassesConfigCollection
     */
    public function load($resource, $type = null): UserExcludedOrgsClassesConfigCollection
    {
        return parent::load($resource, $type);
    }

    /**
     * {@inheritdoc}
     */
    protected function createConfigCollection(): ConfigCollectionInterface
    {
        return new UserExcludedOrgsClassesConfigCollection();
    }
}
