<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Zed\Vertex\Business\Aggregator;

use Generated\Shared\Transfer\CalculableObjectTransfer;
use Generated\Shared\Transfer\VertexSaleTransfer;

interface PriceAggregatorInterface
{
    public function calculatePriceAggregation(
        VertexSaleTransfer $VertexSaleTransfer,
        CalculableObjectTransfer $calculableObjectTransfer,
    ): CalculableObjectTransfer;
}
