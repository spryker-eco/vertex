<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Client\Vertex\Builder\Supplies;

use Generated\Shared\Transfer\VertexCalculationRequestTransfer;
use Generated\Shared\Transfer\VertexSuppliesTransfer;
use SprykerEco\Client\Vertex\Builder\VertexSuppliesRequestBuilderInterface;

class VertexSuppliesMetadataBuilder implements VertexSuppliesRequestBuilderInterface
{
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
        $taxMetadata = $vertexCalculationRequestTransfer->getSaleOrFail()->getTaxMetadata();
        $taxMetadata = $this->filterArrayEmptyValues($taxMetadata);

        return $vertexSuppliesTransfer->setTaxMetadata($taxMetadata);
    }

    /**
     * @param array $metadataArray
     *
     * @return array
     */
    protected function filterArrayEmptyValues(array $metadataArray): array
    {
        foreach ($metadataArray as $key => $value) {
            if (!$value) {
                unset($metadataArray[$key]);

                continue;
            }

            if (is_array($value)) {
                $metadataArray[$key] = $this->filterArrayEmptyValues($value);
            }
        }

        return $metadataArray;
    }
}
