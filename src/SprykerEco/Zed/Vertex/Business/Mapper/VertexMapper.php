<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

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
use Generated\Shared\Transfer\ShippingWarehouseTransfer;
use Generated\Shared\Transfer\StoreTransfer;
use Generated\Shared\Transfer\VertexAddressTransfer;
use Generated\Shared\Transfer\VertexItemTransfer;
use Generated\Shared\Transfer\VertexSaleTransfer;
use Generated\Shared\Transfer\VertexShipmentTransfer;
use Ramsey\Uuid\Uuid;
use Spryker\Zed\Vertex\Business\Mapper\Addresses\AddressMapperInterface;
use Spryker\Zed\Vertex\Business\Mapper\Prices\ItemExpensePriceRetrieverInterface;
use Spryker\Zed\Vertex\Dependency\Facade\VertexToStoreFacadeInterface;
use Spryker\Zed\Vertex\VertexConfig;

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

    /**
     * @var \Spryker\Zed\Vertex\Business\Mapper\Addresses\AddressMapperInterface
     */
    protected AddressMapperInterface $addressMapper;

    /**
     * @var \Spryker\Zed\Vertex\Business\Mapper\Prices\ItemExpensePriceRetrieverInterface
     */
    protected ItemExpensePriceRetrieverInterface $priceFormatter;

    /**
     * @var \Spryker\Zed\Vertex\VertexConfig
     */
    protected VertexConfig $VertexConfig;

    /**
     * @var \Spryker\Zed\Vertex\Dependency\Facade\VertexToStoreFacadeInterface
     */
    protected VertexToStoreFacadeInterface $storeFacade;

    /**
     * @param \Spryker\Zed\Vertex\Business\Mapper\Addresses\AddressMapperInterface $addressMapper
     * @param \Spryker\Zed\Vertex\Business\Mapper\Prices\ItemExpensePriceRetrieverInterface $priceFormatter
     * @param \Spryker\Zed\Vertex\Dependency\Facade\VertexToStoreFacadeInterface $storeFacade
     * @param \Spryker\Zed\Vertex\VertexConfig $VertexConfig
     */
    public function __construct(
        AddressMapperInterface $addressMapper,
        ItemExpensePriceRetrieverInterface $priceFormatter,
        VertexToStoreFacadeInterface $storeFacade,
        VertexConfig $VertexConfig
    ) {
        $this->addressMapper = $addressMapper;
        $this->priceFormatter = $priceFormatter;
        $this->storeFacade = $storeFacade;
        $this->VertexConfig = $VertexConfig;
    }

    /**
     * @param \Generated\Shared\Transfer\CalculableObjectTransfer $calculableObjectTransfer
     * @param \Generated\Shared\Transfer\VertexSaleTransfer $VertexSaleTransfer
     *
     * @return \Generated\Shared\Transfer\VertexSaleTransfer
     */
    public function mapCalculableObjectToVertexSaleTransfer(
        CalculableObjectTransfer $calculableObjectTransfer,
        VertexSaleTransfer $VertexSaleTransfer
    ): VertexSaleTransfer {
        $VertexSaleTransfer = $VertexSaleTransfer->fromArray($calculableObjectTransfer->toArray(), true);
        $saleItemTransfers = new ArrayObject();
        $saleShipmentTransfers = new ArrayObject();

        if (!$calculableObjectTransfer->getTaxMetadata()) {
            $VertexSaleTransfer->setTaxMetadata([]);
        }

        $originalTransfer = $this->getOriginalTransfer($calculableObjectTransfer);
        $transferIdentifier = $this->getTransferIdentifier($originalTransfer);

        $documentDate = (new DateTime())->format('Y-m-d');
        if (method_exists($originalTransfer, 'getCreatedAt') && $originalTransfer->getCreatedAt()) {
            $createdAt = DateTime::createFromFormat('Y-m-d H:i:s.u', $originalTransfer->getCreatedAt());
            $documentDate = $createdAt ? $createdAt->format('Y-m-d') : $documentDate;
        }

        $VertexSaleTransfer
            ->setTransactionId($transferIdentifier)
            ->setDocumentNumber($transferIdentifier)
            ->setDocumentDate($documentDate)
            ->setPriceMode($calculableObjectTransfer->getPriceModeOrFail());

        foreach ($calculableObjectTransfer->getItems() as $itemIndex => $itemTransfer) {
            $VertexItemTransfer = $this->mapItemTransfersToSaleItemTransfers(
                $itemTransfer,
                $calculableObjectTransfer->getPriceModeOrFail(),
                $originalTransfer->getBillingAddress(),
                $itemIndex,
            );

            $saleItemTransfers->append($VertexItemTransfer);
        }

        foreach ($calculableObjectTransfer->getExpenses() as $hash => $expenseTransfer) {
            if ($expenseTransfer->getType() !== static::SHIPMENT_EXPENSE_TYPE) {
                continue;
            }

            $VertexShipmentTransfer = $this->mapExpenseTransferToSaleShipmentTransfer(
                $expenseTransfer,
                $calculableObjectTransfer->getPriceModeOrFail(),
                $originalTransfer->getBillingAddress(),
            );

            $VertexShipmentTransfer->setId($hash);
            $saleShipmentTransfers->append($VertexShipmentTransfer);
        }

        $VertexSaleTransfer->setItems($saleItemTransfers);
        $VertexSaleTransfer->setShipments($saleShipmentTransfers);

        $VertexSaleTransfer = $this->setTaxSaleCountryCode($calculableObjectTransfer, $VertexSaleTransfer, $originalTransfer);

        return $VertexSaleTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\ItemTransfer $itemTransfer
     * @param string $priceMode
     * @param \Generated\Shared\Transfer\AddressTransfer|null $billingAddressTransfer
     * @param int $itemIndex
     *
     * @return \Generated\Shared\Transfer\VertexItemTransfer
     */
    public function mapItemTransfersToSaleItemTransfers(
        ItemTransfer $itemTransfer,
        string $priceMode,
        ?AddressTransfer $billingAddressTransfer,
        int $itemIndex
    ): VertexItemTransfer {
        $VertexItemTransfer = new VertexItemTransfer();

        $VertexItemTransfer->setId(sprintf('%s_%s', $itemTransfer->getSku(), $itemIndex));
        $VertexItemTransfer->setSku($itemTransfer->getSku());
        $VertexItemTransfer->setQuantity($itemTransfer->getQuantity());

        $VertexItemTransfer->setPriceAmount($this->priceFormatter->getUnitPriceWithoutDiscount($itemTransfer, $priceMode));

        if ($itemTransfer->getCanceledAmount()) {
            $VertexItemTransfer->setRefundableAmount($this->priceFormatter->getUnitPriceWithoutDiscount($itemTransfer, $priceMode));
        }

        $VertexItemTransfer->setDiscountAmount($itemTransfer->getUnitDiscountAmountFullAggregation());

        if ($itemTransfer->getShipment() && $itemTransfer->getShipment()->getShippingAddress()) {
            $shippingVertexAddressTransfer = $this->addressMapper->mapAddressTransferToVertexAddressTransfer($itemTransfer->getShipment()->getShippingAddress(), new VertexAddressTransfer());
            $VertexItemTransfer->setShippingAddress($shippingVertexAddressTransfer);
        }

        if ($billingAddressTransfer && $billingAddressTransfer->getCountry()) {
            $billingVertexAddressTransfer = $this->addressMapper->mapAddressTransferToVertexAddressTransfer($billingAddressTransfer, new VertexAddressTransfer());
            $VertexItemTransfer->setBillingAddress($billingVertexAddressTransfer);
        }

        if ($itemTransfer->getMerchantProfileAddress()) {
            $sellerAddress = $this->addressMapper->mapMerchantProfileAddressTransferToVertexAddressTransfer($itemTransfer->getMerchantProfileAddress(), new VertexAddressTransfer());
            $VertexItemTransfer->setSellerAddress($sellerAddress);
        }

        if (!$itemTransfer->getTaxMetadata()) {
            $VertexItemTransfer->setTaxMetadata([]);
        }

        if ($itemTransfer->getMerchantStockAddresses()->count()) {
            foreach ($itemTransfer->getMerchantStockAddresses() as $merchantStockAddress) {
                $shippingWarehouseTransfer = $this->mapMerchantStockAddressTransferToShippingWarehouse(
                    $VertexItemTransfer,
                    $merchantStockAddress,
                    new ShippingWarehouseTransfer(),
                );

                $VertexItemTransfer->addShippingWarehouse($shippingWarehouseTransfer);
            }
        }

        return $VertexItemTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\VertexItemTransfer $VertexItemTransfer
     * @param \Generated\Shared\Transfer\MerchantStockAddressTransfer $merchantStockAddressTransfer
     * @param \Generated\Shared\Transfer\ShippingWarehouseTransfer $shippingWarehouseTransfer
     *
     * @return \Generated\Shared\Transfer\ShippingWarehouseTransfer
     */
    public function mapMerchantStockAddressTransferToShippingWarehouse(
        VertexItemTransfer $VertexItemTransfer,
        MerchantStockAddressTransfer $merchantStockAddressTransfer,
        ShippingWarehouseTransfer $shippingWarehouseTransfer
    ): ShippingWarehouseTransfer {
        $quantityToShip = 0;
        if ($merchantStockAddressTransfer->getQuantityToShip()) {
            $quantityToShip = $merchantStockAddressTransfer->getQuantityToShip()->toInt();
        }

        $shippingWarehouseTransfer->setQuantity($quantityToShip);

        if ($merchantStockAddressTransfer->getStockAddress()) {
            $warehouseAddress = $this->addressMapper->mapStockAddressTransferToVertexAddressTransfer($merchantStockAddressTransfer->getStockAddress(), new VertexAddressTransfer());
            $shippingWarehouseTransfer->setWarehouseAddress($warehouseAddress);
        }

        return $shippingWarehouseTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\ExpenseTransfer $expenseTransfer
     * @param string $priceMode
     * @param \Generated\Shared\Transfer\AddressTransfer|null $billingAddressTransfer
     *
     * @return \Generated\Shared\Transfer\VertexShipmentTransfer
     */
    public function mapExpenseTransferToSaleShipmentTransfer(
        ExpenseTransfer $expenseTransfer,
        string $priceMode,
        ?AddressTransfer $billingAddressTransfer
    ): VertexShipmentTransfer {
        $VertexShipmentTransfer = new VertexShipmentTransfer();

        if ($expenseTransfer->getShipment() && $expenseTransfer->getShipment()->getMethod()) {
            $VertexShipmentTransfer->setShipmentMethodKey($expenseTransfer->getShipment()->getMethod()->getShipmentMethodKey());
        }
        if ($expenseTransfer->getShipment() && $expenseTransfer->getShipment()->getShippingAddress()) {
            $shippingVertexAddressTransfer = $this->addressMapper->mapAddressTransferToVertexAddressTransfer($expenseTransfer->getShipment()->getShippingAddress(), new VertexAddressTransfer());

            $VertexShipmentTransfer->setShippingAddress($shippingVertexAddressTransfer);
        }

        if ($billingAddressTransfer) {
            $billingVertexAddressTransfer = $this->addressMapper->mapAddressTransferToVertexAddressTransfer($billingAddressTransfer, new VertexAddressTransfer());
            $VertexShipmentTransfer->setBillingAddress($billingVertexAddressTransfer);
        }

        $VertexShipmentTransfer->setPriceAmount($this->priceFormatter->getSumPriceWithoutDiscount($expenseTransfer, $priceMode));

        if ($expenseTransfer->getCanceledAmount()) {
            $VertexShipmentTransfer->setRefundableAmount($this->priceFormatter->getSumPriceWithoutDiscount($expenseTransfer, $priceMode));
        }
        $VertexShipmentTransfer->setDiscountAmount($expenseTransfer->getSumDiscountAmountAggregation());

        return $VertexShipmentTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\CalculableObjectTransfer $calculableObjectTransfer
     *
     * @throws \Exception
     *
     * @return \Generated\Shared\Transfer\OrderTransfer|\Generated\Shared\Transfer\QuoteTransfer
     */
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

    /**
     * @param \Generated\Shared\Transfer\OrderTransfer|\Generated\Shared\Transfer\QuoteTransfer $transfer
     *
     * @return string
     */
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

    /**
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     * @param \Generated\Shared\Transfer\VertexSaleTransfer $VertexSaleTransfer
     *
     * @return \Generated\Shared\Transfer\VertexSaleTransfer
     */
    public function mapOrderTransferToVertexSaleTransfer(OrderTransfer $orderTransfer, VertexSaleTransfer $VertexSaleTransfer): VertexSaleTransfer
    {
        $calculableObjectTransfer = new CalculableObjectTransfer();
        $calculableObjectTransfer->fromArray($orderTransfer->toArray(), true);
        if (!$orderTransfer->getTaxMetadata()) {
            $calculableObjectTransfer->setTaxMetadata(new SaleTaxMetadataTransfer());
        }

        $calculableObjectTransfer->setStore((new StoreTransfer())->setName($orderTransfer->getStore()));
        $calculableObjectTransfer->setOriginalOrder($orderTransfer);

        return $this->mapCalculableObjectToVertexSaleTransfer($calculableObjectTransfer, $VertexSaleTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\CalculableObjectTransfer $calculableObjectTransfer
     * @param \Generated\Shared\Transfer\VertexSaleTransfer $VertexSaleTransfer
     * @param \Generated\Shared\Transfer\OrderTransfer|\Generated\Shared\Transfer\QuoteTransfer $originalTransfer
     *
     * @return \Generated\Shared\Transfer\VertexSaleTransfer
     */
    public function setTaxSaleCountryCode(
        CalculableObjectTransfer $calculableObjectTransfer,
        VertexSaleTransfer $VertexSaleTransfer,
        OrderTransfer|QuoteTransfer $originalTransfer
    ): VertexSaleTransfer {
        $sellerCountryCode = $customerCountryCode = $this->findStoreCountryCode($calculableObjectTransfer);

        if ($this->VertexConfig->getSellerCountryCode()) {
            $sellerCountryCode = $this->VertexConfig->getSellerCountryCode();
        }

        if ($this->VertexConfig->getCustomerCountryCode()) {
            $customerCountryCode = $this->VertexConfig->getCustomerCountryCode();
        }

        if ($originalTransfer->getBillingAddress() && $originalTransfer->getBillingAddress()->getIso2Code()) {
            $customerCountryCode = $originalTransfer->getBillingAddress()->getIso2Code();
        }

        $VertexSaleTransfer->setSellerCountryCode($sellerCountryCode ?: null);
        $VertexSaleTransfer->setCustomerCountryCode($customerCountryCode ?: null);

        return $VertexSaleTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\CalculableObjectTransfer $calculableObjectTransfer
     *
     * @return string|null
     */
    protected function findStoreCountryCode(CalculableObjectTransfer $calculableObjectTransfer): ?string
    {
        if (!empty($calculableObjectTransfer->getStoreOrFail()->getCountries()[0])) {
            return $calculableObjectTransfer->getStoreOrFail()->getCountries()[0];
        }

        $storeTransfer = $this->storeFacade->getStoreByName($calculableObjectTransfer->getStoreOrFail()->getNameOrFail());

        return $storeTransfer->getCountries() !== [] ? $storeTransfer->getCountries()[0] : null;
    }
}
