<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Zed\Vertex\Business\Resolver;

use Generated\Shared\Transfer\VertexConfigTransfer;
use Generated\Shared\Transfer\VertexValidationResponseTransfer;
use InvalidArgumentException;
use Spryker\Shared\Log\LoggerTrait;
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
        $vertexConfigTransfer = (new VertexConfigTransfer())
            ->setClientId($this->vertexConfig->getClientId())
            ->setClientSecret($this->vertexConfig->getClientSecret())
            ->setSecurityUri($this->vertexConfig->getSecurityUri())
            ->setTransactionCallsUri($this->vertexConfig->getTransactionCallsUri())
            ->setIsActive($this->vertexConfig->isActive())
            ->setIsTaxIdValidatorEnabled($this->vertexConfig->isTaxIdValidatorEnabled())
            ->setIsTaxAssistEnabled($this->vertexConfig->isTaxAssistEnabled())
            ->setTaxamoToken($this->vertexConfig->getTaxamoToken())
            ->setTaxamoApiUrl($this->vertexConfig->getTaxamoApiUrl())
            ->setCredentialHash($this->getCredentialHash($this->vertexConfig->getClientId(), $this->vertexConfig->getClientSecret()))
            ->setIsInvoicingEnabled($this->vertexConfig->isInvoicingEnabled())
            ->setVendorCode($this->vertexConfig->getVendorCode())
            ->setDefaultTaxpayerCompanyCode($this->vertexConfig->getDefaultTaxpayerCompanyCode());

        $vertexValidationResponseTransfer = $this->vertexConfigValidator->validate($vertexConfigTransfer);

        if (!$vertexValidationResponseTransfer->getIsValid()) {
            $this->getLogger()->warning(
                $vertexValidationResponseTransfer->getMessage() ?: 'Vertex config is not valid',
            );

            throw new InvalidArgumentException($vertexValidationResponseTransfer->getMessage() ?: 'Vertex config is not valid');
        }

        return $vertexConfigTransfer;
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
