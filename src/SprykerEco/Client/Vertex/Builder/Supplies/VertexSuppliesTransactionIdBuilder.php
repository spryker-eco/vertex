<?php

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Pyz\Zed\VertexApi\Business\Builder\Supplies;

use Generated\Shared\Transfer\TaxCalculationRequestTransfer;
use Generated\Shared\Transfer\VertexSuppliesTransfer;
use Pyz\Zed\VertexApi\Business\Builder\VertexSuppliesRequestBuilderInterface;

class VertexSuppliesTransactionIdBuilder implements VertexSuppliesRequestBuilderInterface
{
    /**
     * @param \Generated\Shared\Transfer\TaxCalculationRequestTransfer $taxCalculationRequestTransfer
     * @param \Generated\Shared\Transfer\VertexSuppliesTransfer $vertexSuppliesTransfer
     *
     * @return \Generated\Shared\Transfer\VertexSuppliesTransfer
     */
    public function build(
        TaxCalculationRequestTransfer $taxCalculationRequestTransfer,
        VertexSuppliesTransfer $vertexSuppliesTransfer
    ): VertexSuppliesTransfer {
        return $vertexSuppliesTransfer->setTransactionId(
            $taxCalculationRequestTransfer->getSaleOrFail()->getTransactionIdOrFail(),
        );
    }
}
