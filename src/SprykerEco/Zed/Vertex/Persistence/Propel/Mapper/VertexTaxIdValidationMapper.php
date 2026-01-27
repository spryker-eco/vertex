<?php

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerEco\Zed\Vertex\Persistence\Propel\Mapper;

use Generated\Shared\Transfer\TaxIdValidationHistoryTransfer;
use Orm\Zed\Vertex\Persistence\Base\SpyVertexTaxIdValidationHistory;
    
class VertexTaxIdValidationMapper
{
    /**
     * @param \Generated\Shared\Transfer\TaxIdValidationHistoryTransfer $taxIdValidationHistoryTransfer
     * @param \Orm\Zed\Vertex\Persistence\Base\SpyVertexTaxIdValidationHistory $taxIdValidationHistoryEntity
     *
     * @return \Orm\Zed\Vertex\Persistence\Base\SpyVertexTaxIdValidationHistory
     */
    public function mapTaxIdValidationHistoryTransferToVertexTaxIdValidationHistoryEntity(
        TaxIdValidationHistoryTransfer $taxIdValidationHistoryTransfer,
        SpyVertexTaxIdValidationHistory $vertexTaxIdValidationHistoryEntity
    ): SpyVertexTaxIdValidationHistory {
        return $vertexTaxIdValidationHistoryEntity->fromArray($taxIdValidationHistoryTransfer->toArray());
    }
}
