<?php

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Pyz\Zed\VertexApi\Business\ResponseBuilder;

use Generated\Shared\Transfer\TaxCalculationRequestTransfer;
use Generated\Shared\Transfer\TaxCalculationResponseTransfer;
use Generated\Shared\Transfer\VertexApiResponseTransfer;

interface VertexSuppliesResponseBuilderInterface
{
    /**
     * @param \Generated\Shared\Transfer\VertexApiResponseTransfer $vertexApiResponseTransfer
     * @param \Generated\Shared\Transfer\TaxCalculationRequestTransfer $taxCalculationRequestTransfer
     * @param array<string, string> $lineItemIdToInitialIdentifierMapping
     *
     * @return \Generated\Shared\Transfer\TaxCalculationResponseTransfer
     */
    public function buildResponse(
        VertexApiResponseTransfer $vertexApiResponseTransfer,
        TaxCalculationRequestTransfer $taxCalculationRequestTransfer,
        array $lineItemIdToInitialIdentifierMapping
    ): TaxCalculationResponseTransfer;

    /**
     * @param \Generated\Shared\Transfer\TaxCalculationRequestTransfer $taxCalculationRequestTransfer
     * @param string $errorMessage
     *
     * @return \Generated\Shared\Transfer\TaxCalculationResponseTransfer
     */
    public function buildErrorResponse(
        TaxCalculationRequestTransfer $taxCalculationRequestTransfer,
        string $errorMessage
    ): TaxCalculationResponseTransfer;
}
