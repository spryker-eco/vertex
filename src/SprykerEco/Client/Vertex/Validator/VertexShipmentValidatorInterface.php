<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Client\Vertex\Validator;

use Generated\Shared\Transfer\VertexShipmentTransfer;
use Generated\Shared\Transfer\VertexValidationResponseTransfer;

interface VertexShipmentValidatorInterface
{
    public function validate(
        VertexShipmentTransfer $vertexShipmentTransfer,
        VertexValidationResponseTransfer $vertexValidationResponseTransfer,
    ): void;
}

