<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Zed\Vertex;

use Spryker\Zed\Kernel\AbstractBundleConfig;
use SprykerEco\Shared\Vertex\VertexConstants;

class VertexConfig extends AbstractBundleConfig
{
    public const MESSAGE_VERTEX_IS_DISABLED = 'Tax service is disabled.';

    protected const string CONFIGURATION_KEY_SECURITY_URI = 'integrations:vertex:configurations:security_uri';

    protected const string CONFIGURATION_KEY_TRANSACTION_CALLS_URI = 'integrations:vertex:configurations:transaction_calls_uri';

    protected const string CONFIGURATION_KEY_CLIENT_ID = 'integrations:vertex:configurations:client_id';

    protected const string CONFIGURATION_KEY_CLIENT_SECRET = 'integrations:vertex:configurations:client_secret';

    protected const string CONFIGURATION_KEY_DEFAULT_TAXPAYER_COMPANY_CODE = 'integrations:vertex:configurations:default_taxpayer_company_code';

    protected const string CONFIGURATION_KEY_IS_TAX_ID_VALIDATOR_ENABLED = 'integrations:vertex:taxamo:is_tax_id_validator_enabled';

    protected const string CONFIGURATION_KEY_TAXAMO_API_URL = 'integrations:vertex:taxamo:taxamo_api_url';

    protected const string CONFIGURATION_KEY_TAXAMO_TOKEN = 'integrations:vertex:taxamo:taxamo_token';

    protected const string CONFIGURATION_KEY_IS_INVOICING_ENABLED = 'integrations:vertex:invoicing:is_invoicing_enabled';

    protected const string CONFIGURATION_KEY_IS_TAX_ASSIST_ENABLED = 'integrations:vertex:tax_assist:is_tax_assist_enabled';

    protected const string CONFIGURATION_KEY_TAX_PROVIDER = 'taxes:tax_provider:provider:tax_provider';

    protected const string TAX_PROVIDER_SPRYKER = 'spryker';

    protected const string TAX_PROVIDER_VERTEX = 'vertex';

    /**
     * Specification:
     * - Returns the OAuth client ID for Vertex API authentication.
     * - Managed via Back Office Configuration under Integrations > Vertex.
     *
     * @api
     */
    public function getClientId(): string
    {
        return (string)$this->getModuleConfig(static::CONFIGURATION_KEY_CLIENT_ID, '');
    }

    /**
     * Specification:
     * - Returns the OAuth client secret for Vertex API authentication.
     * - Managed via Back Office Configuration under Integrations > Vertex.
     *
     * @api
     */
    public function getClientSecret(): string
    {
        return (string)$this->getModuleConfig(static::CONFIGURATION_KEY_CLIENT_SECRET, '');
    }

    /**
     * Specification:
     * - Returns the security URI endpoint for Vertex API OAuth authentication.
     * - Managed via Back Office Configuration under Integrations > Vertex.
     *
     * @api
     */
    public function getSecurityUri(): string
    {
        return (string)$this->getModuleConfig(static::CONFIGURATION_KEY_SECURITY_URI, '');
    }

    /**
     * Specification:
     * - Returns the transaction calls URI endpoint for Vertex API tax calculations.
     * - Managed via Back Office Configuration under Integrations > Vertex.
     *
     * @api
     */
    public function getTransactionCallsUri(): string
    {
        return (string)$this->getModuleConfig(static::CONFIGURATION_KEY_TRANSACTION_CALLS_URI, '');
    }

    /**
     * Specification:
     * - Returns the Taxamo API URL for tax ID validation.
     * - Managed via Back Office Configuration under Integrations > Vertex.
     *
     * @api
     */
    public function getTaxamoApiUrl(): string
    {
        return (string)$this->getModuleConfig(static::CONFIGURATION_KEY_TAXAMO_API_URL, '');
    }

    /**
     * Specification:
     * - Returns the Taxamo API token for authentication.
     * - Managed via Back Office Configuration under Integrations > Vertex.
     *
     * @api
     */
    public function getTaxamoToken(): string
    {
        return (string)$this->getModuleConfig(static::CONFIGURATION_KEY_TAXAMO_TOKEN, '');
    }

    /**
     * Specification:
     * - Returns whether Vertex tax calculation is active.
     * - Managed via Back Office Configuration under Integrations > Vertex.
     * - Returns false by default if not configured.
     *
     * @api
     */
    public function isActive(): bool
    {
        return $this->getTaxProvider() === static::TAX_PROVIDER_VERTEX;
    }

    /**
     * Specification:
     * - Returns whether the tax ID validator feature is enabled.
     * - Managed via Back Office Configuration under Integrations > Vertex.
     * - Returns false by default if not configured.
     *
     * @api
     */
    public function isTaxIdValidatorEnabled(): bool
    {
        return (bool)$this->getModuleConfig(static::CONFIGURATION_KEY_IS_TAX_ID_VALIDATOR_ENABLED, false);
    }

    /**
     * Specification:
     * - Returns whether the tax assist feature is enabled.
     * - Managed via Back Office Configuration under Integrations > Vertex.
     * - Returns false by default if not configured.
     *
     * @api
     */
    public function isTaxAssistEnabled(): bool
    {
        return (bool)$this->getModuleConfig(static::CONFIGURATION_KEY_IS_TAX_ASSIST_ENABLED, false);
    }

    /**
     * Specification:
     * - 2 letters ISO country code, for example US, DE
     * - Overrides the default value (the first country of the store defined in the Quote/Order).
     *
     * @api
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
     */
    public function getCustomerCountryCode(): string
    {
        return '';
    }

    /**
     * Specification:
     * - Returns whether invoicing feature is enabled for Vertex.
     * - Managed via Back Office Configuration under Integrations > Vertex.
     * - Returns false by default if not configured.
     *
     * @api
     */
    public function isInvoicingEnabled(): bool
    {
        return (bool)$this->getModuleConfig(static::CONFIGURATION_KEY_IS_INVOICING_ENABLED, false);
    }

    /**
     * Specification:
     * - Returns the vendor code used for Vertex tax calculations.
     * - Retrieved from configuration using VertexConstants::VENDOR_CODE.
     * - Returns empty string by default if not configured.
     *
     * @api
     */
    public function getVendorCode(): string
    {
        return $this->get(VertexConstants::VENDOR_CODE, '');
    }

    /**
     * Specification:
     * - Returns the default taxpayer company code identifying the organization in Vertex.
     * - Managed via Back Office Configuration under Integrations > Vertex.
     *
     * @api
     */
    public function getDefaultTaxpayerCompanyCode(): string
    {
        return (string)$this->getModuleConfig(static::CONFIGURATION_KEY_DEFAULT_TAXPAYER_COMPANY_CODE, '');
    }

    /**
     * Specification:
     * - Returns the selected tax provider used for tax calculation.
     * - Managed via Back Office Configuration under Taxes > Tax Provider.
     * - Returns "spryker" (default Spryker tax calculation) by default.
     *
     * @api
     */
    public function getTaxProvider(): string
    {
        return (string)$this->getModuleConfig(static::CONFIGURATION_KEY_TAX_PROVIDER, static::TAX_PROVIDER_SPRYKER);
    }
}
