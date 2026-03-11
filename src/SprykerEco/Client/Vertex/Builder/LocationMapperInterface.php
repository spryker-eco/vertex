<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Client\Vertex\Builder;

use Generated\Shared\Transfer\VertexAddressTransfer;
use Generated\Shared\Transfer\VertexLocationTransfer;

interface LocationMapperInterface
{
    public function mapVertexAddressTransferToVertexLocationTransfer(VertexAddressTransfer $vertexAddressTransfer): VertexLocationTransfer;
}
