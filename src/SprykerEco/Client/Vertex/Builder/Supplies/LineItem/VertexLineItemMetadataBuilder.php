<?php

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerEco\Client\Vertex\Builder\Supplies\LineItem;

use Generated\Shared\Transfer\VertexItemTransfer;
use Generated\Shared\Transfer\VertexShipmentTransfer;
use Generated\Shared\Transfer\VertexLineItemTransfer;
use SprykerEco\Client\Vertex\Builder\VertexLineItemBuilderInterface;

class VertexLineItemMetadataBuilder implements VertexLineItemBuilderInterface
{
    /**
     * @param \Generated\Shared\Transfer\VertexItemTransfer|\Generated\Shared\Transfer\VertexShipmentTransfer $itemTransfer
     * @param \Generated\Shared\Transfer\VertexLineItemTransfer $vertexLineItemTransfer
     *
     * @return \Generated\Shared\Transfer\VertexLineItemTransfer
     */
    public function build(VertexItemTransfer|VertexShipmentTransfer $itemTransfer, VertexLineItemTransfer $vertexLineItemTransfer): VertexLineItemTransfer
    {
        $taxMetadata = $itemTransfer->getTaxMetadata();
        $taxMetadata = $this->filterArrayEmptyValues($taxMetadata);

        return $vertexLineItemTransfer->setTaxMetadata($taxMetadata);
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
