<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Client\Vertex\Validator;

use Generated\Shared\Transfer\VertexSaleTransfer;
use Generated\Shared\Transfer\VertexValidationResponseTransfer;

class VertexSaleValidator implements VertexSaleValidatorInterface
{
    protected const ERROR_FIELD_IS_REQUIRED = 'Field %s is required';

    public function __construct(
        protected VertexItemValidatorInterface $itemValidator,
        protected VertexShipmentValidatorInterface $shipmentValidator
    ) {
    }

    public function validate(
        VertexSaleTransfer $vertexSaleTransfer,
        VertexValidationResponseTransfer $vertexValidationResponseTransfer,
    ): VertexValidationResponseTransfer {
        if (!$vertexSaleTransfer->getTransactionId()) {
            $vertexValidationResponseTransfer->addMessage(sprintf(static::ERROR_FIELD_IS_REQUIRED, VertexSaleTransfer::TRANSACTION_ID));
        }

        if (!$vertexSaleTransfer->getDocumentNumber()) {
            $vertexValidationResponseTransfer->addMessage(sprintf(static::ERROR_FIELD_IS_REQUIRED, VertexSaleTransfer::DOCUMENT_NUMBER));
        }

        if (!$vertexSaleTransfer->getDocumentDate()) {
            $vertexValidationResponseTransfer->addMessage(sprintf(static::ERROR_FIELD_IS_REQUIRED, VertexSaleTransfer::DOCUMENT_DATE));
        }

        if (!$vertexSaleTransfer->getItems()->count()) {
            $vertexValidationResponseTransfer->addMessage(sprintf(static::ERROR_FIELD_IS_REQUIRED, VertexSaleTransfer::ITEMS));
        }

        foreach ($vertexSaleTransfer->getItems() as $vertexItemTransfer) {
            $this->itemValidator->validate(
                $vertexItemTransfer,
                $vertexValidationResponseTransfer,
            );
        }

        foreach ($vertexSaleTransfer->getShipments() as $vertexShipmentTransfer) {
            $this->shipmentValidator->validate(
                $vertexShipmentTransfer,
                $vertexValidationResponseTransfer,
            );
        }

        return $vertexValidationResponseTransfer;
    }
}
