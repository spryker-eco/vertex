<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Vertex\Communication\Plugin\Quote;

use Generated\Shared\Transfer\CalculableObjectTransfer;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use SprykerEco\Zed\Vertex\Dependency\Plugin\CalculableObjectVertexExpanderPluginInterface;

/**
 * @method \SprykerEco\Zed\Vertex\Communication\VertexCommunicationFactory getFactory()
 * @method \SprykerEco\Zed\Vertex\VertexConfig getConfig()
 * @method \SprykerEco\Zed\Vertex\Business\VertexFacadeInterface getFacade()
 */
class CalculableObjectItemWithVertexSpecificFieldsExpanderPlugin extends AbstractPlugin implements CalculableObjectVertexExpanderPluginInterface
{
    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\CalculableObjectTransfer $quoteTransfer
     *
     * @return \Generated\Shared\Transfer\CalculableObjectTransfer
     */
    public function expand(CalculableObjectTransfer $quoteTransfer): CalculableObjectTransfer
    {
        /** @var \Generated\Shared\Transfer\CalculableObjectTransfer $quoteTransfer */
        $quoteTransfer = $this->getFactory()->createItemWithVertexTaxCodeExpander()->expand($quoteTransfer);

        return $quoteTransfer;
    }
}
