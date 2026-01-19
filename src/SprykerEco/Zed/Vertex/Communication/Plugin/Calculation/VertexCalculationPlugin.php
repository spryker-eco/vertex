<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Vertex\Communication\Plugin\Calculation;

use Generated\Shared\Transfer\CalculableObjectTransfer;
use Spryker\Zed\CalculationExtension\Dependency\Plugin\CalculationPluginInterface;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;

/**
 * @method \Spryker\Zed\Vertex\Business\VertexFacadeInterface getFacade()
 * @method \Spryker\Zed\Vertex\VertexConfig getConfig()
 * @method \Spryker\Zed\Vertex\Communication\VertexCommunicationFactory getFactory()
 */
class VertexCalculationPlugin extends AbstractPlugin implements CalculationPluginInterface
{
    /**
     * {@inheritDoc}
     * - If tax app is not configured or disabled runs fallback calculation plugins defined in {@link \Spryker\Zed\Vertex\VertexDependencyProvider::getFallbackQuoteCalculationPlugins} and {@link \Spryker\Zed\Vertex\VertexDependencyProvider::getFallbackOrderCalculationPlugins} depending on the type of calculation required.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\CalculableObjectTransfer $calculableObjectTransfer
     *
     * @return void
     */
    public function recalculate(CalculableObjectTransfer $calculableObjectTransfer): void
    {
        $this->getFacade()->recalculate($calculableObjectTransfer);
    }
}
