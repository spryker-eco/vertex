<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Vertex\Business\Sender;

use Generated\Shared\Transfer\MessageAttributesTransfer;
use Generated\Shared\Transfer\OrderTransfer;
use Generated\Shared\Transfer\SubmitPaymentTaxInvoiceTransfer;
use Generated\Shared\Transfer\VertexSaleTransfer;
use Spryker\Shared\Log\LoggerTrait;
use SprykerEco\Zed\Vertex\Business\Mapper\VertexMapperInterface;
use SprykerEco\Zed\Vertex\Dependency\Facade\VertexToMessageBrokerFacadeInterface;
use SprykerEco\Zed\Vertex\Dependency\Facade\VertexToSalesFacadeInterface;
use SprykerEco\Zed\Vertex\Dependency\Facade\VertexToStoreFacadeInterface;

class PaymentSubmitTaxInvoiceSender implements PaymentSubmitTaxInvoiceSenderInterface
{
    use LoggerTrait;

    /**
     * @var \Spryker\Zed\Vertex\Dependency\Facade\VertexToMessageBrokerFacadeInterface
     */
    protected VertexToMessageBrokerFacadeInterface $messageBrokerFacade;

    /**
     * @var \Spryker\Zed\Vertex\Dependency\Facade\VertexToStoreFacadeInterface
     */
    protected VertexToStoreFacadeInterface $storeFacade;

    /**
     * @var \Spryker\Zed\Vertex\Dependency\Facade\VertexToSalesFacadeInterface
     */
    protected VertexToSalesFacadeInterface $salesFacade;

    /**
     * @var \Spryker\Zed\Vertex\Business\Mapper\VertexMapperInterface
     */
    protected VertexMapperInterface $vertexMapper;

    /**
     * @var array<\Spryker\Zed\VertexExtension\Dependency\Plugin\OrderVertexExpanderPluginInterface>
     */
    protected array $orderVertexExpanderPlugins;

    /**
     * @param \Spryker\Zed\Vertex\Dependency\Facade\VertexToMessageBrokerFacadeInterface $messageBrokerFacade
     * @param \Spryker\Zed\Vertex\Dependency\Facade\VertexToStoreFacadeInterface $storeFacade
     * @param \Spryker\Zed\Vertex\Dependency\Facade\VertexToSalesFacadeInterface $salesFacade
     * @param \Spryker\Zed\Vertex\Business\Mapper\VertexMapperInterface $vertexMapper
     * @param array<\Spryker\Zed\VertexExtension\Dependency\Plugin\OrderVertexExpanderPluginInterface> $orderVertexExpanderPlugins
     */
    public function __construct(
        VertexToMessageBrokerFacadeInterface $messageBrokerFacade,
        VertexToStoreFacadeInterface $storeFacade,
        VertexToSalesFacadeInterface $salesFacade,
        VertexMapperInterface $vertexMapper,
        array $orderVertexExpanderPlugins
    ) {
        $this->messageBrokerFacade = $messageBrokerFacade;
        $this->storeFacade = $storeFacade;
        $this->salesFacade = $salesFacade;
        $this->vertexMapper = $vertexMapper;
        $this->orderVertexExpanderPlugins = $orderVertexExpanderPlugins;
    }

    /**
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return void
     */
    public function sendSubmitPaymentTaxInvoiceMessage(OrderTransfer $orderTransfer): void
    {
        $idSalesOrder = $orderTransfer->getIdSalesOrderOrFail();
        $orderTransfer = $this->salesFacade->findOrderByIdSalesOrder($idSalesOrder);

        if (!$orderTransfer) {
            $this->getLogger()->warning(sprintf('Order with ID `%s` not found', $idSalesOrder));

            return;
        }

        $orderTransfer = $this->executeOrderVertexExpanderPlugins($orderTransfer);

        $vertexSaleTransfer = $this->vertexMapper->mapOrderTransferToVertexSaleTransfer($orderTransfer, new VertexSaleTransfer());

        $submitPaymentTaxInvoiceTransfer = new SubmitPaymentTaxInvoiceTransfer();
        $submitPaymentTaxInvoiceTransfer->setSale($vertexSaleTransfer);

        $this->setMessageAttributesTransfer($submitPaymentTaxInvoiceTransfer, $orderTransfer);
//TODO: Check it we need to use a MessageBroker.

        $this->messageBrokerFacade->sendMessage($submitPaymentTaxInvoiceTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\SubmitPaymentTaxInvoiceTransfer $submitPaymentTaxInvoiceTransfer
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return void
     */
    protected function setMessageAttributesTransfer(
        SubmitPaymentTaxInvoiceTransfer $submitPaymentTaxInvoiceTransfer,
        OrderTransfer $orderTransfer
    ): void {
        $storeTransfer = $this->storeFacade->getStoreByName($orderTransfer->getStoreOrFail());

        $messageAttributesTransfer = new MessageAttributesTransfer();
        $messageAttributesTransfer->setStoreReference($storeTransfer->getStoreReference());

        $submitPaymentTaxInvoiceTransfer->setMessageAttributes($messageAttributesTransfer);
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
}
