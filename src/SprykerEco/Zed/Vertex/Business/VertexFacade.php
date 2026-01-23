<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Vertex\Business;

use Generated\Shared\Transfer\CalculableObjectTransfer;
use Generated\Shared\Transfer\OrderTransfer;
use Generated\Shared\Transfer\VertexConfigCriteriaTransfer;
use Generated\Shared\Transfer\VertexConfigTransfer;
use Generated\Shared\Transfer\VertexValidationRequestTransfer;
use Generated\Shared\Transfer\VertexValidationResponseTransfer;
use Spryker\Zed\Kernel\Business\AbstractFacade;

/**
 * @method \SprykerEco\Zed\Vertex\Business\VertexBusinessFactory getFactory()
 * @method \SprykerEco\Zed\Vertex\Persistence\VertexRepositoryInterface getRepository()
 * @method \SprykerEco\Zed\Vertex\Persistence\VertexEntityManagerInterface getEntityManager()
 */
class VertexFacade extends AbstractFacade implements VertexFacadeInterface
{
    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\CalculableObjectTransfer $calculableObjectTransfer
     *
     * @return void
     */
    public function recalculate(CalculableObjectTransfer $calculableObjectTransfer): void
    {
        $this->getFactory()->createCalculator()->recalculate($calculableObjectTransfer);
    }

//    /**
//     * {@inheritDoc}
//     *
//     * @api
//     *
//     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
//     *
//     * @return void
////     */
//    public function sendSubmitPaymentTaxInvoiceMessage(OrderTransfer $orderTransfer): void
//    {
//        $this->getFactory()->createPaymentSubmitTaxInvoiceSender()->sendSubmitPaymentTaxInvoiceMessage($orderTransfer);
//    }

    public function handleSubmitPaymentTaxInvoice(OrderTransfer  $orderTransfer): void
    {
        $this->getFactory()->createPaymentSubmitTaxInvoiceHandler()->sendSubmitPaymentTaxInvoiceMessage($orderTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param array<int> $orderItemIds
     * @param int $idSalesOrder
     *
     * @return void
     */
    public function processOrderRefund(array $orderItemIds, int $idSalesOrder): void
    {
        $this->getFactory()->createRefundProcessor()->processOrderRefund($orderItemIds, $idSalesOrder);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\VertexValidationRequestTransfer $vertexValidationRequestTransfer
     *
     * @return \Generated\Shared\Transfer\VertexValidationResponseTransfer
     */
    public function validateTaxId(VertexValidationRequestTransfer $vertexValidationRequestTransfer): VertexValidationResponseTransfer
    {
        return $this->getFactory()->createTaxIdValidator()->validate($vertexValidationRequestTransfer);
    }
}
