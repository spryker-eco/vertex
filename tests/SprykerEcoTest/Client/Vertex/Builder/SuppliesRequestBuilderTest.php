<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEcoTest\Client\Vertex\Builder;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\VertexSuppliesTransfer;
use SprykerEcoTest\Client\Vertex\VertexClientTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerEcoTest
 * @group Client
 * @group Vertex
 * @group Builder
 * @group SuppliesRequestBuilderTest
 * Add your own group annotations below this line
 */
class SuppliesRequestBuilderTest extends Unit
{
    protected VertexClientTester $tester;

    /**
     * @return void
     */
    public function testQuotationBuilderBuildMethodReturnsCorrectlyConstructedSuppliesTransfer(): void
    {
        // Arrange
        $vertexCalculationRequestTransfer = $this->tester->haveVertexCalculationRequestTransfer();
        $suppliesRequestBuilder = $this->tester->getFactory()->createSuppliesQuotationRequestBuilder();

        // // Act
        $suppliesTransfer = $suppliesRequestBuilder->build($vertexCalculationRequestTransfer, (new VertexSuppliesTransfer()));

        // // Assert
        $this->assertEquals($vertexCalculationRequestTransfer->getSale()->getTransactionId(), $suppliesTransfer->getTransactionId());
        $this->assertEquals($vertexCalculationRequestTransfer->getSale()->getItems()[0]->getId(), $suppliesTransfer->getLineItems()[0]->getLineItemId());
        $this->assertEquals('QUOTATION', $suppliesTransfer->getSaleMessageType());
        $this->assertIsBool($suppliesTransfer->getReturnAssistedParametersIndicator());
    }

    /**
     * @return void
     */
    public function testQuotationBuilderBuildMethodReturnsCorrectlyConstructedSuppliesTransferWithPassedMetadataOnItemLevel(): void
    {
        // Arrange
        $vertexCalculationRequestTransfer = $this->tester->haveVertexCalculationRequestTransfer();
        $lineItem = $vertexCalculationRequestTransfer->getSale()->getItems()[0]->setTaxMetadata([
            'testMetadata' => ['testMetadataKey' => 'testMetadataValue'],
        ]);

        $suppliesRequestBuilder = $this->tester->getFactory()->createSuppliesQuotationRequestBuilder();

        // Act
        $suppliesTransfer = $suppliesRequestBuilder->build($vertexCalculationRequestTransfer, (new VertexSuppliesTransfer()));

        // Assert
        $lineItem = $suppliesTransfer->getLineItems()[0];
        $this->assertContains('testMetadataValue', $lineItem->getTaxMetadata()['testMetadata']);
    }

    /**
     * @return void
     */
    public function testInvoiceBuilderBuildMethodReturnsSuppliesTransferWithCorrectSaleMessageType(): void
    {
        // Arrange
        $vertexCalculationRequestTransfer = $this->tester->haveVertexCalculationRequestTransfer();

        $suppliesRequestBuilder = $this->tester->getFactory()->createSuppliesInvoiceRequestBuilder();

        // Act
        $suppliesTransfer = $suppliesRequestBuilder->build($vertexCalculationRequestTransfer, (new VertexSuppliesTransfer()));

        // Assert
        $this->assertEquals('INVOICE', $suppliesTransfer->getSaleMessageType());
    }

    /**
     * @return void
     */
    public function testSuppliesRequestBuilderBuildMethodReturnsDuplicatedSuppliesLineItemsPerEachSaleItemWarehouseWhenRequestItemsHasShippingWarehouses(): void
    {
        // Arrange
        $vertexCalculationRequestTransfer = $this->tester->haveVertexCalculationRequestTransferWithWarehouseMapping();

        $suppliesRequestBuilder = $this->tester->getFactory()->createSuppliesQuotationRequestBuilder();

        // Act
        $suppliesTransfer = $suppliesRequestBuilder->build($vertexCalculationRequestTransfer, (new VertexSuppliesTransfer()));

        // Assert
        $saleItem = $vertexCalculationRequestTransfer->getSale()->getItems()[0];

        [
            $firstSaleItemWarehouse,
            $secondSaleItemWarehouse,
        ] = $saleItem->getVertexShippingWarehouses();

        [
            $lineItemWithFirstWarehouse,
            $lineItemWithSecondWarehouse,
        ] = $suppliesTransfer->getLineItems();

        $this->assertEquals(
            $saleItem->getId() . '_0',
            $lineItemWithFirstWarehouse->getLineItemId(),
        );

        $this->assertEquals(
            $saleItem->getId() . '_1',
            $lineItemWithSecondWarehouse->getLineItemId(),
        );

        $this->assertEquals(
            (int)$firstSaleItemWarehouse->getQuantity(),
            (int)$lineItemWithFirstWarehouse->getQuantity()->getValue(),
        );

        $this->assertEquals(
            (int)$secondSaleItemWarehouse->getQuantity(),
            (int)$lineItemWithSecondWarehouse->getQuantity()->getValue(),
        );
    }

