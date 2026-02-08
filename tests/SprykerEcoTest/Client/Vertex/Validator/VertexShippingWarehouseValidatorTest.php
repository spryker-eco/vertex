<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEcoTest\Client\Vertex\Validator;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\VertexAddressTransfer;
use Generated\Shared\Transfer\VertexShippingWarehouseTransfer;
use Generated\Shared\Transfer\VertexValidationResponseTransfer;
use SprykerEco\Client\Vertex\Validator\VertexAddressValidator;
use SprykerEco\Client\Vertex\Validator\VertexShippingWarehouseValidator;
use SprykerEcoTest\Client\Vertex\VertexClientTester;

/**
 * @group SprykerEcoTest
 * @group Client
 * @group Vertex
 * @group Validator
 * @group VertexShippingWarehouseValidatorTest
 */
class VertexShippingWarehouseValidatorTest extends Unit
{
    protected VertexClientTester $tester;

    public function testValidateReturnsNoErrorsWhenWarehouseIsValid(): void
    {
        // Arrange
        $warehouse = (new VertexShippingWarehouseTransfer())
            ->setQuantity(10)
            ->setWarehouseAddress(
                (new VertexAddressTransfer())
                    ->setAddress1('123 Warehouse St')
                    ->setAddress2('Building A')
                    ->setCity('New York')
                    ->setCountry('US')
                    ->setZipCode('10001')
            );

        $responseTransfer = new VertexValidationResponseTransfer();
        $validator = new VertexShippingWarehouseValidator(new VertexAddressValidator());

        // Act
        $validator->validate($warehouse, $responseTransfer);

        // Assert
        $this->assertEmpty($responseTransfer->getMessages());
    }

    public function testValidateAddsErrorWhenQuantityIsMissing(): void
    {
        // Arrange
        $warehouse = (new VertexShippingWarehouseTransfer())
            ->setWarehouseAddress(
                (new VertexAddressTransfer())
                    ->setAddress1('123 Warehouse St')
                    ->setAddress2('Building A')
                    ->setCity('New York')
                    ->setCountry('US')
                    ->setZipCode('10001')
            );

        $responseTransfer = new VertexValidationResponseTransfer();
        $validator = new VertexShippingWarehouseValidator(new VertexAddressValidator());

        // Act
        $validator->validate($warehouse, $responseTransfer);

        // Assert
        $this->assertCount(1, $responseTransfer->getMessages());
        $this->assertStringContainsString('quantity', $responseTransfer->getMessages()[0]);
    }

    public function testValidateValidatesWarehouseAddressWhenPresent(): void
    {
        // Arrange
        $warehouse = (new VertexShippingWarehouseTransfer())
            ->setQuantity(10)
            ->setWarehouseAddress(
                (new VertexAddressTransfer())
                    ->setAddress1('123 Warehouse St')
                    ->setCountry('US')
            // Missing city and zipCode
            );

        $responseTransfer = new VertexValidationResponseTransfer();
        $validator = new VertexShippingWarehouseValidator(new VertexAddressValidator());

        // Act
        $validator->validate($warehouse, $responseTransfer);

        // Assert
        $this->assertGreaterThan(0, count($responseTransfer->getMessages()));
        $this->assertStringContainsString('warehouseAddress', $responseTransfer->getMessages()[0]);
    }

    public function testValidateDoesNotValidateAddressWhenWarehouseAddressIsNull(): void
    {
        // Arrange
        $warehouse = (new VertexShippingWarehouseTransfer())
            ->setQuantity(10)
            ->setWarehouseAddress(null);

        $responseTransfer = new VertexValidationResponseTransfer();
        $validator = new VertexShippingWarehouseValidator(new VertexAddressValidator());

        // Act
        $validator->validate($warehouse, $responseTransfer);

        // Assert
        $this->assertEmpty($responseTransfer->getMessages());
    }
}

