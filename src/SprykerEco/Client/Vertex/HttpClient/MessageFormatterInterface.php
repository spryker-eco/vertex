<?php

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerEco\Client\Vertex\HttpClient;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

interface MessageFormatterInterface
{
    /**
     * @param \Psr\Http\Message\RequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface|null $response
     * @param \Throwable|null $error
     *
     * @return string
     */
    public function format(RequestInterface $request, ?ResponseInterface $response = null, ?Throwable $error = null): string;

    /**
     * @param \Psr\Http\Message\RequestInterface $originalRequest
     * @param \Psr\Http\Message\ResponseInterface|null $originalResponse
     * @param \Throwable|null $error
     *
     * @return array<string, mixed>
     */
    public function extractContext(RequestInterface $originalRequest, ?ResponseInterface $originalResponse = null, ?Throwable $error = null): array;
}
