<?php

namespace SprykerEco\Zed\Vertex\Business\Validator;

use Generated\Shared\Transfer\VertexValidationResponseTransfer;
use Generated\Shared\Transfer\VertexSaleTransfer;

class QuotationValidator
{
    protected const ERROR_FIELD_IS_REQUIRED = 'Field %s is required';

    public function validate(VertexSaleTransfer $vertexSaleTransfer): VertexValidationResponseTransfer
    {
        $vertexValidationResponseTransfer = (new VertexValidationResponseTransfer());

        if (!$vertexSaleTransfer->getTransactionId()) {
            $vertexValidationResponseTransfer->addMessage(sprintf(static::ERROR_FIELD_IS_REQUIRED, VertexSaleTransfer::TRANSACTION_ID));
        }

        if (!$vertexValidationResponseTransfer->getDocumentNumber()) {
            $vertexValidationResponseTransfer->addMessage(sprintf(static::ERROR_FIELD_IS_REQUIRED, VertexSaleTransfer::DOCUMENT_NUMBER));
        }

        if (!$vertexValidationResponseTransfer->getDocumentDate()) {
            $vertexValidationResponseTransfer->addMessage(sprintf(static::ERROR_FIELD_IS_REQUIRED, VertexSaleTransfer::DOCUMENT_DATE));
        }

        if (!$vertexValidationResponseTransfer->getTaxMetadata()) {
            $vertexValidationResponseTransfer->addMessage(sprintf(static::ERROR_FIELD_IS_REQUIRED, VertexSaleTransfer::TAX_METADATA));
        }



        return $vertexValidationResponseTransfer;
    }
}
