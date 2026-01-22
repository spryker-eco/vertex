<?php

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerEco\Client\Vertex\Api\V2\Client;

use Generated\Shared\Transfer\VertexApiCredentialTransfer;
use Generated\Shared\Transfer\VertexAuthResponseTransfer;
use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use SprykerEco\Client\Vertex\VertexConfig;
use Spryker\Shared\Log\LoggerTrait;
use Throwable;

class SecurityApi implements SecurityApiInterface
{
    use LoggerTrait;

    /**
     * @var string
     */
    protected const INVALID_CREDENTIALS_ERROR_MESSAGE = 'Invalid credentials.';

    /**
     * @var string
     */
    protected const REQUEST_FAILED_ERROR_MESSAGE = 'Request to Vertex API failed.';

    /**
     * @var string
     */
    protected const INVALID_RESPONSE_MESSAGE = 'Invalid response from Vertex API.';

    protected ClientInterface $client;

    /**
     * @param \GuzzleHttp\ClientInterface $client
     */
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @param \Generated\Shared\Transfer\VertexApiCredentialTransfer $vertexApiCredentialTransfer
     *
     * @return \Generated\Shared\Transfer\VertexAuthResponseTransfer
     */
    public function requestAccessToken(
        VertexApiCredentialTransfer $vertexApiCredentialTransfer
    ): VertexAuthResponseTransfer {
        $vertexAuthResponseTransfer = new VertexAuthResponseTransfer();

        try {
            $response = $this->client->request(
                'POST',
                $vertexApiCredentialTransfer->getSecurityUri(),
                [
                    'form_params' => [
                        'client_id' => $vertexApiCredentialTransfer->getClientId(),
                        'client_secret' => $vertexApiCredentialTransfer->getClientSecret(),
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

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return \Generated\Shared\Transfer\VertexAuthResponseTransfer
     */
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

    /**
     * @param int $responseStatusCode
     * @param string $responseContents
     *
     * @return \Generated\Shared\Transfer\VertexAuthResponseTransfer
     */
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
