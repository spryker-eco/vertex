<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEcoTest\Client\Vertex\Validator;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\VertexAddressTransfer;
use Generated\Shared\Transfer\VertexItemTransfer;
use Generated\Shared\Transfer\VertexShippingWarehouseTransfer;
use Generated\Shared\Transfer\VertexValidationResponseTransfer;
use SprykerEco\Client\Vertex\Validator\VertexAddressValidator;
use SprykerEco\Client\Vertex\Validator\VertexItemValidator;
use SprykerEco\Client\Vertex\Validator\VertexShippingWarehouseValidator;
use SprykerEcoTest\Client\Vertex\VertexClientTester;

/**
 * @group SprykerEcoTest
 * @group Client
 * @group Vertex
 * @group Validator
 * @group VertexItemValidatorTest
 */
class VertexItemValidatorTest extends Unit
{
    protected VertexClientTester $tester;

    public function testValidateReturnsNoErrorsWhenItemIsValid(): void
    {
        // Arrange
        $item = (new VertexItemTransfer())
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
                    ->setZipCode('10001'),
            );

        $responseTransfer = new VertexValidationResponseTransfer();
        $validator = new VertexItemValidator(
            new VertexAddressValidator(),
            new VertexShippingWarehouseValidator(new VertexAddressValidator()),
        );

        // Act
        $validator->validate($item, $responseTransfer);

