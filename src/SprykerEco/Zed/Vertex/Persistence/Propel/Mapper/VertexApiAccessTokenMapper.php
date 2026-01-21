<?php

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Pyz\Zed\Vertex\Persistence\Propel\Mapper;

use Generated\Shared\Transfer\VertexApiAccessTokenTransfer;
use Orm\Zed\VertexApi\Persistence\SpyVertexApiAccessToken;

class VertexApiAccessTokenMapper
{
    /**
     * @param \Generated\Shared\Transfer\VertexApiAccessTokenTransfer $vertexApiAccessTokenTransfer
     * @param \Orm\Zed\VertexApi\Persistence\SpyVertexApiAccessToken $vertexApiAccessTokenEntity
     *
     * @return \Orm\Zed\VertexApi\Persistence\SpyVertexApiAccessToken
     */
    public function mapVertexApiAccessTokenTransferToVertexApiAccessTokenEntity(
        VertexApiAccessTokenTransfer $vertexApiAccessTokenTransfer,
        SpyVertexApiAccessToken $vertexApiAccessTokenEntity
    ): SpyVertexApiAccessToken {
        return $vertexApiAccessTokenEntity->fromArray($vertexApiAccessTokenTransfer->toArray());
    }

    /**
     * @param \Orm\Zed\VertexApi\Persistence\SpyVertexApiAccessToken $vertexApiAccessTokenEntity
     * @param \Generated\Shared\Transfer\VertexApiAccessTokenTransfer $vertexApiAccessTokenTransfer
     *
     * @return \Generated\Shared\Transfer\VertexApiAccessTokenTransfer
     */
    public function mapVertexApiAccessTokenEntityToVertexApiAccessTokenTransfer(
        SpyVertexApiAccessToken $vertexApiAccessTokenEntity,
        VertexApiAccessTokenTransfer $vertexApiAccessTokenTransfer
    ): VertexApiAccessTokenTransfer {
        return $vertexApiAccessTokenTransfer->fromArray($vertexApiAccessTokenEntity->toArray(), true);
    }
}
