<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Client\Vertex\Builder;

use Generated\Shared\Transfer\VertexCalculationRequestTransfer;
use Generated\Shared\Transfer\VertexSuppliesTransfer;

class SuppliesRequestBuilder
{
    /**
     * @param array<\SprykerEco\Client\Vertex\Builder\VertexSuppliesRequestBuilderInterface> $vertexRequestBuilders
     */
    public function __construct(protected array $vertexRequestBuilders)
    {
    }

    public function build(
        VertexCalculationRequestTransfer $vertexCalculationRequestTransfer,
        VertexSuppliesTransfer $vertexSuppliesTransfer,
    ): VertexSuppliesTransfer {
        foreach ($this->vertexRequestBuilders as $builder) {
            $vertexSuppliesTransfer = $builder->build($vertexCalculationRequestTransfer, $vertexSuppliesTransfer);
        }

        return $vertexSuppliesTransfer;
    }
}
