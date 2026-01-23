<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Vertex\Communication\Controller;

use Generated\Shared\Transfer\VertexValidationRequestTransfer;
use Generated\Shared\Transfer\VertexValidationResponseTransfer;
use Spryker\Zed\Kernel\Communication\Controller\AbstractGatewayController;

/**
 * @method \SprykerEco\Zed\Vertex\Business\VertexFacadeInterface getFacade()
 * @method \SprykerEco\Zed\Vertex\Persistence\VertexRepositoryInterface getRepository()
 * @method \SprykerEco\Zed\Vertex\Communication\VertexCommunicationFactory getFactory()
 */
class GatewayController extends AbstractGatewayController
{
    /**
     * @param \Generated\Shared\Transfer\VertexValidationRequestTransfer $vertexValidationRequestTransfer
     *
     * @return \Generated\Shared\Transfer\VertexValidationResponseTransfer
     */
    public function requestTaxIdValidationAction(VertexValidationRequestTransfer $vertexValidationRequestTransfer): VertexValidationResponseTransfer
    {
        return $this->getFacade()
            ->validateTaxId($vertexValidationRequestTransfer);
    }
}
