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
use Generated\Shared\Transfer\VertexApiAuthResponseTransfer;
use Generated\Shared\Transfer\VertexApiResponseTransfer;
use Generated\Shared\Transfer\VertexConfigTransfer;
use Spryker\Client\Kernel\AbstractClient;

/**
 * @method \Pyz\Zed\VertexApi\Business\VertexApiBusinessFactory getFactory()
 * @method \Pyz\Zed\VertexApi\Persistence\VertexApiRepositoryInterface getRepository()
 * @method \Pyz\Zed\VertexApi\Persistence\VertexApiEntityManagerInterface getEntityManager()
 */
class VertexClient extends AbstractClient implements VertexClientInterface
{
    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\VertexConfigTransfer $vertexConfigTransfer
     *
     * @return \Generated\Shared\Transfer\VertexAuthResponseTransfer
     */
    public function authenticate(VertexConfigTransfer $vertexConfigTransfer): VertexAuthResponseTransfer
    {
        return $this->getFactory()->createVertexApiAuthenticator()->authenticate($vertexConfigTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\TaxCalculationRequestTransfer $taxCalculationRequestTransfer
     *
     * @return \Generated\Shared\Transfer\TaxCalculationResponseTransfer
     */
    public function calculateTax(TaxCalculationRequestTransfer $taxCalculationRequestTransfer): TaxCalculationResponseTransfer
    {
        return $this->getFactory()->createVertexTaxCalculator()->calculateTax($taxCalculationRequestTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\TaxIdValidationRequestTransfer $taxIdValidationRequest
     *
     * @return \Generated\Shared\Transfer\TaxIdValidationResponseTransfer
     */
    public function validateTaxId(TaxIdValidationRequestTransfer $taxIdValidationRequest): TaxIdValidationResponseTransfer
    {
        return $this->getFactory()->createVertexTaxIdValidator()->validate($taxIdValidationRequest);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\TaxamoApiRequestTransfer $taxamoApiRequest
     *
     * @return \Generated\Shared\Transfer\VertexApiResponseTransfer
     */
    public function sendValidationApiRequestTaxId(TaxamoApiRequestTransfer $taxamoApiRequest): VertexApiResponseTransfer
    {
        return $this->getFactory()->createTaxamoApi()->validateTaxId($taxamoApiRequest);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\SubmitPaymentTaxInvoiceTransfer $submitPaymentTaxInvoiceTransfer
     *
     * @return \Generated\Shared\Transfer\SubmitPaymentTaxInvoiceResponseTransfer
     */
    public function handleSubmitPaymentTaxInvoice(
        SubmitPaymentTaxInvoiceTransfer $submitPaymentTaxInvoiceTransfer
    ): SubmitPaymentTaxInvoiceResponseTransfer {
        return $this->getFactory()->createVertexApiMessageHandler()->handleSubmitPaymentTaxInvoice($submitPaymentTaxInvoiceTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\TaxCalculationRequestTransfer $taxCalculationRequestTransfer
     *
     * @return \Generated\Shared\Transfer\TaxCalculationResponseTransfer
     */
    public function submitVoidPaymentTaxInvoice(TaxCalculationRequestTransfer $taxCalculationRequestTransfer): TaxCalculationResponseTransfer
    {
        return $this->getFactory()->createVertexApiRefunds()->submitVoidPaymentTaxInvoice($taxCalculationRequestTransfer);
    }
}
