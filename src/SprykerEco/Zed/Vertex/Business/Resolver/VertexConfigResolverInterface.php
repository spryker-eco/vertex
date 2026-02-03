<?php

namespace SprykerEco\Zed\Vertex\Business\Resolver;

use Generated\Shared\Transfer\VertexConfigTransfer;
use Generated\Shared\Transfer\VertexValidationResponseTransfer;

interface VertexConfigResolverInterface
{
    public function resolve(): ?VertexConfigTransfer;
}
