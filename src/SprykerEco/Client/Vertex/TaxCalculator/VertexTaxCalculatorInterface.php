<?php

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Pyz\Zed\VertexApi\Business\TaxCalculator;

use Generated\Shared\Transfer\TaxCalculationRequestTransfer;
use Generated\Shared\Transfer\TaxCalculationResponseTransfer;

interface VertexTaxCalculatorInterface
{
    /**
     * @param \Generated\Shared\Transfer\TaxCalculationRequestTransfer $taxCalculationRequestTransfer
     *
     * @return \Generated\Shared\Transfer\TaxCalculationResponseTransfer
     */
    public function calculateTax(TaxCalculationRequestTransfer $taxCalculationRequestTransfer): TaxCalculationResponseTransfer;
}
