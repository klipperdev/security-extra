<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\SecurityExtra\Model;

use Klipper\Component\Model\Traits\IdInterface;

/**
 * Login audit interface.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface LogonAuditInterface extends IdInterface
{
    /**
     * Set the user id.
     *
     * @param null|int|string $userId The user id
     *
     * @return static
     */
    public function setUserId($userId);

    /**
     * Get the user id.
     *
     * @return null|int|string
     */
    public function getUserId();

    /**
     * Set the username.
     *
     * @param null|string $username The username
     *
     * @return static
     */
    public function setUsername(?string $username);

    /**
     * Get the username.
     */
    public function getUsername(): ?string;

    /**
     * Set the host.
     *
     * @param null|string $host The host
     *
     * @return static
     */
    public function setHost(?string $host);

    /**
     * Get the host.
     */
    public function getHost(): ?string;

    /**
     * Set the URI.
     *
     * @param null|string $uri The URI
     *
     * @return static
     */
    public function setUri(?string $uri);

    /**
     * Get the URI.
     */
    public function getUri(): ?string;

    /**
     * Set the logged at.
     *
     * @param null|\DateTimeInterface $loggedAt The logged at
     *
     * @return static
     */
    public function setLoggedAt(?\DateTimeInterface $loggedAt);

    /**
     * Get the logged at.
     */
    public function getLoggedAt(): ?\DateTimeInterface;

    /**
     * Set the IP.
     *
     * @param null|string $ip The IP
     *
     * @return static
     */
    public function setIp(?string $ip);

    /**
     * Get the IP.
     */
    public function getIp(): ?string;

    /**
     * Set the user agent.
     *
     * @param null|string $userAgent The user agent
     *
     * @return static
     */
    public function setUserAgent(?string $userAgent);

    /**
     * Get the user agent.
     */
    public function getUserAgent(): ?string;

    /**
     * Set the languages.
     *
     * @param string[] $languages The languages
     *
     * @return static
     */
    public function setLanguages(array $languages);

    /**
     * Get the languages.
     *
     * @return string[]
     */
    public function getLanguages(): array;

    /**
     * Set the timezone.
     *
     * @param null|string $timezone The timezone
     *
     * @return static
     */
    public function setTimezone(?string $timezone);

    /**
     * Get the timezone.
     */
    public function getTimezone(): ?string;

    /**
     * Set the country code.
     *
     * @param null|string $countryCode The country code
     *
     * @return static
     */
    public function setCountryCode(?string $countryCode);

    /**
     * Get the country code.
     */
    public function getCountryCode(): ?string;

    /**
     * Set the country name.
     *
     * @param null|string $countryName The country name
     *
     * @return static
     */
    public function setCountryName(?string $countryName);

    /**
     * Get the country name.
     */
    public function getCountryName(): ?string;

    /**
     * Set the admin level code.
     *
     * @param null|string $adminLevelCode The admin level code
     *
     * @return static
     */
    public function setAdminLevelCode(?string $adminLevelCode);

    /**
     * Get the admin level code.
     */
    public function getAdminLevelCode(): ?string;

    /**
     * Set the admin level name.
     *
     * @param null|string $adminLevelName The admin level name
     *
     * @return static
     */
    public function setAdminLevelName(?string $adminLevelName);

    /**
     * Get the admin level name.
     */
    public function getAdminLevelName(): ?string;

    /**
     * Set the postal code.
     *
     * @param null|string $postalCode The postal code
     *
     * @return static
     */
    public function setPostalCode(?string $postalCode);

    /**
     * Get the postal code.
     */
    public function getPostalCode(): ?string;

    /**
     * Set the locality.
     *
     * @param null|string $locality The locality
     *
     * @return static
     */
    public function setLocality(?string $locality);

    /**
     * Get the locality.
     */
    public function getLocality(): ?string;

    /**
     * Set the latitude.
     *
     * @param null|float $latitude The latitude
     *
     * @return static
     */
    public function setLatitude(?float $latitude);

    /**
     * Get the latitude.
     */
    public function getLatitude(): ?float;

    /**
     * Set the longitude.
     *
     * @param null|float $longitude The longitude
     *
     * @return static
     */
    public function setLongitude(?float $longitude);

    /**
     * Get the longitude.
     */
    public function getLongitude(): ?float;
}
