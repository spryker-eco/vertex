<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Zed\Vertex\Communication\Constraint;

use SprykerEco\Shared\Vertex\VertexConfig;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class VertexUrlConstraintValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof VertexUrlConstraint) {
            throw new UnexpectedTypeException($constraint, VertexUrlConstraint::class);
        }

        if (!is_string($value) || $value === '') {
            return;
        }

        if ($this->isPlantedSentinel($value)) {
            return;
        }

        if (filter_var($value, FILTER_VALIDATE_URL) !== false) {
            return;
        }

        $this->context->buildViolation($constraint->message)->addViolation();
    }

    protected function isPlantedSentinel(string $value): bool
    {
        return str_starts_with($value, VertexConfig::VERTEX_CONFIGURATION_INCOMPLETE_SENTINEL)
            || $value === VertexConfig::TAX_PROVIDER_NOT_CONFIGURED_SENTINEL;
    }
}
