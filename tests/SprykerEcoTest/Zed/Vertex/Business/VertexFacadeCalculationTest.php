<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEcoTest\Zed\Vertex\Business;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\StoreTransfer;
use Generated\Shared\Transfer\VertexAuthResponseTransfer;
use Generated\Shared\Transfer\VertexCalculationRequestTransfer;
use PHPUnit\Framework\Constraint\Callback;
use PHPUnit\Framework\MockObject\Rule\InvokedCount as InvokedCountMatcher;
use Ramsey\Uuid\Uuid;
use Spryker\Zed\Calculation\Communication\Plugin\Calculator\DiscountAmountAggregatorForGenericAmountPlugin;
use Spryker\Zed\Calculation\Communication\Plugin\Calculator\GrandTotalCalculatorPlugin;
use Spryker\Zed\Calculation\Communication\Plugin\Calculator\ItemDiscountAmountFullAggregatorPlugin;
use Spryker\Zed\Calculation\Communication\Plugin\Calculator\ItemSubtotalAggregatorPlugin;
use Spryker\Zed\Calculation\Communication\Plugin\Calculator\PriceCalculatorPlugin;
use SprykerEco\Client\Vertex\VertexClient;
use SprykerEco\Shared\Vertex\VertexConstants;
use SprykerEcoTest\Zed\Vertex\VertexBusinessTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerEcoTest
 * @group Zed
 * @group Vertex
 * @group Business
 * @group Facade
 * @group VertexFacadeCalculationTest
 * Add your own group annotations below this line
 */
class VertexFacadeCalculationTest extends Unit
{
    protected const PRICE_MODE_GROSS = 'GROSS_MODE';

    protected VertexBusinessTester $tester;

    protected StoreTransfer $storeTransfer;

    public function setUp(): void
    {
        parent::setUp();

        $this->storeTransfer = $this->tester->haveStore([StoreTransfer::COUNTRIES => ['US']], false);
        $this->tester->setQuoteTaxMetadataExpanderPlugins();
    }

    public function testCalculableObjectHasTaxTotalWhenRecalculateRequestsTaxFromExternalApiSuccessfully(): void
    {
        // Arrange
        $calculableObjectTransfer = $this->tester->createCalculableObjectTransfer($this->storeTransfer);

        $vertexCalculationResponseTransfer = $this->tester->haveVertexCalculationResponseTransfer(['isSuccessful' => true]);
        $this->tester->mockVertexClientWithVertexCalculationResponse($vertexCalculationResponseTransfer);

        // Act
        $this->tester->getFacade()->recalculate($calculableObjectTransfer);

        // Assert
        $this->assertGreaterThanOrEqual(0, $vertexCalculationResponseTransfer->getSale()->getTaxTotal());
        foreach ($calculableObjectTransfer->getExpenses() as $expense) {
            $this->assertNotNull($expense->getSumTaxAmount());
            $this->assertNotNull($expense->getUnitTaxAmount());
        }
    }

    public function testCalculableObjectHasTheSameTaxRequestHashWhenRecalculateWasCalledTwiceWithoutChanges(): void
    {
        // Arrange
        $calculableObjectTransfer = $this->tester->createCalculableObjectTransfer($this->storeTransfer);

        $vertexCalculationResponseTransfer = $this->tester->haveVertexCalculationResponseTransfer(['isSuccessful' => true]);

        $clientMock = $this->createMock(VertexClient::class);
        $clientMock->expects($this->once())->method('calculateQuoteTax')->willReturn($vertexCalculationResponseTransfer);
        $clientMock->expects($this->once())->method('authenticate')->willReturn(
            (new VertexAuthResponseTransfer())
                ->setAccessToken('test-token')
                ->setExpiresIn(1000),
        );
        $this->tester->mockFactoryMethod('getVertexClient', $clientMock);

        // Act
        $this->tester->getFacade()->recalculate($calculableObjectTransfer);

        $firstCalculationHash = $calculableObjectTransfer->getVertexSaleHash();

        $this->tester->getFacade()->recalculate($calculableObjectTransfer);

        $secondCalculationHash = $calculableObjectTransfer->getVertexSaleHash();

        $this->assertSame($firstCalculationHash, $secondCalculationHash);
    }

