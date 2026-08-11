<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Zed\Vertex\Business\Configuration;

use Generated\Shared\Transfer\ConfigurationValueCollectionRequestTransfer;
use Generated\Shared\Transfer\ConfigurationValueDeletionTransfer;
use Generated\Shared\Transfer\ConfigurationValueTransfer;
use Spryker\Shared\Store\StoreConstants;
use Spryker\Zed\Store\Business\StoreFacadeInterface;
use SprykerEco\Shared\Vertex\VertexConfig as SharedVertexConfig;
use SprykerEco\Zed\Vertex\Business\Validator\VertexConfigValidatorInterface;
use SprykerEco\Zed\Vertex\VertexConfig;

class VertexConfigurationCompletenessGuard implements VertexConfigurationCompletenessGuardInterface
{
    public function __construct(
        protected VertexConfig $vertexConfig,
        protected VertexConfigValidatorInterface $vertexConfigValidator,
        protected StoreFacadeInterface $storeFacade,
        protected VertexConfigTransferBuilderInterface $vertexConfigTransferBuilder,
        protected VertexSentinelEncoderInterface $vertexSentinelEncoder,
    ) {
    }

    public function guard(
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
     * @return array<string, array<string, mixed>>
     */
    protected function groupCredentialRequestsByScope(
        ConfigurationValueCollectionRequestTransfer $configurationValueCollectionRequestTransfer,
    ): array {
        $credentialRequestGroups = [];

        foreach ($configurationValueCollectionRequestTransfer->getConfigurationValues() as $configurationValueTransfer) {
            $settingKey = $configurationValueTransfer->getSettingKey();

            if ($settingKey === null || !in_array($settingKey, $this->vertexConfig->getVertexConfigurationCredentialKeys(), true)) {
                continue;
            }

            $scopeSignature = $this->getScopeSignature($configurationValueTransfer->getScope(), $configurationValueTransfer->getScopeIdentifier());
            $credentialRequestGroup = $credentialRequestGroups[$scopeSignature] ?? [];
            $credentialRequestGroup['scope'] = $configurationValueTransfer->getScope();
            $credentialRequestGroup['scopeIdentifier'] = $configurationValueTransfer->getScopeIdentifier();
            $credentialRequestGroup['overrides'][$settingKey] = (string)$configurationValueTransfer->getValue();
            $credentialRequestGroup['valueTransfers'][$settingKey] = $configurationValueTransfer;
            $credentialRequestGroups[$scopeSignature] = $credentialRequestGroup;
        }

        foreach ($configurationValueCollectionRequestTransfer->getDeletionKeys() as $configurationValueDeletionTransfer) {
            $settingKey = $configurationValueDeletionTransfer->getSettingKey();

            if ($settingKey === null || !in_array($settingKey, $this->vertexConfig->getVertexConfigurationCredentialKeys(), true)) {
                continue;
            }

            $scopeSignature = $this->getScopeSignature($configurationValueDeletionTransfer->getScope(), $configurationValueDeletionTransfer->getScopeIdentifier());
            $credentialRequestGroup = $credentialRequestGroups[$scopeSignature] ?? [];
            $credentialRequestGroup['scope'] = $configurationValueDeletionTransfer->getScope();
            $credentialRequestGroup['scopeIdentifier'] = $configurationValueDeletionTransfer->getScopeIdentifier();
            $credentialRequestGroup['overrides'][$settingKey] = $this->resolveDeletionEffectiveValue($configurationValueDeletionTransfer);
            $credentialRequestGroup['deletionTransfers'][$settingKey] = $configurationValueDeletionTransfer;
            $credentialRequestGroups[$scopeSignature] = $credentialRequestGroup;
        }

        return $credentialRequestGroups;
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

        return $this->findBrokenStore($credentialRequestGroup);
    }

    /**
     * @param array<string, mixed> $credentialRequestGroup
     */
    protected function guardCredentialRequestGroup(array $credentialRequestGroup): ?string
    {
        $configurationScopeTransfers = $this->vertexConfigTransferBuilder->createConfigurationScopeTransfersFromScope(
            $credentialRequestGroup['scope'],
            $credentialRequestGroup['scopeIdentifier'],
        );

        if ($this->vertexConfig->getTaxProvider($configurationScopeTransfers) !== SharedVertexConfig::TAX_PROVIDER_VERTEX) {
            return null;
        }

        $vertexConfigTransfer = $this->vertexConfigTransferBuilder->build($configurationScopeTransfers, $credentialRequestGroup['overrides']);
        $vertexValidationResponseTransfer = $this->vertexConfigValidator->validate($vertexConfigTransfer);

        if ($vertexValidationResponseTransfer->getIsValid()) {
            return null;
        }

        return $this->vertexSentinelEncoder->encodeIncompleteSentinel(
            SharedVertexConfig::VERTEX_CONFIGURATION_INCOMPLETE_CASE_REMOVAL,
            $this->vertexSentinelEncoder->getScopeLabel($credentialRequestGroup['scope'], $credentialRequestGroup['scopeIdentifier']),
            $vertexValidationResponseTransfer->getMessages(),
        );
    }

    /**
     * @param array<string, mixed> $credentialRequestGroup
     */
    protected function findBrokenStore(array $credentialRequestGroup): ?string
    {
        if ($this->isStoreScope($credentialRequestGroup['scope'])) {
            return null;
        }

        $globalOverrides = $credentialRequestGroup['overrides'];

        foreach ($this->storeFacade->getAllStores() as $storeTransfer) {
            $storeIdentifier = $storeTransfer->getNameOrFail();
            $configurationScopeTransfers = $this->vertexConfigTransferBuilder->createConfigurationScopeTransfersFromScope(StoreConstants::SCOPE_STORE, $storeIdentifier);

            if ($this->vertexConfig->getTaxProvider($configurationScopeTransfers) !== SharedVertexConfig::TAX_PROVIDER_VERTEX) {
                continue;
            }

            $storeOverrides = $this->resolveStoreEffectiveOverrides($configurationScopeTransfers, $globalOverrides);
            $vertexConfigTransfer = $this->vertexConfigTransferBuilder->build($configurationScopeTransfers, $storeOverrides);
            $vertexValidationResponseTransfer = $this->vertexConfigValidator->validate($vertexConfigTransfer);

            if ($vertexValidationResponseTransfer->getIsValid()) {
                continue;
            }

            return $this->vertexSentinelEncoder->encodeIncompleteSentinel(
                SharedVertexConfig::VERTEX_CONFIGURATION_INCOMPLETE_CASE_CROSS_SCOPE,
                $this->vertexSentinelEncoder->getScopeLabel(StoreConstants::SCOPE_STORE, $storeIdentifier),
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

        foreach ($this->vertexConfig->getVertexConfigurationCredentialKeys() as $settingKey) {
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
        $globalStoredValue = $this->vertexConfigTransferBuilder->getStoredCredentialValue($settingKey, []);
        $storeStoredValue = $this->vertexConfigTransferBuilder->getStoredCredentialValue($settingKey, $configurationScopeTransfers);

        if ($storeStoredValue !== $globalStoredValue) {
            return $storeStoredValue;
        }

        if (array_key_exists($settingKey, $globalOverrides)) {
            return (string)$globalOverrides[$settingKey];
        }

        return $globalStoredValue;
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

    protected function resolveDeletionEffectiveValue(ConfigurationValueDeletionTransfer $configurationValueDeletionTransfer): string
    {
        if (!$this->isStoreScope($configurationValueDeletionTransfer->getScope())) {
            return '';
        }

        return $this->vertexConfigTransferBuilder->getStoredCredentialValue($configurationValueDeletionTransfer->getSettingKeyOrFail(), []);
    }

    protected function getScopeSignature(?string $scope, ?string $scopeIdentifier): string
    {
        return $scope . '|' . $scopeIdentifier;
    }

    protected function isStoreScope(?string $scope): bool
    {
        return $scope === StoreConstants::SCOPE_STORE;
    }
}
