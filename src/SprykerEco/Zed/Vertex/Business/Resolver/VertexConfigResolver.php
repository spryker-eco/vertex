<?php

namespace SprykerEco\Zed\Vertex\Business\Resolver;

use Generated\Shared\Transfer\VertexConfigTransfer;
use SprykerEco\Zed\Vertex\VertexConfig;

class VertexConfigResolver implements VertexConfigResolverInterface
{
    public function __construct(protected VertexConfig $vertexConfig)
    {
    }

    public function resolve(?int $idStore = null): VertexConfigTransfer // TODO: is idStore needed?
    {
        $clientId = $this->vertexConfig->getClientId();
        $clientSecret = $this->vertexConfig->getClientSecret();
        $securityUri = $this->vertexConfig->getSecurityUri();
        $transactionCallsUri = $this->vertexConfig->getTransactionCallsUri();

        if (!$this->vertexConfig->isActive()) {
            return null;
        }

        return (new VertexConfigTransfer())
            ->setClientId($clientId)
            ->setClientSecret($clientSecret)
            ->setSecurityUri($securityUri)
            ->setTransactionCallsUri($transactionCallsUri)
            ->setIsActive($this->vertexConfig->isActive());
    }
}
