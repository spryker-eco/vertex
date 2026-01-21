<?php

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerEco\Client\Vertex\Refund;

use Generated\Shared\Transfer\TaxCalculationRequestTransfer;
use Generated\Shared\Transfer\TaxCalculationResponseTransfer;
use SprykerEco\Client\Vertex\TaxCalculator\VertexTaxCalculatorInterface;

class VertexApiRefunds implements VertexApiRefundsInterface
{
    /**
     * @param \SprykerEco\Client\Vertex\TaxCalculator\VertexTaxCalculatorInterface $vertexTaxCalculator
     */
    public function __construct(protected VertexTaxCalculatorInterface $vertexTaxCalculator)
    {
    }

    /**
     * @param \Generated\Shared\Transfer\TaxCalculationRequestTransfer $taxCalculationRequestTransfer
     * @param \Generated\Shared\Transfer\VertexConfigTransfer $vertexConfigTransfer
     *
     * @return \Generated\Shared\Transfer\TaxCalculationResponseTransfer
     */
    public function submitVoidPaymentTaxInvoice(
        TaxCalculationRequestTransfer $taxCalculationRequestTransfer,
        VertexConfigTransfer $vertexConfigTransfer
    ): TaxCalculationResponseTransfer {
        if (!$vertexConfigTransfer->getIsActive() || !$vertexConfigTransfer->getIsInvoicingEnabled()) {
            $taxCalculationResponseTransfer = new TaxCalculationResponseTransfer();
            $taxCalculationResponseTransfer->setIsSuccessful(false);
            $taxCalculationResponseTransfer->setErrorMessage('App is Inactive or configured to not submit void invoice');

            return $taxCalculationResponseTransfer;
        }

        return $this->vertexTaxCalculator->calculateTax($taxCalculationRequestTransfer, $vertexConfigTransfer);
    }
}
