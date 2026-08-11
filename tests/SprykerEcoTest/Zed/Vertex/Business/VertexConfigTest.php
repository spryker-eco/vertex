<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEcoTest\Zed\Vertex\Business;

use Codeception\Stub;
use Codeception\Test\Unit;
use SprykerEco\Shared\Vertex\VertexConfig as VertexSharedConfig;
use SprykerEco\Zed\Vertex\VertexConfig;

/**
 * Auto-generated group annotations
 *
 * @group SprykerEcoTest
 * @group Zed
 * @group Vertex
 * @group Business
 * @group VertexConfigTest
 * Add your own group annotations below this line
 */
class VertexConfigTest extends Unit
{
    protected const string BACK_OFFICE_VALUE = 'back-office-value';

    protected const string ENVIRONMENT_VALUE = 'environment-value';

    protected const string ENVIRONMENT_KEY_IS_ACTIVE = 'VERTEX:IS_ACTIVE';

    protected const string CONFIGURATION_KEY_TAX_PROVIDER = 'taxes:tax_provider:provider:tax_provider';

    protected const string TAX_PROVIDER_VERTEX = 'vertex';

    protected const string TAX_PROVIDER_SPRYKER = 'spryker';

    /**
     * @dataProvider configGetterDataProvider
     */
    public function testReturnsBackOfficeConfigurationWhenConfigurationModuleIsUsed(
        string $method,
        string $environmentConfigKey,
        string $backOfficeConfigKey,
    ): void {
        // Arrange
        $vertexConfig = $this->createVertexConfig(
            [$backOfficeConfigKey => static::BACK_OFFICE_VALUE],
            [$environmentConfigKey => static::ENVIRONMENT_VALUE],
            true,
        );

        // Act
        $configValue = $vertexConfig->{$method}();

        // Assert
        $this->assertSame(static::BACK_OFFICE_VALUE, $configValue);
    }

    /**
     * @dataProvider configGetterDataProvider
     */
    public function testReturnsEnvironmentConfigurationWhenConfigurationModuleIsNotUsed(
        string $method,
        string $environmentConfigKey,
        string $backOfficeConfigKey,
    ): void {
        // Arrange
        // Even though the Back Office value is set, the flag switches the source back to the environment configuration.
        $vertexConfig = $this->createVertexConfig(
            [$backOfficeConfigKey => static::BACK_OFFICE_VALUE],
            [$environmentConfigKey => static::ENVIRONMENT_VALUE],
            false,
        );

        // Act
        $configValue = $vertexConfig->{$method}();

        // Assert
        $this->assertSame(static::ENVIRONMENT_VALUE, $configValue);
    }

    /**
     * @dataProvider configGetterDataProvider
     */
    public function testReturnsDefaultWhenConfigurationModuleIsUsedButValueIsNotSet(string $method): void
    {
        // Arrange
        $vertexConfig = $this->createVertexConfig([], [], true);

        // Act
        $configValue = $vertexConfig->{$method}();

        // Assert
        $this->assertSame('', $configValue);
    }

    public function testIsActiveReturnsTrueWhenVertexTaxProviderIsSelectedAndConfigurationModuleIsUsed(): void
    {
        // Arrange
        $vertexConfig = $this->createVertexConfig(
            [static::CONFIGURATION_KEY_TAX_PROVIDER => static::TAX_PROVIDER_VERTEX],
            [static::ENVIRONMENT_KEY_IS_ACTIVE => false],
            true,
        );

        // Act
        $isActive = $vertexConfig->isActive();

        // Assert
        $this->assertTrue($isActive);
    }

    public function testIsActiveReturnsFalseWhenNonVertexTaxProviderIsSelectedAndConfigurationModuleIsUsed(): void
    {
        // Arrange
        $vertexConfig = $this->createVertexConfig(
            [static::CONFIGURATION_KEY_TAX_PROVIDER => static::TAX_PROVIDER_SPRYKER],
            [static::ENVIRONMENT_KEY_IS_ACTIVE => true],
            true,
        );

        // Act
        $isActive = $vertexConfig->isActive();

        // Assert
        // With the Configuration module in use, the environment flag no longer drives activation.
        $this->assertFalse($isActive);
    }

    public function testIsActiveUsesEnvironmentFlagWhenConfigurationModuleIsNotUsed(): void
    {
        // Arrange
        // The Back Office tax provider is ignored because the flag keeps activation on the environment configuration.
        $vertexConfig = $this->createVertexConfig(
            [static::CONFIGURATION_KEY_TAX_PROVIDER => static::TAX_PROVIDER_VERTEX],
            [static::ENVIRONMENT_KEY_IS_ACTIVE => true],
            false,
        );

        // Act
        $isActive = $vertexConfig->isActive();

        // Assert
        $this->assertTrue($isActive);
    }

    public function testIsActiveReturnsFalseWhenConfigurationModuleIsNotUsedAndEnvironmentFlagIsDisabled(): void
    {
        // Arrange
        $vertexConfig = $this->createVertexConfig(
            [static::CONFIGURATION_KEY_TAX_PROVIDER => static::TAX_PROVIDER_VERTEX],
            [static::ENVIRONMENT_KEY_IS_ACTIVE => false],
            false,
        );

        // Act
        $isActive = $vertexConfig->isActive();

        // Assert
        $this->assertFalse($isActive);
    }

