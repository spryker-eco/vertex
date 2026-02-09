<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Client\Vertex\Api\V2\Client;

use Generated\Shared\Transfer\TaxamoApiRequestTransfer;
use Generated\Shared\Transfer\VertexApiResponseTransfer;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use Psr\Http\Message\ResponseInterface;
use Spryker\Service\UtilEncoding\UtilEncodingServiceInterface;
use Spryker\Shared\Log\LoggerTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class TaxamoApi
{
    use LoggerTrait;

    /**
     * @var string
     */
    protected const REQUEST_FAILED_ERROR_MESSAGE = 'Request to Vertex API failed.';

    /**
     * @var string
     */
    protected const REQUEST_FAILED_ERROR_MESSAGE_KEY = 'request-failed';

    /**
     * @var string
     */
    protected const INVALID_CREDENTIALS_ERROR_MESSAGE = 'Invalid credentials.';

    /**
     * @var string
     */
    protected const INVALID_CREDENTIALS_ERROR_MESSAGE_KEY = 'invalid-credentials';

    /**
     * @param \GuzzleHttp\ClientInterface $client
     * @param \Spryker\Service\UtilEncoding\UtilEncodingServiceInterface $utilEncodingService
     */
    public function __construct(
        protected ClientInterface $client,
        protected UtilEncodingServiceInterface $utilEncodingService
    ) {
    }

    /**
     * @param \Generated\Shared\Transfer\TaxamoApiRequestTransfer $taxamoApiRequestTransfer
     *
     * @return \Generated\Shared\Transfer\VertexApiResponseTransfer
     */
    public function validateTaxId(TaxamoApiRequestTransfer $taxamoApiRequestTransfer): VertexApiResponseTransfer
    {
        try {
            $response = $this->client->request(
                Request::METHOD_GET,
                sprintf(
                    '%s/tax_numbers/%s/validate?country_code=%s',
                    rtrim($taxamoApiRequestTransfer->getTaxamoApiUrlOrFail(), '/'),
                    $taxamoApiRequestTransfer->getTaxIdOrFail(),
                    $taxamoApiRequestTransfer->getCountryCodeOrFail(),
                ),
                [
                    'headers' => $this->getHeaders($taxamoApiRequestTransfer),
                ],
            );

            return $this->handleResponse($response);
        } catch (ClientException $clientException) {
            return $this->handleResponse($clientException->getResponse());
        } catch (Throwable $throwable) {
            return $this->getErrorResponse(Response::HTTP_UNPROCESSABLE_ENTITY, $throwable->getMessage());
        }
    }

    /**
     * @param \Generated\Shared\Transfer\TaxamoApiRequestTransfer $taxamoApiRequestTransfer
     *
     * @return array<string>
     */
    protected function getHeaders(TaxamoApiRequestTransfer $taxamoApiRequestTransfer): array
    {
        return [
            'x-marketplace-seller-token' => $taxamoApiRequestTransfer->getTaxamoTokenOrFail(),
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
                ->setErrorCode(static::INVALID_CREDENTIALS_ERROR_MESSAGE_KEY)
                ->setErrorMessage(static::INVALID_CREDENTIALS_ERROR_MESSAGE);
        }

        return $vertexApiResponseTransfer
            ->setIsSuccessful(false)
            ->setErrorCode(static::REQUEST_FAILED_ERROR_MESSAGE_KEY)
            ->setErrorMessage(static::REQUEST_FAILED_ERROR_MESSAGE);
    }
}
