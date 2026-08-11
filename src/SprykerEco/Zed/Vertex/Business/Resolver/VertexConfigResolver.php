<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Zed\Vertex\Business\Resolver;

use Generated\Shared\Transfer\ConfigurationScopeTransfer;
use Generated\Shared\Transfer\VertexConfigTransfer;
use Generated\Shared\Transfer\VertexValidationResponseTransfer;
use InvalidArgumentException;
use Spryker\Shared\Log\LoggerTrait;
use Spryker\Shared\Store\StoreConstants;
use Spryker\Zed\Store\Business\StoreFacadeInterface;
use SprykerEco\Zed\Vertex\Business\Validator\VertexConfigValidatorInterface;
use SprykerEco\Zed\Vertex\VertexConfig;

class VertexConfigResolver implements VertexConfigResolverInterface
{
    use LoggerTrait;

    public function __construct(
        protected VertexConfig $vertexConfig,
        protected StoreFacadeInterface $storeFacade,
        protected VertexConfigValidatorInterface $vertexConfigValidator,
    ) {
    }

    public function resolve(): VertexConfigTransfer
    {
        $configurationScopeTransfers = $this->createConfigurationScopeTransfers();

        $vertexConfigTransfer = (new VertexConfigTransfer())
            ->setClientId($this->vertexConfig->getClientId($configurationScopeTransfers))
            ->setClientSecret($this->vertexConfig->getClientSecret($configurationScopeTransfers))
            ->setSecurityUri($this->vertexConfig->getSecurityUri($configurationScopeTransfers))
            ->setTransactionCallsUri($this->vertexConfig->getTransactionCallsUri($configurationScopeTransfers))
            ->setIsActive($this->vertexConfig->isActive($configurationScopeTransfers))
            ->setIsTaxIdValidatorEnabled($this->vertexConfig->isTaxIdValidatorEnabled())
            ->setIsTaxAssistEnabled($this->vertexConfig->isTaxAssistEnabled($configurationScopeTransfers))
            ->setTaxamoToken($this->vertexConfig->getTaxamoToken($configurationScopeTransfers))
            ->setTaxamoApiUrl($this->vertexConfig->getTaxamoApiUrl($configurationScopeTransfers))
            ->setCredentialHash($this->getCredentialHash($this->vertexConfig->getClientId($configurationScopeTransfers), $this->vertexConfig->getClientSecret($configurationScopeTransfers)))
            ->setIsInvoicingEnabled($this->vertexConfig->isInvoicingEnabled($configurationScopeTransfers))
            ->setVendorCode($this->vertexConfig->getVendorCode($configurationScopeTransfers))
            ->setDefaultTaxpayerCompanyCode($this->vertexConfig->getDefaultTaxpayerCompanyCode($configurationScopeTransfers));

        $vertexValidationResponseTransfer = $this->vertexConfigValidator->validate($vertexConfigTransfer);

        if (!$vertexValidationResponseTransfer->getIsValid()) {
            $message = implode(', ', $vertexValidationResponseTransfer->getMessages()) ?: 'Vertex config is not valid';

            $this->getLogger()->warning($message);

            throw new InvalidArgumentException($message);
        }

        return $vertexConfigTransfer;
    }

    protected function getCredentialHash(string $clientId, string $clientSecret): string
    {
        return md5(hash('sha512', $clientId . $clientSecret));
    }

    /**
     * @return array<\Generated\Shared\Transfer\ConfigurationScopeTransfer>
     */
    protected function createConfigurationScopeTransfers(): array
    {
        if (!$this->storeFacade->isCurrentStoreDefined()) {
            return [];
        }

        return [
            (new ConfigurationScopeTransfer())
                ->setKey(StoreConstants::SCOPE_STORE)
                ->setIdentifier($this->storeFacade->getCurrentStore()->getNameOrFail()),
        ];
    }

    protected function validate(VertexConfigTransfer $vertexConfigTransfer): VertexValidationResponseTransfer
    {
        return $this->vertexConfigValidator->validate($vertexConfigTransfer);
    }
}
