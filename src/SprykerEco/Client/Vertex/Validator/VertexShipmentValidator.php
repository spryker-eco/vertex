<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Client\Vertex\Validator;

use Generated\Shared\Transfer\VertexShipmentTransfer;
use Generated\Shared\Transfer\VertexValidationResponseTransfer;

class VertexShipmentValidator implements VertexShipmentValidatorInterface
{
    protected const ERROR_SHIPMENT_FIELD_IS_REQUIRED = 'Shipment field %s is required';

    public function __construct(protected VertexAddressValidatorInterface $addressValidator)
    {
    }

    public function validate(
        VertexShipmentTransfer $vertexShipmentTransfer,
        VertexValidationResponseTransfer $vertexValidationResponseTransfer,
    ): void {
        if ($vertexShipmentTransfer->getId() === null) {
            $vertexValidationResponseTransfer->addMessage(sprintf(static::ERROR_SHIPMENT_FIELD_IS_REQUIRED, VertexShipmentTransfer::ID));
        }

        if ($vertexShipmentTransfer->getPriceAmount() === null) {
            $vertexValidationResponseTransfer->addMessage(sprintf(static::ERROR_SHIPMENT_FIELD_IS_REQUIRED, VertexShipmentTransfer::PRICE_AMOUNT));
        }

        if (!$vertexShipmentTransfer->getShippingAddress()) {
            $vertexValidationResponseTransfer->addMessage(sprintf(static::ERROR_SHIPMENT_FIELD_IS_REQUIRED, VertexShipmentTransfer::SHIPPING_ADDRESS));
        }

        if ($vertexShipmentTransfer->getShippingAddress()) {
            $this->addressValidator->validate($vertexShipmentTransfer->getShippingAddress(), VertexShipmentTransfer::SHIPPING_ADDRESS, $vertexValidationResponseTransfer);
        }

        if (!$vertexShipmentTransfer->getBillingAddress()) {
            return;
        }

        $this->addressValidator->validate($vertexShipmentTransfer->getBillingAddress(), VertexShipmentTransfer::BILLING_ADDRESS, $vertexValidationResponseTransfer);
    }
}
