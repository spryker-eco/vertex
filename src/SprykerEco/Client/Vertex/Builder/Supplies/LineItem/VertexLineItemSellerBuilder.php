<?php

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerEco\Client\Vertex\Builder\Supplies\LineItem;

use Generated\Shared\Transfer\VertexShipmentTransfer;
use Generated\Shared\Transfer\VertexItemTransfer;
use Generated\Shared\Transfer\VertexLineItemTransfer;
use Generated\Shared\Transfer\VertexSellerTransfer;
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
