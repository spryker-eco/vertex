<?php

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerEco\Client\Vertex\Validator;

use Generated\Shared\Transfer\TaxamoApiRequestTransfer;
use Generated\Shared\Transfer\TaxIdValidationRequestTransfer;
use Generated\Shared\Transfer\TaxIdValidationResponseTransfer;
use Generated\Shared\Transfer\VertexApiResponseTransfer;
use SprykerEco\Client\Vertex\Api\V2\Client\TaxamoApi;

/**
 * This class validates a tax ID using the Vertex Validator API.
 */
class VertexTaxIdValidator
{
    /**
     * @var string
     */
    protected const ERROR_MESSAGE_INACTIVE_VERTEX_APP = 'Unable to connect to Vertex Validator API: Vertex App or Tax ID Validator is inactive.';

    /**
     * @var string
     */
    protected const ERROR_MESSAGE_KEY_INACTIVE_VERTEX_APP = 'validator-api-inactive';

    /**
     * @param \SprykerEco\Client\Vertex\Api\V2\Client\TaxamoApi $taxamoApi
     */
    public function __construct(protected TaxamoApi $taxamoApi)
    {
    }

    /**
     * @param \Generated\Shared\Transfer\TaxIdValidationRequestTransfer $taxIdValidationRequest
     * @param \Generated\Shared\Transfer\VertexConfigTransfer $vertexConfigTransfer
     *
     * @return \Generated\Shared\Transfer\TaxIdValidationResponseTransfer
     */
    public function validate(TaxIdValidationRequestTransfer $taxIdValidationRequest, VertexConfigTransfer $vertexConfigTransfer): TaxIdValidationResponseTransfer
    {
        if (!$vertexConfigTransfer->getIsActive() || !$vertexConfigTransfer->getIsTaxIdValidatorEnabled()) {
            return $this->createTaxIdValidationResponseTransfer(false, static::ERROR_MESSAGE_INACTIVE_VERTEX_APP);
        }

        $vertexApiResponse = $this->taxamoApi->validateTaxId(
            (new TaxamoApiRequestTransfer())
                ->fromArray($taxIdValidationRequest->toArray(), true)
                ->setTaxamoToken($vertexConfigTransfer->getTaxamoToken())
                ->setTaxamoApiUrl($vertexConfigTransfer->getTaxamoApiUrl()),
        );
        $errorMessage = $this->getValidationMessage($vertexApiResponse);
        $errorCode = $this->getErrorCode($vertexApiResponse);

        return $this->createTaxIdValidationResponseTransfer(
            $errorMessage ? false : $vertexApiResponse->getIsSuccessful(),
            $errorMessage,
            json_encode($vertexApiResponse->getVertexResponse()),
            $errorCode,
        );
    }

    /**
     * @param \Generated\Shared\Transfer\VertexApiResponseTransfer $vertexApiResponse
     *
     * @return string|null
     */
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

    /**
     * @param bool $isValid
     * @param string|null $message
     * @param string|null $additionalInfo
     *
     * @return \Generated\Shared\Transfer\TaxIdValidationResponseTransfer
     */
    protected function createTaxIdValidationResponseTransfer(
        bool $isValid = true,
        ?string $message = null,
        ?string $additionalInfo = null,
        ?string $errorCode = null
    ): TaxIdValidationResponseTransfer {
        $taxIdValidationResponseTransfer = (new TaxIdValidationResponseTransfer())->setIsValid($isValid);

        if ($message !== null) {
            $taxIdValidationResponseTransfer->setMessage($message);
        }

        if ($additionalInfo !== null) {
            $taxIdValidationResponseTransfer->setAdditionalInfo($additionalInfo);
        }

        if ($errorCode !== null) {
            $taxIdValidationResponseTransfer->setErrorCode($errorCode);
        }

        return $taxIdValidationResponseTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\VertexApiResponseTransfer $vertexApiResponse
     *
     * @return string|null
     */
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
