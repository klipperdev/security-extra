<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\SecurityExtra\Model\Traits;

use Doctrine\ORM\Mapping as ORM;
use Klipper\Component\SecurityExtra\Model\LogonAuditInterface;

/**
 * Trait of add dependency entity with an user.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
trait LogonAuditTrait
{
    /**
     * @var null|int|string
     */
    protected $userId;

    /**
     * @ORM\Column(type="string")
     */
    protected ?string $userIdentifier = null;

    /**
     * @ORM\Column(type="string")
     */
    protected ?string $host = null;

    /**
     * @ORM\Column(type="string")
     */
    protected ?string $uri = null;

    /**
     * @ORM\Column(type="datetime")
     */
    protected ?\DateTimeInterface $loggedAt = null;

    /**
     * @ORM\Column(type="string")
     */
    protected ?string $ip = null;

    /**
     * @ORM\Column(type="string")
     */
    protected ?string $userAgent = null;

    /**
     * @ORM\Column(type="json")
     */
    protected array $languages = [];

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected ?string $timezone = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected ?string $countryCode = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected ?string $countryName = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected ?string $adminLevelCode = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected ?string $adminLevelName = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected ?string $postalCode = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected ?string $locality = null;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected ?float $latitude = null;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected ?float $longitude = null;

    /**
     * @see LogonAuditInterface::setUserId()
     *
     * @param mixed $userId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * @see LogonAuditInterface::getUserId()
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @see LogonAuditInterface::setUserIdentifier()
     */
    public function setUserIdentifier(?string $userIdentifier): self
    {
        $this->userIdentifier = $userIdentifier;

        return $this;
    }

    /**
     * @see LogonAuditInterface::getUserIdentifier()
     */
    public function getUserIdentifier(): ?string
    {
        return $this->userIdentifier;
    }

    /**
     * @see LogonAuditInterface::setHost()
     */
    public function setHost(?string $host): self
    {
        $this->host = $host;

        return $this;
    }

    /**
     * @see LogonAuditInterface::getHost()
     */
    public function getHost(): ?string
    {
        return $this->host;
    }

    /**
     * @see LogonAuditInterface::setUri()
     */
    public function setUri(?string $uri): self
    {
        $this->uri = $uri;

        return $this;
    }

    /**
     * @see LogonAuditInterface::getUri()
     */
    public function getUri(): ?string
    {
        return $this->uri;
    }

    /**
     * @see LogonAuditInterface::setLoggedAt()
     */
    public function setLoggedAt(?\DateTimeInterface $loggedAt): self
    {
        $this->loggedAt = $loggedAt;

        return $this;
    }

    /**
     * @see LogonAuditInterface::getLoggedAt()
     */
    public function getLoggedAt(): ?\DateTimeInterface
    {
        return $this->loggedAt;
    }

    /**
     * @see LogonAuditInterface::setIp()
     */
    public function setIp(?string $ip): self
    {
        $this->ip = $ip;

        return $this;
    }

    /**
     * @see LogonAuditInterface::getIp()
     */
    public function getIp(): ?string
    {
        return $this->ip;
    }

    /**
     * @see LogonAuditInterface::setUserAgent()
     */
    public function setUserAgent(?string $userAgent): self
    {
        $this->userAgent = $userAgent;

        return $this;
    }

    /**
     * @see LogonAuditInterface::getUserAgent()
     */
    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    /**
     * @see LogonAuditInterface::setLanguages()
     */
    public function setLanguages(array $languages): self
    {
        $this->languages = $languages;

        return $this;
    }

    /**
     * @see LogonAuditInterface::getLanguages()
     */
    public function getLanguages(): array
    {
        return $this->languages;
    }

    /**
     * @see LogonAuditInterface::setTimezone()
     */
    public function setTimezone(?string $timezone): self
    {
        $this->timezone = $timezone;

        return $this;
    }

    /**
     * @see LogonAuditInterface::getTimezone()
     */
    public function getTimezone(): ?string
    {
        return $this->timezone;
    }

    /**
     * @see LogonAuditInterface::setCountryCode()
     */
    public function setCountryCode(?string $countryCode): self
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    /**
     * @see LogonAuditInterface::getCountryCode()
     */
    public function getCountryCode(): ?string
    {
        return $this->countryCode;
    }

    /**
     * @see LogonAuditInterface::setCountryName()
     */
    public function setCountryName(?string $countryName): self
    {
        $this->countryName = $countryName;

        return $this;
    }

    /**
     * @see LogonAuditInterface::getCountryName()
     */
    public function getCountryName(): ?string
    {
        return $this->countryName;
    }

    /**
     * @see LogonAuditInterface::setAdminLevelCode()
     */
    public function setAdminLevelCode(?string $adminLevelCode): self
    {
        $this->adminLevelCode = $adminLevelCode;

        return $this;
    }

    /**
     * @see LogonAuditInterface::getAdminLevelCode()
     */
    public function getAdminLevelCode(): ?string
    {
        return $this->adminLevelCode;
    }

    /**
     * @see LogonAuditInterface::setAdminLevelName()
     */
    public function setAdminLevelName(?string $adminLevelName): self
    {
        $this->adminLevelName = $adminLevelName;

        return $this;
    }

    /**
     * @see LogonAuditInterface::getAdminLevelName()
     */
    public function getAdminLevelName(): ?string
    {
        return $this->adminLevelName;
    }

    /**
     * @see LogonAuditInterface::setPostalCode()
     */
    public function setPostalCode(?string $postalCode): self
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    /**
     * @see LogonAuditInterface::getPostalCode()
     */
    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    /**
     * @see LogonAuditInterface::setLocality()
     */
    public function setLocality(?string $locality): self
    {
        $this->locality = $locality;

        return $this;
    }

    /**
     * @see LogonAuditInterface::getLocality()
     */
    public function getLocality(): ?string
    {
        return $this->locality;
    }

    /**
     * @see LogonAuditInterface::setLatitude()
     */
    public function setLatitude(?float $latitude): self
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * @see LogonAuditInterface::getLatitude()
     */
    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    /**
     * @see LogonAuditInterface::setLongitude()
     */
    public function setLongitude(?float $longitude): self
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * @see LogonAuditInterface::getLongitude()
     */
    public function getLongitude(): ?float
    {
        return $this->longitude;
    }
}