    public function testGetTaxProviderReturnsBackOfficeSelectionWhenConfigurationModuleIsUsed(): void
    {
        // Arrange
        $vertexConfig = $this->createVertexConfig(
            [static::CONFIGURATION_KEY_TAX_PROVIDER => static::TAX_PROVIDER_VERTEX],
            [],
            true,
        );

        // Act
        $taxProvider = $vertexConfig->getTaxProvider();

        // Assert
        $this->assertSame(static::TAX_PROVIDER_VERTEX, $taxProvider);
    }

    public function testGetTaxProviderReturnsBackOfficeSelectionEvenWhenConfigurationModuleIsNotUsed(): void
    {
        // Arrange
        // The tax provider has no environment configuration source, so it is always read from the Back Office.
        $vertexConfig = $this->createVertexConfig(
            [static::CONFIGURATION_KEY_TAX_PROVIDER => static::TAX_PROVIDER_VERTEX],
            [],
            false,
        );

        // Act
        $taxProvider = $vertexConfig->getTaxProvider();

        // Assert
        $this->assertSame(static::TAX_PROVIDER_VERTEX, $taxProvider);
    }

    public function testGetTaxProviderReturnsSprykerByDefaultWhenNotConfigured(): void
    {
        // Arrange
        $vertexConfig = $this->createVertexConfig([], [], true);

        // Act
        $taxProvider = $vertexConfig->getTaxProvider();

        // Assert
        $this->assertSame(static::TAX_PROVIDER_SPRYKER, $taxProvider);
    }

    /**
     * @dataProvider countryCodeConfigGetterDataProvider
     */
    public function testCountryCodeGetterReturnsBackOfficeConfigurationWhenConfigurationModuleIsUsed(string $method, string $backOfficeConfigKey): void
    {
        // Arrange
        $vertexConfig = $this->createVertexConfig([$backOfficeConfigKey => static::BACK_OFFICE_VALUE], [], true);

        // Act
        $configValue = $vertexConfig->{$method}();

        // Assert
        $this->assertSame(static::BACK_OFFICE_VALUE, $configValue);
    }

    /**
     * @dataProvider countryCodeConfigGetterDataProvider
     */
    public function testCountryCodeGetterReturnsEmptyStringWhenNotConfigured(string $method): void
    {
        // Arrange
        $vertexConfig = $this->createVertexConfig([], [], true);

        // Act
        $configValue = $vertexConfig->{$method}();

        // Assert
        $this->assertSame('', $configValue);
    }

    /**
     * @return array<string, array{string, string, string}>
     */
    public function configGetterDataProvider(): array
    {
        return [
            'client id' => ['getClientId', 'VERTEX:CLIENT_ID', 'integrations:vertex:configurations:client_id'],
            'client secret' => ['getClientSecret', 'VERTEX:CLIENT_SECRET', 'integrations:vertex:configurations:client_secret'],
            'security uri' => ['getSecurityUri', 'VERTEX:SECURITY_URI', 'integrations:vertex:configurations:security_uri'],
            'transaction calls uri' => ['getTransactionCallsUri', 'VERTEX:TRANSACTION_CALLS_URI', 'integrations:vertex:configurations:transaction_calls_uri'],
            'taxamo api url' => ['getTaxamoApiUrl', 'VERTEX:TAXAMO_API_URL', 'integrations:vertex:taxamo:taxamo_api_url'],
            'taxamo token' => ['getTaxamoToken', 'VERTEX:TAXAMO_TOKEN', 'integrations:vertex:taxamo:taxamo_token'],
            'default taxpayer company code' => ['getDefaultTaxpayerCompanyCode', 'VERTEX:DEFAULT_TAXPAYER_COMPANY_CODE', 'integrations:vertex:configurations:default_taxpayer_company_code'],
            'vendor code' => ['getVendorCode', 'VERTEX:VENDOR_CODE', 'integrations:vertex:configurations:vendor_code'],
        ];
    }

    /**
     * @return array<string, array{string, string}>
     */
    public function countryCodeConfigGetterDataProvider(): array
    {
        return [
            'seller country code' => ['getSellerCountryCode', 'integrations:vertex:configurations:seller_country_code'],
            'customer country code' => ['getCustomerCountryCode', 'integrations:vertex:configurations:customer_country_code'],
        ];
    }

    /**
     * @param array<string, mixed> $backOfficeConfig
     * @param array<string, mixed> $environmentConfig
     */
    protected function createVertexConfig(array $backOfficeConfig, array $environmentConfig, bool $isConfigurationModuleUsed): VertexConfig
    {
        $vertexSharedConfig = Stub::make(VertexSharedConfig::class, [
            'isConfigurationModuleUsed' => $isConfigurationModuleUsed,
        ]);

        return Stub::make(VertexConfig::class, [
            // Simulates the Back Office configuration: returns the stored value or the passed default.
            'getModuleConfig' => function (string $key, mixed $default = null) use ($backOfficeConfig): mixed {
                return $backOfficeConfig[$key] ?? $default;
            },
            // Simulates the environment variable / PHP config value.
            'get' => function (string $key, mixed $default = null) use ($environmentConfig): mixed {
                return $environmentConfig[$key] ?? $default;
            },
            'getSharedConfig' => $vertexSharedConfig,
        ]);
    }
}
