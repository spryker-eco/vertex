<?php

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerEco\Client\Vertex\Builder;

use Generated\Shared\Transfer\AddressTransfer;
use Generated\Shared\Transfer\VertexLocationTransfer;

class LocationMapper
{
    /**
     * @param \Generated\Shared\Transfer\AddressTransfer $addressTransfer
     *
     * @return \Generated\Shared\Transfer\VertexLocationTransfer
     */
    public function mapAddressTransferToVertexLocationTransfer(AddressTransfer $addressTransfer): VertexLocationTransfer
    {
        $vertexLocationTransfer = (new VertexLocationTransfer())
            ->setStreetAddress1($addressTransfer->getAddress1OrFail())
            ->setStreetAddress2($addressTransfer->getAddress2())
            ->setCity($addressTransfer->getCityOrFail())
            ->setMainDivision($addressTransfer->getState())
            ->setPostalCode($addressTransfer->getZipCodeOrFail())
            ->setCountry($addressTransfer->getCountryOrFail()?->getName());

        if (empty($vertexLocationTransfer->getStreetAddress2())) {
            $vertexLocationTransfer->setStreetAddress2(null);
        }

        return $vertexLocationTransfer;
    }
}
