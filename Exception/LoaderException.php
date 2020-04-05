<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\SecurityExtra\Exception;

use Klipper\Component\Security\Exception\LoaderException as BaseLoaderException;

/**
 * Base LoaderException for the security.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class LoaderException extends BaseLoaderException implements ExceptionInterface
{
}