    public function testCalculableObjectHasDifferentTaxRequestHashWhenWasRecalculateCalledTwiceWithChanges(): void
    {
        // Arrange
        $calculableObjectTransfer = $this->tester->createCalculableObjectTransfer($this->storeTransfer);
        $vertexCalculationResponseTransfer = $this->tester->haveVertexCalculationResponseTransfer(['isSuccessful' => true]);

        $clientMock = $this->createMock(VertexClient::class);
        $clientMock->expects($this->exactly(2))->method('calculateQuoteTax')->willReturn($vertexCalculationResponseTransfer);
        $clientMock->expects($this->once())->method('authenticate')->willReturn(
            (new VertexAuthResponseTransfer())
                ->setAccessToken('test-token')
                ->setExpiresIn(1000),
        );
        $this->tester->mockFactoryMethod('getVertexClient', $clientMock);

        // Act
        $this->tester->getFacade()->recalculate($calculableObjectTransfer);

        $firstCalculationHash = $calculableObjectTransfer->getVertexSaleHash();

        $calculableObjectTransfer->getOriginalQuote()->setUuid(Uuid::uuid4()->toString());

        $this->tester->getFacade()->recalculate($calculableObjectTransfer);

        $secondCalculationHash = $calculableObjectTransfer->getVertexSaleHash();

        $this->assertNotEquals($firstCalculationHash, $secondCalculationHash);
    }

    public function testCalculableObjectHasZeroTaxTotalWhenShipmentIsMissingAndPriceModeIsNet(): void
    {
        // Arrange
        $calculableObjectTransfer = $this->tester->createCalculableObjectTransferWithoutShipment($this->storeTransfer);

        $clientMock = $this->createMock(VertexClient::class);
        $clientMock->expects($this->never())->method('calculateQuoteTax');
        $clientMock->expects($this->never())->method('authenticate')->willReturn(
            (new VertexAuthResponseTransfer())
                ->setAccessToken('test-token')
                ->setExpiresIn(1000),
        );
        $this->tester->mockFactoryMethod('getVertexClient', $clientMock);

        $originalQuote = $calculableObjectTransfer->getOriginalQuote();
        $originalQuote->setPriceMode('NET_MODE');

        $calculationFacade = $this->tester->createCalculationFacade(
            [
                new PriceCalculatorPlugin(),
                new ItemSubtotalAggregatorPlugin(),

                new DiscountAmountAggregatorForGenericAmountPlugin(),
                new ItemDiscountAmountFullAggregatorPlugin(),
            ],
        );
        $calculationFacade->recalculateQuote($originalQuote);
        $calculableObjectTransfer->setItems($originalQuote->getItems());

        // Act
        $this->tester->getFacade()->recalculate($calculableObjectTransfer);

        // Assert
        $this->assertSame(0, $calculableObjectTransfer->getTotals()->getTaxTotal()->getAmount());
    }

    public function testCalculableObjectHasSaleTransferExpandedWithMerchantStockAddressWhenRecalculateMethodIsCalled(): void
    {
        // Arrange
        $calculableObjectTransfer = $this->tester->haveCalculableObjectTransferWithMerchantStockAddress($this->storeTransfer);

        $vertexClientMock = $this->makeEmpty(VertexClient::class);
        $vertexCalculationResponseTransfer = $this->tester->haveVertexCalculationResponseTransfer(['isSuccessful' => true]);
        $vertexClientMock->expects($this->once())->method('calculateQuoteTax')->willReturn($vertexCalculationResponseTransfer);
        $vertexClientMock->expects($this->once())->method('authenticate')->willReturn(
            (new VertexAuthResponseTransfer())
                ->setAccessToken('test-token')
                ->setExpiresIn(1000),
        );
        $this->tester->mockFactoryMethod('getVertexClient', $vertexClientMock);

        // Assert
        $this->tester->assertRequestTaxQuotationReceivesSalesItemMappedWithMerchantStockAddress($vertexClientMock);

        // Act
        $this->tester->getFacade()->recalculate($calculableObjectTransfer);
    }

