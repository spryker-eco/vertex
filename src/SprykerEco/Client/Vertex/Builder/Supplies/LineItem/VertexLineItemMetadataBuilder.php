<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Client\Vertex\Builder\Supplies\LineItem;

use Generated\Shared\Transfer\VertexItemTransfer;
use Generated\Shared\Transfer\VertexLineItemTransfer;
use Generated\Shared\Transfer\VertexShipmentTransfer;
use SprykerEco\Client\Vertex\Builder\VertexLineItemBuilderInterface;

class VertexLineItemMetadataBuilder implements VertexLineItemBuilderInterface
{
    public function build(VertexItemTransfer|VertexShipmentTransfer $itemTransfer, VertexLineItemTransfer $vertexLineItemTransfer): VertexLineItemTransfer
    {
        if (!$itemTransfer instanceof VertexItemTransfer) {
            return $vertexLineItemTransfer;
        }

        $taxMetadata = $itemTransfer->getTaxMetadata();
        $taxMetadata = $this->filterArrayEmptyValues($taxMetadata);

        return $vertexLineItemTransfer->setTaxMetadata($taxMetadata);
    }

    /**
     * @param array<mixed> $metadataArray
     *
     * @return array<mixed>
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
