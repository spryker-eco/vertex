<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Shared\Vertex;

use Spryker\Shared\Kernel\AbstractSharedConfig;

class VertexConfig extends AbstractSharedConfig
{
    /**
     * Specification:
     * - Configuration key of the selected tax provider in the Back Office Configuration under Taxes > Tax Provider.
     *
     * @api
     */
    public const string CONFIGURATION_KEY_TAX_PROVIDER = 'taxes:tax_provider:provider:tax_provider';

    /**
     * Specification:
     * - Tax provider value for the default Spryker tax calculation.
     *
     * @api
     */
    public const string TAX_PROVIDER_SPRYKER = 'spryker';

    /**
     * Specification:
     * - Tax provider value for the Vertex tax calculation.
     *
     * @api
     */
    public const string TAX_PROVIDER_VERTEX = 'vertex';

    /**
     * Specification:
     * - Internal marker assigned to the tax provider value during pre-save when Vertex is selected but not configured for the given scope.
     * - Detected by the tax provider constraint to produce a validation error.
     *
     * @api
     */
    public const string TAX_PROVIDER_NOT_CONFIGURED_SENTINEL = '__VERTEX_TAX_PROVIDER_NOT_CONFIGURED__';

    /**
     * Specification:
     * - Internal marker assigned to a Vertex configuration value during pre-save when the value would leave the Vertex configuration incomplete while Vertex is still the selected tax provider for the given scope.
     * - Detected by the Vertex configuration removal constraint to produce a validation error.
     *
     * @api
     */
    public const string VERTEX_CONFIGURATION_INCOMPLETE_SENTINEL = '__VERTEX_CONFIGURATION_INCOMPLETE__';

    /**
     * Specification:
     * - Payload case marking a configuration that is incomplete for the edited scope while Vertex is selected there.
     * - Encoded into the incomplete sentinel during pre-save and read by the removal constraint to choose the message.
     *
     * @api
     */
    public const string VERTEX_CONFIGURATION_INCOMPLETE_CASE_REMOVAL = 'removal';

    /**
     * Specification:
     * - Payload case marking a global change that would break a store inheriting the global Vertex configuration.
     * - Encoded into the incomplete sentinel during pre-save and read by the removal constraint to choose the message.
     *
     * @api
     */
    public const string VERTEX_CONFIGURATION_INCOMPLETE_CASE_CROSS_SCOPE = 'cross_scope';

    /**
     * Specification:
     * - Payload key holding the case of the incomplete sentinel.
     *
     * @api
     */
    public const string VERTEX_CONFIGURATION_INCOMPLETE_PAYLOAD_KEY_CASE = 'case';

    /**
     * Specification:
     * - Payload key holding the human-readable scope label of the incomplete sentinel.
     *
     * @api
     */
    public const string VERTEX_CONFIGURATION_INCOMPLETE_PAYLOAD_KEY_SCOPE = 'scope';

    /**
     * Specification:
     * - Payload key holding the list of concrete validation reasons of the incomplete sentinel.
     *
     * @api
     */
    public const string VERTEX_CONFIGURATION_INCOMPLETE_PAYLOAD_KEY_REASONS = 'reasons';

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
    public function getIsActive(): bool
    {
        return $this->get(VertexConstants::IS_ACTIVE, false);
    }

    /**
     * Specification:
     * - Returns whether the Back Office Configuration module is used as the source of Vertex configuration.
     * - When enabled, configuration values are retrieved from the Configuration module (Back Office).
     * - When disabled, configuration values are retrieved from static environment configuration (VertexConstants).
     *
     * @api
     */
    public function isConfigurationModuleUsed(): bool
    {
        return false;
    }
}
