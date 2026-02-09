<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Vertex\Business\Validator;

use Generated\Shared\Transfer\VertexConfigTransfer;
use Generated\Shared\Transfer\VertexValidationResponseTransfer;
use SprykerEco\Client\Vertex\VertexClientInterface;

class VertexConfigValidator
{
    protected const ERROR_FIELD_IS_REQUIRED = 'Field %s is required';

    protected const REQUEST_APPLICATION_STATUS_IS_ACTIVE = 'application_status_isActive';

    protected const RESPONSE_MESSAGE_BLANK_SECURITY_URI_FIELD = 'Security URI is required.';

    protected const RESPONSE_MESSAGE_TAXAMO_API_URL_FIELD = 'Api URI is required.';

    protected const RESPONSE_MESSAGE_NOT_VALID_URL_SECURITY_URI_FIELD = 'Security URI has wrong format.';

    protected const RESPONSE_MESSAGE_BLANK_TRANSACTION_CALLS_URI_FIELD = 'Transaction Calls URI is required.';

    protected const RESPONSE_MESSAGE_NOT_VALID_URL_TRANSACTION_CALLS_URI_FIELD = 'Transaction Calls URI has wrong format.';

    protected const RESPONSE_MESSAGE_NOT_VALID_URL_TAXAMO_API_URI_FIELD = 'Taxamo api url has wrong format.';

    protected const RESPONSE_MESSAGE_BLANK_CLIENT_ID_FIELD = 'Client ID is required.';

    protected const RESPONSE_MESSAGE_NOT_STRING_CLIENT_ID_FIELD = 'Client ID must be a string.';

    protected const RESPONSE_MESSAGE_BLANK_CLIENT_SECRET_FIELD = 'Client Secret is required.';

    protected const RESPONSE_MESSAGE_NOT_STRING_CLIENT_SECRET_FIELD = 'Client Secret must be a string.';

    protected const RESPONSE_MESSAGE_BLANK_TAXAMO_TOKEN_FIELD = 'Seller token is required.';

    public function __construct(protected VertexClientInterface $vertexClient) {}

    public function validate(VertexConfigTransfer $vertexConfigTransfer): VertexValidationResponseTransfer
    {
        $vertexValidationResponseTransfer = (new VertexValidationResponseTransfer());

        $this->validateSecurityUri($vertexConfigTransfer, $vertexValidationResponseTransfer);
        $this->validateTransactionCallsUri($vertexConfigTransfer, $vertexValidationResponseTransfer);
        $this->validateClientId($vertexConfigTransfer, $vertexValidationResponseTransfer);
        $this->validateClientSecret($vertexConfigTransfer, $vertexValidationResponseTransfer);
        $this->validateIsActive($vertexConfigTransfer, $vertexValidationResponseTransfer);
        $this->validateIsInvoicingEnabled($vertexConfigTransfer, $vertexValidationResponseTransfer);

        if ($vertexConfigTransfer->getIsTaxIdValidatorEnabled() === true) {
            $this->validateTaxamoApiUrl($vertexConfigTransfer, $vertexValidationResponseTransfer);
            $this->validateTaxamoToken($vertexConfigTransfer, $vertexValidationResponseTransfer);
        }

        $isValid = count($vertexValidationResponseTransfer->getMessages()) === 0;
        $vertexValidationResponseTransfer->setIsValid($isValid);

        return $vertexValidationResponseTransfer;
    }

    protected function validateSecurityUri(
        VertexConfigTransfer $vertexConfigTransfer,
        VertexValidationResponseTransfer $vertexValidationResponseTransfer
    ): void {
        if (!$vertexConfigTransfer->getSecurityUri()) {
            $vertexValidationResponseTransfer->addMessage(static::RESPONSE_MESSAGE_BLANK_SECURITY_URI_FIELD);

            return;
        }

        if (!filter_var($vertexConfigTransfer->getSecurityUri(), FILTER_VALIDATE_URL)) {
            $vertexValidationResponseTransfer->addMessage(static::RESPONSE_MESSAGE_NOT_VALID_URL_SECURITY_URI_FIELD);
        }
    }

