<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Zed\Vertex\Persistence\Propel\Mapper;

use Generated\Shared\Transfer\TaxIdValidationHistoryTransfer;
use Orm\Zed\Vertex\Persistence\Base\SpyVertexTaxIdValidationHistory;

class VertexTaxIdValidationMapper
{
    /**
     * @param \Generated\Shared\Transfer\TaxIdValidationHistoryTransfer $taxIdValidationHistoryTransfer
     * @param \Orm\Zed\Vertex\Persistence\Base\SpyVertexTaxIdValidationHistory $vertexTaxIdValidationHistoryEntity
     *
     * @return \Orm\Zed\Vertex\Persistence\Base\SpyVertexTaxIdValidationHistory
     */
    public function mapTaxIdValidationHistoryTransferToVertexTaxIdValidationHistoryEntity(
        TaxIdValidationHistoryTransfer $taxIdValidationHistoryTransfer,
        SpyVertexTaxIdValidationHistory $vertexTaxIdValidationHistoryEntity,
    ): SpyVertexTaxIdValidationHistory {
        return $vertexTaxIdValidationHistoryEntity->fromArray($taxIdValidationHistoryTransfer->toArray());
    }
}
