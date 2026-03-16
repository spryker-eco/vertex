<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

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
        VertexSuppliesTransfer $vertexSuppliesTransfer,
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
