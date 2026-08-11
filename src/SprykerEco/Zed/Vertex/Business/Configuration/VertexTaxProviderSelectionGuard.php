<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Zed\Vertex\Business\Configuration;

use Generated\Shared\Transfer\ConfigurationValueCollectionRequestTransfer;
use Generated\Shared\Transfer\ConfigurationValueTransfer;
use Generated\Shared\Transfer\VertexValidationResponseTransfer;
use SprykerEco\Shared\Vertex\VertexConfig as SharedVertexConfig;
use SprykerEco\Zed\Vertex\Business\Validator\VertexConfigValidatorInterface;

class VertexTaxProviderSelectionGuard implements VertexTaxProviderSelectionGuardInterface
{
    public function __construct(
        protected VertexConfigValidatorInterface $vertexConfigValidator,
        protected VertexConfigTransferBuilderInterface $vertexConfigTransferBuilder,
        protected VertexSentinelEncoderInterface $vertexSentinelEncoder,
    ) {
    }

    public function guard(
        ConfigurationValueCollectionRequestTransfer $configurationValueCollectionRequestTransfer,
    ): void {
        foreach ($configurationValueCollectionRequestTransfer->getConfigurationValues() as $configurationValueTransfer) {
            if (!$this->isVertexTaxProviderSelected($configurationValueTransfer)) {
                continue;
            }

            $vertexValidationResponseTransfer = $this->validateVertexSelectionConfiguration($configurationValueTransfer);

            if ($vertexValidationResponseTransfer->getIsValid()) {
                continue;
            }

            $configurationValueTransfer->setValue(
                $this->vertexSentinelEncoder->encodeTaxProviderNotConfiguredSentinel($vertexValidationResponseTransfer->getMessages()),
            );
        }
    }

    protected function isVertexTaxProviderSelected(ConfigurationValueTransfer $configurationValueTransfer): bool
    {
        return $configurationValueTransfer->getSettingKey() === SharedVertexConfig::CONFIGURATION_KEY_TAX_PROVIDER
            && $configurationValueTransfer->getValue() === SharedVertexConfig::TAX_PROVIDER_VERTEX;
    }

    protected function validateVertexSelectionConfiguration(
        ConfigurationValueTransfer $configurationValueTransfer,
    ): VertexValidationResponseTransfer {
        $configurationScopeTransfers = $this->vertexConfigTransferBuilder->createConfigurationScopeTransfersFromScope(
            $configurationValueTransfer->getScope(),
            $configurationValueTransfer->getScopeIdentifier(),
        );

        return $this->vertexConfigValidator->validate($this->vertexConfigTransferBuilder->build($configurationScopeTransfers));
    }
}
