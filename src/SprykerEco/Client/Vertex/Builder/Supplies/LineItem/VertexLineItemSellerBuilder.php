<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Client\Vertex\Builder\Supplies\LineItem;

use Generated\Shared\Transfer\VertexItemTransfer;
use Generated\Shared\Transfer\VertexLineItemTransfer;
use Generated\Shared\Transfer\VertexSellerTransfer;
use Generated\Shared\Transfer\VertexShipmentTransfer;
use SprykerEco\Client\Vertex\Builder\LocationMapper;
use SprykerEco\Client\Vertex\Builder\VertexLineItemBuilderInterface;

class VertexLineItemSellerBuilder implements VertexLineItemBuilderInterface
{
    /**
     * @param \SprykerEco\Client\Vertex\Builder\LocationMapper $locationMapper
     */
    public function __construct(protected LocationMapper $locationMapper)
    {
    }

    /**
     * @param \Generated\Shared\Transfer\VertexItemTransfer|\Generated\Shared\Transfer\VertexShipmentTransfer $itemTransfer
     * @param \Generated\Shared\Transfer\VertexLineItemTransfer $vertexLineItemTransfer
     *
     * @return \Generated\Shared\Transfer\VertexLineItemTransfer
     */
    public function build(VertexItemTransfer|VertexShipmentTransfer $itemTransfer, VertexLineItemTransfer $vertexLineItemTransfer): VertexLineItemTransfer
    {
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
