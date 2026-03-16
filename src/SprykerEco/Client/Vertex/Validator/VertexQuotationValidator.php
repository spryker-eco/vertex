<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Client\Vertex\Validator;

use Generated\Shared\Transfer\VertexCalculationRequestTransfer;
use Generated\Shared\Transfer\VertexShipmentTransfer;
use Generated\Shared\Transfer\VertexValidationResponseTransfer;

class VertexQuotationValidator implements VertexValidatorInterface
{
    protected const ERROR_FIELD_IS_REQUIRED = 'Field %s is required';

    public function __construct(protected VertexSaleValidatorInterface $saleValidator)
    {
    }

    public function validate(VertexCalculationRequestTransfer $vertexCalculationRequestTransfer): VertexValidationResponseTransfer
    {
        $vertexValidationResponseTransfer = (new VertexValidationResponseTransfer())->setIsValid(true);

        $vertexSaleTransfer = $vertexCalculationRequestTransfer->getSale();
        if (!$vertexSaleTransfer) {
            $vertexValidationResponseTransfer->addMessage(sprintf(static::ERROR_FIELD_IS_REQUIRED, VertexCalculationRequestTransfer::SALE));

            return $vertexValidationResponseTransfer->setIsValid(false);
        }

        $vertexValidationResponseTransfer = $this->saleValidator->validate(
            $vertexSaleTransfer,
            $vertexValidationResponseTransfer,
        );

        foreach ($vertexSaleTransfer->getShipments() as $vertexShipmentTransfer) {
            if ($vertexShipmentTransfer->getDiscountAmount() !== null) {
                continue;
            }

            $vertexValidationResponseTransfer->addMessage(sprintf(static::ERROR_FIELD_IS_REQUIRED, VertexShipmentTransfer::DISCOUNT_AMOUNT));
        }

        if ($vertexValidationResponseTransfer->getMessages()) {
            $vertexValidationResponseTransfer->setIsValid(false);
        }

        return $vertexValidationResponseTransfer;
    }
}
