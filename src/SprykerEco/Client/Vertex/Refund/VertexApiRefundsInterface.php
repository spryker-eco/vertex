<?php

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Pyz\Zed\VertexApi\Business\Refund;

use Generated\Shared\Transfer\TaxCalculationRequestTransfer;
use Generated\Shared\Transfer\TaxCalculationResponseTransfer;

interface VertexApiRefundsInterface
{
    /**
     * @param \Generated\Shared\Transfer\TaxCalculationRequestTransfer $taxCalculationRequestTransfer
     *
     * @return \Generated\Shared\Transfer\TaxCalculationResponseTransfer
     */
    public function submitVoidPaymentTaxInvoice(TaxCalculationRequestTransfer $taxCalculationRequestTransfer): TaxCalculationResponseTransfer;
}
