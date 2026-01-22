<?php

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerEco\Client\Vertex;

use Generated\Shared\Transfer\SubmitPaymentTaxInvoiceResponseTransfer;
use Generated\Shared\Transfer\SubmitPaymentTaxInvoiceTransfer;
use Generated\Shared\Transfer\TaxamoApiRequestTransfer;
use Generated\Shared\Transfer\TaxCalculationRequestTransfer;
use Generated\Shared\Transfer\TaxCalculationResponseTransfer;
use Generated\Shared\Transfer\TaxIdValidationRequestTransfer;
use Generated\Shared\Transfer\TaxIdValidationResponseTransfer;
use Generated\Shared\Transfer\VertexApiResponseTransfer;
use Generated\Shared\Transfer\VertexConfigTransfer;
use Generated\Shared\Transfer\VertexAuthResponseTransfer;

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
     * @param \Generated\Shared\Transfer\TaxCalculationRequestTransfer $taxCalculationRequestTransfer
     * @param \Generated\Shared\Transfer\VertexConfigTransfer $vertexConfigTransfer
     *
     * @return \Generated\Shared\Transfer\TaxCalculationResponseTransfer
     */
    public function calculateTax(
        TaxCalculationRequestTransfer $taxCalculationRequestTransfer,
        VertexConfigTransfer $vertexConfigTransfer
    ): TaxCalculationResponseTransfer;

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

    /**
     * Specification:
     * - See documentation for {@link self::calculateTax() }
     * - Performs an `invoice` call against Vertex API instead of a `quotation` call.
     * - Requires VertexConfigTransfer.vertexAuthResponse.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\SubmitPaymentTaxInvoiceTransfer $submitPaymentTaxInvoiceTransfer
     * @param \Generated\Shared\Transfer\VertexConfigTransfer $vertexConfigTransfer
     *
     * @return \Generated\Shared\Transfer\SubmitPaymentTaxInvoiceResponseTransfer
     */
    public function handleSubmitPaymentTaxInvoice(
        SubmitPaymentTaxInvoiceTransfer $submitPaymentTaxInvoiceTransfer,
        VertexConfigTransfer $vertexConfigTransfer
    ): SubmitPaymentTaxInvoiceResponseTransfer;

    /**
     * @api
     *
     * @param \Generated\Shared\Transfer\TaxCalculationRequestTransfer $taxCalculationRequestTransfer
     * @param \Generated\Shared\Transfer\VertexConfigTransfer $vertexConfigTransfer
     *
     * @return \Generated\Shared\Transfer\TaxCalculationResponseTransfer
     */
    public function submitVoidPaymentTaxInvoice(
        TaxCalculationRequestTransfer $taxCalculationRequestTransfer,
        VertexConfigTransfer $vertexConfigTransfer
    ): TaxCalculationResponseTransfer;
}
