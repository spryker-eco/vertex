<?php

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerEco\Client\Vertex\Builder\Supplies\LineItem;

use Generated\Shared\Transfer\VertexItemTransfer;
use Generated\Shared\Transfer\ShipmentTransfer;
use Generated\Shared\Transfer\VertexCustomerTransfer;
use Generated\Shared\Transfer\VertexLineItemTransfer;
use SprykerEco\Client\Vertex\Builder\LocationMapper;
use SprykerEco\Client\Vertex\Builder\VertexLineItemBuilderInterface;

class VertexLineItemCustomerBuilder implements VertexLineItemBuilderInterface
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
