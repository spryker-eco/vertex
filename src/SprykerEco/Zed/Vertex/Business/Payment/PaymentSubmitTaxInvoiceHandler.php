<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Zed\Vertex\Business\Payment;

use Generated\Shared\Transfer\MessageAttributesTransfer;
use Generated\Shared\Transfer\OrderTransfer;
use Generated\Shared\Transfer\VertexCalculationRequestTransfer;
use Generated\Shared\Transfer\VertexCalculationResponseTransfer;
use Generated\Shared\Transfer\VertexSaleTransfer;
use Generated\Shared\Transfer\VertexSubmitPaymentTaxInvoiceTransfer;
use Spryker\Shared\Log\LoggerTrait;
use Spryker\Zed\Sales\Business\SalesFacadeInterface;
use Spryker\Zed\Store\Business\StoreFacadeInterface;
use SprykerEco\Client\Vertex\VertexClientInterface;
use SprykerEco\Zed\Vertex\Business\AccessTokenProvider\VertexAccessTokenProviderInterface;
use SprykerEco\Zed\Vertex\Business\Mapper\VertexMapperInterface;
use SprykerEco\Zed\Vertex\Business\Resolver\VertexConfigResolverInterface;
use SprykerEco\Zed\Vertex\Dependency\Plugin\OrderVertexExpanderPluginInterface;

class PaymentSubmitTaxInvoiceHandler implements PaymentSubmitTaxInvoiceHandlerInterface
{
    use LoggerTrait;

    /**
     * @param StoreFacadeInterface $storeFacade
     * @param SalesFacadeInterface $salesFacade
     * @param VertexMapperInterface $vertexMapper
     * @param array<\SprykerEco\Zed\Vertex\Dependency\Plugin\OrderVertexExpanderPluginInterface> $orderVertexExpanderPlugins
     * @param VertexConfigResolverInterface $configResolver
     * @param VertexAccessTokenProviderInterface $vertexAccessTokenProvider
     * @param VertexClientInterface $vertexClient
     */
    public function __construct(
        protected StoreFacadeInterface $storeFacade,
        protected SalesFacadeInterface $salesFacade,
        protected VertexMapperInterface $vertexMapper,
        protected array $orderVertexExpanderPlugins,
        protected VertexConfigResolverInterface $configResolver,
        protected VertexAccessTokenProviderInterface $vertexAccessTokenProvider,
        protected VertexClientInterface $vertexClient,
    ) {
    }

    public function handleSubmitPaymentTaxInvoice(OrderTransfer $orderTransfer): VertexCalculationResponseTransfer
    {
        $idSalesOrder = $orderTransfer->getIdSalesOrderOrFail();
        $orderTransfer = $this->salesFacade->findOrderByIdSalesOrder($idSalesOrder);

        $vertexCalculationResponseTransfer = (new VertexCalculationResponseTransfer())->setIsSuccessful(false);

        if (!$orderTransfer) {
            $this->getLogger()->warning(sprintf('Order with ID `%s` not found', $idSalesOrder));

            return $vertexCalculationResponseTransfer;
        }

        $orderTransfer = $this->executeOrderVertexExpanderPlugins($orderTransfer);

        $vertexSaleTransfer = $this->vertexMapper->mapOrderTransferToVertexSaleTransfer($orderTransfer, new VertexSaleTransfer());

        $submitPaymentTaxInvoiceTransfer = new VertexSubmitPaymentTaxInvoiceTransfer();
        $submitPaymentTaxInvoiceTransfer->setSale($vertexSaleTransfer);

        $this->setMessageAttributesTransfer($submitPaymentTaxInvoiceTransfer, $orderTransfer);

        $vertexConfigTransfer = $this->configResolver->resolve();

        if (!$vertexConfigTransfer) {
            $this->getLogger()->warning('Vertex configuration not found');

            return $vertexCalculationResponseTransfer;
        }

        if (!$vertexConfigTransfer->getIsActive()) {
            $this->getLogger()->warning('Vertex configuration not active');

            return $vertexCalculationResponseTransfer;
        }

        if (!$vertexConfigTransfer->getIsInvoicingEnabled()) {
            $this->getLogger()->warning('Invoicing configuration not active');

            return $vertexCalculationResponseTransfer;
        }

        $vertexApiAccessTokenTransfer = $this->vertexAccessTokenProvider->provideVertexAccessToken($vertexConfigTransfer);

        if (!$vertexApiAccessTokenTransfer->getAccessToken()) {
            $this->getLogger()->warning('Vertex API access token not found');

            return $vertexCalculationResponseTransfer;
        }

        $vertexCalculationRequestTransfer = (new VertexCalculationRequestTransfer())
            ->setSale($vertexSaleTransfer)
            ->setVertexApiAccessToken($vertexApiAccessTokenTransfer);

        $this->getLogger()->info(
            'Starting tax calculation request for invoicing process',
            [
                'transactionId' => $vertexCalculationRequestTransfer->getSale()?->getTransactionId(),
                'requestTransfer' => $vertexCalculationRequestTransfer->modifiedToArray(),
            ],
        );

        $vertexCalculationResponseTransfer = $this->vertexClient->sendTaxInvoice($vertexCalculationRequestTransfer, $vertexConfigTransfer);

        $this->getLogger()->info(
            'Finished tax calculation request for invoicing process',
            [
                'transactionId' => $vertexCalculationRequestTransfer->getSale()?->getTransactionId(),
                'responseTransfer' => $vertexCalculationResponseTransfer->modifiedToArray(),
            ],
        );

        return $vertexCalculationResponseTransfer;
    }

    protected function setMessageAttributesTransfer(
        VertexSubmitPaymentTaxInvoiceTransfer $vertexSubmitPaymentTaxInvoiceTransfer,
        OrderTransfer $orderTransfer,
    ): void {
        $storeTransfer = $this->storeFacade->getStoreByName($orderTransfer->getStoreOrFail());

        $messageAttributesTransfer = new MessageAttributesTransfer();
        $messageAttributesTransfer->setStoreReference($storeTransfer->getStoreReference());

        $vertexSubmitPaymentTaxInvoiceTransfer->setMessageAttributes($messageAttributesTransfer);
    }

    protected function executeOrderVertexExpanderPlugins(OrderTransfer $orderTransfer): OrderTransfer
    {
        foreach ($this->orderVertexExpanderPlugins as $orderVertexExpanderPlugin) {
            $orderTransfer = $orderVertexExpanderPlugin->expand($orderTransfer);
        }

        return $orderTransfer;
    }
}
