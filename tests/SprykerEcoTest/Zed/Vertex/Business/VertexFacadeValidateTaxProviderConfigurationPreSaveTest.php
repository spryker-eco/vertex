<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEcoTest\Zed\Vertex\Business;

use Codeception\Stub;
use Codeception\Test\Unit;
use Generated\Shared\Transfer\ConfigurationScopeTransfer;
use Generated\Shared\Transfer\ConfigurationValueCollectionRequestTransfer;
use Generated\Shared\Transfer\ConfigurationValueDeletionTransfer;
use Generated\Shared\Transfer\ConfigurationValueTransfer;
use Generated\Shared\Transfer\StoreTransfer;
use Generated\Shared\Transfer\VertexValidationResponseTransfer;
use Spryker\Shared\Store\StoreConstants;
use Spryker\Zed\Store\Business\StoreFacadeInterface;
use SprykerEco\Client\Vertex\VertexClientInterface;
use SprykerEco\Shared\Vertex\VertexConfig as VertexSharedConfig;
use SprykerEco\Zed\Vertex\Business\Validator\VertexConfigValidator;
use SprykerEco\Zed\Vertex\Business\Validator\VertexConfigValidatorInterface;
use SprykerEco\Zed\Vertex\VertexConfig;
use SprykerEco\Zed\Vertex\VertexDependencyProvider;
use SprykerEcoTest\Zed\Vertex\VertexBusinessTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerEcoTest
 * @group Zed
 * @group Vertex
 * @group Business
 * @group Facade
 * @group VertexFacadeValidateTaxProviderConfigurationPreSaveTest
 * Add your own group annotations below this line
 */
class VertexFacadeValidateTaxProviderConfigurationPreSaveTest extends Unit
{
    protected VertexBusinessTester $tester;

    protected const string STORE_NAME = 'DE';

    protected const string SCOPE_GLOBAL = 'global';

    protected const string UNRELATED_SETTING_KEY = 'integrations:vertex:configurations:vendor_code';

    /**
     * @var array<string, string>
     */
    protected const array CONFIGURED_VERTEX_CONFIG = [
        'getClientId' => 'client-id',
        'getClientSecret' => 'client-secret',
        'getSecurityUri' => 'https://auth.vertexsmb.com/',
        'getTransactionCallsUri' => 'https://api.vertexsmb.com/',
    ];

    /**
     * @var array<string, string>
     */
    protected const array UNCONFIGURED_VERTEX_CONFIG = [
        'getClientId' => '',
        'getClientSecret' => '',
        'getSecurityUri' => '',
        'getTransactionCallsUri' => '',
    ];

    public function testMarksVertexSelectionWithSentinelWhenVertexIsNotConfigured(): void
    {
        // Arrange
        $this->configureFacade(
            $this->createVertexConfigStub(static::UNCONFIGURED_VERTEX_CONFIG),
            $this->createVertexConfigValidator(),
            $this->createStoreFacadeMock(),
        );
        $configurationValueCollectionRequestTransfer = $this->createRequestTransfer(
            VertexSharedConfig::CONFIGURATION_KEY_TAX_PROVIDER,
            VertexSharedConfig::TAX_PROVIDER_VERTEX,
        );

        // Act
        $configurationValueCollectionRequestTransfer = $this->tester->getFacade()->validateTaxProviderConfigurationPreSave($configurationValueCollectionRequestTransfer);

        // Assert
        $configurationValueTransfer = $configurationValueCollectionRequestTransfer->getConfigurationValues()->offsetGet(0);
        $value = (string)$configurationValueTransfer->getValue();
        $this->assertStringStartsWith(VertexSharedConfig::TAX_PROVIDER_NOT_CONFIGURED_SENTINEL, $value);
        $reasons = json_decode(substr($value, strlen(VertexSharedConfig::TAX_PROVIDER_NOT_CONFIGURED_SENTINEL)), true);
        $this->assertContains('Client ID is required.', $reasons);
    }

