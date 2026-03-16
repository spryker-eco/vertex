<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Client\Vertex\Api\V2\Client;

use Generated\Shared\Transfer\VertexApiCredentialTransfer;
use Generated\Shared\Transfer\VertexAuthResponseTransfer;

interface SecurityApiInterface
{
    public function requestAccessToken(
        VertexApiCredentialTransfer $vertexApiCredentialTransfer,
    ): VertexAuthResponseTransfer;
}
