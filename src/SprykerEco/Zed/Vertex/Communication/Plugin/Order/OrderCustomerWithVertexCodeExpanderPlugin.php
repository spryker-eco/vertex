<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Vertex\Communication\Plugin\Order;

use Generated\Shared\Transfer\OrderTransfer;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use SprykerEco\Zed\Vertex\Dependency\Plugin\OrderVertexExpanderPluginInterface;

/**
 * @method \SprykerEco\Zed\Vertex\Communication\VertexCommunicationFactory getFactory()
 * @method \SprykerEco\Zed\Vertex\VertexConfig getConfig()
 * @method \SprykerEco\Zed\Vertex\Business\VertexFacadeInterface getFacade()
 */
class OrderCustomerWithVertexCodeExpanderPlugin extends AbstractPlugin implements OrderVertexExpanderPluginInterface // TODO: plugin definition and implementation are in the same module
{
    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return \Generated\Shared\Transfer\OrderTransfer
     */
    public function expand(OrderTransfer $orderTransfer): OrderTransfer
    {
        /** @var \Generated\Shared\Transfer\OrderTransfer $orderTransfer */
        $orderTransfer = $this->getFactory()->createCustomerWithVertexSpecificFieldsMapper()->expand($orderTransfer);

        return $orderTransfer;
    }
}
