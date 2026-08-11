<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Zed\Vertex\Business\Configuration;

use Generated\Shared\Transfer\ConfigurationScopeTransfer;
use Generated\Shared\Transfer\VertexConfigTransfer;
use Spryker\Shared\Store\StoreConstants;
use SprykerEco\Zed\Vertex\VertexConfig;

class VertexConfigTransferBuilder implements VertexConfigTransferBuilderInterface
{
    public function __construct(protected VertexConfig $vertexConfig)
    {
    }

    /**
     * @param array<\Generated\Shared\Transfer\ConfigurationScopeTransfer> $configurationScopeTransfers
     * @param array<string, mixed> $requestValuesBySettingKey
     */
    public function build(
        array $configurationScopeTransfers,
        array $requestValuesBySettingKey = [],
    ): VertexConfigTransfer {
        return (new VertexConfigTransfer())
            ->setClientId($this->resolveCredentialValue(VertexConfig::CONFIGURATION_KEY_CLIENT_ID, $requestValuesBySettingKey, $configurationScopeTransfers))
            ->setClientSecret($this->resolveCredentialValue(VertexConfig::CONFIGURATION_KEY_CLIENT_SECRET, $requestValuesBySettingKey, $configurationScopeTransfers))
            ->setSecurityUri($this->resolveCredentialValue(VertexConfig::CONFIGURATION_KEY_SECURITY_URI, $requestValuesBySettingKey, $configurationScopeTransfers))
            ->setTransactionCallsUri($this->resolveCredentialValue(VertexConfig::CONFIGURATION_KEY_TRANSACTION_CALLS_URI, $requestValuesBySettingKey, $configurationScopeTransfers))
            ->setIsActive(true)
            ->setIsInvoicingEnabled($this->vertexConfig->isInvoicingEnabled($configurationScopeTransfers))
            ->setIsTaxIdValidatorEnabled($this->vertexConfig->isTaxIdValidatorEnabled())
            ->setTaxamoApiUrl($this->resolveCredentialValue(VertexConfig::CONFIGURATION_KEY_TAXAMO_API_URL, $requestValuesBySettingKey, $configurationScopeTransfers))
            ->setTaxamoToken($this->resolveCredentialValue(VertexConfig::CONFIGURATION_KEY_TAXAMO_TOKEN, $requestValuesBySettingKey, $configurationScopeTransfers));
    }

    /**
     * @param array<\Generated\Shared\Transfer\ConfigurationScopeTransfer> $configurationScopeTransfers
     */
    public function getStoredCredentialValue(string $settingKey, array $configurationScopeTransfers): string
    {
        return match ($settingKey) {
            VertexConfig::CONFIGURATION_KEY_CLIENT_ID => $this->vertexConfig->getClientId($configurationScopeTransfers),
            VertexConfig::CONFIGURATION_KEY_CLIENT_SECRET => $this->vertexConfig->getClientSecret($configurationScopeTransfers),
            VertexConfig::CONFIGURATION_KEY_SECURITY_URI => $this->vertexConfig->getSecurityUri($configurationScopeTransfers),
            VertexConfig::CONFIGURATION_KEY_TRANSACTION_CALLS_URI => $this->vertexConfig->getTransactionCallsUri($configurationScopeTransfers),
            VertexConfig::CONFIGURATION_KEY_TAXAMO_API_URL => $this->vertexConfig->getTaxamoApiUrl($configurationScopeTransfers),
            VertexConfig::CONFIGURATION_KEY_TAXAMO_TOKEN => $this->vertexConfig->getTaxamoToken($configurationScopeTransfers),
            default => '',
        };
    }

    /**
     * @return array<\Generated\Shared\Transfer\ConfigurationScopeTransfer>
     */
    public function createConfigurationScopeTransfersFromScope(?string $scope, ?string $scopeIdentifier): array
    {
        if ($scope !== StoreConstants::SCOPE_STORE || $scopeIdentifier === null || $scopeIdentifier === '') {
            return [];
        }

        return [
            (new ConfigurationScopeTransfer())
                ->setKey($scope)
                ->setIdentifier($scopeIdentifier),
        ];
    }

    /**
     * @param array<string, mixed> $requestValuesBySettingKey
     * @param array<\Generated\Shared\Transfer\ConfigurationScopeTransfer> $configurationScopeTransfers
     */
    protected function resolveCredentialValue(
        string $settingKey,
        array $requestValuesBySettingKey,
        array $configurationScopeTransfers,
    ): string {
        if (array_key_exists($settingKey, $requestValuesBySettingKey)) {
            return (string)$requestValuesBySettingKey[$settingKey];
        }

        return $this->getStoredCredentialValue($settingKey, $configurationScopeTransfers);
    }
}
