<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Client\Vertex\Api\V2\Client;

use Generated\Shared\Transfer\VertexApiAccessTokenTransfer;
use Generated\Shared\Transfer\VertexApiResponseTransfer;
use Generated\Shared\Transfer\VertexConfigTransfer;
use Generated\Shared\Transfer\VertexSuppliesTransfer;
use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Spryker\Service\UtilEncoding\UtilEncodingServiceInterface;
use Spryker\Shared\Log\LoggerTrait;
use SprykerEco\Client\Vertex\Api\V2\Builder\VertexSuppliesApiRequestBuilder;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class SuppliesApi implements SuppliesApiInterface
{
    use LoggerTrait;

    protected const REQUEST_FAILED_ERROR_MESSAGE = 'Request to Vertex API failed.';

    protected const INVALID_CREDENTIALS_ERROR_MESSAGE = 'Invalid credentials.';

    public function __construct(
        protected ClientInterface $client,
        protected VertexSuppliesApiRequestBuilder $vertexSuppliesApiRequestBuilder,
        protected UtilEncodingServiceInterface $utilEncodingService
    ) {}

    /**
     * @param \Generated\Shared\Transfer\VertexSuppliesTransfer $vertexSuppliesTransfer
     * @param \Generated\Shared\Transfer\VertexConfigTransfer $vertexConfigTransfer
     * @param \Generated\Shared\Transfer\VertexApiAccessTokenTransfer $vertexApiAccessTokenTransfer
     *
     * @throws \Throwable
     *
     * @return \Generated\Shared\Transfer\VertexApiResponseTransfer
     */
    public function calculateTax(
        VertexSuppliesTransfer $vertexSuppliesTransfer,
        VertexConfigTransfer $vertexConfigTransfer,
        VertexApiAccessTokenTransfer $vertexApiAccessTokenTransfer
    ): VertexApiResponseTransfer {
        $requestBody = $this->vertexSuppliesApiRequestBuilder->buildVertexSuppliesRequest($vertexSuppliesTransfer);

        try {
            $response = $this->client->request(
                'POST',
                str_replace('/supplies', '', $vertexConfigTransfer->getTransactionCallsUriOrFail()) . '/supplies',
                [
                    'headers' => $this->getHeaders($vertexApiAccessTokenTransfer),
                    'body' => $this->utilEncodingService->encodeJson($requestBody),
                    'http_errors' => false,
                ],
            );

            // all not 200 OK responses are handled as errors in handleResponse method
            return $this->handleResponse($response);
        } catch (Throwable $throwable) {
            $this->getLogger()->error(static::REQUEST_FAILED_ERROR_MESSAGE, ['exception' => $throwable]);

            throw $throwable;
        }
    }

    /**
     * @param \Generated\Shared\Transfer\VertexApiAccessTokenTransfer $vertexApiAccessTokenTransfer
     *
     * @return array<string>
     */
    protected function getHeaders(VertexApiAccessTokenTransfer $vertexApiAccessTokenTransfer): array
    {
        return [
            'Authorization' => 'Bearer ' . $vertexApiAccessTokenTransfer->getAccessTokenOrFail(),
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return \Generated\Shared\Transfer\VertexApiResponseTransfer
     */
    protected function handleResponse(ResponseInterface $response): VertexApiResponseTransfer
    {
        $vertexApiResponseTransfer = (new VertexApiResponseTransfer())
            ->setIsSuccessful(true);

        $responseContents = $response->getBody()->__toString();
        $responseStatusCode = $response->getStatusCode();

        if ($responseStatusCode !== Response::HTTP_OK) {
            return $this->getErrorResponse($responseStatusCode, $responseContents);
        }

        $responseData = $this->utilEncodingService->decodeJson($responseContents, true);

        return $vertexApiResponseTransfer
            ->setVertexResponse($responseData);
    }

    /**
     * @param int $responseStatusCode
     * @param string $responseContents
     *
     * @return \Generated\Shared\Transfer\VertexApiResponseTransfer
     */
    protected function getErrorResponse(int $responseStatusCode, string $responseContents): VertexApiResponseTransfer
    {
        $vertexApiResponseTransfer = new VertexApiResponseTransfer();

        $this->getLogger()->error(static::REQUEST_FAILED_ERROR_MESSAGE, ['context' => $responseContents]);

        if ($responseStatusCode === Response::HTTP_UNAUTHORIZED) {
            return $vertexApiResponseTransfer
                ->setIsSuccessful(false)
                ->setErrorMessage(static::INVALID_CREDENTIALS_ERROR_MESSAGE);
        }

        return $vertexApiResponseTransfer
            ->setIsSuccessful(false)
            ->setErrorMessage(static::REQUEST_FAILED_ERROR_MESSAGE . ' ' . $responseContents);
    }
}
