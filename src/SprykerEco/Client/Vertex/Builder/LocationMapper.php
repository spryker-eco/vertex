<?php

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
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
