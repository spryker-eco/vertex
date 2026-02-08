<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEcoTest\Client\Vertex\Validator;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\SaleTaxMetadataTransfer;
use Generated\Shared\Transfer\VertexAddressTransfer;
use Generated\Shared\Transfer\VertexCalculationRequestTransfer;
use Generated\Shared\Transfer\VertexItemTransfer;
use Generated\Shared\Transfer\VertexSaleTransfer;
use Generated\Shared\Transfer\VertexShipmentTransfer;
use SprykerEco\Client\Vertex\Validator\VertexAddressValidator;
use SprykerEco\Client\Vertex\Validator\VertexItemValidator;
use SprykerEco\Client\Vertex\Validator\VertexQuotationValidator;
use SprykerEco\Client\Vertex\Validator\VertexSaleValidator;
use SprykerEco\Client\Vertex\Validator\VertexShipmentValidator;
use SprykerEco\Client\Vertex\Validator\VertexShippingWarehouseValidator;
use SprykerEcoTest\Client\Vertex\VertexClientTester;

/**
 * @group SprykerEcoTest
 * @group Client
 * @group Vertex
 * @group Validator
 * @group VertexQuotationValidatorTest
 */
class VertexQuotationValidatorTest extends Unit
{
    protected VertexClientTester $tester;

    public function testValidateReturnsValidResponseWhenQuotationIsValid(): void
    {
        // Arrange
        $sale = (new VertexSaleTransfer())
            ->setTransactionId('transaction-1')
            ->setDocumentNumber('DOC-123')
            ->setDocumentDate('2024-01-01')
            ->setTaxMetadata([new SaleTaxMetadataTransfer()])
            ->addItem(
                (new VertexItemTransfer())
                    ->setId('item-1')
                    ->setSku('SKU-123')
                    ->setPriceAmount(1000)
                    ->setDiscountAmount(100)
                    ->setQuantity(2)
                    ->setShippingAddress(
                        (new VertexAddressTransfer())
                            ->setAddress1('123 Main St')
                            ->setAddress2('Apt 4')
                            ->setCity('New York')
                            ->setCountry('US')
                            ->setZipCode('10001')
                    )
            )
            ->addShipment(
                (new VertexShipmentTransfer())
                    ->setId('shipment-1')
                    ->setPriceAmount(500)
                    ->setDiscountAmount(50)
                    ->setShipmentMethodKey('standard')
                    ->setShippingAddress(
                        (new VertexAddressTransfer())
                            ->setAddress1('123 Main St')
                            ->setAddress2('Apt 4')
                            ->setCity('New York')
                            ->setCountry('US')
                            ->setZipCode('10001')
                    )
            );

        $request = (new VertexCalculationRequestTransfer())
            ->setSale($sale);

        $itemValidator = new VertexItemValidator(
            new VertexAddressValidator(),
            new VertexShippingWarehouseValidator(new VertexAddressValidator())
        );
        $shipmentValidator = new VertexShipmentValidator(new VertexAddressValidator());
        $saleValidator = new VertexSaleValidator($itemValidator, $shipmentValidator);
        $validator = new VertexQuotationValidator($saleValidator);

        // Act
        $result = $validator->validate($request);

        // Assert
        $this->assertTrue($result->getIsValid() !== false);
        $this->assertEmpty($result->getMessages());
    }

    public function testValidateAddsErrorWhenSaleIsMissing(): void
    {
        // Arrange
        $request = (new VertexCalculationRequestTransfer())
            ->setSale(null);

        $itemValidator = new VertexItemValidator(
            new VertexAddressValidator(),
            new VertexShippingWarehouseValidator(new VertexAddressValidator())
        );
        $shipmentValidator = new VertexShipmentValidator(new VertexAddressValidator());
        $saleValidator = new VertexSaleValidator($itemValidator, $shipmentValidator);
        $validator = new VertexQuotationValidator($saleValidator);

        // Act
        $result = $validator->validate($request);

        // Assert
        $this->assertFalse($result->getIsValid());
        $this->assertCount(1, $result->getMessages());
        $this->assertStringContainsString('sale', $result->getMessages()[0]);
    }

