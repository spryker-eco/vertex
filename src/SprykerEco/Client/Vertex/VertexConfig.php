<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Client\Vertex;

use Spryker\Client\Kernel\AbstractBundleConfig;

class VertexConfig extends AbstractBundleConfig
{
    /**
     * Specification:
     * - OAuth grant type used for Vertex API authentication.
     * - Uses 'client_credentials' grant type for server-to-server authentication.
     *
     * @api
     *
     * @var string
     */
    public const CREDENTIALS_GRANT_TYPE = 'client_credentials';

    /**
     * Specification:
     * - The timeout in seconds for Vertex API access token requests.
     * - Used when requesting OAuth access tokens from the security endpoint.
     *
     * @api
     *
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
