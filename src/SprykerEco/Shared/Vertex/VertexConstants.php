<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Shared\Vertex;

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

    /**
     * Specification:
     * - Identifier of the Vertex application.
     *
     * @api
     *
     * @var string
     */
    public const CLIENT_ID = 'VERTEX:CLIENT_ID';

    /**
     * Specification:
     * - Secret key of the Vertex application.
     *
     * @api
     *
     * @var string
     */
    public const CLIENT_SECRET = 'VERTEX:CLIENT_SECRET';

    /**
     * Specification:
     * - The URI of the Vertex application.
     *
     * @api
     *
     * @var string
     */
    public const SECURITY_URI = 'VERTEX:SECURITY_URI';

    /**
     * Specification:
     * - The transactions URI of the Vertex application.
     *
     * @api
     *
     * @var string
     */
    public const TRANSACTION_CALLS_URI = 'VERTEX:TRANSACTION_CALLS_URI';

    /**
     * Specification:
     * - Determines whether the tax calculation is active or not.
     *
     * @api
     *
     * @var string
     */
    public const IS_ACTIVE = 'VERTEX:IS_ACTIVE';

    /**
     * Specification:
     * - The Vertex Taxamo API URL for tax calculation.
     *
     * @api
     *
     * @var string
     */
    public const TAXAMO_API_URL = 'VERTEX:TAXAMO_API_URL';

    /**
     * Specification:
     * - The Vertex Taxamo API token for tax calculation.
     *
     * @api
     *
     * @var string
     */
    public const TAXAMO_TOKEN = 'VERTEX:TAXAMO_TOKEN';

    /**
     * Specification:
     * - Determines whether the tax ID validator is enabled or not.
     *
     * @api
     *
     * @var string
     */
    public const IS_TAX_ID_VALIDATOR_ENABLED = 'VERTEX:IS_TAX_ID_VALIDATOR_ENABLED';

    /**
     * Specification:
     * - Determines whether the invoicing is enabled or not.
     *
     * @var string
     */
    public const IS_INVOICING_ENABLED = 'VERTEX:IS_INVOICING_ENABLED';

    /**
     * Specification:
     * - Determines whether the tax assist is enabled or not.
     *
     * @api
     *
     * @var string
     */
    public const IS_TAX_ASSIST_ENABLED = 'VERTEX:IS_TAX_ASSIST_ENABLED';

    /**
     * Specification:
     * - Determines vendor code.
     *
     * @api
     *
     * @var string
     */
    public const VENDOR_CODE = 'VERTEX:VENDOR_CODE';
}
