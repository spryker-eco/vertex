<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEcoTest\Client\Vertex\Validator;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\VertexAddressTransfer;
use Generated\Shared\Transfer\VertexValidationResponseTransfer;
use SprykerEco\Client\Vertex\Validator\VertexAddressValidator;
use SprykerEcoTest\Client\Vertex\VertexClientTester;

/**
 * @group SprykerEcoTest
 * @group Client
 * @group Vertex
 * @group Validator
 * @group VertexAddressValidatorTest
 */
class VertexAddressValidatorTest extends Unit
{
    protected VertexClientTester $tester;

    public function testValidateReturnsNoErrorsWhenAddressIsValid(): void
    {
        // Arrange
        $address = (new VertexAddressTransfer())
            ->setAddress1('123 Main St')
            ->setAddress2('Apt 4')
            ->setCity('New York')
            ->setCountry('US')
            ->setZipCode('10001');

        $responseTransfer = new VertexValidationResponseTransfer();
        $validator = new VertexAddressValidator();

        // Act
        $validator->validate($address, 'testField', $responseTransfer);

        // Assert
        $this->assertEmpty($responseTransfer->getMessages());
    }

    public function testValidateAddsErrorWhenAddress1IsMissing(): void
    {
        // Arrange
        $address = (new VertexAddressTransfer())
            ->setAddress2('Apt 4')
            ->setCity('New York')
            ->setCountry('US')
            ->setZipCode('10001');

        $responseTransfer = new VertexValidationResponseTransfer();
        $validator = new VertexAddressValidator();

        // Act
        $validator->validate($address, 'testField', $responseTransfer);

        // Assert
        $this->assertCount(1, $responseTransfer->getMessages());
        $this->assertStringContainsString('testField.address1', $responseTransfer->getMessages()[0]);
    }

    public function testValidateAddsErrorWhenAddress2IsNull(): void
    {
        // Arrange
        $address = (new VertexAddressTransfer())
            ->setAddress1('123 Main St')
            ->setAddress2(null)
            ->setCity('New York')
            ->setCountry('US')
            ->setZipCode('10001');

        $responseTransfer = new VertexValidationResponseTransfer();
        $validator = new VertexAddressValidator();

        // Act
        $validator->validate($address, 'testField', $responseTransfer);

        // Assert
        $this->assertCount(1, $responseTransfer->getMessages());
        $this->assertStringContainsString('testField.address2', $responseTransfer->getMessages()[0]);
    }

    public function testValidateAddsErrorWhenCityIsMissing(): void
    {
        // Arrange
        $address = (new VertexAddressTransfer())
            ->setAddress1('123 Main St')
            ->setAddress2('Apt 4')
            ->setCountry('US')
            ->setZipCode('10001');

        $responseTransfer = new VertexValidationResponseTransfer();
        $validator = new VertexAddressValidator();

        // Act
        $validator->validate($address, 'testField', $responseTransfer);

        // Assert
        $this->assertCount(1, $responseTransfer->getMessages());
        $this->assertStringContainsString('testField.city', $responseTransfer->getMessages()[0]);
    }

    public function testValidateAddsErrorWhenCountryIsMissing(): void
    {
        // Arrange
        $address = (new VertexAddressTransfer())
            ->setAddress1('123 Main St')
            ->setAddress2('Apt 4')
            ->setCity('New York')
            ->setZipCode('10001');

        $responseTransfer = new VertexValidationResponseTransfer();
        $validator = new VertexAddressValidator();

        // Act
        $validator->validate($address, 'testField', $responseTransfer);

        // Assert
        $this->assertCount(1, $responseTransfer->getMessages());
        $this->assertStringContainsString('testField.country', $responseTransfer->getMessages()[0]);
    }

    public function testValidateAddsErrorWhenZipCodeIsMissing(): void
    {
        // Arrange
        $address = (new VertexAddressTransfer())
            ->setAddress1('123 Main St')
            ->setAddress2('Apt 4')
            ->setCity('New York')
            ->setCountry('US');

        $responseTransfer = new VertexValidationResponseTransfer();
        $validator = new VertexAddressValidator();

        // Act
        $validator->validate($address, 'testField', $responseTransfer);

        // Assert
        $this->assertCount(1, $responseTransfer->getMessages());
        $this->assertStringContainsString('testField.zipCode', $responseTransfer->getMessages()[0]);
    }

    public function testValidateAddsMultipleErrorsWhenMultipleFieldsAreMissing(): void
    {
        // Arrange
        $address = (new VertexAddressTransfer())
            ->setAddress2('Apt 4')
            ->setCountry('US');

        $responseTransfer = new VertexValidationResponseTransfer();
        $validator = new VertexAddressValidator();

        // Act
        $validator->validate($address, 'testField', $responseTransfer);

        // Assert
        $this->assertCount(3, $responseTransfer->getMessages());
        $this->assertStringContainsString('testField.address1', $responseTransfer->getMessages()[0]);
        $this->assertStringContainsString('testField.city', $responseTransfer->getMessages()[1]);
        $this->assertStringContainsString('testField.zipCode', $responseTransfer->getMessages()[2]);
    }
}

