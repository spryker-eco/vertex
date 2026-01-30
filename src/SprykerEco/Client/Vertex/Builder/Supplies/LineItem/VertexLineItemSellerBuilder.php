<?php

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerEco\Client\Vertex\Builder\Supplies\LineItem;

use Generated\Shared\Transfer\SaleItemTransfer;
use Generated\Shared\Transfer\ShipmentTransfer;
use Generated\Shared\Transfer\VertexItemTransfer;
use Generated\Shared\Transfer\VertexLineItemTransfer;
use Generated\Shared\Transfer\VertexSellerTransfer;
use SprykerEco\Client\Vertex\Builder\LocationMapper;
use SprykerEco\Client\Vertex\Builder\VertexLineItemBuilderInterface;

class VertexLineItemSellerBuilder implements VertexLineItemBuilderInterface
{
    /**
     * @var \SprykerEco\Client\Vertex\Builder\LocationMapper
     */
    protected $locationMapper;

    /**
     * @param \SprykerEco\Client\Vertex\Builder\LocationMapper $locationMapper
     */
    public function __construct(LocationMapper $locationMapper)
    {
        $this->locationMapper = $locationMapper;
    }

    /**
     * @param \Generated\Shared\Transfer\VertexItemTransfer|\Generated\Shared\Transfer\ShipmentTransfer $itemTransfer
     * @param \Generated\Shared\Transfer\VertexLineItemTransfer $vertexLineItemTransfer
     *
     * @return \Generated\Shared\Transfer\VertexLineItemTransfer
     */
    public function build(VertexItemTransfer|ShipmentTransfer $itemTransfer, VertexLineItemTransfer $vertexLineItemTransfer): VertexLineItemTransfer
    {
        $vertexSellerTransfer = (new VertexSellerTransfer());
        if ($itemTransfer->getWarehouseAddress()) {
            $vertexSellerTransfer
                ->setPhysicalOrigin(
                    $this->locationMapper
                        ->mapAddressTransferToVertexLocationTransfer($itemTransfer->getWarehouseAddressOrFail()),
                );
        }

        if ($itemTransfer->getSellerAddress()) {
            $vertexSellerTransfer
                ->setAdministrativeOrigin(
                    $this->locationMapper
                        ->mapAddressTransferToVertexLocationTransfer($itemTransfer->getSellerAddressOrFail()),
                );
        }

        $vertexLineItemTransfer
            ->setSeller($vertexSellerTransfer);

        return $vertexLineItemTransfer;
    }
}
