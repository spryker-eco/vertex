<?php

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Pyz\Zed\VertexApi\Business\Api\V2\Client;

use Generated\Shared\Transfer\VertexApiAccessTokenTransfer;
use Generated\Shared\Transfer\VertexApiResponseTransfer;
use Generated\Shared\Transfer\VertexConfigTransfer;
use Generated\Shared\Transfer\VertexSuppliesTransfer;

interface SuppliesApiInterface
{
    /**
     * @param \Generated\Shared\Transfer\VertexSuppliesTransfer $vertexSuppliesTransfer
     * @param \Generated\Shared\Transfer\VertexConfigTransfer $vertexConfigTransfer
     * @param \Generated\Shared\Transfer\VertexApiAccessTokenTransfer $vertexApiAccessTokenTransfer
     *
     * @return \Generated\Shared\Transfer\VertexApiResponseTransfer
     */
    public function calculateTax(
        VertexSuppliesTransfer $vertexSuppliesTransfer,
        VertexConfigTransfer $vertexConfigTransfer,
        VertexApiAccessTokenTransfer $vertexApiAccessTokenTransfer
    ): VertexApiResponseTransfer;
}
