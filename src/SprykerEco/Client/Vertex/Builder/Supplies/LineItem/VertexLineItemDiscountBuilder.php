<?php

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerEco\Client\Vertex\Builder\Supplies\LineItem;

use Generated\Shared\Transfer\VertexItemTransfer;
use Generated\Shared\Transfer\VertexShipmentTransfer;
use Generated\Shared\Transfer\VertexDiscountTransfer;
use Generated\Shared\Transfer\VertexLineItemTransfer;
use SprykerEco\Client\Vertex\Builder\PriceConverter;
use SprykerEco\Client\Vertex\Builder\VertexLineItemBuilderInterface;

class VertexLineItemDiscountBuilder implements VertexLineItemBuilderInterface
{
    /**
     * @var string
     */
    protected const DISCOUNT_TYPE = 'DiscountAmount';

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
        if ($itemTransfer->getDiscountAmount()) {
            $vertexDiscountTransfer = (new VertexDiscountTransfer())
                ->setDiscountValue($this->priceConverter->convertPriceForVertex($itemTransfer->getDiscountAmount()))
                ->setDiscountType(static::DISCOUNT_TYPE);

            if ($itemTransfer->getRefundableAmount()) {
                $vertexDiscountTransfer->setDiscountValue($this->priceConverter->convertToNegatedPriceForVertex($itemTransfer->getDiscountAmount()));
            }

            $vertexLineItemTransfer
                ->setDiscount($vertexDiscountTransfer);
        }

        return $vertexLineItemTransfer;
    }
}