    public function testKeepsVertexSelectionWhenVertexIsConfigured(): void
    {
        // Arrange
        $this->configureFacade(
            $this->createVertexConfigStub(static::CONFIGURED_VERTEX_CONFIG),
            $this->createVertexConfigValidator(),
            $this->createStoreFacadeMock(),
        );
        $configurationValueCollectionRequestTransfer = $this->createRequestTransfer(
            VertexSharedConfig::CONFIGURATION_KEY_TAX_PROVIDER,
            VertexSharedConfig::TAX_PROVIDER_VERTEX,
        );

        // Act
        $configurationValueCollectionRequestTransfer = $this->tester->getFacade()->validateTaxProviderConfigurationPreSave($configurationValueCollectionRequestTransfer);

        // Assert
        $configurationValueTransfer = $configurationValueCollectionRequestTransfer->getConfigurationValues()->offsetGet(0);
        $this->assertSame(VertexSharedConfig::TAX_PROVIDER_VERTEX, $configurationValueTransfer->getValue());
    }

    public function testDoesNotTouchNonVertexTaxProviderSelection(): void
    {
        // Arrange
        $this->configureFacade(
            $this->createVertexConfigStub(static::UNCONFIGURED_VERTEX_CONFIG),
            $this->createVertexConfigValidator(),
            $this->createStoreFacadeMock(),
        );
        $configurationValueCollectionRequestTransfer = $this->createRequestTransfer(
            VertexSharedConfig::CONFIGURATION_KEY_TAX_PROVIDER,
            VertexSharedConfig::TAX_PROVIDER_SPRYKER,
        );

        // Act
        $configurationValueCollectionRequestTransfer = $this->tester->getFacade()->validateTaxProviderConfigurationPreSave($configurationValueCollectionRequestTransfer);

        // Assert
        $configurationValueTransfer = $configurationValueCollectionRequestTransfer->getConfigurationValues()->offsetGet(0);
        $this->assertSame(VertexSharedConfig::TAX_PROVIDER_SPRYKER, $configurationValueTransfer->getValue());
    }

    public function testDoesNotTouchUnrelatedSettingKey(): void
    {
        // Arrange
        $this->configureFacade(
            $this->createVertexConfigStub(static::UNCONFIGURED_VERTEX_CONFIG),
            $this->createVertexConfigValidator(),
            $this->createStoreFacadeMock(),
        );
        // A value that happens to equal "vertex" but belongs to another setting key must be left untouched.
        $configurationValueCollectionRequestTransfer = $this->createRequestTransfer(
            static::UNRELATED_SETTING_KEY,
            VertexSharedConfig::TAX_PROVIDER_VERTEX,
        );

        // Act
        $configurationValueCollectionRequestTransfer = $this->tester->getFacade()->validateTaxProviderConfigurationPreSave($configurationValueCollectionRequestTransfer);

        // Assert
        $configurationValueTransfer = $configurationValueCollectionRequestTransfer->getConfigurationValues()->offsetGet(0);
        $this->assertSame(VertexSharedConfig::TAX_PROVIDER_VERTEX, $configurationValueTransfer->getValue());
    }

    public function testPassesStoreScopeToVertexConfigWhenSelectionIsStoreScoped(): void
    {
        // Arrange
        $expectedConfigurationScopeTransfers = [
            (new ConfigurationScopeTransfer())
                ->setKey(StoreConstants::SCOPE_STORE)
                ->setIdentifier(static::STORE_NAME),
        ];
        $this->configureFacade(
            $this->createScopeAssertingVertexConfigStub($expectedConfigurationScopeTransfers),
            $this->createValidVertexConfigValidatorMock(),
            $this->createStoreFacadeMock(),
        );
        $configurationValueCollectionRequestTransfer = $this->createRequestTransfer(
            VertexSharedConfig::CONFIGURATION_KEY_TAX_PROVIDER,
            VertexSharedConfig::TAX_PROVIDER_VERTEX,
            StoreConstants::SCOPE_STORE,
            static::STORE_NAME,
        );

        // Act & Assert (assertions run inside the config stub getters)
        $this->tester->getFacade()->validateTaxProviderConfigurationPreSave($configurationValueCollectionRequestTransfer);
    }

