<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Vertex\Business;

use Generated\Shared\Transfer\CalculableObjectTransfer;
use Generated\Shared\Transfer\OrderTransfer;
use Generated\Shared\Transfer\VertexConfigCriteriaTransfer;
use Generated\Shared\Transfer\VertexConfigTransfer;
use Generated\Shared\Transfer\VertexValidationRequestTransfer;
use Generated\Shared\Transfer\VertexValidationResponseTransfer;

interface VertexFacadeInterface
{
    /**
     * Specification:
     * - Requires `CalculableObject.store.name`, `CalculableObject.priceMode` to be set.
     * - Executes {@link \Spryker\Zed\VertexExtension\Dependency\Plugin\CalculableObjectVertexExpanderPluginInterface} plugins stack.
     * - Sets `CalculableObject.totals.taxTotal` with returned amount, if tax quotation request is successful.
     * - Sets `CalculableObject.totals.taxTotal` to 0 and overwrites other calculated taxes until a shipment is selected.
     * - Sets 'Item.UnitPriceToPayAggregation', 'Item.SumPriceToPayAggregation', 'Item.UnitTaxAmountFullAggregation' and 'Item.SumTaxAmountFullAggregation' with returned amounts, if tax quotation request is successful.
     * - Sets 'Expense.UnitTaxAmount', 'Expense.SumTaxAmount', 'Expense.UnitPriceToPayAggregation' and 'Expense.SumPriceToPayAggregation' (if expense type is shipment) with returned amounts, if tax quotation request is successful.
     * - Does nothing if `CalculableObjectTransfer.expenses` does not have items of `ShipmentConfig::SHIPMENT_EXPENSE_TYPE` type and price mode = NET_MODE.
     * - Uses {@link \Spryker\Zed\Vertex\VertexConfig::getSellerCountryCode()} to determine the country code of the store (seller) for the tax calculation.
     *   The default value is the first country of the store defined in the Quote.
     * - Uses {@link \Spryker\Zed\Vertex\VertexConfig::getCustomerCountryCode()} to determine the country code of the customer (buyer) for the tax calculation, when shipping address is not yet provided.
     *   The default value is the first country of the store defined in the Quote.
     * - Dispatches tax quotation request to ACP Apps.
     * - Recalculation does not trigger additional API calls when VertexSale data was not changed (cached).
     * - Executes fallback {@link \Spryker\Zed\CalculationExtension\Dependency\Plugin\CalculationPluginInterface} plugins stack if tax app config is missing or inactive.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\CalculableObjectTransfer $calculableObjectTransfer
     *
     * @return void
     */
    public function recalculate(CalculableObjectTransfer $calculableObjectTransfer): void;

    /**
     * Specification:
     * - Requires `Order.idSalesOrder`, `Order.store.name`, 'Order.priceMode' to be set.
     * - Executes {@link \SprykerEco\Zed\Vertex\Dependency\Plugin\OrderVertexExpanderPluginInterface} plugins stack.
     * - Uses {@link \Spryker\Zed\Vertex\VertexConfig::getSellerCountryCode()} to determine the country code of the store (seller).
     *   The default value is the first country of the store defined in the Order.
     * - Uses {@link \SprykerEco\Zed\Vertex\VertexConfig::getCustomerCountryCode()} to determine the country code of the customer (buyer), when shipping address is not provided.
     *   The default value is the first country of the store defined in the Order.
     * - Sends `SubmitPaymentTaxInvoice` message to the message broker.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return void
     */
    public function handleSubmitPaymentTaxInvoice(OrderTransfer  $orderTransfer): void;

    /**
     * Specification:
     * - Executes {@link \Spryker\Zed\VertexExtension\Dependency\Plugin\OrderVertexExpanderPluginInterface} plugins stack.
     * - Sends a refund request to Tax App.
     *
     * @api
     *
     * @param array<int> $orderItemIds
     * @param int $idSalesOrder
     *
     * @return void
     */
    public function processOrderRefund(array $orderItemIds, int $idSalesOrder): void;

    /**
     * Specification:
     *  - Validates Tax id for specific country.
     *  - VertexValidationRequestTransfer.taxId, VertexValidationRequestTransfer.countryCode are required.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\VertexValidationRequestTransfer $vertexValidationRequestTransfer
     *
     * @return \Generated\Shared\Transfer\VertexValidationResponseTransfer
     */
    public function validateTaxId(VertexValidationRequestTransfer $vertexValidationRequestTransfer): VertexValidationResponseTransfer;
}
