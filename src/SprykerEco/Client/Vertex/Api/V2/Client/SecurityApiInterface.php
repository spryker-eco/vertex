<?php

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerEco\Client\Vertex\Api\V2\Client;

use Generated\Shared\Transfer\VertexAuthResponseTransfer;
use Generated\Shared\Transfer\VertexApiCredentialTransfer;

interface SecurityApiInterface
{
    /**
     * @param \Generated\Shared\Transfer\VertexApiCredentialTransfer $vertexApiCredentialTransfer
     *
     * @return \Generated\Shared\Transfer\VertexAuthResponseTransfer
     */
    public function requestAccessToken(
        VertexApiCredentialTransfer $vertexApiCredentialTransfer
    ): VertexAuthResponseTransfer;
}
