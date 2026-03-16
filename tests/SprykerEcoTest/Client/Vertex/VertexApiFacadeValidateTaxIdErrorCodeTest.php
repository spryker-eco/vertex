<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEcoTest\Zed\VertexApi\Business;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\VertexConfigTransfer;
use SprykerEcoTest\Client\Vertex\VertexClientTester;

/**
 * @group SprykerEcoTest
 * @group Zed
 * @group VertexApi
 * @group Business
 * @group Facade
 * @group VertexApiFacadeValidateTaxIdErrorCodeTest
 */
class VertexApiFacadeValidateTaxIdErrorCodeTest extends Unit
{
    protected VertexClientTester $tester;

    public function testGivenACustomerProvidesAnInvalidTaxIdFormatWhenTheTaxIdIsValidatedThenErrorCodeIsReturned(): void
    {
        // Arrange
        $vertexConfigTransfer = $this->tester->haveVertexConfig([
            VertexConfigTransfer::IS_ACTIVE => true,
            VertexConfigTransfer::IS_TAX_ID_VALIDATOR_ENABLED => true,
            VertexConfigTransfer::TAXAMO_TOKEN => 'test',
        ]);

        $mockClient = $this->tester->mockVertexHttpClient('taxamo-invalid-response-with-error-code');
        $vertexClient = $this->tester->getVertexClientWithMockedFactory($mockClient);

        $taxIdValidationRequestTransfer = $this->tester->haveTaxIdValidationRequestTransfer();

        // Act
        $taxIdValidationResponse = $vertexClient->validateTaxId($taxIdValidationRequestTransfer, $vertexConfigTransfer);

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
        $this->assertFalse($taxIdValidationResponse->getIsValid());
        $this->assertSame('Wrong format of the tax number.', $taxIdValidationResponse->getMessage());
        $this->assertSame('INVALID_FORMAT', $taxIdValidationResponse->getMessageKey());
    }

    public function testGivenACustomerProvidesAnInvalidTaxIdWhenTheTaxIdIsValidatedThenValidationErrorCodeIsReturned(): void
    {
        // Arrange
        $vertexConfigTransfer = $this->tester->haveVertexConfig([
            VertexConfigTransfer::IS_ACTIVE => true,
            VertexConfigTransfer::IS_TAX_ID_VALIDATOR_ENABLED => true,
            VertexConfigTransfer::TAXAMO_TOKEN => 'test',
        ]);

        $mockClient = $this->tester->mockVertexHttpClient('taxamo-invalid-response-with-validation-error-code');
        $vertexClient = $this->tester->getVertexClientWithMockedFactory($mockClient);
        $taxIdValidationRequestTransfer = $this->tester->haveTaxIdValidationRequestTransfer();

        // Act
        $taxIdValidationResponse = $vertexClient->validateTaxId($taxIdValidationRequestTransfer, $vertexConfigTransfer);

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
        $this->assertFalse($taxIdValidationResponse->getIsValid());
        $this->assertSame('Tax number mismatched or non-existent.', $taxIdValidationResponse->getMessage());
        $this->assertSame('NOT_REGISTERED', $taxIdValidationResponse->getMessageKey());
    }

    /**
     * @dataProvider getPossibleErrorCodeResponseCombinationsFromVertex
     *
     * @param int $statusCode
     * @param string $fixtureName
     * @param string $errorMessage
     * @param string $expectedErrorCode
     *
     * @return void
     */
    public function testGivenACustomerProvidesInvalidDataWhenTheTaxIdIsValidatedThenProperErrorCodeIsReturned(
        int $statusCode,
        string $fixtureName,
        string $errorMessage,
        string $expectedErrorCode,
    ): void {
        // Arrange
        $vertexConfigTransfer = $this->tester->haveVertexConfig([
            VertexConfigTransfer::IS_ACTIVE => true,
            VertexConfigTransfer::IS_TAX_ID_VALIDATOR_ENABLED => true,
            VertexConfigTransfer::TAXAMO_TOKEN => 'test',
        ]);

        $mockClient = $this->tester->mockVertexHttpClient($fixtureName, $statusCode);
        $vertexClient = $this->tester->getVertexClientWithMockedFactory($mockClient);
        $taxIdValidationRequestTransfer = $this->tester->haveTaxIdValidationRequestTransfer();

        // Act
        $taxIdValidationResponse = $vertexClient->validateTaxId($taxIdValidationRequestTransfer, $vertexConfigTransfer);

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
        $this->assertFalse($taxIdValidationResponse->getIsValid());
        $this->assertSame($errorMessage, $taxIdValidationResponse->getMessage());
        $this->assertSame($expectedErrorCode, $taxIdValidationResponse->getMessageKey());
    }

    public function testValidateTaxIdWhenValidatorIsDisabledThenNoErrorCodeIsReturned(): void
    {
        // Arrange
        $vertexConfigTransfer = $this->tester->haveVertexConfig([
            VertexConfigTransfer::IS_ACTIVE => true,
            VertexConfigTransfer::IS_TAX_ID_VALIDATOR_ENABLED => false,
            VertexConfigTransfer::TAXAMO_TOKEN => 'test',
        ]);

        $taxIdValidationRequestTransfer = $this->tester->haveTaxIdValidationRequestTransfer();

        // Act
        $taxIdValidationResponse = $this->tester->getClient()->validateTaxId($taxIdValidationRequestTransfer, $vertexConfigTransfer);

        // Assert
        $this->assertFalse($taxIdValidationResponse->getIsValid());
        $this->assertNull($taxIdValidationResponse->getMessageKey());
    }

    /**
     * @return array<array>
     */
    protected function getPossibleErrorCodeResponseCombinationsFromVertex(): array
    {
        return [
            [400, 'taxamo_invalid_tax_number_format_response', 'Request to Vertex API failed.', 'request-failed'],
            [401, 'taxamo_invalid_tax_number_format_response', 'Invalid credentials.', 'invalid-credentials'],
            [500, 'taxamo_invalid_tax_number_format_response', 'Request to Vertex API failed.', 'request-failed'],
        ];
    }
}
