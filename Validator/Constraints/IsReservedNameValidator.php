<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\SecurityExtra\Validator\Constraints;

use Klipper\Component\SecurityExtra\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\ConstraintViolation;

/**
 * Reserved name validator.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class IsReservedNameValidator extends ConstraintValidator
{
    protected array $globalReservedNames;

    /**
     * @param array $globalReservedNames The global reserved names
     */
    public function __construct(array $globalReservedNames = [])
    {
        $this->globalReservedNames = $globalReservedNames;
    }

    /**
     * Checks if the passed value is valid.
     *
     * @param mixed      $value      The value that should be validated
     * @param Constraint $constraint The constraint for the validation
     *
     * @throws UnexpectedTypeException
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof IsReservedName) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\IsReservedName');
        }

        if (null !== $value && !\is_string($value)) {
            throw new UnexpectedTypeException($value, 'string');
        }

        if (\in_array(mb_strtolower((string) $value), $this->globalReservedNames, false)) {
            $msg = $constraint->message;
            $params = ['%name%' => $value];
            $violation = new ConstraintViolation(
                $msg,
                $msg,
                $params,
                $this->context->getRoot(),
                $this->context->getPropertyPath(),
                null
            );
            $this->context->getViolations()->add($violation);
        }
    }
}
