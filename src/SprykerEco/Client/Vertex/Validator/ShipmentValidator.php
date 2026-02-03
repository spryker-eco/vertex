<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Client\Vertex\Validator;

use Generated\Shared\Transfer\VertexShipmentTransfer;
use Generated\Shared\Transfer\VertexValidationResponseTransfer;

class ShipmentValidator
{
    protected const ERROR_SHIPMENT_FIELD_IS_REQUIRED = 'Shipment field %s is required';

    /**
     * @var \SprykerEco\Client\Vertex\Validator\AddressValidator
     */
    protected $addressValidator;

    /**
     * @param \SprykerEco\Client\Vertex\Validator\AddressValidator $addressValidator
     */
    public function __construct(AddressValidator $addressValidator)
    {
        $this->addressValidator = $addressValidator;
    }

    /**
     * @param \Generated\Shared\Transfer\VertexShipmentTransfer $vertexShipmentTransfer
     * @param string $fieldName
     * @param \Generated\Shared\Transfer\VertexValidationResponseTransfer $vertexValidationResponseTransfer
     * @param bool $requireDiscountAmount
     *
     * @return void
     */
    public function validate(
        VertexShipmentTransfer $vertexShipmentTransfer,
        string $fieldName,
        VertexValidationResponseTransfer $vertexValidationResponseTransfer,
        bool $requireDiscountAmount = true
    ): void {
        if (!$vertexShipmentTransfer->getId()) {
            $vertexValidationResponseTransfer->addMessage(sprintf(static::ERROR_SHIPMENT_FIELD_IS_REQUIRED, $fieldName . '.' . VertexShipmentTransfer::ID));
        }

        if ($vertexShipmentTransfer->getPriceAmount() === null) {
            $vertexValidationResponseTransfer->addMessage(sprintf(static::ERROR_SHIPMENT_FIELD_IS_REQUIRED, $fieldName . '.' . VertexShipmentTransfer::PRICE_AMOUNT));
        }

        if ($requireDiscountAmount && $vertexShipmentTransfer->getDiscountAmount() === null) {
            $vertexValidationResponseTransfer->addMessage(sprintf(static::ERROR_SHIPMENT_FIELD_IS_REQUIRED, $fieldName . '.' . VertexShipmentTransfer::DISCOUNT_AMOUNT));
        }

        if (!$vertexShipmentTransfer->getShippingAddress()) {
            $vertexValidationResponseTransfer->addMessage(sprintf(static::ERROR_SHIPMENT_FIELD_IS_REQUIRED, $fieldName . '.' . VertexShipmentTransfer::SHIPPING_ADDRESS));
        } else {
            $this->addressValidator->validate($vertexShipmentTransfer->getShippingAddress(), $fieldName . '.' . VertexShipmentTransfer::SHIPPING_ADDRESS, $vertexValidationResponseTransfer);
        }

        if ($vertexShipmentTransfer->getBillingAddress()) {
            $this->addressValidator->validate($vertexShipmentTransfer->getBillingAddress(), $fieldName . '.' . VertexShipmentTransfer::BILLING_ADDRESS, $vertexValidationResponseTransfer);
        }
    }
}

