<?php


namespace PyzTest\Zed\VertexApi\Business;

use Codeception\Test\Unit;
use SprykerEcoTest\Client\Vertex\VertexClientTester;

/**
 * Auto-generated group annotations
 *
 * @group PyzTest
 * @group Zed
 * @group VertexApi
 * @group Business
 * @group Facade
 * @group VertexApiFacadeTest
 * Add your own group annotations below this line
 */
class VertexApiFacadeTest extends Unit
{
    protected VertexClientTester $tester;

    /**
     * @return void
     */
    // public function testHandleSubmitPaymentTaxInvoiceReturnsSuccessfulSubmitPaymentTaxInvoiceResponseWhenTaxCalculationResponseIsSuccessful(): void
    // {
    //     // Arrange
    //     $vertexConfigTransfer = $this->tester->havePersistedVertexConfigTransfer();

    //     $submitPaymentTaxInvoiceTransfer = $this->tester->haveSubmitPaymentTaxInvoiceTransfer(['messageAttributes' => ['storeReference' => $vertexConfigTransfer->getStoreReferenceOrFail()]]);

    //     $taxCalculatorMock = $this->createMock(VertexTaxCalculator::class);
    //     $taxCalculationResponseTransfer = (new TaxCalculationResponseTransfer())->setIsSuccessful(true);
    //     $taxCalculatorMock->expects($this->once())->method('calculateTax')->willReturn($taxCalculationResponseTransfer);
    //     $this->tester->mockFactoryMethod('createInvoiceVertexTaxCalculator', $taxCalculatorMock);

    //     // Act
    //     /** @var \Generated\Shared\Transfer\SubmitPaymentTaxInvoiceResponseTransfer $submitPaymentTaxInvoiceResponseTransfer */
    //     $submitPaymentTaxInvoiceResponseTransfer = $this->tester->getFacade()->handleSubmitPaymentTaxInvoice($submitPaymentTaxInvoiceTransfer);

    //     // Assert
    //     $this->assertTrue($submitPaymentTaxInvoiceResponseTransfer->getIsSuccessful());
    // }

    // /**
    //  * @return void
    //  */
    // public function testHandleSubmitPaymentTaxInvoiceReturnsUnsuccessfulSubmitPaymentTaxInvoiceResponseWhenVertexConfigInvoicingEnabledIsFalse(): void
    // {
    //     // Arrange
    //     $vertexConfigTransfer = $this->tester->havePersistedVertexConfigTransfer(['isInvoicingEnabled' => false]);

    //     $submitPaymentTaxInvoiceTransfer = $this->tester->haveSubmitPaymentTaxInvoiceTransfer(['messageAttributes' => ['storeReference' => $vertexConfigTransfer->getStoreReferenceOrFail()]]);

    //     $taxCalculatorMock = $this->createMock(VertexTaxCalculator::class);
    //     $taxCalculatorMock->expects($this->never())->method('calculateTax');
    //     $this->tester->mockFactoryMethod('createInvoiceVertexTaxCalculator', $taxCalculatorMock);

    //     // Act
    //     /** @var \Generated\Shared\Transfer\SubmitPaymentTaxInvoiceResponseTransfer $submitPaymentTaxInvoiceResponseTransfer */
    //     $submitPaymentTaxInvoiceResponseTransfer = $this->tester->getFacade()->handleSubmitPaymentTaxInvoice($submitPaymentTaxInvoiceTransfer);

    //     // Assert
    //     $this->assertFalse($submitPaymentTaxInvoiceResponseTransfer->getIsSuccessful());
    // }

    // /**
    //  * @return void
    //  */
    // public function testHandleSubmitPaymentTaxInvoiceReturnsUnsuccessfulSubmitPaymentTaxInvoiceResponseWhenVertexConfigIsNotActive(): void
    // {
    //     // Arrange
    //     $vertexConfigTransfer = $this->tester->havePersistedVertexConfigTransfer(['isActive' => false]);

    //     $submitPaymentTaxInvoiceTransfer = $this->tester->haveSubmitPaymentTaxInvoiceTransfer(['messageAttributes' => ['storeReference' => $vertexConfigTransfer->getStoreReferenceOrFail()]]);

