<?php

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Pyz\Zed\VertexApi\Business\Builder\Supplies;

use Generated\Shared\Transfer\TaxCalculationRequestTransfer;
use Generated\Shared\Transfer\VertexSuppliesTransfer;
use Pyz\Zed\VertexApi\Business\Builder\VertexSuppliesRequestBuilderInterface;

class VertexSuppliesPostingDateBuilder implements VertexSuppliesRequestBuilderInterface
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
        if ($taxCalculationRequestTransfer->getReportingDate()) {
            return $vertexSuppliesTransfer->setPostingDate(
                $taxCalculationRequestTransfer->getReportingDateOrFail(),
            );
        }

        return $vertexSuppliesTransfer;
    }
}
