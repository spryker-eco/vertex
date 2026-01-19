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
 * @method \Spryker\Zed\Vertex\Business\VertexFacadeInterface getFacade()
 * @method \Spryker\Zed\Vertex\Persistence\VertexRepositoryInterface getRepository()
 * @method \Spryker\Zed\Vertex\Communication\VertexCommunicationFactory getFactory()
 */
class GatewayController extends AbstractGatewayController
{
    /**
     * @param \Generated\Shared\Transfer\VertexValidationRequestTransfer $VertexValidationRequestTransfer
     *
     * @return \Generated\Shared\Transfer\VertexValidationResponseTransfer
     */
    public function validateTaxIdAction(VertexValidationRequestTransfer $VertexValidationRequestTransfer): VertexValidationResponseTransfer
    {
        return $this->getFacade()
            ->validateTaxId($VertexValidationRequestTransfer);
    }
}
