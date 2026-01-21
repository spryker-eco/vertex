<?php

namespace SprykerEco\Zed\Vertex\Business\Resolver;

use Generated\Shared\Transfer\VertexConfigTransfer;

class VertexConfigResolver implements VertexConfigResolverInterface
{
    public function resolve(int $idStore): VertexConfigTransfer
    {
        return new VertexConfigTransfer();
    }
}
