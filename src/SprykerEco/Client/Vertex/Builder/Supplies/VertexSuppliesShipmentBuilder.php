<?php

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerEco\Client\Vertex\Builder\Supplies;

use Generated\Shared\Transfer\VertexCalculationRequestTransfer;
use Generated\Shared\Transfer\VertexLineItemTransfer;
use Generated\Shared\Transfer\VertexSuppliesTransfer;
use SprykerEco\Client\Vertex\Builder\VertexSuppliesRequestBuilderInterface;

class VertexSuppliesShipmentBuilder implements VertexSuppliesRequestBuilderInterface
{
    /**
     * @param array<\SprykerEco\Client\Vertex\Builder\VertexLineItemBuilderInterface> $vertexLineItemBuilders
     */
    public function __construct(protected array $vertexLineItemBuilders)
    {
    }

    /**
     * @param \Generated\Shared\Transfer\VertexCalculationRequestTransfer $vertexCalculationRequestTransfer
     * @param \Generated\Shared\Transfer\VertexSuppliesTransfer $vertexSuppliesTransfer
     *
     * @return \Generated\Shared\Transfer\VertexSuppliesTransfer
     */
    public function build(
        VertexCalculationRequestTransfer $vertexCalculationRequestTransfer,
        VertexSuppliesTransfer $vertexSuppliesTransfer
    ): VertexSuppliesTransfer {
        foreach ($vertexCalculationRequestTransfer->getSaleOrFail()->getShipments() as $shipment) {
            $vertexLineItemTransfer = new VertexLineItemTransfer();
            foreach ($this->vertexLineItemBuilders as $builder) {
                $vertexLineItemTransfer = $builder->build($shipment, $vertexLineItemTransfer);
            }

            $vertexSuppliesTransfer->addLineItem($vertexLineItemTransfer);
        }

        return $vertexSuppliesTransfer;
    }
}
