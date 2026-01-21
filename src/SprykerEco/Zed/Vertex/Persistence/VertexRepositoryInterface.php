<?php

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerEco\Zed\Vertex\Persistence;

use Generated\Shared\Transfer\VertexApiAccessTokenCriteriaTransfer;
use Generated\Shared\Transfer\VertexApiAccessTokenTransfer;

interface VertexRepositoryInterface
{
    /**
     * @param \Generated\Shared\Transfer\VertexApiAccessTokenCriteriaTransfer $vertexApiAccessTokenCriteriaTransfer
     *
     * @return \Generated\Shared\Transfer\VertexApiAccessTokenTransfer
     */
    public function findAccessToken(VertexApiAccessTokenCriteriaTransfer $vertexApiAccessTokenCriteriaTransfer): VertexApiAccessTokenTransfer;
}
