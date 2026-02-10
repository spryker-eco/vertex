<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Client\Vertex\Validator;

use Generated\Shared\Transfer\TaxamoApiRequestTransfer;
use Generated\Shared\Transfer\TaxIdValidationRequestTransfer;
use Generated\Shared\Transfer\VertexApiResponseTransfer;
use Generated\Shared\Transfer\VertexConfigTransfer;
use Generated\Shared\Transfer\VertexValidationResponseTransfer;
use SprykerEco\Client\Vertex\Api\V2\Client\TaxamoApi;

/**
 * This class validates a tax ID using the Vertex Validator API.
 */
class VertexTaxIdValidator implements VertexTaxIdValidatorInterface
{
    protected const ERROR_MESSAGE_INACTIVE_VERTEX_APP = 'Unable to connect to Vertex Validator API: Vertex App or Tax ID Validator is inactive.';

    public function __construct(protected TaxamoApi $taxamoApi)
    {
    }

    public function validate(
        TaxIdValidationRequestTransfer $taxIdValidationRequest,
        VertexConfigTransfer $vertexConfigTransfer
    ): VertexValidationResponseTransfer {
        if (!$vertexConfigTransfer->getIsActive() || !$vertexConfigTransfer->getIsTaxIdValidatorEnabled()) {
            return $this->createVertexValidationResponseTransfer(false, static::ERROR_MESSAGE_INACTIVE_VERTEX_APP);
        }

        $vertexApiResponse = $this->taxamoApi->validateTaxId(
            (new TaxamoApiRequestTransfer())
                ->fromArray($taxIdValidationRequest->toArray(), true)
                ->setTaxamoToken($vertexConfigTransfer->getTaxamoToken())
                ->setTaxamoApiUrl($vertexConfigTransfer->getTaxamoApiUrl()),
        );
        $errorMessage = $this->getValidationMessage($vertexApiResponse);
        $errorCode = $this->getErrorCode($vertexApiResponse);

        return $this->createVertexValidationResponseTransfer(
            $errorMessage ? false : (bool)$vertexApiResponse->getIsSuccessful(),
            $errorMessage,
            json_encode($vertexApiResponse->getVertexResponse()) ?: null,
            $errorCode,
        );
    }

    protected function getValidationMessage(VertexApiResponseTransfer $vertexApiResponse): ?string
    {
        if ($vertexApiResponse->getIsSuccessful()) {
            $validationData = $vertexApiResponse->getVertexResponse();
            if (isset($validationData['buyer_tax_number_format_valid']) && $validationData['buyer_tax_number_format_valid'] === false) {
                return 'Wrong format of the tax number.';
            }

            if (isset($validationData['buyer_tax_number_valid']) && $validationData['buyer_tax_number_valid'] === false) {
                return 'Tax number mismatched or non-existent.';
            }
        }

        return $vertexApiResponse->getErrorMessage();
    }

    protected function createVertexValidationResponseTransfer(
        bool $isValid = true,
        ?string $message = null,
        ?string $additionalInfo = null,
        ?string $errorCode = null
    ): VertexValidationResponseTransfer {
        $vertexValidationResponseTransfer = (new VertexValidationResponseTransfer())->setIsValid($isValid);

        if ($message !== null) {
            $vertexValidationResponseTransfer->setMessage($message);
        }

        if ($additionalInfo !== null) {
            $vertexValidationResponseTransfer->setAdditionalInfo($additionalInfo);
        }

        if ($errorCode !== null) {
            $vertexValidationResponseTransfer->setMessageKey($errorCode);
        }

        return $vertexValidationResponseTransfer;
    }

    protected function getErrorCode(VertexApiResponseTransfer $vertexApiResponse): ?string
    {
        if (!$vertexApiResponse->getIsSuccessful()) {
            return $vertexApiResponse->getErrorCode();
        }
        $validationData = $vertexApiResponse->getVertexResponse();

        $hasFormatError = isset($validationData['buyer_tax_number_format_valid']) && $validationData['buyer_tax_number_format_valid'] === false;
        $hasValidationError = isset($validationData['buyer_tax_number_valid']) && $validationData['buyer_tax_number_valid'] === false;

        if (($hasFormatError || $hasValidationError) && isset($validationData['buyer_tax_number_validation_info'])) {
            return $validationData['buyer_tax_number_validation_info'];
        }

        return null;
    }
}
