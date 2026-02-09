<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Client\Vertex\Authenticator;

use Generated\Shared\Transfer\VertexApiCredentialTransfer;
use Generated\Shared\Transfer\VertexAuthResponseTransfer;
use Generated\Shared\Transfer\VertexConfigTransfer;
use SprykerEco\Client\Vertex\Api\V2\Client\SecurityApiInterface;

class VertexApiAuthenticator implements VertexApiAuthenticatorInterface
{
    protected SecurityApiInterface $securityApi;

    /**
     * @param \SprykerEco\Client\Vertex\Api\V2\Client\SecurityApiInterface $securityApi
     */
    public function __construct(SecurityApiInterface $securityApi)
    {
        $this->securityApi = $securityApi;
    }

    /**
     * @param \Generated\Shared\Transfer\VertexConfigTransfer $vertexConfigTransfer
     *
     * @return \Generated\Shared\Transfer\VertexAuthResponseTransfer
     */
    public function authenticate(VertexConfigTransfer $vertexConfigTransfer): VertexAuthResponseTransfer
    {
        $vertexApiCredentialTransfer = (new VertexApiCredentialTransfer())
            ->fromArray($vertexConfigTransfer->toArray(), true);

        $vertexAuthResponseTransfer = $this->securityApi->requestAccessToken($vertexApiCredentialTransfer);

        return $vertexAuthResponseTransfer;
    }
}
