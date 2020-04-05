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

use Klipper\Component\Config\Cache\AbstractCache;
use Klipper\Component\Config\ConfigCollectionInterface;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;

/**
 * Cache organizational filter factory.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class CacheOrganizationalFilterFactory extends AbstractCache implements OrganizationalFilterFactoryInterface, WarmableInterface
{
    /**
     * @var OrganizationalFilterFactoryInterface
     */
    protected $factory;

    /**
     * Constructor.
     *
     * @param OrganizationalFilterFactoryInterface $factory The organizational filter factory
     * @param array                                $options An array of options
     */
    public function __construct(OrganizationalFilterFactoryInterface $factory, array $options = [])
    {
        parent::__construct($options);

        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     *
     * @return ConfigCollectionInterface|ExcludedClassesConfigCollection
     */
    public function createExcludedClasses(): ExcludedClassesConfigCollection
    {
        if (null === $this->options['cache_dir'] || $this->options['debug']) {
            return $this->factory->createExcludedClasses();
        }

        return $this->loadConfigurationFromCache('organizational_filter_excluded', function () {
            return $this->factory->createExcludedClasses();
        });
    }

    /**
     * {@inheritdoc}
     *
     * @return ConfigCollectionInterface|UserExcludedOrgsClassesConfigCollection
     */
    public function createUserExcludedOrgsClasses(): UserExcludedOrgsClassesConfigCollection
    {
        if (null === $this->options['cache_dir'] || $this->options['debug']) {
            return $this->factory->createUserExcludedOrgsClasses();
        }

        return $this->loadConfigurationFromCache('organizational_filter_user_excluded_orgs', function () {
            return $this->factory->createUserExcludedOrgsClasses();
        });
    }

    /**
     * {@inheritdoc}
     *
     * @return ConfigCollectionInterface|OptionalAllFilterClassesConfigCollection
     */
    public function createOptionalAllFilterClasses(): OptionalAllFilterClassesConfigCollection
    {
        if (null === $this->options['cache_dir'] || $this->options['debug']) {
            return $this->factory->createOptionalAllFilterClasses();
        }

        return $this->loadConfigurationFromCache('organizational_filter_optional_all_filter', function () {
            return $this->factory->createOptionalAllFilterClasses();
        });
    }

    /**
     * {@inheritdoc}
     */
    public function warmUp($cacheDir): void
    {
        // skip warmUp when the config doesn't use cache
        if (null === $this->options['cache_dir']) {
            return;
        }

        $this->createExcludedClasses();
        $this->createUserExcludedOrgsClasses();
        $this->createOptionalAllFilterClasses();
    }
}
