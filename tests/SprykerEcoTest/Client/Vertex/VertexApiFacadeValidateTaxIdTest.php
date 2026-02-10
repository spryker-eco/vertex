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
 * @group VertexApiFacadeValidateTaxIdTest
 */
class VertexApiFacadeValidateTaxIdTest extends Unit
{
    protected VertexClientTester $tester;

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
        $taxIdValidationRequestTransfer = $this->tester->haveTaxIdValidationRequestTransfer();

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
        string $errorMessage,
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

    public function testValidateTaxIdWhenValidatorIsDisabledThenAFailedResponseIsReturned(): void
    {
        // Arrange
        $vertexConfigTransfer = $this->tester->haveVertexConfig([
            VertexConfigTransfer::IS_ACTIVE => true,
            VertexConfigTransfer::IS_TAX_ID_VALIDATOR_ENABLED => false,
            VertexConfigTransfer::TAXAMO_TOKEN => 'test',
        ]);

        $taxIdValidationRequestTransfer = $this->tester->haveTaxIdValidationRequestTransfer();

        // Act
        $taxIdValidationRequest = $this->tester->getClient()->validateTaxId($taxIdValidationRequestTransfer, $vertexConfigTransfer);

        // Assert
        $this->assertFalse($taxIdValidationRequest->getIsValid());
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
}
