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

use Klipper\Component\SecurityExtra\Annotation\OrganizationalFilterUserExcludedOrgsClass;
use Klipper\Component\SecurityExtra\Organizational\ExcludedClassesConfigCollection;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class UserExcludedOrgsClassesAnnotationLoader extends AbstractAnnotationLoader
{
    /**
     * {@inheritdoc}
     */
    public function load($resource, $type = null): ExcludedClassesConfigCollection
    {
        $configs = new ExcludedClassesConfigCollection();
        $this->addClasses($configs, $resource, OrganizationalFilterUserExcludedOrgsClass::class);

        return $configs;
    }
}
