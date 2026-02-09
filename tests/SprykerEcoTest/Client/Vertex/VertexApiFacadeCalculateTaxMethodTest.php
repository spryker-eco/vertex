<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEcoTest\Client\Vertex;

use Codeception\PHPUnit\Constraint\JsonContains;
use Codeception\Test\Unit;
use Exception;
use Generated\Shared\Transfer\VertexConfigTransfer;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;

/**
 * Auto-generated group annotations
 *
 * @group SprykerEcoTest
 * @group Client
 * @group Vertex
 * @group VertexApiFacadeCalculateQuoteTaxMethodTest
 * Add your own group annotations below this line
 */
class VertexApiFacadeCalculateTaxMethodTest extends Unit
{
    protected VertexClientTester $tester;

    public function testCalculateQuoteTaxMethodReturnsCalculatedTaxDataWhenVertexAPIRequestIsSuccessful(): void
    {
        // Arrange
        $vertexConfigTransfer = $this->tester->haveVertexConfig();
        $mockClient = $this->mockClientForVertexTaxQuotationRequest('vertex-tax-quotation-valid-response', 200);
        $vertexClient = $this->tester->getVertexClientWithMockedFactory($mockClient);
        $vertexCalculationRequestTransfer = $this->tester->haveVertexCalculationRequestTransfer();
        $vertexShipmentTransfer = $vertexCalculationRequestTransfer->getSale()->getShipments()->offsetGet(0);
        $preparedSale = json_decode($this->tester->getVertexResponseFromFixture('vertex-tax-quotation-valid-request'), true);
        $vertexCalculationRequestTransfer->getSale()->fromArray($preparedSale, true);
        foreach ($vertexCalculationRequestTransfer->getSale()->getShipments() as $shipment) {
            $shipment->setShipmentMethodKey($vertexShipmentTransfer->getShipmentMethodKey());
            $shipment->setDiscountAmount($vertexShipmentTransfer->getDiscountAmount());
        }

        // Act
        $vertexCalculationResponseTransfer = $vertexClient->calculateTax($vertexCalculationRequestTransfer, $vertexConfigTransfer);

        // Assert
        $this->assertTrue($vertexCalculationResponseTransfer->getIsSuccessful());
    }

    public function testCalculateQuoteTaxMethodReturnsErrorWhenMissingVertexApiAccessToken(): void
    {
        // Arrange
        $vertexConfigTransfer = $this->tester->haveVertexConfig();
        $mockClient = $this->mockClientForVertexTaxQuotationRequest('vertex-tax-quotation-valid-response', 200);
        $vertexClient = $this->tester->getVertexClientWithMockedFactory($mockClient);
        $vertexCalculationRequestTransfer = $this->tester->haveVertexCalculationRequestTransfer();
        $vertexCalculationRequestTransfer->setVertexApiAccessToken(null);

        // Act
        $vertexCalculationResponseTransfer = $vertexClient->calculateTax($vertexCalculationRequestTransfer, $vertexConfigTransfer);

        // Assert
        $this->assertFalse($vertexCalculationResponseTransfer->getIsSuccessful());
    }

    public function testCalculateQuoteTaxMethodReturnsErrorWhenMissingAccessToken(): void
    {
        // Arrange
        $vertexConfigTransfer = $this->tester->haveVertexConfig();
        $mockClient = $this->mockClientForVertexTaxQuotationRequest('vertex-tax-quotation-valid-response', 200);
        $vertexClient = $this->tester->getVertexClientWithMockedFactory($mockClient);
        $vertexCalculationRequestTransfer = $this->tester->haveVertexCalculationRequestTransfer();
        $vertexCalculationRequestTransfer->setVertexApiAccessToken($this->tester->haveVertexApiAccessToken()->setAccessToken(null));

        // Act
        $vertexCalculationResponseTransfer = $vertexClient->calculateTax($vertexCalculationRequestTransfer, $vertexConfigTransfer);

        // Assert
        $this->assertFalse($vertexCalculationResponseTransfer->getIsSuccessful());
    }

