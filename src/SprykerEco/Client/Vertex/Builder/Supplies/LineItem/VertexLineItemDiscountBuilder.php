<?php

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerEco\Client\Vertex\Builder\Supplies\LineItem;

use Generated\Shared\Transfer\VertexItemTransfer;
use Generated\Shared\Transfer\ShipmentTransfer;
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
     * @var \SprykerEco\Client\Vertex\Builder\PriceConverter
     */
    protected $priceConverter;

    /**
     * @param \SprykerEco\Client\Vertex\Builder\PriceConverter $priceConverter
     */
    public function __construct(PriceConverter $priceConverter)
    {
        $this->priceConverter = $priceConverter;
    }

    /**
     * @param \Generated\Shared\Transfer\VertexItemTransfer|\Generated\Shared\Transfer\ShipmentTransfer $itemTransfer
     * @param \Generated\Shared\Transfer\VertexLineItemTransfer $vertexLineItemTransfer
     *
     * @return \Generated\Shared\Transfer\VertexLineItemTransfer
     */
    public function build(VertexItemTransfer|ShipmentTransfer $itemTransfer, VertexLineItemTransfer $vertexLineItemTransfer): VertexLineItemTransfer
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
