<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Vertex\Business\Payment;

use Generated\Shared\Transfer\MessageAttributesTransfer;
use Generated\Shared\Transfer\OrderTransfer;
use Generated\Shared\Transfer\VertexCalculationRequestTransfer;
use Generated\Shared\Transfer\VertexSaleTransfer;
use Generated\Shared\Transfer\VertexSubmitPaymentTaxInvoiceTransfer;
use Spryker\Shared\Log\LoggerTrait;
use Spryker\Zed\Sales\Business\SalesFacadeInterface;
use Spryker\Zed\Store\Business\StoreFacadeInterface;
use SprykerEco\Client\Vertex\VertexClientInterface;
use SprykerEco\Zed\Vertex\Business\AccessTokenProvider\VertexAccessTokenProviderInterface;
use SprykerEco\Zed\Vertex\Business\Mapper\VertexMapperInterface;
use SprykerEco\Zed\Vertex\Business\Resolver\VertexConfigResolverInterface;

class PaymentSubmitTaxInvoiceHandler implements PaymentSubmitTaxInvoiceHandlerInterface
{
    use LoggerTrait;

    /**
     * @param \Spryker\Zed\Store\Business\StoreFacadeInterface $storeFacade
     * @param \Spryker\Zed\Sales\Business\SalesFacadeInterface $salesFacade
     * @param \SprykerEco\Zed\Vertex\Business\Mapper\VertexMapperInterface $vertexMapper
     * @param array<\SprykerEco\Zed\Vertex\Dependency\Plugin\OrderVertexExpanderPluginInterface|\Spryker\Zed\TaxAppExtension\Dependency\Plugin\OrderTaxAppExpanderPluginInterface> $orderVertexExpanderPlugins
     * @param \SprykerEco\Zed\Vertex\Business\AccessTokenProvider\VertexAccessTokenProviderInterface $vertexAccessTokenProvider
     * @param \SprykerEco\Zed\Vertex\Business\Resolver\VertexConfigResolverInterface $configResolver
     */
    public function __construct(
        protected StoreFacadeInterface $storeFacade,
        protected SalesFacadeInterface $salesFacade,
        protected VertexMapperInterface $vertexMapper,
        protected array $orderVertexExpanderPlugins,
        protected VertexConfigResolverInterface $configResolver,
        protected VertexAccessTokenProviderInterface $accessTokenProvider,
        protected VertexClientInterface $vertexClient,
    ) {
    }

    /**
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return void
     */
    public function handleSubmitPaymentTaxInvoice(OrderTransfer $orderTransfer): void
    {
        $idSalesOrder = $orderTransfer->getIdSalesOrderOrFail();
        $orderTransfer = $this->salesFacade->findOrderByIdSalesOrder($idSalesOrder);

        if (!$orderTransfer) {
            $this->getLogger()->warning(sprintf('Order with ID `%s` not found', $idSalesOrder));

            return;
        }

        $orderTransfer = $this->executeOrderVertexExpanderPlugins($orderTransfer);

        $vertexSaleTransfer = $this->vertexMapper->mapOrderTransferToVertexSaleTransfer($orderTransfer, new VertexSaleTransfer());

        $submitPaymentTaxInvoiceTransfer = new VertexSubmitPaymentTaxInvoiceTransfer();
        $submitPaymentTaxInvoiceTransfer->setSale($vertexSaleTransfer);

        $this->setMessageAttributesTransfer($submitPaymentTaxInvoiceTransfer, $orderTransfer);

        $vertexConfigTransfer = $this->configResolver->resolve();

        if (!$vertexConfigTransfer) {
            // TODO

            return;
        }

        if (!$vertexConfigTransfer || !$vertexConfigTransfer->getIsActive() || !$vertexConfigTransfer->getIsInvoicingEnabled()) {
            // TODO

            return;
        }

        $vertexApiAccessTokenTransfer = $this->accessTokenProvider->provideVertexAccessToken($vertexConfigTransfer);

        $vertexCalculationRequestTransfer = (new VertexCalculationRequestTransfer())
            ->setSale($vertexSaleTransfer)
            ->setVertexApiAccessToken($vertexApiAccessTokenTransfer);

        $this->getLogger()->info(
            'Starting tax calculation request for invoicing process',
            [
                'transactionId' => $vertexCalculationRequestTransfer->getSale()->getTransactionId(),
                'requestTransfer' => $vertexCalculationRequestTransfer->modifiedToArray(),
            ],
        );

        $vertexCalculationResponseTransfer = $this->vertexClient->calculateOrderTax($vertexCalculationRequestTransfer, $vertexConfigTransfer);

        $this->getLogger()->info(
            'Finished tax calculation request for invoicing process',
            [
                'transactionId' => $vertexCalculationRequestTransfer->getSale()->getTransactionId(),
                'responseTransfer' => $vertexCalculationResponseTransfer->modifiedToArray(),
            ],
        );
    }

    /**
     * @param \Generated\Shared\Transfer\SubmitPaymentTaxInvoiceTransfer $submitPaymentTaxInvoiceTransfer
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return void
     */
    protected function setMessageAttributesTransfer(
        VertexSubmitPaymentTaxInvoiceTransfer $submitPaymentTaxInvoiceTransfer,
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
