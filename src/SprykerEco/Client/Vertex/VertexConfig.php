<?php

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerEco\Client\Vertex;

use Spryker\Client\Kernel\AbstractConfig;

class VertexConfig extends AbstractConfig
{
    /**
     * @var string
     */
    public const CREDENTIALS_GRANT_TYPE = 'client_credentials';

    /**
     * @var int
     */
    public const VERTEX_REQUEST_ACCESS_TOKEN_TIMEOUT = 2;

    /**
     * Specification:
     * - The Vertex API request timeout in seconds.
     *
     * @api
     *
     * @var int
     */
    public const REQUEST_TIMEOUT = 10;

    /**
     * Specification:
     * - The Vertex API request connect timeout in seconds.
     *
     * @api
     *
     * @var int
     */
    public const REQUEST_CONNECT_TIMEOUT = 3;
}
