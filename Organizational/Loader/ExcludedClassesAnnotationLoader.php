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

use Klipper\Component\SecurityExtra\Annotation\OrganizationalFilterExcludedClass;
use Klipper\Component\SecurityExtra\Organizational\ExcludedClassesConfigCollection;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class ExcludedClassesAnnotationLoader extends AbstractAnnotationLoader
{
    /**
     * {@inheritdoc}
     */
    public function load($resource, $type = null): ExcludedClassesConfigCollection
    {
        $configs = new ExcludedClassesConfigCollection();
        $this->addClasses($configs, $resource, OrganizationalFilterExcludedClass::class);

        return $configs;
    }
}
