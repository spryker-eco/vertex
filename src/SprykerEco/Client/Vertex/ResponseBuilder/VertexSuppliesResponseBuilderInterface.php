<?php

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerEco\Client\Vertex\ResponseBuilder;

use Generated\Shared\Transfer\VertexCalculationRequestTransfer;
use Generated\Shared\Transfer\VertexCalculationResponseTransfer;
use Generated\Shared\Transfer\VertexApiResponseTransfer;

interface VertexSuppliesResponseBuilderInterface
{
    /**
     * @param \Generated\Shared\Transfer\VertexApiResponseTransfer $vertexApiResponseTransfer
     * @param \Generated\Shared\Transfer\VertexCalculationRequestTransfer $vertexCalculationRequestTransfer
     * @param array<string, string> $lineItemIdToInitialIdentifierMapping
     *
     * @return \Generated\Shared\Transfer\VertexCalculationResponseTransfer
     */
    public function buildResponse(
        VertexApiResponseTransfer $vertexApiResponseTransfer,
        VertexCalculationRequestTransfer $vertexCalculationRequestTransfer,
        array $lineItemIdToInitialIdentifierMapping
    ): VertexCalculationResponseTransfer;

    /**
     * @param \Generated\Shared\Transfer\VertexCalculationRequestTransfer $vertexCalculationRequestTransfer
     * @param string $errorMessage
     *
     * @return \Generated\Shared\Transfer\VertexCalculationResponseTransfer
     */
    public function buildErrorResponse(
        VertexCalculationRequestTransfer $vertexCalculationRequestTransfer,
        string $errorMessage
    ): VertexCalculationResponseTransfer;
}
