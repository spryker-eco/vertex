<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Zed\Vertex;

use Spryker\Zed\Kernel\AbstractBundleConfig;
use SprykerEco\Shared\Vertex\VertexConstants;

/**
 * @method \SprykerEco\Shared\Vertex\VertexConfig getSharedConfig()
 */
class VertexConfig extends AbstractBundleConfig
{
    public const MESSAGE_VERTEX_IS_DISABLED = 'Tax service is disabled.';

    /**
     * Specification:
     * - Back Office Configuration key of the Vertex security URI under Integrations > Vertex.
     *
     * @api
     */
    public const string CONFIGURATION_KEY_SECURITY_URI = 'integrations:vertex:configurations:security_uri';

    /**
     * Specification:
     * - Back Office Configuration key of the Vertex transaction calls URI under Integrations > Vertex.
     *
     * @api
     */
    public const string CONFIGURATION_KEY_TRANSACTION_CALLS_URI = 'integrations:vertex:configurations:transaction_calls_uri';

    /**
     * Specification:
     * - Back Office Configuration key of the Vertex OAuth client ID under Integrations > Vertex.
     *
     * @api
     */
    public const string CONFIGURATION_KEY_CLIENT_ID = 'integrations:vertex:configurations:client_id';

    /**
     * Specification:
     * - Back Office Configuration key of the Vertex OAuth client secret under Integrations > Vertex.
     *
     * @api
     */
    public const string CONFIGURATION_KEY_CLIENT_SECRET = 'integrations:vertex:configurations:client_secret';

    /**
     * Specification:
     * - Back Office Configuration key of the default taxpayer company code under Integrations > Vertex.
     *
     * @api
     */
    protected const string CONFIGURATION_KEY_DEFAULT_TAXPAYER_COMPANY_CODE = 'integrations:vertex:configurations:default_taxpayer_company_code';

    /**
     * Specification:
     * - Back Office Configuration key of the Taxamo API URL under Integrations > Vertex.
     *
     * @api
     */
    public const string CONFIGURATION_KEY_TAXAMO_API_URL = 'integrations:vertex:taxamo:taxamo_api_url';

    /**
     * Specification:
     * - Back Office Configuration key of the Taxamo API token under Integrations > Vertex.
     *
     * @api
     */
    public const string CONFIGURATION_KEY_TAXAMO_TOKEN = 'integrations:vertex:taxamo:taxamo_token';

    /**
     * Specification:
     * - Back Office Configuration keys of the Vertex credential fields validated by {@link \SprykerEco\Zed\Vertex\Business\Validator\VertexConfigValidatorInterface}.
     * - Guarded against removal while Vertex is the selected tax provider for the given scope.
     *
     * @api
     *
     * @var array<string>
     */
    public const array VERTEX_CONFIGURATION_CREDENTIAL_KEYS = [
        self::CONFIGURATION_KEY_SECURITY_URI,
        self::CONFIGURATION_KEY_TRANSACTION_CALLS_URI,
        self::CONFIGURATION_KEY_CLIENT_ID,
        self::CONFIGURATION_KEY_CLIENT_SECRET,
        self::CONFIGURATION_KEY_TAXAMO_API_URL,
        self::CONFIGURATION_KEY_TAXAMO_TOKEN,
    ];

    /**
     * Specification:
     * - Back Office Configuration key toggling invoice save in Vertex under Integrations > Vertex.
     *
     * @api
     */
    protected const string CONFIGURATION_KEY_IS_INVOICING_ENABLED = 'integrations:vertex:invoicing:is_invoicing_enabled';

    /**
     * Specification:
     * - Back Office Configuration key toggling Tax Assist in Vertex under Integrations > Vertex.
     *
     * @api
     */
    protected const string CONFIGURATION_KEY_IS_TAX_ASSIST_ENABLED = 'integrations:vertex:tax_assist:is_tax_assist_enabled';

    /**
     * Specification:
     * - Back Office Configuration key of the Vertex vendor code under Integrations > Vertex.
     *
     * @api
     */
    protected const string CONFIGURATION_KEY_VENDOR_CODE = 'integrations:vertex:configurations:vendor_code';

    /**
     * Specification:
     * - Back Office Configuration key of the seller country code under Integrations > Vertex.
     *
     * @api
     */
    protected const string CONFIGURATION_KEY_SELLER_COUNTRY_CODE = 'integrations:vertex:configurations:seller_country_code';

