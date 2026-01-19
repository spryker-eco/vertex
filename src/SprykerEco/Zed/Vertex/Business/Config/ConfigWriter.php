<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Vertex\Business\Config;

use Exception;
use Generated\Shared\Transfer\VertexConfigTransfer;
use SprykerEco\Zed\Vertex\Business\Exception\VertexConfigurationCouldNotBeSaved;
use SprykerEco\Zed\Vertex\Dependency\Facade\VertexToStoreFacadeInterface;
use SprykerEco\Zed\Vertex\Persistence\VertexEntityManagerInterface;

class ConfigWriter implements ConfigWriterInterface
{
    /**
     * @var string
     */
    protected const LOG_MESSAGE_CONFIG_SAVING_FAILED = 'Tax app config saving failed due to exception. Exception message: %s';

    /**
     * @var \Spryker\Zed\Vertex\Persistence\VertexEntityManagerInterface
     */
    protected VertexEntityManagerInterface $VertexEntityManager;

    /**
     * @var \Spryker\Zed\Vertex\Dependency\Facade\VertexToStoreFacadeInterface
     */
    protected VertexToStoreFacadeInterface $storeFacade;

    /**
     * @param \Spryker\Zed\Vertex\Persistence\VertexEntityManagerInterface $VertexEntityManager
     * @param \Spryker\Zed\Vertex\Dependency\Facade\VertexToStoreFacadeInterface $storeFacade
     */
    public function __construct(
        VertexEntityManagerInterface $VertexEntityManager,
        VertexToStoreFacadeInterface $storeFacade
    ) {
        $this->VertexEntityManager = $VertexEntityManager;
        $this->storeFacade = $storeFacade;
    }

    /**
     * @param \Generated\Shared\Transfer\VertexConfigTransfer $VertexConfigTransfer
     *
     * @throws \Spryker\Zed\Vertex\Business\Exception\VertexConfigurationCouldNotBeSaved
     *
     * @return void
     */
    public function write(VertexConfigTransfer $VertexConfigTransfer): void
    {
        $storeTransfers = $this->storeFacade->getAllStores();

        try {
            $this->VertexEntityManager->saveVertexConfig($VertexConfigTransfer, $storeTransfers);
        } catch (Exception $e) {
            throw new VertexConfigurationCouldNotBeSaved(sprintf(static::LOG_MESSAGE_CONFIG_SAVING_FAILED, $e->getMessage()), 0, $e);
        }
    }
}
