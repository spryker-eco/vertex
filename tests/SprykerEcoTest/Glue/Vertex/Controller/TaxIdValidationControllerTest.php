<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Glue\Vertex\Controller;

use Codeception\Stub;
use Codeception\Test\Unit;
use Generated\Shared\Transfer\RestVertexValidationAttributesTransfer;
use Generated\Shared\Transfer\VertexValidationRequestTransfer;
use Generated\Shared\Transfer\VertexValidationResponseTransfer;
use Spryker\Client\GlossaryStorage\GlossaryStorageClientInterface;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceBuilder;
use Spryker\Glue\GlueApplication\Rest\Request\Data\MetadataInterface;
use Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface;
use SprykerEco\Client\Vertex\VertexClientInterface;
use SprykerEco\Glue\Vertex\Controller\TaxIdValidationController;
use SprykerEco\Glue\Vertex\VertexDependencyProvider;
use SprykerEcoTest\Glue\Vertex\VertexTester;
use Symfony\Component\HttpFoundation\Response;

/**
 * Auto-generated group annotations
 *
 * @group SprykerEcoTest
 * @group Glue
 * @group Vertex
 * @group Controller
 * @group TaxIdValidationControllerTest
 * Add your own group annotations below this line
 */
class TaxIdValidationControllerTest extends Unit
{
    /**
     * @var string
     */
    protected const SERVICE_RESOURCE_BUILDER = 'resource_builder';

    /**
     * @var string
     */
    protected const GLOSSARY_KEY_TAX_ID_INVALID = 'validation.error.tax_id_invalid';

    /**
     * @var string
     */
    protected const GLOSSARY_KEY_TAX_ID_FORMAT_INVALID = 'vertex.validation.error.tax_id_format_invalid';

    /**
     * @var string
     */
    protected const GLOSSARY_SUFFIX_VERTEX = 'vertex';

    /**
     * @var \SprykerEcoTest\Glue\Vertex\VertexTester
     */
    protected VertexTester $tester;

    /**
     * @return void
     */
    public function _before(): void
    {
        parent::_before();

        $this->tester->getContainer()->set(static::SERVICE_RESOURCE_BUILDER, new RestResourceBuilder());
    }

    /**
     * @return void
     */
    public function testPostrequestTaxIdValidationWhenRequestIsValidReturnsSuccessfulResponse(): void
    {
        // Arrange
        $restVertexValidationAttributesTransfer = $this->tester->createRestVertexValidationAttributesTransfer();

        $vertexClientMock = $this->getMockBuilder(VertexClientInterface::class)->getMock();
        $restRequestMock = Stub::makeEmpty(RestRequestInterface::class);
        $vertexClientMock
            ->method('requestTaxIdValidation')
            ->with($this->callback(function (VertexValidationRequestTransfer $vertexValidationRequestTransfer) use ($restVertexValidationAttributesTransfer) {
                $this->assertSame($vertexValidationRequestTransfer->getTaxId(), $restVertexValidationAttributesTransfer->getTaxId());
                $this->assertSame($vertexValidationRequestTransfer->getCountryCode(), $restVertexValidationAttributesTransfer->getCountryCode());

                return true;
            }))
            ->willReturn(
                (new VertexValidationResponseTransfer())
                ->setIsValid(true),
            );

        $this->tester->setDependency(VertexDependencyProvider::CLIENT_VERTEX, $vertexClientMock);

        // Act
        $restResponse = (new TaxIdValidationController())->postAction($restRequestMock, $restVertexValidationAttributesTransfer);

        //Assert
        $this->assertCount(0, $restResponse->getErrors());
        $this->assertSame(Response::HTTP_OK, $restResponse->getStatus());
    }

    /**
     * @return void
     */
    public function testGivenAMalformedRequestWhenTheTaxIdValidationApiIsCalledThenTheErrorMessageIsReturnedInTheResponse(): void
    {
        // Arrange
        $restVertexValidationAttributesTransfer = (new RestVertexValidationAttributesTransfer())->setTaxId('test')->setCountryCode('DE');

        $vertexClientMock = $this->getMockBuilder(VertexClientInterface::class)->getMock();
        $restRequestMock = Stub::makeEmpty(RestRequestInterface::class);
        $vertexClientMock
            ->method('requestTaxIdValidation')
            ->willReturn(
                (new VertexValidationResponseTransfer())
                    ->setIsValid(false)
                    ->setMessage('error'),
            );

        $this->tester->setDependency(VertexDependencyProvider::CLIENT_VERTEX, $vertexClientMock);

        // Act
        $restResponse = (new TaxIdValidationController())->postAction($restRequestMock, $restVertexValidationAttributesTransfer);

        //Assert
        $this->assertCount(1, $restResponse->getErrors());
        $this->assertSame('error', $restResponse->getErrors()[0]->getDetail());
        $this->assertSame(Response::HTTP_BAD_REQUEST, $restResponse->getStatus());
    }

