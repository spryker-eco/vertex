<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEcoTest\Shared\Vertex\Helper;

use ArrayObject;
use Codeception\Module;
use Generated\Shared\DataBuilder\AddressBuilder;
use Generated\Shared\DataBuilder\CalculableObjectBuilder;
use Generated\Shared\DataBuilder\ItemBuilder;
use Generated\Shared\DataBuilder\OrderBuilder;
use Generated\Shared\DataBuilder\TaxAppAddressBuilder;
use Generated\Shared\DataBuilder\TaxAppConfigBuilder;
use Generated\Shared\DataBuilder\TaxAppConfigConditionsBuilder;
use Generated\Shared\DataBuilder\TaxAppConfigCriteriaBuilder;
use Generated\Shared\DataBuilder\TaxAppSaleBuilder;
use Generated\Shared\DataBuilder\TaxRefundRequestBuilder;
use Generated\Shared\DataBuilder\VertexCalculationRequestBuilder;
use Generated\Shared\DataBuilder\VertexCalculationResponseBuilder;
use Generated\Shared\DataBuilder\VertexValidationRequestBuilder;
use Generated\Shared\Transfer\CalculableObjectTransfer;
use Generated\Shared\Transfer\OrderTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\StoreTransfer;
use Generated\Shared\Transfer\TaxAppConfigConditionsTransfer;
use Generated\Shared\Transfer\TaxAppConfigCriteriaTransfer;
use Generated\Shared\Transfer\TaxAppConfigTransfer;
use Generated\Shared\Transfer\TaxAppSaleTransfer;
use Generated\Shared\Transfer\TaxRefundRequestTransfer;
use Generated\Shared\Transfer\VertexCalculationRequestTransfer;
use Generated\Shared\Transfer\VertexCalculationResponseTransfer;
use Generated\Shared\Transfer\VertexValidationRequestTransfer;
use SprykerTest\Shared\Testify\Helper\DataCleanupHelperTrait;
use SprykerTest\Shared\Testify\Helper\LocatorHelperTrait;
use SprykerTest\Shared\Testify\Helper\TableRelationsCleanupHelperTrait;
use SprykerTest\Zed\Testify\Helper\Business\DependencyProviderHelperTrait;

class VertexDataHelper extends Module
{
    use LocatorHelperTrait;
    use DataCleanupHelperTrait;
    use DependencyProviderHelperTrait;
    use TableRelationsCleanupHelperTrait;

    /**
     * @param array $seed
     *
     * @return \Generated\Shared\Transfer\TaxAppConfigTransfer
     */
    public function createTaxAppConfigTransfer(array $seed = []): TaxAppConfigTransfer
    {
        return (new TaxAppConfigBuilder())->seed($seed)->withApiUrls()->build();
    }

    /**
     * @param array $seed
     *
     * @return \Generated\Shared\Transfer\TaxAppValidationRequestTransfer
     */
    public function createVertexValidationRequestTransfer(array $seed = []): VertexValidationRequestTransfer
    {
        return (new VertexValidationRequestBuilder())->seed($seed)->build();
    }

    /**
     * @param array $seed
     *
     * @return \Generated\Shared\Transfer\TaxAppConfigCriteriaTransfer
     */
    public function createTaxAppConfigCriteriaTransfer(array $seed = []): TaxAppConfigCriteriaTransfer
    {
        return (new TaxAppConfigCriteriaBuilder())->seed($seed)->build();
    }

    /**
     * @param array $seed
     *
     * @return \Generated\Shared\Transfer\TaxAppConfigCriteriaTransfer
     */
    public function createTaxAppConfigCriteriaTransferWithTaxAppConfigConditionsTransfer(array $seed = []): TaxAppConfigCriteriaTransfer
    {
        $taxAppConfigCriteriaTransfer = $this->createTaxAppConfigCriteriaTransfer($seed);
        $taxAppConfigConditionsTransfer = $this->createTaxAppConfigConditionsTransfer($seed);

        return $taxAppConfigCriteriaTransfer->setTaxAppConfigConditions($taxAppConfigConditionsTransfer);
    }

    /**
     * @param array $seed
     *
     * @return \Generated\Shared\Transfer\TaxAppConfigConditionsTransfer
     */
    public function createTaxAppConfigConditionsTransfer(array $seed = []): TaxAppConfigConditionsTransfer
    {
        return (new TaxAppConfigConditionsBuilder())->seed($seed)->build();
    }

