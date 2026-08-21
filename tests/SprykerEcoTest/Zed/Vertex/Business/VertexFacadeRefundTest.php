<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEcoTest\Zed\Vertex\Business;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\ExpenseTransfer;
use Generated\Shared\Transfer\OrderTransfer;
use Generated\Shared\Transfer\StoreTransfer;
use Generated\Shared\Transfer\VertexAuthResponseTransfer;
use Generated\Shared\Transfer\VertexCalculationRequestTransfer;
use Generated\Shared\Transfer\VertexSaleTransfer;
use Generated\Shared\DataBuilder\ExpenseBuilder;
use SprykerEco\Client\Vertex\VertexClient;
use SprykerEcoTest\Zed\Vertex\VertexBusinessTester;
use Orm\Zed\Sales\Persistence\SpySalesExpense;

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

    public const CLIENT_VERTEX = 'CLIENT_VERTEX';

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
        $this->tester->mockVertexConfigResolver();

        $orderTransfer = $this->getOrderTransferForRefund($storeTransfer);

        $orderItemsIds = array_map(function ($item) {
            return $item->getIdSalesOrderItem();
        }, $orderTransfer->getItems()->getArrayCopy());

        $vertexClientMock = $this->createMock(VertexClient::class);

        // Assert
        $vertexClientMock->expects($this->once())->method('sendTaxRefund')->willReturn($this->tester->haveTaxCalculationResponseTransfer(['isSuccessful' => true]));
        $vertexClientMock->expects($this->once())->method('authenticate')->willReturn(
            (new VertexAuthResponseTransfer())
                ->setAccessToken('test-token')
                ->setExpiresIn(1000),
        );
        $this->tester->setDependency(static::CLIENT_VERTEX, $vertexClientMock);

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

        $this->tester->mockConfigMethod('isInvoicingEnabled', false);

        $vertexClientMock = $this->createMock(VertexClient::class);

        // Assert
        $vertexClientMock->expects($this->never())->method('sendTaxRefund');
        $vertexClientMock->expects($this->never())->method('authenticate');
        $this->tester->setDependency(static::CLIENT_VERTEX, $vertexClientMock);

        // Act
        $this->tester->getFacade()->processOrderRefund($orderItemsIds, $orderTransfer->getIdSalesOrder());
    }

    public function testRefundRequestContainsShipmentsWhenShipmentIsRefundable(): void
    {
        // Arrange
        $storeTransfer = $this->tester->haveStore();
        $this->tester->mockVertexConfigResolver();

        $orderTransfer = $this->getOrderTransferForRefund($storeTransfer);
        $orderItemsIds = $this->getOrderItemIds($orderTransfer);

        $shipmentExpenseTransfer = new ExpenseBuilder()->build();
        $salesExpenseEntity = (new SpySalesExpense())->fromArray($shipmentExpenseTransfer->toArray())
            ->setFkSalesOrder($orderTransfer->getIdSalesOrder())
            ->setType('SHIPMENT_EXPENSE_TYPE')
            ->setNetPrice($shipmentExpenseTransfer->getSumNetPrice())
            ->setGrossPrice($shipmentExpenseTransfer->getSumGrossPrice())
            ->setRefundableAmount($shipmentExpenseTransfer->getSumNetPrice());

        $salesExpenseEntity->save();

        $vertexSaleTransfer = null;
        $vertexClientMock = $this->createMock(VertexClient::class);
        $vertexClientMock->method('authenticate')->willReturn(
            (new VertexAuthResponseTransfer())
                ->setAccessToken('test-token')
                ->setExpiresIn(1000),
        );
        $vertexClientMock->expects($this->once())
            ->method('sendTaxRefund')
            ->willReturnCallback(function (VertexCalculationRequestTransfer $vertexCalculationRequestTransfer) use (&$vertexSaleTransfer) {
                $vertexSaleTransfer = $vertexCalculationRequestTransfer->getSale();

                return $this->tester->haveTaxCalculationResponseTransfer(['isSuccessful' => true]);
            });
        $this->tester->setDependency(static::CLIENT_VERTEX, $vertexClientMock);

        // Act
        $this->tester->getFacade()->processOrderRefund($orderItemsIds, $orderTransfer->getIdSalesOrder());

        // Assert
        $this->assertInstanceOf(VertexSaleTransfer::class, $vertexSaleTransfer);
        $this->assertCount(
            1,
            $vertexSaleTransfer->getShipments(),
            'Expected the shipment of the order to be refunded by default.',
        );

        $vertexShipmentTransfer = $vertexSaleTransfer->getShipments()[0];

        $this->assertSame(
            $shipmentExpenseTransfer->getSumNetPrice(),
            $vertexShipmentTransfer->getPriceAmount(),
            'Expected the shipment sent to Vertex to carry the price of the actually refunded shipment.',
        );
    }

    public function testRefundRequestContainsNoShipmentsWhenShipmentIsNotRefundable(): void
    {
        // Arrange
        $storeTransfer = $this->tester->haveStore();
        $this->tester->mockVertexConfigResolver();
        $this->tester->mockConfigMethod('isShipmentRefundable', false);

        $orderTransfer = $this->getOrderTransferForRefund($storeTransfer);
        $orderItemsIds = $this->getOrderItemIds($orderTransfer);

        $vertexSaleTransfer = null;
        $vertexClientMock = $this->createMock(VertexClient::class);
        $vertexClientMock->method('authenticate')->willReturn(
            (new VertexAuthResponseTransfer())
                ->setAccessToken('test-token')
                ->setExpiresIn(1000),
        );
        $vertexClientMock->expects($this->once())
            ->method('sendTaxRefund')
            ->willReturnCallback(function (VertexCalculationRequestTransfer $vertexCalculationRequestTransfer) use (&$vertexSaleTransfer) {
                $vertexSaleTransfer = $vertexCalculationRequestTransfer->getSale();

                return $this->tester->haveTaxCalculationResponseTransfer(['isSuccessful' => true]);
            });
        $this->tester->setDependency(static::CLIENT_VERTEX, $vertexClientMock);

        // Act
        $this->tester->getFacade()->processOrderRefund($orderItemsIds, $orderTransfer->getIdSalesOrder());

        // Assert
        $this->assertInstanceOf(VertexSaleTransfer::class, $vertexSaleTransfer);
        $this->assertCount(
            0,
            $vertexSaleTransfer->getShipments(),
            'Expected no shipment to be credited when the shipment is not refundable.',
        );
    }

    /**
     * @return array<int>
     */
    protected function getOrderItemIds(OrderTransfer $orderTransfer): array
    {
        return array_map(function ($itemTransfer) {
            return $itemTransfer->getIdSalesOrderItem();
        }, $orderTransfer->getItems()->getArrayCopy());
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
