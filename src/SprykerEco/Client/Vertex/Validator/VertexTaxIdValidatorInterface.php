<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Client\Vertex\Validator;

use Generated\Shared\Transfer\TaxIdValidationRequestTransfer;
use Generated\Shared\Transfer\VertexConfigTransfer;
use Generated\Shared\Transfer\VertexValidationResponseTransfer;

interface VertexTaxIdValidatorInterface
{
    /**
     * @param \Generated\Shared\Transfer\TaxIdValidationRequestTransfer $taxIdValidationRequest
     * @param \Generated\Shared\Transfer\VertexConfigTransfer $vertexConfigTransfer
     *
     * @return \Generated\Shared\Transfer\VertexValidationResponseTransfer
     */
    public function validate(
        TaxIdValidationRequestTransfer $taxIdValidationRequest,
        VertexConfigTransfer $vertexConfigTransfer
    ): VertexValidationResponseTransfer;
}
