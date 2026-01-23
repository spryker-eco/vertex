<?php

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerEco\Client\Vertex\MessageBroker;

use Generated\Shared\Transfer\SubmitPaymentTaxInvoiceResponseTransfer;
use Generated\Shared\Transfer\SubmitPaymentTaxInvoiceTransfer;
use Generated\Shared\Transfer\TaxCalculationRequestTransfer;
use Generated\Shared\Transfer\VertexConfigTransfer;
use Spryker\Shared\Log\LoggerTrait;
use SprykerEco\Client\Vertex\TaxCalculator\VertexTaxCalculatorInterface;

class VertexApiMessageHandler implements VertexApiMessageHandlerInterface // TODO: remove
{
    use LoggerTrait;

    /**
     * @param \SprykerEco\Client\Vertex\TaxCalculator\VertexTaxCalculatorInterface $vertexTaxCalculator
     */
    public function __construct(protected VertexTaxCalculatorInterface $vertexTaxCalculator)
    {
    }

    /**
     * @param \Generated\Shared\Transfer\SubmitPaymentTaxInvoiceTransfer $submitPaymentTaxInvoiceTransfer
     * @param \Generated\Shared\Transfer\VertexConfigTransfer $vertexConfigTransfer
     *
     * @return \Generated\Shared\Transfer\SubmitPaymentTaxInvoiceResponseTransfer
     */
    public function handleSubmitPaymentTaxInvoice(
        SubmitPaymentTaxInvoiceTransfer $submitPaymentTaxInvoiceTransfer,
        VertexConfigTransfer $vertexConfigTransfer
    ): SubmitPaymentTaxInvoiceResponseTransfer {
        $storeReference = $submitPaymentTaxInvoiceTransfer->getMessageAttributes()->getStoreReferenceOrFail();
        //TODO: check $storeReference, where to get it from?

        $submitPaymentTaxInvoiceResponseTransfer = (new SubmitPaymentTaxInvoiceResponseTransfer())->setIsSuccessful(false);

        if (!$this->shouldHandleSubmitTaxInvoice($vertexConfigTransfer)) { // TODO: move to zed
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
        $taxCalculationResponseTransfer = $this->vertexTaxCalculator->calculateTax($taxCalculationRequestTransfer, $vertexConfigTransfer);

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
