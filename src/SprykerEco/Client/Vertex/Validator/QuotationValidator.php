<?php

namespace SprykerEco\Client\Vertex\Validator;

use Generated\Shared\Transfer\VertexCalculationRequestTransfer;
use Generated\Shared\Transfer\VertexSaleTransfer;
use Generated\Shared\Transfer\VertexValidationResponseTransfer;

class QuotationValidator
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

        // For quotations, reportingDate is optional (unlike refunds where it's required)

        $vertexSaleTransfer = $vertexCalculationRequestTransfer->getSale();
        if (!$vertexSaleTransfer) {
            $vertexValidationResponseTransfer->addMessage(sprintf(static::ERROR_FIELD_IS_REQUIRED, VertexCalculationRequestTransfer::SALE));

            return $vertexValidationResponseTransfer;
        }

        // For quotations: refundableAmount is optional for items, discountAmount is required for shipments
        return $this->saleValidator->validate(
            $vertexSaleTransfer,
            $vertexValidationResponseTransfer,
            false, // requireRefundableAmountForItems
            true   // requireDiscountAmountForShipments
        );
    }

    /**
     * @param \Generated\Shared\Transfer\VertexSaleTransfer $vertexSaleTransfer
     *
     * @return \Generated\Shared\Transfer\VertexValidationResponseTransfer
     */
    public function validateSale(VertexSaleTransfer $vertexSaleTransfer): VertexValidationResponseTransfer
    {
        $vertexValidationResponseTransfer = (new VertexValidationResponseTransfer());

        // For quotations: refundableAmount is optional for items, discountAmount is required for shipments
        return $this->saleValidator->validate(
            $vertexSaleTransfer,
            $vertexValidationResponseTransfer,
            false, // requireRefundableAmountForItems
            true   // requireDiscountAmountForShipments
        );
    }
}
