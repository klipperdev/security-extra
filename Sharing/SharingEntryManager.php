<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\SecurityExtra\Sharing;

use Klipper\Component\DoctrineExtensions\Util\SqlFilterUtil;
use Klipper\Component\DoctrineExtra\Util\ClassUtils;
use Klipper\Component\Resource\Domain\DomainManagerInterface;
use Klipper\Component\Security\Exception\InvalidArgumentException;
use Klipper\Component\Security\Identity\SubjectInterface;
use Klipper\Component\Security\Model\RoleInterface;
use Klipper\Component\Security\Model\SharingInterface;
use Klipper\Component\Security\Model\Traits\RoleableInterface;
use Klipper\Component\Security\Model\UserInterface;
use Klipper\Component\Security\Organizational\OrganizationalUtil;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * The Sharing Entry Manager.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class SharingEntryManager implements SharingEntryManagerInterface
{
    protected DomainManagerInterface $domainManager;

    protected ?SharingEntryFactoryInterface $factory;

    /**
     * @var SharingEntryLabelBuilderInterface[]
     */
    protected array $labelBuilders;

    protected ?string $roleClass = null;

    /**
     * @var SharingEntryConfigInterface[]
     */
    protected array $configs = [];

    private PropertyAccessorInterface $propertyAccessor;

    private bool $initialized = false;

    /**
     * @param DomainManagerInterface              $domainManager    The domain manager
     * @param null|SharingEntryFactoryInterface   $factory          The sharing entry factory
     * @param SharingEntryLabelBuilderInterface[] $labelBuilders    The sharing entry label builders
     * @param null|PropertyAccessorInterface      $propertyAccessor The property access
     */
    public function __construct(
        DomainManagerInterface $domainManager,
        ?SharingEntryFactoryInterface $factory = null,
        array $labelBuilders = [],
        ?PropertyAccessorInterface $propertyAccessor = null
    ) {
        $this->domainManager = $domainManager;
        $this->factory = $factory;
        $this->labelBuilders = $labelBuilders;
        $this->propertyAccessor = $propertyAccessor ?? PropertyAccess::createPropertyAccessor();
    }

    public function setConfig(SharingEntryConfigInterface $config): self
    {
        $this->configs[$config->getType()] = $config;

        return $this;
    }

    public function getSharingEntries(array $sharings): array
    {
        $this->init();
        $finds = $this->findObjects($sharings);
        $finds = $this->findRoles($sharings, $finds);
        $entries = [];

        /** @var SharingInterface[] $sharings */
        foreach ($sharings as $sharing) {
            if (!isset($finds[$sharing->getSubjectClass()][$sharing->getSubjectId()])) {
                $msg = 'The "%s" subject with the id "%s" does not exist';

                throw new InvalidArgumentException(sprintf($msg, $sharing->getSubjectClass(), $sharing->getSubjectId()));
            }

            $identityName = OrganizationalUtil::format($sharing->getIdentityName());

            if (isset($finds[$sharing->getIdentityClass()][$identityName])) {
                $identity = $finds[$sharing->getIdentityClass()][$identityName];
            } else {
                /** @var UserInterface $identity */
                $identity = $this->domainManager->get(UserInterface::class)->newInstance();

                if (method_exists($identity, 'setEmail')) {
                    $identity->setEmail($sharing->getIdentityName());
                }
            }

            $entries[] = new SharingEntry(
                $sharing,
                $finds[$sharing->getSubjectClass()][$sharing->getSubjectId()],
                $identity,
                $this->buildLabel($identity, $sharing),
                $this->getSharingRoles($finds, $sharing)
            );
        }

        return $entries;
    }

    /**
     * Build the sharing entry label.
     *
     * @param object           $identity The identity instance
     * @param SharingInterface $sharing  The sharing
     */
    protected function buildLabel(object $identity, SharingInterface $sharing): string
    {
        foreach ($this->labelBuilders as $labelBuilder) {
            if ($labelBuilder->supports($identity, $sharing)) {
                return $labelBuilder->buildLabel($identity, $sharing);
            }
        }

        throw new InvalidArgumentException(sprintf(
            'The sharing entry label cannot be build for the identity "%s"',
            ClassUtils::getClass($identity)
        ));
    }

    /**
     * Find the subject and identity of sharings.
     *
     * @param SharingInterface[] $sharings The sharings
     */
    private function findObjects(array $sharings): array
    {
        $map = $this->buildMap($sharings);
        $finds = [];

        foreach ($map as $type => $ids) {
            $result = $this->findBy($type, $ids);

            foreach ($result as $object) {
                $finds[$type][$this->getIdentifier($object)] = $object;
            }
        }

        return $finds;
    }

    /**
     * Get the roles of sharing.
     *
     * @param array            $findMap The map of found objects
     * @param SharingInterface $sharing The sharing
     *
     * @return RoleInterface[]
     */
    private function getSharingRoles(array $findMap, SharingInterface $sharing): array
    {
        $roleClass = $this->getRoleClass();
        $sharingRoles = [];

        foreach ($sharing->getRoles() as $role) {
            if (\in_array($role, ['ROLE_USER', 'ROLE_ORGANIZATION_USER'], true)) {
                continue;
            }

            $role = $role instanceof RoleInterface ? $role->getName() : $role;

            if (isset($findMap[$roleClass][$role])) {
                $sharingRoles[] = $findMap[$roleClass][$role];
            } else {
                $msg = 'The role "%s" does not exist';

                throw new InvalidArgumentException(sprintf($msg, $role));
            }
        }

        return $sharingRoles;
    }

    /**
     * Find and add the role instances.
     *
     * @param SharingInterface[] $sharings The sharings
     * @param array              $findMap  The map of found objects
     */
    private function findRoles(array $sharings, array $findMap): array
    {
        $roleClass = $this->getRoleClass();
        $findRoles = [];

        foreach ($findMap as $class => $objects) {
            foreach ($objects as $object) {
                if ($object instanceof RoleableInterface) {
                    foreach ($object->getRoles() as $role) {
                        $findRoles[] = $role instanceof RoleInterface ? [$role->getName()] : [$role];
                    }
                }
            }
        }

        foreach ($sharings as $sharing) {
            $findRoles[] = $sharing->getRoles();
        }

        if (!empty($findRoles)) {
            $result = $this->findBy($roleClass, array_unique(array_merge(...$findRoles)));

            /** @var RoleInterface[] $result */
            foreach ($result as $role) {
                $findMap[$roleClass][$role->getName()] = $role;
            }
        }

        return $findMap;
    }

    /**
     * Build the map of classes and ids.
     *
     * @param SharingInterface[] $sharings The sharings
     */
    private function buildMap(array $sharings): array
    {
        $map = [];

        foreach ($sharings as $sharing) {
            $map[$sharing->getSubjectClass()][] = $sharing->getSubjectId();
            $map[$sharing->getIdentityClass()][] = OrganizationalUtil::format($sharing->getIdentityName());
        }

        foreach ($map as $key => &$values) {
            $values = array_unique($values);
        }

        return $map;
    }

    /**
     * Find by.
     *
     * @param string         $type The class name
     * @param int[]|string[] $ids  The ids
     *
     * @return object[]
     */
    private function findBy(string $type, array $ids): array
    {
        $em = $this->domainManager->get($type)->getObjectManager();
        $repo = $em->getRepository($type);
        $fieldName = $this->getIdentifierName($type);
        $repoMethod = $this->getRepositoryMethod($type);

        $filters = SqlFilterUtil::findFilters($em, ['userable']);
        SqlFilterUtil::disableFilters($em, $filters);

        $result = null !== $repoMethod
            ? $repo->{$repoMethod}(array_unique($ids))
            : $repo->findBy([$fieldName => array_unique($ids)]);

        SqlFilterUtil::enableFilters($em, $filters);

        return $result;
    }

    /**
     * Get the name of repository method.
     *
     * @param string $type The class name
     */
    private function getRepositoryMethod(string $type): ?string
    {
        $repoMethod = null;

        if (isset($this->configs[$type])) {
            $repoMethod = $this->configs[$type]->getRepositoryMethod();
        }

        return $repoMethod;
    }

    /**
     * Get the field name of identifier.
     *
     * @param string $type The class name
     */
    private function getIdentifierName(string $type): string
    {
        if (isset($this->configs[$type])) {
            return $this->configs[$type]->getField();
        }

        return 'id';
    }

    /**
     * Get the value of object identifier.
     *
     * @param object $object The object
     *
     * @return int|string
     */
    private function getIdentifier(object $object)
    {
        $type = ClassUtils::getClass($object);

        if (isset($this->configs[$type])) {
            $fieldName = $this->configs[$type]->getField();

            return $this->propertyAccessor->getValue($object, $fieldName);
        }

        if ($object instanceof SubjectInterface) {
            return $object->getSubjectIdentifier();
        }

        if (method_exists($object, 'getId')) {
            return $object->getId();
        }

        throw new InvalidArgumentException('The object must either implement the SubjectInterface, or have a method named "getId"');
    }

    /**
     * Get the role class name.
     */
    private function getRoleClass(): string
    {
        if (null === $this->roleClass) {
            $this->roleClass = $this->domainManager->get(RoleInterface::class)->getClass();
        }

        return $this->roleClass;
    }

    /**
     * Initialize the configurations.
     */
    private function init(): void
    {
        if (!$this->initialized) {
            $this->initialized = true;

            if (null !== $this->factory) {
                foreach ($this->factory->createConfigurations() as $config) {
                    $this->setConfig($config);
                }
            }
        }
    }
}
