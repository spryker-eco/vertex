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
use Spryker\Shared\Kernel\Transfer\AbstractTransfer;

class AddressMapper implements AddressMapperInterface
{
    public function mapAddressTransferToVertexAddressTransfer(
        AddressTransfer $addressTransfer,
        VertexAddressTransfer $VertexAddressTransfer
    ): VertexAddressTransfer {
        return $this->mapAddressAndMerchantProfileAddressTransferToVertexAddressTransfer($addressTransfer, $VertexAddressTransfer);
    }

    public function mapStockAddressTransferToVertexAddressTransfer(
        StockAddressTransfer $addressTransfer,
        VertexAddressTransfer $VertexAddressTransfer
    ): VertexAddressTransfer {
        $VertexAddressTransfer = $VertexAddressTransfer->fromArray($addressTransfer->toArray(), true);

        if (
            $addressTransfer->getCountry()
            && $addressTransfer->getCountry()->getIso2Code()
        ) {
            $VertexAddressTransfer->setCountry($addressTransfer->getCountry()->getIso2Code());
        }

        return $VertexAddressTransfer;
    }

    public function mapMerchantProfileAddressTransferToVertexAddressTransfer(
        MerchantProfileAddressTransfer $addressTransfer,
        VertexAddressTransfer $VertexAddressTransfer
    ): VertexAddressTransfer {
        return $this->mapAddressAndMerchantProfileAddressTransferToVertexAddressTransfer($addressTransfer, $VertexAddressTransfer);
    }

    protected function mapAddressAndMerchantProfileAddressTransferToVertexAddressTransfer(
        AbstractTransfer $addressTransfer,
        VertexAddressTransfer $VertexAddressTransfer
    ): VertexAddressTransfer {
        $VertexAddressTransfer = $VertexAddressTransfer->fromArray($addressTransfer->toArray(), true);

        if ($addressTransfer->offsetExists('iso2Code')) {
            $VertexAddressTransfer->setCountry($addressTransfer->getIso2Code());
        }

        return $VertexAddressTransfer;
    }
}
