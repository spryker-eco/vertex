<?php

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Pyz\Zed\VertexApi\Business\TaxCalculator;

use Generated\Shared\Transfer\TaxCalculationRequestTransfer;
use Generated\Shared\Transfer\TaxCalculationResponseTransfer;
use Generated\Shared\Transfer\VertexConfigCriteriaTransfer;
use Generated\Shared\Transfer\VertexSuppliesTransfer;
use Pyz\Zed\VertexApi\Business\AccessTokenProvider\AccessTokenProviderInterface;
use Pyz\Zed\VertexApi\Business\Api\V2\Client\SuppliesApiInterface;
use Pyz\Zed\VertexApi\Business\Builder\SuppliesRequestBuilder;
use Pyz\Zed\VertexApi\Business\ResponseBuilder\VertexSuppliesResponseBuilderInterface;
use Pyz\Zed\VertexConfig\Business\VertexConfigFacadeInterface;

/**
 * This class is used for tax calculation (`quotation`) AND for `invoice` request sending, depending on SuppliesRequestBuilder builders list.
 */
class VertexTaxCalculator implements VertexTaxCalculatorInterface
{
    /**
     * @var string
     */
    protected const ERROR_MESSAGE_MISSING_VERTEX_ACCESS_TOKEN = 'Unable to connect to Vertex API: access token is invalid';

    /**
     * @var string
     */
    protected const ERROR_MESSAGE_INACTIVE_VERTEX_APP = 'Unable to connect to Vertex API: Vertex App is inactive';

    protected SuppliesRequestBuilder $vertexSuppliesRequestBuilder;

    protected SuppliesApiInterface $suppliesApi;

    protected VertexSuppliesResponseBuilderInterface $responseBuilder;

    protected VertexConfigFacadeInterface $vertexConfigFacade;

    protected AccessTokenProviderInterface $accessTokenProvider;

    /**
     * @param \Pyz\Zed\VertexApi\Business\Builder\SuppliesRequestBuilder $vertexSuppliesRequestBuilder
     * @param \Pyz\Zed\VertexApi\Business\Api\V2\Client\SuppliesApiInterface $suppliesApi
     * @param \Pyz\Zed\VertexApi\Business\ResponseBuilder\VertexSuppliesResponseBuilderInterface $vertexSuppliesResponseBuilder
     * @param \Pyz\Zed\VertexConfig\Business\VertexConfigFacadeInterface $vertexConfigFacade
     * @param \Pyz\Zed\VertexApi\Business\AccessTokenProvider\AccessTokenProviderInterface $accessTokenProvider
     */
    public function __construct(
        SuppliesRequestBuilder $vertexSuppliesRequestBuilder,
        SuppliesApiInterface $suppliesApi,
        VertexSuppliesResponseBuilderInterface $vertexSuppliesResponseBuilder,
        VertexConfigFacadeInterface $vertexConfigFacade,
        AccessTokenProviderInterface $accessTokenProvider
    ) {
        $this->vertexSuppliesRequestBuilder = $vertexSuppliesRequestBuilder;
        $this->suppliesApi = $suppliesApi;
        $this->responseBuilder = $vertexSuppliesResponseBuilder;
        $this->vertexConfigFacade = $vertexConfigFacade;
        $this->accessTokenProvider = $accessTokenProvider;
    }

    /**
     * @param \Generated\Shared\Transfer\TaxCalculationRequestTransfer $taxCalculationRequestTransfer
     *
     * @return \Generated\Shared\Transfer\TaxCalculationResponseTransfer
     */
    public function calculateTax(TaxCalculationRequestTransfer $taxCalculationRequestTransfer): TaxCalculationResponseTransfer
    {
        $vertexConfigCriteriaTransfer = (new VertexConfigCriteriaTransfer())
            ->setStoreReference($taxCalculationRequestTransfer->getTenantIdentifierOrFail());

        $vertexConfigTransfer = $this->vertexConfigFacade->getConfig($vertexConfigCriteriaTransfer);

        if (!$vertexConfigTransfer->getIsActive()) {
            return $this->responseBuilder->buildErrorResponse($taxCalculationRequestTransfer, static::ERROR_MESSAGE_INACTIVE_VERTEX_APP);
        }

        $taxCalculationRequestTransfer->setVertexConfiguration($vertexConfigTransfer);

        $vertexApiAccessTokenTransfer = $this->accessTokenProvider->provideVertexAccessToken($vertexConfigTransfer);

        if (!$vertexApiAccessTokenTransfer->getAccessToken()) {
            return $this->responseBuilder->buildErrorResponse($taxCalculationRequestTransfer, static::ERROR_MESSAGE_MISSING_VERTEX_ACCESS_TOKEN);
        }

        $vertexSuppliesRequestTransfer = $this->vertexSuppliesRequestBuilder->build(
            $taxCalculationRequestTransfer,
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
            return $this->responseBuilder->buildErrorResponse($taxCalculationRequestTransfer, $vertexApiResponseTransfer->getErrorMessage());
        }

        return $this->responseBuilder->buildResponse($vertexApiResponseTransfer, $taxCalculationRequestTransfer, $lineItemIdToInitialIdentifierMapping);
    }
}
