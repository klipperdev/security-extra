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

use Klipper\Component\SecurityExtra\Organizational\ExcludedClassesConfigCollection;
use Symfony\Component\Config\Loader\Loader;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class ExcludedClassesConfigurationLoader extends Loader
{
    protected ExcludedClassesConfigCollection $classes;

    /**
     * @param string[] $classes The class names
     */
    public function __construct(array $classes = [])
    {
        $this->classes = new ExcludedClassesConfigCollection();

        foreach ($classes as $class) {
            $this->classes->add($class);
        }
    }

    public function load($resource, string $type = null): ExcludedClassesConfigCollection
    {
        return $this->classes;
    }

    public function supports($resource, string $type = null): bool
    {
        return 'config' === $type;
    }
}
