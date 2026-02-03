<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Client\Vertex\Validator;

use Generated\Shared\Transfer\VertexItemTransfer;
use Generated\Shared\Transfer\VertexValidationResponseTransfer;

class ItemValidator
{
    protected const ERROR_ITEM_FIELD_IS_REQUIRED = 'Item field %s is required';

    /**
     * @var \SprykerEco\Client\Vertex\Validator\AddressValidator
     */
    protected $addressValidator;

    /**
     * @var \SprykerEco\Client\Vertex\Validator\ShippingWarehouseValidator
     */
    protected $shippingWarehouseValidator;

    /**
     * @param \SprykerEco\Client\Vertex\Validator\AddressValidator $addressValidator
     * @param \SprykerEco\Client\Vertex\Validator\ShippingWarehouseValidator $shippingWarehouseValidator
     */
    public function __construct(
        AddressValidator $addressValidator,
        ShippingWarehouseValidator $shippingWarehouseValidator
    ) {
        $this->addressValidator = $addressValidator;
        $this->shippingWarehouseValidator = $shippingWarehouseValidator;
    }

    /**
     * @param \Generated\Shared\Transfer\VertexItemTransfer $vertexItemTransfer
     * @param string $fieldName
     * @param \Generated\Shared\Transfer\VertexValidationResponseTransfer $vertexValidationResponseTransfer
     * @param bool $requireRefundableAmount
     *
     * @return void
     */
    public function validate(
        VertexItemTransfer $vertexItemTransfer,
        string $fieldName,
        VertexValidationResponseTransfer $vertexValidationResponseTransfer,
        bool $requireRefundableAmount = false
    ): void {
        if (!$vertexItemTransfer->getId()) {
            $vertexValidationResponseTransfer->addMessage(sprintf(static::ERROR_ITEM_FIELD_IS_REQUIRED, $fieldName . '.' . VertexItemTransfer::ID));
        }

        if ($vertexItemTransfer->getPriceAmount() === null) {
            $vertexValidationResponseTransfer->addMessage(sprintf(static::ERROR_ITEM_FIELD_IS_REQUIRED, $fieldName . '.' . VertexItemTransfer::PRICE_AMOUNT));
        }

        if ($vertexItemTransfer->getDiscountAmount() === null) {
            $vertexValidationResponseTransfer->addMessage(sprintf(static::ERROR_ITEM_FIELD_IS_REQUIRED, $fieldName . '.' . VertexItemTransfer::DISCOUNT_AMOUNT));
        }

        if (!$vertexItemTransfer->getQuantity()) {
            $vertexValidationResponseTransfer->addMessage(sprintf(static::ERROR_ITEM_FIELD_IS_REQUIRED, $fieldName . '.' . VertexItemTransfer::QUANTITY));
        }

        if (!$vertexItemTransfer->getSku()) {
            $vertexValidationResponseTransfer->addMessage(sprintf(static::ERROR_ITEM_FIELD_IS_REQUIRED, $fieldName . '.' . VertexItemTransfer::SKU));
        }

        if ($requireRefundableAmount && $vertexItemTransfer->getRefundableAmount() === null) {
            $vertexValidationResponseTransfer->addMessage(sprintf(static::ERROR_ITEM_FIELD_IS_REQUIRED, $fieldName . '.' . VertexItemTransfer::REFUNDABLE_AMOUNT));
        }

        if (!$vertexItemTransfer->getShippingAddress()) {
            $vertexValidationResponseTransfer->addMessage(sprintf(static::ERROR_ITEM_FIELD_IS_REQUIRED, $fieldName . '.' . VertexItemTransfer::SHIPPING_ADDRESS));
        } else {
            $this->addressValidator->validate($vertexItemTransfer->getShippingAddress(), $fieldName . '.' . VertexItemTransfer::SHIPPING_ADDRESS, $vertexValidationResponseTransfer);
        }

        if ($vertexItemTransfer->getSellerAddress()) {
            $this->addressValidator->validate($vertexItemTransfer->getSellerAddress(), $fieldName . '.' . VertexItemTransfer::SELLER_ADDRESS, $vertexValidationResponseTransfer);
        }

        if ($vertexItemTransfer->getBillingAddress()) {
            $this->addressValidator->validate($vertexItemTransfer->getBillingAddress(), $fieldName . '.' . VertexItemTransfer::BILLING_ADDRESS, $vertexValidationResponseTransfer);
        }

        if ($vertexItemTransfer->getVertexShippingWarehouses()) {
            foreach ($vertexItemTransfer->getVertexShippingWarehouses() as $warehouseIndex => $warehouse) {
                $this->shippingWarehouseValidator->validate(
                    $warehouse,
                    $fieldName . '.' . VertexItemTransfer::VERTEX_SHIPPING_WAREHOUSES . '[' . $warehouseIndex . ']',
                    $vertexValidationResponseTransfer
                );
            }
        }
    }
}