    public function testCalculableObjectHasSaleTransferWithItemsWhenMerchantStockAddressIsEmpty(): void
    {
        // Arrange
        $vertexClientMock = $this->makeEmpty(VertexClient::class);
        $vertexCalculationResponseTransfer = $this->tester->haveVertexCalculationResponseTransfer(['isSuccessful' => true]);
        $vertexClientMock->expects($this->once())->method('calculateQuoteTax')->willReturn($vertexCalculationResponseTransfer);
        $vertexClientMock->expects($this->once())->method('authenticate')->willReturn(
            (new VertexAuthResponseTransfer())
                ->setAccessToken('test-token')
                ->setExpiresIn(1000),
        );
        $this->tester->mockFactoryMethod('getVertexClient', $vertexClientMock);

        /** @var \Generated\Shared\Transfer\CalculableObjectTransfer $calculableObjectTransfer */
        $calculableObjectTransfer = $this->tester->createCalculableObjectTransfer($this->storeTransfer);

        // Assert
        $this->tester->assertRequestTaxQuotationReceivesSalesItemWithCorrectItemsAndWithoutWarehouseAddress(
            $vertexClientMock,
            $calculableObjectTransfer,
        );

        // Act
        $this->tester->getFacade()->recalculate($calculableObjectTransfer);
    }

    public function testQuoteHasCorrectGrandTotalWhenPriceModeIsNetAndRecalculateRequestsTaxFromExternalApiSuccessfully(): void
    {
        // Arrange
        $calculableObjectTransfer = $this->tester->createCalculableObjectTransfer($this->storeTransfer);
        $vertexCalculationResponseTransfer = $this->tester->haveVertexCalculationResponseTransfer(['isSuccessful' => true]);

        $this->tester->mockVertexClientWithVertexCalculationResponse($vertexCalculationResponseTransfer);

        $originalQuote = $calculableObjectTransfer->getOriginalQuote();

        $calculationFacade = $this->tester->createCalculationFacade(
            [
                new GrandTotalCalculatorPlugin(),
            ],
        );
        $calculationFacade->recalculateQuote($originalQuote);

        // Act
        $this->tester->getFacade()->recalculate($calculableObjectTransfer);

        // Assert
        $this->tester->assertQuoteHasCorrectGrandTotal($calculableObjectTransfer);
    }

    public function testQuoteHasZeroTaxTotalWhenRecalculateExternalApiRequestFails(): void
    {
        // Arrange
        $calculableObjectTransfer = $this->tester->createCalculableObjectTransfer($this->storeTransfer);

        $vertexCalculationResponseTransfer = $this->tester->haveVertexCalculationResponseTransfer(['isSuccessful' => false]);
        $this->tester->mockVertexClientWithVertexCalculationResponse($vertexCalculationResponseTransfer);

        $originalQuote = $calculableObjectTransfer->getOriginalQuote();

        $calculationFacade = $this->tester->createCalculationFacade(
            [
                new GrandTotalCalculatorPlugin(),
            ],
        );
        $calculationFacade->recalculateQuote($originalQuote);

        // Act
        $this->tester->getFacade()->recalculate($calculableObjectTransfer);

        // Assert
        $this->tester->assertQuoteHasZeroTaxTotal($calculableObjectTransfer);
    }

    public function testQuoteHasCorrectGrandTotalWhenPriceModeIsGrossAndRecalculateRequestsTaxFromExternalApiSuccessfully(): void
    {
        // Arrange
        $vertexCalculationResponseTransfer = $this->tester->haveVertexCalculationResponseTransfer(['isSuccessful' => true]);

        $this->tester->mockVertexClientWithVertexCalculationResponse($vertexCalculationResponseTransfer);

        $calculableObjectTransfer = $this->tester->createCalculableObjectTransfer($this->storeTransfer, static::PRICE_MODE_GROSS);

        $originalQuote = $calculableObjectTransfer->getOriginalQuote();

        $calculationFacade = $this->tester->createCalculationFacade(
            [
                new GrandTotalCalculatorPlugin(),
            ],
        );
        $calculationFacade->recalculateQuote($originalQuote);

        // Act
        $this->tester->getFacade()->recalculate($calculableObjectTransfer);

        // Assert
        $this->tester->assertQuoteHasCorrectGrandTotal($calculableObjectTransfer);
    }

