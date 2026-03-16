<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Client\Vertex\Validator;

use Generated\Shared\Transfer\VertexSaleTransfer;
use Generated\Shared\Transfer\VertexValidationResponseTransfer;

interface VertexSaleValidatorInterface
{
    public function validate(
        VertexSaleTransfer $vertexSaleTransfer,
        VertexValidationResponseTransfer $vertexValidationResponseTransfer,
    ): VertexValidationResponseTransfer;
}
