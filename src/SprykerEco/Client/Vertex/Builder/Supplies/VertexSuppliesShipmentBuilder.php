<?php

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Pyz\Zed\VertexApi\Business\Builder\Supplies;

use Generated\Shared\Transfer\TaxCalculationRequestTransfer;
use Generated\Shared\Transfer\VertexLineItemTransfer;
use Generated\Shared\Transfer\VertexSuppliesTransfer;
use Pyz\Zed\VertexApi\Business\Builder\VertexSuppliesRequestBuilderInterface;

class VertexSuppliesShipmentBuilder implements VertexSuppliesRequestBuilderInterface
{
    /**
     * @var array<\Pyz\Zed\VertexApi\Business\Builder\VertexLineItemBuilderInterface>
     */
    protected array $vertexLineItemBuilders;

    /**
     * @param array<\Pyz\Zed\VertexApi\Business\Builder\VertexLineItemBuilderInterface> $vertexLineItemBuilders
     */
    public function __construct(array $vertexLineItemBuilders)
    {
        $this->vertexLineItemBuilders = $vertexLineItemBuilders;
    }

    /**
     * @param \Generated\Shared\Transfer\TaxCalculationRequestTransfer $taxCalculationRequestTransfer
     * @param \Generated\Shared\Transfer\VertexSuppliesTransfer $vertexSuppliesTransfer
     *
     * @return \Generated\Shared\Transfer\VertexSuppliesTransfer
     */
    public function build(
        TaxCalculationRequestTransfer $taxCalculationRequestTransfer,
        VertexSuppliesTransfer $vertexSuppliesTransfer
    ): VertexSuppliesTransfer {
        foreach ($taxCalculationRequestTransfer->getSaleOrFail()->getShipments() as $shipment) {
            $vertexLineItemTransfer = new VertexLineItemTransfer();
            foreach ($this->vertexLineItemBuilders as $builder) {
                $vertexLineItemTransfer = $builder->build($shipment, $vertexLineItemTransfer);
            }

            $vertexSuppliesTransfer->addLineItem($vertexLineItemTransfer);
        }

        return $vertexSuppliesTransfer;
    }
}
