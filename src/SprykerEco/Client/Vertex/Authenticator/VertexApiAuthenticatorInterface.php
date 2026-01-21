<?php

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerEco\Client\Vertex\Authenticator;

use Generated\Shared\Transfer\VertexAuthResponseTransfer;
use Generated\Shared\Transfer\VertexConfigTransfer;

interface VertexApiAuthenticatorInterface
{
    /**
     * @param \Generated\Shared\Transfer\VertexConfigTransfer $vertexConfigTransfer
     *
     * @return \Generated\Shared\Transfer\VertexAuthResponseTransfer
     */
    public function authenticate(VertexConfigTransfer $vertexConfigTransfer): VertexAuthResponseTransfer;
}