    public function testPassesEmptyScopeToVertexConfigWhenSelectionIsGlobalScoped(): void
    {
        // Arrange
        $this->configureFacade(
            $this->createScopeAssertingVertexConfigStub([]),
            $this->createValidVertexConfigValidatorMock(),
            $this->createStoreFacadeMock(),
        );
        $configurationValueCollectionRequestTransfer = $this->createRequestTransfer(
            VertexSharedConfig::CONFIGURATION_KEY_TAX_PROVIDER,
            VertexSharedConfig::TAX_PROVIDER_VERTEX,
            static::SCOPE_GLOBAL,
        );

        // Act & Assert (assertions run inside the config stub getters)
        $this->tester->getFacade()->validateTaxProviderConfigurationPreSave($configurationValueCollectionRequestTransfer);
    }

    public function testMarksGuardedCredentialWithSentinelWhenClearedWhileVertexIsSelected(): void
    {
        // Arrange
        // Vertex is the selected tax provider and the Tax ID validator is enabled, but the Taxamo token is being cleared.
        $this->configureFacade(
            $this->createReverseVertexConfigStub(VertexSharedConfig::TAX_PROVIDER_VERTEX, true),
            $this->createVertexConfigValidator(),
            $this->createStoreFacadeMock(),
        );
        $configurationValueCollectionRequestTransfer = $this->createRequestTransfer(
            VertexConfig::CONFIGURATION_KEY_TAXAMO_TOKEN,
            '',
        );

        // Act
        $configurationValueCollectionRequestTransfer = $this->tester->getFacade()->validateTaxProviderConfigurationPreSave($configurationValueCollectionRequestTransfer);

        // Assert
        $configurationValueTransfer = $configurationValueCollectionRequestTransfer->getConfigurationValues()->offsetGet(0);
        $this->assertStringStartsWith(VertexSharedConfig::VERTEX_CONFIGURATION_INCOMPLETE_SENTINEL, (string)$configurationValueTransfer->getValue());
    }

    public function testKeepsGuardedCredentialWhenConfigurationStaysComplete(): void
    {
        // Arrange
        $this->configureFacade(
            $this->createReverseVertexConfigStub(VertexSharedConfig::TAX_PROVIDER_VERTEX, true),
            $this->createVertexConfigValidator(),
            $this->createStoreFacadeMock(),
        );
        $configurationValueCollectionRequestTransfer = $this->createRequestTransfer(
            VertexConfig::CONFIGURATION_KEY_TAXAMO_TOKEN,
            'new-taxamo-token',
        );

        // Act
        $configurationValueCollectionRequestTransfer = $this->tester->getFacade()->validateTaxProviderConfigurationPreSave($configurationValueCollectionRequestTransfer);

        // Assert
        $configurationValueTransfer = $configurationValueCollectionRequestTransfer->getConfigurationValues()->offsetGet(0);
        $this->assertSame('new-taxamo-token', $configurationValueTransfer->getValue());
    }

    public function testDoesNotMarkGuardedCredentialWhenVertexIsNotSelected(): void
    {
        // Arrange
        // Vertex is not the selected tax provider, so clearing its configuration is allowed.
        $this->configureFacade(
            $this->createReverseVertexConfigStub(VertexSharedConfig::TAX_PROVIDER_SPRYKER, true),
            $this->createVertexConfigValidator(),
            $this->createStoreFacadeMock(),
        );
        $configurationValueCollectionRequestTransfer = $this->createRequestTransfer(
            VertexConfig::CONFIGURATION_KEY_TAXAMO_TOKEN,
            '',
        );

        // Act
        $configurationValueCollectionRequestTransfer = $this->tester->getFacade()->validateTaxProviderConfigurationPreSave($configurationValueCollectionRequestTransfer);

        // Assert
        $configurationValueTransfer = $configurationValueCollectionRequestTransfer->getConfigurationValues()->offsetGet(0);
        $this->assertSame('', $configurationValueTransfer->getValue());
    }

    public function testDoesNotMarkGuardedCredentialWhenTaxIdValidatorIsDisabled(): void
    {
        // Arrange
        // With the Tax ID validator disabled, the Taxamo credentials are not part of the required Vertex configuration.
        $this->configureFacade(
            $this->createReverseVertexConfigStub(VertexSharedConfig::TAX_PROVIDER_VERTEX, false),
            $this->createVertexConfigValidator(),
            $this->createStoreFacadeMock(),
        );
        $configurationValueCollectionRequestTransfer = $this->createRequestTransfer(
            VertexConfig::CONFIGURATION_KEY_TAXAMO_TOKEN,
            '',
        );

        // Act
        $configurationValueCollectionRequestTransfer = $this->tester->getFacade()->validateTaxProviderConfigurationPreSave($configurationValueCollectionRequestTransfer);

        // Assert
        $configurationValueTransfer = $configurationValueCollectionRequestTransfer->getConfigurationValues()->offsetGet(0);
        $this->assertSame('', $configurationValueTransfer->getValue());
    }

