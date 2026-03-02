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

    /**
     * Specification:
     * - Returns the OAuth client ID for Vertex API authentication.
     * - Retrieved from configuration using VertexConstants::CLIENT_ID.
     * - Returns empty string if not configured.
     *
     * @api
     *
     * @return string
     */
    public function getClientId(): string
    {
        return $this->get(VertexConstants::CLIENT_ID, '');
    }

    /**
     * Specification:
     * - Returns the OAuth client secret for Vertex API authentication.
     * - Retrieved from configuration using VertexConstants::CLIENT_SECRET.
     * - Returns empty string if not configured.
     *
     * @api
     *
     * @return string
     */
    public function getClientSecret(): string
    {
        return $this->get(VertexConstants::CLIENT_SECRET, '');
    }

    /**
     * Specification:
     * - Returns the security URI endpoint for Vertex API OAuth authentication.
     * - Retrieved from configuration using VertexConstants::SECURITY_URI.
     * - Returns empty string if not configured.
     *
     * @api
     *
     * @return string
     */
    public function getSecurityUri(): string
    {
        return $this->get(VertexConstants::SECURITY_URI, '');
    }

    /**
     * Specification:
     * - Returns the transaction calls URI endpoint for Vertex API tax calculations.
     * - Retrieved from configuration using VertexConstants::TRANSACTION_CALLS_URI.
     * - Returns empty string if not configured.
     *
     * @api
     *
     * @return string
     */
    public function getTransactionCallsUri(): string
    {
        return $this->get(VertexConstants::TRANSACTION_CALLS_URI, '');
    }

    /**
     * Specification:
     * - Returns the Taxamo API URL for tax ID validation.
     * - Retrieved from configuration using VertexConstants::TAXAMO_API_URL.
     * - Returns empty string if not configured.
     *
     * @api
     *
     * @return string
     */
    public function getTaxamoApiUrl(): string
    {
        return $this->get(VertexConstants::TAXAMO_API_URL, '');
    }

    /**
     * Specification:
     * - Returns the Taxamo API token for authentication.
     * - Retrieved from configuration using VertexConstants::TAXAMO_TOKEN.
     * - Returns empty string if not configured.
     *
     * @api
     *
     * @return string
     */
    public function getTaxamoToken(): string
    {
        return $this->get(VertexConstants::TAXAMO_TOKEN, '');
    }

    /**
     * Specification:
     * - Returns whether Vertex tax calculation is active.
     * - Retrieved from configuration using VertexConstants::IS_ACTIVE.
     * - Returns false by default if not configured.
     *
     * @api
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->get(VertexConstants::IS_ACTIVE, false);
    }

    /**
     * Specification:
     * - Returns whether the tax ID validator feature is enabled.
     * - Retrieved from configuration using VertexConstants::IS_TAX_ID_VALIDATOR_ENABLED.
     * - Returns false by default if not configured.
     *
     * @api
     *
     * @return bool
     */
    public function isTaxIdValidatorEnabled(): bool
    {
        return $this->get(VertexConstants::IS_TAX_ID_VALIDATOR_ENABLED, false);
    }

    /**
     * Specification:
     * - Returns whether the tax assist feature is enabled.
     * - Retrieved from configuration using VertexConstants::IS_TAX_ASSIST_ENABLED.
     * - Returns false by default if not configured.
     *
     * @api
     *
     * @return bool
     */
    public function isTaxAssistEnabled(): bool
    {
        return $this->get(VertexConstants::IS_TAX_ASSIST_ENABLED, false);
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

    /**
     * Specification:
     * - Returns whether invoicing feature is enabled for Vertex.
     * - Retrieved from configuration using VertexConstants::IS_INVOICING_ENABLED.
     * - Returns false by default if not configured.
     *
     * @api
     *
     * @return bool
     */
    public function isInvoicingEnabled(): bool
    {
        return $this->get(VertexConstants::IS_INVOICING_ENABLED, false);
    }

    /**
     * Specification:
     * - Returns the vendor code used for Vertex tax calculations.
     * - Retrieved from configuration using VertexConstants::VENDOR_CODE.
     * - Returns empty string by default if not configured.
     *
     * @api
     *
     * @return string
     */
    public function getVendorCode(): string
    {
        return $this->get(VertexConstants::VENDOR_CODE, '');
    }

    public function getDefaultTaxpayerCompanyCode(): string
    {
        return $this->get(VertexConstants::DEFAULT_TAXPAYER_COMPANY_CODE, '');
    }
}
