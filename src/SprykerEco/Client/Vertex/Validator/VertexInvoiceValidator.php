<?php

namespace SprykerEco\Client\Vertex\Validator;

use Generated\Shared\Transfer\VertexCalculationRequestTransfer;
use Generated\Shared\Transfer\VertexSaleTransfer;
use Generated\Shared\Transfer\VertexValidationResponseTransfer;

class VertexInvoiceValidator implements VertexInvoiceValidatorInterface
{
    protected const ERROR_FIELD_IS_REQUIRED = 'Field %s is required';

    public function __construct(protected VertexSaleValidator $saleValidator)
    {
    }

    public function validate(VertexCalculationRequestTransfer $vertexCalculationRequestTransfer): VertexValidationResponseTransfer
    {
        $vertexValidationResponseTransfer = (new VertexValidationResponseTransfer());

        if (!$vertexCalculationRequestTransfer->getReportingDate()) {
            $vertexValidationResponseTransfer->addMessage(sprintf(static::ERROR_FIELD_IS_REQUIRED, VertexCalculationRequestTransfer::REPORTING_DATE));
        }

        $vertexSaleTransfer = $vertexCalculationRequestTransfer->getSale();
        if (!$vertexSaleTransfer) {
            $vertexValidationResponseTransfer->addMessage(sprintf(static::ERROR_FIELD_IS_REQUIRED, VertexCalculationRequestTransfer::SALE));

            return $vertexValidationResponseTransfer;
        }

        $vertexValidationResponseTransfer = $this->saleValidator->validate(
            $vertexSaleTransfer,
            $vertexValidationResponseTransfer,
        );

        foreach ($vertexSaleTransfer->getItems() as $vertexSaleItemTransfer) {
            if (!$vertexSaleItemTransfer->getRefundableAmount()) {
                $vertexValidationResponseTransfer->addMessage(sprintf(static::ERROR_FIELD_IS_REQUIRED, VertexSaleItemTransfer::REFUNDABLE_AMOUNT));
            }
        }

        if ($vertexValidationResponseTransfer->getMessages()) {
            $vertexValidationResponseTransfer->setIsSuccess(false);
        }

        return $vertexValidationResponseTransfer;
    }
}

