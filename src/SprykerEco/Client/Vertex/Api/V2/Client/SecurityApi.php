<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Client\Vertex\Api\V2\Client;

use Generated\Shared\Transfer\VertexApiCredentialTransfer;
use Generated\Shared\Transfer\VertexAuthResponseTransfer;
use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Spryker\Shared\Log\LoggerTrait;
use SprykerEco\Client\Vertex\VertexConfig;
use Throwable;

class SecurityApi implements SecurityApiInterface
{
    use LoggerTrait;

    protected const INVALID_CREDENTIALS_ERROR_MESSAGE = 'Invalid credentials.';

    protected const REQUEST_FAILED_ERROR_MESSAGE = 'Request to Vertex API failed.';

    protected const INVALID_RESPONSE_MESSAGE = 'Invalid response from Vertex API.';

    public function __construct(protected ClientInterface $client)
    {
    }

    public function requestAccessToken(
        VertexApiCredentialTransfer $vertexApiCredentialTransfer,
    ): VertexAuthResponseTransfer {
        $vertexAuthResponseTransfer = new VertexAuthResponseTransfer();

        try {
            $response = $this->client->request(
                'POST',
                $vertexApiCredentialTransfer->getSecurityUriOrFail(),
                [
                    'form_params' => [
                        'client_id' => $vertexApiCredentialTransfer->getClientIdOrFail(),
                        'client_secret' => $vertexApiCredentialTransfer->getClientSecretOrFail(),
                        'grant_type' => VertexConfig::CREDENTIALS_GRANT_TYPE,
                    ],
                    'timeout' => VertexConfig::VERTEX_REQUEST_ACCESS_TOKEN_TIMEOUT,
                    'http_errors' => false,
                ],
            );

            return $this->handleResponse($response);
        } catch (Throwable $throwable) {
            $this->getLogger()->error(static::REQUEST_FAILED_ERROR_MESSAGE, ['exception' => $throwable]);

            return $vertexAuthResponseTransfer->addError(static::REQUEST_FAILED_ERROR_MESSAGE);
        }
    }

    protected function handleResponse(ResponseInterface $response): VertexAuthResponseTransfer
    {
        $vertexAuthResponseTransfer = new VertexAuthResponseTransfer();

        $responseContents = $response->getBody()->__toString();
        $responseStatusCode = $response->getStatusCode();

        if ($responseStatusCode !== 200) {
            return $this->getErrorResponse($responseStatusCode, $responseContents);
        }

        $responseData = json_decode($responseContents, true);

        if (
            !$responseData['access_token']
            || !$responseData['expires_in']
        ) {
            return $vertexAuthResponseTransfer->addError(static::INVALID_RESPONSE_MESSAGE);
        }

        return $vertexAuthResponseTransfer
            ->setAccessToken($responseData['access_token'])
            ->setExpiresIn((int)$responseData['expires_in']);
    }

    protected function getErrorResponse(int $responseStatusCode, string $responseContents): VertexAuthResponseTransfer
    {
        $vertexAuthResponseTransfer = new VertexAuthResponseTransfer();

        if ($responseStatusCode === 401) {
            return $vertexAuthResponseTransfer->addError(static::INVALID_CREDENTIALS_ERROR_MESSAGE . ' ' . $responseContents);
        }

        $this->getLogger()->error(static::REQUEST_FAILED_ERROR_MESSAGE, ['context' => $responseContents]);

        return $vertexAuthResponseTransfer->addError(static::REQUEST_FAILED_ERROR_MESSAGE);
    }
}