    public function testQuoteHasHideTaxInCartFlagWhenVertexIsActive(): void
    {
        // Arrange
        $calculableObjectTransfer = $this->tester->createCalculableObjectTransfer($this->storeTransfer);
        $vertexCalculationResponseTransfer = $this->tester->haveVertexCalculationResponseTransfer(['isSuccessful' => true]);
        $this->tester->mockVertexClientWithVertexCalculationResponse($vertexCalculationResponseTransfer);

        // Act
        $this->tester->getFacade()->recalculate($calculableObjectTransfer);

        // Assert
        $this->assertTrue($calculableObjectTransfer->getOriginalQuote()->getHideTaxInCart());
    }

    public function testQuoteDoesNotHaveHideTaxInCartFlagWhenVertexIsNotActive(): void
    {
        // Arrange
        $storeTransfer = $this->tester->haveStore([StoreTransfer::NAME => 'Foo'], false);

        $calculableObjectTransfer = $this->tester->createCalculableObjectTransfer($storeTransfer);

        $this->tester->setConfig(VertexConstants::IS_ACTIVE, false);

        // Act
        $this->tester->getFacade()->recalculate($calculableObjectTransfer);

        // Assert
        $this->assertFalse($calculableObjectTransfer->getOriginalQuote()->getHideTaxInCart());
    }

    public function testCalculateObjectItemsHaveSumTaxAmountWhenStoreIdIsNotProvidedInCalculableObject(): void
    {
        // Arrange
        $storeTransfer = clone $this->storeTransfer;
        $storeTransfer->setIdStore(null);
        $calculableObjectTransfer = $this->tester->createCalculableObjectTransfer($storeTransfer);
        $vertexCalculationResponseTransfer = $this->tester->haveVertexCalculationResponseTransfer(['isSuccessful' => true], $calculableObjectTransfer->getItems()->getArrayCopy());
        $this->tester->mockVertexClientWithVertexCalculationResponse($vertexCalculationResponseTransfer);

        // Act
        $this->tester->getFacade()->recalculate($calculableObjectTransfer);

        // Assert
        $this->assertSame(
            $vertexCalculationResponseTransfer->getSale()->getItems()->offsetGet(0)->getTaxTotal(),
            $calculableObjectTransfer->getItems()->offsetGet(0)->getSumTaxAmount(),
        );
        $this->assertSame(
            $vertexCalculationResponseTransfer->getSale()->getItems()->offsetGet(1)->getTaxTotal(),
            $calculableObjectTransfer->getItems()->offsetGet(1)->getSumTaxAmount(),
        );
    }

    public function testRecalculateWithPriceInGrossModeAppliesExternalResultsCorrectly(): void
    {
        // Arrange
        $calculableObjectTransfer = $this->tester->createCalculableObjectTransfer($this->storeTransfer, static::PRICE_MODE_GROSS);
        $vertexCalculationResponseTransfer = $this->tester->haveVertexCalculationResponseTransfer(['isSuccessful' => true], $calculableObjectTransfer->getItems()->getArrayCopy());
        $this->tester->mockVertexClientWithVertexCalculationResponse($vertexCalculationResponseTransfer);

        // Act
        $this->tester->getFacade()->recalculate($calculableObjectTransfer);

        // Assert
        $this->assertSame(
            $vertexCalculationResponseTransfer->getSale()->getItems()->offsetGet(0)->getTaxTotal(),
            $calculableObjectTransfer->getItems()->offsetGet(0)->getSumTaxAmount(),
        );
        $this->assertSame(
            $vertexCalculationResponseTransfer->getSale()->getItems()->offsetGet(1)->getTaxTotal(),
            $calculableObjectTransfer->getItems()->offsetGet(1)->getSumTaxAmount(),
        );
    }

