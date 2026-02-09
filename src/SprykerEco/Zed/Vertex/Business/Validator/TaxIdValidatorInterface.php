<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Vertex\Business\Validator;

use Generated\Shared\Transfer\VertexValidationRequestTransfer;
use Generated\Shared\Transfer\VertexValidationResponseTransfer;

interface TaxIdValidatorInterface
{
    public function validate(VertexValidationRequestTransfer $vertexValidationRequestTransfer): VertexValidationResponseTransfer;
}
