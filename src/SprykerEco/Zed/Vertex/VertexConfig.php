<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Vertex;

use Spryker\Zed\Kernel\AbstractBundleConfig;
use SprykerEco\Shared\Vertex\VertexConstants;

class VertexConfig extends AbstractBundleConfig
{
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
        return $this->get(VertexConstants::CLIENT_ID, null);
    }

    public function getClientSecret(): string
    {
        return $this->get(VertexConstants::CLIENT_SECRET, null);
    }

    public function getSecurityUri(): string
    {
        return $this->get(VertexConstants::SECURITY_URI, null);
    }

    public function getTransactionCallsUri(): string
    {
        return $this->get(VertexConstants::TRANSACTION_CALLS_URI, null);
    }

    public function getTaxamoApiUrl(): string
    {
        return $this->get(VertexConstants::TAXAMO_API_URL, null);
    }

    public function getTaxamoToken(): string
    {
        return $this->get(VertexConstants::TAXAMO_TOKEN, null);
    }

    public function isActive(): bool
    {
        return $this->get(VertexConstants::IS_ACTIVE, false);
    }

    public function isTaxIdValidatorEnabled(): bool
    {
        return $this->get(VertexConstants::IS_TAX_ID_VALIDATOR_ENABLED, false);
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

    public function isInvoicingEnabled(): bool
    {
        return $this->get(VertexConstants::IS_INVOICING_ENABLED, false);
    }
}
