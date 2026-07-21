<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Zed\Vertex\Communication\Plugin\Configuration;

use Generated\Shared\Transfer\ConfigurationValueCollectionRequestTransfer;
use Spryker\Zed\ConfigurationExtension\Dependency\Plugin\ConfigurationValuePreSavePluginInterface;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;

/**
 * @method \SprykerEco\Zed\Vertex\Business\VertexFacadeInterface getFacade()
 * @method \SprykerEco\Zed\Vertex\VertexConfig getConfig()
 * @method \SprykerEco\Zed\Vertex\Communication\VertexCommunicationFactory getFactory()
 */
class VertexTaxProviderPreSavePlugin extends AbstractPlugin implements ConfigurationValuePreSavePluginInterface
{
    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function preSave(
        ConfigurationValueCollectionRequestTransfer $requestTransfer,
    ): ConfigurationValueCollectionRequestTransfer {
        return $this->getFacade()->validateTaxProviderConfigurationPreSave($requestTransfer);
    }
}
