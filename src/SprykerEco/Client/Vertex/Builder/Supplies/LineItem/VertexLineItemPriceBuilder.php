<?php

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerEco\Client\Vertex\Builder\Supplies\LineItem;

use Generated\Shared\Transfer\VertexItemTransfer;
use Generated\Shared\Transfer\VertexShipmentTransfer;
use Generated\Shared\Transfer\VertexLineItemTransfer;
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
