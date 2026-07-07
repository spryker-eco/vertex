<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEcoTest\Zed\Vertex\Business\Mapper;

use Codeception\Stub;
use Codeception\Test\Unit;
use Generated\Shared\Transfer\CalculableObjectTransfer;
use Generated\Shared\Transfer\ConfigurationScopeTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\StoreTransfer;
use Generated\Shared\Transfer\VertexSaleTransfer;
use Spryker\Shared\Store\StoreConstants;
use Spryker\Zed\Store\Business\StoreFacadeInterface;
use SprykerEco\Zed\Vertex\Business\Mapper\Addresses\AddressMapperInterface;
use SprykerEco\Zed\Vertex\Business\Mapper\Prices\ItemExpensePriceRetrieverInterface;
use SprykerEco\Zed\Vertex\Business\Mapper\VertexMapper;
use SprykerEco\Zed\Vertex\VertexConfig;

/**
 * Auto-generated group annotations
 *
 * @group SprykerEcoTest
 * @group Zed
 * @group Vertex
 * @group Business
 * @group Mapper
 * @group VertexMapperTest
 * Add your own group annotations below this line
 */
class VertexMapperTest extends Unit
{
    protected const string STORE_NAME = 'DE';

    public function testSetTaxSaleCountryCodePassesStoreScopeToVertexConfigWhenCurrentStoreIsDefined(): void
    {
        // Arrange
        $expectedConfigurationScopeTransfers = [
            (new ConfigurationScopeTransfer())
                ->setKey(StoreConstants::SCOPE_STORE)
                ->setIdentifier(static::STORE_NAME),
        ];

        $vertexMapper = $this->createVertexMapper(
            $this->createVertexConfigMock($expectedConfigurationScopeTransfers),
            $this->createStoreFacadeMock(true, static::STORE_NAME),
        );

        // Act & Assert
        $vertexMapper->setTaxSaleCountryCode(
            $this->createCalculableObjectTransfer(),
            new VertexSaleTransfer(),
            new QuoteTransfer(),
        );
    }

    public function testSetTaxSaleCountryCodePassesEmptyScopeToVertexConfigWhenCurrentStoreIsNotDefined(): void
    {
        // Arrange
        $vertexMapper = $this->createVertexMapper(
            $this->createVertexConfigMock([]),
            $this->createStoreFacadeMock(false),
        );

        // Act & Assert
        $vertexMapper->setTaxSaleCountryCode(
            $this->createCalculableObjectTransfer(),
            new VertexSaleTransfer(),
            new QuoteTransfer(),
        );
    }

    protected function createVertexMapper(VertexConfig $vertexConfig, StoreFacadeInterface $storeFacade): VertexMapper
    {
        return new VertexMapper(
            Stub::makeEmpty(AddressMapperInterface::class),
            Stub::makeEmpty(ItemExpensePriceRetrieverInterface::class),
            $storeFacade,
            $vertexConfig,
        );
    }

    protected function createCalculableObjectTransfer(): CalculableObjectTransfer
    {
        return (new CalculableObjectTransfer())->setStore(
            (new StoreTransfer())->setName(static::STORE_NAME)->setCountries(['DE']),
        );
    }

    /**
     * @param array<\Generated\Shared\Transfer\ConfigurationScopeTransfer> $expectedConfigurationScopeTransfers
     */
    protected function createVertexConfigMock(array $expectedConfigurationScopeTransfers): VertexConfig
    {
        $assertsScopeAndReturnsEmptyString = function (array $configurationScopeTransfers = []) use ($expectedConfigurationScopeTransfers): string {
            $this->assertEquals($expectedConfigurationScopeTransfers, $configurationScopeTransfers);

            return '';
        };

        return Stub::make(VertexConfig::class, [
            'getSellerCountryCode' => $assertsScopeAndReturnsEmptyString,
            'getCustomerCountryCode' => $assertsScopeAndReturnsEmptyString,
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
}
