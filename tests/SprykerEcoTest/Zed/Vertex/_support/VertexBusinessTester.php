<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEcoTest\Zed\Vertex;

use Codeception\Actor;
use Codeception\Stub;
use Codeception\Stub\Expected;
use Generated\Shared\DataBuilder\AddressBuilder;
use Generated\Shared\DataBuilder\ExpenseBuilder;
use Generated\Shared\DataBuilder\ItemBuilder;
use Generated\Shared\DataBuilder\PriceProductBuilder;
use Generated\Shared\DataBuilder\QuoteBuilder;
use Generated\Shared\DataBuilder\ShipmentBuilder;
use Generated\Shared\DataBuilder\ShipmentMethodBuilder;
use Generated\Shared\DataBuilder\StockAddressBuilder;
use Generated\Shared\Transfer\AddressTransfer;
use Generated\Shared\Transfer\CalculableObjectTransfer;
use Generated\Shared\Transfer\CustomerTransfer;
use Generated\Shared\Transfer\ExpenseTransfer;
use Generated\Shared\Transfer\ItemTransfer;
use Generated\Shared\Transfer\MerchantStockAddressTransfer;
use Generated\Shared\Transfer\OrderTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\StockAddressTransfer;
use Generated\Shared\Transfer\StoreTransfer;
use Generated\Shared\Transfer\VertexAuthResponseTransfer;
use Generated\Shared\Transfer\VertexCalculationResponseTransfer;
use Generated\Shared\Transfer\VertexConfigTransfer;
use Orm\Zed\Vertex\Persistence\SpyVertexTaxIdValidationHistoryQuery;
use ReflectionProperty;
use Spryker\Shared\Oms\OmsConstants;
use Spryker\Zed\Calculation\Business\CalculationBusinessFactory;
use Spryker\Zed\Calculation\Business\CalculationFacade;
use Spryker\Zed\Calculation\CalculationDependencyProvider;
use Spryker\Zed\Calculation\Dependency\Service\CalculationToUtilTextBridge;
use Spryker\Zed\Kernel\Container;
use Spryker\Zed\Oms\Business\OrderStateMachine\PersistenceManager;
use SprykerEco\Client\Vertex\VertexClient;
use SprykerEco\Zed\Vertex\Business\Resolver\VertexConfigResolverInterface;
use SprykerEco\Zed\Vertex\VertexDependencyProvider;

/**
 * Inherited Methods
 *
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause($vars = [])
 * @method \SprykerEco\Zed\Vertex\Business\VertexBusinessFactory getFactory(?string $moduleName = NULL)
 * @method \SprykerEco\Zed\Vertex\Business\VertexFacade getFacade()
 *
 * @SuppressWarnings(\SprykerEcoTest\Zed\Vertex\PHPMD)
 */
class VertexBusinessTester extends Actor
{
    use _generated\VertexBusinessTesterActions;

    public function assertTaxIdValidationHistoryEntryDoesNotExist(string $taxId, string $countryCode, string $responseData): void
    {
        $taxIdValidationHistoryEntity = $this->getVertexTaxIdValidationHistoryQuery()
            ->filterByTaxId($taxId)
            ->filterByCountryCode($countryCode)
            ->findOne();

        $this->assertNotNull($taxIdValidationHistoryEntity);
        $this->assertSame($responseData, $taxIdValidationHistoryEntity->getResponseData());
    }

    public function setQuoteTaxMetadataExpanderPlugins(): void
    {
        $this->setDependency(
            VertexDependencyProvider::PLUGINS_CALCULABLE_OBJECT_VERTEX_EXPANDER,
            [],
        );
    }

