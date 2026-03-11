<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Client\Vertex\Builder\Supplies\LineItem;

use Generated\Shared\Transfer\VertexItemTransfer;
use Generated\Shared\Transfer\VertexLineItemTransfer;
use Generated\Shared\Transfer\VertexSellerTransfer;
use Generated\Shared\Transfer\VertexShipmentTransfer;
use SprykerEco\Client\Vertex\Builder\LocationMapperInterface;
use SprykerEco\Client\Vertex\Builder\VertexLineItemBuilderInterface;

class VertexLineItemSellerBuilder implements VertexLineItemBuilderInterface
{
    public function __construct(protected LocationMapperInterface $locationMapper)
    {
    }

    public function build(VertexItemTransfer|VertexShipmentTransfer $itemTransfer, VertexLineItemTransfer $vertexLineItemTransfer): VertexLineItemTransfer
    {
        if (!$itemTransfer instanceof VertexItemTransfer) {
            return $vertexLineItemTransfer;
        }

        $vertexSellerTransfer = (new VertexSellerTransfer());
        if ($itemTransfer->getWarehouseAddress()) {
            $vertexSellerTransfer
                ->setPhysicalOrigin(
                    $this->locationMapper
                        ->mapVertexAddressTransferToVertexLocationTransfer($itemTransfer->getWarehouseAddressOrFail()),
                );
        }

        if ($itemTransfer->getSellerAddress()) {
            $vertexSellerTransfer
                ->setAdministrativeOrigin(
                    $this->locationMapper
                        ->mapVertexAddressTransferToVertexLocationTransfer($itemTransfer->getSellerAddressOrFail()),
                );
        }

        $vertexLineItemTransfer
            ->setSeller($vertexSellerTransfer);

        return $vertexLineItemTransfer;
    }
}
