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

    /**
     * @dataProvider configGetterDataProvider
     */
    public function testReturnsBackOfficeConfigurationWhenBackOfficeAndEnvironmentConfigurationsAreSet(
        string $method,
        string $environmentConfigKey,
        string $backOfficeConfigKey,
    ): void {
        // Arrange
        $vertexConfig = $this->createVertexConfig(
            [$backOfficeConfigKey => static::BACK_OFFICE_VALUE],
            [$environmentConfigKey => static::ENVIRONMENT_VALUE],
        );

        // Act
        $configValue = $vertexConfig->{$method}();

        // Assert
        $this->assertSame(static::BACK_OFFICE_VALUE, $configValue);
    }

    /**
     * @dataProvider configGetterDataProvider
     */
    public function testReturnsEnvironmentConfigurationWhenBackOfficeConfigurationIsNotSet(
        string $method,
        string $environmentConfigKey,
    ): void {
        // Arrange
        $vertexConfig = $this->createVertexConfig(
            [],
            [$environmentConfigKey => static::ENVIRONMENT_VALUE],
        );

        // Act
        $configValue = $vertexConfig->{$method}();

        // Assert
        $this->assertSame(static::ENVIRONMENT_VALUE, $configValue);
    }

    public function testIsActiveReturnsTrueWhenVertexTaxProviderIsSelectedInBackOffice(): void
    {
        // Arrange
        // Back Office activation via the Vertex tax provider wins over the disabled environment flag.
        $vertexConfig = $this->createVertexConfig(
            ['taxes:tax_provider:provider:tax_provider' => 'vertex'],
            [static::ENVIRONMENT_KEY_IS_ACTIVE => false],
        );

        // Act
        $isActive = $vertexConfig->isActive();

        // Assert
        $this->assertTrue($isActive);
    }

    public function testIsActiveFallsBackToEnvironmentFlagWhenBackOfficeTaxProviderIsNotSelected(): void
    {
        // Arrange
        // Without a Back Office tax provider the environment flag drives activation as a fallback.
        $vertexConfig = $this->createVertexConfig(
            [],
            [static::ENVIRONMENT_KEY_IS_ACTIVE => true],
        );

        // Act
        $isActive = $vertexConfig->isActive();

        // Assert
        $this->assertTrue($isActive);
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
        ];
    }

    /**
     * @param array<string, mixed> $backOfficeConfig
     * @param array<string, mixed> $environmentConfig
     */
    protected function createVertexConfig(array $backOfficeConfig, array $environmentConfig): VertexConfig
    {
        return Stub::make(VertexConfig::class, [
            // Simulates the Back Office configuration: returns the stored value or the passed env fallback default.
            'getModuleConfig' => function (string $key, mixed $default = null) use ($backOfficeConfig): mixed {
                return $backOfficeConfig[$key] ?? $default;
            },
            // Simulates the environment variable / PHP config value.
            'get' => function (string $key, mixed $default = null) use ($environmentConfig): mixed {
                return $environmentConfig[$key] ?? $default;
            },
            // The shared config cannot be resolved by the mock class name, so provide a real instance for its constants.
            'getSharedConfig' => new VertexSharedConfig(),
        ]);
    }
}
