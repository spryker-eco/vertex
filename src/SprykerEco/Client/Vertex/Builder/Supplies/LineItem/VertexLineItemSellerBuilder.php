<?php

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Pyz\Zed\VertexApi\Business\Builder\Supplies\LineItem;

use Generated\Shared\Transfer\SaleItemTransfer;
use Generated\Shared\Transfer\ShipmentTransfer;
use Generated\Shared\Transfer\VertexLineItemTransfer;
use Generated\Shared\Transfer\VertexSellerTransfer;
use Pyz\Zed\VertexApi\Business\Builder\LocationMapper;
use Pyz\Zed\VertexApi\Business\Builder\VertexLineItemBuilderInterface;

class VertexLineItemSellerBuilder implements VertexLineItemBuilderInterface
{
    /**
     * @var \Pyz\Zed\VertexApi\Business\Builder\LocationMapper
     */
    protected $locationMapper;

    /**
     * @param \Pyz\Zed\VertexApi\Business\Builder\LocationMapper $locationMapper
     */
    public function __construct(LocationMapper $locationMapper)
    {
        $this->locationMapper = $locationMapper;
    }

    /**
     * @param \Generated\Shared\Transfer\SaleItemTransfer|\Generated\Shared\Transfer\ShipmentTransfer $itemTransfer
     * @param \Generated\Shared\Transfer\VertexLineItemTransfer $vertexLineItemTransfer
     *
     * @return \Generated\Shared\Transfer\VertexLineItemTransfer
     */
    public function build(SaleItemTransfer|ShipmentTransfer $itemTransfer, VertexLineItemTransfer $vertexLineItemTransfer): VertexLineItemTransfer
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
