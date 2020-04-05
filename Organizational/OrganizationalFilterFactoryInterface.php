<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\SecurityExtra\Organizational;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface OrganizationalFilterFactoryInterface
{
    /**
     * Create the excluded classes.
     */
    public function createExcludedClasses(): ExcludedClassesConfigCollection;

    /**
     * Create the user excluded organizations classes.
     */
    public function createUserExcludedOrgsClasses(): UserExcludedOrgsClassesConfigCollection;

    /**
     * Create the optional all filter classes.
     */
    public function createOptionalAllFilterClasses(): OptionalAllFilterClassesConfigCollection;
}
