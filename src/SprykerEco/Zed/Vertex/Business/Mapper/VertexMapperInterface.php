<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Vertex\Business\Mapper;

use Generated\Shared\Transfer\AddressTransfer;
use Generated\Shared\Transfer\CalculableObjectTransfer;
use Generated\Shared\Transfer\ExpenseTransfer;
use Generated\Shared\Transfer\ItemTransfer;
use Generated\Shared\Transfer\MerchantStockAddressTransfer;
use Generated\Shared\Transfer\OrderTransfer;
use Generated\Shared\Transfer\ShippingWarehouseTransfer;
use Generated\Shared\Transfer\VertexItemTransfer;
use Generated\Shared\Transfer\VertexSaleTransfer;
use Generated\Shared\Transfer\VertexShipmentTransfer;

interface VertexMapperInterface
{
    /**
     * @param \Generated\Shared\Transfer\CalculableObjectTransfer $calculableObjectTransfer
     * @param \Generated\Shared\Transfer\VertexSaleTransfer $VertexSaleTransfer
     *
     * @return \Generated\Shared\Transfer\VertexSaleTransfer
     */
    public function mapCalculableObjectToVertexSaleTransfer(
        CalculableObjectTransfer $calculableObjectTransfer,
        VertexSaleTransfer $VertexSaleTransfer
    ): VertexSaleTransfer;

    /**
     * @param \Generated\Shared\Transfer\ItemTransfer $itemTransfer
     * @param string $priceMode
     * @param \Generated\Shared\Transfer\AddressTransfer|null $billingAddressTransfer
     * @param int $itemIndex
     *
     * @return \Generated\Shared\Transfer\VertexItemTransfer
     */
    public function mapItemTransfersToSaleItemTransfers(
        ItemTransfer $itemTransfer,
        string $priceMode,
        ?AddressTransfer $billingAddressTransfer,
        int $itemIndex
    ): VertexItemTransfer;

    /**
     * @param \Generated\Shared\Transfer\VertexItemTransfer $VertexItemTransfer
     * @param \Generated\Shared\Transfer\MerchantStockAddressTransfer $merchantStockAddressTransfer
     * @param \Generated\Shared\Transfer\ShippingWarehouseTransfer $shippingWarehouseTransfer
     *
     * @return \Generated\Shared\Transfer\ShippingWarehouseTransfer
     */
    public function mapMerchantStockAddressTransferToShippingWarehouse(
        VertexItemTransfer $VertexItemTransfer,
        MerchantStockAddressTransfer $merchantStockAddressTransfer,
        ShippingWarehouseTransfer $shippingWarehouseTransfer
    ): ShippingWarehouseTransfer;

    /**
     * @param \Generated\Shared\Transfer\ExpenseTransfer $expenseTransfer
     * @param string $priceMode
     * @param \Generated\Shared\Transfer\AddressTransfer|null $billingAddressTransfer
     *
     * @return \Generated\Shared\Transfer\VertexShipmentTransfer
     */
    public function mapExpenseTransferToSaleShipmentTransfer(
        ExpenseTransfer $expenseTransfer,
        string $priceMode,
        ?AddressTransfer $billingAddressTransfer
    ): VertexShipmentTransfer;

    /**
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     * @param \Generated\Shared\Transfer\VertexSaleTransfer $VertexSaleTransfer
     *
     * @return \Generated\Shared\Transfer\VertexSaleTransfer
     */
    public function mapOrderTransferToVertexSaleTransfer(OrderTransfer $orderTransfer, VertexSaleTransfer $VertexSaleTransfer): VertexSaleTransfer;
}
