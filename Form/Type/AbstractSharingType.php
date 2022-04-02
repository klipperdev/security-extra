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

use Doctrine\Common\Collections\ArrayCollection;
use Klipper\Component\Model\Traits\IdInterface;
use Klipper\Component\Resource\Domain\DomainManagerInterface;
use Klipper\Component\Routing\OrganizationalRouting;
use Klipper\Component\Security\Model\GroupInterface;
use Klipper\Component\Security\Model\RoleInterface;
use Klipper\Component\Security\Model\SharingInterface;
use Klipper\Component\Security\Model\UserInterface;
use Klipper\Component\Security\Organizational\OrganizationalContextInterface;
use Klipper\Component\Security\Organizational\OrganizationalUtil;
use Klipper\Component\SecurityExtra\Entity\Repository\UserRepositoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Base of Sharing Form Type.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
abstract class AbstractSharingType extends AbstractType
{
    protected OrganizationalRouting $orgRouting;

    protected OrganizationalContextInterface $orgContext;

    protected DomainManagerInterface $domainManager;

    protected ValidatorInterface $validator;

    /**
     * @param OrganizationalRouting          $orgRouting    The organizational routing
     * @param OrganizationalContextInterface $orgContext    The organizational context
     * @param DomainManagerInterface         $domainManager The domain manager
     * @param ValidatorInterface             $validator     the validator
     */
    public function __construct(
        OrganizationalRouting $orgRouting,
        OrganizationalContextInterface $orgContext,
        DomainManagerInterface $domainManager,
        ValidatorInterface $validator
    ) {
        $this->orgRouting = $orgRouting;
        $this->orgContext = $orgContext;
        $this->domainManager = $domainManager;
        $this->validator = $validator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $domainManager = $this->domainManager;

        $builder
            ->setAttribute('sharing_config', new \stdClass())
            ->addEventListener(FormEvents::PRE_SET_DATA, static function (FormEvent $event) use ($options, $domainManager): void {
                $dataIdentityName = null;
                $form = $event->getForm();

                /** @var null|SharingInterface $sharing */
                $sharing = $event->getData();

                /** @var \stdClass $config */
                $config = $form->getConfig()->getAttribute('sharing_config');
                $config->identityType = $options['identity_type'];
                $config->isUpdate = false;
                $config->identityValue = null;

                // for update sharing
                if (null !== $sharing
                        && null !== ($identityClass = $sharing->getIdentityClass())
                        && null !== ($identityName = $sharing->getIdentityName())) {
                    $config->isUpdate = true;
                    $config->domain = $domainManager->get($identityClass);
                    $interfaces = class_implements($config->domain->getClass());

                    if (\in_array(RoleInterface::class, $interfaces, true)) {
                        $config->identityType = 'role';
                        $identityName = OrganizationalUtil::format($identityName);
                    } elseif (\in_array(GroupInterface::class, $interfaces, true)) {
                        $config->identityType = 'group';
                        $identityName = OrganizationalUtil::format($identityName);
                    } else {
                        $config->identityType = 'user';
                    }

                    $dataIdentityName = $identityName;
                }

                // global config
                if ('role' === $config->identityType) {
                    $config->domain = $domainManager->get(RoleInterface::class);
                    $config->choiceLabel = 'label';
                    $config->choiceValue = 'name';
                } elseif ('group' === $config->identityType) {
                    $config->domain = $domainManager->get(GroupInterface::class);
                    $config->choiceLabel = 'label';
                    $config->choiceValue = 'name';
                } else {
                    $config->domain = $domainManager->get(UserInterface::class);
                    $config->choiceLabel = 'fullName';
                    $config->choiceValue = 'username';
                }

                // for update sharing
                if (null !== $dataIdentityName) {
                    $repository = $config->domain->getRepository();
                    $dataIdentityValue = $repository->findOneBy([
                        $config->choiceValue => $dataIdentityName,
                    ]);

                    if (null === $dataIdentityValue) {
                        $dataIdentityValue = $dataIdentityName;
                    }

                    $config->identityValue = [$dataIdentityValue];
                }
            })
            ->addEventListener(FormEvents::POST_SET_DATA, static function (FormEvent $event): void {
                $form = $event->getForm();

                if (!$form->has('identity_type') || !$form->has('identity')) {
                    return;
                }

                /** @var \stdClass $config */
                $config = $form->getConfig()->getAttribute('sharing_config');

                $form->get('identity_type')->setData($config->identityType);
                $form->get('identity')->setData($config->identityValue);
            })
            ->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) use ($options): void {
                /** @var SharingInterface $sharing */
                $sharing = $event->getData();

                /** @var IdInterface $subject */
                $subject = $options['subject'];
                $form = $event->getForm();

                if (!$form->has('identity_type') || !$form->has('identity')) {
                    return;
                }

                $identityForm = $form->get('identity');
                $identityType = $form->get('identity_type')->getData();

                /** @var null|ArrayCollection $data */
                $data = $identityForm->getData();

                /* @var null|GroupInterface|RoleInterface|string|UserInterface $identity */
                if ($data instanceof ArrayCollection) {
                    $identity = $data->count() > 0 ? $data->current() : null;
                } else {
                    $identity = \is_array($data) ? current($data) : $data;
                }

                $invitation = null;

                if ('role' === $identityType) {
                    $identityClass = $this->domainManager->get(RoleInterface::class)->getClass();
                    $identityName = $identity instanceof RoleInterface
                        ? OrganizationalUtil::formatNameWithOrg($identity->getRole(), $this->orgContext->getCurrentOrganization())
                        : 'empty';
                } elseif ('group' === $identityType) {
                    $identityClass = $this->domainManager->get(GroupInterface::class)->getClass();
                    $identityName = $identity instanceof GroupInterface
                        ? OrganizationalUtil::formatNameWithOrg($identity->getGroup(), $this->orgContext->getCurrentOrganization())
                        : 'empty';
                } else {
                    $domainUser = $this->domainManager->get(UserInterface::class);
                    $identityClass = $domainUser->getClass();

                    if (\is_string($identity)) {
                        /** @var UserRepositoryInterface $repoUser */
                        $repoUser = $domainUser->getRepository();
                        $foundUsers = $repoUser->findByUserIdentifierOrHavingEmails([$identity]);

                        if (isset($foundUsers[0]) && $foundUsers[0] instanceof UserInterface) {
                            $identity = $foundUsers[0];
                        }
                    }

                    if ($identity instanceof UserInterface) {
                        $identityConstraints = [new NotBlank()];
                        $identityName = $identity->getUserIdentifier();
                        $invitation = $identity->getEmail();
                    } else {
                        $identityConstraints = [new NotBlank(), new Email()];
                        $identityName = empty($identity) ? null : (string) $identity;
                        $invitation = $identityName;
                    }

                    foreach ($identityConstraints as $constraint) {
                        $violations = $this->validator->validate($identityName, $constraint);

                        if ($violations->count() > 0) {
                            for ($i = 0; $i < $violations->count(); ++$i) {
                                $violation = $violations->get($i);
                                $identityForm->addError(new FormError(
                                    $violation->getMessage(),
                                    $violation->getMessageTemplate(),
                                    $violation->getParameters(),
                                    $violation->getPlural(),
                                    $violation
                                ));
                            }
                        }
                    }
                }

                $sharing->setSubjectClass(\get_class($subject));
                $sharing->setSubjectId($subject->getId());
                $sharing->setIdentityClass($identityClass);
                $sharing->setIdentityName($identityName);

                if (method_exists($sharing, 'setInvitation')) {
                    $sharing->setInvitation($invitation);
                }
            })
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'subject' => null,
            'identity_type' => null,
        ]);

        $resolver->addAllowedTypes('data', SharingInterface::class);
        $resolver->addAllowedTypes('subject', IdInterface::class);

        $resolver->addAllowedTypes('identity_type', ['null', 'string']);
        $resolver->addAllowedValues('identity_type', [null, '', 'user', 'role', 'group']);
        $resolver->setNormalizer('identity_type', static function (Options $options, $value) {
            return null !== $value && '' !== $value ? $value : 'user';
        });
    }

    public function getBlockPrefix(): string
    {
        return 'sharing';
    }
}
