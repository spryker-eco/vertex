<?php

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Pyz\Zed\VertexApi\Business\Builder\Supplies\LineItem;

use Generated\Shared\Transfer\SaleItemTransfer;
use Generated\Shared\Transfer\ShipmentTransfer;
use Generated\Shared\Transfer\VertexLineItemTransfer;
use Generated\Shared\Transfer\VertexProductTransfer;
use Pyz\Zed\VertexApi\Business\Builder\VertexLineItemBuilderInterface;

class VertexLineItemProductBuilder implements VertexLineItemBuilderInterface
{
    /**
     * @param \Generated\Shared\Transfer\SaleItemTransfer|\Generated\Shared\Transfer\ShipmentTransfer $itemTransfer
     * @param \Generated\Shared\Transfer\VertexLineItemTransfer $vertexLineItemTransfer
     *
     * @return \Generated\Shared\Transfer\VertexLineItemTransfer
     */
    public function build(SaleItemTransfer|ShipmentTransfer $itemTransfer, VertexLineItemTransfer $vertexLineItemTransfer): VertexLineItemTransfer
    {
        $vertexProductTransfer = (new VertexProductTransfer())
            ->setValue(($itemTransfer instanceof SaleItemTransfer) ? $itemTransfer->getSkuOrFail() : $itemTransfer->getShipmentMethodKey());

        $vertexLineItemTransfer
            ->setProduct($vertexProductTransfer);

        return $vertexLineItemTransfer;
    }
}
