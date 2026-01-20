<?php

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Pyz\Zed\VertexApi\Business\Api\V2\Client;

use Generated\Shared\Transfer\VertexApiAuthResponseTransfer;
use Generated\Shared\Transfer\VertexApiCredentialTransfer;
use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Pyz\Zed\VertexApi\VertexApiConfig;
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
     * @return \Generated\Shared\Transfer\VertexApiAuthResponseTransfer
     */
    public function requestAccessToken(
        VertexApiCredentialTransfer $vertexApiCredentialTransfer
    ): VertexApiAuthResponseTransfer {
        $vertexApiAuthResponseTransfer = new VertexApiAuthResponseTransfer();

        try {
            $response = $this->client->request(
                'POST',
                $vertexApiCredentialTransfer->getSecurityUri(),
                [
                    'form_params' => [
                        'client_id' => $vertexApiCredentialTransfer->getClientId(),
                        'client_secret' => $vertexApiCredentialTransfer->getClientSecret(),
                        'grant_type' => VertexApiConfig::CREDENTIALS_GRANT_TYPE,
                    ],
                    'timeout' => VertexApiConfig::VERTEX_REQUEST_ACCESS_TOKEN_TIMEOUT,
                    'http_errors' => false,
                ],
            );

            return $this->handleResponse($response);
        } catch (Throwable $throwable) {
            $this->getLogger()->error(static::REQUEST_FAILED_ERROR_MESSAGE, ['exception' => $throwable]);

            return $vertexApiAuthResponseTransfer->addError(static::REQUEST_FAILED_ERROR_MESSAGE);
        }
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return \Generated\Shared\Transfer\VertexApiAuthResponseTransfer
     */
    protected function handleResponse(ResponseInterface $response): VertexApiAuthResponseTransfer
    {
        $vertexApiAuthResponseTransfer = new VertexApiAuthResponseTransfer();

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
            return $vertexApiAuthResponseTransfer->addError(static::INVALID_RESPONSE_MESSAGE);
        }

        return $vertexApiAuthResponseTransfer
            ->setAccessToken($responseData['access_token'])
            ->setExpiresIn((int)$responseData['expires_in']);
    }

    /**
     * @param int $responseStatusCode
     * @param string $responseContents
     *
     * @return \Generated\Shared\Transfer\VertexApiAuthResponseTransfer
     */
    protected function getErrorResponse(int $responseStatusCode, string $responseContents): VertexApiAuthResponseTransfer
    {
        $vertexApiAuthResponseTransfer = new VertexApiAuthResponseTransfer();

        if ($responseStatusCode === 401) {
            return $vertexApiAuthResponseTransfer->addError(static::INVALID_CREDENTIALS_ERROR_MESSAGE . ' ' . $responseContents);
        }

        $this->getLogger()->error(static::REQUEST_FAILED_ERROR_MESSAGE, ['context' => $responseContents]);

        return $vertexApiAuthResponseTransfer->addError(static::REQUEST_FAILED_ERROR_MESSAGE);
    }
}