    protected function validateTransactionCallsUri(
        VertexConfigTransfer $vertexConfigTransfer,
        VertexValidationResponseTransfer $vertexValidationResponseTransfer
    ): void {
        if (!$vertexConfigTransfer->getTransactionCallsUri()) {
            $vertexValidationResponseTransfer->addMessage(static::RESPONSE_MESSAGE_BLANK_TRANSACTION_CALLS_URI_FIELD);

            return;
        }

        if (!filter_var($vertexConfigTransfer->getTransactionCallsUri(), FILTER_VALIDATE_URL)) {
            $vertexValidationResponseTransfer->addMessage(static::RESPONSE_MESSAGE_NOT_VALID_URL_TRANSACTION_CALLS_URI_FIELD);
        }
    }

    protected function validateClientId(
        VertexConfigTransfer $vertexConfigTransfer,
        VertexValidationResponseTransfer $vertexValidationResponseTransfer
    ): void {
        if (!$vertexConfigTransfer->getClientId()) {
            $vertexValidationResponseTransfer->addMessage(static::RESPONSE_MESSAGE_BLANK_CLIENT_ID_FIELD);

            return;
        }

        if (!is_string($vertexConfigTransfer->getClientId())) {
            $vertexValidationResponseTransfer->addMessage(static::RESPONSE_MESSAGE_NOT_STRING_CLIENT_ID_FIELD);
        }
    }

    protected function validateClientSecret(
        VertexConfigTransfer $vertexConfigTransfer,
        VertexValidationResponseTransfer $vertexValidationResponseTransfer
    ): void {
        if (!$vertexConfigTransfer->getClientSecret()) {
            $vertexValidationResponseTransfer->addMessage(static::RESPONSE_MESSAGE_BLANK_CLIENT_SECRET_FIELD);

            return;
        }

        if (!is_string($vertexConfigTransfer->getClientSecret())) {
            $vertexValidationResponseTransfer->addMessage(static::RESPONSE_MESSAGE_NOT_STRING_CLIENT_SECRET_FIELD);
        }
    }

    protected function validateIsActive(
        VertexConfigTransfer $vertexConfigTransfer,
        VertexValidationResponseTransfer $vertexValidationResponseTransfer
    ): void {
        if ($vertexConfigTransfer->getIsActive() === null) {
            $vertexValidationResponseTransfer->addMessage(sprintf(static::ERROR_FIELD_IS_REQUIRED, static::REQUEST_APPLICATION_STATUS_IS_ACTIVE));
        }
    }

    protected function validateIsInvoicingEnabled(
        VertexConfigTransfer $vertexConfigTransfer,
        VertexValidationResponseTransfer $vertexValidationResponseTransfer
    ): void {
        if ($vertexConfigTransfer->getIsInvoicingEnabled() === null) {
            $vertexValidationResponseTransfer->addMessage(sprintf(static::ERROR_FIELD_IS_REQUIRED, 'isInvoicingEnabled'));
        }
    }

    protected function validateTaxamoApiUrl(
        VertexConfigTransfer $vertexConfigTransfer,
        VertexValidationResponseTransfer $vertexValidationResponseTransfer
    ): void {
        if (!$vertexConfigTransfer->getTaxamoApiUrl()) {
            $vertexValidationResponseTransfer->addMessage(static::RESPONSE_MESSAGE_TAXAMO_API_URL_FIELD);
        }

        if (!filter_var($vertexConfigTransfer->getTaxamoApiUrl(), FILTER_VALIDATE_URL)) {
            $vertexValidationResponseTransfer->addMessage(static::RESPONSE_MESSAGE_NOT_VALID_URL_TAXAMO_API_URI_FIELD);
        }
    }

    protected function validateTaxamoToken(
        VertexConfigTransfer $vertexConfigTransfer,
        VertexValidationResponseTransfer $vertexValidationResponseTransfer
    ): void {
        if (!$vertexConfigTransfer->getTaxamoToken()) {
            $vertexValidationResponseTransfer->addMessage(static::RESPONSE_MESSAGE_BLANK_TAXAMO_TOKEN_FIELD);
        }
    }
}
