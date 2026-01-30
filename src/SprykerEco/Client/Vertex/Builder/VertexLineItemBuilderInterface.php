<?php

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerEco\Client\Vertex\Builder;

use Generated\Shared\Transfer\ShipmentTransfer;
use Generated\Shared\Transfer\VertexItemTransfer;
use Generated\Shared\Transfer\VertexLineItemTransfer;

interface VertexLineItemBuilderInterface
{
    /**
     * @param \Generated\Shared\Transfer\VertexItemTransfer|\Generated\Shared\Transfer\ShipmentTransfer $itemTransfer
     * @param \Generated\Shared\Transfer\VertexLineItemTransfer $vertexLineItemTransfer
     *
     * @return \Generated\Shared\Transfer\VertexLineItemTransfer
     */
    public function build(VertexItemTransfer|ShipmentTransfer $itemTransfer, VertexLineItemTransfer $vertexLineItemTransfer): VertexLineItemTransfer;
}
