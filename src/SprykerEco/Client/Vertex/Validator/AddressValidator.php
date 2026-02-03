<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Client\Vertex\Validator;

use Generated\Shared\Transfer\VertexAddressTransfer;
use Generated\Shared\Transfer\VertexValidationResponseTransfer;

class AddressValidator
{
    protected const ERROR_ADDRESS_FIELD_IS_REQUIRED = 'Address field %s is required';

    /**
     * @param \Generated\Shared\Transfer\VertexAddressTransfer $address
     * @param string $fieldName
     * @param \Generated\Shared\Transfer\VertexValidationResponseTransfer $vertexValidationResponseTransfer
     *
     * @return void
     */
    public function validate(
        VertexAddressTransfer $address,
        string $fieldName,
        VertexValidationResponseTransfer $vertexValidationResponseTransfer
    ): void {
        if (!$address->getAddress1()) {
            $vertexValidationResponseTransfer->addMessage(sprintf(static::ERROR_ADDRESS_FIELD_IS_REQUIRED, $fieldName . '.' . VertexAddressTransfer::ADDRESS1));
        }

        if ($address->getAddress2() === null) {
            $vertexValidationResponseTransfer->addMessage(sprintf(static::ERROR_ADDRESS_FIELD_IS_REQUIRED, $fieldName . '.' . VertexAddressTransfer::ADDRESS2));
        }

        if (!$address->getCity()) {
            $vertexValidationResponseTransfer->addMessage(sprintf(static::ERROR_ADDRESS_FIELD_IS_REQUIRED, $fieldName . '.' . VertexAddressTransfer::CITY));
        }

        if (!$address->getCountry()) {
            $vertexValidationResponseTransfer->addMessage(sprintf(static::ERROR_ADDRESS_FIELD_IS_REQUIRED, $fieldName . '.' . VertexAddressTransfer::COUNTRY));
        }

        if (!$address->getZipCode()) {
            $vertexValidationResponseTransfer->addMessage(sprintf(static::ERROR_ADDRESS_FIELD_IS_REQUIRED, $fieldName . '.' . VertexAddressTransfer::ZIP_CODE));
        }
    }
}

