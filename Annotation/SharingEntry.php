<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\SecurityExtra\Annotation;

use Klipper\Component\Config\Annotation\AbstractAnnotation;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @Annotation
 * @Target({"CLASS"})
 */
class SharingEntry extends AbstractAnnotation
{
    /**
     * @var string
     *
     * @Required
     */
    protected $field;

    /**
     * @var null|string
     *
     * @Required
     */
    protected $repositoryMethod;

    public function getField(): string
    {
        return $this->field;
    }

    public function setField(string $field): void
    {
        $this->field = $field;
    }

    public function getRepositoryMethod(): ?string
    {
        return $this->repositoryMethod;
    }

    public function setRepositoryMethod(?string $repositoryMethod): void
    {
        $this->repositoryMethod = $repositoryMethod;
    }
}