    public function testRecalculateWithPriceInGrossModeDoesNotHaveHideTaxInCartFlag(): void
    {
        // Arrange
        $vertexCalculationResponseTransfer = $this->tester->haveVertexCalculationResponseTransfer(['isSuccessful' => true]);
        $this->tester->mockVertexClientWithVertexCalculationResponse($vertexCalculationResponseTransfer);
        $calculableObjectTransfer = $this->tester->createCalculableObjectTransfer($this->storeTransfer, static::PRICE_MODE_GROSS);

        // Act
        $this->tester->getFacade()->recalculate($calculableObjectTransfer);

        // Assert
        $this->assertEmpty($calculableObjectTransfer->getOriginalQuote()->getHideTaxInCart());
    }

    public function testRecalculateWithNonConfiguredSellerCountryCodeIsTakenFromDefaultStoreCountry(): void
    {
        // Arrange
        $this->tester->mockConfigMethod('getSellerCountryCode', '');
        $expectedCountryCode = $this->storeTransfer->getCountries()[0];
        $vertexCalculationResponseTransfer = $this->tester->haveVertexCalculationResponseTransfer(['isSuccessful' => true]);

        $vertexClient = $this->createMock(VertexClient::class);
        $vertexClient->expects($this->once())->method('authenticate')->willReturn(
            (new VertexAuthResponseTransfer())
                ->setAccessToken('test-token')
                ->setExpiresIn(1000),
        );
        $this->tester->mockFactoryMethod('getVertexClient', $vertexClient);

        $calculableObjectTransfer = $this->tester->createCalculableObjectTransfer($this->storeTransfer);

        // Assert
        $vertexClient->expects(new InvokedCountMatcher(1))
            ->method('calculateQuoteTax')
            ->with(new Callback(function (VertexCalculationRequestTransfer $vertexCalculationRequestTransfer) use ($expectedCountryCode) {
                self::assertSame($expectedCountryCode, $vertexCalculationRequestTransfer->getSale()->getSellerCountryCode());

                return true;
            }))
            ->willReturn($vertexCalculationResponseTransfer);

        // Act
        $this->tester->getFacade()->recalculate($calculableObjectTransfer);
    }

    public function testRecalculateWithConfiguredSellerCountryCodeIsAppliedToTaxResponse(): void
    {
        // Arrange
        $this->tester->mockConfigMethod('getSellerCountryCode', 'FR');
        $vertexCalculationResponseTransfer = $this->tester->haveVertexCalculationResponseTransfer(['isSuccessful' => true]);

        $vertexClientMock = $this->createMock(VertexClient::class);
        $vertexClientMock->expects($this->once())->method('authenticate')->willReturn(
            (new VertexAuthResponseTransfer())
                ->setAccessToken('test-token')
                ->setExpiresIn(1000),
        );
        $this->tester->mockFactoryMethod('getVertexClient', $vertexClientMock);

        $calculableObjectTransfer = $this->tester->createCalculableObjectTransfer($this->storeTransfer);

        // Assert
        $vertexClientMock->expects(new InvokedCountMatcher(1))
            ->method('calculateQuoteTax')
            ->with(new Callback(function (VertexCalculationRequestTransfer $vertexCalculationRequestTransfer) {
                self::assertSame('FR', $vertexCalculationRequestTransfer->getSale()->getSellerCountryCode());

                return true;
            }))
            ->willReturn($vertexCalculationResponseTransfer);

        // Act
        $this->tester->getFacade()->recalculate($calculableObjectTransfer);
    }

