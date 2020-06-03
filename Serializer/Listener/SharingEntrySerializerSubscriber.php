<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\SecurityExtra\Serializer\Listener;

use JMS\Serializer\EventDispatcher\Events;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\Metadata\PropertyMetadata;
use Klipper\Component\DoctrineExtra\Util\ClassUtils;
use Klipper\Component\SecurityExtra\Serializer\Type\PermissionCollection;
use Klipper\Component\SecurityExtra\Sharing\SharingEntryInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class SharingEntrySerializerSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            [
                'event' => Events::PRE_SERIALIZE,
                'format' => 'json',
                'method' => 'onPreSerialize',
            ],
        ];
    }

    /**
     * Replace url generator aliases by her real classname and inject object in property meta.
     *
     * @param ObjectEvent $event The event
     */
    public function onPreSerialize(ObjectEvent $event): void
    {
        $object = $event->getObject();

        if (!$object instanceof SharingEntryInterface) {
            return;
        }

        $classMeta = $event->getContext()->getMetadataFactory()->getMetadataForClass(ClassUtils::getClass($object));

        if (null !== $classMeta && isset($classMeta->propertyMetadata['permissions'])) {
            /** @var PropertyMetadata $propertyMeta */
            $propertyMeta = $classMeta->propertyMetadata['permissions'];

            if (null === $propertyMeta->type['name']) {
                $propertyMeta->type['name'] = PermissionCollection::class;
            }
        }
    }
}
