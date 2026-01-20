<?php

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Pyz\Zed\VertexApi\Business\ResponseBuilder;

use Generated\Shared\Transfer\TaxCalculationRequestTransfer;
use Generated\Shared\Transfer\TaxCalculationResponseTransfer;
use Generated\Shared\Transfer\VertexApiResponseTransfer;
use Pyz\Zed\VertexApi\Business\Builder\PriceConverter;

class VertexSuppliesResponseBuilder implements VertexSuppliesResponseBuilderInterface
{
    protected PriceConverter $priceConverter;

    /**
     * @param \Pyz\Zed\VertexApi\Business\Builder\PriceConverter $priceConverter
     */
    public function __construct(PriceConverter $priceConverter)
    {
        $this->priceConverter = $priceConverter;
    }

    /**
     * @param \Generated\Shared\Transfer\VertexApiResponseTransfer $vertexApiResponseTransfer
     * @param \Generated\Shared\Transfer\TaxCalculationRequestTransfer $taxCalculationRequestTransfer
     * @param array<string, string> $lineItemIdToInitialIdentifierMapping
     *
     * @return \Generated\Shared\Transfer\TaxCalculationResponseTransfer
     */
    public function buildResponse(
        VertexApiResponseTransfer $vertexApiResponseTransfer,
        TaxCalculationRequestTransfer $taxCalculationRequestTransfer,
        array $lineItemIdToInitialIdentifierMapping
    ): TaxCalculationResponseTransfer {
        $vertexResponse = $vertexApiResponseTransfer->getVertexResponse();

        $lineItemTaxes = $this->getLineItemTaxesIndexedByLineItemId($vertexResponse, $lineItemIdToInitialIdentifierMapping);

        $saleTransfer = $taxCalculationRequestTransfer->getSale();

        // Total tax calculated for the order
        $totalTax = $vertexResponse['data']['totalTax'];

        // "Refunded" tax amount - this is the amount which will be subtracted from tax invoice after refund. Negative value.
        $refundedTaxTotal = 0;

        foreach ($saleTransfer->getItems() as $item) {
            $itemId = $item->getIdOrFail();
            if (!isset($lineItemTaxes[$itemId])) {
                continue;
            }

            if ($lineItemTaxes[$itemId] < 0) {
                $refundedTaxTotal = $refundedTaxTotal + $lineItemTaxes[$itemId];

                $item->setRefundedTaxTotal($this->priceConverter->convertPriceForSpryker($lineItemTaxes[$itemId]));
                $item->setTaxTotal($this->priceConverter->convertPriceForSpryker(0));

                continue;
            }

            $item->setTaxTotal($this->priceConverter->convertPriceForSpryker($lineItemTaxes[$itemId]));
        }

        foreach ($saleTransfer->getShipments() as $shipment) {
            $shipmentId = $shipment->getIdOrFail();
            if (!isset($lineItemTaxes[$shipmentId])) {
                continue;
            }

            if ($lineItemTaxes[$shipmentId] < 0) {
                $refundedTaxTotal = $refundedTaxTotal + $lineItemTaxes[$shipmentId];

                $shipment->setRefundedTaxTotal($this->priceConverter->convertPriceForSpryker($lineItemTaxes[$shipmentId]));
                $shipment->setTaxTotal($this->priceConverter->convertPriceForSpryker(0));

                continue;
            }

            $shipment->setTaxTotal($this->priceConverter->convertPriceForSpryker($lineItemTaxes[$shipmentId]));
        }

        // It is still necessary to return the correct total tax amount for the order including the refunded tax amount.
        $taxTotal = $totalTax - $refundedTaxTotal;

        $saleTransfer->setTaxTotal($this->priceConverter->convertPriceForSpryker($taxTotal));
        $saleTransfer->setRefundedTaxTotal($this->priceConverter->convertPriceForSpryker($refundedTaxTotal));

        return (new TaxCalculationResponseTransfer())
            ->setSale($saleTransfer)
            ->setIsSuccessful(true);
    }

    /**
     * @param array<string, mixed> $vertexResponse
     * @param array<string, string> $lineItemIdToInitialIdentifierMapping
     *
     * @return array<string, int>
     */
    protected function getLineItemTaxesIndexedByLineItemId(array $vertexResponse, array $lineItemIdToInitialIdentifierMapping): array
    {
        if (!isset($vertexResponse['data']['lineItems'])) {
            return [];
        }

        $lineItemTaxes = [];
        foreach ($vertexResponse['data']['lineItems'] as $lineItem) {
            $lineItemId = $lineItemIdToInitialIdentifierMapping[$lineItem['lineItemId']] ?? $lineItem['lineItemId'];

            if (!isset($lineItemTaxes[$lineItemId])) {
                $lineItemTaxes[$lineItemId] = 0;
            }

            $lineItemTaxes[$lineItemId] = $lineItemTaxes[$lineItemId] + $lineItem['totalTax'];
        }

        return $lineItemTaxes;
    }

    /**
     * @param \Generated\Shared\Transfer\TaxCalculationRequestTransfer $taxCalculationRequestTransfer
     * @param string $errorMessage
     *
     * @return \Generated\Shared\Transfer\TaxCalculationResponseTransfer
     */
    public function buildErrorResponse(
        TaxCalculationRequestTransfer $taxCalculationRequestTransfer,
        string $errorMessage
    ): TaxCalculationResponseTransfer {
        return (new TaxCalculationResponseTransfer())
            ->setSale($taxCalculationRequestTransfer->getSale())
            ->setIsSuccessful(false)
            ->setErrorMessage($errorMessage);
    }
}