    public function testMarksCredentialDeletionWithSentinelWhenClearedWhileVertexIsSelected(): void
    {
        // Arrange
        // Clearing a credential in the Back Office arrives as a deletion key; at global scope it falls back to the empty default.
        $this->configureFacade(
            $this->createReverseVertexConfigStub(VertexSharedConfig::TAX_PROVIDER_VERTEX, true),
            $this->createVertexConfigValidator(),
            $this->createStoreFacadeMock(),
        );
        $configurationValueCollectionRequestTransfer = $this->createDeletionRequestTransfer(
            VertexConfig::CONFIGURATION_KEY_CLIENT_ID,
            static::SCOPE_GLOBAL,
        );

        // Act
        $configurationValueCollectionRequestTransfer = $this->tester->getFacade()->validateTaxProviderConfigurationPreSave($configurationValueCollectionRequestTransfer);

        // Assert
        // A sentinel configuration value is added so the removal constraint blocks the save; the deletion key is left in place because the writer short-circuits on the validation error before processing any deletion.
        $this->assertCount(1, $configurationValueCollectionRequestTransfer->getDeletionKeys());
        $this->assertCount(1, $configurationValueCollectionRequestTransfer->getConfigurationValues());
        $configurationValueTransfer = $configurationValueCollectionRequestTransfer->getConfigurationValues()->offsetGet(0);
        $this->assertSame(VertexConfig::CONFIGURATION_KEY_CLIENT_ID, $configurationValueTransfer->getSettingKey());
        $this->assertStringStartsWith(VertexSharedConfig::VERTEX_CONFIGURATION_INCOMPLETE_SENTINEL, (string)$configurationValueTransfer->getValue());
    }

    public function testKeepsStoreScopedCredentialDeletionWhenGlobalFallbackIsValid(): void
    {
        // Arrange
        // Deleting a store override falls back to the global value, which is complete, so the deletion must be allowed.
        $this->configureFacade(
            $this->createReverseVertexConfigStub(VertexSharedConfig::TAX_PROVIDER_VERTEX, true),
            $this->createVertexConfigValidator(),
            $this->createStoreFacadeMock(),
        );
        $configurationValueCollectionRequestTransfer = $this->createDeletionRequestTransfer(
            VertexConfig::CONFIGURATION_KEY_CLIENT_ID,
            StoreConstants::SCOPE_STORE,
            static::STORE_NAME,
        );

        // Act
        $configurationValueCollectionRequestTransfer = $this->tester->getFacade()->validateTaxProviderConfigurationPreSave($configurationValueCollectionRequestTransfer);

        // Assert
        $this->assertCount(1, $configurationValueCollectionRequestTransfer->getDeletionKeys());
        $this->assertCount(0, $configurationValueCollectionRequestTransfer->getConfigurationValues());
    }

    public function testPassesStoreScopeToVertexConfigWhenGuardedCredentialIsStoreScoped(): void
    {
        // Arrange
        $expectedConfigurationScopeTransfers = [
            (new ConfigurationScopeTransfer())
                ->setKey(StoreConstants::SCOPE_STORE)
                ->setIdentifier(static::STORE_NAME),
        ];
        $this->configureFacade(
            $this->createReverseScopeAssertingVertexConfigStub($expectedConfigurationScopeTransfers, VertexSharedConfig::TAX_PROVIDER_VERTEX),
            $this->createValidVertexConfigValidatorMock(),
            $this->createStoreFacadeMock(),
        );
        $configurationValueCollectionRequestTransfer = $this->createRequestTransfer(
            VertexConfig::CONFIGURATION_KEY_TAXAMO_TOKEN,
            '',
            StoreConstants::SCOPE_STORE,
            static::STORE_NAME,
        );

        // Act & Assert (assertions run inside the config stub getters)
        $this->tester->getFacade()->validateTaxProviderConfigurationPreSave($configurationValueCollectionRequestTransfer);
    }

