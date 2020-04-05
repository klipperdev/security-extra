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

use Klipper\Component\Config\Loader\AbstractAnnotationLoader as BaseAbstractAnnotationLoader;
use Klipper\Component\SecurityExtra\Organizational\AbstractClassesConfigCollection;
use Symfony\Component\Config\Resource\DirectoryResource;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
abstract class AbstractAnnotationLoader extends BaseAbstractAnnotationLoader
{
    /**
     * {@inheritdoc}
     */
    public function supports($resource, $type = null): bool
    {
        return 'annotation' === $type && \is_string($resource) && is_dir($resource);
    }

    /**
     * @param AbstractClassesConfigCollection $configs         The config collection
     * @param mixed                           $resource        The resource
     * @param string                          $annotationClass The annotation class name
     */
    protected function addClasses(AbstractClassesConfigCollection $configs, $resource, $annotationClass): void
    {
        $configs->addResource(new DirectoryResource($resource));

        foreach ($this->classFinder->findClasses([$resource]) as $class) {
            try {
                $refClass = new \ReflectionClass($class);
                $classAnnotations = $this->reader->getClassAnnotations($refClass);

                foreach ($classAnnotations as $annotation) {
                    if (is_a($annotation, $annotationClass)) {
                        $configs->add($class);
                    }
                }
            } catch (\ReflectionException $e) {
                // skip
            }
        }
    }
}
