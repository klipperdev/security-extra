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

use Klipper\Component\Config\AbstractConfigCollection;
use Klipper\Component\Config\ConfigCollectionInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class SharingEntryConfigCollection extends AbstractConfigCollection
{
    /**
     * Adds a sharing entry config.
     *
     * @param SharingEntryConfigInterface $config A sharing entry config instance
     */
    public function add(SharingEntryConfigInterface $config): void
    {
        if (isset($this->configs[$config->getType()])) {
            $this->configs[$config->getType()]->merge($config);
        } else {
            $this->configs[$config->getType()] = $config;
        }
    }

    /**
     * {@inheritdoc}
     *
     * @return SharingEntryConfigInterface[]
     */
    public function all(): array
    {
        return parent::all();
    }

    /**
     * Gets a sharing entry config by type.
     *
     * @param string $type The sharing entry config type
     *
     * @return null|SharingEntryConfigInterface A sharing entry config instance or null when not found
     */
    public function get(string $type): ?SharingEntryConfigInterface
    {
        return $this->configs[$type] ?? null;
    }

    /**
     * Removes a sharing entry config or an array of sharing entry configs by type from the collection.
     *
     * @param string|string[] $type The sharing entry config type or an array of sharing entry config types
     */
    public function remove(string $type): void
    {
        foreach ((array) $type as $n) {
            unset($this->configs[$n]);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @param ConfigCollectionInterface|SharingEntryConfigCollection $collection The sharing entry collection
     */
    public function addCollection(ConfigCollectionInterface $collection): void
    {
        foreach ($collection->all() as $config) {
            $this->add($config);
        }

        parent::addCollection($collection);
    }
}