    /**
     * @param \Generated\Shared\Transfer\StoreTransfer $storeTransfer
     * @param string $priceMode
     * @param bool $withBillingAddress
     * @param array<mixed> $billingAddressSeed
     *
     * @return \Generated\Shared\Transfer\CalculableObjectTransfer
     */
    public function createCalculableObjectTransfer(
        StoreTransfer $storeTransfer,
        string $priceMode = 'NET_MODE',
        bool $withBillingAddress = true,
        array $billingAddressSeed = [],
    ): CalculableObjectTransfer {
        $merchantTransfer1 = $this->haveMerchant();
        $this->haveMerchantProfile($merchantTransfer1);
        $merchantTransfer2 = $this->haveMerchant();
        $this->haveMerchantProfile($merchantTransfer2);

        $addressBuilder = (new AddressBuilder([AddressTransfer::ISO2_CODE => 'FR']));

        $shipmentBuilder = (new ShipmentBuilder())
            ->withShippingAddress($addressBuilder)
            ->withMethod();

        $expenseBuilder = (new ExpenseBuilder([
            ExpenseTransfer::TYPE => 'SHIPMENT_EXPENSE_TYPE',
        ]))->withShipment($shipmentBuilder);

        $customExpenseBuilder = (new ExpenseBuilder([
            ExpenseTransfer::TYPE => 'CUSTOM_EXPENSE_TYPE',
            ExpenseTransfer::UNIT_TAX_AMOUNT => null,
            ExpenseTransfer::SUM_TAX_AMOUNT => null,
        ]));

        $quoteBuilder = (new QuoteBuilder())
            ->withCustomer()
            ->withItem(
                (new ItemBuilder([ItemTransfer::MERCHANT_REFERENCE => $merchantTransfer1->getMerchantReference()]))
                    ->withShipment(
                        (new ShipmentBuilder())
                            ->withAnotherShippingAddress(),
                    ),
            )
            ->withAnotherItem(
                (new ItemBuilder([ItemTransfer::MERCHANT_REFERENCE => $merchantTransfer2->getMerchantReference()]))
                    ->withAnotherShipment(
                        (new ShipmentBuilder())
                            ->withAnotherShippingAddress(),
                    ),
            )
            ->withTotals()
            ->withExpense($expenseBuilder)
            ->withExpense($customExpenseBuilder);

        if ($withBillingAddress) {
            $quoteBuilder->withBillingAddress($billingAddressSeed);
        }

        $quoteTransfer = $quoteBuilder->build();

        $quoteTransfer->setStore($storeTransfer);
        $quoteTransfer->setPriceMode($priceMode);

        $calculableObjectTransfer = (new CalculableObjectTransfer())->fromArray($quoteTransfer->toArray(), true);
        $calculableObjectTransfer->setOriginalQuote($quoteTransfer);

        return $calculableObjectTransfer;
    }

    public function createCalculableObjectTransferWithoutShipment(StoreTransfer $storeTransfer): CalculableObjectTransfer
    {
        $merchantTransfer1 = $this->haveMerchant();
        $this->haveMerchantProfile($merchantTransfer1);
        $merchantTransfer2 = $this->haveMerchant();
        $this->haveMerchantProfile($merchantTransfer2);

        $quoteTransfer = (new QuoteBuilder())
            ->withCustomer()
            ->withBillingAddress()
            ->withItem(
                (new ItemBuilder([ItemTransfer::MERCHANT_REFERENCE => $merchantTransfer1->getMerchantReference()]))
                    ->withPriceProduct(new PriceProductBuilder()),
            )
            ->withAnotherItem(
                (new ItemBuilder([ItemTransfer::MERCHANT_REFERENCE => $merchantTransfer2->getMerchantReference()]))
                    ->withPriceProduct(new PriceProductBuilder()),
            )
            ->withTotals()
            ->build();

        $quoteTransfer->setStore($storeTransfer);
        $quoteTransfer->setPriceMode('NET_MODE');

        $calculableObjectTransfer = (new CalculableObjectTransfer())->fromArray($quoteTransfer->toArray(), true);
        $calculableObjectTransfer->setOriginalQuote($quoteTransfer);

        return $calculableObjectTransfer;
    }

    public function ensureVertexTaxIdValidationHistoryTableIsEmpty(): void
    {
        $this->ensureDatabaseTableIsEmpty($this->getVertexTaxIdValidationHistoryQuery());
    }

    protected function getVertexTaxIdValidationHistoryQuery(): SpyVertexTaxIdValidationHistoryQuery
    {
        return SpyVertexTaxIdValidationHistoryQuery::create();
    }

    protected function clearPersistenceManagerCache(): void
    {
        $stateCacheProperty = new ReflectionProperty(PersistenceManager::class, 'stateCache');
        $stateCacheProperty->setAccessible(true);
        $stateCacheProperty->setValue([]);
        $processCacheProperty = new ReflectionProperty(PersistenceManager::class, 'processCache');
        $processCacheProperty->setAccessible(true);
        $processCacheProperty->setValue([]);
    }

