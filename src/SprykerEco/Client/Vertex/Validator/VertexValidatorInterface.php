<?php

namespace SprykerEco\Client\Vertex\Validator;

use Generated\Shared\Transfer\VertexCalculationRequestTransfer;
use Generated\Shared\Transfer\VertexValidationResponseTransfer;

interface VertexValidatorInterface
{
    public function validate(VertexCalculationRequestTransfer $vertexCalculationRequestTransfer): VertexValidationResponseTransfer;
}
