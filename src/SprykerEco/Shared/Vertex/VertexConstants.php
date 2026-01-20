<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Shared\TaxApp;

/**
 * Declares global environment configuration keys. Do not use it for other class constants.
 */
interface VertexConstants
{
    /**
     * Specification:
     * - Oauth provider name for tax calculation.
     *
     * @api
     *
     * @var string
     */
    public const OAUTH_PROVIDER_NAME = 'VERTEX:OAUTH_PROVIDER_NAME';

    /**
     * Specification:
     * - Oauth grant type for tax calculation.
     *
     * @api
     *
     * @var string
     */
    public const OAUTH_GRANT_TYPE = 'VERTEX:OAUTH_GRANT_TYPE';

    /**
     * Specification:
     * - Oauth audience option for tax calculation.
     *
     * @api
     *
     * @var string
     */
    public const OAUTH_OPTION_AUDIENCE = 'VERTEX:OAUTH_OPTION_AUDIENCE';
}
