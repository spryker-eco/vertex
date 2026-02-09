<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Vertex\Persistence;

use Generated\Shared\Transfer\TaxIdValidationHistoryTransfer;
use Generated\Shared\Transfer\VertexApiAccessTokenTransfer;

interface VertexEntityManagerInterface
{
    public function saveAccessToken(VertexApiAccessTokenTransfer $vertexApiAccessTokenTransfer): void;

    public function saveTaxIdValidationHistory(TaxIdValidationHistoryTransfer $taxIdValidationHistoryTransfer): void;
}