    public function testRecalculateWithNonConfiguredCustomerCountryCodeIsTakenFromDefaultStoreCountry(): void
    {
        // Arrange
        $this->tester->mockConfigMethod('getCustomerCountryCode', '');
        $expectedCountryCode = $this->storeTransfer->getCountries()[0];
        $vertexCalculationResponseTransfer = $this->tester->haveVertexCalculationResponseTransfer(['isSuccessful' => true]);

        $vertexClientMock = $this->createMock(VertexClient::class);
        $vertexClientMock->expects($this->once())->method('authenticate')->willReturn(
            (new VertexAuthResponseTransfer())
                ->setAccessToken('test-token')
                ->setExpiresIn(1000),
        );
        $this->tester->mockFactoryMethod('getVertexClient', $vertexClientMock);

        $calculableObjectTransfer = $this->tester->createCalculableObjectTransfer($this->storeTransfer, 'GROSS_MODE', false);

        // Assert
        $vertexClientMock->expects(new InvokedCountMatcher(1))
            ->method('calculateQuoteTax')
            ->with(new Callback(function (VertexCalculationRequestTransfer $vertexCalculationRequestTransfer) use ($expectedCountryCode) {
                self::assertSame($expectedCountryCode, $vertexCalculationRequestTransfer->getSale()->getCustomerCountryCode());

                return true;
            }))
            ->willReturn($vertexCalculationResponseTransfer);

        // Act
        $this->tester->getFacade()->recalculate($calculableObjectTransfer);
    }

    public function testRecalculateWithConfiguredCustomerCountryCodeIsAppliedToTaxResponse(): void
    {
        // Arrange
        $this->tester->mockConfigMethod('getCustomerCountryCode', 'FR');
        $vertexCalculationResponseTransfer = $this->tester->haveVertexCalculationResponseTransfer(['isSuccessful' => true]);

        $vertexClientMock = $this->createMock(VertexClient::class);
        $vertexClientMock->expects($this->once())->method('authenticate')->willReturn(
            (new VertexAuthResponseTransfer())
                ->setAccessToken('test-token')
                ->setExpiresIn(1000),
        );
        $this->tester->mockFactoryMethod('getVertexClient', $vertexClientMock);

        $calculableObjectTransfer = $this->tester->createCalculableObjectTransfer($this->storeTransfer, 'GROSS_MODE', false);

        // Assert
        $vertexClientMock->expects(new InvokedCountMatcher(1))
            ->method('calculateQuoteTax')
            ->with(new Callback(function (VertexCalculationRequestTransfer $vertexCalculationRequestTransfer) {
                self::assertSame('FR', $vertexCalculationRequestTransfer->getSale()->getCustomerCountryCode());

                return true;
            }))
            ->willReturn($vertexCalculationResponseTransfer);

        // Act
        $this->tester->getFacade()->recalculate($calculableObjectTransfer);
    }

    public function testRecalculateWithProvidedBillingCountryIsSetToCustomerCountryCodeAppliedToTaxResponse(): void
    {
        // Arrange
        $this->tester->mockConfigMethod('getCustomerCountryCode', 'FR');
        $vertexCalculationResponseTransfer = $this->tester->haveVertexCalculationResponseTransfer(['isSuccessful' => true]);

        $vertexClientMock = $this->createMock(VertexClient::class);
        $vertexClientMock->expects($this->once())->method('authenticate')->willReturn(
            (new VertexAuthResponseTransfer())
                ->setAccessToken('test-token')
                ->setExpiresIn(1000),
        );
        $this->tester->mockFactoryMethod('getVertexClient', $vertexClientMock);

        $calculableObjectTransfer = $this->tester->createCalculableObjectTransfer($this->storeTransfer, 'GROSS_MODE', true, ['iso2Code' => 'FOO']);

        // Assert
        $vertexClientMock->expects(new InvokedCountMatcher(1))
            ->method('calculateQuoteTax')
            ->with(new Callback(function (VertexCalculationRequestTransfer $vertexCalculationRequestTransfer) {
                self::assertSame('FOO', $vertexCalculationRequestTransfer->getSale()->getCustomerCountryCode());

                return true;
            }))
            ->willReturn($vertexCalculationResponseTransfer);

        // Act
        $this->tester->getFacade()->recalculate($calculableObjectTransfer);
    }
}