    /**
     * @return void
     */
    public function testSuppliesLineItemsHaveNegativePriceWhenTaxCalculationRequestItemsHaveRefundableAmount(): void
    {
        $vertexCalculationRequestTransfer = $this->tester->haveVertexCalculationRequestTransferForRefunds();

        $suppliesRequestBuilder = $this->tester->getFactory()->createSuppliesQuotationRequestBuilder();

        // Act
        $suppliesTransfer = $suppliesRequestBuilder->build($vertexCalculationRequestTransfer, (new VertexSuppliesTransfer()));

        // Assert
        $this->assertLessThan(0, $suppliesTransfer->getLineItems()[0]->getUnitPrice());
        $this->assertLessThan(0, $suppliesTransfer->getLineItems()[1]->getUnitPrice());
    }

    public function testSuppliesSellerHasCountryWhenSellerCountryCodeIsSet(): void
    {
        // Arrange
        $vertexCalculationRequestTransfer = $this->tester->haveVertexCalculationRequestTransfer();
        $vertexCalculationRequestTransfer->getSale()->setSellerCountryCode('DE');
        $suppliesRequestBuilder = $this->tester->getFactory()->createSuppliesQuotationRequestBuilder();

        // Act
        $suppliesTransfer = $suppliesRequestBuilder->build($vertexCalculationRequestTransfer, (new VertexSuppliesTransfer()));

        // Assert
        $this->assertEquals('DE', $suppliesTransfer->getSeller()->getAdministrativeOrigin()->getCountry());
        $this->assertEquals('DE', $suppliesTransfer->getSeller()->getPhysicalOrigin()->getCountry());
    }

    public function testSuppliesLineItemsCustomerHasTaxIncludedIndicatorWhenPriceModeIsGross(): void
    {
        // Arrange
        $vertexCalculationRequestTransfer = $this->tester->haveVertexCalculationRequestTransfer();
        $vertexCalculationRequestTransfer->getSale()->setPriceMode('GROSS_MODE');
        $suppliesRequestBuilder = $this->tester->getFactory()->createSuppliesQuotationRequestBuilder();

        // Act
        $suppliesTransfer = $suppliesRequestBuilder->build($vertexCalculationRequestTransfer, (new VertexSuppliesTransfer()));

        // Assert
        $this->assertEquals(true, $suppliesTransfer->getLineItems()[0]->getTaxIncludedIndicator());
    }

    public function testSuppliesLineItemsCustomerHasCountryWhenNoShippingAddressAndSellerCountryCodeIsSet(): void
    {
        // Arrange
        $vertexCalculationRequestTransfer = $this->tester->haveVertexCalculationRequestTransfer();
        $vertexCalculationRequestTransfer->getSale()->setCustomerCountryCode('DE');
        $vertexCalculationRequestTransfer->getSale()->getItems()[0]->setShippingAddress(null);
        $vertexCalculationRequestTransfer->getSale()->getItems()[0]->setBillingAddress(null);

        $suppliesRequestBuilder = $this->tester->getFactory()->createSuppliesQuotationRequestBuilder();

        // Act
        $suppliesTransfer = $suppliesRequestBuilder->build($vertexCalculationRequestTransfer, (new VertexSuppliesTransfer()));

        // Assert
        $this->assertEquals('DE', $suppliesTransfer->getLineItems()[0]->getCustomer()->getDestination()->getCountry());
    }
}