    /**
     * Specification:
     * - Back Office Configuration key of the customer country code under Integrations > Vertex.
     *
     * @api
     */
    protected const string CONFIGURATION_KEY_CUSTOMER_COUNTRY_CODE = 'integrations:vertex:configurations:customer_country_code';

    /**
     * Specification:
     * - Returns the Back Office Configuration keys of the Vertex credential fields guarded during pre-save validation.
     *
     * @api
     *
     * @return array<string>
     */
    public function getVertexConfigurationCredentialKeys(): array
    {
        return static::VERTEX_CONFIGURATION_CREDENTIAL_KEYS;
    }

    /**
     * Resolves a configuration value from either the environment configuration or the Back Office Configuration module,
     * depending on the {@link \SprykerEco\Shared\Vertex\VertexConfig::isConfigurationModuleUsed()} flag.
     *
     * @param array<\Generated\Shared\Transfer\ConfigurationScopeTransfer> $configurationScopeTransfers
     */
    protected function resolveConfigurationValue(
        string $configurationKey,
        string $environmentConfigurationKey,
        mixed $default,
        array $configurationScopeTransfers,
    ): mixed {
        if (!$this->getSharedConfig()->isConfigurationModuleUsed()) {
            return $this->get($environmentConfigurationKey, $default);
        }

        return $this->getModuleConfig($configurationKey, $default, $configurationScopeTransfers);
    }

    /**
     * Resolves a Back Office Configuration module-only value that has no environment configuration fallback,
     * depending on the {@link \SprykerEco\Shared\Vertex\VertexConfig::isConfigurationModuleUsed()} flag.
     *
     * @param array<\Generated\Shared\Transfer\ConfigurationScopeTransfer> $configurationScopeTransfers
     */
    protected function resolveModuleConfigurationValue(
        string $configurationKey,
        mixed $default,
        array $configurationScopeTransfers,
    ): mixed {
        if (!$this->getSharedConfig()->isConfigurationModuleUsed()) {
            return $default;
        }

        return $this->getModuleConfig($configurationKey, $default, $configurationScopeTransfers);
    }

    /**
     * Specification:
     * - Returns the OAuth client ID for Vertex API authentication.
     * - Managed via Back Office Configuration under Integrations > Vertex.
     * - Retrieved from configuration using VertexConstants::CLIENT_ID.
     * - Returns empty string if not configured.
     *
     * @api
     *
     * @param array<\Generated\Shared\Transfer\ConfigurationScopeTransfer> $configurationScopeTransfers
     */
    public function getClientId(array $configurationScopeTransfers = []): string
    {
        return (string)$this->resolveConfigurationValue(
            static::CONFIGURATION_KEY_CLIENT_ID,
            VertexConstants::CLIENT_ID,
            '',
            $configurationScopeTransfers,
        );
    }

    /**
     * Specification:
     * - Returns the OAuth client secret for Vertex API authentication.
     * - Managed via Back Office Configuration under Integrations > Vertex.
     * - Retrieved from configuration using VertexConstants::CLIENT_SECRET.
     * - Returns empty string if not configured.
     *
     * @api
     *
     * @param array<\Generated\Shared\Transfer\ConfigurationScopeTransfer> $configurationScopeTransfers
     */
    public function getClientSecret(array $configurationScopeTransfers = []): string
    {
        return (string)$this->resolveConfigurationValue(
            static::CONFIGURATION_KEY_CLIENT_SECRET,
            VertexConstants::CLIENT_SECRET,
            '',
            $configurationScopeTransfers,
        );
    }

    /**
     * Specification:
     * - Returns the security URI endpoint for Vertex API OAuth authentication.
     * - Managed via Back Office Configuration under Integrations > Vertex.
     * - Retrieved from configuration using VertexConstants::SECURITY_URI.
     * - Returns empty string if not configured.
     *
     * @api
     *
     * @param array<\Generated\Shared\Transfer\ConfigurationScopeTransfer> $configurationScopeTransfers
     */
    public function getSecurityUri(array $configurationScopeTransfers = []): string
    {
        return (string)$this->resolveConfigurationValue(
            static::CONFIGURATION_KEY_SECURITY_URI,
            VertexConstants::SECURITY_URI,
            '',
            $configurationScopeTransfers,
        );
    }

