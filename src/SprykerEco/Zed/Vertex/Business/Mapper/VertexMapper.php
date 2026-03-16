<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Zed\Vertex\Business\Mapper;

use ArrayObject;
use DateTime;
use Exception;
use Generated\Shared\Transfer\AddressTransfer;
use Generated\Shared\Transfer\CalculableObjectTransfer;
use Generated\Shared\Transfer\ExpenseTransfer;
use Generated\Shared\Transfer\ItemTransfer;
use Generated\Shared\Transfer\MerchantStockAddressTransfer;
use Generated\Shared\Transfer\OrderTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\SaleTaxMetadataTransfer;
use Generated\Shared\Transfer\StoreTransfer;
use Generated\Shared\Transfer\VertexAddressTransfer;
use Generated\Shared\Transfer\VertexItemTransfer;
use Generated\Shared\Transfer\VertexSaleTransfer;
use Generated\Shared\Transfer\VertexShipmentTransfer;
use Generated\Shared\Transfer\VertexShippingWarehouseTransfer;
use Ramsey\Uuid\Uuid;
use Spryker\Zed\Store\Business\StoreFacadeInterface;
use SprykerEco\Zed\Vertex\Business\Mapper\Addresses\AddressMapperInterface;
use SprykerEco\Zed\Vertex\Business\Mapper\Prices\ItemExpensePriceRetrieverInterface;
use SprykerEco\Zed\Vertex\VertexConfig;

class VertexMapper implements VertexMapperInterface
{
    /**
     * @uses \Spryker\Shared\Shipment\ShipmentConfig::SHIPMENT_EXPENSE_TYPE
     *
     * @var string
     */
    public const SHIPMENT_EXPENSE_TYPE = 'SHIPMENT_EXPENSE_TYPE';

    /**
     * @var string
     */
    protected const ORIGINAL_TRANSFER_MISSING_EXCEPTION = 'Could not get original transfer from CalculableObjectTransfer';

    public function __construct(
        protected AddressMapperInterface $addressMapper,
        protected ItemExpensePriceRetrieverInterface $priceFormatter,
        protected StoreFacadeInterface $storeFacade,
        protected VertexConfig $vertexConfig,
    ) {
    }

    public function mapCalculableObjectToVertexSaleTransfer(
        CalculableObjectTransfer $calculableObjectTransfer,
        VertexSaleTransfer $vertexSaleTransfer,
    ): VertexSaleTransfer {
        $vertexSaleTransfer = $vertexSaleTransfer->fromArray($calculableObjectTransfer->toArray(), true);
        $saleItemTransfers = new ArrayObject();
        $vertexShipmentTransfers = new ArrayObject();

        if (!$calculableObjectTransfer->getTaxMetadata()) {
            $vertexSaleTransfer->setTaxMetadata([]);
        }

        $originalTransfer = $this->getOriginalTransfer($calculableObjectTransfer);
        $transferIdentifier = $this->getTransferIdentifier($originalTransfer);

        $documentDate = (new DateTime())->format('Y-m-d');
        if (method_exists($originalTransfer, 'getCreatedAt') && $originalTransfer->getCreatedAt()) {
            $createdAt = DateTime::createFromFormat('Y-m-d H:i:s.u', $originalTransfer->getCreatedAt());
            $documentDate = $createdAt ? $createdAt->format('Y-m-d') : $documentDate;
        }

        $vertexSaleTransfer
            ->setTransactionId($transferIdentifier)
            ->setDocumentNumber($transferIdentifier)
            ->setDocumentDate($documentDate)
            ->setPriceMode($calculableObjectTransfer->getPriceModeOrFail());

        foreach ($calculableObjectTransfer->getItems() as $itemIndex => $itemTransfer) {
            $vertexItemTransfer = $this->mapItemTransfersToSaleItemTransfers(
                $itemTransfer,
                $calculableObjectTransfer->getPriceModeOrFail(),
                $originalTransfer->getBillingAddress(),
                $itemIndex,
            );

            $saleItemTransfers->append($vertexItemTransfer);
        }

        foreach ($calculableObjectTransfer->getExpenses() as $hash => $expenseTransfer) {
            if ($expenseTransfer->getType() !== static::SHIPMENT_EXPENSE_TYPE) {
                continue;
            }

            $vertexShipmentTransfer = $this->mapExpenseTransferToVertexShipmentTransfer(
                $expenseTransfer,
                $calculableObjectTransfer->getPriceModeOrFail(),
                $originalTransfer->getBillingAddress(),
            );

            $vertexShipmentTransfer->setId((string)$hash);
            $vertexShipmentTransfers->append($vertexShipmentTransfer);
        }

        $vertexSaleTransfer->setItems($saleItemTransfers);
        $vertexSaleTransfer->setShipments($vertexShipmentTransfers);

        $vertexSaleTransfer = $this->setTaxSaleCountryCode($calculableObjectTransfer, $vertexSaleTransfer, $originalTransfer);

        return $vertexSaleTransfer;
    }

