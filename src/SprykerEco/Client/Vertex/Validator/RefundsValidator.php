<?php

namespace SprykerEco\Client\Vertex\Validator;

use Generated\Shared\Transfer\VertexCalculationRequestTransfer;
use Generated\Shared\Transfer\VertexSaleTransfer;
use Generated\Shared\Transfer\VertexValidationResponseTransfer;

class RefundsValidator
{
    protected const ERROR_FIELD_IS_REQUIRED = 'Field %s is required';

    /**
     * @var \SprykerEco\Client\Vertex\Validator\SaleValidator
     */
    protected $saleValidator;

    /**
     * @param \SprykerEco\Client\Vertex\Validator\SaleValidator $saleValidator
     */
    public function __construct(SaleValidator $saleValidator)
    {
        $this->saleValidator = $saleValidator;
    }

    /**
     * @param \Generated\Shared\Transfer\VertexCalculationRequestTransfer $vertexCalculationRequestTransfer
     *
     * @return \Generated\Shared\Transfer\VertexValidationResponseTransfer
     */
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

        // For refunds: refundableAmount is required for items, discountAmount is optional for shipments
        return $this->saleValidator->validate(
            $vertexSaleTransfer,
            $vertexValidationResponseTransfer,
            true,  // requireRefundableAmountForItems
            false  // requireDiscountAmountForShipments
        );
    }
}