    public function configureTestStateMachine(array $activeProcesses, ?string $xmlFolder = null): void
    {
        $this->clearPersistenceManagerCache();

        if (!$xmlFolder) {
            $xmlFolder = realpath(__DIR__ . '/../../../../../_data/state-machine/');
        }

        $this->setConfig(OmsConstants::PROCESS_LOCATION, $xmlFolder);
        $this->setConfig(OmsConstants::ACTIVE_PROCESSES, $activeProcesses);
    }

    public function haveCalculableObjectTransferWithMerchantStockAddress(StoreTransfer $storeTransfer): CalculableObjectTransfer
    {
        $merchantTransfer1 = $this->haveMerchant();
        $this->haveMerchantProfile($merchantTransfer1);
        $merchantTransfer2 = $this->haveMerchant();
        $this->haveMerchantProfile($merchantTransfer2);

        $addressBuilder = (new AddressBuilder([AddressTransfer::ISO2_CODE => 'FR']));

        $shipmentBuilder = (new ShipmentBuilder())
            ->withShippingAddress($addressBuilder)
            ->withMethod();

        $expenseBuilder = (new ExpenseBuilder([
            ExpenseTransfer::TYPE => 'SHIPMENT_EXPENSE_TYPE',
        ]))->withShipment($shipmentBuilder);

        $quoteTransfer = (new QuoteBuilder())
            ->withItem(
                (new ItemBuilder([
                    ItemTransfer::MERCHANT_REFERENCE => $merchantTransfer1->getMerchantReference(),
                    ItemTransfer::MERCHANT_STOCK_ADDRESSES => [
                        [
                            MerchantStockAddressTransfer::QUANTITY_TO_SHIP => 3,
                            MerchantStockAddressTransfer::STOCK_ADDRESS => (new StockAddressBuilder(
                                [
                                    StockAddressTransfer::ADDRESS1 => 'address-1-1',
                                    StockAddressTransfer::CITY => 'city-1-1',
                                    StockAddressTransfer::ZIP_CODE => 'zipcode-1-1',
                                ],
                            ))->withCountry()->build(),
                        ],
                        [
                            MerchantStockAddressTransfer::QUANTITY_TO_SHIP => 1,
                            MerchantStockAddressTransfer::STOCK_ADDRESS => (new StockAddressBuilder(
                                [
                                    StockAddressTransfer::ADDRESS1 => 'address-1-2',
                                    StockAddressTransfer::CITY => 'city-1-2',
                                    StockAddressTransfer::ZIP_CODE => 'zipcode-1-2',
                                ],
                            ))->withCountry()->build(),
                        ],
                    ],
                ])),
            )
            ->withAnotherItem(
                (new ItemBuilder([
                    ItemTransfer::MERCHANT_REFERENCE => $merchantTransfer2->getMerchantReference(),
                    ItemTransfer::MERCHANT_STOCK_ADDRESSES => [
                        [
                            MerchantStockAddressTransfer::QUANTITY_TO_SHIP => 10,
                            MerchantStockAddressTransfer::STOCK_ADDRESS => (new StockAddressBuilder(
                                [
                                    StockAddressTransfer::ADDRESS1 => 'address-2-1',
                                    StockAddressTransfer::CITY => 'city-2-1',
                                    StockAddressTransfer::ZIP_CODE => 'zipcode-2-1',
                                ],
                            ))->withCountry()->build(),
                        ],
                    ],
                ])),
            )
            ->withTotals()
            ->withExpense($expenseBuilder)
            ->build();

        $quoteTransfer->setStore($storeTransfer);
        $quoteTransfer->setPriceMode('NET_MODE');

        $calculableObjectTransfer = (new CalculableObjectTransfer())->fromArray($quoteTransfer->toArray(), true);
        $calculableObjectTransfer->setOriginalQuote($quoteTransfer);

        return $calculableObjectTransfer;
    }

