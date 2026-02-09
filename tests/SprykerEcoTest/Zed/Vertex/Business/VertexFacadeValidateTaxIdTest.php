<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEcoTest\Zed\Vertex\Business;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\VertexValidationResponseTransfer;
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
 * @group VertexFacadeValidateTaxIdTest
 * Add your own group annotations below this line
 */
class VertexFacadeValidateTaxIdTest extends Unit
{
    protected VertexBusinessTester $tester;

    public function setUp(): void
    {
        parent::setUp();

        $this->tester->setConfig(VertexConstants::IS_ACTIVE, true);

        $this->tester->ensureVertexTaxIdValidationHistoryTableIsEmpty();
    }

    public function testGivenValidTaxIdWhenApiReturnsSuccessfulResponseThenTaxIdValidationHistoryEntryIsCreated(): void
    {
        // Arrange
        $vertexValidationRequestTransfer = $this->tester->createVertexValidationRequestTransfer();

        $vertexClientMock = $this->createMock(VertexClient::class);
        $vertexClientMock->expects($this->once())
            ->method('validateTaxId')
            ->willReturn(
                (new VertexValidationResponseTransfer())
                    ->setIsValid(true)
                    ->setAdditionalInfo('test'),
            );
        $this->tester->mockFactoryMethod('getVertexClient', $vertexClientMock);

        // Act
        $vertexValidationResponseTransfer = $this->tester->getFacade()->validateTaxId($vertexValidationRequestTransfer);

        // Assert
        $this->assertTrue($vertexValidationResponseTransfer->getIsValid());
        $this->tester->assertTaxIdValidationHistoryEntryDoesNotExist($vertexValidationRequestTransfer->getTaxId(), $vertexValidationRequestTransfer->getCountryCode(), $vertexValidationResponseTransfer->getAdditionalInfo());
    }

    public function testGivenMalformedRequestWhenTaxIdValidationApiIsCalledThenTheResponseContainsServiceUnavailableMessage(): void
    {
        // Arrange
        $vertexValidationRequestTransfer = $this->tester->createVertexValidationRequestTransfer();

        $vertexClientMock = $this->createMock(VertexClient::class);
        $vertexClientMock->expects($this->once())
            ->method('validateTaxId')
            ->willReturn(
                (new VertexValidationResponseTransfer())
                    ->setIsValid(false)
                    ->setMessage('Tax Validator API is unavailable.'),
            );
        $this->tester->mockFactoryMethod('getVertexClient', $vertexClientMock);

        // Act
        $vertexValidationResponseTransfer = $this->tester->getFacade()->validateTaxId($vertexValidationRequestTransfer);

        // Assert
        $this->assertFalse($vertexValidationResponseTransfer->getIsValid());
        $this->assertSame('Tax Validator API is unavailable.', $vertexValidationResponseTransfer->getMessage());
    }

    public function testGivenMalformedRequestWhenTaxIdValidationApiIsCalledThenFailedResponseIsReturned(): void
    {
        // Arrange
        $vertexValidationRequestTransfer = $this->tester->createVertexValidationRequestTransfer();

        $vertexClientMock = $this->createMock(VertexClient::class);
        $vertexClientMock->expects($this->once())
            ->method('validateTaxId')
            ->willReturn(
                (new VertexValidationResponseTransfer())
                    ->setIsValid(false)
                    ->setMessage('message'),
            );
        $this->tester->mockFactoryMethod('getVertexClient', $vertexClientMock);

        // Act
        $vertexValidationResponseTransfer = $this->tester->getFacade()->validateTaxId($vertexValidationRequestTransfer);

        // Assert
        $this->assertFalse($vertexValidationResponseTransfer->getIsValid());
        $this->assertSame('message', $vertexValidationResponseTransfer->getMessage());
    }

    public function testValidateTaxIdWhenServiceIsDisabledThenTheErrorMessageIsReturnedInTheResponse(): void
    {
        // Arrange
        $vertexValidationRequestTransfer = $this->tester->createVertexValidationRequestTransfer();
        $this->tester->setConfig(VertexConstants::IS_ACTIVE, false);

        // Act
        $vertexValidationResponseTransfer = $this->tester->getFacade()->validateTaxId($vertexValidationRequestTransfer);

        // Assert
        $this->assertFalse($vertexValidationResponseTransfer->getIsValid());
        $this->assertSame('Tax service is disabled.', $vertexValidationResponseTransfer->getMessage());
    }
}
