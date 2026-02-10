<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

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
     * - OMS command plugin that triggers tax calculation for invoicing when order state changes.
     * - Creates an OrderTransfer from the provided order entity with order ID and store name.
     * - Delegates to {@link \SprykerEco\Zed\Vertex\Business\VertexFacadeInterface::handleSubmitPaymentTaxInvoice()} to calculate tax for invoicing.
     * - The facade method makes a synchronous API call to Vertex API to calculate tax for the order.
     * - Returns an empty array as required by the OMS command interface.
     *
     * @api
     *
     * @param array<\Orm\Zed\Sales\Persistence\SpySalesOrderItem> $orderItems
     * @param \Orm\Zed\Sales\Persistence\SpySalesOrder $orderEntity
     * @param \Spryker\Zed\Oms\Business\Util\ReadOnlyArrayObject $data
     *
     * @return array
     */
    public function run(array $orderItems, SpySalesOrder $orderEntity, ReadOnlyArrayObject $data): array // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter
    {
        $orderTransfer = new OrderTransfer();
        $orderTransfer->setIdSalesOrder($orderEntity->getIdSalesOrder());
        $orderTransfer->setStore($orderEntity->getStore());

        $this->getFacade()->handleSubmitPaymentTaxInvoice($orderTransfer);

        return [];
    }
}
