<?php

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerEco\Zed\Vertex\Persistence;

use Generated\Shared\Transfer\VertexApiAccessTokenCriteriaTransfer;
use Generated\Shared\Transfer\VertexApiAccessTokenTransfer;
use Spryker\Zed\Kernel\Persistence\AbstractRepository;

/**
 * @method \SprykerEco\Zed\Vertex\Persistence\VertexApiPersistenceFactory getFactory()
 */
class VertexRepository extends AbstractRepository implements VertexRepositoryInterface
{
    /**
     * @param \Generated\Shared\Transfer\VertexApiAccessTokenCriteriaTransfer $vertexApiAccessTokenCriteriaTransfer
     *
     * @return \Generated\Shared\Transfer\VertexApiAccessTokenTransfer
     */
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