    //     $taxCalculatorMock = $this->createMock(VertexTaxCalculator::class);
    //     $taxCalculatorMock->expects($this->never())->method('calculateTax');
    //     $this->tester->mockFactoryMethod('createInvoiceVertexTaxCalculator', $taxCalculatorMock);

    //     // Act
    //     /** @var \Generated\Shared\Transfer\SubmitPaymentTaxInvoiceResponseTransfer $submitPaymentTaxInvoiceResponseTransfer */
    //     $submitPaymentTaxInvoiceResponseTransfer = $this->tester->getFacade()->handleSubmitPaymentTaxInvoice($submitPaymentTaxInvoiceTransfer);

    //     // Assert
    //     $this->assertFalse($submitPaymentTaxInvoiceResponseTransfer->getIsSuccessful());
    // }

    // /**
    //  * @return void
    //  */
    // public function testHandleSubmitPaymentTaxInvoiceReturnsUnsuccessfulSubmitPaymentTaxInvoiceResponseWhenVertexConfigIsMissing(): void
    // {
    //     // Arrange
    //     $submitPaymentTaxInvoiceTransfer = $this->tester->haveSubmitPaymentTaxInvoiceTransfer(['messageAttributes' => ['storeReference' => 'SOME_STORE']]);

    //     $taxCalculatorMock = $this->createMock(VertexTaxCalculator::class);
    //     $taxCalculatorMock->expects($this->never())->method('calculateTax');
    //     $this->tester->mockFactoryMethod('createInvoiceVertexTaxCalculator', $taxCalculatorMock);

    //     // Act
    //     /** @var \Generated\Shared\Transfer\SubmitPaymentTaxInvoiceResponseTransfer $submitPaymentTaxInvoiceResponseTransfer */
    //     $submitPaymentTaxInvoiceResponseTransfer = $this->tester->getFacade()->handleSubmitPaymentTaxInvoice($submitPaymentTaxInvoiceTransfer);

    //     // Assert
    //     $this->assertFalse($submitPaymentTaxInvoiceResponseTransfer->getIsSuccessful());
    // }

    // /**
    //  * @return void
    //  */
    // public function testHandleSubmitPaymentTaxInvoiceIncludesDefaultTaxpayerCompanyCode(): void
    // {
    //     // Arrange
    //     $this->tester->mockAccessTokenProvider($this->tester->haveValidVertexApiAccessTokenTransfer());

    //     $vertexConfigTransfer = $this->tester->havePersistedVertexConfigTransfer([
    //         'defaultTaxpayerCompanyCode' => 'TEST',
    //     ]);

    //     $submitPaymentTaxInvoiceTransfer = $this->tester->haveSubmitPaymentTaxInvoiceTransfer([
    //         'messageAttributes' => [
    //             'storeReference' => $vertexConfigTransfer->getStoreReferenceOrFail(),
    //         ],
    //     ]);

    //     $this->tester->mockVertexHttpClient([
    //         $this->tester->getVertexStandardResponse('vertex-tax-quotation-valid-response'),
    //     ]);

    //     // Act
    //     $this->tester->getFacade()->handleSubmitPaymentTaxInvoice($submitPaymentTaxInvoiceTransfer);

    //     // Assert
    //     $request = $this->tester->getLastSentVertexRequest();
    //     $this->assertThat((string)$request->getBody(), new JsonContains([
    //         'seller' => [
    //             'company' => $vertexConfigTransfer->getDefaultTaxpayerCompanyCodeOrFail(),
    //         ],
    //     ]));
    // }

    // /**
    //  * @return void
    //  */
    // public function testHandleSubmitPaymentTaxInvoiceUsesCompanyCodeFromRequest(): void
    // {
    //     // Arrange
    //     $this->tester->mockAccessTokenProvider($this->tester->haveValidVertexApiAccessTokenTransfer());

    //     $vertexConfigTransfer = $this->tester->havePersistedVertexConfigTransfer([
    //         'defaultTaxpayerCompanyCode' => 'TEST',
    //     ]);

    //     $submitPaymentTaxInvoiceTransfer = $this->tester->haveSubmitPaymentTaxInvoiceTransfer([
    //         'messageAttributes' => [
    //             'storeReference' => $vertexConfigTransfer->getStoreReferenceOrFail(),
    //         ],
    //     ]);

