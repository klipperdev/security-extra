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

use Klipper\Component\Config\Cache\AbstractCache;
use Klipper\Component\Config\ConfigCollectionInterface;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;

/**
 * Cache sharing entry factory.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class CacheSharingEntryFactory extends AbstractCache implements SharingEntryFactoryInterface, WarmableInterface
{
    protected SharingEntryFactoryInterface $factory;

    /**
     * @param SharingEntryFactoryInterface $factory The sharing entry factory
     * @param array                        $options An array of options
     */
    public function __construct(SharingEntryFactoryInterface $factory, array $options = [])
    {
        parent::__construct($options);

        $this->factory = $factory;
    }

    /**
     * @return ConfigCollectionInterface|SharingEntryConfigCollection
     */
    public function createConfigurations(): SharingEntryConfigCollection
    {
        if (null === $this->options['cache_dir'] || $this->options['debug']) {
            return $this->factory->createConfigurations();
        }

        return $this->loadConfigurationFromCache('sharing_entry', function () {
            return $this->factory->createConfigurations();
        });
    }

    /**
     * @param mixed $cacheDir
     */
    public function warmUp($cacheDir): void
    {
        // skip warmUp when the config doesn't use cache
        if (null === $this->options['cache_dir']) {
            return;
        }

        $this->createConfigurations();
    }
}
