<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Client\Vertex\Validator;

use Generated\Shared\Transfer\VertexItemTransfer;
use Generated\Shared\Transfer\VertexSaleTransfer;
use Generated\Shared\Transfer\VertexShipmentTransfer;
use Generated\Shared\Transfer\VertexValidationResponseTransfer;

class SaleValidator
{
    protected const ERROR_FIELD_IS_REQUIRED = 'Field %s is required';

    /**
     * @var \SprykerEco\Client\Vertex\Validator\ItemValidator
     */
    protected $itemValidator;

    /**
     * @var \SprykerEco\Client\Vertex\Validator\ShipmentValidator
     */
    protected $shipmentValidator;

    /**
     * @param \SprykerEco\Client\Vertex\Validator\ItemValidator $itemValidator
     * @param \SprykerEco\Client\Vertex\Validator\ShipmentValidator $shipmentValidator
     */
    public function __construct(
        ItemValidator $itemValidator,
        ShipmentValidator $shipmentValidator
    ) {
        $this->itemValidator = $itemValidator;
        $this->shipmentValidator = $shipmentValidator;
    }

    /**
     * @param \Generated\Shared\Transfer\VertexSaleTransfer $vertexSaleTransfer
     * @param \Generated\Shared\Transfer\VertexValidationResponseTransfer $vertexValidationResponseTransfer
     * @param bool $requireRefundableAmountForItems
     * @param bool $requireDiscountAmountForShipments
     *
     * @return \Generated\Shared\Transfer\VertexValidationResponseTransfer
     */
    public function validate(
        VertexSaleTransfer $vertexSaleTransfer,
        VertexValidationResponseTransfer $vertexValidationResponseTransfer,
        bool $requireRefundableAmountForItems = false,
        bool $requireDiscountAmountForShipments = true
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

        if (!$vertexSaleTransfer->getTaxMetadata()) {
            $vertexValidationResponseTransfer->addMessage(sprintf(static::ERROR_FIELD_IS_REQUIRED, VertexSaleTransfer::TAX_METADATA));
        }

        if (!$vertexSaleTransfer->getItems()->count()) {
            $vertexValidationResponseTransfer->addMessage(sprintf(static::ERROR_FIELD_IS_REQUIRED, VertexSaleTransfer::ITEMS));
        }

        if (!$vertexSaleTransfer->getShipments()->count()) {
            $vertexValidationResponseTransfer->addMessage(sprintf(static::ERROR_FIELD_IS_REQUIRED, VertexSaleTransfer::SHIPMENTS));
        }

        foreach ($vertexSaleTransfer->getItems() as $index => $vertexItemTransfer) {
            $this->itemValidator->validate(
                $vertexItemTransfer,
                VertexSaleTransfer::ITEMS . '[' . $index . ']',
                $vertexValidationResponseTransfer,
                $requireRefundableAmountForItems
            );
        }

        foreach ($vertexSaleTransfer->getShipments() as $index => $vertexShipmentTransfer) {
            $this->shipmentValidator->validate(
                $vertexShipmentTransfer,
                VertexSaleTransfer::SHIPMENTS . '[' . $index . ']',
                $vertexValidationResponseTransfer,
                $requireDiscountAmountForShipments
            );
        }

        return $vertexValidationResponseTransfer;
    }
}

