<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Vertex\Persistence;

use Generated\Shared\Transfer\VertexConfigCriteriaTransfer;
use Generated\Shared\Transfer\VertexConfigTransfer;
use Generated\Shared\Transfer\TaxIdValidationHistoryTransfer;

interface VertexEntityManagerInterface
{
    /**
     * @param \Generated\Shared\Transfer\TaxIdValidationHistoryTransfer $taxIdValidationHistoryTransfer
     *
     * @return void
     */
    public function saveTaxIdValidationHistory(TaxIdValidationHistoryTransfer $taxIdValidationHistoryTransfer): void;

    /**
     * @param \Generated\Shared\Transfer\VertexConfigTransfer $VertexConfigTransfer
     * @param array<\Generated\Shared\Transfer\StoreTransfer> $storeTransfers
     *
     * @return void
     */
    public function saveVertexConfig(VertexConfigTransfer $VertexConfigTransfer, array $storeTransfers): void;

    /**
     * @param \Generated\Shared\Transfer\VertexConfigCriteriaTransfer $VertexConfigCriteriaTransfer
     *
     * @return void
     */
    public function deleteVertexConfig(VertexConfigCriteriaTransfer $VertexConfigCriteriaTransfer): void;
}
