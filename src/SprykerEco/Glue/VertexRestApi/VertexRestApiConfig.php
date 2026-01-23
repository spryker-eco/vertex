<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Glue\VertexRestApi;

use Spryker\Glue\Kernel\AbstractBundleConfig;

class VertexRestApiConfig extends AbstractBundleConfig
{
    /**
     * @var string
     */
    public const RESOURCE_TAX_VALIATE_ID = 'tax-id-validate';

    /**
     * @var string
     */
    public const CONTROLLER_TAX_VALIATE_ID = 'tax-id-validation';

    /**
     * @var string
     */
    public const RESPONSE_DETAIL_MESSAGE_INVALID_REQUEST_DATA = 'Invalid request data.';
}
