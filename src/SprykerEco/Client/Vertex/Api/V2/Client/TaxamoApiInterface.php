<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Client\Vertex\Api\V2\Client;

use Generated\Shared\Transfer\TaxamoApiRequestTransfer;
use Generated\Shared\Transfer\VertexApiResponseTransfer;

interface TaxamoApiInterface
{
    public function validateTaxId(TaxamoApiRequestTransfer $taxamoApiRequestTransfer): VertexApiResponseTransfer;
}