    public function mockVertexConfigResolver(bool $isActive = true): VertexConfigTransfer
    {
        $vertexConfigTransfer = (new VertexConfigTransfer())
            ->setIsActive($isActive)
            ->setCredentialHash('test')
            ->setIsInvoicingEnabled(true)
            ->setIsTaxAssistEnabled(true)
            ->setIsTaxIdValidatorEnabled(true);

        $vertexConfigResolverMock = Stub::makeEmpty(VertexConfigResolverInterface::class, ['resolve' => $vertexConfigTransfer]);
        $this->mockFactoryMethod('createVertexConfigResolver', $vertexConfigResolverMock);

        return $vertexConfigTransfer;
    }

    public function mockVertexClientWithVertexCalculationResponse(VertexCalculationResponseTransfer $vertexCalculationResponseTransfer): void
    {
        $vertexClientMock = Stub::makeEmpty(VertexClient::class);
        $vertexClientMock->expects(Expected::once()->getMatcher())->method('calculateTax')->willReturn($vertexCalculationResponseTransfer);
        $vertexClientMock->expects(Expected::once()->getMatcher())->method('authenticate')->willReturn(
            (new VertexAuthResponseTransfer())
                ->setAccessToken('some-access-token')
                ->setExpiresIn(100000),
        );
        $this->setDependency('CLIENT_VERTEX', $vertexClientMock);
    }

    /**
     * @param array $calculatorPlugins
     *
     * @return \Spryker\Zed\Calculation\Business\CalculationFacade
     */
    public function createCalculationFacade(array $calculatorPlugins): CalculationFacade
    {
        $calculationFacade = new CalculationFacade();

        $calculationBusinessFactory = new CalculationBusinessFactory();

        $container = new Container();
        $container[CalculationDependencyProvider::QUOTE_CALCULATOR_PLUGIN_STACK] = function () use ($calculatorPlugins) {
            return $calculatorPlugins;
        };

        $container[CalculationDependencyProvider::PLUGINS_QUOTE_POST_RECALCULATE] = function () {
            return [];
        };

        $container->set(CalculationDependencyProvider::SERVICE_UTIL_TEXT, function (Container $container) {
            return new CalculationToUtilTextBridge($container->getLocator()->utilText()->service());
        });

        $calculationBusinessFactory->setContainer($container);
        $calculationFacade->setFactory($calculationBusinessFactory);

        return $calculationFacade;
    }

    public function createOrderByStateMachineProcessName(string $stateMachineProcessName, StoreTransfer $storeTransfer): OrderTransfer
    {
        $quoteTransfer = $this->buildFakeQuote(
            $this->haveCustomer(),
            $storeTransfer,
        );

        $saveOrderTransfer = $this->haveOrderFromQuote($quoteTransfer, $stateMachineProcessName);

        return (new OrderTransfer())
            ->setIdSalesOrder($saveOrderTransfer->getIdSalesOrder())
            ->setOrderReference($saveOrderTransfer->getOrderReference())
            ->setStore($quoteTransfer->getStore()->getName())
            ->setCustomer($quoteTransfer->getCustomer())
            ->setItems($saveOrderTransfer->getOrderItems())
            ->setExpenses($quoteTransfer->getExpenses())
            ->setBillingAddress($quoteTransfer->getBillingAddress());
    }

    protected function buildFakeQuote(CustomerTransfer $customerTransfer, StoreTransfer $storeTransfer): QuoteTransfer
    {
        $shipmentBuilder = (new ShipmentBuilder())
            ->withShippingAddress()
            ->withMethod((new ShipmentMethodBuilder())->withPrice());

        $expenseBuilder = (new ExpenseBuilder([ExpenseTransfer::TYPE => 'SHIPMENT_EXPENSE_TYPE']))
            ->withShipment($shipmentBuilder);

        /** @var \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer */
        $quoteTransfer = (new QuoteBuilder())
            ->withItem((new ItemBuilder())->withShipment($shipmentBuilder))
            ->withShipment($shipmentBuilder)
            ->withTotals()
            ->withShippingAddress()
            ->withBillingAddress()
            ->withCurrency()
            ->withExpense($expenseBuilder)
            ->build();

        $quoteTransfer
            ->setPriceMode('NET_MODE')
            ->setCustomer($customerTransfer)
            ->setStore($storeTransfer);

        return $quoteTransfer;
    }
}
