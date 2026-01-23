<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Vertex\Communication\Plugin\Oms\Command;

use Generated\Shared\Transfer\OrderTransfer;
use Orm\Zed\Sales\Persistence\SpySalesOrder;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use Spryker\Zed\Oms\Business\Util\ReadOnlyArrayObject;
use Spryker\Zed\Oms\Dependency\Plugin\Command\CommandByOrderInterface;

/**
 * @method \SprykerEco\Zed\Vertex\Business\VertexFacadeInterface getFacade()
 * @method \SprykerEco\Zed\Vertex\Communication\VertexCommunicationFactory getFactory()
 * @method \SprykerEco\Zed\Vertex\VertexConfig getConfig()
 */
class VertexSubmitPaymentTaxInvoicePlugin extends AbstractPlugin implements CommandByOrderInterface
{
    /**
     * {@inheritDoc}
     * - Sends SubmitPaymentTaxInvoice message to the message broker `payment-tax-invoice-commands' channel.
     *
     * @api
     *
     * @param array<\Orm\Zed\Sales\Persistence\SpySalesOrderItem> $orderItems
     * @param \Orm\Zed\Sales\Persistence\SpySalesOrder $orderEntity
     * @param \Spryker\Zed\Oms\Business\Util\ReadOnlyArrayObject $data
     *
     * @return array
     */
    public function run(array $orderItems, SpySalesOrder $orderEntity, ReadOnlyArrayObject $data): array
    {
        $orderTransfer = new OrderTransfer();
        $orderTransfer->setIdSalesOrder($orderEntity->getIdSalesOrder());
        $orderTransfer->setStore($orderEntity->getStore());

        $this->getFacade()->handleSubmitPaymentTaxInvoice($orderTransfer);

        return [];
    }
}
