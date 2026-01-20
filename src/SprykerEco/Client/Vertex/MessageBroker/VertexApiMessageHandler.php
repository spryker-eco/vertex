<?php

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Pyz\Zed\VertexApi\Business\MessageBroker;

use Generated\Shared\Transfer\SubmitPaymentTaxInvoiceResponseTransfer;
use Generated\Shared\Transfer\SubmitPaymentTaxInvoiceTransfer;
use Generated\Shared\Transfer\TaxCalculationRequestTransfer;
use Generated\Shared\Transfer\VertexConfigCriteriaTransfer;
use Generated\Shared\Transfer\VertexConfigTransfer;
use Pyz\Zed\VertexApi\Business\TaxCalculator\VertexTaxCalculatorInterface;
use Pyz\Zed\VertexConfig\Business\VertexConfigFacadeInterface;
use Spryker\Shared\Log\LoggerTrait;

class VertexApiMessageHandler implements VertexApiMessageHandlerInterface
{
    use LoggerTrait;

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
     * @param \Generated\Shared\Transfer\SubmitPaymentTaxInvoiceTransfer $submitPaymentTaxInvoiceTransfer
     *
     * @return \Generated\Shared\Transfer\SubmitPaymentTaxInvoiceResponseTransfer
     */
    public function handleSubmitPaymentTaxInvoice(SubmitPaymentTaxInvoiceTransfer $submitPaymentTaxInvoiceTransfer): SubmitPaymentTaxInvoiceResponseTransfer
    {
        $storeReference = $submitPaymentTaxInvoiceTransfer->getMessageAttributes()->getStoreReferenceOrFail();

        $vertexConfigCriteriaTransfer = (new VertexConfigCriteriaTransfer())
            ->setStoreReference($storeReference);
        $vertexConfigTransfer = $this->vertexConfigFacade->getConfig($vertexConfigCriteriaTransfer);

        $submitPaymentTaxInvoiceResponseTransfer = (new SubmitPaymentTaxInvoiceResponseTransfer())->setIsSuccessful(false);

        if (!$this->shouldHandleSubmitTaxInvoice($vertexConfigTransfer)) {
            $infoMessage = sprintf('[Vertex] App is Inactive or configured to not submit invoice for Store Reference %s, message discarded.', $submitPaymentTaxInvoiceTransfer->getMessageAttributes()->getStoreReference());
            $submitPaymentTaxInvoiceResponseTransfer->setInfoMessage($infoMessage);
            $this->getLogger()->warning($infoMessage);

            return $submitPaymentTaxInvoiceResponseTransfer;
        }

        $taxCalculationRequestTransfer = new TaxCalculationRequestTransfer();
        $taxCalculationRequestTransfer->setSale($submitPaymentTaxInvoiceTransfer->getSaleOrFail());
        $taxCalculationRequestTransfer->setStoreReference($storeReference);
        $taxCalculationRequestTransfer->setTenantIdentifier($storeReference);

        $this->getLogger()->info(
            'Starting tax calculation request for invoicing process',
            [
                'transactionId' => $taxCalculationRequestTransfer->getSale()->getTransactionId(),
                'requestTransfer' => $taxCalculationRequestTransfer->modifiedToArray(),
            ],
        );
        $taxCalculationResponseTransfer = $this->vertexTaxCalculator->calculateTax($taxCalculationRequestTransfer);

        $this->getLogger()->info(
            'Finished tax calculation request for invoicing process',
            [
                'transactionId' => $taxCalculationRequestTransfer->getSale()->getTransactionId(),
                'responseTransfer' => $taxCalculationResponseTransfer->modifiedToArray(),
            ],
        );
        $submitPaymentTaxInvoiceResponseTransfer
            ->setIsSuccessful($taxCalculationResponseTransfer->getIsSuccessful())
            ->setErrorMessage($taxCalculationResponseTransfer->getErrorMessage());

        return $submitPaymentTaxInvoiceResponseTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\VertexConfigTransfer $vertexConfigTransfer
     *
     * @return bool
     */
    protected function shouldHandleSubmitTaxInvoice(VertexConfigTransfer $vertexConfigTransfer): bool
    {
        return $vertexConfigTransfer->getIsActive() && $vertexConfigTransfer->getIsInvoicingEnabled();
    }
}
