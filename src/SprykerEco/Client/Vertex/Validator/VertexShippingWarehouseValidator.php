<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Client\Vertex\Validator;

use Generated\Shared\Transfer\VertexShippingWarehouseTransfer;
use Generated\Shared\Transfer\VertexValidationResponseTransfer;

class VertexShippingWarehouseValidator implements VertexShippingWarehouseValidatorInterface
{
    protected const ERROR_WAREHOUSE_FIELD_IS_REQUIRED = 'Field %s is required for shipping warehouse';

    public function __construct(protected VertexAddressValidatorInterface $addressValidator)
    {
    }

    public function validate(
        VertexShippingWarehouseTransfer $warehouse,
        VertexValidationResponseTransfer $vertexValidationResponseTransfer,
    ): void {
        if (!$warehouse->getQuantity()) {
            $vertexValidationResponseTransfer->addMessage(sprintf(static::ERROR_WAREHOUSE_FIELD_IS_REQUIRED, VertexShippingWarehouseTransfer::QUANTITY));
        }

        if (!$warehouse->getWarehouseAddress()) {
            return;
        }

        $this->addressValidator->validate(
            $warehouse->getWarehouseAddress(),
            VertexShippingWarehouseTransfer::WAREHOUSE_ADDRESS,
            $vertexValidationResponseTransfer,
        );
    }
}
