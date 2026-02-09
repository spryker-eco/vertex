<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Client\Vertex\Builder\Supplies\LineItem;

use Generated\Shared\Transfer\VertexItemTransfer;
use Generated\Shared\Transfer\VertexLineItemTransfer;
use Generated\Shared\Transfer\VertexQuantityTransfer;
use Generated\Shared\Transfer\VertexShipmentTransfer;
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
