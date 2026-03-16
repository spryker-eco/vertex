<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Client\Vertex\Api\V2\Builder;

use Generated\Shared\Transfer\VertexSuppliesTransfer;

interface VertexSuppliesApiRequestBuilderInterface
{
    /**
     * @param \Generated\Shared\Transfer\VertexSuppliesTransfer $vertexSuppliesTransfer
     *
     * @return array<string, mixed>
     */
    public function buildVertexSuppliesRequest(VertexSuppliesTransfer $vertexSuppliesTransfer): array;
}
