<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

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
