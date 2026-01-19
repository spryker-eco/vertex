<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Vertex\Business\Mapper\Addresses;

use Generated\Shared\Transfer\AddressTransfer;
use Generated\Shared\Transfer\MerchantProfileAddressTransfer;
use Generated\Shared\Transfer\StockAddressTransfer;
use Generated\Shared\Transfer\VertexAddressTransfer;

interface AddressMapperInterface
{
    /**
     * @param \Generated\Shared\Transfer\AddressTransfer $addressTransfer
     * @param \Generated\Shared\Transfer\VertexAddressTransfer $VertexAddressTransfer
     *
     * @return \Generated\Shared\Transfer\VertexAddressTransfer
     */
    public function mapAddressTransferToVertexAddressTransfer(
        AddressTransfer $addressTransfer,
        VertexAddressTransfer $VertexAddressTransfer
    ): VertexAddressTransfer;

    /**
     * @param \Generated\Shared\Transfer\MerchantProfileAddressTransfer $addressTransfer
     * @param \Generated\Shared\Transfer\VertexAddressTransfer $VertexAddressTransfer
     *
     * @return \Generated\Shared\Transfer\VertexAddressTransfer
     */
    public function mapMerchantProfileAddressTransferToVertexAddressTransfer(
        MerchantProfileAddressTransfer $addressTransfer,
        VertexAddressTransfer $VertexAddressTransfer
    ): VertexAddressTransfer;

    /**
     * @param \Generated\Shared\Transfer\StockAddressTransfer $addressTransfer
     * @param \Generated\Shared\Transfer\VertexAddressTransfer $VertexAddressTransfer
     *
     * @return \Generated\Shared\Transfer\VertexAddressTransfer
     */
    public function mapStockAddressTransferToVertexAddressTransfer(
        StockAddressTransfer $addressTransfer,
        VertexAddressTransfer $VertexAddressTransfer
    ): VertexAddressTransfer;
}
