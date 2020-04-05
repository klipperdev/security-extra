<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\SecurityExtra\Form\Type;

use Klipper\Component\Form\Util\FormChoiceUtil;
use Klipper\Component\SecurityExtra\Choice\PermissionContext;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Permission Context Form Type.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class PermissionContextType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choices' => FormChoiceUtil::simpleList(PermissionContext::listIdentifiers()),
            'choice_translation_domain' => PermissionContext::getTranslationDomain(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): string
    {
        return ChoiceType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'permission_context';
    }
}
