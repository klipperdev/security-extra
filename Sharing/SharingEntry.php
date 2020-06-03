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

use Klipper\Component\DoctrineExtra\Util\ClassUtils;
use Klipper\Component\Security\Exception\UnexpectedTypeException;
use Klipper\Component\Security\Model\PermissionInterface;
use Klipper\Component\Security\Model\RoleInterface;
use Klipper\Component\Security\Model\SharingInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class SharingEntry implements SharingEntryInterface
{
    /**
     * @var int|string
     */
    protected $id;

    protected SharingInterface $sharing;

    protected object $subject;

    protected object $identity;

    protected string $label;

    /**
     * @var RoleInterface[]
     */
    protected array $roles;

    protected string $type;

    protected bool $enabled;

    /**
     * @var PermissionInterface[]
     */
    protected array $permissions;

    protected ?\DateTime $startedAt;

    protected ?\DateTime $endedAt;

    protected bool $active;

    /**
     * @param SharingInterface $sharing  The sharing
     * @param object           $subject  The subject
     * @param object           $identity The identity
     * @param string           $label    The label
     * @param RoleInterface[]  $roles    The roles
     */
    public function __construct(
        SharingInterface $sharing,
        object $subject,
        object $identity,
        string $label,
        array $roles = []
    ) {
        $this->validate($sharing, $subject, $identity);

        $isInvitation = method_exists($sharing, 'isInvitation') && $sharing->isInvitation();

        $this->id = $sharing->getId();
        $this->sharing = $sharing;
        $this->subject = $subject;
        $this->identity = $identity;
        $this->label = $label;
        $this->roles = $roles;
        $this->type = substr(ClassUtils::getClass($identity), strrpos(ClassUtils::getClass($identity), '\\') + 1);
        $this->type = $isInvitation ? 'invitation' : strtolower($this->type);
        $this->enabled = $sharing->isEnabled();
        $this->permissions = $sharing->getPermissions();
        $this->startedAt = $sharing->getStartedAt();
        $this->endedAt = $sharing->getEndedAt();
        $this->active = $this->buildActive($this->enabled, $this->startedAt, $this->endedAt, $isInvitation);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getSharing(): SharingInterface
    {
        return $this->sharing;
    }

    public function getSubject(): object
    {
        return $this->subject;
    }

    public function getIdentity(): object
    {
        return $this->identity;
    }

    public function getIdentityClass(): string
    {
        return ClassUtils::getClass($this->identity);
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function getPermissions(): array
    {
        return $this->permissions;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getRoleNames(): array
    {
        $roles = [];

        foreach ($this->roles as $role) {
            $roles[] = $role->getRole();
        }

        return $roles;
    }

    public function getStartedAt(): ?\DateTime
    {
        return $this->startedAt;
    }

    public function getEndedAt(): ?\DateTime
    {
        return $this->endedAt;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * Validate the subject and identity.
     *
     * @param SharingInterface $sharing  The sharing
     * @param object           $subject  The subject
     * @param object           $identity The identity
     */
    private function validate(SharingInterface $sharing, object $subject, object $identity): void
    {
        if (!$this->isInstanceOf($subject, $sharing->getSubjectClass())) {
            throw new UnexpectedTypeException($subject, $sharing->getSubjectClass());
        }

        if (!$this->isInstanceOf($identity, $sharing->getIdentityClass())) {
            throw new UnexpectedTypeException($identity, $sharing->getIdentityClass());
        }
    }

    /**
     * Check the object is instance of the class name.
     *
     * @param object|string $object The object instance or the class name
     * @param string        $class  The class name
     */
    private function isInstanceOf($object, string $class): bool
    {
        return (\is_object($object) && $class === ClassUtils::getClass($object))
            || is_subclass_of($object, $class)
            || \in_array($class, class_implements($object), true);
    }

    /**
     * Build the active value.
     *
     * @param bool                    $enabled      Check if the sharing is enabled
     * @param null|\DateTimeInterface $startedAt    The started datetime
     * @param null|\DateTimeInterface $endedAt      The ended datetime
     * @param bool                    $isInvitation Check if it's an invitation
     *
     * @throws
     */
    private function buildActive(bool $enabled, ?\DateTimeInterface $startedAt, ?\DateTimeInterface $endedAt, bool $isInvitation = false): bool
    {
        $now = new \DateTime();

        $started = null === $startedAt || $startedAt <= $now;
        $ended = null !== $endedAt && $endedAt < $now;

        return $enabled && $started && !$ended && !$isInvitation;
    }
}