    public function mapItemTransfersToSaleItemTransfers(
        ItemTransfer $itemTransfer,
        string $priceMode,
        ?AddressTransfer $billingAddressTransfer,
        int $itemIndex,
    ): VertexItemTransfer {
        $vertexItemTransfer = new VertexItemTransfer();

        $vertexItemTransfer->setId(sprintf('%s_%s', $itemTransfer->getSku(), $itemIndex));
        $vertexItemTransfer->setSku($itemTransfer->getSku());
        $vertexItemTransfer->setQuantity((int)$itemTransfer->getQuantity());

        $vertexItemTransfer->setPriceAmount($this->priceFormatter->getUnitPriceWithoutDiscount($itemTransfer, $priceMode));

        if ($itemTransfer->getCanceledAmount()) {
            $vertexItemTransfer->setRefundableAmount($this->priceFormatter->getUnitPriceWithoutDiscount($itemTransfer, $priceMode));
        }

        $vertexItemTransfer->setDiscountAmount($itemTransfer->getUnitDiscountAmountFullAggregation());

        if ($itemTransfer->getShipment() && $itemTransfer->getShipment()->getShippingAddress()) {
            $shippingVertexAddressTransfer = $this->addressMapper->mapAddressTransferToVertexAddressTransfer($itemTransfer->getShipment()->getShippingAddress(), new VertexAddressTransfer());
            $vertexItemTransfer->setShippingAddress($shippingVertexAddressTransfer);
        }

        if ($billingAddressTransfer && $billingAddressTransfer->getCountry()) {
            $billingVertexAddressTransfer = $this->addressMapper->mapAddressTransferToVertexAddressTransfer($billingAddressTransfer, new VertexAddressTransfer());
            $vertexItemTransfer->setBillingAddress($billingVertexAddressTransfer);
        }

        if ($itemTransfer->getMerchantProfileAddress()) {
            $sellerAddress = $this->addressMapper->mapMerchantProfileAddressTransferToVertexAddressTransfer($itemTransfer->getMerchantProfileAddress(), new VertexAddressTransfer());
            $vertexItemTransfer->setSellerAddress($sellerAddress);
        }

        if (!$itemTransfer->getTaxMetadata()) {
            $vertexItemTransfer->setTaxMetadata([]);
        }

        if ($itemTransfer->getMerchantStockAddresses()->count()) {
            foreach ($itemTransfer->getMerchantStockAddresses() as $merchantStockAddress) {
                $vertexShippingWarehouseTransfer = $this->mapMerchantStockAddressTransferToVertexShippingWarehouse(
                    $merchantStockAddress,
                    new VertexShippingWarehouseTransfer(),
                );

                $vertexItemTransfer->addVertexShippingWarehouse($vertexShippingWarehouseTransfer);
            }
        }

        return $vertexItemTransfer;
    }

    public function mapMerchantStockAddressTransferToVertexShippingWarehouse(
        MerchantStockAddressTransfer $merchantStockAddressTransfer,
        VertexShippingWarehouseTransfer $vertexShippingWarehouseTransfer,
    ): VertexShippingWarehouseTransfer {
        $quantityToShip = 0;
        if ($merchantStockAddressTransfer->getQuantityToShip()) {
            $quantityToShip = $merchantStockAddressTransfer->getQuantityToShip()->toInt();
        }

        $vertexShippingWarehouseTransfer->setQuantity($quantityToShip);

        if ($merchantStockAddressTransfer->getStockAddress()) {
            $warehouseAddress = $this->addressMapper->mapStockAddressTransferToVertexAddressTransfer($merchantStockAddressTransfer->getStockAddress(), new VertexAddressTransfer());
            $vertexShippingWarehouseTransfer->setWarehouseAddress($warehouseAddress);
        }

        return $vertexShippingWarehouseTransfer;
    }

