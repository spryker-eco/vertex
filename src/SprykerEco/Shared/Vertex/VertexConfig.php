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
     *
     * @var string
     */
    public const string CONFIGURATION_KEY_TAX_PROVIDER = 'taxes:tax_provider:provider:tax_provider';

    /**
     * Specification:
     * - Tax provider value for the default Spryker tax calculation.
     *
     * @api
     *
     * @var string
     */
    public const string TAX_PROVIDER_SPRYKER = 'spryker';

    /**
     * Specification:
     * - Tax provider value for the Vertex tax calculation.
     *
     * @api
     *
     * @var string
     */
    public const string TAX_PROVIDER_VERTEX = 'vertex';

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
        return (bool)$this->get(VertexConstants::IS_ACTIVE, false);
    }
}
