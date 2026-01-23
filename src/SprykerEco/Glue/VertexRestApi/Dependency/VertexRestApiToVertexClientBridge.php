<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Glue\VertexRestApi\Dependency;

use Generated\Shared\Transfer\VertexValidationRequestTransfer;
use Generated\Shared\Transfer\VertexValidationResponseTransfer;
use SprykerEco\Client\Vertex\VertexClientInterface;

class VertexRestApiToVertexClientBridge implements VertexRestApiToVertexClientInterface
{
    /**
     * @param \SprykerEco\Client\Vertex\VertexClientInterface $VertexClient
     */
    public function __construct(protected VertexClientInterface $vertexClient)
    {
    }

    /**
     * @param \Generated\Shared\Transfer\VertexValidationRequestTransfer $VertexValidationRequestTransfer
     *
     * @return \Generated\Shared\Transfer\VertexValidationResponseTransfer
     */
    public function requestTaxIdValidation(VertexValidationRequestTransfer $vertexValidationRequestTransfer): VertexValidationResponseTransfer
    {
        return $this->vertexClient->requestTaxIdValidation($vertexValidationRequestTransfer);
    }
}
