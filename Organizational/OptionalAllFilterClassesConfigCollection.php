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

use Klipper\Component\Config\ConfigCollectionInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class OptionalAllFilterClassesConfigCollection extends AbstractClassesConfigCollection
{
    /**
     * @param ConfigCollectionInterface|OptionalAllFilterClassesConfigCollection $collection The collection
     */
    public function addCollection(ConfigCollectionInterface $collection): void
    {
        foreach ($collection as $config) {
            $this->add($config);
        }

        parent::addCollection($collection);
    }
}
