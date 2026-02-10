<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Client\Vertex\Builder\Supplies;

use Generated\Shared\Transfer\VertexCalculationRequestTransfer;
use Generated\Shared\Transfer\VertexCustomerTransfer;
use Generated\Shared\Transfer\VertexItemTransfer;
use Generated\Shared\Transfer\VertexLineItemTransfer;
use Generated\Shared\Transfer\VertexLocationTransfer;
use Generated\Shared\Transfer\VertexSaleTransfer;
use Generated\Shared\Transfer\VertexShippingWarehouseTransfer;
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
     * @param array<\SprykerEco\Client\Vertex\Builder\VertexLineItemBuilderInterface> $vertexLineItemBuilders
     */
    public function __construct(protected array $vertexLineItemBuilders)
    {
    }

    /**
     * @param \Generated\Shared\Transfer\VertexCalculationRequestTransfer $vertexCalculationRequestTransfer
     * @param \Generated\Shared\Transfer\VertexSuppliesTransfer $vertexSuppliesTransfer
     *
     * @return \Generated\Shared\Transfer\VertexSuppliesTransfer
     */
    public function build(
        VertexCalculationRequestTransfer $vertexCalculationRequestTransfer,
        VertexSuppliesTransfer $vertexSuppliesTransfer,
    ): VertexSuppliesTransfer {
        $vertexSaleTransfer = $vertexCalculationRequestTransfer->getSaleOrFail();
        foreach ($vertexSaleTransfer->getItems() as $vertexItemTransfer) {
            if (!$this->hasItemMultipleWarehouses($vertexItemTransfer)) {
                $vertexSuppliesTransfer = $this->buildWithSingleWarehouse($vertexSaleTransfer, $vertexItemTransfer, $vertexSuppliesTransfer);

                continue;
            }

            $vertexSuppliesTransfer = $this->buildWithMultipleWarehouses($vertexSaleTransfer, $vertexItemTransfer, $vertexSuppliesTransfer);
        }

        return $vertexSuppliesTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\VertexSaleTransfer $vertexSaleTransfer
     * @param \Generated\Shared\Transfer\VertexItemTransfer $vertexItemTransfer
     * @param \Generated\Shared\Transfer\VertexSuppliesTransfer $vertexSuppliesTransfer
     *
     * @return \Generated\Shared\Transfer\VertexSuppliesTransfer
     */
    protected function buildWithSingleWarehouse(
        VertexSaleTransfer $vertexSaleTransfer,
        VertexItemTransfer $vertexItemTransfer,
        VertexSuppliesTransfer $vertexSuppliesTransfer,
    ): VertexSuppliesTransfer {
        $vertexLineItemTransfer = new VertexLineItemTransfer();

        if ($vertexItemTransfer->getVertexShippingWarehouses()->count()) {
            $vertexItemTransfer->setWarehouseAddressOrFail(
                $vertexItemTransfer->getVertexShippingWarehouses()[0]->getWarehouseAddressOrFail(),
            );
        }

        $vertexLineItemTransfer->setTaxIncludedIndicator($vertexSaleTransfer->getPriceMode() === static::PRICE_MODE_GROSS);
        $this->setDefaultCustomerDestination($vertexLineItemTransfer, $vertexSaleTransfer);

        $this->runVertexSuppliesLineItemsBuilders($vertexItemTransfer, $vertexLineItemTransfer);

        return $vertexSuppliesTransfer->addLineItem($vertexLineItemTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\VertexSaleTransfer $vertexSaleTransfer
     * @param \Generated\Shared\Transfer\VertexItemTransfer $vertexItemTransfer
     * @param \Generated\Shared\Transfer\VertexSuppliesTransfer $vertexSuppliesTransfer
     *
     * @return \Generated\Shared\Transfer\VertexSuppliesTransfer
     */
    protected function buildWithMultipleWarehouses(
        VertexSaleTransfer $vertexSaleTransfer,
        VertexItemTransfer $vertexItemTransfer,
        VertexSuppliesTransfer $vertexSuppliesTransfer,
    ): VertexSuppliesTransfer {
        $idIndex = 0;
        foreach ($vertexItemTransfer->getVertexShippingWarehouses() as $vertexShippingWarehouse) {
            $clonedVertexItemTransfer = $this->cloneVertexItemTransferWithVertexShippingWarehouseData(
                $vertexItemTransfer,
                $vertexShippingWarehouse,
                $idIndex,
            );

            $vertexLineItemTransfer = new VertexLineItemTransfer();

            $vertexLineItemTransfer->setShouldBeGrouped(true);
            $vertexLineItemTransfer->setInitialIdentifier($vertexItemTransfer->getIdOrFail());
            $vertexLineItemTransfer->setTaxIncludedIndicator($vertexSaleTransfer->getPriceMode() === static::PRICE_MODE_GROSS);
            $this->setDefaultCustomerDestination($vertexLineItemTransfer, $vertexSaleTransfer);

            $this->runVertexSuppliesLineItemsBuilders($clonedVertexItemTransfer, $vertexLineItemTransfer);

            $vertexSuppliesTransfer->addLineItem($vertexLineItemTransfer);
        }

        return $vertexSuppliesTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\VertexItemTransfer $vertexItemTransfer
     * @param \Generated\Shared\Transfer\VertexShippingWarehouseTransfer $vertexShippingWarehouseTransfer
     * @param int $idIndex
     *
     * @return \Generated\Shared\Transfer\VertexItemTransfer
     */
    protected function cloneVertexItemTransferWithVertexShippingWarehouseData(
        VertexItemTransfer $vertexItemTransfer,
        VertexShippingWarehouseTransfer $vertexShippingWarehouseTransfer,
        int &$idIndex,
    ): VertexItemTransfer {
        $clonedItemTransfer = clone $vertexItemTransfer;

        $clonedItemTransfer->setId(
            $vertexItemTransfer->getId() . '_' . $idIndex++,
        );

        $clonedItemTransfer->setQuantity($vertexShippingWarehouseTransfer->getQuantityOrFail());

        $clonedItemTransfer->setWarehouseAddressOrFail(
            $vertexShippingWarehouseTransfer->getWarehouseAddressOrFail(),
        );

        return $clonedItemTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\VertexItemTransfer $vertexItemTransfer
     * @param \Generated\Shared\Transfer\VertexLineItemTransfer $vertexLineItemTransfer
     *
     * @return \Generated\Shared\Transfer\VertexLineItemTransfer
     */
    protected function runVertexSuppliesLineItemsBuilders(
        VertexItemTransfer $vertexItemTransfer,
        VertexLineItemTransfer $vertexLineItemTransfer,
    ): VertexLineItemTransfer {
        foreach ($this->vertexLineItemBuilders as $builder) {
            $vertexLineItemTransfer = $builder->build($vertexItemTransfer, $vertexLineItemTransfer);
        }

        return $vertexLineItemTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\VertexItemTransfer $vertexItemTransfer
     *
     * @return bool
     */
    protected function hasItemMultipleWarehouses(VertexItemTransfer $vertexItemTransfer): bool
    {
        return $vertexItemTransfer->getVertexShippingWarehouses()->count() > 1;
    }

    /**
     * @param \Generated\Shared\Transfer\VertexLineItemTransfer $vertexLineItemTransfer
     * @param \Generated\Shared\Transfer\VertexSaleTransfer $vertexSaleTransfer
     *
     * @return void
     */
    protected function setDefaultCustomerDestination(VertexLineItemTransfer $vertexLineItemTransfer, VertexSaleTransfer $vertexSaleTransfer): void
    {
        if (!$vertexSaleTransfer->getCustomerCountryCode()) {
            return;
        }

        $vertexCustomerTransfer = $vertexLineItemTransfer->getCustomer() ?? new VertexCustomerTransfer();
        $vertexCustomerTransfer->setAdministrativeDestination((new VertexLocationTransfer())->setCountry($vertexSaleTransfer->getCustomerCountryCode()));
        $vertexCustomerTransfer->setDestination((new VertexLocationTransfer())->setCountry($vertexSaleTransfer->getCustomerCountryCode()));
        $vertexLineItemTransfer->setCustomer($vertexCustomerTransfer);
    }
}