    public function mapExpenseTransferToVertexShipmentTransfer(
        ExpenseTransfer $expenseTransfer,
        string $priceMode,
        ?AddressTransfer $billingAddressTransfer,
    ): VertexShipmentTransfer {
        $vertexShipmentTransfer = new VertexShipmentTransfer();

        if ($expenseTransfer->getShipment() && $expenseTransfer->getShipment()->getMethod()) {
            $vertexShipmentTransfer->setShipmentMethodKey($expenseTransfer->getShipment()->getMethod()->getShipmentMethodKey());
        }
        if ($expenseTransfer->getShipment() && $expenseTransfer->getShipment()->getShippingAddress()) {
            $shippingVertexAddressTransfer = $this->addressMapper->mapAddressTransferToVertexAddressTransfer($expenseTransfer->getShipment()->getShippingAddress(), new VertexAddressTransfer());

            $vertexShipmentTransfer->setShippingAddress($shippingVertexAddressTransfer);
        }

        if ($billingAddressTransfer) {
            $billingVertexAddressTransfer = $this->addressMapper->mapAddressTransferToVertexAddressTransfer($billingAddressTransfer, new VertexAddressTransfer());
            $vertexShipmentTransfer->setBillingAddress($billingVertexAddressTransfer);
        }

        $vertexShipmentTransfer->setPriceAmount($this->priceFormatter->getSumPriceWithoutDiscount($expenseTransfer, $priceMode));

        if ($expenseTransfer->getCanceledAmount()) {
            $vertexShipmentTransfer->setRefundableAmount($this->priceFormatter->getSumPriceWithoutDiscount($expenseTransfer, $priceMode));
        }
        $discountAmount = $expenseTransfer->getSumDiscountAmountAggregation() ?? $expenseTransfer->getSumPrice();
        $vertexShipmentTransfer->setDiscountAmount($discountAmount);

        return $vertexShipmentTransfer;
    }

    protected function getOriginalTransfer(CalculableObjectTransfer $calculableObjectTransfer): OrderTransfer|QuoteTransfer
    {
        if ($calculableObjectTransfer->getOriginalQuote() !== null) {
            return $calculableObjectTransfer->getOriginalQuote();
        }

        if ($calculableObjectTransfer->getOriginalOrder() !== null) {
            return $calculableObjectTransfer->getOriginalOrder();
        }

        throw new Exception(static::ORIGINAL_TRANSFER_MISSING_EXCEPTION);
    }

    protected function getTransferIdentifier(OrderTransfer|QuoteTransfer $transfer): string
    {
        $transferIdentifier = null;

        if (method_exists($transfer, 'getUuid')) {
            $transferIdentifier = $transfer->getUuid() ?? Uuid::uuid4()->toString();
            //@phpstan-ignore-next-line
            $transfer->setUuid($transferIdentifier);
        }

        if (method_exists($transfer, 'getOrderReference') && !$transferIdentifier) {
            $transferIdentifier = $transfer->getOrderReference();
        }

        if (!$transferIdentifier) {
            return Uuid::uuid4()->toString();
        }

        return $transferIdentifier;
    }

    public function mapOrderTransferToVertexSaleTransfer(OrderTransfer $orderTransfer, VertexSaleTransfer $vertexSaleTransfer): VertexSaleTransfer
    {
        $calculableObjectTransfer = new CalculableObjectTransfer();
        $calculableObjectTransfer->fromArray($orderTransfer->toArray(), true);
        if (!$orderTransfer->getTaxMetadata()) {
            $calculableObjectTransfer->setTaxMetadata(new SaleTaxMetadataTransfer());
        }

        $calculableObjectTransfer->setStore((new StoreTransfer())->setName($orderTransfer->getStore()));
        $calculableObjectTransfer->setOriginalOrder($orderTransfer);

        return $this->mapCalculableObjectToVertexSaleTransfer($calculableObjectTransfer, $vertexSaleTransfer);
    }

    public function setTaxSaleCountryCode(
        CalculableObjectTransfer $calculableObjectTransfer,
        VertexSaleTransfer $vertexSaleTransfer,
        OrderTransfer|QuoteTransfer $originalTransfer,
    ): VertexSaleTransfer {
        $sellerCountryCode = $customerCountryCode = $this->findStoreCountryCode($calculableObjectTransfer);

        if ($this->vertexConfig->getSellerCountryCode()) {
            $sellerCountryCode = $this->vertexConfig->getSellerCountryCode();
        }

        if ($this->vertexConfig->getCustomerCountryCode()) {
            $customerCountryCode = $this->vertexConfig->getCustomerCountryCode();
        }

        if ($originalTransfer->getBillingAddress() && $originalTransfer->getBillingAddress()->getIso2Code()) {
            $customerCountryCode = $originalTransfer->getBillingAddress()->getIso2Code();
        }

        $vertexSaleTransfer->setSellerCountryCode($sellerCountryCode ?: null);
        $vertexSaleTransfer->setCustomerCountryCode($customerCountryCode ?: null);

        return $vertexSaleTransfer;
    }

    protected function findStoreCountryCode(CalculableObjectTransfer $calculableObjectTransfer): ?string
    {
        if (!empty($calculableObjectTransfer->getStoreOrFail()->getCountries()[0])) {
            return $calculableObjectTransfer->getStoreOrFail()->getCountries()[0];
        }

        $storeTransfer = $this->storeFacade->getStoreByName($calculableObjectTransfer->getStoreOrFail()->getNameOrFail());

        return $storeTransfer->getCountries() !== [] ? $storeTransfer->getCountries()[0] : null;
    }
}
