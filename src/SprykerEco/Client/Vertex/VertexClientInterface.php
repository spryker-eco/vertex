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
use Generated\Shared\Transfer\TaxIdValidationResponseTransfer;
use Generated\Shared\Transfer\VertexApiResponseTransfer;
use Generated\Shared\Transfer\VertexConfigTransfer;
use Generated\Shared\Transfer\VertexAuthResponseTransfer;
use Generated\Shared\Transfer\VertexValidationRequestTransfer;
use Generated\Shared\Transfer\VertexValidationResponseTransfer;

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
     * - Performs tax `quotation` request to Vertex API.
     * - If VertexConfigTransfer.defaultTaxpayerCompanyCode is set for the requesting tenant, and no other value is defined, it will be used for tax quotation request.
     * - Requires VertexConfigTransfer.vertexAuthResponse.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\VertexCalculationRequestTransfer $vertexCalculationRequestTransfer
     * @param \Generated\Shared\Transfer\VertexConfigTransfer $vertexConfigTransfer
     *
     * @return \Generated\Shared\Transfer\VertexCalculationResponseTransfer
     */
    public function calculateTax(
        VertexCalculationRequestTransfer $vertexCalculationRequestTransfer,
        VertexConfigTransfer $vertexConfigTransfer
    ): VertexCalculationResponseTransfer;

    /**
     * Specification:
     * - Performs a request to validate a country's tax ID in the Vertex Validator API.
     * - Requires VertexConfigTransfer.vertexAuthResponse.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\TaxIdValidationRequestTransfer $taxIdValidationRequest
     * @param \Generated\Shared\Transfer\VertexConfigTransfer $vertexConfigTransfer
     *
     * @return \Generated\Shared\Transfer\TaxIdValidationResponseTransfer
     */
    public function validateTaxId(
        TaxIdValidationRequestTransfer $taxIdValidationRequest,
        VertexConfigTransfer $vertexConfigTransfer
    ): TaxIdValidationResponseTransfer;

    /**
     * Specification:
     * - Makes Zed request.
     * - Validates Tax id for specific country.
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
     * Specification
     * - Sends Api request to validate a country's tax ID in the Vertex Validator API.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\TaxamoApiRequestTransfer $taxamoApiRequest
     *
     * @return \Generated\Shared\Transfer\VertexApiResponseTransfer
     */
    public function sendValidationApiRequestTaxId(TaxamoApiRequestTransfer $taxamoApiRequest): VertexApiResponseTransfer;
}
