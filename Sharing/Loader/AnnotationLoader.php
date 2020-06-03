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

use Klipper\Component\Config\Loader\AbstractAnnotationLoader;
use Klipper\Component\SecurityExtra\Annotation\SharingEntry;
use Klipper\Component\SecurityExtra\Sharing\SharingEntryConfig;
use Klipper\Component\SecurityExtra\Sharing\SharingEntryConfigCollection;
use Symfony\Component\Config\Resource\DirectoryResource;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class AnnotationLoader extends AbstractAnnotationLoader
{
    public function supports($resource, string $type = null): bool
    {
        return 'annotation' === $type && \is_string($resource) && is_dir($resource);
    }

    public function load($resource, string $type = null): SharingEntryConfigCollection
    {
        $configs = new SharingEntryConfigCollection();
        $configs->addResource(new DirectoryResource($resource));

        foreach ($this->classFinder->findClasses([$resource]) as $class) {
            try {
                $refClass = new \ReflectionClass($class);
                $configs = $this->getConfigurations($refClass, $configs);
            } catch (\ReflectionException $e) {
                // skip
            }
        }

        return $configs;
    }

    /**
     * Get the subject configurations.
     *
     * @param \ReflectionClass             $refClass The reflection class
     * @param SharingEntryConfigCollection $configs  The sharing entry config collection
     */
    protected function getConfigurations(\ReflectionClass $refClass, SharingEntryConfigCollection $configs): SharingEntryConfigCollection
    {
        $class = $refClass->name;
        $classAnnotations = $this->reader->getClassAnnotations($refClass);

        foreach ($classAnnotations as $annotation) {
            if ($annotation instanceof SharingEntry) {
                $configs->add(new SharingEntryConfig(
                    $class,
                    $annotation->getField(),
                    $annotation->getRepositoryMethod()
                ));
            }
        }

        return $configs;
    }
}
