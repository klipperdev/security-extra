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
    protected ?string $username = null;

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
     * {@inheritdoc}
     *
     * @see LogonAuditInterface::setUserId()
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @see LogonAuditInterface::getUserId()
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * {@inheritdoc}
     *
     * @see LogonAuditInterface::setUsername()
     */
    public function setUsername(?string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @see LogonAuditInterface::getUsername()
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * {@inheritdoc}
     *
     * @see LogonAuditInterface::setHost()
     */
    public function setHost(?string $host): self
    {
        $this->host = $host;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @see LogonAuditInterface::getHost()
     */
    public function getHost(): ?string
    {
        return $this->host;
    }

    /**
     * {@inheritdoc}
     *
     * @see LogonAuditInterface::setUri()
     */
    public function setUri(?string $uri): self
    {
        $this->uri = $uri;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @see LogonAuditInterface::getUri()
     */
    public function getUri(): ?string
    {
        return $this->uri;
    }

    /**
     * {@inheritdoc}
     *
     * @see LogonAuditInterface::setLoggedAt()
     */
    public function setLoggedAt(?\DateTimeInterface $loggedAt): self
    {
        $this->loggedAt = $loggedAt;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @see LogonAuditInterface::getLoggedAt()
     */
    public function getLoggedAt(): ?\DateTimeInterface
    {
        return $this->loggedAt;
    }

    /**
     * {@inheritdoc}
     *
     * @see LogonAuditInterface::setIp()
     */
    public function setIp(?string $ip): self
    {
        $this->ip = $ip;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @see LogonAuditInterface::getIp()
     */
    public function getIp(): ?string
    {
        return $this->ip;
    }

    /**
     * {@inheritdoc}
     *
     * @see LogonAuditInterface::setUserAgent()
     */
    public function setUserAgent(?string $userAgent): self
    {
        $this->userAgent = $userAgent;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @see LogonAuditInterface::getUserAgent()
     */
    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    /**
     * {@inheritdoc}
     *
     * @see LogonAuditInterface::setLanguages()
     */
    public function setLanguages(array $languages): self
    {
        $this->languages = $languages;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @see LogonAuditInterface::getLanguages()
     */
    public function getLanguages(): array
    {
        return $this->languages;
    }

    /**
     * {@inheritdoc}
     *
     * @see LogonAuditInterface::setTimezone()
     */
    public function setTimezone(?string $timezone): self
    {
        $this->timezone = $timezone;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @see LogonAuditInterface::getTimezone()
     */
    public function getTimezone(): ?string
    {
        return $this->timezone;
    }

    /**
     * {@inheritdoc}
     *
     * @see LogonAuditInterface::setCountryCode()
     */
    public function setCountryCode(?string $countryCode): self
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @see LogonAuditInterface::getCountryCode()
     */
    public function getCountryCode(): ?string
    {
        return $this->countryCode;
    }

    /**
     * {@inheritdoc}
     *
     * @see LogonAuditInterface::setCountryName()
     */
    public function setCountryName(?string $countryName): self
    {
        $this->countryName = $countryName;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @see LogonAuditInterface::getCountryName()
     */
    public function getCountryName(): ?string
    {
        return $this->countryName;
    }

    /**
     * {@inheritdoc}
     *
     * @see LogonAuditInterface::setAdminLevelCode()
     */
    public function setAdminLevelCode(?string $adminLevelCode): self
    {
        $this->adminLevelCode = $adminLevelCode;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @see LogonAuditInterface::getAdminLevelCode()
     */
    public function getAdminLevelCode(): ?string
    {
        return $this->adminLevelCode;
    }

    /**
     * {@inheritdoc}
     *
     * @see LogonAuditInterface::setAdminLevelName()
     */
    public function setAdminLevelName(?string $adminLevelName): self
    {
        $this->adminLevelName = $adminLevelName;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @see LogonAuditInterface::getAdminLevelName()
     */
    public function getAdminLevelName(): ?string
    {
        return $this->adminLevelName;
    }

    /**
     * {@inheritdoc}
     *
     * @see LogonAuditInterface::setPostalCode()
     */
    public function setPostalCode(?string $postalCode): self
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @see LogonAuditInterface::getPostalCode()
     */
    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    /**
     * {@inheritdoc}
     *
     * @see LogonAuditInterface::setLocality()
     */
    public function setLocality(?string $locality): self
    {
        $this->locality = $locality;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @see LogonAuditInterface::getLocality()
     */
    public function getLocality(): ?string
    {
        return $this->locality;
    }

    /**
     * {@inheritdoc}
     *
     * @see LogonAuditInterface::setLatitude()
     */
    public function setLatitude(?float $latitude): self
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @see LogonAuditInterface::getLatitude()
     */
    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    /**
     * {@inheritdoc}
     *
     * @see LogonAuditInterface::setLongitude()
     */
    public function setLongitude(?float $longitude): self
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @see LogonAuditInterface::getLongitude()
     */
    public function getLongitude(): ?float
    {
        return $this->longitude;
    }
}