    /**
     * @dataProvider glossaryMessageDataProvider
     *
     * @param string $acceptLanguage
     * @param \Generated\Shared\Transfer\RestVertexValidationAttributesTransfer $restVertexValidationAttributesTransfer
     * @param \Generated\Shared\Transfer\VertexValidationResponseTransfer $VertexValidationResponseTransfer
     * @param array<string, string> $glossaryTranslations
     * @param string $expectedMessage
     *
     * @return void
     */
    public function testPostrequestTaxIdValidationWithDifferentLocalesAndGlossaryKeys(
        string $acceptLanguage,
        RestVertexValidationAttributesTransfer $restVertexValidationAttributesTransfer,
        VertexValidationResponseTransfer $VertexValidationResponseTransfer,
        array $glossaryTranslations,
        string $expectedMessage
    ): void {
        // Arrange
        $vertexClientMock = $this->getMockBuilder(VertexClientInterface::class)->getMock();
        $glossaryStorageClientMock = $this->getMockBuilder(GlossaryStorageClientInterface::class)->getMock();

        // Create metadata mock with the accept language
        $metadataMock = $this->getMockBuilder(MetadataInterface::class)->getMock();
        $metadataMock->method('getLocale')->willReturn($acceptLanguage);

        // Create REST request mock with metadata
        $restRequestMock = $this->getMockBuilder(RestRequestInterface::class)->getMock();
        $restRequestMock->method('getMetadata')->willReturn($metadataMock);

        $vertexClientMock
            ->method('requestTaxIdValidation')
            ->willReturn($VertexValidationResponseTransfer);

        $glossaryStorageClientMock
            ->method('translate')
            ->willReturnCallback(function (string $key, string $locale) use ($glossaryTranslations) {
                $lookupKey = $key . '.' . $locale;

                return $glossaryTranslations[$lookupKey] ?? $key;
            });

        $this->tester->setDependency(VertexDependencyProvider::CLIENT_VERTEX, $vertexClientMock);
        $this->tester->setDependency(VertexDependencyProvider::CLIENT_GLOSSARY_STORAGE, $glossaryStorageClientMock);

        // Act
        $restResponse = (new TaxIdValidationController())->postAction($restRequestMock, $restVertexValidationAttributesTransfer);

        // Assert
        $this->assertCount(1, $restResponse->getErrors());
        $this->assertSame($expectedMessage, $restResponse->getErrors()[0]->getDetail());
        $this->assertSame(Response::HTTP_BAD_REQUEST, $restResponse->getStatus());
    }

    /**
     * @return array<string, array<mixed>>
     */
    public function glossaryMessageDataProvider(): array
    {
        return [
            'with English locale and valid translation' => [
                'en_US',
                (new RestVertexValidationAttributesTransfer())
                    ->setTaxId('DE123456789')
                    ->setCountryCode('DE'),
                (new VertexValidationResponseTransfer())
                    ->setIsValid(false)
                    ->setMessage('Default error message')
                    ->setMessageKey(static::GLOSSARY_KEY_TAX_ID_INVALID),
                [
                    sprintf('%s.%s.en_US', static::GLOSSARY_SUFFIX_VERTEX, static::GLOSSARY_KEY_TAX_ID_INVALID) => 'The tax ID is invalid.',
                ],
                'The tax ID is invalid.',
            ],
            'with German locale and valid translation' => [
                'de_DE',
                (new RestVertexValidationAttributesTransfer())
                    ->setTaxId('DE123456789')
                    ->setCountryCode('DE'),
                (new VertexValidationResponseTransfer())
                    ->setIsValid(false)
                    ->setMessage('Default error message')
                    ->setMessageKey(static::GLOSSARY_KEY_TAX_ID_INVALID),
                [
                    sprintf('%s.%s.de_DE', static::GLOSSARY_SUFFIX_VERTEX, static::GLOSSARY_KEY_TAX_ID_INVALID) => 'Die Steuer-ID ist ungültig.',
                ],
                'Die Steuer-ID ist ungültig.',
            ],
            'with locale but no translation should use default message' => [
                'en_US',
                (new RestVertexValidationAttributesTransfer())
                    ->setTaxId('DE123456789')
                    ->setCountryCode('DE'),
                (new VertexValidationResponseTransfer())
                    ->setIsValid(false)
                    ->setMessage('Default error message')
                    ->setMessageKey(static::GLOSSARY_KEY_TAX_ID_INVALID),
                [
                    // No translation available for this key
                ],
                'Default error message',
            ],
            'with locale but no code should use default message' => [
                'en_US',
                (new RestVertexValidationAttributesTransfer())
                    ->setTaxId('DE123456789')
                    ->setCountryCode('DE'),
                (new VertexValidationResponseTransfer())
                    ->setIsValid(false)
                    ->setMessage('Default error message')
                    ->setMessageKey(null),
                [],
                'Default error message',
            ],
            'with different glossary key' => [
                'en_US',
                (new RestVertexValidationAttributesTransfer())
                    ->setTaxId('DE123456789')
                    ->setCountryCode('DE'),
                (new VertexValidationResponseTransfer())
                    ->setIsValid(false)
                    ->setMessage('Format is invalid')
                    ->setMessageKey(static::GLOSSARY_KEY_TAX_ID_FORMAT_INVALID),
                [
                    sprintf('%s.%s.en_US', static::GLOSSARY_SUFFIX_VERTEX, static::GLOSSARY_KEY_TAX_ID_FORMAT_INVALID) => 'The tax ID format is invalid.',
                ],
                'The tax ID format is invalid.',
            ],
        ];
    }
}
