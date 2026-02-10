<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Zed\Vertex\Communication\Plugin\Oms;

use Generated\Shared\Transfer\OmsEventTriggeredTransfer;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use Spryker\Zed\OmsExtension\Dependency\Plugin\OmsEventTriggeredListenerPluginInterface;

/**
 * @method \SprykerEco\Zed\Vertex\Business\VertexFacadeInterface getFacade()
 * @method \SprykerEco\Zed\Vertex\Communication\VertexCommunicationFactory getFactory()
 * @method \SprykerEco\Zed\Vertex\VertexConfig getConfig()
 */
class VertexOrderRefundedEventListenerPlugin extends AbstractPlugin implements OmsEventTriggeredListenerPluginInterface
{
    /**
     * {@inheritDoc}
     * Specification:
     * - Checks if this plugin is applicable for the given OMS event.
     * - Returns true if the event ID is 'refund', false otherwise.
     * - Used to determine if the plugin should handle the triggered event.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\OmsEventTriggeredTransfer $omsEventTriggeredTransfer
     *
     * @return bool
     */
    public function isApplicable(OmsEventTriggeredTransfer $omsEventTriggeredTransfer): bool
    {
        return $omsEventTriggeredTransfer->getIdEvent() === 'refund';
    }

    /**
     * {@inheritDoc}
     * - Delegates to {@link \SprykerEco\Zed\Vertex\Business\VertexFacadeInterface::processOrderRefund()} to process the refund.
     * - Requires `OmsEventTriggeredTransfer.orderItemIds` to be set.
     * - Requires `OmsEventTriggeredTransfer.idSalesOrder` to be set.
     * - The facade method makes a synchronous API call to Vertex API to calculate tax for the refunded order items.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\OmsEventTriggeredTransfer $omsEventTriggeredTransfer
     *
     * @return void
     */
    public function onEventTriggered(OmsEventTriggeredTransfer $omsEventTriggeredTransfer): void
    {
        $this->getFacade()->processOrderRefund($omsEventTriggeredTransfer->getOrderItemIds(), $omsEventTriggeredTransfer->getIdSalesOrderOrFail());
    }
}
