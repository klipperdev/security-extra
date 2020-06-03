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
use Klipper\Component\Security\Organizational\OrganizationalContextInterface;
use Klipper\Component\SecurityExtra\Choice\SharingIdentityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Sharing Identity Type Form Type.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class SharingIdentityTypeType extends AbstractType
{
    private ?OrganizationalContextInterface $orgContext;

    /**
     * @param null|OrganizationalContextInterface $orgContext The organizational context
     */
    public function __construct(?OrganizationalContextInterface $orgContext)
    {
        $this->orgContext = $orgContext;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choices' => FormChoiceUtil::simpleList($this->filter(SharingIdentityType::listIdentifiers())),
            'choice_translation_domain' => SharingIdentityType::getTranslationDomain(),
        ]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'sharing_identity_type';
    }

    /**
     * Filter the list identifiers.
     *
     * @param array $identifiers The list identifier
     */
    private function filter(array $identifiers): array
    {
        if (null !== $this->orgContext && !$this->orgContext->isOrganization()) {
            $list = $identifiers;
            $identifiers = [];

            foreach ($list as $key => $value) {
                if (\in_array($key, SharingIdentityType::RESTRICTED, true)) {
                    $identifiers[$key] = $value;
                }
            }
        }

        return $identifiers;
    }
}
