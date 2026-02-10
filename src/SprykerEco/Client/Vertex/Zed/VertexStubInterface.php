<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Client\Vertex\Zed;

use Generated\Shared\Transfer\VertexValidationRequestTransfer;
use Generated\Shared\Transfer\VertexValidationResponseTransfer;

interface VertexStubInterface
{
    public function requestTaxIdValidation(VertexValidationRequestTransfer $vertexValidationRequestTransfer): VertexValidationResponseTransfer;
}
