<?php

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerEco\Zed\Vertex\Persistence;

use Generated\Shared\Transfer\TaxIdValidationHistoryTransfer;
use Generated\Shared\Transfer\VertexApiAccessTokenTransfer;

interface VertexEntityManagerInterface
{
    /**
     * @param \Generated\Shared\Transfer\VertexApiAccessTokenTransfer $vertexApiAccessTokenTransfer
     *
     * @return void
     */
    public function saveAccessToken(VertexApiAccessTokenTransfer $vertexApiAccessTokenTransfer): void;

    /**
     * @param \Generated\Shared\Transfer\TaxIdValidationHistoryTransfer $taxIdValidationHistoryTransfer
     *
     * @return void
     */
    public function saveTaxIdValidationHistory(TaxIdValidationHistoryTransfer $taxIdValidationHistoryTransfer): void;
}
