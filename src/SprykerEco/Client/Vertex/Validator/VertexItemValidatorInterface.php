<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Client\Vertex\Validator;

use Generated\Shared\Transfer\VertexItemTransfer;
use Generated\Shared\Transfer\VertexValidationResponseTransfer;

interface VertexItemValidatorInterface
{
    public function validate(
        VertexItemTransfer $vertexItemTransfer,
        VertexValidationResponseTransfer $vertexValidationResponseTransfer,
    ): void;
}