    //     $submitPaymentTaxInvoiceTransfer->getSale()->addTaxMetadataEntry('seller', ['company' => 'my-custom-company']);

    //     $this->tester->mockVertexHttpClient([
    //         $this->tester->getVertexStandardResponse('vertex-tax-quotation-valid-response'),
    //     ]);

    //     // Act
    //     $this->tester->getFacade()->handleSubmitPaymentTaxInvoice($submitPaymentTaxInvoiceTransfer);

    //     // Assert
    //     $request = $this->tester->getLastSentVertexRequest();
    //     $this->assertThat((string)$request->getBody(), new JsonContains([
    //         'seller' => [
    //             'company' => 'my-custom-company',
    //         ],
    //     ]));
    // }

    // /**
    //  * @return void
    //  */
    // public function testSubmitVoidPaymentTaxInvoiceReturnsSuccessfulResponseWhenAppIsActiveAndInvoicingIsEnabled(): void
    // {
    //     // Arrange
    //     $vertexConfigTransfer = $this->tester->havePersistedVertexConfigTransfer([
    //         VertexConfigTransfer::IS_ACTIVE => true,
    //         VertexConfigTransfer::IS_INVOICING_ENABLED => true,
    //     ]);

    //     $taxCalculationRequestTransfer = $this->tester->haveTaxCalculationRequestTransfer([
    //         TaxCalculationRequestTransfer::STORE_REFERENCE => $vertexConfigTransfer->getStoreReference(),
    //         TaxCalculationRequestTransfer::TENANT_IDENTIFIER => $vertexConfigTransfer->getStoreReference(),
    //     ]);

    //     $taxCalculatorMock = $this->createMock(VertexTaxCalculator::class);
    //     $taxCalculationResponseTransfer = (new TaxCalculationResponseTransfer())->setIsSuccessful(true);
    //     $taxCalculatorMock->expects($this->once())->method('calculateTax')->willReturn($taxCalculationResponseTransfer);
    //     $this->tester->mockFactoryMethod('createInvoiceVertexTaxCalculator', $taxCalculatorMock);

    //     // Act
    //     /** @var \Generated\Shared\Transfer\SubmitPaymentTaxInvoiceResponseTransfer $submitPaymentTaxInvoiceResponseTransfer */
    //     $submitPaymentTaxInvoiceResponseTransfer = $this->tester->getFacade()->submitVoidPaymentTaxInvoice($taxCalculationRequestTransfer);

    //     // Assert
    //     $this->assertTrue($submitPaymentTaxInvoiceResponseTransfer->getIsSuccessful());
    // }

    // /**
    //  * @return void
    //  */
    // public function testSubmitVoidPaymentTaxInvoiceReturnsUnsuccessfulResponseWhenAppIsActiveAndInvoicingIsDisabled(): void
    // {
    //     // Arrange
    //     $vertexConfigTransfer = $this->tester->havePersistedVertexConfigTransfer([
    //         VertexConfigTransfer::IS_ACTIVE => false,
    //         VertexConfigTransfer::IS_INVOICING_ENABLED => false,
    //     ]);

    //     $taxCalculationRequestTransfer = $this->tester->haveTaxCalculationRequestTransfer([
    //         TaxCalculationRequestTransfer::STORE_REFERENCE => $vertexConfigTransfer->getStoreReference(),
    //     ]);

    //     $taxCalculatorMock = $this->createMock(VertexTaxCalculator::class);
    //     $taxCalculatorMock->expects($this->never())->method('calculateTax');
    //     $this->tester->mockFactoryMethod('createInvoiceVertexTaxCalculator', $taxCalculatorMock);

    //     // Act
    //     /** @var \Generated\Shared\Transfer\SubmitPaymentTaxInvoiceResponseTransfer $submitPaymentTaxInvoiceResponseTransfer */
    //     $submitPaymentTaxInvoiceResponseTransfer = $this->tester->getFacade()->submitVoidPaymentTaxInvoice($taxCalculationRequestTransfer);

    //     // Assert
    //     $this->assertFalse($submitPaymentTaxInvoiceResponseTransfer->getIsSuccessful());
    // }
}
