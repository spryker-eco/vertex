<?php

namespace SprykerEco\Zed\Vertex\Business\Resolver;

use Generated\Shared\Transfer\VertexConfigTransfer;

interface VertexConfigResolverInterface
{
    public function resolve(int $idStore): ?VertexConfigTransfer;
}