    public function testCalculateQuoteTaxMethodReturnsErrorWhenVertexAppHasNotBeenConfigured(): void
    {
        // Arrange
        $vertexConfigTransfer = new VertexConfigTransfer();
        $mockClient = $this->mockClientForVertexTaxQuotationRequest('vertex-tax-quotation-valid-response', 200);
        $vertexClient = $this->tester->getVertexClientWithMockedFactory($mockClient);
        $vertexCalculationRequestTransfer = $this->tester->haveVertexCalculationRequestTransfer();

        // Act
        $vertexCalculationResponseTransfer = $vertexClient->calculateTax($vertexCalculationRequestTransfer, $vertexConfigTransfer);

        // Assert
        $this->assertFalse($vertexCalculationResponseTransfer->getIsSuccessful());
    }

    /**
     * @return void
     */
    public function testCalculateQuoteTaxMethodReturnsErrorWhenVertexAppIsInactive(): void
    {
        // Arrange
        $vertexConfigTransfer = $this->tester->haveVertexConfig()->setIsActive(false);
        $mockClient = $this->mockClientForVertexTaxQuotationRequest('vertex-tax-quotation-valid-response', 200);
        $vertexClient = $this->tester->getVertexClientWithMockedFactory($mockClient);
        $vertexCalculationRequestTransfer = $this->tester->haveVertexCalculationRequestTransfer();
        $vertexCalculationRequestTransfer->setVertexApiAccessToken($this->tester->haveVertexApiAccessToken()->setAccessToken(null));

        // Act
        $vertexCalculationResponseTransfer = $vertexClient->calculateTax($vertexCalculationRequestTransfer, $vertexConfigTransfer);

        // Assert
        $this->assertFalse($vertexCalculationResponseTransfer->getIsSuccessful());
    }

    public function testCalculateQuoteTaxMethodReturnsErrorWhenVertexAPIRequestIsFailingDueToInvalidCredentials(): void
    {
        // Arrange
        $vertexConfigTransfer = $this->tester->haveVertexConfig();
        $mockClient = $this->mockClientForVertexTaxQuotationRequest('vertex-tax-quotation-invalid-credentials-invalid-response', 401);
        $vertexClient = $this->tester->getVertexClientWithMockedFactory($mockClient);
        $vertexCalculationRequestTransfer = $this->tester->haveVertexCalculationRequestTransfer();

        // Act
        $vertexCalculationResponseTransfer = $vertexClient->calculateTax($vertexCalculationRequestTransfer, $vertexConfigTransfer);

        // Assert
        $this->assertFalse($vertexCalculationResponseTransfer->getIsSuccessful());
        $this->assertStringContainsString('Invalid credentials.', $vertexCalculationResponseTransfer->getErrorMessage());
    }

    public function testCalculateQuoteTaxMethodReturnsErrorWhenVertexAPIRequestIsFailingDueToInvalidRequest(): void
    {
        // Arrange
        $vertexConfigTransfer = $this->tester->haveVertexConfig();
        $mockClient = $this->mockClientForVertexTaxQuotationRequest('vertex-tax-quotation-invalid-request-invalid-response', 400);
        $vertexClient = $this->tester->getVertexClientWithMockedFactory($mockClient);
        $vertexCalculationRequestTransfer = $this->tester->haveVertexCalculationRequestTransfer();

        // Act
        $vertexCalculationResponseTransfer = $vertexClient->calculateTax($vertexCalculationRequestTransfer, $vertexConfigTransfer);

        // Assert
        $this->assertFalse($vertexCalculationResponseTransfer->getIsSuccessful());
        $this->assertStringContainsString('Request to Vertex API failed.', $vertexCalculationResponseTransfer->getErrorMessage());
    }

    public function testCalculateQuoteTaxMethodReturnsErrorWhenVertexAPIRequestIsFailingDueToException(): void
    {
        // Arrange
        $vertexConfigTransfer = $this->tester->haveVertexConfig();
        $vertexCalculationRequestTransfer = $this->tester->haveVertexCalculationRequestTransfer();
        $mockClient = $this->mockClientForVertexTaxQuotationRequestWithException();
        $vertexClient = $this->tester->getVertexClientWithMockedFactory($mockClient);

        // Assert
        $this->expectException(Exception::class);

        // Act
        $vertexCalculationResponseTransfer = $vertexClient->calculateTax($vertexCalculationRequestTransfer, $vertexConfigTransfer);
    }

