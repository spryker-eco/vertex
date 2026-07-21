<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Zed\Vertex\Business\Configuration;

use Generated\Shared\Transfer\ConfigurationScopeTransfer;
use Generated\Shared\Transfer\ConfigurationValueCollectionRequestTransfer;
use Generated\Shared\Transfer\ConfigurationValueDeletionTransfer;
use Generated\Shared\Transfer\ConfigurationValueTransfer;
use Generated\Shared\Transfer\VertexConfigTransfer;
use Generated\Shared\Transfer\VertexValidationResponseTransfer;
use Spryker\Shared\Store\StoreConstants;
use Spryker\Zed\Store\Business\StoreFacadeInterface;
use SprykerEco\Shared\Vertex\VertexConfig as SharedVertexConfig;
use SprykerEco\Zed\Vertex\Business\Validator\VertexConfigValidatorInterface;
use SprykerEco\Zed\Vertex\VertexConfig;

class TaxProviderPreSaveValidator implements TaxProviderPreSaveValidatorInterface
{
    public function __construct(
        protected VertexConfig $vertexConfig,
        protected VertexConfigValidatorInterface $vertexConfigValidator,
        protected StoreFacadeInterface $storeFacade,
    ) {
    }

    public function validate(
        ConfigurationValueCollectionRequestTransfer $configurationValueCollectionRequestTransfer,
    ): ConfigurationValueCollectionRequestTransfer {
        $this->validateTaxProviderSelection($configurationValueCollectionRequestTransfer);
        $this->validateVertexConfigurationCompleteness($configurationValueCollectionRequestTransfer);

        return $configurationValueCollectionRequestTransfer;
    }

