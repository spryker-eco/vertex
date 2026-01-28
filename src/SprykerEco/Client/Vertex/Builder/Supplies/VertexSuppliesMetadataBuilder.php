<?php

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
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
