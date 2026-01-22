<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Vertex;

use Spryker\Shared\Vertex\VertexConstants;
use Spryker\Zed\Kernel\AbstractBundleConfig;

class VertexConfig extends AbstractBundleConfig
{
    public const CLIENT_ID = 'VERTEX:CLIENT_ID';

    public const CLIENT_SECRET = 'VERTEX:CLIENT_SECRET';

    public const SECURITY_URI = 'VERTEX:SECURITY_URI';

    public const TRANSACTION_CALLS_URI = 'VERTEX:TRANSACTION_CALLS_URI';

    public const IS_ACTIVE = 'VERTEX:IS_ACTIVE';

    /**
     * @var string
     */
    public const MESSAGE_VERTEX_IS_DISABLED = 'Tax service is disabled.';

    /**
     * @var string
     */
    public const MESSAGE_TAX_VALIDATOR_IS_UNAVAILABLE = 'Tax Validator API is unavailable.';


    public function getClientId(): string
    {
        return $this->get(static::CLIENT_ID, null);
    }

    public function getClientSecret(): string
    {
        return $this->get(static::CLIENT_SECRET, null);
    }

    public function getSecurityUri(): string
    {
        return $this->get(static::SECURITY_URI, null);
    }

    public function getTransactionCallsUri(): string
    {
        return $this->get(static::TRANSACTION_CALLS_URI, null);
    }

    public function isActive(): bool
    {
        return $this->get(static::IS_ACTIVE, false);
    }

    /**
     * @api
     *
     * @return string
     */
    public function getOauthProviderNameForTaxCalculation(): string
    {
        return $this->get(VertexConstants::OAUTH_PROVIDER_NAME, '');
    }

    /**
     * @api
     *
     * @return string
     */
    public function getOauthGrantTypeForTaxCalculation(): string
    {
        return $this->get(VertexConstants::OAUTH_GRANT_TYPE, '');
    }

    /**
     * @api
     *
     * @return string
     */
    public function getOauthOptionAudienceForTaxCalculation(): string
    {
        return $this->get(VertexConstants::OAUTH_OPTION_AUDIENCE, '');
    }

    /**
     * Specification:
     * - 2 letters ISO country code, for example US, DE
     * - Overrides the default value (the first country of the store defined in the Quote/Order).
     *
     * @api
     *
     * @return string
     */
    public function getSellerCountryCode(): string
    {
        return '';
    }

    /**
     * Specification:
     * - 2 letters ISO country code, for example US, DE
     * - Used for tax calculation when a customer did not provide shipping address.
     * - Overrides the default value (the first country of the store defined in the Quote/Order).
     *
     * @api
     *
     * @return string
     */
    public function getCustomerCountryCode(): string
    {
        return '';
    }
}