        // Assert
        $this->assertEmpty($responseTransfer->getMessages());
    }

    public function testValidateAddsErrorWhenIdIsMissing(): void
    {
        // Arrange
        $item = (new VertexItemTransfer())
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
                    ->setZipCode('10001'),
            );

        $responseTransfer = new VertexValidationResponseTransfer();
        $validator = new VertexItemValidator(
            new VertexAddressValidator(),
            new VertexShippingWarehouseValidator(new VertexAddressValidator()),
        );

        // Act
        $validator->validate($item, $responseTransfer);

        // Assert
        $this->assertCount(1, $responseTransfer->getMessages());
        $this->assertStringContainsString('id', $responseTransfer->getMessages()[0]);
        $this->assertStringContainsString('SKU-123', $responseTransfer->getMessages()[0]);
    }

    public function testValidateAddsErrorWhenPriceAmountIsMissing(): void
    {
        // Arrange
        $item = (new VertexItemTransfer())
            ->setId('item-1')
            ->setSku('SKU-123')
            ->setDiscountAmount(100)
            ->setQuantity(2)
            ->setShippingAddress(
                (new VertexAddressTransfer())
                    ->setAddress1('123 Main St')
                    ->setAddress2('Apt 4')
                    ->setCity('New York')
                    ->setCountry('US')
                    ->setZipCode('10001'),
            );

        $responseTransfer = new VertexValidationResponseTransfer();
        $validator = new VertexItemValidator(
            new VertexAddressValidator(),
            new VertexShippingWarehouseValidator(new VertexAddressValidator()),
        );

        // Act
        $validator->validate($item, $responseTransfer);

        // Assert
        $this->assertCount(1, $responseTransfer->getMessages());
        $this->assertStringContainsString('priceAmount', $responseTransfer->getMessages()[0]);
    }

    public function testValidateAddsErrorWhenDiscountAmountIsNull(): void
    {
        // Arrange
        $item = (new VertexItemTransfer())
            ->setId('item-1')
            ->setSku('SKU-123')
            ->setPriceAmount(1000)
            ->setDiscountAmount(null)
            ->setQuantity(2)
            ->setShippingAddress(
                (new VertexAddressTransfer())
                    ->setAddress1('123 Main St')
                    ->setAddress2('Apt 4')
                    ->setCity('New York')
                    ->setCountry('US')
                    ->setZipCode('10001'),
            );

        $responseTransfer = new VertexValidationResponseTransfer();
        $validator = new VertexItemValidator(
            new VertexAddressValidator(),
            new VertexShippingWarehouseValidator(new VertexAddressValidator()),
        );

        // Act
        $validator->validate($item, $responseTransfer);

        // Assert
        $this->assertCount(1, $responseTransfer->getMessages());
        $this->assertStringContainsString('discountAmount', $responseTransfer->getMessages()[0]);
    }

    public function testValidateAddsErrorWhenQuantityIsMissing(): void
    {
        // Arrange
        $item = (new VertexItemTransfer())
            ->setId('item-1')
            ->setSku('SKU-123')
            ->setPriceAmount(1000)
            ->setDiscountAmount(100)
            ->setShippingAddress(
                (new VertexAddressTransfer())
                    ->setAddress1('123 Main St')
                    ->setAddress2('Apt 4')
                    ->setCity('New York')
                    ->setCountry('US')
                    ->setZipCode('10001'),
            );

        $responseTransfer = new VertexValidationResponseTransfer();
        $validator = new VertexItemValidator(
            new VertexAddressValidator(),
            new VertexShippingWarehouseValidator(new VertexAddressValidator()),
        );

        // Act
        $validator->validate($item, $responseTransfer);

        // Assert
        $this->assertCount(1, $responseTransfer->getMessages());
        $this->assertStringContainsString('quantity', $responseTransfer->getMessages()[0]);
    }

    public function testValidateAddsErrorWhenSkuIsMissing(): void
    {
        // Arrange
        $item = (new VertexItemTransfer())
            ->setId('item-1')
            ->setPriceAmount(1000)
            ->setDiscountAmount(100)
            ->setQuantity(2)
            ->setShippingAddress(
                (new VertexAddressTransfer())
                    ->setAddress1('123 Main St')
                    ->setAddress2('Apt 4')
                    ->setCity('New York')
                    ->setCountry('US')
                    ->setZipCode('10001'),
            );

        $responseTransfer = new VertexValidationResponseTransfer();
        $validator = new VertexItemValidator(
            new VertexAddressValidator(),
            new VertexShippingWarehouseValidator(new VertexAddressValidator()),
        );

        // Act
        $validator->validate($item, $responseTransfer);

        // Assert
        $this->assertCount(1, $responseTransfer->getMessages());
        $this->assertStringContainsString('sku', $responseTransfer->getMessages()[0]);
    }

    public function testValidateAddsErrorWhenShippingAddressIsMissing(): void
    {
        // Arrange
        $item = (new VertexItemTransfer())
            ->setId('item-1')
            ->setSku('SKU-123')
            ->setPriceAmount(1000)
            ->setDiscountAmount(100)
            ->setQuantity(2);

        $responseTransfer = new VertexValidationResponseTransfer();
        $validator = new VertexItemValidator(
            new VertexAddressValidator(),
            new VertexShippingWarehouseValidator(new VertexAddressValidator()),
        );

        // Act
        $validator->validate($item, $responseTransfer);

        // Assert
        $this->assertCount(1, $responseTransfer->getMessages());
        $this->assertStringContainsString('shippingAddress', $responseTransfer->getMessages()[0]);
    }

    public function testValidateValidatesShippingAddressWhenPresent(): void
    {
        // Arrange
        $item = (new VertexItemTransfer())
            ->setId('item-1')
            ->setSku('SKU-123')
            ->setPriceAmount(1000)
            ->setDiscountAmount(100)
            ->setQuantity(2)
            ->setShippingAddress(
                (new VertexAddressTransfer())
                    ->setAddress1('123 Main St'),
                // Missing city, country, zipCode
            );

        $responseTransfer = new VertexValidationResponseTransfer();
        $validator = new VertexItemValidator(
            new VertexAddressValidator(),
            new VertexShippingWarehouseValidator(new VertexAddressValidator()),
        );

        // Act
        $validator->validate($item, $responseTransfer);

        // Assert
        $this->assertGreaterThan(0, count($responseTransfer->getMessages()));
        $this->assertStringContainsString('shippingAddress', $responseTransfer->getMessages()[0]);
    }

    public function testValidateValidatesSellerAddressWhenPresent(): void
    {
        // Arrange
        $item = (new VertexItemTransfer())
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
                    ->setZipCode('10001'),
            )
            ->setSellerAddress(
                (new VertexAddressTransfer())
                    ->setAddress1('456 Seller St'),
                // Missing city, country, zipCode
            );

        $responseTransfer = new VertexValidationResponseTransfer();
        $validator = new VertexItemValidator(
            new VertexAddressValidator(),
            new VertexShippingWarehouseValidator(new VertexAddressValidator()),
        );

        // Act
        $validator->validate($item, $responseTransfer);

        // Assert
        $this->assertGreaterThan(0, count($responseTransfer->getMessages()));
        $this->assertStringContainsString('sellerAddress', $responseTransfer->getMessages()[0]);
    }

    public function testValidateValidatesBillingAddressWhenPresent(): void
    {
        // Arrange
        $item = (new VertexItemTransfer())
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
                    ->setZipCode('10001'),
            )
            ->setBillingAddress(
                (new VertexAddressTransfer())
                    ->setAddress1('789 Billing St'),
                // Missing city, country, zipCode
            );

        $responseTransfer = new VertexValidationResponseTransfer();
        $validator = new VertexItemValidator(
            new VertexAddressValidator(),
            new VertexShippingWarehouseValidator(new VertexAddressValidator()),
        );

        // Act
        $validator->validate($item, $responseTransfer);

        // Assert
        $this->assertGreaterThan(0, count($responseTransfer->getMessages()));
        $this->assertStringContainsString('billingAddress', $responseTransfer->getMessages()[0]);
    }

    public function testValidateValidatesShippingWarehousesWhenPresent(): void
    {
        // Arrange
        $item = (new VertexItemTransfer())
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
                    ->setZipCode('10001'),
            )
            ->addVertexShippingWarehouse(
                (new VertexShippingWarehouseTransfer())
                    // Missing quantity
                    ->setWarehouseAddress(
                        (new VertexAddressTransfer())
                            ->setAddress1('Warehouse St')
                            ->setCity('New York')
                            ->setCountry('US')
                            ->setZipCode('10001'),
                    ),
            );

        $responseTransfer = new VertexValidationResponseTransfer();
        $validator = new VertexItemValidator(
            new VertexAddressValidator(),
            new VertexShippingWarehouseValidator(new VertexAddressValidator()),
        );

        // Act
        $validator->validate($item, $responseTransfer);

        // Assert
        $this->assertGreaterThan(0, count($responseTransfer->getMessages()));
        $this->assertStringContainsString('quantity', $responseTransfer->getMessages()[0]);
    }
}
