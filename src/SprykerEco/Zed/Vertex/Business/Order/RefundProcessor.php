<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Vertex\Business\Order;

use ArrayObject;
use DateTime;
use Generated\Shared\Transfer\OrderTransfer;
use Generated\Shared\Transfer\VertexSaleTransfer;
use Generated\Shared\Transfer\TaxRefundRequestTransfer;
use Spryker\Client\Vertex\VertexClientInterface;
use Spryker\Shared\Log\LoggerTrait;
use SprykerEco\Zed\Vertex\Business\AccessTokenProvider\AccessTokenProviderInterface;
use SprykerEco\Zed\Vertex\Business\Config\ConfigReaderInterface;
use SprykerEco\Zed\Vertex\Business\Mapper\VertexMapperInterface;
use SprykerEco\Zed\Vertex\Dependency\Facade\VertexToSalesFacadeInterface;
use SprykerEco\Zed\Vertex\Dependency\Facade\VertexToStoreFacadeInterface;

class RefundProcessor implements RefundProcessorInterface
{
    use LoggerTrait;

    /**
     * @param \SprykerEco\Client\Vertex\VertexClientInterface $VertexClient
     * @param \SprykerEco\Zed\Vertex\Dependency\Facade\VertexToStoreFacadeInterface $storeFacade
     * @param \SprykerEco\Zed\Vertex\Dependency\Facade\VertexToSalesFacadeInterface $salesFacade
     * @param \SprykerEco\Zed\Vertex\Business\Mapper\VertexMapperInterface $VertexMapper
     * @param \SprykerEco\Zed\Vertex\Business\AccessTokenProvider\AccessTokenProviderInterface $accessTokenProvider
     * @param \SprykerEco\Zed\Vertex\Business\Config\ConfigReaderInterface $configReader
     * @param array<\SprykerEco\Zed\VertexExtension\Dependency\Plugin\OrderVertexExpanderPluginInterface> $orderVertexExpanderPlugins // TODO
     */
    public function __construct(
        VertexClientInterface $vertexClient,
        VertexToStoreFacadeInterface $storeFacade,
        VertexToSalesFacadeInterface $salesFacade,
        VertexMapperInterface $VertexMapper,
        AccessTokenProviderInterface $accessTokenProvider,
        ConfigReaderInterface $configReader,
        array $orderVertexExpanderPlugins
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
        $VertexConfigTransfer = $this->configReader->getVertexConfigByIdStore($storeTransfer->getIdStoreOrFail());

        if ($VertexConfigTransfer === null || !$VertexConfigTransfer->getIsActive()) {
            $this->getLogger()->warning('App is not configured or is not active.');

            return;
        }

        $orderTransfer = $this->executeOrderVertexExpanderPlugins($orderTransfer);

        $VertexSaleTransfer = $this->VertexMapper->mapOrderTransferToVertexSaleTransfer($orderTransfer, new VertexSaleTransfer());

        $taxRefundRequestTransfer = new TaxRefundRequestTransfer();
        $taxRefundRequestTransfer->setSale($VertexSaleTransfer);
        $taxRefundRequestTransfer->setReportingDate((new DateTime())->format('Y-m-d'));

        $taxRefundRequestTransfer = $this->expandTaxRefundRequestWithAccessToken($taxRefundRequestTransfer);

        $this->vertexClient->requestTaxRefund($taxRefundRequestTransfer, $VertexConfigTransfer, $storeTransfer); // TODO
    }

    /**
     * @param \Generated\Shared\Transfer\TaxRefundRequestTransfer $taxRefundRequestTransfer
     *
     * @return \Generated\Shared\Transfer\TaxRefundRequestTransfer
     */
    protected function expandTaxRefundRequestWithAccessToken(
        TaxRefundRequestTransfer $taxRefundRequestTransfer
    ): TaxRefundRequestTransfer {
        $taxRefundRequestTransfer->setAuthorization($this->accessTokenProvider->getAccessToken());

        return $taxRefundRequestTransfer;
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
