<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Client\Vertex\Builder;

use Generated\Shared\Transfer\VertexAddressTransfer;
use Generated\Shared\Transfer\VertexLocationTransfer;

class LocationMapper
{
    /**
     * @param \Generated\Shared\Transfer\VertexAddressTransfer $vertexAddressTransfer
     *
     * @return \Generated\Shared\Transfer\VertexLocationTransfer
     */
    public function mapVertexAddressTransferToVertexLocationTransfer(VertexAddressTransfer $vertexAddressTransfer): VertexLocationTransfer
    {
        $vertexLocationTransfer = (new VertexLocationTransfer())
            ->setStreetAddress1($vertexAddressTransfer->getAddress1OrFail())
            ->setStreetAddress2($vertexAddressTransfer->getAddress2())
            ->setCity($vertexAddressTransfer->getCityOrFail())
            ->setMainDivision($vertexAddressTransfer->getState())
            ->setPostalCode($vertexAddressTransfer->getZipCodeOrFail())
            ->setCountry($vertexAddressTransfer->getCountryOrFail());

        if (empty($vertexLocationTransfer->getStreetAddress2())) {
            $vertexLocationTransfer->setStreetAddress2(null);
        }

        return $vertexLocationTransfer;
    }
}
