<?php

namespace SprykerEco\Client\Vertex\Validator;

use Generated\Shared\Transfer\VertexCalculationRequestTransfer;
use Generated\Shared\Transfer\VertexSaleTransfer;
use Generated\Shared\Transfer\VertexValidationResponseTransfer;

interface VertexInvoiceValidatorInterface
{
    public function validate(VertexCalculationRequestTransfer $vertexCalculationRequestTransfer): VertexValidationResponseTransfer;
}

