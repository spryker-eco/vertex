<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEcoTest\Zed\Vertex\Business\Resolver;

use Codeception\Stub;
use Codeception\Test\Unit;
use Generated\Shared\Transfer\ConfigurationScopeTransfer;
use Generated\Shared\Transfer\StoreTransfer;
use Generated\Shared\Transfer\VertexValidationResponseTransfer;
use Spryker\Shared\Store\StoreConstants;
use Spryker\Zed\Store\Business\StoreFacadeInterface;
use SprykerEco\Zed\Vertex\Business\Resolver\VertexConfigResolver;
use SprykerEco\Zed\Vertex\Business\Validator\VertexConfigValidatorInterface;
use SprykerEco\Zed\Vertex\VertexConfig;

/**
 * Auto-generated group annotations
 *
 * @group SprykerEcoTest
 * @group Zed
 * @group Vertex
 * @group Business
 * @group Resolver
 * @group VertexConfigResolverTest
 * Add your own group annotations below this line
 */
class VertexConfigResolverTest extends Unit
{
    protected const string STORE_NAME = 'DE';

    public function testResolvePassesStoreScopeToVertexConfigWhenCurrentStoreIsDefined(): void
    {
        // Arrange
        $expectedConfigurationScopeTransfers = [
            (new ConfigurationScopeTransfer())
                ->setKey(StoreConstants::SCOPE_STORE)
                ->setIdentifier(static::STORE_NAME),
        ];

        $vertexConfigResolver = new VertexConfigResolver(
            $this->createVertexConfigMock($expectedConfigurationScopeTransfers),
            $this->createStoreFacadeMock(true, static::STORE_NAME),
            $this->createVertexConfigValidatorMock(),
        );

        // Act & Assert
        $vertexConfigResolver->resolve();
    }

    public function testResolvePassesEmptyScopeToVertexConfigWhenCurrentStoreIsNotDefined(): void
    {
        // Arrange
        $vertexConfigResolver = new VertexConfigResolver(
            $this->createVertexConfigMock([]),
            $this->createStoreFacadeMock(false),
            $this->createVertexConfigValidatorMock(),
        );

        // Act & Assert
        $vertexConfigResolver->resolve();
    }

    /**
     * @param array<\Generated\Shared\Transfer\ConfigurationScopeTransfer> $expectedConfigurationScopeTransfers
     */
    protected function createVertexConfigMock(array $expectedConfigurationScopeTransfers): VertexConfig
    {
        $assertsScopeAndReturnsString = function (array $configurationScopeTransfers = []) use ($expectedConfigurationScopeTransfers): string {
            $this->assertEquals($expectedConfigurationScopeTransfers, $configurationScopeTransfers);

            return '';
        };

        $assertsScopeAndReturnsBool = function (array $configurationScopeTransfers = []) use ($expectedConfigurationScopeTransfers): bool {
            $this->assertEquals($expectedConfigurationScopeTransfers, $configurationScopeTransfers);

            return false;
        };

        return Stub::make(VertexConfig::class, [
            'getClientId' => $assertsScopeAndReturnsString,
            'getClientSecret' => $assertsScopeAndReturnsString,
            'getSecurityUri' => $assertsScopeAndReturnsString,
            'getTransactionCallsUri' => $assertsScopeAndReturnsString,
            'isActive' => $assertsScopeAndReturnsBool,
            'isTaxIdValidatorEnabled' => false,
            'isTaxAssistEnabled' => $assertsScopeAndReturnsBool,
            'getTaxamoToken' => $assertsScopeAndReturnsString,
            'getTaxamoApiUrl' => $assertsScopeAndReturnsString,
            'isInvoicingEnabled' => $assertsScopeAndReturnsBool,
            'getVendorCode' => $assertsScopeAndReturnsString,
            'getDefaultTaxpayerCompanyCode' => $assertsScopeAndReturnsString,
        ]);
    }

    protected function createStoreFacadeMock(bool $isCurrentStoreDefined, ?string $storeName = null): StoreFacadeInterface
    {
        $storeFacadeMock = $this->getMockBuilder(StoreFacadeInterface::class)->getMock();
        $storeFacadeMock->method('isCurrentStoreDefined')->willReturn($isCurrentStoreDefined);

        if ($storeName === null) {
            $storeFacadeMock->expects($this->never())->method('getCurrentStore');

            return $storeFacadeMock;
        }

        $storeFacadeMock->method('getCurrentStore')->willReturn((new StoreTransfer())->setName($storeName));

        return $storeFacadeMock;
    }

    protected function createVertexConfigValidatorMock(): VertexConfigValidatorInterface
    {
        $vertexConfigValidatorMock = $this->getMockBuilder(VertexConfigValidatorInterface::class)->getMock();
        $vertexConfigValidatorMock->method('validate')->willReturn((new VertexValidationResponseTransfer())->setIsValid(true));

        return $vertexConfigValidatorMock;
    }
}
