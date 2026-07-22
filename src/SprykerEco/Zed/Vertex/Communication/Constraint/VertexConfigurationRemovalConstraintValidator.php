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

class VertexConfigurationRemovalConstraintValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof VertexConfigurationRemovalConstraint) {
            throw new UnexpectedTypeException($constraint, VertexConfigurationRemovalConstraint::class);
        }

        if (!is_string($value)) {
            return;
        }

        $payload = $this->decodeIncompleteSentinel($value);

        if ($payload === null) {
            return;
        }

        $message = $payload[VertexConfig::VERTEX_CONFIGURATION_INCOMPLETE_PAYLOAD_KEY_CASE] === VertexConfig::VERTEX_CONFIGURATION_INCOMPLETE_CASE_CROSS_SCOPE
            ? $constraint->crossScopeMessage
            : $constraint->message;

        $this->context->buildViolation($message)
            ->setParameter('{{ scope }}', $payload[VertexConfig::VERTEX_CONFIGURATION_INCOMPLETE_PAYLOAD_KEY_SCOPE])
            ->setParameter('{{ reasons }}', $this->formatReasons($payload[VertexConfig::VERTEX_CONFIGURATION_INCOMPLETE_PAYLOAD_KEY_REASONS]))
            ->addViolation();
    }

    /**
     * Decodes the incomplete sentinel planted during pre-save into its case, scope label and validation reasons.
     * Returns null for any value that is not an incomplete sentinel, so real user input passes through untouched.
     *
     * @return array{case: string, scope: string, reasons: array<string>}|null
     */
    protected function decodeIncompleteSentinel(string $value): ?array
    {
        if (!str_starts_with($value, VertexConfig::VERTEX_CONFIGURATION_INCOMPLETE_SENTINEL)) {
            return null;
        }

        $encodedPayload = substr($value, strlen(VertexConfig::VERTEX_CONFIGURATION_INCOMPLETE_SENTINEL));
        $payload = $encodedPayload === '' ? [] : json_decode($encodedPayload, true);

        if (!is_array($payload)) {
            $payload = [];
        }

        $reasons = $payload[VertexConfig::VERTEX_CONFIGURATION_INCOMPLETE_PAYLOAD_KEY_REASONS] ?? [];

        return [
            VertexConfig::VERTEX_CONFIGURATION_INCOMPLETE_PAYLOAD_KEY_CASE => (string)($payload[VertexConfig::VERTEX_CONFIGURATION_INCOMPLETE_PAYLOAD_KEY_CASE] ?? VertexConfig::VERTEX_CONFIGURATION_INCOMPLETE_CASE_REMOVAL),
            VertexConfig::VERTEX_CONFIGURATION_INCOMPLETE_PAYLOAD_KEY_SCOPE => (string)($payload[VertexConfig::VERTEX_CONFIGURATION_INCOMPLETE_PAYLOAD_KEY_SCOPE] ?? ''),
            VertexConfig::VERTEX_CONFIGURATION_INCOMPLETE_PAYLOAD_KEY_REASONS => is_array($reasons) ? array_values(array_map('strval', $reasons)) : [],
        ];
    }

    /**
     * @param array<string> $reasons
     */
    protected function formatReasons(array $reasons): string
    {
        return implode(' ', $reasons);
    }
}
