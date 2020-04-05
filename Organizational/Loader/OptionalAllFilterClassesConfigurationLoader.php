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

use Klipper\Component\SecurityExtra\Organizational\OptionalAllFilterClassesConfigCollection;
use Symfony\Component\Config\Loader\Loader;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class OptionalAllFilterClassesConfigurationLoader extends Loader
{
    /**
     * @var OptionalAllFilterClassesConfigCollection
     */
    protected $classes;

    /**
     * Constructor.
     *
     * @param string[] $classes The class names
     */
    public function __construct(array $classes = [])
    {
        $this->classes = new OptionalAllFilterClassesConfigCollection();

        foreach ($classes as $class) {
            $this->classes->add($class);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function load($resource, $type = null): OptionalAllFilterClassesConfigCollection
    {
        return $this->classes;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($resource, $type = null): bool
    {
        return 'config' === $type;
    }
}
