<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEcoTest\Client\Vertex\HttpClient;

use Codeception\Test\Unit;
use Exception;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use SprykerEco\Client\Vertex\HttpClient\FilteringMessageFormatter;
use SprykerEcoTest\Client\Vertex\VertexClientTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerEcoTest
 * @group Client
 * @group Vertex
 * @group HttpClient
 * @group FilteringMessageFormatterTest
 * Add your own group annotations below this line
 */
class FilteringMessageFormatterTest extends Unit
{
    protected VertexClientTester $tester;

    /**
     * @return void
     */
    public function testFormatBuildsCorrectMessageForRequestResponseWithError(): void
    {
        // Arrange
        $filteringMessageFormatter = new FilteringMessageFormatter();

        $request = new Request('GET', '/test');
        $response = new Response();

        // Act
        $message = $filteringMessageFormatter->format($request, $response, new Exception('Error message'));

        // Assert
        $this->assertEquals('Vertex API request sent. Vertex API response received. Error happened.', $message);
    }

    /**
     * @return void
     */
    public function testFormatBuildsCorrectMessageForRequestResponse(): void
    {
        // Arrange
        $filteringMessageFormatter = new FilteringMessageFormatter();

        $request = new Request('GET', '/test');
        $response = new Response();

        // Act
        $message = $filteringMessageFormatter->format($request, $response);

        // Assert
        $this->assertEquals('Vertex API request sent. Vertex API response received.', $message);
    }

    /**
     * @return void
     */
    public function testExtractContextExtractsBuildsCorrectMessageForRequestResponseWithError(): void
    {
        // Arrange
        $filteringMessageFormatter = new FilteringMessageFormatter();

        $request = new Request('GET', '/test');

        // Act
        $context = $filteringMessageFormatter->extractContext($request, null, new Exception('Error message', 100));

        // Assert
        $this->assertEquals('Error message', $context['api_exception']->getMessage());
        $this->assertEquals(100, $context['api_exception']->getCode());
    }

    public function testExtractContextHasCorrectMessageForBadRequestResponseWithApiErrors(): void
    {
        // Arrange
        $filteringMessageFormatter = new FilteringMessageFormatter();

        $request = new Request('POST', '/test');
        $response = new Response(400, [], '{"errors":[{"message":"Error message","code":100}]}');

        // Act
        $context = $filteringMessageFormatter->extractContext($request, $response);

        // Assert
        $this->assertEquals('{"errors":[{"message":"Error message","code":100}]}', $context['api_response']);
    }

    /**
     * @return void
     */
    public function testExtractContextCorrectlyProcessesValidRequest(): void
    {
        // Arrange
        $filteringMessageFormatter = new FilteringMessageFormatter();

        $request = new Request('POST', '/test', ['testHeader' => 'testHeaderValue'], 'testBody');

        // Act
        $context = $filteringMessageFormatter->extractContext($request);

        // Assert
        $this->assertStringContainsString('/test', $context['api_request']);
        $this->assertStringContainsString('POST', $context['api_request']);
    }
}
