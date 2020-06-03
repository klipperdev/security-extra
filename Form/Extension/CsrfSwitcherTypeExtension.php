<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\SecurityExtra\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Disable the CSRF security for the API.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class CsrfSwitcherTypeExtension extends AbstractTypeExtension
{
    private ?bool $enabled = null;

    /**
     * Set if the CSRF must be enabled.
     *
     * @param bool $enabled The value
     */
    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        if (null === $this->enabled) {
            return;
        }

        $resolver->setDefaults([
            'csrf_protection' => $this->enabled,
        ]);
    }

    public static function getExtendedTypes(): iterable
    {
        return [FormType::class];
    }
}
