<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEcoTest\Zed\Vertex\Business;

use Codeception\Stub;
use Codeception\Test\Unit;
use Generated\Shared\Transfer\AcpHttpRequestTransfer;
use Generated\Shared\Transfer\AcpHttpResponseTransfer;
use Generated\Shared\Transfer\StoreTransfer;
use Generated\Shared\Transfer\TaxAppValidationResponseTransfer;
use Generated\Shared\Transfer\VertexConfigTransfer;
use Generated\Shared\Transfer\VertexValidationResponseTransfer;
use Spryker\Zed\TaxApp\Dependency\Facade\TaxAppToStoreFacadeInterface;
use SprykerEco\Client\Vertex\VertexClient;
use SprykerEcoTest\Zed\Vertex\VertexBusinessTester;
use SprykerTest\Zed\TaxApp\TaxAppBusinessTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group TaxApp
 * @group Business
 * @group Facade
 * @group VertexFacadeValidateTaxIdTest
 * Add your own group annotations below this line
 */
class TaxAppFacadeValidateTaxIdTest extends Unit
{
    protected VertexBusinessTester $tester;

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->tester->ensureTaxIdValidationHistoryTableIsEmpty();
    }

    public function testGivenValidTaxIdWhenApiReturnsSuccessfulResponseThenTaxIdValidationHistoryEntryIsCreated(): void
    {
        // Arrange
        $vertexValidationRequestTransfer = $this->tester->createVertexValidationRequestTransfer();
        $taxAppConfigTransfer = (new VertexConfigTransfer())->setVendorCode('vendorCode')->setIsActive(true);
        $vertexValidationResponseTransfer = (new VertexValidationResponseTransfer())
            ->setAdditionalInfo('test')
            ->setIsValid(true);

        // Mock client
        $vertexClientMock = $this->createMock(VertexClient::class);
        $vertexClientMock->expects($this->once())
            ->method('validateTaxId')
            ->willReturn(
                (new VertexValidationResponseTransfer())
                    ->setIsValid(true)
                    ->setAdditionalInfo('test')
            );
        $this->tester->mockFactoryMethod('getVertexClient', $vertexClientMock);

        // Act
        $vertexValidationResponseTransfer = $this->tester->getFacade()->validateTaxId($vertexValidationRequestTransfer);

        // Assert
        $this->assertTrue($vertexValidationResponseTransfer->getIsValid());
        $this->tester->assertTaxIdValidationHistoryEntryDoesNotExist($vertexValidationRequestTransfer->getTaxId(), $vertexValidationRequestTransfer->getCountryCode(), $vertexValidationResponseTransfer->getAdditionalInfo());
    }

    /**
     * @return void
     */
    public function testGivenAMalformedRequestWhenTheTaxIdValidationApiIsCalledThenTheResponseContainsAServiceUnavailableMessage(): void
    {
        // Arrange
        $vertexValidationRequestTransfer = $this->tester->createTaxAppValidationRequestTransfer();
        $this->tester->haveTaxAppConfig(['vendor_code' => 'vendorCode', 'fk_store' => $this->storeTransfer->getIdStore(), 'is_active' => true]);

        // Mock client
        $vertexClientMock = $this->createMock(VertexClient::class);
        $vertexClientMock->expects($this->once())
            ->method('validateTaxId')
            ->willReturn(
                (new VertexValidationResponseTransfer())
                    ->setIsValid(false)
                    ->setMessage('Tax Validator API is unavailable.')
            );
        $this->tester->mockFactoryMethod('getVertexClient', $vertexClientMock);

        // Act
        $vertexValidationResponseTransfer = $this->tester->getFacade()->validateTaxId($vertexValidationRequestTransfer);

        // Assert
        $this->assertFalse($vertexValidationResponseTransfer->getIsValid());
        $this->assertSame('Tax Validator API is unavailable.', $vertexValidationResponseTransfer->getMessage());
    }

    /**
     * @return void
     */
    public function testGivenAMalformedRequestWhenTheTaxIdValidationApiIsCalledThenAFailedResponseIsReturned(): void
    {
        // Arrange
        $vertexValidationRequestTransfer = $this->tester->createTaxAppValidationRequestTransfer();
        $this->tester->haveTaxAppConfig(['vendor_code' => 'vendorCode', 'fk_store' => $this->storeTransfer->getIdStore(), 'is_active' => true]);

        // Mock client
        $vertexClientMock = $this->createMock(VertexClient::class);
        $vertexClientMock->expects($this->once())
            ->method('validateTaxId')
            ->willReturn(
                (new VertexValidationResponseTransfer())
                    ->setIsValid(false)
                    ->setMessage('message')
            );
        $this->tester->mockFactoryMethod('getVertexClient', $vertexClientMock);

        // Act
        $vertexValidationResponseTransfer = $this->tester->getFacade()->validateTaxId($vertexValidationRequestTransfer);

        // Assert
        $this->assertFalse($vertexValidationResponseTransfer->getIsValid());
        $this->assertSame('message', $vertexValidationResponseTransfer->getMessage());
    }

    /**
     * @return void
     */
    public function testValidateTaxIdWhenServiceIsDisabledThenTheErrorMessageIsReturnedInTheResponse(): void
    {
        // Arrange
        $vertexValidationRequestTransfer = $this->tester->createTaxAppValidationRequestTransfer();
        $this->tester->haveTaxAppConfig(['vendor_code' => 'vendorCode', 'fk_store' => $this->storeTransfer->getIdStore(), 'is_active' => false]);

        // Act
        $vertexValidationResponseTransfer = $this->tester->getFacade()->validateTaxId($vertexValidationRequestTransfer);

        // Assert
        $this->assertFalse($vertexValidationResponseTransfer->getIsValid());
        $this->assertSame('Tax service is disabled.', $vertexValidationResponseTransfer->getMessage());
    }
}
