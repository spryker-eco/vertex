<?php

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerEco\Client\Vertex\Validator;

use Generated\Shared\Transfer\TaxIdValidationRequestTransfer;
use Generated\Shared\Transfer\VertexValidationResponseTransfer;
use Generated\Shared\Transfer\VertexConfigTransfer;

interface VertexTaxIdValidatorInterface
{
    /**
     * @param \Generated\Shared\Transfer\TaxIdValidationRequestTransfer $taxIdValidationRequest
     * @param \Generated\Shared\Transfer\VertexConfigTransfer $vertexConfigTransfer
     *
     * @return \Generated\Shared\Transfer\VertexValidationResponseTransfer
     */
    public function validate(TaxIdValidationRequestTransfer $taxIdValidationRequest, VertexConfigTransfer $vertexConfigTransfer): VertexValidationResponseTransfer;
}
