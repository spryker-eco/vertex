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
}
