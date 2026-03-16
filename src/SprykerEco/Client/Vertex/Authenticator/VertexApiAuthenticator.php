<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Client\Vertex\Authenticator;

use Generated\Shared\Transfer\VertexApiCredentialTransfer;
use Generated\Shared\Transfer\VertexAuthResponseTransfer;
use Generated\Shared\Transfer\VertexConfigTransfer;
use SprykerEco\Client\Vertex\Api\V2\Client\SecurityApiInterface;

class VertexApiAuthenticator implements VertexApiAuthenticatorInterface
{
    public function __construct(protected SecurityApiInterface $securityApi)
    {
    }

    public function authenticate(VertexConfigTransfer $vertexConfigTransfer): VertexAuthResponseTransfer
    {
        $vertexApiCredentialTransfer = (new VertexApiCredentialTransfer())
            ->fromArray($vertexConfigTransfer->toArray(), true);

        return $this->securityApi->requestAccessToken($vertexApiCredentialTransfer);
    }
}
