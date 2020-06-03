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

use Klipper\Component\SecurityExtra\Exception\InvalidArgumentException;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class SharingEntryConfig implements SharingEntryConfigInterface
{
    private string $type;

    private string $field;

    private ?string $repositoryMethod;

    /**
     * @param string      $type             The type. Typically, this is the PHP class name
     * @param string      $field            The field name to be using to find the instances
     * @param null|string $repositoryMethod The name of repository method
     */
    public function __construct(string $type, string $field, ?string $repositoryMethod = null)
    {
        $this->type = $type;
        $this->field = $field;
        $this->repositoryMethod = $repositoryMethod;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function getRepositoryMethod(): ?string
    {
        return $this->repositoryMethod;
    }

    public function merge(SharingEntryConfigInterface $newConfig): void
    {
        if ($this->getType() !== $newConfig->getType()) {
            throw new InvalidArgumentException(sprintf(
                'The sharing entry config of "%s" can be merged only with the same type, given: "%s"',
                $this->getType(),
                $newConfig->getType()
            ));
        }

        $this->field = $newConfig->getField();

        if (null !== $newRepoMethod = $newConfig->getRepositoryMethod()) {
            $this->repositoryMethod = $newRepoMethod;
        }
    }
}