    public function createStoreTransferWithStoreReference(): StoreTransfer
    {
        return (new StoreTransfer())
            ->setName('test_store_name')
            ->setIdStore(1)
            ->setStoreReference('test_store_reference');
    }

    public function assertTaxAppWithVendorCodeIsConfigured(string $vendorCode, ?int $idStore = null, ?bool $isActive = null): void
    {
        $taxAppConfigEntity = $this->findTaxAppConfigByVendorCode($vendorCode);

        $this->assertNotNull($taxAppConfigEntity, sprintf('Expected to find a Tax App configuration for the vendor with vendor code "%s" but it was not found.', $vendorCode));
        $this->assertSame($taxAppConfigEntity->getFkStore(), $idStore, sprintf('Expected for a Tax App configuration to have the store with id "%s" but it has "%s".', $idStore, $taxAppConfigEntity->getFkStore()));

        if ($isActive === null) {
            return;
        }

        $this->assertSame($taxAppConfigEntity->getIsActive(), $isActive, sprintf('Expected for a Tax App configuration to have the isActive flag "%b" but it has "%b".', $isActive, $taxAppConfigEntity->getIsActive()));
    }

    /**
     * @param string $vendorCode
     * @param array<\Generated\Shared\Transfer\StoreTransfer> $allowedStores
     *
     * @return void
     */
    public function assertTaxAppWithVendorCodeIsConfiguredWithMultipleStores(string $vendorCode, array $allowedStores = []): void
    {
        $taxAppConfigEntities = $this->getTaxAppConfigQuery()
            ->filterByVendorCode($vendorCode)
            ->find();

        $this->assertSame(count($allowedStores), $taxAppConfigEntities->count(), sprintf('Expected to find Tax App configurations with vendor code "%s" but it was not found.', $vendorCode));

        $countAppConfigEntities = 0;
        foreach ($taxAppConfigEntities as $taxAppConfigEntity) {
            foreach ($allowedStores as $allowedStore) {
                if ($taxAppConfigEntity->getFkStore() !== $allowedStore->getIdStore()) {
                    continue;
                }

                $this->assertSame($taxAppConfigEntity->getFkStore(), $allowedStore->getIdStore(), sprintf('Expected for a Tax App configuration to have the store with id "%s" but it has "%s".', $allowedStore->getIdStore(), $taxAppConfigEntity->getFkStore()));
                $countAppConfigEntities++;
            }
        }

        $this->assertSame(count($allowedStores), $countAppConfigEntities, sprintf('Expected to find Tax App configurations with vendor code "%s" but it was not found.', $vendorCode));
    }

    public function assertTaxAppWithVendorCodeDoesNotExist(string $vendorCode): void
    {
        $taxAppConfigEntity = $this->findTaxAppConfigByVendorCode($vendorCode);

        $this->assertNull($taxAppConfigEntity, sprintf('Expected not to find a Tax App configuration for the vendor with vendor code "%s" but it was found.', $vendorCode));
    }

    /**
     * @param array<mixed> $seed
     * @param \Generated\Shared\Transfer\StoreTransfer|null $storeTransfer
     *
     * @return \Generated\Shared\Transfer\CalculableObjectTransfer
     */
    public function haveQuoteTransfer(array $seed = [], ?StoreTransfer $storeTransfer = null): CalculableObjectTransfer
    {
        $calculableObjectTransfer = (new CalculableObjectBuilder())->seed($seed)->build();

        $quoteTransfer = (new QuoteTransfer())->fromArray($calculableObjectTransfer->toArray(), true);
        $quoteTransfer->setBillingAddress((new AddressBuilder())->build());
        $storeTransfer = $storeTransfer ?? (new StoreTransfer())->setIdStore(1);

        $calculableObjectTransfer->setOriginalQuote($quoteTransfer)->setStore($storeTransfer);

        if (!isset($seed['items']) || !is_array($seed['items'])) {
            return $calculableObjectTransfer;
        }

        return $calculableObjectTransfer->setItems($this->getItems($seed['items']));
    }

    /**
     * @param array<mixed> $seed
     *
     * @return \Generated\Shared\Transfer\OrderTransfer
     */
    public function haveOrderTransfer(array $seed = []): OrderTransfer
    {
        $orderTransfer = (new OrderBuilder())
            ->seed($seed)
            ->withBillingAddress()
            ->build();

        if (!isset($seed['items']) || !is_array($seed['items'])) {
            return $orderTransfer;
        }

        return $orderTransfer->setItems($this->getItems($seed['items']));
    }

