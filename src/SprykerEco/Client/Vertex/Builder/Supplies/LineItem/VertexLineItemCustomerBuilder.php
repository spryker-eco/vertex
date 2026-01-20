<?php

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Pyz\Zed\VertexApi\Business\Builder\Supplies\LineItem;

use Generated\Shared\Transfer\SaleItemTransfer;
use Generated\Shared\Transfer\ShipmentTransfer;
use Generated\Shared\Transfer\VertexCustomerTransfer;
use Generated\Shared\Transfer\VertexLineItemTransfer;
use Pyz\Zed\VertexApi\Business\Builder\LocationMapper;
use Pyz\Zed\VertexApi\Business\Builder\VertexLineItemBuilderInterface;

class VertexLineItemCustomerBuilder implements VertexLineItemBuilderInterface
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
        $vertexCustomerTransfer = $vertexLineItemTransfer->getCustomer() ?? new VertexCustomerTransfer();

        if ($itemTransfer->getShippingAddress() === null && $itemTransfer->getBillingAddress() === null) {
            return $vertexLineItemTransfer;
        }

        if ($itemTransfer->getShippingAddress()) {
            $vertexCustomerTransfer->setDestination(
                $this->locationMapper
                        ->mapAddressTransferToVertexLocationTransfer($itemTransfer->getShippingAddressOrFail()),
            );
        }

        if ($itemTransfer->getBillingAddress()) {
            $vertexCustomerTransfer->setAdministrativeDestination(
                $this->locationMapper
                        ->mapAddressTransferToVertexLocationTransfer($itemTransfer->getBillingAddress()),
            );
        }

        $vertexLineItemTransfer
            ->setCustomer($vertexCustomerTransfer);

        return $vertexLineItemTransfer;
    }
}
