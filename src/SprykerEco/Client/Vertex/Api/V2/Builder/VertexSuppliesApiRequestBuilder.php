<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Client\Vertex\Api\V2\Builder;

use Generated\Shared\Transfer\VertexSuppliesTransfer;

class VertexSuppliesApiRequestBuilder
{
    /**
     * @param \Generated\Shared\Transfer\VertexSuppliesTransfer $vertexSuppliesTransfer
     *
     * @return array<string, mixed>
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