    /**
     * Specification:
     * - Returns the transaction calls URI endpoint for Vertex API tax calculations.
     * - Managed via Back Office Configuration under Integrations > Vertex.
     * - Retrieved from configuration using VertexConstants::TRANSACTION_CALLS_URI.
     * - Returns empty string if not configured.
     *
     * @api
     *
     * @param array<\Generated\Shared\Transfer\ConfigurationScopeTransfer> $configurationScopeTransfers
     */
    public function getTransactionCallsUri(array $configurationScopeTransfers = []): string
    {
        return (string)$this->resolveConfigurationValue(
            static::CONFIGURATION_KEY_TRANSACTION_CALLS_URI,
            VertexConstants::TRANSACTION_CALLS_URI,
            '',
            $configurationScopeTransfers,
        );
    }

    /**
     * Specification:
     * - Returns the Taxamo API URL for tax ID validation.
     * - Managed via Back Office Configuration under Integrations > Vertex.
     * - Retrieved from configuration using VertexConstants::TAXAMO_API_URL.
     * - Returns empty string if not configured.
     *
     * @api
     *
     * @param array<\Generated\Shared\Transfer\ConfigurationScopeTransfer> $configurationScopeTransfers
     */
    public function getTaxamoApiUrl(array $configurationScopeTransfers = []): string
    {
        return (string)$this->resolveConfigurationValue(
            static::CONFIGURATION_KEY_TAXAMO_API_URL,
            VertexConstants::TAXAMO_API_URL,
            '',
            $configurationScopeTransfers,
        );
    }

    /**
     * Specification:
     * - Returns the Taxamo API token for authentication.
     * - Managed via Back Office Configuration under Integrations > Vertex.
     * - Retrieved from configuration using VertexConstants::TAXAMO_TOKEN.
     * - Returns empty string if not configured.
     *
     * @api
     *
     * @param array<\Generated\Shared\Transfer\ConfigurationScopeTransfer> $configurationScopeTransfers
     */
    public function getTaxamoToken(array $configurationScopeTransfers = []): string
    {
        return (string)$this->resolveConfigurationValue(
            static::CONFIGURATION_KEY_TAXAMO_TOKEN,
            VertexConstants::TAXAMO_TOKEN,
            '',
            $configurationScopeTransfers,
        );
    }

    /**
     * Specification:
     * - Returns whether Vertex tax calculation is active.
     * - When the Configuration module is used (see {@link \SprykerEco\Shared\Vertex\VertexConfig::isConfigurationModuleUsed()}), active means Vertex is selected as the tax provider in Back Office Configuration under Taxes > Tax Provider for the given scope.
     * - Otherwise it is retrieved from environment configuration using VertexConstants::IS_ACTIVE.
     * - Returns false by default if not configured.
     *
     * @api
     *
     * @param array<\Generated\Shared\Transfer\ConfigurationScopeTransfer> $configurationScopeTransfers
     */
    public function isActive(array $configurationScopeTransfers = []): bool
    {
        if (!$this->getSharedConfig()->isConfigurationModuleUsed()) {
            return (bool)$this->get(VertexConstants::IS_ACTIVE, false);
        }

        return $this->getTaxProvider($configurationScopeTransfers) === $this->getSharedConfig()::TAX_PROVIDER_VERTEX;
    }

    /**
     * Specification:
     * - Returns whether the tax ID validator feature is enabled.
     * - Returns false by default if not configured.
     *
     * @api
     */
    public function isTaxIdValidatorEnabled(): bool
    {
        return false;
    }

    /**
     * Specification:
     * - Returns whether the tax assist feature is enabled.
     * - Managed via Back Office Configuration under Integrations > Vertex.
     * - Returns false by default if not configured.
     *
     * @api
     *
     * @param array<\Generated\Shared\Transfer\ConfigurationScopeTransfer> $configurationScopeTransfers
     */
    public function isTaxAssistEnabled(array $configurationScopeTransfers = []): bool
    {
        return (bool)$this->resolveModuleConfigurationValue(static::CONFIGURATION_KEY_IS_TAX_ASSIST_ENABLED, false, $configurationScopeTransfers);
    }

