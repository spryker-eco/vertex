<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Client\Vertex\ResponseBuilder;

use Generated\Shared\Transfer\VertexApiResponseTransfer;
use Generated\Shared\Transfer\VertexCalculationRequestTransfer;
use Generated\Shared\Transfer\VertexCalculationResponseTransfer;
use SprykerEco\Client\Vertex\Builder\PriceConverter;

class VertexSuppliesResponseBuilder implements VertexSuppliesResponseBuilderInterface
{
    public function __construct(protected PriceConverter $priceConverter)
    {
    }

    /**
     * @param \Generated\Shared\Transfer\VertexApiResponseTransfer $vertexApiResponseTransfer
     * @param \Generated\Shared\Transfer\VertexCalculationRequestTransfer $vertexCalculationRequestTransfer
     * @param array<string, string> $lineItemIdToInitialIdentifierMapping
     *
     * @return \Generated\Shared\Transfer\VertexCalculationResponseTransfer
     */
    public function buildResponse(
        VertexApiResponseTransfer $vertexApiResponseTransfer,
        VertexCalculationRequestTransfer $vertexCalculationRequestTransfer,
        array $lineItemIdToInitialIdentifierMapping,
    ): VertexCalculationResponseTransfer {
        $vertexResponse = $vertexApiResponseTransfer->getVertexResponse();

        $lineItemTaxes = $this->getLineItemTaxesIndexedByLineItemId($vertexResponse, $lineItemIdToInitialIdentifierMapping);

        $vertexSaleTransfer = $vertexCalculationRequestTransfer->getSale();

        if (!$vertexSaleTransfer) {
            return (new VertexCalculationResponseTransfer())
                ->setIsSuccessful(false)
                ->setErrorMessage('Vertex sale transfer is missing');
        }

        // Total tax calculated for the order
        $totalTax = $vertexResponse['data']['totalTax'];

        // "Refunded" tax amount - this is the amount which will be subtracted from tax invoice after refund. Negative value.
        $refundedTaxTotal = 0;

        foreach ($vertexSaleTransfer->getItems() as $item) {
            $itemId = $item->getIdOrFail();
            if (!isset($lineItemTaxes[$itemId])) {
                continue;
            }

            if ($lineItemTaxes[$itemId] < 0) {
                $refundedTaxTotal = $refundedTaxTotal + $lineItemTaxes[$itemId];

                $item->setRefundedTaxTotal((int)$this->priceConverter->convertPriceForSpryker($lineItemTaxes[$itemId]));
                $item->setTaxTotal((int)$this->priceConverter->convertPriceForSpryker(0));

                continue;
            }

            $item->setTaxTotal((int)$this->priceConverter->convertPriceForSpryker($lineItemTaxes[$itemId]));
        }

        foreach ($vertexSaleTransfer->getShipments() as $shipment) {
            $shipmentId = $shipment->getIdOrFail();
            if (!isset($lineItemTaxes[$shipmentId])) {
                continue;
            }

            if ($lineItemTaxes[$shipmentId] < 0) {
                $refundedTaxTotal = $refundedTaxTotal + $lineItemTaxes[$shipmentId];

                $shipment->setRefundedTaxTotal((int)$this->priceConverter->convertPriceForSpryker($lineItemTaxes[$shipmentId]));
                $shipment->setTaxTotal((int)$this->priceConverter->convertPriceForSpryker(0));

                continue;
            }

            $shipment->setTaxTotal((int)$this->priceConverter->convertPriceForSpryker($lineItemTaxes[$shipmentId]));
        }

        // It is still necessary to return the correct total tax amount for the order including the refunded tax amount.
        $taxTotal = $totalTax - $refundedTaxTotal;

        $vertexSaleTransfer->setTaxTotal((int)$this->priceConverter->convertPriceForSpryker($taxTotal));
        $vertexSaleTransfer->setRefundedTaxTotal((int)$this->priceConverter->convertPriceForSpryker($refundedTaxTotal));

        return (new VertexCalculationResponseTransfer())
            ->setSale($vertexSaleTransfer)
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

    public function buildErrorResponse(
        VertexCalculationRequestTransfer $vertexCalculationRequestTransfer,
        string $errorMessage,
    ): VertexCalculationResponseTransfer {
        return (new VertexCalculationResponseTransfer())
            ->setSale($vertexCalculationRequestTransfer->getSale())
            ->setIsSuccessful(false)
            ->setErrorMessage($errorMessage);
    }
}
