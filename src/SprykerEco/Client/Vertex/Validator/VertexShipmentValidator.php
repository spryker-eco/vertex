<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Client\Vertex\Validator;

use Generated\Shared\Transfer\VertexShipmentTransfer;
use Generated\Shared\Transfer\VertexValidationResponseTransfer;

class VertexShipmentValidator implements VertexShipmentValidatorInterface
{
    protected const ERROR_SHIPMENT_FIELD_IS_REQUIRED = 'Shipment field %s is required';

    /**
     * @var \SprykerEco\Client\Vertex\Validator\VertexAddressValidator
     */
    protected $addressValidator;

    /**
     * @param \SprykerEco\Client\Vertex\Validator\VertexAddressValidator $addressValidator
     */
    public function __construct(VertexAddressValidator $addressValidator)
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
        VertexValidationResponseTransfer $vertexValidationResponseTransfer,
    ): void {
        if (!$vertexShipmentTransfer->getId()) {
            $vertexValidationResponseTransfer->addMessage(sprintf(static::ERROR_SHIPMENT_FIELD_IS_REQUIRED,  VertexShipmentTransfer::ID));
        }

        if (!$vertexShipmentTransfer->getPriceAmount()) {
            $vertexValidationResponseTransfer->addMessage(sprintf(static::ERROR_SHIPMENT_FIELD_IS_REQUIRED,  VertexShipmentTransfer::PRICE_AMOUNT));
        }

        if (!$vertexShipmentTransfer->getShipmentMethodKey()) {
            $vertexValidationResponseTransfer->addMessage(sprintf(static::ERROR_SHIPMENT_FIELD_IS_REQUIRED,  VertexShipmentTransfer::SHIPMENT_METHOD_KEY));
        }

        if (!$vertexShipmentTransfer->getShippingAddress()) {
            $vertexValidationResponseTransfer->addMessage(sprintf(static::ERROR_SHIPMENT_FIELD_IS_REQUIRED,  VertexShipmentTransfer::SHIPPING_ADDRESS));
        }

        if ($vertexShipmentTransfer->getShippingAddress()) {
            $this->addressValidator->validate($vertexShipmentTransfer->getShippingAddress(),  VertexShipmentTransfer::SHIPPING_ADDRESS, $vertexValidationResponseTransfer);
        }

        if ($vertexShipmentTransfer->getBillingAddress()) {
            $this->addressValidator->validate($vertexShipmentTransfer->getBillingAddress(),  VertexShipmentTransfer::BILLING_ADDRESS, $vertexValidationResponseTransfer);
        }
    }
}

