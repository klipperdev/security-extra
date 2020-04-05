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

use Doctrine\Common\Persistence\ManagerRegistry;
use Geocoder\ProviderAggregator;
use Klipper\Component\DoctrineExtra\Util\ManagerUtils;
use Klipper\Component\Security\Model\UserInterface;
use Klipper\Component\SecurityExtra\Model\LogonAuditInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class LogonAuditSubscriber implements EventSubscriberInterface
{
    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @var string
     */
    protected $logonAuditClass;

    /**
     * @var null|ProviderAggregator
     */
    protected $geocoder;

    /**
     * @var null|string
     */
    protected $provider;

    /**
     * @var null|LoggerInterface
     */
    protected $logger;

    /**
     * Constructor.
     *
     * @param ManagerRegistry      $registry        The doctrine registry
     * @param null|LoggerInterface $logger          The logger of security
     * @param string               $logonAuditClass The logon audit class name
     */
    public function __construct(
        ManagerRegistry $registry,
        LoggerInterface $logger = null,
        $logonAuditClass = LogonAuditInterface::class
    ) {
        $this->registry = $registry;
        $this->logonAuditClass = $logonAuditClass;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            SecurityEvents::INTERACTIVE_LOGIN => [
                ['onInteractiveLogin', 0],
            ],
        ];
    }

    /**
     * Set the geocoder instance.
     *
     * @param ProviderAggregator $geocoder The geocoder service
     * @param string             $provider The provider name
     */
    public function setGeocoder(ProviderAggregator $geocoder, string $provider): void
    {
        $this->geocoder = $geocoder;
        $this->provider = $provider;
    }

    /**
     * Logon audit on interactive login.
     *
     * @param InteractiveLoginEvent $event The event
     */
    public function onInteractiveLogin(InteractiveLoginEvent $event): void
    {
        $this->doLogon($event->getRequest(), $event->getAuthenticationToken()->getUser());
    }

    /**
     * Do the logon audit.
     *
     * @param Request $request The request
     * @param mixed   $user    The user instance
     */
    protected function doLogon(Request $request, $user): void
    {
        if (!$user instanceof UserInterface) {
            return;
        }

        try {
            $om = ManagerUtils::getRequiredManager($this->registry, $this->logonAuditClass);
            $class = $om->getClassMetadata($this->logonAuditClass)->getName();
            $audit = new $class();

            $this->addRequireFields($audit, $user, $request);
            $om->persist($audit);
            $om->flush();

            $this->addGeocodeFields($audit);
            $om->persist($audit);
            $om->flush();
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->critical($e->getMessage(), ['exception' => $e]);
            }
        }
    }

    /**
     * Add The require fields in logon audit instance.
     *
     * @param LogonAuditInterface $audit   The logon audit instance
     * @param UserInterface       $user    The user
     * @param Request             $request The request
     *
     * @throws
     */
    protected function addRequireFields(LogonAuditInterface $audit, UserInterface $user, Request $request): void
    {
        $loggedAt = new \DateTime();
        $loggedAt->setTimestamp($request->server->get('REQUEST_TIME'));

        $audit
            ->setUserId($user->getId())
            ->setUsername($user->getUsername())
            ->setHost($request->getHttpHost())
            ->setUri($request->getRequestUri())
            ->setLoggedAt($loggedAt)
            ->setIp($request->getClientIp())
            ->setUserAgent($request->server->get('HTTP_USER_AGENT'))
            ->setLanguages($request->getLanguages())
        ;
    }

    /**
     * Set the geocode fields.
     *
     * @param LogonAuditInterface $audit The logon audit instance
     *
     * @throws
     */
    protected function addGeocodeFields(LogonAuditInterface $audit): void
    {
        if (null === $this->geocoder || null === $this->provider) {
            return;
        }

        $addresses = $this->geocoder->using($this->provider)->geocode($audit->getIp());

        if (0 === $addresses->count()) {
            return;
        }

        $address = $addresses->first();

        if (null !== ($country = $address->getCountry())) {
            $audit->setCountryCode($country->getCode())
                ->setCountryName($country->getName())
            ;
        }

        $audit
            ->setPostalCode($address->getPostalCode())
            ->setLocality($address->getLocality())
            ->setTimezone('' !== $address->getTimezone() ? $address->getTimezone() : null)
        ;

        if ($address->getAdminLevels()->count() > 0) {
            $audit
                ->setAdminLevelCode($address->getAdminLevels()->first()->getCode())
                ->setAdminLevelName($address->getAdminLevels()->first()->getName())
            ;
        }

        if (null !== $address->getCoordinates()) {
            $audit
                ->setLatitude($address->getCoordinates()->getLatitude())
                ->setLongitude($address->getCoordinates()->getLongitude())
            ;
        }
    }
}
