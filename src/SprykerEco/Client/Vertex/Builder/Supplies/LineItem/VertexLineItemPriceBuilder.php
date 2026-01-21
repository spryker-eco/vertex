<?php

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerEco\Client\Vertex\Builder\Supplies\LineItem;

use Generated\Shared\Transfer\SaleItemTransfer;
use Generated\Shared\Transfer\ShipmentTransfer;
use Generated\Shared\Transfer\VertexLineItemTransfer;
use SprykerEco\Client\Vertex\Builder\PriceConverter;
use SprykerEco\Client\Vertex\Builder\VertexLineItemBuilderInterface;

class VertexLineItemPriceBuilder implements VertexLineItemBuilderInterface
{
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
     * @param \Generated\Shared\Transfer\SaleItemTransfer|\Generated\Shared\Transfer\ShipmentTransfer $itemTransfer
     * @param \Generated\Shared\Transfer\VertexLineItemTransfer $vertexLineItemTransfer
     *
     * @return \Generated\Shared\Transfer\VertexLineItemTransfer
     */
    public function build(SaleItemTransfer|ShipmentTransfer $itemTransfer, VertexLineItemTransfer $vertexLineItemTransfer): VertexLineItemTransfer
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
