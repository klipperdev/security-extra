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

use Klipper\Component\Security\Permission\FieldVote;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Permission checker extension for form type.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class PermissionCheckerTypeExtension extends AbstractTypeExtension
{
    /**
     * @var AuthorizationCheckerInterface
     */
    protected $checker;

    /**
     * @var null|bool
     */
    private $enabled = false;

    public function __construct(AuthorizationCheckerInterface $checker)
    {
        $this->checker = $checker;
    }

    /**
     * Set if the permission checker must be enabled.
     *
     * @param bool $enabled The value
     */
    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if (!$options['permission_checker']) {
            return;
        }

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($options): void {
            $form = $event->getForm();
            $class = $form->getConfig()->getDataClass();

            if (null === $class) {
                return;
            }

            foreach ($form->all() as $name => $child) {
                $field = (string) $child->getConfig()->getOption('property_path', $name);

                if (!$this->checker->isGranted('perm_edit', new FieldVote($class, $field))) {
                    if ($options['permission_remove_fields']) {
                        $form->remove($name);
                    } else {
                        $config = $child->getConfig();
                        $form->add(
                            $config->getName(),
                            \get_class($config->getType()->getInnerType()),
                            array_merge($config->getOptions(), [
                                'disabled' => true,
                            ])
                        );
                    }
                }
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'permission_checker' => $this->enabled,
            'permission_remove_fields' => false,
        ]);

        $resolver->addAllowedTypes('permission_checker', 'bool');
        $resolver->addAllowedTypes('permission_remove_fields', 'bool');
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes()
    {
        return [FormType::class];
    }
}
