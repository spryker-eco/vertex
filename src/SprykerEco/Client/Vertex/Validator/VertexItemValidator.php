<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Client\Vertex\Validator;

use Generated\Shared\Transfer\VertexItemTransfer;
use Generated\Shared\Transfer\VertexValidationResponseTransfer;

class VertexItemValidator implements VertexItemValidatorInterface
{
    protected const ERROR_ITEM_FIELD_IS_REQUIRED = 'Field %s is required for item %s';

    public function __construct(
        protected VertexAddressValidatorInterface $addressValidator,
        protected VertexShippingWarehouseValidatorInterface $shippingWarehouseValidator
    ) {}

    public function validate(
        VertexItemTransfer $vertexItemTransfer,
        VertexValidationResponseTransfer $vertexValidationResponseTransfer,
    ): void {
        if (!$vertexItemTransfer->getId()) {
            $vertexValidationResponseTransfer->addMessage(sprintf(static::ERROR_ITEM_FIELD_IS_REQUIRED, VertexItemTransfer::ID, $vertexItemTransfer->getSku()));
        }

        if (!$vertexItemTransfer->getPriceAmount()) {
            $vertexValidationResponseTransfer->addMessage(sprintf(static::ERROR_ITEM_FIELD_IS_REQUIRED, VertexItemTransfer::PRICE_AMOUNT, $vertexItemTransfer->getSku()));
        }

        if ($vertexItemTransfer->getDiscountAmount() === null) {
            $vertexValidationResponseTransfer->addMessage(sprintf(static::ERROR_ITEM_FIELD_IS_REQUIRED, VertexItemTransfer::DISCOUNT_AMOUNT, $vertexItemTransfer->getSku()));
        }

        if (!$vertexItemTransfer->getQuantity()) {
            $vertexValidationResponseTransfer->addMessage(sprintf(static::ERROR_ITEM_FIELD_IS_REQUIRED, VertexItemTransfer::QUANTITY, $vertexItemTransfer->getSku()));
        }

        if (!$vertexItemTransfer->getSku()) {
            $vertexValidationResponseTransfer->addMessage(sprintf(static::ERROR_ITEM_FIELD_IS_REQUIRED, VertexItemTransfer::SKU, $vertexItemTransfer->getSku()));
        }

        if ($vertexItemTransfer->getShippingAddress()) {
            $this->addressValidator->validate($vertexItemTransfer->getShippingAddress(), VertexItemTransfer::SHIPPING_ADDRESS, $vertexValidationResponseTransfer);
        }

        if ($vertexItemTransfer->getSellerAddress()) {
            $this->addressValidator->validate($vertexItemTransfer->getSellerAddress(), VertexItemTransfer::SELLER_ADDRESS, $vertexValidationResponseTransfer);
        }

        if ($vertexItemTransfer->getBillingAddress()) {
            $this->addressValidator->validate($vertexItemTransfer->getBillingAddress(), VertexItemTransfer::BILLING_ADDRESS, $vertexValidationResponseTransfer);
        }

        if ($vertexItemTransfer->getVertexShippingWarehouses()->count()) {
            foreach ($vertexItemTransfer->getVertexShippingWarehouses() as $vertexShippingWarehouseTransfer) {
                $this->shippingWarehouseValidator->validate(
                    $vertexShippingWarehouseTransfer,
                    $vertexValidationResponseTransfer,
                );
            }
        }
    }
}
