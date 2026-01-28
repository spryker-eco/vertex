<?php

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerEco\Client\Vertex\Builder\Supplies;

use Generated\Shared\Transfer\SaleItemTransfer;
use Generated\Shared\Transfer\SaleTransfer;
use Generated\Shared\Transfer\ShippingWarehouseTransfer;
use Generated\Shared\Transfer\VertexCalculationRequestTransfer;
use Generated\Shared\Transfer\VertexCustomerTransfer;
use Generated\Shared\Transfer\VertexLineItemTransfer;
use Generated\Shared\Transfer\VertexLocationTransfer;
use Generated\Shared\Transfer\VertexSuppliesTransfer;
use SprykerEco\Client\Vertex\Builder\VertexSuppliesRequestBuilderInterface;

class VertexSuppliesLineItemsBuilder implements VertexSuppliesRequestBuilderInterface
{
    /**
     * @var string
     */
    protected const PRICE_MODE_GROSS = 'GROSS_MODE';

    /**
     * @var string
     */
    protected const PRICE_MODE_NET = 'NET_MODE';

    /**
     * @var array<\SprykerEco\Client\Vertex\Builder\VertexLineItemBuilderInterface>
     */
    protected array $vertexLineItemBuilders;

    /**
     * @param array<\SprykerEco\Client\Vertex\Builder\VertexLineItemBuilderInterface> $vertexLineItemBuilders
     */
    public function __construct(array $vertexLineItemBuilders)
    {
        $this->vertexLineItemBuilders = $vertexLineItemBuilders;
    }