    /**
     * @param array<int, mixed> $seed
     *
     * @return \ArrayObject<\Generated\Shared\Transfer\ItemTransfer>
     */
    protected function getItems(array $seed): ArrayObject
    {
        $items = [];

        foreach ($seed as $itemSeed) {
            $items[] = (new ItemBuilder())->seed($itemSeed)->build();
        }

        return new ArrayObject($items);
    }

    /**
     * @param array $seed
     * @param array<\Generated\Shared\Transfer\ItemTransfer> $items
     *
     * @return \Generated\Shared\Transfer\TaxAppSaleTransfer
     */
    public function haveTaxAppSaleTransfer(array $seed = [], array $items = []): TaxAppSaleTransfer
    {
        $shippingAddress = (new TaxAppAddressBuilder())->build();

        $item1Seed = ['shipping_address' => $shippingAddress];
        $item2Seed = ['shipping_address' => $shippingAddress];

        if (isset($items[0])) {
            $item1Seed = [
                'id' => isset($items[0]) ? sprintf('%s_%s', $items[0]->getSku(), 0) : null,
                'sku' => isset($items[0]) ? $items[0]->getSku() : null,
                'quantity' => isset($items[0]) ? $items[0]->getQuantity() : null,
                'shipping_address' => $shippingAddress,
            ];
        }

        if (isset($items[1])) {
            $item2Seed = [
                'id' => isset($items[1]) ? sprintf('%s_%s', $items[1]->getSku(), 1) : null,
                'sku' => isset($items[1]) ? $items[1]->getSku() : null,
                'quantity' => isset($items[1]) ? $items[1]->getQuantity() : null,
                'shipping_address' => $shippingAddress,
            ];
        }

        return (new TaxAppSaleBuilder())->seed($seed)
            ->withItem($item1Seed)
            ->withAnotherItem($item2Seed)
            ->withShipment()
            ->build();
    }

    /**
     * @param array $seed
     *
     * @return \Generated\Shared\Transfer\VertexCalculationRequestTransfer
     */
    public function haveVertexCalculationRequestTransfer(array $seed = []): VertexCalculationRequestTransfer
    {
        return (new VertexCalculationRequestBuilder())->seed($seed)->withSale($this->haveTaxAppSaleTransfer()->toArray())->build();
    }

    /**
     * @param array $seed
     *
     * @return \Generated\Shared\Transfer\TaxRefundRequestTransfer
     */
    public function haveTaxRefundRequestTransfer(array $seed = []): TaxRefundRequestTransfer
    {
        return (new TaxRefundRequestBuilder())->seed($seed)->withSale($this->haveTaxAppSaleTransfer()->toArray())->build();
    }

    /**
     * @param array $seed
     * @param array<\Generated\Shared\Transfer\ItemTransfer> $items
     *
     * @return \Generated\Shared\Transfer\VertexCalculationResponseTransfer
     */
    public function haveVertexCalculationResponseTransfer(array $seed = [], array $items = []): VertexCalculationResponseTransfer
    {
        $saleTransfer = $this->haveTaxAppSaleTransfer($seed, $items);
        $saleTransfer->setTaxTotal($this->getTaxAppItemsTaxTotals($saleTransfer->getItems()));

        return (new VertexCalculationResponseBuilder())->seed($seed)->withSale($saleTransfer->toArray())->build();
    }

    /**
     * @param \ArrayObject $itemTransfers
     *
     * @return int
     */
    public function getTaxAppItemsTaxTotals(ArrayObject $itemTransfers): int
    {
        $itemsTaxTotal = 0;

        foreach ($itemTransfers as $itemTransfer) {
            $itemsTaxTotal += $itemTransfer->getTaxTotal();
        }

        return $itemsTaxTotal;
    }

    /**
     * @param array $seed
     * @param array<\Generated\Shared\Transfer\ItemTransfer> $items
     *
     * @return \Generated\Shared\Transfer\TaxCalculationResponseTransfer
     */
    public function haveTaxCalculationResponseTransfer(array $seed = [], array $items = []): VertexCalculationResponseTransfer
    {
        $saleTransfer = $this->haveTaxAppSaleTransfer($seed, $items);
        $saleTransfer->setTaxTotal($this->getTaxAppItemsTaxTotals($saleTransfer->getItems()));

        return (new VertexCalculationResponseBuilder())->seed($seed)->withSale($saleTransfer->toArray())->build();
    }
}