    /**
     * Specification:
     * - 2 letters ISO country code, for example US, DE
     * - Managed via Back Office Configuration under Integrations > Vertex.
     * - Overrides the default value (the first country of the store defined in the Quote/Order).
     * - Returns empty string if not configured.
     *
     * @api
     *
     * @param array<\Generated\Shared\Transfer\ConfigurationScopeTransfer> $configurationScopeTransfers
     */
    public function getSellerCountryCode(array $configurationScopeTransfers = []): string
    {
        return (string)$this->resolveModuleConfigurationValue(static::CONFIGURATION_KEY_SELLER_COUNTRY_CODE, '', $configurationScopeTransfers);
    }

    /**
     * Specification:
     * - 2 letters ISO country code, for example US, DE
     * - Used for tax calculation when a customer did not provide shipping address.
     * - Managed via Back Office Configuration under Integrations > Vertex.
     * - Overrides the default value (the first country of the store defined in the Quote/Order).
     * - Returns empty string if not configured.
     *
     * @api
     *
     * @param array<\Generated\Shared\Transfer\ConfigurationScopeTransfer> $configurationScopeTransfers
     */
    public function getCustomerCountryCode(array $configurationScopeTransfers = []): string
    {
        return (string)$this->resolveModuleConfigurationValue(static::CONFIGURATION_KEY_CUSTOMER_COUNTRY_CODE, '', $configurationScopeTransfers);
    }

    /**
     * Specification:
     * - Returns whether invoicing feature is enabled for Vertex.
     * - Managed via Back Office Configuration under Integrations > Vertex.
     * - Returns false by default if not configured.
     *
     * @api
     *
     * @param array<\Generated\Shared\Transfer\ConfigurationScopeTransfer> $configurationScopeTransfers
     */
    public function isInvoicingEnabled(array $configurationScopeTransfers = []): bool
    {
        return (bool)$this->resolveModuleConfigurationValue(static::CONFIGURATION_KEY_IS_INVOICING_ENABLED, false, $configurationScopeTransfers);
    }

    /**
     * Specification:
     * - Returns the vendor code used for Vertex tax calculations.
     * - Managed via Back Office Configuration under Integrations > Vertex.
     * - Retrieved from configuration using VertexConstants::VENDOR_CODE.
     * - Returns empty string by default if not configured.
     *
     * @api
     *
     * @param array<\Generated\Shared\Transfer\ConfigurationScopeTransfer> $configurationScopeTransfers
     */
    public function getVendorCode(array $configurationScopeTransfers = []): string
    {
        return (string)$this->resolveConfigurationValue(
            static::CONFIGURATION_KEY_VENDOR_CODE,
            VertexConstants::VENDOR_CODE,
            '',
            $configurationScopeTransfers,
        );
    }

    /**
     * Specification:
     * - Returns the default taxpayer company code identifying the organization in Vertex.
     * - Managed via Back Office Configuration under Integrations > Vertex.
     * - Retrieved from configuration using VertexConstants::DEFAULT_TAXPAYER_COMPANY_CODE.
     * - Returns empty string if not configured.
     *
     * @api
     *
     * @param array<\Generated\Shared\Transfer\ConfigurationScopeTransfer> $configurationScopeTransfers
     */
    public function getDefaultTaxpayerCompanyCode(array $configurationScopeTransfers = []): string
    {
        return (string)$this->resolveConfigurationValue(
            static::CONFIGURATION_KEY_DEFAULT_TAXPAYER_COMPANY_CODE,
            VertexConstants::DEFAULT_TAXPAYER_COMPANY_CODE,
            '',
            $configurationScopeTransfers,
        );
    }

    /**
     * Specification:
     * - Returns the selected tax provider used for tax calculation.
     * - Managed via Back Office Configuration under Taxes > Tax Provider.
     * - Returns "spryker" (default Spryker tax calculation) by default.
     *
     * @api
     *
     * @param array<\Generated\Shared\Transfer\ConfigurationScopeTransfer> $configurationScopeTransfers
     */
    public function getTaxProvider(array $configurationScopeTransfers = []): string
    {
        return (string)$this->getModuleConfig(
            $this->getSharedConfig()::CONFIGURATION_KEY_TAX_PROVIDER,
            $this->getSharedConfig()::TAX_PROVIDER_SPRYKER,
            $configurationScopeTransfers,
        );
    }
}
