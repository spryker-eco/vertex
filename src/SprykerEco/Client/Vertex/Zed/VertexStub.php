<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Client\Vertex\Zed;

use Generated\Shared\Transfer\VertexValidationRequestTransfer;
use Generated\Shared\Transfer\VertexValidationResponseTransfer;
use Spryker\Client\ZedRequest\ZedRequestClientInterface;

class VertexStub implements VertexStubInterface
{
    /**
     * @param \Spryker\Client\ZedRequest\ZedRequestClientInterface $zedRequestClient
     */
    public function __construct(protected ZedRequestClientInterface $zedRequestClient)
    {
    }

   /**
    * @param \Generated\Shared\Transfer\VertexValidationRequestTransfer $vertexValidationRequestTransfer
    *
    * @return \Generated\Shared\Transfer\VertexValidationResponseTransfer
    */
    public function requestTaxIdValidation(VertexValidationRequestTransfer $vertexValidationRequestTransfer): VertexValidationResponseTransfer
    {
        /** @var \Generated\Shared\Transfer\VertexValidationResponseTransfer $vertexValidationResponseTransfer */
        $vertexValidationResponseTransfer = $this->zedRequestClient->call('/vertex/gateway/request-tax-id-validation', $vertexValidationRequestTransfer);

        return $vertexValidationResponseTransfer;
    }
}