    public function testBlocksGlobalRemovalWhenStoreInheritsVertexConfiguration(): void
    {
        // Arrange
        // Global uses Spryker (so the global change itself is valid), but store AT selected Vertex and inherits the global credentials being removed.
        $this->configureFacade(
            $this->createCrossScopeVertexConfigStub(VertexSharedConfig::TAX_PROVIDER_SPRYKER, VertexSharedConfig::TAX_PROVIDER_VERTEX, true),
            $this->createVertexConfigValidator(),
            $this->createStoreFacadeMock([static::STORE_NAME]),
        );
        $configurationValueCollectionRequestTransfer = $this->createDeletionRequestTransfer(
            VertexConfig::CONFIGURATION_KEY_CLIENT_ID,
            static::SCOPE_GLOBAL,
        );

        // Act
        $configurationValueCollectionRequestTransfer = $this->tester->getFacade()->validateTaxProviderConfigurationPreSave($configurationValueCollectionRequestTransfer);

        // Assert
        // The blocking sentinel is added as a configuration value; the deletion key stays because the writer short-circuits on the error before deletions run.
        $this->assertCount(1, $configurationValueCollectionRequestTransfer->getDeletionKeys());
        $this->assertCount(1, $configurationValueCollectionRequestTransfer->getConfigurationValues());
        $configurationValueTransfer = $configurationValueCollectionRequestTransfer->getConfigurationValues()->offsetGet(0);
        $this->assertStringStartsWith(VertexSharedConfig::VERTEX_CONFIGURATION_INCOMPLETE_SENTINEL, (string)$configurationValueTransfer->getValue());
    }

    public function testAllowsGlobalRemovalWhenNoStoreUsesVertex(): void
    {
        // Arrange
        // Store AT still uses Spryker, so removing global Vertex configuration breaks nothing.
        $this->configureFacade(
            $this->createCrossScopeVertexConfigStub(VertexSharedConfig::TAX_PROVIDER_SPRYKER, VertexSharedConfig::TAX_PROVIDER_SPRYKER, true),
            $this->createVertexConfigValidator(),
            $this->createStoreFacadeMock([static::STORE_NAME]),
        );
        $configurationValueCollectionRequestTransfer = $this->createDeletionRequestTransfer(
            VertexConfig::CONFIGURATION_KEY_CLIENT_ID,
            static::SCOPE_GLOBAL,
        );

        // Act
        $configurationValueCollectionRequestTransfer = $this->tester->getFacade()->validateTaxProviderConfigurationPreSave($configurationValueCollectionRequestTransfer);

        // Assert
        $this->assertCount(1, $configurationValueCollectionRequestTransfer->getDeletionKeys());
        $this->assertCount(0, $configurationValueCollectionRequestTransfer->getConfigurationValues());
    }

    public function testAllowsGlobalRemovalWhenStoreWithVertexHasOwnConfiguration(): void
    {
        // Arrange
        // Store AT selected Vertex but has its own complete credentials, so it is unaffected by the global removal.
        $this->configureFacade(
            $this->createCrossScopeVertexConfigStub(VertexSharedConfig::TAX_PROVIDER_SPRYKER, VertexSharedConfig::TAX_PROVIDER_VERTEX, false),
            $this->createVertexConfigValidator(),
            $this->createStoreFacadeMock([static::STORE_NAME]),
        );
        $configurationValueCollectionRequestTransfer = $this->createDeletionRequestTransfer(
            VertexConfig::CONFIGURATION_KEY_CLIENT_ID,
            static::SCOPE_GLOBAL,
        );

        // Act
        $configurationValueCollectionRequestTransfer = $this->tester->getFacade()->validateTaxProviderConfigurationPreSave($configurationValueCollectionRequestTransfer);

        // Assert
        $this->assertCount(1, $configurationValueCollectionRequestTransfer->getDeletionKeys());
        $this->assertCount(0, $configurationValueCollectionRequestTransfer->getConfigurationValues());
    }

