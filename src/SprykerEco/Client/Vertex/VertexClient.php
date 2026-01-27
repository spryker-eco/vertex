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
use Generated\Shared\Transfer\VertexValidationRequestTransfer;
use Generated\Shared\Transfer\VertexValidationResponseTransfer;
use Spryker\Client\Kernel\AbstractClient;

/**
 * @method \SprykerEco\Client\Vertex\VertexFactory getFactory()
 * @method \SprykerEco\Client\Vertex\VertexConfig getConfig()
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
        return $this->getFactory()
            ->createVertexApiAuthenticator()
            ->authenticate($vertexConfigTransfer);
    }

    /**
     * {@inheritDoc}
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
    ): TaxCalculationResponseTransfer {
        return $this->getFactory()
            ->createVertexTaxCalculator()
            ->calculateTax($taxCalculationRequestTransfer, $vertexConfigTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\TaxIdValidationRequestTransfer $taxIdValidationRequest
     * @param \Generated\Shared\Transfer\VertexConfigTransfer $vertexConfigTransfer
     *
     * @return \Generated\Shared\Transfer\TaxIdValidationResponseTransfer
     */
    public function validateTaxId(TaxIdValidationRequestTransfer $taxIdValidationRequest, VertexConfigTransfer $vertexConfigTransfer): TaxIdValidationResponseTransfer
    {
        return $this->getFactory()
            ->createVertexTaxIdValidator()
            ->validate($taxIdValidationRequest, $vertexConfigTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\VertexValidationRequestTransfer $vertexValidationRequestTransfer
     *
     * @return \Generated\Shared\Transfer\VertexValidationResponseTransfer
     */
    public function requestTaxIdValidation(
        VertexValidationRequestTransfer $vertexValidationRequestTransfer
    ): VertexValidationResponseTransfer {
        return $this->getFactory()
            ->createZedStub()
            ->requestTaxIdValidation($vertexValidationRequestTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\TaxamoApiRequestTransfer $taxamoApiRequestTransfer
     *
     * @return \Generated\Shared\Transfer\VertexApiResponseTransfer
     */
    public function sendValidationApiRequestTaxId(TaxamoApiRequestTransfer $taxamoApiRequestTransfer): VertexApiResponseTransfer
    {
        return $this->getFactory()
            ->createTaxamoApi()
            ->validateTaxId($taxamoApiRequestTransfer);
    }
}
