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

use Klipper\Component\SecurityExtra\Organizational\UserExcludedOrgsClassesConfigCollection;
use Symfony\Component\Config\Loader\Loader;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class UserExcludedOrgsClassesConfigurationLoader extends Loader
{
    /**
     * @var UserExcludedOrgsClassesConfigCollection
     */
    protected $classes;

    /**
     * Constructor.
     *
     * @param string[] $classes The class names
     */
    public function __construct(array $classes = [])
    {
        $this->classes = new UserExcludedOrgsClassesConfigCollection();

        foreach ($classes as $class) {
            $this->classes->add($class);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function load($resource, $type = null): UserExcludedOrgsClassesConfigCollection
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
