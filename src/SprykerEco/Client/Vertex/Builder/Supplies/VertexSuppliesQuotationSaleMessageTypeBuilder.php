<?php

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Pyz\Zed\VertexApi\Business\Builder\Supplies;

use Generated\Shared\Transfer\TaxCalculationRequestTransfer;
use Generated\Shared\Transfer\VertexSuppliesTransfer;
use Pyz\Zed\VertexApi\Business\Builder\VertexSuppliesRequestBuilderInterface;

class VertexSuppliesQuotationSaleMessageTypeBuilder implements VertexSuppliesRequestBuilderInterface
{
    /**
     * @var string
     */
    protected const SALE_MESSAGE_TYPE = 'QUOTATION';

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
        return $vertexSuppliesTransfer->setSaleMessageType(static::SALE_MESSAGE_TYPE);
    }
}
