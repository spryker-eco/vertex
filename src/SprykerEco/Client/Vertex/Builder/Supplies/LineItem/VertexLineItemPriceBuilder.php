<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Client\Vertex\Builder\Supplies\LineItem;

use Generated\Shared\Transfer\VertexItemTransfer;
use Generated\Shared\Transfer\VertexLineItemTransfer;
use Generated\Shared\Transfer\VertexShipmentTransfer;
use SprykerEco\Client\Vertex\Builder\PriceConverter;
use SprykerEco\Client\Vertex\Builder\VertexLineItemBuilderInterface;

class VertexLineItemPriceBuilder implements VertexLineItemBuilderInterface
{
    /**
     * @param \SprykerEco\Client\Vertex\Builder\PriceConverter $priceConverter
     */
    public function __construct(protected PriceConverter $priceConverter)
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
        $vertexLineItemTransfer
            ->setLineItemId($itemTransfer->getIdOrFail())
            ->setUnitPrice($this->priceConverter->convertPriceForVertex($itemTransfer->getPriceAmountOrFail()));

        if ($itemTransfer->getRefundableAmount()) {
            $vertexLineItemTransfer
                ->setUnitPrice($this->priceConverter->convertToNegatedPriceForVertex($itemTransfer->getRefundableAmountOrFail()));
        }

        return $vertexLineItemTransfer;
    }
}
