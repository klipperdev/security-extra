<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\SecurityExtra\Serializer\Handler;

use JMS\Serializer\GraphNavigatorInterface;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\Visitor\SerializationVisitorInterface;
use Klipper\Component\MetadataExtensions\Permission\PermissionMetadataManagerInterface;
use Klipper\Component\Security\Model\PermissionInterface;
use Klipper\Component\SecurityExtra\Serializer\Type\PermissionCollection;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class PermissionCollectionHandler implements SubscribingHandlerInterface
{
    /**
     * @var PermissionMetadataManagerInterface
     */
    private $pmManager;

    /**
     * Constructor.
     *
     * @param PermissionMetadataManagerInterface $pmManager The permission metadata manager
     */
    public function __construct(PermissionMetadataManagerInterface $pmManager)
    {
        $this->pmManager = $pmManager;
    }

    public static function getSubscribingMethods(): array
    {
        return [
            [
                'direction' => GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                'format' => 'json',
                'type' => PermissionCollection::class,
                'method' => 'convertPermissions',
            ],
            [
                'direction' => GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                'format' => 'xml',
                'type' => PermissionCollection::class,
                'method' => 'convertPermissions',
            ],
            [
                'direction' => GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                'format' => 'yml',
                'type' => PermissionCollection::class,
                'method' => 'convertPermissions',
            ],
        ];
    }

    /**
     * Convert the permissions in permission metadatas.
     *
     * @param SerializationVisitorInterface $visitor The serializer visitor
     * @param PermissionInterface[]         $data    The data
     * @param array                         $type    The serializer type
     */
    public function convertPermissions(SerializationVisitorInterface $visitor, $data, array $type): ?string
    {
        return $visitor->visitArray($this->pmManager->buildPermissions($data), $type);
    }
}
