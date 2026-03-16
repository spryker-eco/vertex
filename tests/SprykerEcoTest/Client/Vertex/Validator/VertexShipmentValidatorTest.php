<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEcoTest\Client\Vertex\Validator;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\VertexAddressTransfer;
use Generated\Shared\Transfer\VertexShipmentTransfer;
use Generated\Shared\Transfer\VertexValidationResponseTransfer;
use SprykerEco\Client\Vertex\Validator\VertexAddressValidator;
use SprykerEco\Client\Vertex\Validator\VertexShipmentValidator;
use SprykerEcoTest\Client\Vertex\VertexClientTester;

/**
 * @group SprykerEcoTest
 * @group Client
 * @group Vertex
 * @group Validator
 * @group VertexShipmentValidatorTest
 */
class VertexShipmentValidatorTest extends Unit
{
    protected VertexClientTester $tester;

    public function testValidateReturnsNoErrorsWhenShipmentIsValid(): void
    {
        // Arrange
        $shipment = (new VertexShipmentTransfer())
            ->setId('shipment-1')
            ->setPriceAmount(1000)
            ->setShipmentMethodKey('standard')
            ->setShippingAddress(
                (new VertexAddressTransfer())
                    ->setAddress1('123 Main St')
                    ->setAddress2('Apt 4')
                    ->setCity('New York')
                    ->setCountry('US')
                    ->setZipCode('10001'),
            );

        $responseTransfer = new VertexValidationResponseTransfer();
        $validator = new VertexShipmentValidator(new VertexAddressValidator());

        // Act
        $validator->validate($shipment, $responseTransfer);

        // Assert
        $this->assertEmpty($responseTransfer->getMessages());
    }

    public function testValidateAddsErrorWhenIdIsMissing(): void
    {
        // Arrange
        $shipment = (new VertexShipmentTransfer())
            ->setPriceAmount(1000)
            ->setShipmentMethodKey('standard')
            ->setShippingAddress(
                (new VertexAddressTransfer())
                    ->setAddress1('123 Main St')
                    ->setAddress2('Apt 4')
                    ->setCity('New York')
                    ->setCountry('US')
                    ->setZipCode('10001'),
            );

        $responseTransfer = new VertexValidationResponseTransfer();
        $validator = new VertexShipmentValidator(new VertexAddressValidator());

        // Act
        $validator->validate($shipment, $responseTransfer);

        // Assert
        $this->assertCount(1, $responseTransfer->getMessages());
        $this->assertStringContainsString('id', $responseTransfer->getMessages()[0]);
    }

    public function testValidateAddsErrorWhenPriceAmountIsMissing(): void
    {
        // Arrange
        $shipment = (new VertexShipmentTransfer())
            ->setId('shipment-1')
            ->setShipmentMethodKey('standard')
            ->setShippingAddress(
                (new VertexAddressTransfer())
                    ->setAddress1('123 Main St')
                    ->setAddress2('Apt 4')
                    ->setCity('New York')
                    ->setCountry('US')
                    ->setZipCode('10001'),
            );

        $responseTransfer = new VertexValidationResponseTransfer();
        $validator = new VertexShipmentValidator(new VertexAddressValidator());

        // Act
        $validator->validate($shipment, $responseTransfer);

        // Assert
        $this->assertCount(1, $responseTransfer->getMessages());
        $this->assertStringContainsString('priceAmount', $responseTransfer->getMessages()[0]);
    }

    public function testValidateAddsErrorWhenShippingAddressIsMissing(): void
    {
        // Arrange
        $shipment = (new VertexShipmentTransfer())
            ->setId('shipment-1')
            ->setPriceAmount(1000)
            ->setShipmentMethodKey('standard');

        $responseTransfer = new VertexValidationResponseTransfer();
        $validator = new VertexShipmentValidator(new VertexAddressValidator());

        // Act
        $validator->validate($shipment, $responseTransfer);

        // Assert
        $this->assertCount(1, $responseTransfer->getMessages());
        $this->assertStringContainsString('shippingAddress', $responseTransfer->getMessages()[0]);
    }

    public function testValidateValidatesShippingAddressWhenPresent(): void
    {
        // Arrange
        $shipment = (new VertexShipmentTransfer())
            ->setId('shipment-1')
            ->setPriceAmount(1000)
            ->setShipmentMethodKey('standard')
            ->setShippingAddress(
                (new VertexAddressTransfer())
                    ->setAddress1('123 Main St'),
                // Missing city, country, zipCode
            );

        $responseTransfer = new VertexValidationResponseTransfer();
        $validator = new VertexShipmentValidator(new VertexAddressValidator());

        // Act
        $validator->validate($shipment, $responseTransfer);

        // Assert
        $this->assertGreaterThan(0, count($responseTransfer->getMessages()));
        $this->assertStringContainsString('shippingAddress', $responseTransfer->getMessages()[0]);
    }

    public function testValidateValidatesBillingAddressWhenPresent(): void
    {
        // Arrange
        $shipment = (new VertexShipmentTransfer())
            ->setId('shipment-1')
            ->setPriceAmount(1000)
            ->setShipmentMethodKey('standard')
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
                    ->setAddress1('456 Billing St'),
                // Missing city, country, zipCode
            );

        $responseTransfer = new VertexValidationResponseTransfer();
        $validator = new VertexShipmentValidator(new VertexAddressValidator());

        // Act
        $validator->validate($shipment, $responseTransfer);

        // Assert
        $this->assertSame(4, count($responseTransfer->getMessages()));
        $this->assertEqualsCanonicalizing(
            [
                'Address field billingAddress.address2 is required',
                'Address field billingAddress.zipCode is required',
                'Address field billingAddress.city is required',
                'Address field billingAddress.country is required',
            ],
            $responseTransfer->getMessages(),
        );
    }
}
