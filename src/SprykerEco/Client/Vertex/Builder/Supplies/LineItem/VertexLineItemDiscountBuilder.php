<?php

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Pyz\Zed\VertexApi\Business\Builder\Supplies\LineItem;

use Generated\Shared\Transfer\SaleItemTransfer;
use Generated\Shared\Transfer\ShipmentTransfer;
use Generated\Shared\Transfer\VertexDiscountTransfer;
use Generated\Shared\Transfer\VertexLineItemTransfer;
use Pyz\Zed\VertexApi\Business\Builder\PriceConverter;
use Pyz\Zed\VertexApi\Business\Builder\VertexLineItemBuilderInterface;

class VertexLineItemDiscountBuilder implements VertexLineItemBuilderInterface
{
    /**
     * @var string
     */
    protected const DISCOUNT_TYPE = 'DiscountAmount';

    /**
     * @var \Pyz\Zed\VertexApi\Business\Builder\PriceConverter
     */
    protected $priceConverter;

    /**
     * @param \Pyz\Zed\VertexApi\Business\Builder\PriceConverter $priceConverter
     */
    public function __construct(PriceConverter $priceConverter)
    {
        $this->priceConverter = $priceConverter;
    }

    /**
     * @param \Generated\Shared\Transfer\SaleItemTransfer|\Generated\Shared\Transfer\ShipmentTransfer $itemTransfer
     * @param \Generated\Shared\Transfer\VertexLineItemTransfer $vertexLineItemTransfer
     *
     * @return \Generated\Shared\Transfer\VertexLineItemTransfer
     */
    public function build(SaleItemTransfer|ShipmentTransfer $itemTransfer, VertexLineItemTransfer $vertexLineItemTransfer): VertexLineItemTransfer
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
