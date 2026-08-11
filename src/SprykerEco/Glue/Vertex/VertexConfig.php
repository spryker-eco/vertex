<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Glue\Vertex;

use Spryker\Glue\Kernel\AbstractBundleConfig;

/**
 * @method \SprykerEco\Shared\Vertex\VertexConfig getSharedConfig()
 */
class VertexConfig extends AbstractBundleConfig
{
    /**
     * Specification:
     * - Resource type identifier for the tax ID validation REST API endpoint.
     *
     * @api
     *
     * @var string
     */
    public const RESOURCE_TAX_VALIATE_ID = 'tax-id-validate';

    /**
     * Specification:
     * - Controller identifier for handling tax ID validation requests.
     *
     * @api
     *
     * @var string
     */
    public const CONTROLLER_TAX_VALIATE_ID = 'tax-id-validation';

    /**
     * Specification:
     * - Default error message returned when request data is invalid.
     *
     * @api
     *
     * @var string
     */
    public const RESPONSE_DETAIL_MESSAGE_INVALID_REQUEST_DATA = 'Invalid request data.';

    /**
     * Specification:
     * - Returns whether Vertex tax calculation is active.
     * - Active when the Vertex tax provider is selected in Back Office Configuration under Taxes > Tax Provider.
     * - Returns false by default if not configured.
     *
     * @api
     */
    public function getIsActive(): bool
    {
        $vertexSharedConfig = $this->getSharedConfig();

        return $this->getModuleConfig(
            $vertexSharedConfig::CONFIGURATION_KEY_TAX_PROVIDER,
            $vertexSharedConfig::TAX_PROVIDER_SPRYKER,
        ) === $vertexSharedConfig::TAX_PROVIDER_VERTEX
            || $this->getSharedConfig()->getIsActive();
    }
}
