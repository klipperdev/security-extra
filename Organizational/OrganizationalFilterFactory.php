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

use Symfony\Component\Config\Loader\LoaderInterface;

/**
 * Organizational filter factory.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class OrganizationalFilterFactory implements OrganizationalFilterFactoryInterface
{
    protected LoaderInterface $excludedLoader;

    protected LoaderInterface $userExcludedOrgsLoader;

    protected LoaderInterface $optionalAllFilterLoader;

    /**
     * @var mixed
     */
    protected $resource;

    /**
     * @param LoaderInterface $excludedLoader          The loader of organizational filter excluded classes
     * @param LoaderInterface $userExcludedOrgsLoader  The loader of organizational filter user excluded classes
     * @param LoaderInterface $optionalAllFilterLoader The loader of organizational filter optional all filter classes
     * @param mixed           $resource                The main resource to load
     */
    public function __construct(
        LoaderInterface $excludedLoader,
        LoaderInterface $userExcludedOrgsLoader,
        LoaderInterface $optionalAllFilterLoader,
        $resource
    ) {
        $this->excludedLoader = $excludedLoader;
        $this->userExcludedOrgsLoader = $userExcludedOrgsLoader;
        $this->optionalAllFilterLoader = $optionalAllFilterLoader;
        $this->resource = $resource;
    }

    /**
     * @throws
     */
    public function createExcludedClasses(): ExcludedClassesConfigCollection
    {
        return $this->excludedLoader->load($this->resource);
    }

    /**
     * @throws
     */
    public function createUserExcludedOrgsClasses(): UserExcludedOrgsClassesConfigCollection
    {
        return $this->userExcludedOrgsLoader->load($this->resource);
    }

    /**
     * @throws
     */
    public function createOptionalAllFilterClasses(): OptionalAllFilterClassesConfigCollection
    {
        return $this->optionalAllFilterLoader->load($this->resource);
    }
}
