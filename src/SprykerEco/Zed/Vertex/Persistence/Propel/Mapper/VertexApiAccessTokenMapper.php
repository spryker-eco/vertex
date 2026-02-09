<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Vertex\Persistence\Propel\Mapper;

use Generated\Shared\Transfer\VertexApiAccessTokenTransfer;
use Orm\Zed\Vertex\Persistence\Base\SpyVertexApiAccessToken;

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
