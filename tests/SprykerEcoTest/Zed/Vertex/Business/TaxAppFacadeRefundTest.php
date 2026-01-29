<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\TaxApp\Business;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\OrderTransfer;
use Generated\Shared\Transfer\StoreTransfer;
use Generated\Shared\Transfer\VertexAuthResponseTransfer;
use SprykerEco\Client\Vertex\VertexClient;
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
class TaxAppFacadeRefundTest extends Unit
{
    public const DEFAULT_OMS_PROCESS_NAME = 'Test01';

    protected VertexBusinessTester $tester;

    public function setUp(): void
    {
        parent::setUp();

        $this->tester->configureTestStateMachine([static::DEFAULT_OMS_PROCESS_NAME]);
    }

    /**
     * This test will fail if Vertex is configured but disabled locally due to the way it is constructed.
     * Store logic cannot be stubbed due to the way order saving happens (`getCurrentStore` method is used).
     *
     * @return void
     */
    public function testVertexClientWasCalledWhenRefundWasRequestedForAnOrder(): void
    {
        // Arrange
        $storeTransfer = $this->tester->haveStore();

        $orderTransfer = $this->getOrderTransferForRefund($storeTransfer);

        $orderItemsIds = array_map(function ($item) {
            return $item->getIdSalesOrderItem();
        }, $orderTransfer->getItems()->getArrayCopy());

        $vertexClientMock = $this->createMock(VertexClient::class);

        // Assert
        $vertexClientMock->expects($this->once())->method('calculateTax')->willReturn($this->tester->haveTaxCalculationResponseTransfer(['isSuccessful' => true]));
        $vertexClientMock->expects($this->once())->method('authenticate')->willReturn(
            (new VertexAuthResponseTransfer())
                ->setAccessToken('test-token')
                ->setExpiresIn(1000)
        );
        $this->tester->setDependency('CLIENT_VERTEX', $vertexClientMock);

        // Act
        $this->tester->getFacade()->processOrderRefund($orderItemsIds, $orderTransfer->getIdSalesOrder());
    }

    /**
     * @param \Generated\Shared\Transfer\StoreTransfer $storeTransfer
     *
     * @return \Generated\Shared\Transfer\OrderTransfer
     */
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
