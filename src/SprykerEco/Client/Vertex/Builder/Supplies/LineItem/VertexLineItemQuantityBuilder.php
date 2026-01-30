<?php

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerEco\Client\Vertex\Builder\Supplies\LineItem;

use Generated\Shared\Transfer\VertexItemTransfer;
use Generated\Shared\Transfer\VertexShipmentTransfer;
use Generated\Shared\Transfer\VertexLineItemTransfer;
use Generated\Shared\Transfer\VertexQuantityTransfer;
use SprykerEco\Client\Vertex\Builder\VertexLineItemBuilderInterface;

class VertexLineItemQuantityBuilder implements VertexLineItemBuilderInterface
{
    /**
     * @var string
     */
    protected const UNIT_OF_MEASURE_EACH = 'EA';

    /**
     * @param \Generated\Shared\Transfer\VertexItemTransfer|\Generated\Shared\Transfer\VertexShipmentTransfer $itemTransfer
     * @param \Generated\Shared\Transfer\VertexLineItemTransfer $vertexLineItemTransfer
     *
     * @return \Generated\Shared\Transfer\VertexLineItemTransfer
     */
    public function build(VertexItemTransfer|VertexShipmentTransfer $itemTransfer, VertexLineItemTransfer $vertexLineItemTransfer): VertexLineItemTransfer
    {
        $vertexQuantityTransfer = (new VertexQuantityTransfer())
            ->setUnitOfMeasure(static::UNIT_OF_MEASURE_EACH)
            ->setValue((float)$itemTransfer->getQuantityOrFail());

        $vertexLineItemTransfer
            ->setQuantity($vertexQuantityTransfer);

        return $vertexLineItemTransfer;
    }
}
