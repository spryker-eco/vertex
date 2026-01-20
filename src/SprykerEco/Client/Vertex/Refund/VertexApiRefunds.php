<?php

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Pyz\Zed\VertexApi\Business\Refund;

use Generated\Shared\Transfer\TaxCalculationRequestTransfer;
use Generated\Shared\Transfer\TaxCalculationResponseTransfer;
use Generated\Shared\Transfer\VertexConfigCriteriaTransfer;
use Pyz\Zed\VertexApi\Business\TaxCalculator\VertexTaxCalculatorInterface;
use Pyz\Zed\VertexConfig\Business\VertexConfigFacadeInterface;

class VertexApiRefunds implements VertexApiRefundsInterface
{
    protected VertexConfigFacadeInterface $vertexConfigFacade;

    protected VertexTaxCalculatorInterface $vertexTaxCalculator;

    /**
     * @param \Pyz\Zed\VertexConfig\Business\VertexConfigFacadeInterface $vertexConfigFacade
     * @param \Pyz\Zed\VertexApi\Business\TaxCalculator\VertexTaxCalculatorInterface $vertexTaxCalculator
     */
    public function __construct(VertexConfigFacadeInterface $vertexConfigFacade, VertexTaxCalculatorInterface $vertexTaxCalculator)
    {
        $this->vertexConfigFacade = $vertexConfigFacade;
        $this->vertexTaxCalculator = $vertexTaxCalculator;
    }

    /**
     * @inheritDoc
     */
    public function submitVoidPaymentTaxInvoice(TaxCalculationRequestTransfer $taxCalculationRequestTransfer): TaxCalculationResponseTransfer
    {
        $vertexConfigCriteriaTransfer = (new VertexConfigCriteriaTransfer())
            ->setStoreReference($taxCalculationRequestTransfer->getTenantIdentifierOrFail());

        $vertexConfigTransfer = $this->vertexConfigFacade->getConfig($vertexConfigCriteriaTransfer);

        if (!$vertexConfigTransfer->getIsActive() || !$vertexConfigTransfer->getIsInvoicingEnabled()) {
            $taxCalculationResponseTransfer = new TaxCalculationResponseTransfer();
            $taxCalculationResponseTransfer->setIsSuccessful(false);
            $taxCalculationResponseTransfer->setErrorMessage('App is Inactive or configured to not submit void invoice');

            return $taxCalculationResponseTransfer;
        }

        return $this->vertexTaxCalculator->calculateTax($taxCalculationRequestTransfer);
    }
}
