<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Zed\Vertex\Business\Mapper;

use Generated\Shared\Transfer\AddressTransfer;
use Generated\Shared\Transfer\CalculableObjectTransfer;
use Generated\Shared\Transfer\ExpenseTransfer;
use Generated\Shared\Transfer\ItemTransfer;
use Generated\Shared\Transfer\MerchantStockAddressTransfer;
use Generated\Shared\Transfer\OrderTransfer;
use Generated\Shared\Transfer\VertexItemTransfer;
use Generated\Shared\Transfer\VertexSaleTransfer;
use Generated\Shared\Transfer\VertexShipmentTransfer;
use Generated\Shared\Transfer\VertexShippingWarehouseTransfer;

interface VertexMapperInterface
{
    public function mapCalculableObjectToVertexSaleTransfer(
        CalculableObjectTransfer $calculableObjectTransfer,
        VertexSaleTransfer $VertexSaleTransfer,
    ): VertexSaleTransfer;

    public function mapItemTransfersToSaleItemTransfers(
        ItemTransfer $itemTransfer,
        string $priceMode,
        ?AddressTransfer $billingAddressTransfer,
        int $itemIndex,
    ): VertexItemTransfer;

    public function mapMerchantStockAddressTransferToVertexShippingWarehouse(
        VertexItemTransfer $VertexItemTransfer,
        MerchantStockAddressTransfer $merchantStockAddressTransfer,
        VertexShippingWarehouseTransfer $vertexShippingWarehouseTransfer,
    ): VertexShippingWarehouseTransfer;

    public function mapExpenseTransferToVertexShipmentTransfer(
        ExpenseTransfer $expenseTransfer,
        string $priceMode,
        ?AddressTransfer $billingAddressTransfer,
    ): VertexShipmentTransfer;

    public function mapOrderTransferToVertexSaleTransfer(OrderTransfer $orderTransfer, VertexSaleTransfer $VertexSaleTransfer): VertexSaleTransfer;
}
