<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Vertex\Business\Order;

use ArrayObject;
use DateTime;
use Generated\Shared\Transfer\OrderTransfer;
use Generated\Shared\Transfer\VertexCalculationRequestTransfer;
use Generated\Shared\Transfer\VertexCalculationResponseTransfer;
use Generated\Shared\Transfer\VertexSaleTransfer;
use Spryker\Shared\Log\LoggerTrait;
use SprykerEco\Client\Vertex\VertexClientInterface;
use SprykerEco\Zed\Vertex\Business\AccessTokenProvider\AccessTokenProviderInterface;
use SprykerEco\Zed\Vertex\Business\AccessTokenProvider\VertexAccessTokenProviderInterface;
use SprykerEco\Zed\Vertex\Business\Config\ConfigReaderInterface;
use SprykerEco\Zed\Vertex\Business\Mapper\vertexMapperInterface;
use SprykerEco\Zed\Vertex\Business\Resolver\VertexConfigResolverInterface;
use SprykerEco\Zed\Vertex\Dependency\Facade\VertexToSalesFacadeInterface;
use SprykerEco\Zed\Vertex\Dependency\Facade\VertexToStoreFacadeInterface;

class RefundProcessor implements RefundProcessorInterface
{
    use LoggerTrait;

    /**
     * @param \SprykerEco\Client\Vertex\VertexClientInterface $VertexClient
     * @param \SprykerEco\Zed\Vertex\Dependency\Facade\VertexToStoreFacadeInterface $storeFacade
     * @param \SprykerEco\Zed\Vertex\Dependency\Facade\VertexToSalesFacadeInterface $salesFacade
     * @param \SprykerEco\Zed\Vertex\Business\Mapper\vertexMapperInterface $vertexMapper
     * @param \SprykerEco\Zed\Vertex\Business\Config\ConfigReaderInterface $configReader
     * @param array<\SprykerEco\Zed\Vertex\Dependency\Plugin\OrderVertexExpanderPluginInterface> $orderVertexExpanderPlugins // TODO
     */
    public function __construct(
        protected VertexClientInterface $vertexClient,
        protected VertexToStoreFacadeInterface $storeFacade,
        protected VertexToSalesFacadeInterface $salesFacade,
        protected vertexMapperInterface $vertexMapper,
        protected array $orderVertexExpanderPlugins,
        protected VertexAccessTokenProviderInterface $vertexAccessTokenProvider,
        protected VertexConfigResolverInterface $configResolver,
    ) {}

    /**
     * @param array<int> $orderItemIds
     * @param int $idSalesOrder
     *
     * @return void
     */
    public function processOrderRefund(array $orderItemIds, int $idSalesOrder): void
    {
        $orderTransfer = $this->createOrderWithItemsToBeRefunded($orderItemIds, $idSalesOrder);

        if (!$orderTransfer) {
            $this->getLogger()->warning(sprintf('Order with ID `%s` not found', $idSalesOrder));

            return;
        }

        if ($orderTransfer->getStore() === null) {
            $this->getLogger()->warning('Store from order not found');

            return;
        }

        $storeTransfer = $this->storeFacade->getStoreByName($orderTransfer->getStoreOrFail());
        $vertexConfigTransfer = $this->configResolver->resolve($storeTransfer->getIdStoreOrFail());

        if ($vertexConfigTransfer === null || !$vertexConfigTransfer->getIsActive()) {
            $this->getLogger()->warning('App is not configured or is not active.');

            return;
        }

        $orderTransfer = $this->executeOrderVertexExpanderPlugins($orderTransfer);

        $vertexSaleTransfer = $this->vertexMapper->mapOrderTransferToVertexSaleTransfer($orderTransfer, new VertexSaleTransfer());

        if (!$vertexConfigTransfer->getIsActive() || !$vertexConfigTransfer->getIsInvoicingEnabled()) {
            $vertexCalculationResponseTransfer = new VertexCalculationResponseTransfer();
            $vertexCalculationResponseTransfer->setIsSuccessful(false);
            $vertexCalculationResponseTransfer->setErrorMessage('App is Inactive or configured to not submit void invoice');

            return;
        }

        $vertexApiAccessTokenTransfer = $this->vertexAccessTokenProvider->provideVertexAccessToken($vertexConfigTransfer);

        //TODO: Add an early return if the access token is not available
        $this->vertexClient->calculateTax(
            (new VertexCalculationRequestTransfer())
                ->setSale($vertexSaleTransfer)
                ->setReportingDate((new DateTime())->format('Y-m-d'))
                ->setVertexApiAccessToken($vertexApiAccessTokenTransfer),
            $vertexConfigTransfer
        );
    }

    /**
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return \Generated\Shared\Transfer\OrderTransfer
     */
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

            if (in_array($itemId, $orderItemIds)) {
                $newOrderItems[] = $item;
            }
        }

        $orderTransfer->setItems(new ArrayObject($newOrderItems));

        return $orderTransfer;
    }
}
