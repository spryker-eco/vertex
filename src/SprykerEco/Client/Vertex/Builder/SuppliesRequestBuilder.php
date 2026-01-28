<?php

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerEco\Client\Vertex\Builder;

use Generated\Shared\Transfer\VertexCalculationRequestTransfer;
use Generated\Shared\Transfer\VertexSuppliesTransfer;

class SuppliesRequestBuilder
{
    /**
     * @var array<\Pyz\Zed\VertexApi\Business\Builder\VertexSuppliesRequestBuilderInterface>
     */
    protected $vertexRequestBuilders;

    /**
     * @param array<\Pyz\Zed\VertexApi\Business\Builder\VertexSuppliesRequestBuilderInterface> $vertexRequestBuilders
     */
    public function __construct(array $vertexRequestBuilders)
    {
        $this->vertexRequestBuilders = $vertexRequestBuilders;
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
        foreach ($this->vertexRequestBuilders as $builder) {
            $vertexSuppliesTransfer = $builder->build($vertexCalculationRequestTransfer, $vertexSuppliesTransfer);
        }

        return $vertexSuppliesTransfer;
    }
}
