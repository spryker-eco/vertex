<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Vertex\Business\Aggregator;

use ArrayObject;
use Generated\Shared\Transfer\CalculableObjectTransfer;
use Generated\Shared\Transfer\ExpenseTransfer;
use Generated\Shared\Transfer\ItemTransfer;
use Generated\Shared\Transfer\VertexItemTransfer;
use Generated\Shared\Transfer\VertexSaleTransfer;
use Generated\Shared\Transfer\VertexShipmentTransfer;
use SprykerEco\Zed\Vertex\Business\Mapper\Prices\ItemExpensePriceRetriever;
use SprykerEco\Zed\Vertex\Business\Mapper\VertexMapper;

class PriceAggregator implements PriceAggregatorInterface
{
    public function calculatePriceAggregation(
        VertexSaleTransfer $VertexSaleTransfer,
        CalculableObjectTransfer $calculableObjectTransfer
    ): CalculableObjectTransfer {
        $this->calculateTaxAmountFullAggregationAndPriceToPayAggregationForItems($VertexSaleTransfer, $calculableObjectTransfer);
        $this->calculatePriceToPayAggregationForExpenses($VertexSaleTransfer, $calculableObjectTransfer);

        return $calculableObjectTransfer;
    }

    protected function calculateTaxAmountFullAggregationAndPriceToPayAggregationForItems(
        VertexSaleTransfer $VertexSaleTransfer,
        CalculableObjectTransfer $calculableObjectTransfer
    ): CalculableObjectTransfer {
        $indexedQuoteItems = $this->getItemsIndexedBySkuAndItemIndex($calculableObjectTransfer->getItems());

        /** @var \Generated\Shared\Transfer\VertexItemTransfer $vertexSaleItem */
        foreach ($VertexSaleTransfer->getItems() as $vertexSaleItem) {
            if (!isset($indexedQuoteItems[$vertexSaleItem->getId()])) {
                continue;
            }

            $indexedQuoteItems[$vertexSaleItem->getId()] = $this->calculateTaxAmountFullAggregationForItem($indexedQuoteItems[$vertexSaleItem->getId()], $vertexSaleItem);
            $indexedQuoteItems[$vertexSaleItem->getId()] = $this->calculatePriceToPayAggregationForItem($indexedQuoteItems[$vertexSaleItem->getId()], $calculableObjectTransfer->getPriceModeOrFail());
            $indexedQuoteItems[$vertexSaleItem->getId()]->setTaxRateAverageAggregation(0);
        }

        return $calculableObjectTransfer;
    }

    protected function calculateTaxAmountFullAggregationForItem(ItemTransfer $quoteItem, VertexItemTransfer $VertexItemTransfer): ItemTransfer
    {
        $saleItemQuantity = $this->getItemQuantity($VertexItemTransfer);

        $taxTotal = (int)$VertexItemTransfer->getTaxTotal();

        if ($VertexItemTransfer->getRefundedTaxTotal()) {
            $taxTotal = $VertexItemTransfer->getRefundedTaxTotal();
            $quoteItem->setTaxAmountAfterCancellation($VertexItemTransfer->getRefundedTaxTotal());
        }

        $quoteItem->setUnitTaxAmount((int)round($taxTotal / $saleItemQuantity));
        $quoteItem->setSumTaxAmount($taxTotal);
        // TaxAmountFullAggregation includes ProductOption taxes which are not supported by Vertex module.
        $quoteItem->setUnitTaxAmountFullAggregation((int)round($taxTotal / $saleItemQuantity));
        $quoteItem->setSumTaxAmountFullAggregation($taxTotal);

        return $quoteItem;
    }

    protected function getItemQuantity(VertexItemTransfer $VertexItemTransfer): int
    {
        if (!$VertexItemTransfer->getVertexShippingWarehouses()->count()) {
            return $VertexItemTransfer->getQuantityOrFail();
        }

        $quantity = 0;
        foreach ($VertexItemTransfer->getVertexShippingWarehouses() as $warehouseMapping) {
            $quantity = $quantity + $warehouseMapping->getQuantity();
        }

        return $quantity;
    }

    protected function calculatePriceToPayAggregationForItem(ItemTransfer $itemTransfer, string $priceMode): ItemTransfer
    {
        $itemTransfer->requireSumSubtotalAggregation()
            ->requireUnitSubtotalAggregation();

        $itemTransfer->setUnitPriceToPayAggregation(
            $this->calculatePriceToPayAggregation(
                $itemTransfer->getUnitSubtotalAggregationOrFail(),
                $priceMode,
                $itemTransfer->getUnitDiscountAmountAggregation() ?? 0,
                $itemTransfer->getUnitTaxAmountFullAggregation() ?? 0,
            ),
        );

        $itemTransfer->setSumPriceToPayAggregation(
            $this->calculatePriceToPayAggregation(
                $itemTransfer->getSumSubtotalAggregationOrFail(),
                $priceMode,
                $itemTransfer->getSumDiscountAmountFullAggregation() ?? 0,
                $itemTransfer->getSumTaxAmountFullAggregation() ?? 0,
            ),
        );

        return $itemTransfer;
    }

