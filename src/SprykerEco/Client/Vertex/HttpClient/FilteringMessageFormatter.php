<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Client\Vertex\HttpClient;

use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class FilteringMessageFormatter implements MessageFormatterInterface
{
    protected const MESSAGE_FORMAT_REQUEST = 'Vertex API request sent.';

    protected const MESSAGE_FORMAT_RESPONSE = '%s Vertex API response received.';

    protected const MESSAGE_FORMAT_ERROR = '%s Error happened.';

    /**
     * @var array<string>
     */
    protected const FILTERED_KEYS = ['password', 'token', 'access_token', 'refresh_token', 'client_secret', 'Authorization'];

    protected const MASKED_VALUE = '*****';

    public function format(RequestInterface $request, ?ResponseInterface $response = null, ?Throwable $error = null): string // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter
    {
        $message = static::MESSAGE_FORMAT_REQUEST;

        // todo: check why do we need all response logs, maybe we can enable it by request for specific tenants only.
        if ($response) {
            $message = sprintf(static::MESSAGE_FORMAT_RESPONSE, $message);
        }

        if ($error) {
            return sprintf(static::MESSAGE_FORMAT_ERROR, $message);
        }

        return $message;
    }

    /**
     * @param \Psr\Http\Message\RequestInterface $originalRequest
     * @param \Psr\Http\Message\ResponseInterface|null $originalResponse
     * @param \Throwable|null $error
     *
     * @return array<string, mixed>
     */
    public function extractContext(RequestInterface $originalRequest, ?ResponseInterface $originalResponse = null, ?Throwable $error = null): array
    {
        $request = clone $originalRequest;

        $context = [
            'api_request' => $request->getMethod() . ' ' . $request->getUri() . ' (body is hidden in logs)',
        ];

        if ($originalResponse) {
            $response = clone $originalResponse;

            $response = $this->filterMessage($response);

            $context = [
                ...$context,
                'api_response' => $response->getBody()->__toString(),
            ];
        }

        if ($error) {
            return [
                ...$context,
                'api_exception' => $error,
            ];
        }

        return $context;
    }

    protected function filterMessage(MessageInterface $message): MessageInterface
    {
        $messageBody = $message->getBody()->__toString();

        if (!$messageBody) {
            return $message;
        }

        $messageData = json_decode($messageBody, true);

        if (json_last_error()) {
            parse_str($messageBody, $messageData);
            $messageData = $this->filterContents($messageData);
            $messageBody = http_build_query($messageData);

            return $message->withBody(Utils::streamFor($messageBody));
        }

        if (!$messageData) {
            return $message;
        }

        if (isset($messageData['errors'])) {
            return $message;
        }

        // this is a special case for Vertex API access token request, as it contains sensitive data.
        if (isset($messageData['access_token'])) {
            return $message->withBody(Utils::streamFor('access token response is masked.'));
        }

        //$messageData['meta'] and $messageData['data']['lineItems'] are usually not needed in logs, so disabled.
        unset($messageData['data']['lineItems']);

        return $message->withBody(Utils::streamFor('(full response is hidden in logs) ' . json_encode($messageData['data'] ?? $messageData)));
    }

    /**
     * @param array<mixed> $contents
     *
     * @return array<mixed>
     */
    protected function filterContents(array $contents): array
    {
        array_walk_recursive($contents, function (&$value, $key): void {
            if (!in_array(strtolower($key), static::FILTERED_KEYS)) {
                return;
            }

            $value = static::MASKED_VALUE;
        });

        return $contents;
    }
}