    public function testValidateAddsErrorWhenShipmentDiscountAmountIsMissing(): void
    {
        // Arrange
        $sale = (new VertexSaleTransfer())
            ->setTransactionId('transaction-1')
            ->setDocumentNumber('DOC-123')
            ->setDocumentDate('2024-01-01')
            ->setTaxMetadata([new SaleTaxMetadataTransfer()])
            ->addItem(
                (new VertexItemTransfer())
                    ->setId('item-1')
                    ->setSku('SKU-123')
                    ->setPriceAmount(1000)
                    ->setDiscountAmount(100)
                    ->setQuantity(2)
                    ->setShippingAddress(
                        (new VertexAddressTransfer())
                            ->setAddress1('123 Main St')
                            ->setAddress2('Apt 4')
                            ->setCity('New York')
                            ->setCountry('US')
                            ->setZipCode('10001')
                    )
            )
            ->addShipment(
                (new VertexShipmentTransfer())
                    ->setId('shipment-1')
                    ->setPriceAmount(500)
                    ->setShipmentMethodKey('standard')
                    ->setShippingAddress(
                        (new VertexAddressTransfer())
                            ->setAddress1('123 Main St')
                            ->setAddress2('Apt 4')
                            ->setCity('New York')
                            ->setCountry('US')
                            ->setZipCode('10001')
                    )
            );

        $request = (new VertexCalculationRequestTransfer())
            ->setSale($sale);

        $itemValidator = new VertexItemValidator(
            new VertexAddressValidator(),
            new VertexShippingWarehouseValidator(new VertexAddressValidator())
        );
        $shipmentValidator = new VertexShipmentValidator(new VertexAddressValidator());
        $saleValidator = new VertexSaleValidator($itemValidator, $shipmentValidator);
        $validator = new VertexQuotationValidator($saleValidator);

        // Act
        $result = $validator->validate($request);

        // Assert
        $this->assertFalse($result->getIsValid());
        $this->assertGreaterThan(0, count($result->getMessages()));
        $this->assertStringContainsString('discountAmount', $result->getMessages()[0]);
    }

    public function testValidateSetsIsValidToFalseWhenMessagesArePresent(): void
    {
        // Arrange
        $sale = (new VertexSaleTransfer())
            ->setTransactionId('transaction-1')
            ->setDocumentNumber('DOC-123')
            ->setDocumentDate('2024-01-01')
            ->setTaxMetadata([new SaleTaxMetadataTransfer()])
            ->addItem(
                (new VertexItemTransfer())
                    ->setId('item-1')
                    ->setSku('SKU-123')
                    ->setPriceAmount(1000)
                    ->setDiscountAmount(100)
                    ->setQuantity(2)
                    ->setShippingAddress(
                        (new VertexAddressTransfer())
                            ->setAddress1('123 Main St')
                            ->setCity('New York')
                            ->setCountry('US')
                            ->setZipCode('10001')
                    )
            )
            ->addShipment(
                (new VertexShipmentTransfer())
                    ->setId('shipment-1')
                    ->setPriceAmount(500)
                    ->setShipmentMethodKey('standard')
                    ->setShippingAddress(
                        (new VertexAddressTransfer())
                            ->setAddress1('123 Main St')
                            ->setCity('New York')
                            ->setCountry('US')
                            ->setZipCode('10001')
                    )
            );

        $request = (new VertexCalculationRequestTransfer())
            ->setSale($sale);

        $itemValidator = new VertexItemValidator(
            new VertexAddressValidator(),
            new VertexShippingWarehouseValidator(new VertexAddressValidator())
        );
        $shipmentValidator = new VertexShipmentValidator(new VertexAddressValidator());
        $saleValidator = new VertexSaleValidator($itemValidator, $shipmentValidator);
        $validator = new VertexQuotationValidator($saleValidator);

        // Act
        $result = $validator->validate($request);

        // Assert
        $this->assertFalse($result->getIsValid());
    }

    public function testValidateValidatesSaleWhenPresent(): void
    {
        // Arrange
        $sale = (new VertexSaleTransfer())
            // Missing required fields
            ->setTransactionId('transaction-1');

        $request = (new VertexCalculationRequestTransfer())
            ->setSale($sale);

        $itemValidator = new VertexItemValidator(
            new VertexAddressValidator(),
            new VertexShippingWarehouseValidator(new VertexAddressValidator())
        );
        $shipmentValidator = new VertexShipmentValidator(new VertexAddressValidator());
        $saleValidator = new VertexSaleValidator($itemValidator, $shipmentValidator);
        $validator = new VertexQuotationValidator($saleValidator);

        // Act
        $result = $validator->validate($request);

        // Assert
        $this->assertFalse($result->getIsValid());
        $this->assertGreaterThan(0, count($result->getMessages()));
    }
}

