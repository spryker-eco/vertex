<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEcoTest\Client\Vertex\Validator;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\VertexAddressTransfer;
use Generated\Shared\Transfer\VertexItemTransfer;
use Generated\Shared\Transfer\VertexSaleTransfer;
use Generated\Shared\Transfer\VertexShipmentTransfer;
use Generated\Shared\Transfer\VertexValidationResponseTransfer;
use SprykerEco\Client\Vertex\Validator\VertexAddressValidator;
use SprykerEco\Client\Vertex\Validator\VertexItemValidator;
use SprykerEco\Client\Vertex\Validator\VertexSaleValidator;
use SprykerEco\Client\Vertex\Validator\VertexShipmentValidator;
use SprykerEco\Client\Vertex\Validator\VertexShippingWarehouseValidator;
use SprykerEcoTest\Client\Vertex\VertexClientTester;

/**
 * @group SprykerEcoTest
 * @group Client
 * @group Vertex
 * @group Validator
 * @group VertexSaleValidatorTest
 */
class VertexSaleValidatorTest extends Unit
{
    protected VertexClientTester $tester;

    public function testValidateReturnsNoErrorsWhenSaleIsValid(): void
    {
        // Arrange
        $sale = (new VertexSaleTransfer())
            ->setTransactionId('transaction-1')
            ->setDocumentNumber('DOC-123')
            ->setDocumentDate('2024-01-01')
            ->setTaxMetadata([])
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
                            ->setCity('New York')
                            ->setCountry('US')
                            ->setZipCode('10001')
                    )
            );

        $responseTransfer = new VertexValidationResponseTransfer();
        $itemValidator = new VertexItemValidator(
            new VertexAddressValidator(),
            new VertexShippingWarehouseValidator(new VertexAddressValidator())
        );
        $shipmentValidator = new VertexShipmentValidator(new VertexAddressValidator());
        $validator = new VertexSaleValidator($itemValidator, $shipmentValidator);

        // Act
        $result = $validator->validate($sale, $responseTransfer);

        // Assert
        $this->assertEmpty($result->getMessages());
    }

    public function testValidateAddsErrorWhenTransactionIdIsMissing(): void
    {
        // Arrange
        $sale = (new VertexSaleTransfer())
            ->setDocumentNumber('DOC-123')
            ->setDocumentDate('2024-01-01')
            ->setTaxMetadata([])
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

        $responseTransfer = new VertexValidationResponseTransfer();
        $itemValidator = new VertexItemValidator(
            new VertexAddressValidator(),
            new VertexShippingWarehouseValidator(new VertexAddressValidator())
        );
        $shipmentValidator = new VertexShipmentValidator(new VertexAddressValidator());
        $validator = new VertexSaleValidator($itemValidator, $shipmentValidator);

        // Act
        $result = $validator->validate($sale, $responseTransfer);

        // Assert
        $this->assertCount(1, $result->getMessages());
        $this->assertStringContainsString('transactionId', $result->getMessages()[0]);
    }

    public function testValidateAddsErrorWhenDocumentNumberIsMissing(): void
    {
        // Arrange
        $sale = (new VertexSaleTransfer())
            ->setTransactionId('transaction-1')
            ->setDocumentDate('2024-01-01')
            ->setTaxMetadata([])
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

        $responseTransfer = new VertexValidationResponseTransfer();
        $itemValidator = new VertexItemValidator(
            new VertexAddressValidator(),
            new VertexShippingWarehouseValidator(new VertexAddressValidator())
        );
        $shipmentValidator = new VertexShipmentValidator(new VertexAddressValidator());
        $validator = new VertexSaleValidator($itemValidator, $shipmentValidator);

        // Act
        $result = $validator->validate($sale, $responseTransfer);

        // Assert
        $this->assertCount(1, $result->getMessages());
        $this->assertStringContainsString('documentNumber', $result->getMessages()[0]);
    }

    public function testValidateAddsErrorWhenDocumentDateIsMissing(): void
    {
        // Arrange
        $sale = (new VertexSaleTransfer())
            ->setTransactionId('transaction-1')
            ->setDocumentNumber('DOC-123')
            ->setTaxMetadata([])
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

        $responseTransfer = new VertexValidationResponseTransfer();
        $itemValidator = new VertexItemValidator(
            new VertexAddressValidator(),
            new VertexShippingWarehouseValidator(new VertexAddressValidator())
        );
        $shipmentValidator = new VertexShipmentValidator(new VertexAddressValidator());
        $validator = new VertexSaleValidator($itemValidator, $shipmentValidator);

        // Act
        $result = $validator->validate($sale, $responseTransfer);

        // Assert
        $this->assertCount(1, $result->getMessages());
        $this->assertStringContainsString('documentDate', $result->getMessages()[0]);
    }

    public function testValidateAddsErrorWhenTaxMetadataIsNull(): void
    {
        // Arrange
        $sale = (new VertexSaleTransfer())
            ->setTransactionId('transaction-1')
            ->setDocumentNumber('DOC-123')
            ->setDocumentDate('2024-01-01')
            ->setTaxMetadata(null)
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

        $responseTransfer = new VertexValidationResponseTransfer();
        $itemValidator = new VertexItemValidator(
            new VertexAddressValidator(),
            new VertexShippingWarehouseValidator(new VertexAddressValidator())
        );
        $shipmentValidator = new VertexShipmentValidator(new VertexAddressValidator());
        $validator = new VertexSaleValidator($itemValidator, $shipmentValidator);

        // Act
        $result = $validator->validate($sale, $responseTransfer);

        // Assert
        $this->assertCount(1, $result->getMessages());
        $this->assertStringContainsString('taxMetadata', $result->getMessages()[0]);
    }

    public function testValidateAddsErrorWhenItemsAreEmpty(): void
    {
        // Arrange
        $sale = (new VertexSaleTransfer())
            ->setTransactionId('transaction-1')
            ->setDocumentNumber('DOC-123')
            ->setDocumentDate('2024-01-01')
            ->setTaxMetadata([])
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

        $responseTransfer = new VertexValidationResponseTransfer();
        $itemValidator = new VertexItemValidator(
            new VertexAddressValidator(),
            new VertexShippingWarehouseValidator(new VertexAddressValidator())
        );
        $shipmentValidator = new VertexShipmentValidator(new VertexAddressValidator());
        $validator = new VertexSaleValidator($itemValidator, $shipmentValidator);

        // Act
        $result = $validator->validate($sale, $responseTransfer);

        // Assert
        $this->assertCount(1, $result->getMessages());
        $this->assertStringContainsString('items', $result->getMessages()[0]);
    }

    public function testValidateAddsErrorWhenShipmentsAreEmpty(): void
    {
        // Arrange
        $sale = (new VertexSaleTransfer())
            ->setTransactionId('transaction-1')
            ->setDocumentNumber('DOC-123')
            ->setDocumentDate('2024-01-01')
            ->setTaxMetadata([])
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
            );

        $responseTransfer = new VertexValidationResponseTransfer();
        $itemValidator = new VertexItemValidator(
            new VertexAddressValidator(),
            new VertexShippingWarehouseValidator(new VertexAddressValidator())
        );
        $shipmentValidator = new VertexShipmentValidator(new VertexAddressValidator());
        $validator = new VertexSaleValidator($itemValidator, $shipmentValidator);

        // Act
        $result = $validator->validate($sale, $responseTransfer);

        // Assert
        $this->assertCount(1, $result->getMessages());
        $this->assertStringContainsString('shipments', $result->getMessages()[0]);
    }

    public function testValidateValidatesItemsWhenPresent(): void
    {
        // Arrange
        $sale = (new VertexSaleTransfer())
            ->setTransactionId('transaction-1')
            ->setDocumentNumber('DOC-123')
            ->setDocumentDate('2024-01-01')
            ->setTaxMetadata([])
            ->addItem(
                (new VertexItemTransfer())
                    // Missing required fields
                    ->setSku('SKU-123')
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

        $responseTransfer = new VertexValidationResponseTransfer();
        $itemValidator = new VertexItemValidator(
            new VertexAddressValidator(),
            new VertexShippingWarehouseValidator(new VertexAddressValidator())
        );
        $shipmentValidator = new VertexShipmentValidator(new VertexAddressValidator());
        $validator = new VertexSaleValidator($itemValidator, $shipmentValidator);

        // Act
        $result = $validator->validate($sale, $responseTransfer);

        // Assert
        $this->assertGreaterThan(0, count($result->getMessages()));
    }

    public function testValidateValidatesShipmentsWhenPresent(): void
    {
        // Arrange
        $sale = (new VertexSaleTransfer())
            ->setTransactionId('transaction-1')
            ->setDocumentNumber('DOC-123')
            ->setDocumentDate('2024-01-01')
            ->setTaxMetadata([])
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
                    // Missing required fields
                    ->setId('shipment-1')
            );

        $responseTransfer = new VertexValidationResponseTransfer();
        $itemValidator = new VertexItemValidator(
            new VertexAddressValidator(),
            new VertexShippingWarehouseValidator(new VertexAddressValidator())
        );
        $shipmentValidator = new VertexShipmentValidator(new VertexAddressValidator());
        $validator = new VertexSaleValidator($itemValidator, $shipmentValidator);

        // Act
        $result = $validator->validate($sale, $responseTransfer);

        // Assert
        $this->assertGreaterThan(0, count($result->getMessages()));
    }
}

