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
 * @group VertexApiFacadeSubmitVoidPaymentTaxInvoiceMethodTest
 * Add your own group annotations below this line
 */
class VertexApiFacadeSubmitVoidPaymentTaxInvoiceMethodTest extends Unit
{
    protected VertexClientTester $tester;

    /**
     * @return void
     */
    // public function testSubmitVoidPaymentTaxInvoiceMethodReturnsCalculatedTaxDataWhenVertexAPIRequestIsSuccessful(): void
    // {
    //     // Arrange
    //     $this->tester->mockAccessTokenProvider($this->tester->haveValidVertexApiAccessTokenTransfer());
    //     $this->tester->mockClientForVertexTaxQuotationRequest('vertex-tax-quotation-valid-response', 200);
    //     $this->tester->mockVertexConfigFacadeToReturnConfigWithData();
    //     $taxCalculationRequestTransfer = $this->tester->haveTaxCalculationRequestTransfer();
    //     $taxCalculationRequestTransfer->setReportingDate('2023-12-31');

    //     $preparedSale = json_decode($this->tester->getVertexResponseFromFixture('vertex-tax-quotation-valid-request'), true);
    //     $taxCalculationRequestTransfer->getSale()->fromArray($preparedSale, true);

    //     // Act
    //     $taxCalculationResponseTransfer = $this->tester->getFacade()->submitVoidPaymentTaxInvoice($taxCalculationRequestTransfer);

    //     // Assert
    //     $this->assertTrue($taxCalculationResponseTransfer->getIsSuccessful());
    // }
}