    public function testCalculateQuoteTaxMethodUsesTheRightTransactionCallsUriToCallSuppliesEndpoint(): void
    {
        // Arrange
        $vertexConfigTransfer = $this->tester->haveVertexConfig();
        $vertexCalculationRequestTransfer = $this->tester->haveVertexCalculationRequestTransfer();

        $vertexConfigTransfer->setTransactionCallsUri('https://transaction-calls-uri.com/vertex-ws/v2');

        //  Assert
        $mockClient = $this->mockAndAssertThatClientUsesTheRightTransactionCallsUriToCallSuppliesEndpoint(
            $vertexConfigTransfer->getTransactionCallsUri() . '/supplies',
        );
        $vertexClient = $this->tester->getVertexClientWithMockedFactory($mockClient);

        // Act
        $vertexCalculationResponseTransfer = $vertexClient->calculateTax($vertexCalculationRequestTransfer, $vertexConfigTransfer);
    }

    public function testCalculateQuoteTaxUsesDefaultTaxpayerCompanyCode(): void
    {
        // Arrange
        $vertexConfigTransfer = $this->tester->haveVertexConfig();
        $vertexConfigTransfer->setDefaultTaxpayerCompanyCode('default-company-code');
        $mockClient = $this->tester->mockVertexHttpClient('vertex-tax-quotation-valid-response');
        $vertexClient = $this->tester->getVertexClientWithMockedFactory($mockClient);
        $vertexCalculationRequestTransfer = $this->tester->haveVertexCalculationRequestTransfer();

        // Act
        $vertexCalculationResponseTransfer = $vertexClient->calculateTax($vertexCalculationRequestTransfer, $vertexConfigTransfer);

        // Assert
        $request = $this->tester->getLastSentVertexRequest();
        $this->assertThat((string)$request->getBody(), new JsonContains([
            'seller' => [
                'company' => 'default-company-code',
            ],
        ]));
    }

    public function testCalculateQuoteTaxDoesNotOverrideSpecifiedCompanyCodeWithDefaultTaxpayerCompanyCode(): void
    {
        // Arrange
        $vertexConfigTransfer = $this->tester->haveVertexConfig();
        $vertexConfigTransfer->setDefaultTaxpayerCompanyCode('default-company-code');
        $vertexCalculationRequestTransfer = $this->tester->haveVertexCalculationRequestTransfer();
        $vertexCalculationRequestTransfer->getSale()->addTaxMetadataEntry('seller', ['company' => 'my-custom-company']);
        $mockClient = $this->tester->mockVertexHttpClient('vertex-tax-quotation-valid-response');
        $vertexClient = $this->tester->getVertexClientWithMockedFactory($mockClient);

        // Act
        $vertexClient->calculateTax($vertexCalculationRequestTransfer, $vertexConfigTransfer);

        // Assert
        $request = $this->tester->getLastSentVertexRequest();
        $this->assertThat((string)$request->getBody(), new JsonContains([
            'seller' => [
                'company' => 'my-custom-company',
            ],
        ]));
    }

    protected function mockAndAssertThatClientUsesTheRightTransactionCallsUriToCallSuppliesEndpoint(string $expectedUrl): ClientInterface
    {
        $response = $this->tester->getVertexStandardResponse('vertex-tax-quotation-valid-response');

        $mockClient = $this->makeEmpty(ClientInterface::class);

        $mockClient->expects($this->once())
            ->method('request')
            ->with('POST', $expectedUrl)
            ->willReturn($response);

        return $mockClient;
    }

    protected function mockClientForVertexTaxQuotationRequestWithException(): ClientInterface
    {
        $mockClient = $this->makeEmpty(ClientInterface::class);

        $mockClient->expects($this->once())
            ->method('request')
            ->willThrowException((new Exception('TEST')));

        return $mockClient;
    }

    protected function mockClientForVertexTaxQuotationRequest(string $fixtureName, int $statusCode): ClientInterface
    {
        $response = new Response(
            $statusCode,
            [],
            $this->tester->getVertexResponseFromFixture($fixtureName),
        );

        $mockClient = $this->makeEmpty(ClientInterface::class);

        $mockClient->expects($this->any())
            ->method('request')
            ->willReturn($response);

        return $mockClient;
    }
}
