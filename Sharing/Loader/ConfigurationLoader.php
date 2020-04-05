<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\SecurityExtra\Sharing\Loader;

use Klipper\Component\SecurityExtra\Sharing\SharingEntryConfigCollection;
use Klipper\Component\SecurityExtra\Sharing\SharingEntryConfigInterface;
use Symfony\Component\Config\Loader\Loader;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class ConfigurationLoader extends Loader
{
    /**
     * @var SharingEntryConfigInterface[]
     */
    protected $configs;

    /**
     * Constructor.
     *
     * @param SharingEntryConfigInterface[] $configs The sharing entry configs
     */
    public function __construct(array $configs = [])
    {
        $this->configs = new SharingEntryConfigCollection();

        foreach ($configs as $config) {
            $this->configs->add($config);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function load($resource, $type = null): SharingEntryConfigCollection
    {
        return $this->configs;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($resource, $type = null): bool
    {
        return 'config' === $type;
    }
}
