<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Zed\Vertex\Business\Order;

use ArrayObject;
use DateTime;
use Generated\Shared\Transfer\OrderTransfer;
use Generated\Shared\Transfer\VertexCalculationRequestTransfer;
use Generated\Shared\Transfer\VertexCalculationResponseTransfer;
use Generated\Shared\Transfer\VertexSaleTransfer;
use Spryker\Shared\Log\LoggerTrait;
use Spryker\Zed\Sales\Business\SalesFacadeInterface;
use Spryker\Zed\Store\Business\StoreFacadeInterface;
use SprykerEco\Client\Vertex\VertexClientInterface;
use SprykerEco\Zed\Vertex\Business\AccessTokenProvider\VertexAccessTokenProviderInterface;
use SprykerEco\Zed\Vertex\Business\Mapper\VertexMapperInterface;
use SprykerEco\Zed\Vertex\Business\Resolver\VertexConfigResolverInterface;

class RefundProcessor implements RefundProcessorInterface
{
    use LoggerTrait;

    protected const ERROR_MESSAGE_MISSING_VERTEX_ACCESS_TOKEN = 'Unable to connect to Vertex API: access token is invalid';

    /**
     * @param \SprykerEco\Client\Vertex\VertexClientInterface $vertexClient
     * @param \Spryker\Zed\Store\Business\StoreFacadeInterface $storeFacade
     * @param \Spryker\Zed\Sales\Business\SalesFacadeInterface $salesFacade
     * @param \SprykerEco\Zed\Vertex\Business\Mapper\VertexMapperInterface $vertexMapper
     * @param array<\SprykerEco\Zed\Vertex\Dependency\Plugin\OrderVertexExpanderPluginInterface|\Spryker\Zed\TaxAppExtension\Dependency\Plugin\OrderTaxAppExpanderPluginInterface> $orderVertexExpanderPlugins
     * @param \SprykerEco\Zed\Vertex\Business\AccessTokenProvider\VertexAccessTokenProviderInterface $vertexAccessTokenProvider
     * @param \SprykerEco\Zed\Vertex\Business\Resolver\VertexConfigResolverInterface $configResolver
     */
    public function __construct(
        protected VertexClientInterface $vertexClient,
        protected StoreFacadeInterface $storeFacade,
        protected SalesFacadeInterface $salesFacade,
        protected VertexMapperInterface $vertexMapper,
        protected array $orderVertexExpanderPlugins,
        protected VertexAccessTokenProviderInterface $vertexAccessTokenProvider,
        protected VertexConfigResolverInterface $configResolver,
    ) {
    }

    /**
     * @param array<int> $orderItemIds
     * @param int $idSalesOrder
     *
     * @return \Generated\Shared\Transfer\VertexCalculationResponseTransfer
     */
    public function processOrderRefund(array $orderItemIds, int $idSalesOrder): VertexCalculationResponseTransfer
    {
        $vertexCalculationResponseTransfer = (new VertexCalculationResponseTransfer())
            ->setIsSuccessful(false);
        $vertexConfigTransfer = $this->configResolver->resolve();

        if (!$vertexConfigTransfer->getIsActive()) {
            $this->getLogger()->warning('App is not configured or is not active.');

            return $vertexCalculationResponseTransfer;
        }

        if (!$vertexConfigTransfer->getIsInvoicingEnabled()) {
            $this->getLogger()->warning('App is Inactive or configured to not submit void invoice');

            return $vertexCalculationResponseTransfer;
        }

        $orderTransfer = $this->createOrderWithItemsToBeRefunded($orderItemIds, $idSalesOrder);

        if (!$orderTransfer) {
            $this->getLogger()->warning(sprintf('Order with ID `%s` not found', $idSalesOrder));

            return $vertexCalculationResponseTransfer;
        }

        if ($orderTransfer->getStore() === null) {
            $this->getLogger()->warning('Store from order not found');

            return $vertexCalculationResponseTransfer;
        }

        $orderTransfer = $this->executeOrderVertexExpanderPlugins($orderTransfer);

        $vertexSaleTransfer = $this->vertexMapper->mapOrderTransferToVertexSaleTransfer($orderTransfer, new VertexSaleTransfer());

        $vertexApiAccessTokenTransfer = $this->vertexAccessTokenProvider->provideVertexAccessToken($vertexConfigTransfer);

        if (!$vertexApiAccessTokenTransfer->getAccessToken()) {
            $this->getLogger()->warning(static::ERROR_MESSAGE_MISSING_VERTEX_ACCESS_TOKEN);

            return (new VertexCalculationResponseTransfer())
                ->setSale($vertexSaleTransfer)
                ->setIsSuccessful(false)
                ->setErrorMessage(static::ERROR_MESSAGE_MISSING_VERTEX_ACCESS_TOKEN);
        }

        return $this->vertexClient->sendTaxRefund(
            (new VertexCalculationRequestTransfer())
                ->setSale($vertexSaleTransfer)
                ->setReportingDate((new DateTime())->format('Y-m-d'))
                ->setVertexApiAccessToken($vertexApiAccessTokenTransfer),
            $vertexConfigTransfer,
        );
    }

    protected function executeOrderVertexExpanderPlugins(OrderTransfer $orderTransfer): OrderTransfer
    {
        foreach ($this->orderVertexExpanderPlugins as $orderVertexExpanderPlugin) {
            $orderTransfer = $orderVertexExpanderPlugin->expand($orderTransfer);
        }

        return $orderTransfer;
    }

    /**
     * @param array<int> $orderItemIds
     * @param int $idSalesOrder
     *
     * @return \Generated\Shared\Transfer\OrderTransfer|null
     */
    protected function createOrderWithItemsToBeRefunded(array $orderItemIds, int $idSalesOrder): ?OrderTransfer
    {
        $orderTransfer = $this->salesFacade->findOrderByIdSalesOrder($idSalesOrder);

        if (!$orderTransfer) {
            return null;
        }

        $newOrderItems = [];
        foreach ($orderTransfer->getItems() as $item) {
            $itemId = $item->getIdSalesOrderItemOrFail();

            if (!in_array($itemId, $orderItemIds)) {
                continue;
            }

            $newOrderItems[] = $item;
        }

        $orderTransfer->setItems(new ArrayObject($newOrderItems));

        return $orderTransfer;
    }
}
