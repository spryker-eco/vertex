<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerEcoTest\Client\Vertex;

use Codeception\Actor;
use Codeception\Stub;
use Codeception\Stub\Expected;
use Generated\Shared\Transfer\VertexApiAccessTokenTransfer;
use Generated\Shared\Transfer\VertexApiCredentialTransfer;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use SprykerEco\Client\Vertex\VertexClientInterface;
use SprykerEco\Client\Vertex\VertexDependencyProvider;
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

    /**
     * @return \SprykerEco\Client\Vertex\VertexClientInterface
     */
    public function getClient(): VertexClientInterface
    {
        return $this->getLocator()->vertex()->client();
    }

    /**
     * @param string $fixtureName
     *
     * @return string
     */
    protected function getFixturesPath(string $fixtureName): string
    {
        $pathTemplate = '%s/%s.json';

        return sprintf($pathTemplate, codecept_data_dir('Fixtures'), $fixtureName);
    }

    /**
     * @param string $fixtureName
     *
     * @return string
     */
    public function getVertexResponseFromFixture(string $fixtureName): string
    {
        return file_get_contents($this->getFixturesPath($fixtureName));
    }

    /**
     * @param \Generated\Shared\Transfer\VertexApiCredentialTransfer $vertexApiCredentialTransfer
     *
     * @return \GuzzleHttp\ClientInterface
     */
    public function mockClientForVertexApiCredentialWithValidResponse(VertexApiCredentialTransfer $vertexApiCredentialTransfer): ClientInterface
    {
        $response = new Response(
            200,
            [],
            '{"access_token":"access-token","expires_in":"3600"}',
        );

        return $this->mockClientForVertexApiCredentialRequest($vertexApiCredentialTransfer, $response);
    }

    /**
     * @param \Generated\Shared\Transfer\VertexApiCredentialTransfer $vertexApiCredentialTransfer
     *
     * @return \GuzzleHttp\ClientInterface
     */
    public function mockClientForVertexApiCredentialWithFailedResponse(VertexApiCredentialTransfer $vertexApiCredentialTransfer): ClientInterface
    {
        $response = new Response(
            500,
            [],
            '{"error":"Error message"}',
        );

        return $this->mockClientForVertexApiCredentialRequest($vertexApiCredentialTransfer, $response);
    }

    /**
     * @param \Generated\Shared\Transfer\VertexApiCredentialTransfer $vertexApiCredentialTransfer
     *
     * @return \GuzzleHttp\ClientInterface
     */
    public function mockClientForVertexApiCredentialResponseWithEmptyAccessToken(VertexApiCredentialTransfer $vertexApiCredentialTransfer): ClientInterface
    {
        $response = new Response(
            200,
            [],
            '{"access_token":"","expires_in":"3600"}',
        );

        return $this->mockClientForVertexApiCredentialRequest($vertexApiCredentialTransfer, $response);
    }

    /**
     * @param \Generated\Shared\Transfer\VertexApiCredentialTransfer $vertexApiCredentialTransfer
     *
     * @return \GuzzleHttp\ClientInterface
     */
    public function mockClientForVertexApiCredentialResponseWithMissingAccessToken(VertexApiCredentialTransfer $vertexApiCredentialTransfer): ClientInterface
    {
        $response = new Response(
            200,
            [],
            '{"expires_in":"3600"}',
        );

        return $this->mockClientForVertexApiCredentialRequest($vertexApiCredentialTransfer, $response);
    }

    /**
     * @param \Generated\Shared\Transfer\VertexApiCredentialTransfer $vertexApiCredentialTransfer
     *
     * @return \GuzzleHttp\ClientInterface
     */
    public function mockClientForVertexApiCredentialResponseWithInvalidCredentials(VertexApiCredentialTransfer $vertexApiCredentialTransfer): ClientInterface
    {
        $response = new Response(
            401,
            [],
            '',
        );

        return $this->mockClientForVertexApiCredentialRequest($vertexApiCredentialTransfer, $response);
    }

    /**
     * @param \Generated\Shared\Transfer\VertexApiCredentialTransfer $vertexApiCredentialTransfer
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return \GuzzleHttp\ClientInterface
     */
    protected function mockClientForVertexApiCredentialRequest(
        VertexApiCredentialTransfer $vertexApiCredentialTransfer,
        ResponseInterface $response
    ): ClientInterface {
        $mockClient = Stub::makeEmpty(ClientInterface::class, [
            'request' => function () use ($response) {
                Expected::once();

                return $response;
            },
        ]);

        return $mockClient;
    }

    // /**
    //  * @param \Generated\Shared\Transfer\VertexApiAccessTokenTransfer $vertexApiAccessTokenTransfer
    //  *
    //  * @return void
    //  */
    // public function mockAccessTokenProvider(VertexApiAccessTokenTransfer $vertexApiAccessTokenTransfer): void
    // {
    //     $mockAccessTokenProvider = Stub::makeEmpty(AccessTokenProviderInterface::class, [
    //        'provideVertexAccessToken' => function () use ($vertexApiAccessTokenTransfer) {
    //             Expected::once();

    //             return $vertexApiAccessTokenTransfer;
    //        },
    //     ]);

    //     $this->mockFactoryMethod('createAccessTokenProvider', $mockAccessTokenProvider);
    // }

    /**
     * @return \Generated\Shared\Transfer\VertexApiAccessTokenTransfer
     */
    public function haveValidVertexApiAccessTokenTransfer(): VertexApiAccessTokenTransfer
    {
        return (new VertexApiAccessTokenTransfer())
            ->setAccessToken('access-token')
            ->setCredentialHash('credential-hash')
            ->setExpirationDate('2099-1-1 00:05:00');
    }

    // /**
    //  * @param array $responses
    //  *
    //  * @return void
    //  */
    // public function mockVertexHttpClient(array $responses): void
    // {
    //     $this->mockHttpClient = new MockHandler($responses);

    //     $handlerStack = HandlerStack::create($this->mockHttpClient);
    //     $client = new Client([
    //         'handler' => $handlerStack,
    //         RequestOptions::TIMEOUT => 10,
    //         RequestOptions::CONNECT_TIMEOUT => 3,
    //     ]);

    //     $this->setVertexHttpClient($client);
    // }

    /**
     * @return \GuzzleHttp\ClientInterface
     */
    public function mockVertexHttpClient(string $fixtureName, int $statusCode = 200): ClientInterface
    {
        $this->mockHttpClient = new MockHandler([$this->getVertexStandardResponse($fixtureName, $statusCode)]);

        $handlerStack = HandlerStack::create($this->mockHttpClient);
        $client = new Client([
            'handler' => $handlerStack,
            RequestOptions::TIMEOUT => 10,
            RequestOptions::CONNECT_TIMEOUT => 3,
        ]);

        return $client;
    }

    /**
     * @param \GuzzleHttp\ClientInterface $httpClient
     *
     * @return void
     */
    protected function setVertexHttpClient(ClientInterface $httpClient): void
    {
        $this->mockFactoryMethod('createHttpClient', $httpClient);
    }

    /**
     * @return \Psr\Http\Message\RequestInterface|null
     */
    public function getLastSentVertexRequest(): ?RequestInterface
    {
        return $this->mockHttpClient->getLastRequest();
    }

    /**
     * @param string $fixtureName
     * @param int $code
     *
     * @return \GuzzleHttp\Psr7\Response
     */
    public function getVertexStandardResponse(string $fixtureName, int $code = 200): Response
    {
        return new Response(
            $code,
            [],
            $this->getVertexResponseFromFixture($fixtureName),
        );
    }

    /**
     * @param string $fixtureName
     * @param int $statusCode
     *
     * @return void
     */
    public function mockClientForVertexTaxQuotationRequest(string $fixtureName, int $statusCode): void
    {
        $response = new Response(
            $statusCode,
            [],
            $this->getVertexResponseFromFixture($fixtureName),
        );

        $mockClient = Stub::makeEmpty(ClientInterface::class, [
            'request' => function () use ($response) {
                Expected::once();

                return $response;
            },
        ]);

        $this->mockFactoryMethod('createHttpClient', $mockClient);
    }

    /**
     * @param \GuzzleHttp\ClientInterface $mockClient
     *
     * @return \SprykerEco\Client\Vertex\VertexClientInterface
     */
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

            protected function getDependencyProvider()
            {
                return new VertexDependencyProvider();
            }
        };

        $client = $this->getClient();
        $client->setFactory($factoryMock);

        return $client;
    }
}
