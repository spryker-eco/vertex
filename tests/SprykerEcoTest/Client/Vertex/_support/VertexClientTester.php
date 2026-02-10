<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEcoTest\Client\Vertex;

use Codeception\Actor;
use Codeception\Stub;
use Codeception\Stub\Expected;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use SprykerEco\Client\Vertex\VertexClientInterface;
use SprykerEco\Client\Vertex\VertexFactory;

/**
 * Inherited Methods
 *
 * @method \SprykerEco\Client\Vertex\VertexFactory getFactory()
 *
 * @SuppressWarnings(\SprykerEcoTest\Client\Vertex\PHPMD)
 */
class VertexClientTester extends Actor
{
    use _generated\VertexClientTesterActions;

    protected MockHandler $mockHttpClient;

    public function getClient(): VertexClientInterface
    {
        return $this->getLocator()->vertex()->client();
    }

    protected function getFixturesPath(string $fixtureName): string
    {
        $pathTemplate = '%s/%s.json';

        return sprintf($pathTemplate, codecept_data_dir('Fixtures'), $fixtureName);
    }

    public function getVertexResponseFromFixture(string $fixtureName): string
    {
        return file_get_contents($this->getFixturesPath($fixtureName));
    }

    public function mockClientForVertexApiCredentialWithValidResponse(): ClientInterface
    {
        $response = new Response(
            200,
            [],
            '{"access_token":"access-token","expires_in":"3600"}',
        );

        return $this->mockClientForVertexApiCredentialRequest($response);
    }

    public function mockClientForVertexApiCredentialWithFailedResponse(): ClientInterface
    {
        $response = new Response(
            500,
            [],
            '{"error":"Error message"}',
        );

        return $this->mockClientForVertexApiCredentialRequest($response);
    }

    public function mockClientForVertexApiCredentialResponseWithEmptyAccessToken(): ClientInterface
    {
        $response = new Response(
            200,
            [],
            '{"access_token":"","expires_in":"3600"}',
        );

        return $this->mockClientForVertexApiCredentialRequest($response);
    }

    public function mockClientForVertexApiCredentialResponseWithMissingAccessToken(): ClientInterface
    {
        $response = new Response(
            200,
            [],
            '{"expires_in":"3600"}',
        );

        return $this->mockClientForVertexApiCredentialRequest($response);
    }

    public function mockClientForVertexApiCredentialResponseWithInvalidCredentials(): ClientInterface
    {
        $response = new Response(
            401,
            [],
            '',
        );

        return $this->mockClientForVertexApiCredentialRequest($response);
    }

    protected function mockClientForVertexApiCredentialRequest(
        ResponseInterface $response,
    ): ClientInterface {
        return Stub::makeEmpty(ClientInterface::class, [
            'request' => function () use ($response) {
                Expected::once();

                return $response;
            },
        ]);
    }

    public function mockVertexHttpClient(string $fixtureName, int $statusCode = 200): ClientInterface
    {
        $this->mockHttpClient = new MockHandler([$this->getVertexStandardResponse($fixtureName, $statusCode)]);

        $handlerStack = HandlerStack::create($this->mockHttpClient);

        return new Client([
            'handler' => $handlerStack,
            RequestOptions::TIMEOUT => 10,
            RequestOptions::CONNECT_TIMEOUT => 3,
        ]);
    }

    public function getLastSentVertexRequest(): ?RequestInterface
    {
        return $this->mockHttpClient->getLastRequest();
    }

    public function getVertexStandardResponse(string $fixtureName, int $code = 200): Response
    {
        return new Response(
            $code,
            [],
            $this->getVertexResponseFromFixture($fixtureName),
        );
    }

    public function getVertexClientWithMockedFactory(ClientInterface $mockClient): VertexClientInterface
    {
        $factoryMock = new class ($mockClient) extends VertexFactory {
            private ClientInterface $httpClient;

            public function __construct(ClientInterface $httpClient)
            {
                $this->httpClient = $httpClient;
            }

            public function createHttpClient(): ClientInterface
            {
                return $this->httpClient;
            }
        };

        $client = $this->getClient();
        $client->setFactory($factoryMock);

        return $client;
    }
}
