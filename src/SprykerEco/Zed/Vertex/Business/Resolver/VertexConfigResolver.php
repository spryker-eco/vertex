<?php

namespace SprykerEco\Zed\Vertex\Business\Resolver;

use Generated\Shared\Transfer\VertexConfigTransfer;
use Generated\Shared\Transfer\VertexValidationResponseTransfer;
use SprykerEco\Zed\Vertex\Business\Validator\VertexConfigValidator;
use SprykerEco\Zed\Vertex\Dependency\Facade\VertexToStoreFacadeInterface;
use SprykerEco\Zed\Vertex\VertexConfig;

class VertexConfigResolver implements VertexConfigResolverInterface
{
    public function __construct(
        protected VertexConfig $vertexConfig,
        protected VertexToStoreFacadeInterface $storeFacade,
        protected VertexConfigValidator $vertexConfigValidator
    ) {
    }

    public function resolve(): ?VertexConfigTransfer
    {
        $vertexValidationResponseTransfer = $this->vertexConfigValidator->validate();

        if (!$vertexValidationResponseTransfer->getIsSuccess()) {


            return null;
        }

        return (new VertexConfigTransfer())
            ->setClientId($this->vertexConfig->getClientId())
            ->setClientSecret($this->vertexConfig->getClientSecret())
            ->setSecurityUri($this->vertexConfig->getSecurityUri())
            ->setTransactionCallsUri($this->vertexConfig->getTransactionCallsUri())
            ->setIsActive($this->vertexConfig->isActive())
            ->setIsTaxIdValidatorEnabled($this->vertexConfig->isTaxIdValidatorEnabled())
            ->setIsTaxAssistEnabled($this->vertexConfig->isTaxIdValidatorEnabled())
            ->setTaxamoToken($this->vertexConfig->getTaxamoToken())
            ->setTaxamoApiUrl($this->vertexConfig->getTaxamoApiUrl())
            ->setCredentialHash($this->getCredentialHash($this->vertexConfig->getClientId(), $this->vertexConfig->getClientSecret()))
            ->setIsInvoicingEnabled($this->vertexConfig->isInvoicingEnabled());
    }

    protected function getCredentialHash(string $clientId, string $clientSecret): string
    {
        return md5(hash('sha512', $clientId . $clientSecret));
    }

    protected function validate(VertexConfigTransfer $vertexConfigTransfer): VertexValidationResponseTransfer
    {
        return $this->vertexConfigValidator->validate($vertexConfigTransfer);
    }
}
