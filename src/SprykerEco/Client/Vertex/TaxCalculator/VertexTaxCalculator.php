<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Client\Vertex\TaxCalculator;

use Generated\Shared\Transfer\VertexCalculationRequestTransfer;
use Generated\Shared\Transfer\VertexCalculationResponseTransfer;
use Generated\Shared\Transfer\VertexConfigTransfer;
use Generated\Shared\Transfer\VertexSuppliesTransfer;
use SprykerEco\Client\Vertex\Api\V2\Client\SuppliesApiInterface;
use SprykerEco\Client\Vertex\Builder\SuppliesRequestBuilder;
use SprykerEco\Client\Vertex\ResponseBuilder\VertexSuppliesResponseBuilderInterface;
use SprykerEco\Client\Vertex\Validator\VertexValidatorInterface;

class VertexTaxCalculator implements VertexTaxCalculatorInterface
{
    protected const ERROR_MESSAGE_MISSING_VERTEX_ACCESS_TOKEN = 'Unable to connect to Vertex API: access token is invalid';

    protected const ERROR_MESSAGE_INACTIVE_VERTEX_APP = 'Unable to connect to Vertex API: Vertex App is inactive';

    protected const ERROR_MESSAGE_TRANSACTION_CALL_URI = 'Unable to connect to Vertex API: TransactionCallsUri config is not set';

    public function __construct(
        protected SuppliesRequestBuilder $vertexSuppliesRequestBuilder,
        protected SuppliesApiInterface $suppliesApi,
        protected VertexSuppliesResponseBuilderInterface $vertexSuppliesResponseBuilder,
        protected VertexValidatorInterface $quotationValidator,
    ) {
    }

    public function calculateTax(
        VertexCalculationRequestTransfer $vertexCalculationRequestTransfer,
        VertexConfigTransfer $vertexConfigTransfer
    ): VertexCalculationResponseTransfer {
        if (!$vertexConfigTransfer->getIsActive()) {
            return $this->vertexSuppliesResponseBuilder->buildErrorResponse($vertexCalculationRequestTransfer, static::ERROR_MESSAGE_INACTIVE_VERTEX_APP);
        }

        if (!$vertexConfigTransfer->getTransactionCallsUri()) {
            return $this->vertexSuppliesResponseBuilder->buildErrorResponse($vertexCalculationRequestTransfer, static::ERROR_MESSAGE_TRANSACTION_CALL_URI);
        }

        $vertexValidationResponseTransfer = $this->quotationValidator->validate($vertexCalculationRequestTransfer);

        if ($vertexValidationResponseTransfer->getIsValid() === false) {
            return $this->vertexSuppliesResponseBuilder->buildErrorResponse($vertexCalculationRequestTransfer, implode(', ', $vertexValidationResponseTransfer->getMessages()));
        }

        $vertexApiAccessTokenTransfer = $vertexCalculationRequestTransfer->getVertexApiAccessToken();
        if (!$vertexApiAccessTokenTransfer?->getAccessToken()) {
            return $this->vertexSuppliesResponseBuilder->buildErrorResponse($vertexCalculationRequestTransfer, static::ERROR_MESSAGE_MISSING_VERTEX_ACCESS_TOKEN);
        }

        $vertexCalculationRequestTransfer->setVertexConfiguration($vertexConfigTransfer);
        $vertexSuppliesRequestTransfer = $this->vertexSuppliesRequestBuilder->build(
            $vertexCalculationRequestTransfer,
            (new VertexSuppliesTransfer()),
        );

        $lineItemIdToInitialIdentifierMapping = [];
        foreach ($vertexSuppliesRequestTransfer->getLineItems() as $lineItem) {
            if ($lineItem->getInitialIdentifier() && $lineItem->getShouldBeGrouped()) {
                $lineItemIdToInitialIdentifierMapping[$lineItem->getLineItemId()] = $lineItem->getInitialIdentifier();
            }
        }

        $vertexApiResponseTransfer = $this->suppliesApi->calculateTax(
            $vertexSuppliesRequestTransfer,
            $vertexConfigTransfer,
            $vertexApiAccessTokenTransfer,
        );

        if (!$vertexApiResponseTransfer->getIsSuccessful()) {
            return $this->vertexSuppliesResponseBuilder->buildErrorResponse($vertexCalculationRequestTransfer, $vertexApiResponseTransfer->getErrorMessageOrFail());
        }

        return $this->vertexSuppliesResponseBuilder->buildResponse($vertexApiResponseTransfer, $vertexCalculationRequestTransfer, $lineItemIdToInitialIdentifierMapping);
    }
}
