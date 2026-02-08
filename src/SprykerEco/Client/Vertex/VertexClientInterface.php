<?php

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerEco\Client\Vertex;

use Generated\Shared\Transfer\SubmitPaymentTaxInvoiceResponseTransfer;
use Generated\Shared\Transfer\VertexSubmitPaymentTaxInvoiceTransfer;
use Generated\Shared\Transfer\TaxamoApiRequestTransfer;
use Generated\Shared\Transfer\VertexCalculationRequestTransfer;
use Generated\Shared\Transfer\VertexCalculationResponseTransfer;
use Generated\Shared\Transfer\TaxIdValidationRequestTransfer;
use Generated\Shared\Transfer\VertexValidationResponseTransfer;
use Generated\Shared\Transfer\VertexApiResponseTransfer;
use Generated\Shared\Transfer\VertexConfigTransfer;
use Generated\Shared\Transfer\VertexAuthResponseTransfer;
use Generated\Shared\Transfer\VertexValidationRequestTransfer;

interface VertexClientInterface
{
    /**
     * Specification:
     * - Performs authentication with Vertex API.
     * - Requires VertexConfigTransfer.clientId.
     * - Requires VertexConfigTransfer.clientSecret.
     * - Requires VertexConfigTransfer.securityUri.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\VertexConfigTransfer $vertexConfigTransfer
     *
     * @return \Generated\Shared\Transfer\VertexAuthResponseTransfer
     */
    public function authenticate(VertexConfigTransfer $vertexConfigTransfer): VertexAuthResponseTransfer;

    /**
     * Specification:
     * - Calculates tax for a quotation using Vertex API.
     * - Validates the calculation request using VertexQuotationValidator.
     * - Requires VertexConfigTransfer.vertexAuthResponse with valid access token.
     * - Requires VertexCalculationRequestTransfer.sale with valid sale data.
     * - Sends request to Vertex API and returns tax calculation results.
     * - Returns VertexCalculationResponseTransfer with calculated tax amounts.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\VertexCalculationRequestTransfer $vertexCalculationRequestTransfer
     * @param \Generated\Shared\Transfer\VertexConfigTransfer $vertexConfigTransfer
     *
     * @return \Generated\Shared\Transfer\VertexCalculationResponseTransfer
     */
    public function calculateQuoteTax(
        VertexCalculationRequestTransfer $vertexCalculationRequestTransfer,
        VertexConfigTransfer $vertexConfigTransfer
    ): VertexCalculationResponseTransfer;

    /**
     * Specification:
     * - Calculates tax for an invoice using Vertex API.
     * - Validates the calculation request using VertexInvoiceValidator.
     * - Requires VertexConfigTransfer.vertexAuthResponse with valid access token.
     * - Requires VertexCalculationRequestTransfer.reportingDate to be set.
     * - Requires VertexCalculationRequestTransfer.sale with valid sale data.
     * - Sends request to Vertex API and returns tax calculation results.
     * - Returns VertexCalculationResponseTransfer with calculated tax amounts.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\VertexCalculationRequestTransfer $vertexCalculationRequestTransfer
     * @param \Generated\Shared\Transfer\VertexConfigTransfer $vertexConfigTransfer
     *
     * @return \Generated\Shared\Transfer\VertexCalculationResponseTransfer
     */
    public function calculateOrderTax(
        VertexCalculationRequestTransfer $vertexCalculationRequestTransfer,
        VertexConfigTransfer $vertexConfigTransfer
    ): VertexCalculationResponseTransfer;

    /**
     * Specification:
     * - Performs a request to validate a country's tax ID using the Vertex Validator API (Taxamo).
     * - Requires VertexConfigTransfer.isActive to be true.
     * - Requires VertexConfigTransfer.isTaxIdValidatorEnabled to be true.
     * - Returns VertexValidationResponseTransfer with validation result, error messages, and additional info.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\TaxIdValidationRequestTransfer $taxIdValidationRequest
     * @param \Generated\Shared\Transfer\VertexConfigTransfer $vertexConfigTransfer
     *
     * @return \Generated\Shared\Transfer\VertexValidationResponseTransfer
     */
    public function validateTaxId(
        TaxIdValidationRequestTransfer $taxIdValidationRequest,
        VertexConfigTransfer $vertexConfigTransfer
    ): VertexValidationResponseTransfer;

    /**
     * Specification:
     * - Makes a Zed (server-side) request to validate a tax ID for a specific country.
     * - Delegates to the Zed layer which handles the validation logic and configuration.
     * - Returns VertexValidationResponseTransfer with validation result and error messages.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\VertexValidationRequestTransfer $vertexValidationRequestTransfer
     *
     * @return \Generated\Shared\Transfer\VertexValidationResponseTransfer
     */
    public function requestTaxIdValidation(
        VertexValidationRequestTransfer $vertexValidationRequestTransfer
    ): VertexValidationResponseTransfer;

    /**
     * Specification:
     * TODO: remove?
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\TaxamoApiRequestTransfer $taxamoApiRequest
     *
     * @return \Generated\Shared\Transfer\VertexApiResponseTransfer
     */
    public function sendValidationApiRequestTaxId(TaxamoApiRequestTransfer $taxamoApiRequest): VertexApiResponseTransfer;
}