    public function testIncompleteSentinelCarriesRemovalScopeAndConcreteReasons(): void
    {
        // Arrange
        // Vertex is selected globally and the Client ID is being cleared, leaving the configuration incomplete.
        $this->configureFacade(
            $this->createReverseVertexConfigStub(VertexSharedConfig::TAX_PROVIDER_VERTEX, false),
            $this->createVertexConfigValidator(),
            $this->createStoreFacadeMock(),
        );
        $configurationValueCollectionRequestTransfer = $this->createRequestTransfer(
            VertexConfig::CONFIGURATION_KEY_CLIENT_ID,
            '',
        );

        // Act
        $configurationValueCollectionRequestTransfer = $this->tester->getFacade()->validateTaxProviderConfigurationPreSave($configurationValueCollectionRequestTransfer);

        // Assert
        $configurationValueTransfer = $configurationValueCollectionRequestTransfer->getConfigurationValues()->offsetGet(0);
        $payload = $this->decodeIncompleteSentinelPayload((string)$configurationValueTransfer->getValue());
        $this->assertSame(VertexSharedConfig::VERTEX_CONFIGURATION_INCOMPLETE_CASE_REMOVAL, $payload[VertexSharedConfig::VERTEX_CONFIGURATION_INCOMPLETE_PAYLOAD_KEY_CASE]);
        $this->assertSame('the global (Default) scope', $payload[VertexSharedConfig::VERTEX_CONFIGURATION_INCOMPLETE_PAYLOAD_KEY_SCOPE]);
        $this->assertContains('Client ID is required.', $payload[VertexSharedConfig::VERTEX_CONFIGURATION_INCOMPLETE_PAYLOAD_KEY_REASONS]);
    }