    /**
     * @param \ArrayObject<int, \Generated\Shared\Transfer\ItemTransfer> $itemTransfers
     *
     * @return array<string, \Generated\Shared\Transfer\ItemTransfer>
     */
    protected function getItemsIndexedBySkuAndItemIndex(ArrayObject $itemTransfers): array
    {
        $indexedItems = [];
        foreach ($itemTransfers as $itemIndex => $itemTransfer) {
            $indexedItems[sprintf('%s_%s', $itemTransfer->getSku(), $itemIndex)] = $itemTransfer;
        }

        return $indexedItems;
    }

    protected function calculatePriceToPayAggregationForExpenses(
        VertexSaleTransfer $VertexSaleTransfer,
        CalculableObjectTransfer $calculableObjectTransfer
    ): CalculableObjectTransfer {
        $calculableObjectTransfer = $this->preDefineTaxAmount($calculableObjectTransfer);
        $indexedExpenses = $this->filterShipmentExpenses($calculableObjectTransfer->getExpenses());

        foreach ($VertexSaleTransfer->getShipments() as $VertexShipmentTransfer) {
            if (!isset($indexedExpenses[$VertexShipmentTransfer->getId()])) {
                continue;
            }

            $indexedExpenses[$VertexShipmentTransfer->getId()] = $this->calculateTaxAmountForExpense($indexedExpenses[$VertexShipmentTransfer->getId()], $VertexShipmentTransfer);
            $indexedExpenses[$VertexShipmentTransfer->getId()] = $this->calculatePriceToPayAggregationForExpense($indexedExpenses[$VertexShipmentTransfer->getId()], $calculableObjectTransfer->getPriceModeOrFail());
        }

        return $calculableObjectTransfer;
    }

    protected function preDefineTaxAmount(CalculableObjectTransfer $calculableObjectTransfer): CalculableObjectTransfer
    {
        foreach ($calculableObjectTransfer->getExpenses() as $expenseTransfer) {
            if ($expenseTransfer->getSumTaxAmount() === null) {
                $expenseTransfer->setSumTaxAmount(0);
            }
            if ($expenseTransfer->getUnitTaxAmount() === null) {
                $expenseTransfer->setUnitTaxAmount(0);
            }
        }

        return $calculableObjectTransfer;
    }

    protected function calculateTaxAmountForExpense(ExpenseTransfer $expenseTransfer, VertexShipmentTransfer $VertexShipmentTransfer): ExpenseTransfer
    {
        if ($VertexShipmentTransfer->getRefundedTaxTotal()) {
            $expenseTransfer->setTaxAmountAfterCancellation($VertexShipmentTransfer->getRefundedTaxTotal());

            return $expenseTransfer;
        }

        $expenseTransfer->setUnitTaxAmount($VertexShipmentTransfer->getTaxTotal() ?? 0);
        $expenseTransfer->setSumTaxAmount($VertexShipmentTransfer->getTaxTotal() ?? 0);

        return $expenseTransfer;
    }

    protected function calculatePriceToPayAggregationForExpense(ExpenseTransfer $expenseTransfer, string $priceMode): ExpenseTransfer
    {
        $expenseTransfer->setUnitPriceToPayAggregation(
            $this->calculatePriceToPayAggregation(
                $expenseTransfer->getUnitPriceOrFail(),
                $priceMode,
                $expenseTransfer->getUnitDiscountAmountAggregation() ?? 0,
                $expenseTransfer->getUnitTaxAmount() ?? 0,
            ),
        );

        $expenseTransfer->setSumPriceToPayAggregation(
            $this->calculatePriceToPayAggregation(
                $expenseTransfer->getSumPriceOrFail(),
                $priceMode,
                $expenseTransfer->getSumDiscountAmountAggregation() ?? 0,
                $expenseTransfer->getSumTaxAmount() ?? 0,
            ),
        );

        return $expenseTransfer;
    }

    /**
     * @param \ArrayObject<string, \Generated\Shared\Transfer\ExpenseTransfer> $expenseTransfers
     *
     * @return array<string, \Generated\Shared\Transfer\ExpenseTransfer>
     */
    protected function filterShipmentExpenses(ArrayObject $expenseTransfers): array
    {
        $indexedExpenses = [];
        foreach ($expenseTransfers as $hash => $expenseTransfer) {
            if ($expenseTransfer->getType() !== VertexMapper::SHIPMENT_EXPENSE_TYPE) {
                continue;
            }

            $indexedExpenses[$hash] = $expenseTransfer;
        }

        return $indexedExpenses;
    }

    protected function calculatePriceToPayAggregation(int $price, string $priceMode, int $discountAmount = 0, int $taxAmount = 0): int
    {
        if ($priceMode === ItemExpensePriceRetriever::PRICE_MODE_NET) {
            return $price + $taxAmount - $discountAmount;
        }

        return $price - $discountAmount;
    }
}
