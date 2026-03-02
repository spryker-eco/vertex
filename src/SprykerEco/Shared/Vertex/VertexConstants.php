<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Shared\Vertex;

/**
 * Declares global environment configuration keys. Do not use it for other class constants.
 */
interface VertexConstants
{
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
     * - Vertex vendor code.
     *
     * @api
     *
     * @var string
     */
    public const VENDOR_CODE = 'VERTEX:VENDOR_CODE';

    /**
     * Specification:
     * - Default taxpayer company code.
     *
     * @api
     *
     * @var string
     */
    public const DEFAULT_TAXPAYER_COMPANY_CODE = 'VERTEX:DEFAULT_TAXPAYER_COMPANY_CODE';
}
