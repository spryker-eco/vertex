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

class VertexTaxProviderConstraintValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof VertexTaxProviderConstraint) {
            throw new UnexpectedTypeException($constraint, VertexTaxProviderConstraint::class);
        }

        if (!is_string($value) || !str_starts_with($value, VertexConfig::TAX_PROVIDER_NOT_CONFIGURED_SENTINEL)) {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->setParameter('{{ reasons }}', $this->formatReasons($this->decodeReasons($value)))
            ->addViolation();
    }

    /**
     * @return array<string>
     */
    protected function decodeReasons(string $value): array
    {
        $encodedReasons = substr($value, strlen(VertexConfig::TAX_PROVIDER_NOT_CONFIGURED_SENTINEL));

        if ($encodedReasons === '') {
            return [];
        }

        $reasons = json_decode($encodedReasons, true);

        return is_array($reasons) ? array_values(array_map('strval', $reasons)) : [];
    }

    /**
     * @param array<string> $reasons
     */
    protected function formatReasons(array $reasons): string
    {
        return implode(' ', $reasons);
    }
}
