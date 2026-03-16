<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Zed\Vertex\Business\Order;

use Generated\Shared\Transfer\VertexCalculationResponseTransfer;

interface RefundProcessorInterface
{
    /**
     * @param array<int> $orderItemIds
     * @param int $idSalesOrder
     *
     * @return \Generated\Shared\Transfer\VertexCalculationResponseTransfer
     */
    public function processOrderRefund(array $orderItemIds, int $idSalesOrder): VertexCalculationResponseTransfer;
}
