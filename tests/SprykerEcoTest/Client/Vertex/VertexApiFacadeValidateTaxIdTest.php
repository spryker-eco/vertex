<?php

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace PyzTest\Zed\VertexApi\Business;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\TaxIdValidationRequestTransfer;
use Generated\Shared\Transfer\VertexConfigTransfer;
use PyzTest\Zed\VertexApi\VertexApiBusinessTester;
use SprykerEcoTest\Client\Vertex\VertexClientTester;

/**
 * Auto-generated group annotations
 *
 * @group PyzTest
 * @group Zed
 * @group VertexApi
 * @group Business
 * @group Facade
 * @group VertexApiFacadeValidateTaxIdTest
 * Add your own group annotations below this line
 */
class VertexApiFacadeValidateTaxIdTest extends Unit
{
    protected VertexClientTester $tester;

    /**
     * @return void
     */
    public function testGivenACustomerProvidesAValidTaxIdWhenTheTaxIdIsValidatedThenASuccessfulResponseIsReturned(): void
    {
        // Arrange
        $vertexConfigTransfer = $this->tester->haveVertexConfig([
            VertexConfigTransfer::IS_ACTIVE => true,
            VertexConfigTransfer::IS_TAX_ID_VALIDATOR_ENABLED => true,
            VertexConfigTransfer::TAXAMO_TOKEN => 'test',
        ]);

        $mockClient = $this->tester->mockVertexHttpClient('taxamo-valid-response');
        $vertexClient = $this->tester->getVertexClientWithMockedFactory($mockClient);
        $taxIdValidationRequestTransfer = $this->tester->haveTaxIdValidationRequestTransfer([
            TaxIdValidationRequestTransfer::TENANT_IDENTIFIER => $vertexConfigTransfer->getStoreReference(),
        ]);

        // Act
        $taxIdValidationRequest = $vertexClient->validateTaxId($taxIdValidationRequestTransfer, $vertexConfigTransfer);

        // Assert
        $request = $this->tester->getLastSentVertexRequest();
        $this->assertStringContainsString(
            sprintf(
                '%s/tax_numbers/%s/validate?country_code=%s',
                rtrim($vertexConfigTransfer->getTaxamoApiUrl(), '/'),
                $taxIdValidationRequestTransfer->getTaxId(),
                $taxIdValidationRequestTransfer->getCountryCode(),
            ),
            (string)$request->getUri(),
        );

        $this->assertTrue($taxIdValidationRequest->getIsValid());
    }

    /**
     * @dataProvider getPossibleResponseCombinationsFromVertex
     *
     * @param int $statusCode
     * @param string $fixtureName
     * @param string $errorMessage
     *
     * @return void
     */
    public function testGivenACustomerProvidesAnInValidDataWhenTheTaxIdIsValidatedThenAFailedResponseIsReturned(
        int $statusCode,
        string $fixtureName,
        string $errorMessage
    ): void {
        // Arrange
        $vertexConfigTransfer = $this->tester->haveVertexConfig([
            VertexConfigTransfer::IS_ACTIVE => true,
            VertexConfigTransfer::IS_TAX_ID_VALIDATOR_ENABLED => true,
            VertexConfigTransfer::TAXAMO_TOKEN => 'test',
        ]);

        $mockClient = $this->tester->mockVertexHttpClient($fixtureName, $statusCode);
        $vertexClient = $this->tester->getVertexClientWithMockedFactory($mockClient);
        $taxIdValidationRequestTransfer = $this->tester->haveTaxIdValidationRequestTransfer([
            TaxIdValidationRequestTransfer::TENANT_IDENTIFIER => $vertexConfigTransfer->getStoreReference(),
        ]);

        // Act
        $taxIdValidationRequest = $vertexClient->validateTaxId($taxIdValidationRequestTransfer, $vertexConfigTransfer);

        // Assert
        $request = $this->tester->getLastSentVertexRequest();
        $this->assertStringContainsString(
            sprintf(
                '%s/tax_numbers/%s/validate?country_code=%s',
                rtrim($vertexConfigTransfer->getTaxamoApiUrl(), '/'),
                $taxIdValidationRequestTransfer->getTaxId(),
                $taxIdValidationRequestTransfer->getCountryCode(),
            ),
            (string)$request->getUri(),
        );
        $this->assertFalse($taxIdValidationRequest->getIsValid());
        $this->assertSame($errorMessage, $taxIdValidationRequest->getMessage());
    }

    /**
     * @return void
     */
    public function testValidateTaxIdWhenValidatorIsDisabledThenAFailedResponseIsReturned(): void
    {
        // Arrange
        $vertexConfigTransfer = $this->tester->haveVertexConfig([
            VertexConfigTransfer::IS_ACTIVE => true,
            VertexConfigTransfer::IS_TAX_ID_VALIDATOR_ENABLED => false,
            VertexConfigTransfer::TAXAMO_TOKEN => 'test',
        ]);

        $taxIdValidationRequestTransfer = $this->tester->haveTaxIdValidationRequestTransfer([
            TaxIdValidationRequestTransfer::TENANT_IDENTIFIER => $vertexConfigTransfer->getStoreReference(),
        ]);

        // Act
        $taxIdValidationRequest = $this->tester->getClient()->validateTaxId($taxIdValidationRequestTransfer, $vertexConfigTransfer);

        // Assert
        $this->assertFalse($taxIdValidationRequest->getIsValid());
    }

    /**
     * @return void
     */
    public function testSendValidationApiRequestTaxIdWithGivenACustomerProvidesAValidTaxIdWhenTheTaxIdIsValidatedThenASuccessfulResponseIsReturned(): void
    {
        // Arrange
        $taxamoApiRequestTransfer = $this->tester->createTaxamoApiRequestTransfer();
        $mockClient = $this->tester->mockVertexHttpClient('taxamo-valid-response', 200);
        $vertexClient = $this->tester->getVertexClientWithMockedFactory($mockClient);

        // Act
        $taxIdValidationRequest = $vertexClient->sendValidationApiRequestTaxId($taxamoApiRequestTransfer);

        // Assert
        $request = $this->tester->getLastSentVertexRequest();
        $this->assertStringContainsString(
            sprintf(
                '%s/tax_numbers/%s/validate?country_code=%s',
                rtrim($taxamoApiRequestTransfer->getTaxamoApiUrl(), '/'),
                $taxamoApiRequestTransfer->getTaxId(),
                $taxamoApiRequestTransfer->getCountryCode(),
            ),
            (string)$request->getUri(),
        );

        $this->assertTrue($taxIdValidationRequest->getIsSuccessful());
    }

    /**
     * @return array<array>
     */
    protected function getPossibleResponseCombinationsFromVertex(): array
    {
        return [
            [200, 'taxamo-invalid-response', 'Wrong format of the tax number.'],
            [200, 'taxamo_invalid_tax_number_format_response', 'Tax number mismatched or non-existent.'],
            [400, 'taxamo_invalid_tax_number_format_response', 'Request to Vertex API failed.'],
            [401, 'taxamo_invalid_tax_number_format_response', 'Invalid credentials.'],
            [500, 'taxamo_invalid_tax_number_format_response', 'Request to Vertex API failed.'],
        ];
    }

    /**
     * @param string $expectedUrl
     *
     * @return \GuzzleHttp\ClientInterface
     */
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
}
