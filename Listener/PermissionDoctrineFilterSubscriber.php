<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\SecurityExtra\Listener;

use Doctrine\Common\Persistence\ObjectManager;
use Klipper\Component\DoctrineExtensions\Util\SqlFilterUtil;
use Klipper\Component\Security\Event\PostLoadPermissionsEvent;
use Klipper\Component\Security\Event\PreLoadPermissionsEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class PermissionDoctrineFilterSubscriber implements EventSubscriberInterface
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var string[]
     */
    protected $filters = [];

    /**
     * @var string[]
     */
    protected $currentFilters = [];

    /**
     * Constructor.
     *
     * @param ObjectManager $objectManager The doctrine object manager
     * @param string[]      $filters       The doctrine sql filters
     */
    public function __construct(ObjectManager $objectManager, array $filters = [])
    {
        $this->objectManager = $objectManager;
        $this->filters = 0 === \count($filters)
            ? SqlFilterUtil::findFilters($objectManager, [], true)
            : $filters;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            PreLoadPermissionsEvent::class => [
                ['preLoad', 0],
            ],
            PostLoadPermissionsEvent::class => [
                ['postLoad', 0],
            ],
        ];
    }

    /**
     * Disable the doctrine sql filters before the loading of permissions.
     */
    public function preLoad(): void
    {
        $filters = empty($this->filters)
            ? SqlFilterUtil::findFilters($this->objectManager, [], true)
            : $this->filters;

        $this->currentFilters = SqlFilterUtil::findFilters($this->objectManager, $filters);
        SqlFilterUtil::disableFilters($this->objectManager, $this->currentFilters);
    }

    /**
     * Re-enable the doctrine sql filters after the loading of permissions.
     */
    public function postLoad(): void
    {
        SqlFilterUtil::enableFilters($this->objectManager, $this->currentFilters);
        $this->currentFilters = [];
    }
}