    protected function validateTaxProviderSelection(
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
                $this->encodeTaxProviderNotConfiguredSentinel($vertexValidationResponseTransfer->getMessages()),
            );
        }
    }

    protected function validateVertexConfigurationCompleteness(
        ConfigurationValueCollectionRequestTransfer $configurationValueCollectionRequestTransfer,
    ): void {
        $credentialRequestGroups = $this->groupCredentialRequestsByScope($configurationValueCollectionRequestTransfer);

        foreach ($credentialRequestGroups as $credentialRequestGroup) {
            $incompleteSentinel = $this->resolveIncompleteSentinel($credentialRequestGroup);

            if ($incompleteSentinel === null) {
                continue;
            }

            $this->markCredentialRequestGroupAsIncomplete(
                $configurationValueCollectionRequestTransfer,
                $credentialRequestGroup,
                $incompleteSentinel,
            );
        }
    }

    /**
     * @param array<string, mixed> $credentialRequestGroup
     */
    protected function resolveIncompleteSentinel(array $credentialRequestGroup): ?string
    {
        $incompleteSentinel = $this->guardCredentialRequestGroup($credentialRequestGroup);

        if ($incompleteSentinel !== null) {
            return $incompleteSentinel;
        }

        return $this->findStoreBrokenByGlobalChange($credentialRequestGroup);
    }

    /**
     * @param array<string, mixed> $credentialRequestGroup
     */
    protected function findStoreBrokenByGlobalChange(array $credentialRequestGroup): ?string
    {
        if ($this->isStoreScope($credentialRequestGroup['scope'])) {
            return null;
        }

        $globalOverrides = $credentialRequestGroup['overrides'];

        foreach ($this->storeFacade->getAllStores() as $storeTransfer) {
            $storeIdentifier = $storeTransfer->getNameOrFail();
            $configurationScopeTransfers = $this->createConfigurationScopeTransfersFromScope(StoreConstants::SCOPE_STORE, $storeIdentifier);

            if ($this->vertexConfig->getTaxProvider($configurationScopeTransfers) !== SharedVertexConfig::TAX_PROVIDER_VERTEX) {
                continue;
            }

            $storeOverrides = $this->resolveStoreEffectiveOverrides($configurationScopeTransfers, $globalOverrides);
            $vertexConfigTransfer = $this->buildVertexConfigTransfer($configurationScopeTransfers, $storeOverrides);
            $vertexValidationResponseTransfer = $this->vertexConfigValidator->validate($vertexConfigTransfer);

            if ($vertexValidationResponseTransfer->getIsValid()) {
                continue;
            }

            return $this->encodeIncompleteSentinel(
                SharedVertexConfig::VERTEX_CONFIGURATION_INCOMPLETE_CASE_CROSS_SCOPE,
                $this->getScopeLabel(StoreConstants::SCOPE_STORE, $storeIdentifier),
                $vertexValidationResponseTransfer->getMessages(),
            );
        }

        return null;
    }

    /**
     * @param array<\Generated\Shared\Transfer\ConfigurationScopeTransfer> $configurationScopeTransfers
     * @param array<string, mixed> $globalOverrides
     *
     * @return array<string, string>
     */
    protected function resolveStoreEffectiveOverrides(array $configurationScopeTransfers, array $globalOverrides): array
    {
        $storeOverrides = [];

        foreach (VertexConfig::VERTEX_CONFIGURATION_CREDENTIAL_KEYS as $settingKey) {
            $storeOverrides[$settingKey] = $this->resolveStoreEffectiveCredentialValue($settingKey, $configurationScopeTransfers, $globalOverrides);
        }

        return $storeOverrides;
    }

    /**
     * @param array<\Generated\Shared\Transfer\ConfigurationScopeTransfer> $configurationScopeTransfers
     * @param array<string, mixed> $globalOverrides
     */
    protected function resolveStoreEffectiveCredentialValue(
        string $settingKey,
        array $configurationScopeTransfers,
        array $globalOverrides,
    ): string {
        $globalStoredValue = $this->getStoredCredentialValue($settingKey, []);
        $storeStoredValue = $this->getStoredCredentialValue($settingKey, $configurationScopeTransfers);

        if ($storeStoredValue !== $globalStoredValue) {
            return $storeStoredValue;
        }

        if (array_key_exists($settingKey, $globalOverrides)) {
            return (string)$globalOverrides[$settingKey];
        }

        return $globalStoredValue;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    protected function groupCredentialRequestsByScope(
        ConfigurationValueCollectionRequestTransfer $configurationValueCollectionRequestTransfer,
    ): array {
        $credentialRequestGroups = [];

        foreach ($configurationValueCollectionRequestTransfer->getConfigurationValues() as $configurationValueTransfer) {
            $settingKey = $configurationValueTransfer->getSettingKey();

            if ($settingKey === null || !in_array($settingKey, VertexConfig::VERTEX_CONFIGURATION_CREDENTIAL_KEYS, true)) {
                continue;
            }

            $scopeSignature = $this->getScopeSignature($configurationValueTransfer->getScope(), $configurationValueTransfer->getScopeIdentifier());
            $credentialRequestGroups[$scopeSignature]['scope'] = $configurationValueTransfer->getScope();
            $credentialRequestGroups[$scopeSignature]['scopeIdentifier'] = $configurationValueTransfer->getScopeIdentifier();
            $credentialRequestGroups[$scopeSignature]['overrides'][$settingKey] = (string)$configurationValueTransfer->getValue();
            $credentialRequestGroups[$scopeSignature]['valueTransfers'][$settingKey] = $configurationValueTransfer;
        }

        foreach ($configurationValueCollectionRequestTransfer->getDeletionKeys() as $configurationValueDeletionTransfer) {
            $settingKey = $configurationValueDeletionTransfer->getSettingKey();

            if ($settingKey === null || !in_array($settingKey, VertexConfig::VERTEX_CONFIGURATION_CREDENTIAL_KEYS, true)) {
                continue;
            }

            $scopeSignature = $this->getScopeSignature($configurationValueDeletionTransfer->getScope(), $configurationValueDeletionTransfer->getScopeIdentifier());
            $credentialRequestGroups[$scopeSignature]['scope'] = $configurationValueDeletionTransfer->getScope();
            $credentialRequestGroups[$scopeSignature]['scopeIdentifier'] = $configurationValueDeletionTransfer->getScopeIdentifier();
            $credentialRequestGroups[$scopeSignature]['overrides'][$settingKey] = $this->resolveDeletionEffectiveValue($configurationValueDeletionTransfer);
            $credentialRequestGroups[$scopeSignature]['deletionTransfers'][$settingKey] = $configurationValueDeletionTransfer;
        }

        return $credentialRequestGroups;
    }

    /**
     * @param array<string, mixed> $credentialRequestGroup
     */
    protected function guardCredentialRequestGroup(array $credentialRequestGroup): ?string
    {
        $configurationScopeTransfers = $this->createConfigurationScopeTransfersFromScope(
            $credentialRequestGroup['scope'],
            $credentialRequestGroup['scopeIdentifier'],
        );

        if ($this->vertexConfig->getTaxProvider($configurationScopeTransfers) !== SharedVertexConfig::TAX_PROVIDER_VERTEX) {
            return null;
        }

        $vertexConfigTransfer = $this->buildVertexConfigTransfer($configurationScopeTransfers, $credentialRequestGroup['overrides']);
        $vertexValidationResponseTransfer = $this->vertexConfigValidator->validate($vertexConfigTransfer);

        if ($vertexValidationResponseTransfer->getIsValid()) {
            return null;
        }

        return $this->encodeIncompleteSentinel(
            SharedVertexConfig::VERTEX_CONFIGURATION_INCOMPLETE_CASE_REMOVAL,
            $this->getScopeLabel($credentialRequestGroup['scope'], $credentialRequestGroup['scopeIdentifier']),
            $vertexValidationResponseTransfer->getMessages(),
        );
    }

    /**
     * @param array<string> $reasons
     */
    protected function encodeIncompleteSentinel(string $case, string $scope, array $reasons): string
    {
        return SharedVertexConfig::VERTEX_CONFIGURATION_INCOMPLETE_SENTINEL . json_encode([
            SharedVertexConfig::VERTEX_CONFIGURATION_INCOMPLETE_PAYLOAD_KEY_CASE => $case,
            SharedVertexConfig::VERTEX_CONFIGURATION_INCOMPLETE_PAYLOAD_KEY_SCOPE => $scope,
            SharedVertexConfig::VERTEX_CONFIGURATION_INCOMPLETE_PAYLOAD_KEY_REASONS => array_values($reasons),
        ]);
    }

    /**
     * @param array<string, mixed> $credentialRequestGroup
     */
    protected function markCredentialRequestGroupAsIncomplete(
        ConfigurationValueCollectionRequestTransfer $configurationValueCollectionRequestTransfer,
        array $credentialRequestGroup,
        string $incompleteSentinel,
    ): void {
        /** @var array<\Generated\Shared\Transfer\ConfigurationValueTransfer> $valueTransfers */
        $valueTransfers = $credentialRequestGroup['valueTransfers'] ?? [];

        foreach ($valueTransfers as $configurationValueTransfer) {
            $configurationValueTransfer->setValue($incompleteSentinel);
        }

        /** @var array<\Generated\Shared\Transfer\ConfigurationValueDeletionTransfer> $deletionTransfers */
        $deletionTransfers = $credentialRequestGroup['deletionTransfers'] ?? [];

        foreach ($deletionTransfers as $configurationValueDeletionTransfer) {
            $configurationValueCollectionRequestTransfer->addConfigurationValue(
                (new ConfigurationValueTransfer())
                    ->fromArray($configurationValueDeletionTransfer->toArray(), true)
                    ->setValue($incompleteSentinel),
            );
        }
    }

    protected function getScopeLabel(?string $scope, ?string $scopeIdentifier): string
    {
        if ($this->isStoreScope($scope) && $scopeIdentifier !== null && $scopeIdentifier !== '') {
            return 'store ' . $scopeIdentifier;
        }

        return 'the global (Default) scope';
    }

    protected function isStoreScope(?string $scope): bool
    {
        return $scope === StoreConstants::SCOPE_STORE;
    }

    protected function resolveDeletionEffectiveValue(ConfigurationValueDeletionTransfer $configurationValueDeletionTransfer): string
    {
        if (!$this->isStoreScope($configurationValueDeletionTransfer->getScope())) {
            return '';
        }

        return $this->getStoredCredentialValue($configurationValueDeletionTransfer->getSettingKeyOrFail(), []);
    }

    protected function isVertexTaxProviderSelected(ConfigurationValueTransfer $configurationValueTransfer): bool
    {
        return $configurationValueTransfer->getSettingKey() === SharedVertexConfig::CONFIGURATION_KEY_TAX_PROVIDER
            && $configurationValueTransfer->getValue() === SharedVertexConfig::TAX_PROVIDER_VERTEX;
    }

    protected function validateVertexSelectionConfiguration(
        ConfigurationValueTransfer $configurationValueTransfer,
    ): VertexValidationResponseTransfer {
        $configurationScopeTransfers = $this->createConfigurationScopeTransfersFromScope(
            $configurationValueTransfer->getScope(),
            $configurationValueTransfer->getScopeIdentifier(),
        );

        return $this->vertexConfigValidator->validate($this->buildVertexConfigTransfer($configurationScopeTransfers));
    }

    /**
     * @param array<string> $reasons
     */
    protected function encodeTaxProviderNotConfiguredSentinel(array $reasons): string
    {
        return SharedVertexConfig::TAX_PROVIDER_NOT_CONFIGURED_SENTINEL . json_encode(array_values($reasons));
    }

    /**
     * @param array<\Generated\Shared\Transfer\ConfigurationScopeTransfer> $configurationScopeTransfers
     * @param array<string, mixed> $requestValuesBySettingKey
     */
    protected function buildVertexConfigTransfer(
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

    /**
     * @param array<\Generated\Shared\Transfer\ConfigurationScopeTransfer> $configurationScopeTransfers
     */
    protected function getStoredCredentialValue(string $settingKey, array $configurationScopeTransfers): string
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

    protected function getScopeSignature(?string $scope, ?string $scopeIdentifier): string
    {
        return $scope . '|' . $scopeIdentifier;
    }

    /**
     * @return array<\Generated\Shared\Transfer\ConfigurationScopeTransfer>
     */
    protected function createConfigurationScopeTransfersFromScope(?string $scope, ?string $scopeIdentifier): array
    {
        if (!$this->isStoreScope($scope) || $scopeIdentifier === null || $scopeIdentifier === '') {
            return [];
        }

        return [
            (new ConfigurationScopeTransfer())
                ->setKey($scope)
                ->setIdentifier($scopeIdentifier),
        ];
    }
}
