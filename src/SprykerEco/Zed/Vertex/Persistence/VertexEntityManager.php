<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Vertex\Persistence;

use Generated\Shared\Transfer\TaxIdValidationHistoryTransfer;
use Generated\Shared\Transfer\VertexApiAccessTokenTransfer;
use Orm\Zed\Vertex\Persistence\SpyVertexApiAccessToken;
use Orm\Zed\Vertex\Persistence\SpyVertexTaxIdValidationHistory;
use Spryker\Zed\Kernel\Persistence\AbstractEntityManager;

/**
 * @method \SprykerEco\Zed\Vertex\Persistence\VertexPersistenceFactory getFactory()
 */
class VertexEntityManager extends AbstractEntityManager implements VertexEntityManagerInterface
{
    /**
     * @param \Generated\Shared\Transfer\TaxIdValidationHistoryTransfer $taxIdValidationHistoryTransfer
     *
     * @return void
     */
    public function saveTaxIdValidationHistory(TaxIdValidationHistoryTransfer $taxIdValidationHistoryTransfer): void
    {
        $taxIdValidationHistoryEntity = new SpyVertexTaxIdValidationHistory();
        $this->getFactory()
            ->createVertexTaxIdValidationMapper()
            ->mapTaxIdValidationHistoryTransferToVertexTaxIdValidationHistoryEntity($taxIdValidationHistoryTransfer, $taxIdValidationHistoryEntity);

        $taxIdValidationHistoryEntity->save();
    }

    /**
     * @param \Generated\Shared\Transfer\VertexApiAccessTokenTransfer $vertexApiAccessTokenTransfer
     *
     * @return void
     */
    public function saveAccessToken(VertexApiAccessTokenTransfer $vertexApiAccessTokenTransfer): void
    {
        $vertexApiAccessTokenEntity = $this->getFactory()
            ->createVertexApiAccessTokenQuery()
            ->findOneByCredentialHash($vertexApiAccessTokenTransfer->getCredentialHashOrFail());

        if (!$vertexApiAccessTokenEntity) {
            $vertexApiAccessTokenEntity = new SpyVertexApiAccessToken();
        }

        $vertexApiCredentialMapper = $this->getFactory()->createVertexApiAccessTokenMapper();

        $vertexApiAccessTokenEntity = $vertexApiCredentialMapper->mapVertexApiAccessTokenTransferToVertexApiAccessTokenEntity(
            $vertexApiAccessTokenTransfer,
            $vertexApiAccessTokenEntity,
        );

        $vertexApiAccessTokenEntity->save();
    }
}
