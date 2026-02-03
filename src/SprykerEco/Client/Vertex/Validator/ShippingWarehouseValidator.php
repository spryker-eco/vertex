<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Client\Vertex\Validator;

use Generated\Shared\Transfer\VertexShippingWarehouseTransfer;
use Generated\Shared\Transfer\VertexValidationResponseTransfer;

class ShippingWarehouseValidator
{
    protected const ERROR_WAREHOUSE_FIELD_IS_REQUIRED = 'Warehouse field %s is required';

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
     * @param \Generated\Shared\Transfer\VertexShippingWarehouseTransfer $warehouse
     * @param string $fieldName
     * @param \Generated\Shared\Transfer\VertexValidationResponseTransfer $vertexValidationResponseTransfer
     *
     * @return void
     */
    public function validate(
        VertexShippingWarehouseTransfer $warehouse,
        string $fieldName,
        VertexValidationResponseTransfer $vertexValidationResponseTransfer
    ): void {
        if ($warehouse->getQuantity() === null) {
            $vertexValidationResponseTransfer->addMessage(sprintf(static::ERROR_WAREHOUSE_FIELD_IS_REQUIRED, $fieldName . '.' . VertexShippingWarehouseTransfer::QUANTITY));
        }

        if ($warehouse->getWarehouseAddress()) {
            $this->addressValidator->validate(
                $warehouse->getWarehouseAddress(),
                $fieldName . '.' . VertexShippingWarehouseTransfer::WAREHOUSE_ADDRESS,
                $vertexValidationResponseTransfer
            );
        }
    }
}

