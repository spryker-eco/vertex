<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Zed\Vertex\Persistence;

use Generated\Shared\Transfer\VertexApiAccessTokenCriteriaTransfer;
use Generated\Shared\Transfer\VertexApiAccessTokenTransfer;
use Spryker\Zed\Kernel\Persistence\AbstractRepository;

/**
 * @method \SprykerEco\Zed\Vertex\Persistence\VertexPersistenceFactory getFactory()
 */
class VertexRepository extends AbstractRepository implements VertexRepositoryInterface
{
    public function findAccessToken(VertexApiAccessTokenCriteriaTransfer $vertexApiAccessTokenCriteriaTransfer): VertexApiAccessTokenTransfer
    {
        $vertexAccessTokenEntity = $this->getFactory()
            ->createVertexApiAccessTokenQuery()
            ->filterByCredentialHash($vertexApiAccessTokenCriteriaTransfer->getCredentialHashOrFail())
            ->findOne();

        if (!$vertexAccessTokenEntity) {
            return (new VertexApiAccessTokenTransfer());
        }

        return $this->getFactory()->createVertexApiAccessTokenMapper()
            ->mapVertexApiAccessTokenEntityToVertexApiAccessTokenTransfer(
                $vertexAccessTokenEntity,
                (new VertexApiAccessTokenTransfer()),
            );
    }
}