    /**
     * @param \Generated\Shared\Transfer\VertexCalculationRequestTransfer $vertexCalculationRequestTransfer
     * @param \Generated\Shared\Transfer\VertexSuppliesTransfer $vertexSuppliesTransfer
     *
     * @return \Generated\Shared\Transfer\VertexSuppliesTransfer
     */
    public function build(
        VertexCalculationRequestTransfer $vertexCalculationRequestTransfer,
        VertexSuppliesTransfer $vertexSuppliesTransfer
    ): VertexSuppliesTransfer {
        $saleTransfer = $vertexCalculationRequestTransfer->getSaleOrFail();
        foreach ($saleTransfer->getItems() as $item) {
            if (!$this->hasItemMultipleWarehouses($item)) {
                $vertexSuppliesTransfer = $this->buildWithSingleWarehouse($saleTransfer, $item, $vertexSuppliesTransfer);

                continue;
            }

            $vertexSuppliesTransfer = $this->buildWithMultipleWarehouses($saleTransfer, $item, $vertexSuppliesTransfer);
        }

        return $vertexSuppliesTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\SaleTransfer $saleTransfer
     * @param \Generated\Shared\Transfer\SaleItemTransfer $saleItemTransfer
     * @param \Generated\Shared\Transfer\VertexSuppliesTransfer $vertexSuppliesTransfer
     *
     * @return \Generated\Shared\Transfer\VertexSuppliesTransfer
     */
    protected function buildWithSingleWarehouse(
        SaleTransfer $saleTransfer,
        SaleItemTransfer $saleItemTransfer,
        VertexSuppliesTransfer $vertexSuppliesTransfer
    ): VertexSuppliesTransfer {
        $vertexLineItemTransfer = new VertexLineItemTransfer();

        if ($saleItemTransfer->getShippingWarehouses()->count()) {
            $saleItemTransfer->setWarehouseAddressOrFail(
                $saleItemTransfer->getShippingWarehouses()[0]->getWarehouseAddressOrFail(),
            );
        }

        $vertexLineItemTransfer->setTaxIncludedIndicator($saleTransfer->getPriceMode() === static::PRICE_MODE_GROSS);
        $this->setDefaultCustomerDestination($vertexLineItemTransfer, $saleTransfer);

        $this->runVertexSuppliesLineItemsBuilders($saleItemTransfer, $vertexLineItemTransfer);

        return $vertexSuppliesTransfer->addLineItem($vertexLineItemTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\SaleTransfer $saleTransfer
     * @param \Generated\Shared\Transfer\SaleItemTransfer $saleItemTransfer
     * @param \Generated\Shared\Transfer\VertexSuppliesTransfer $vertexSuppliesTransfer
     *
     * @return \Generated\Shared\Transfer\VertexSuppliesTransfer
     */
    protected function buildWithMultipleWarehouses(
        SaleTransfer $saleTransfer,
        SaleItemTransfer $saleItemTransfer,
        VertexSuppliesTransfer $vertexSuppliesTransfer
    ): VertexSuppliesTransfer {
        $idIndex = 0;
        foreach ($saleItemTransfer->getShippingWarehouses() as $shippingWarehouse) {
            $clonedSaleItemTransfer = $this->cloneSaleItemTransferWithShippingWarehouseData(
                $saleItemTransfer,
                $shippingWarehouse,
                $idIndex,
            );

            $vertexLineItemTransfer = new VertexLineItemTransfer();

            $vertexLineItemTransfer->setShouldBeGrouped(true);
            $vertexLineItemTransfer->setInitialIdentifier($saleItemTransfer->getIdOrFail());
            $vertexLineItemTransfer->setTaxIncludedIndicator($saleTransfer->getPriceMode() === static::PRICE_MODE_GROSS);
            $this->setDefaultCustomerDestination($vertexLineItemTransfer, $saleTransfer);

            $this->runVertexSuppliesLineItemsBuilders($clonedSaleItemTransfer, $vertexLineItemTransfer);

            $vertexSuppliesTransfer->addLineItem($vertexLineItemTransfer);
        }

        return $vertexSuppliesTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\SaleItemTransfer $saleItemTransfer
     * @param \Generated\Shared\Transfer\ShippingWarehouseTransfer $shippingWarehouseTransfer
     * @param int $idIndex
     *
     * @return \Generated\Shared\Transfer\SaleItemTransfer
     */
    protected function cloneSaleItemTransferWithShippingWarehouseData(
        SaleItemTransfer $saleItemTransfer,
        ShippingWarehouseTransfer $shippingWarehouseTransfer,
        int &$idIndex
    ): SaleItemTransfer {
        $clonedItemTransfer = clone $saleItemTransfer;

        $clonedItemTransfer->setId(
            $saleItemTransfer->getId() . '_' . $idIndex++,
        );

        $clonedItemTransfer->setQuantity((string)$shippingWarehouseTransfer->getQuantityOrFail());

        $clonedItemTransfer->setWarehouseAddressOrFail(
            $shippingWarehouseTransfer->getWarehouseAddressOrFail(),
        );

        return $clonedItemTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\SaleItemTransfer $saleItemTransfer
     * @param \Generated\Shared\Transfer\VertexLineItemTransfer $vertexLineItemTransfer
     *
     * @return \Generated\Shared\Transfer\VertexLineItemTransfer
     */
    protected function runVertexSuppliesLineItemsBuilders(
        SaleItemTransfer $saleItemTransfer,
        VertexLineItemTransfer $vertexLineItemTransfer
    ): VertexLineItemTransfer {
        foreach ($this->vertexLineItemBuilders as $builder) {
            $vertexLineItemTransfer = $builder->build($saleItemTransfer, $vertexLineItemTransfer);
        }

        return $vertexLineItemTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\SaleItemTransfer $saleItemTransfer
     *
     * @return bool
     */
    protected function hasItemMultipleWarehouses(SaleItemTransfer $saleItemTransfer): bool
    {
        return $saleItemTransfer->getShippingWarehouses()->count() > 1;
    }

    /**
     * @param \Generated\Shared\Transfer\VertexLineItemTransfer $vertexLineItemTransfer
     * @param \Generated\Shared\Transfer\SaleTransfer $saleTransfer
     *
     * @return void
     */
    protected function setDefaultCustomerDestination(VertexLineItemTransfer $vertexLineItemTransfer, SaleTransfer $saleTransfer): void
    {
        if (!$saleTransfer->getCustomerCountryCode()) {
            return;
        }

        $vertexCustomerTransfer = $vertexLineItemTransfer->getCustomer() ?? new VertexCustomerTransfer();
        $vertexCustomerTransfer->setAdministrativeDestination((new VertexLocationTransfer())->setCountry($saleTransfer->getCustomerCountryCode()));
        $vertexCustomerTransfer->setDestination((new VertexLocationTransfer())->setCountry($saleTransfer->getCustomerCountryCode()));
        $vertexLineItemTransfer->setCustomer($vertexCustomerTransfer);
    }
}
