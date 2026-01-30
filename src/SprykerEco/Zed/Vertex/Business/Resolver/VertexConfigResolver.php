<?php

namespace SprykerEco\Zed\Vertex\Business\Resolver;

use Generated\Shared\Transfer\VertexConfigTransfer;
use SprykerEco\Zed\Vertex\Dependency\Facade\VertexToStoreFacadeInterface;
use SprykerEco\Zed\Vertex\VertexConfig;

class VertexConfigResolver implements VertexConfigResolverInterface
{
    public function __construct(protected VertexConfig $vertexConfig, protected VertexToStoreFacadeInterface $storeFacade)
    {
    }

    public function resolve(?int $idStore = null): VertexConfigTransfer
    {
        $clientId = $this->vertexConfig->getClientId();
        $clientSecret = $this->vertexConfig->getClientSecret();
        $securityUri = $this->vertexConfig->getSecurityUri();
        $transactionCallsUri = $this->vertexConfig->getTransactionCallsUri();

        if (!$idStore) {
            $idStore = (int)$this->storeFacade->getCurrentStore()->getIdStore();
        }

        return (new VertexConfigTransfer())
            ->setClientId($clientId)
            ->setClientSecret($clientSecret)
            ->setSecurityUri($securityUri)
            ->setTransactionCallsUri($transactionCallsUri)
            ->setIsActive($this->vertexConfig->isActive())
            ->setIsTaxIdValidatorEnabled($this->vertexConfig->isTaxIdValidatorEnabled())
            ->setIsTaxAssistEnabled($this->vertexConfig->isTaxIdValidatorEnabled())
            ->setTaxamoToken($this->vertexConfig->getTaxamoToken())
            ->setTaxamoApiUrl($this->vertexConfig->getTaxamoApiUrl())
            ->setCredentialHash($this->getCredentialHash($clientId, $clientSecret))
            ->setIsInvoicingEnabled($this->vertexConfig->isInvoicingEnabled());
    }

    public function getCredentialHash(string $clientId, string $clientSecret): string
    {
        return md5(hash('sha512', $clientId . $clientSecret));
    }
}
