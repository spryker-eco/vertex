<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerEco\Glue\Vertex\Api\Storefront\Exception;

use Spryker\ApiPlatform\Exception\GlueApiException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Builds {@see GlueApiException} for the tax-id-validate endpoint. Mirrors the status codes and
 * (locale-translated) detail messages the legacy
 * {@see \SprykerEco\Glue\Vertex\Processor\Validator\TaxIdValidator} emitted; the legacy Spryker
 * `code` was unset, so we keep the JSON:API `code` empty here too.
 */
class VertexExceptionFactory
{
    /**
     * @var string
     */
    protected const string RESPONSE_CODE_EMPTY = '';

    public function createVertexDisabledException(string $detail): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_BAD_REQUEST,
            static::RESPONSE_CODE_EMPTY,
            $detail,
        );
    }

    public function createInvalidRequestDataException(string $detail): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            static::RESPONSE_CODE_EMPTY,
            $detail,
        );
    }

    public function createValidationFailedException(string $detail): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_BAD_REQUEST,
            static::RESPONSE_CODE_EMPTY,
            $detail,
        );
    }

    public function createValidationFailedWithoutMessageException(): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            static::RESPONSE_CODE_EMPTY,
        );
    }
}
