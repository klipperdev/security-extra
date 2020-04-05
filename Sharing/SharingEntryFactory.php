<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\SecurityExtra\Sharing;

use Symfony\Component\Config\Loader\LoaderInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class SharingEntryFactory implements SharingEntryFactoryInterface
{
    /**
     * @var LoaderInterface
     */
    protected $loader;

    /**
     * @var mixed
     */
    protected $resource;

    /**
     * Constructor.
     *
     * @param LoaderInterface $loader   The sharing entry loader
     * @param mixed           $resource The main resource to load
     */
    public function __construct(LoaderInterface $loader, $resource)
    {
        $this->loader = $loader;
        $this->resource = $resource;
    }

    /**
     * {@inheritdoc}
     *
     * @throws
     */
    public function createConfigurations(): SharingEntryConfigCollection
    {
        return $this->loader->load($this->resource);
    }
}
