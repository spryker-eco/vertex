<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEcoTest\Client\Vertex\Builder;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\VertexAddressTransfer;
use SprykerEco\Client\Vertex\Builder\LocationMapper;

/**
 * Auto-generated group annotations
 *
 * @group SprykerEcoTest
 * @group Client
 * @group Vertex
 * @group Builder
 * @group LocationMapperTest
 * Add your own group annotations below this line
 */
class LocationMapperTest extends Unit
{
    /**
     * @return void
     */
    public function testMapVertexAddressTransferToVertexLocationTransferMapsAllFields(): void
    {
        // Arrange
        $vertexAddressTransfer = $this->createVertexAddressTransfer('Apt 4');

        // Act
        $vertexLocationTransfer = (new LocationMapper())->mapVertexAddressTransferToVertexLocationTransfer($vertexAddressTransfer);

        // Assert
        $this->assertSame('123 Main St', $vertexLocationTransfer->getStreetAddress1());
        $this->assertSame('Apt 4', $vertexLocationTransfer->getStreetAddress2());
        $this->assertSame('New York', $vertexLocationTransfer->getCity());
        $this->assertSame('NY', $vertexLocationTransfer->getMainDivision());
        $this->assertSame('10001', $vertexLocationTransfer->getPostalCode());
        $this->assertSame('US', $vertexLocationTransfer->getCountry());
    }

    /**
     * An empty second address line is not sent to Vertex, it is nulled out. This is what makes
     * address2 optional in practice, so the address validator must not require a non-null value.
     *
     * @return void
     */
    public function testMapVertexAddressTransferToVertexLocationTransferNullsAnEmptyStreetAddress2(): void
    {
        // Arrange
        $vertexAddressTransfer = $this->createVertexAddressTransfer('');

        // Act
        $vertexLocationTransfer = (new LocationMapper())->mapVertexAddressTransferToVertexLocationTransfer($vertexAddressTransfer);

        // Assert
        $this->assertNull($vertexLocationTransfer->getStreetAddress2());
    }

    /**
     * A null and an empty address2 have to produce an identical location, otherwise accepting a
     * null value in the validator would change the request rather than only the validation.
     *
     * @return void
     */
    public function testMapVertexAddressTransferToVertexLocationTransferTreatsNullAndEmptyStreetAddress2Alike(): void
    {
        // Arrange
        $locationMapper = new LocationMapper();

        // Act
        $vertexLocationTransferFromNull = $locationMapper->mapVertexAddressTransferToVertexLocationTransfer(
            $this->createVertexAddressTransfer(null),
        );
        $vertexLocationTransferFromEmptyString = $locationMapper->mapVertexAddressTransferToVertexLocationTransfer(
            $this->createVertexAddressTransfer(''),
        );

        // Assert
        $this->assertNull($vertexLocationTransferFromNull->getStreetAddress2());
        $this->assertSame(
            $vertexLocationTransferFromEmptyString->toArray(),
            $vertexLocationTransferFromNull->toArray(),
        );
    }

    /**
     * @param string|null $address2
     *
     * @return \Generated\Shared\Transfer\VertexAddressTransfer
     */
    protected function createVertexAddressTransfer(?string $address2): VertexAddressTransfer
    {
        return (new VertexAddressTransfer())
            ->setAddress1('123 Main St')
            ->setAddress2($address2)
            ->setCity('New York')
            ->setState('NY')
            ->setZipCode('10001')
            ->setCountry('US');
    }
}