    public function testIncompleteSentinelCarriesCrossScopeStoreAndReasons(): void
    {
        // Arrange
        // Global uses Spryker, but store DE selected Vertex and inherits the global Client ID being removed.
        $this->configureFacade(
            $this->createCrossScopeVertexConfigStub(VertexSharedConfig::TAX_PROVIDER_SPRYKER, VertexSharedConfig::TAX_PROVIDER_VERTEX, true),
            $this->createVertexConfigValidator(),
            $this->createStoreFacadeMock([static::STORE_NAME]),
        );
        $configurationValueCollectionRequestTransfer = $this->createDeletionRequestTransfer(
            VertexConfig::CONFIGURATION_KEY_CLIENT_ID,
            static::SCOPE_GLOBAL,
        );

        // Act
        $configurationValueCollectionRequestTransfer = $this->tester->getFacade()->validateTaxProviderConfigurationPreSave($configurationValueCollectionRequestTransfer);

        // Assert
        $configurationValueTransfer = $configurationValueCollectionRequestTransfer->getConfigurationValues()->offsetGet(0);
        $payload = $this->decodeIncompleteSentinelPayload((string)$configurationValueTransfer->getValue());
        $this->assertSame(VertexSharedConfig::VERTEX_CONFIGURATION_INCOMPLETE_CASE_CROSS_SCOPE, $payload[VertexSharedConfig::VERTEX_CONFIGURATION_INCOMPLETE_PAYLOAD_KEY_CASE]);
        $this->assertSame('store ' . static::STORE_NAME, $payload[VertexSharedConfig::VERTEX_CONFIGURATION_INCOMPLETE_PAYLOAD_KEY_SCOPE]);
        $this->assertContains('Client ID is required.', $payload[VertexSharedConfig::VERTEX_CONFIGURATION_INCOMPLETE_PAYLOAD_KEY_REASONS]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function decodeIncompleteSentinelPayload(string $value): array
    {
        $this->assertStringStartsWith(VertexSharedConfig::VERTEX_CONFIGURATION_INCOMPLETE_SENTINEL, $value);

        $encodedPayload = substr($value, strlen(VertexSharedConfig::VERTEX_CONFIGURATION_INCOMPLETE_SENTINEL));

        return json_decode($encodedPayload, true);
    }

    protected function configureFacade(
        VertexConfig $vertexConfig,
        VertexConfigValidatorInterface $vertexConfigValidator,
        StoreFacadeInterface $storeFacade,
    ): void {
        $this->tester->mockFactoryMethod('getConfig', $vertexConfig);
        $this->tester->mockFactoryMethod('createVertexConfigValidator', $vertexConfigValidator);
        $this->tester->setDependency(VertexDependencyProvider::FACADE_STORE, $storeFacade);
    }

    protected function createRequestTransfer(
        string $settingKey,
        string $value,
        ?string $scope = null,
        ?string $scopeIdentifier = null,
    ): ConfigurationValueCollectionRequestTransfer {
        $configurationValueTransfer = (new ConfigurationValueTransfer())
            ->setSettingKey($settingKey)
            ->setValue($value)
            ->setScope($scope)
            ->setScopeIdentifier($scopeIdentifier);

        return (new ConfigurationValueCollectionRequestTransfer())
            ->addConfigurationValue($configurationValueTransfer);
    }

    protected function createDeletionRequestTransfer(
        string $settingKey,
        string $scope,
        ?string $scopeIdentifier = null,
    ): ConfigurationValueCollectionRequestTransfer {
        $configurationValueDeletionTransfer = (new ConfigurationValueDeletionTransfer())
            ->setSettingKey($settingKey)
            ->setScope($scope)
            ->setScopeIdentifier($scopeIdentifier);

        return (new ConfigurationValueCollectionRequestTransfer())
            ->addDeletionKey($configurationValueDeletionTransfer);
    }

    /**
     * @param array<string, string> $vertexConfigValues
     */
    protected function createVertexConfigStub(array $vertexConfigValues): VertexConfig
    {
        return Stub::make(VertexConfig::class, $vertexConfigValues + [
            'isInvoicingEnabled' => false,
            'isTaxIdValidatorEnabled' => false,
            'getTaxamoApiUrl' => '',
            'getTaxamoToken' => '',
        ]);
    }

    /**
     * @param array<\Generated\Shared\Transfer\ConfigurationScopeTransfer> $expectedConfigurationScopeTransfers
     */
    protected function createScopeAssertingVertexConfigStub(array $expectedConfigurationScopeTransfers): VertexConfig
    {
        $assertScopeAndReturnString = function (array $configurationScopeTransfers = []) use ($expectedConfigurationScopeTransfers): string {
            $this->assertEquals($expectedConfigurationScopeTransfers, $configurationScopeTransfers);

            return '';
        };

        $assertScopeAndReturnBool = function (array $configurationScopeTransfers = []) use ($expectedConfigurationScopeTransfers): bool {
            $this->assertEquals($expectedConfigurationScopeTransfers, $configurationScopeTransfers);

            return false;
        };

        return Stub::make(VertexConfig::class, [
            'getClientId' => $assertScopeAndReturnString,
            'getClientSecret' => $assertScopeAndReturnString,
            'getSecurityUri' => $assertScopeAndReturnString,
            'getTransactionCallsUri' => $assertScopeAndReturnString,
            'getTaxamoApiUrl' => $assertScopeAndReturnString,
            'getTaxamoToken' => $assertScopeAndReturnString,
            'isInvoicingEnabled' => $assertScopeAndReturnBool,
            'isTaxIdValidatorEnabled' => false,
        ]);
    }

    protected function createReverseVertexConfigStub(string $taxProvider, bool $isTaxIdValidatorEnabled): VertexConfig
    {
        return Stub::make(VertexConfig::class, [
            'getTaxProvider' => $taxProvider,
            'getClientId' => 'client-id',
            'getClientSecret' => 'client-secret',
            'getSecurityUri' => 'https://auth.vertexsmb.com/',
            'getTransactionCallsUri' => 'https://api.vertexsmb.com/',
            'getTaxamoApiUrl' => 'https://api.taxamo.com/',
            'getTaxamoToken' => 'taxamo-token',
            'isInvoicingEnabled' => false,
            'isTaxIdValidatorEnabled' => $isTaxIdValidatorEnabled,
        ]);
    }

    /**
     * @param array<\Generated\Shared\Transfer\ConfigurationScopeTransfer> $expectedConfigurationScopeTransfers
     */
    protected function createReverseScopeAssertingVertexConfigStub(
        array $expectedConfigurationScopeTransfers,
        string $taxProvider,
    ): VertexConfig {
        $assertScopeAndReturnString = function (array $configurationScopeTransfers = []) use ($expectedConfigurationScopeTransfers): string {
            $this->assertEquals($expectedConfigurationScopeTransfers, $configurationScopeTransfers);

            return '';
        };

        $assertScopeAndReturnBool = function (array $configurationScopeTransfers = []) use ($expectedConfigurationScopeTransfers): bool {
            $this->assertEquals($expectedConfigurationScopeTransfers, $configurationScopeTransfers);

            return false;
        };

        $assertScopeAndReturnTaxProvider = function (array $configurationScopeTransfers = []) use ($expectedConfigurationScopeTransfers, $taxProvider): string {
            $this->assertEquals($expectedConfigurationScopeTransfers, $configurationScopeTransfers);

            return $taxProvider;
        };

        return Stub::make(VertexConfig::class, [
            'getTaxProvider' => $assertScopeAndReturnTaxProvider,
            'getClientId' => $assertScopeAndReturnString,
            'getClientSecret' => $assertScopeAndReturnString,
            'getSecurityUri' => $assertScopeAndReturnString,
            'getTransactionCallsUri' => $assertScopeAndReturnString,
            'getTaxamoApiUrl' => $assertScopeAndReturnString,
            'getTaxamoToken' => $assertScopeAndReturnString,
            'isInvoicingEnabled' => $assertScopeAndReturnBool,
            'isTaxIdValidatorEnabled' => true,
        ]);
    }

    protected function createVertexConfigValidator(): VertexConfigValidatorInterface
    {
        return new VertexConfigValidator(
            $this->getMockBuilder(VertexClientInterface::class)->getMock(),
        );
    }

    /**
     * @param array<string> $storeNames
     */
    protected function createStoreFacadeMock(array $storeNames = []): StoreFacadeInterface
    {
        $storeTransfers = array_map(
            fn (string $storeName): StoreTransfer => (new StoreTransfer())->setName($storeName),
            $storeNames,
        );

        $storeFacadeMock = $this->getMockBuilder(StoreFacadeInterface::class)->getMock();
        $storeFacadeMock->method('getAllStores')->willReturn($storeTransfers);

        return $storeFacadeMock;
    }

    protected function createCrossScopeVertexConfigStub(
        string $globalTaxProvider,
        string $storeTaxProvider,
        bool $storeInheritsGlobal,
    ): VertexConfig {
        $globalValues = [
            'getClientId' => 'global-client-id',
            'getClientSecret' => 'global-client-secret',
            'getSecurityUri' => 'https://global-auth.vertexsmb.com/',
            'getTransactionCallsUri' => 'https://global-api.vertexsmb.com/',
            'getTaxamoApiUrl' => 'https://global-api.taxamo.com/',
            'getTaxamoToken' => 'global-taxamo-token',
        ];
        $storeValues = $storeInheritsGlobal ? $globalValues : [
            'getClientId' => 'store-client-id',
            'getClientSecret' => 'store-client-secret',
            'getSecurityUri' => 'https://store-auth.vertexsmb.com/',
            'getTransactionCallsUri' => 'https://store-api.vertexsmb.com/',
            'getTaxamoApiUrl' => 'https://store-api.taxamo.com/',
            'getTaxamoToken' => 'store-taxamo-token',
        ];

        $stubMethods = [
            'getTaxProvider' => function (array $configurationScopeTransfers = []) use ($globalTaxProvider, $storeTaxProvider): string {
                return $configurationScopeTransfers === [] ? $globalTaxProvider : $storeTaxProvider;
            },
            'isInvoicingEnabled' => false,
            'isTaxIdValidatorEnabled' => true,
        ];

        foreach ($globalValues as $method => $globalValue) {
            $storeValue = $storeValues[$method];
            $stubMethods[$method] = function (array $configurationScopeTransfers = []) use ($globalValue, $storeValue): string {
                return $configurationScopeTransfers === [] ? $globalValue : $storeValue;
            };
        }

        return Stub::make(VertexConfig::class, $stubMethods);
    }

    protected function createValidVertexConfigValidatorMock(): VertexConfigValidatorInterface
    {
        $vertexConfigValidatorMock = $this->getMockBuilder(VertexConfigValidatorInterface::class)->getMock();
        $vertexConfigValidatorMock->method('validate')->willReturn((new VertexValidationResponseTransfer())->setIsValid(true));

        return $vertexConfigValidatorMock;
    }
}
