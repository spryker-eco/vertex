<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Client\Vertex\ResponseBuilder;

use Generated\Shared\Transfer\VertexApiResponseTransfer;
use Generated\Shared\Transfer\VertexCalculationRequestTransfer;
use Generated\Shared\Transfer\VertexCalculationResponseTransfer;

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
