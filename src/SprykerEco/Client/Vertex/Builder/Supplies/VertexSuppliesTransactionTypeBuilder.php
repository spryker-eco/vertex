<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Client\Vertex\Builder\Supplies;

use Generated\Shared\Transfer\VertexCalculationRequestTransfer;
use Generated\Shared\Transfer\VertexSuppliesTransfer;
use SprykerEco\Client\Vertex\Builder\VertexSuppliesRequestBuilderInterface;

class VertexSuppliesTransactionTypeBuilder implements VertexSuppliesRequestBuilderInterface
{
    protected const TRANSACTION_TYPE = 'SALE';

    public function build(
        VertexCalculationRequestTransfer $vertexCalculationRequestTransfer, // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter
        VertexSuppliesTransfer $vertexSuppliesTransfer,
    ): VertexSuppliesTransfer {
        return $vertexSuppliesTransfer->setTransactionType(static::TRANSACTION_TYPE);
    }
}
