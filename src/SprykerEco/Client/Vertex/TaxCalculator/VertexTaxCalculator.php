<?php

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerEco\Client\Vertex\TaxCalculator;

use Generated\Shared\Transfer\VertexCalculationRequestTransfer;
use Generated\Shared\Transfer\VertexCalculationResponseTransfer;
use Generated\Shared\Transfer\VertexSuppliesTransfer;
use Generated\Shared\Transfer\VertexConfigTransfer;
use Generated\Shared\Transfer\VertexValidationResponseTransfer;
use SprykerEco\Client\Vertex\Api\V2\Client\SuppliesApiInterface;
use SprykerEco\Client\Vertex\Builder\SuppliesRequestBuilder;
use SprykerEco\Client\Vertex\ResponseBuilder\VertexSuppliesResponseBuilderInterface;
use SprykerEco\Client\Vertex\Validator\VertexValidatorInterface;

/**
 * This class is used for tax calculation (`quotation`) AND for `invoice` request sending, depending on SuppliesRequestBuilder builders list.
 */
class VertexTaxCalculator implements VertexTaxCalculatorInterface
{
    protected const ERROR_MESSAGE_MISSING_VERTEX_ACCESS_TOKEN = 'Unable to connect to Vertex API: access token is invalid';

    protected const ERROR_MESSAGE_INACTIVE_VERTEX_APP = 'Unable to connect to Vertex API: Vertex App is inactive';

    protected const ERROR_MESSAGE_TRANSACTION_CALL_URI = 'Unable to connect to Vertex API: TransactionCallsUri config is not set';

    protected const ERROR_MESSAGE_SALE_IS_MISSED = 'Sale is missed';

    /**
     * @param \SprykerEco\Client\Vertex\Builder\SuppliesRequestBuilder $vertexSuppliesRequestBuilder
     * @param \SprykerEco\Client\Vertex\Api\V2\Client\SuppliesApiInterface $suppliesApi
     * @param \SprykerEco\Client\Vertex\ResponseBuilder\VertexSuppliesResponseBuilderInterface $vertexSuppliesResponseBuilder
     */
    public function __construct(
        protected SuppliesRequestBuilder $vertexSuppliesRequestBuilder,
        protected SuppliesApiInterface $suppliesApi,
        protected VertexSuppliesResponseBuilderInterface $vertexSuppliesResponseBuilder,
        protected VertexValidatorInterface $quotationValidator,
    ) {
    }

    /**
     * @param \Generated\Shared\Transfer\VertexCalculationRequestTransfer $vertexCalculationRequestTransfer
     * @param \Generated\Shared\Transfer\VertexConfigTransfer $vertexConfigTransfer
     *
     * @return \Generated\Shared\Transfer\VertexCalculationResponseTransfer
     */
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

        if (!$vertexValidationResponseTransfer->getIsValid()) {
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
            return $this->vertexSuppliesResponseBuilder->buildErrorResponse($vertexCalculationRequestTransfer, $vertexApiResponseTransfer->getErrorMessage());
        }

        return $this->vertexSuppliesResponseBuilder->buildResponse($vertexApiResponseTransfer, $vertexCalculationRequestTransfer, $lineItemIdToInitialIdentifierMapping);
    }
}
