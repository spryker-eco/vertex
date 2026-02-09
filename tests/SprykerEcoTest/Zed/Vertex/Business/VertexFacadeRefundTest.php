<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEcoTest\Zed\Vertex\Business;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\OrderTransfer;
use Generated\Shared\Transfer\StoreTransfer;
use Generated\Shared\Transfer\VertexAuthResponseTransfer;
use SprykerEco\Client\Vertex\VertexClient;
use SprykerEco\Shared\Vertex\VertexConstants;
use SprykerEcoTest\Zed\Vertex\VertexBusinessTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerEcoTest
 * @group Zed
 * @group Vertex
 * @group Business
 * @group Facade
 * @group VertexFacadeRefundTest
 * Add your own group annotations below this line
 */
class VertexFacadeRefundTest extends Unit
{
    public const DEFAULT_OMS_PROCESS_NAME = 'Test01';

    protected VertexBusinessTester $tester;

    public function setUp(): void
    {
        parent::setUp();

        $this->tester->configureTestStateMachine([static::DEFAULT_OMS_PROCESS_NAME]);
    }

    public function testVertexClientWasCalledWhenRefundWasRequestedForAnOrderAndInvoicingIsEnabled(): void
    {
        // Arrange
        $storeTransfer = $this->tester->haveStore();

        $orderTransfer = $this->getOrderTransferForRefund($storeTransfer);

        $orderItemsIds = array_map(function ($item) {
            return $item->getIdSalesOrderItem();
        }, $orderTransfer->getItems()->getArrayCopy());

        $this->tester->setConfig(
            VertexConstants::IS_INVOICING_ENABLED,
            true,
        );

        $vertexClientMock = $this->createMock(VertexClient::class);

        // Assert
        $vertexClientMock->expects($this->once())->method('sendTaxRefund')->willReturn($this->tester->haveTaxCalculationResponseTransfer(['isSuccessful' => true]));
        $vertexClientMock->expects($this->once())->method('authenticate')->willReturn(
            (new VertexAuthResponseTransfer())
                ->setAccessToken('test-token')
                ->setExpiresIn(1000),
        );
        $this->tester->setDependency('CLIENT_VERTEX', $vertexClientMock);

        // Act
        $this->tester->getFacade()->processOrderRefund($orderItemsIds, $orderTransfer->getIdSalesOrder());
    }

    public function testVertexClientWasCalledWhenRefundWasRequestedForAnOrderAndInvoicingIsDisabled(): void
    {
        // Arrange
        $storeTransfer = $this->tester->haveStore();

        $orderTransfer = $this->getOrderTransferForRefund($storeTransfer);

        $orderItemsIds = array_map(function ($item) {
            return $item->getIdSalesOrderItem();
        }, $orderTransfer->getItems()->getArrayCopy());

        $this->tester->setConfig(
            VertexConstants::IS_INVOICING_ENABLED,
            false,
        );

        $vertexClientMock = $this->createMock(VertexClient::class);

        // Assert
        $vertexClientMock->expects($this->never())->method('sendTaxRefund');
        $vertexClientMock->expects($this->never())->method('authenticate');
        $this->tester->setDependency('CLIENT_VERTEX', $vertexClientMock);

        // Act
        $this->tester->getFacade()->processOrderRefund($orderItemsIds, $orderTransfer->getIdSalesOrder());
    }

    protected function getOrderTransferForRefund(StoreTransfer $storeTransfer): OrderTransfer
    {
        $orderTransfer = $this->tester->createOrderByStateMachineProcessName(
            static::DEFAULT_OMS_PROCESS_NAME,
            $storeTransfer,
        );
        $orderTransfer->setCreatedAt(date('Y-m-d h:i:s'));
        $orderTransfer->setEmail($orderTransfer->getCustomer()->getEmail());

        foreach ($orderTransfer->getItems() as $item) {
            $item->setSku('some_sku');
            $item->setCanceledAmount($item->getSumPrice());
        }

        return $orderTransfer;
    }
}
