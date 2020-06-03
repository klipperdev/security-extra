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
use Klipper\Component\SecurityExtra\Organizational\ExcludedClassesConfigCollection;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class ExcludedClassesArrayResourceLoader extends AbstractArrayResourceLoader
{
    /**
     * @param mixed $resource
     *
     * @return ConfigCollectionInterface|ExcludedClassesConfigCollection
     */
    public function load($resource, string $type = null): ExcludedClassesConfigCollection
    {
        return parent::load($resource, $type);
    }

    protected function createConfigCollection(): ConfigCollectionInterface
    {
        return new ExcludedClassesConfigCollection();
    }
}
