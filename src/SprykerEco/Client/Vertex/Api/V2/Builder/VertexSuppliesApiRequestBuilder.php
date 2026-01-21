<?php

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerEco\Client\Vertex\Api\V2\Builder;

use Generated\Shared\Transfer\VertexSuppliesTransfer;

class VertexSuppliesApiRequestBuilder
{
    /**
     * @param \Generated\Shared\Transfer\VertexSuppliesTransfer $vertexSuppliesTransfer
     *
     * @return array
     */
    public function buildVertexSuppliesRequest(VertexSuppliesTransfer $vertexSuppliesTransfer): array
    {
        $vertexRequestBody = $vertexSuppliesTransfer->toArray(true, true);
        $saleTaxMetadata = $vertexRequestBody['taxMetadata'];
        unset($vertexRequestBody['taxMetadata']);

        $vertexRequestBody = array_replace_recursive($vertexRequestBody, $saleTaxMetadata);

        foreach ($vertexRequestBody['lineItems'] as $idx => $lineItem) {
            $lineItemMetadata = $lineItem['taxMetadata'];
            unset($lineItem['taxMetadata']);
            unset($lineItem['shouldBeGrouped']);
            unset($lineItem['initialIdentifier']);
            $lineItem['lineItemNumber'] = $idx;

            $vertexRequestBody['lineItems'][$idx] = array_replace_recursive($lineItem, $lineItemMetadata);
        }

        return $vertexRequestBody;
    }
}
