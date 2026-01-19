<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Vertex\Business\Config;

use Exception;
use Generated\Shared\Transfer\VertexConfigCriteriaTransfer;
use Spryker\Shared\Kernel\Transfer\Exception\NullValueException;
use Spryker\Shared\Log\LoggerTrait;
use SprykerEco\Zed\Vertex\Business\Exception\VertexConfigurationCouldNotBeDeleted;
use SprykerEco\Zed\Vertex\Dependency\Facade\VertexToStoreFacadeInterface;
use SprykerEco\Zed\Vertex\Persistence\VertexEntityManagerInterface;

class ConfigDeleter implements ConfigDeleterInterface
{
    use LoggerTrait;

    /**
     * @var string
     */
    protected const LOG_MESSAGE_CONFIG_DELETION_FAILED = 'Tax app config deletion failed due to exception';

    /**
     * @var \Spryker\Zed\Vertex\Persistence\VertexEntityManagerInterface
     */
    protected $VertexEntityManager;

    /**
     * @var \Spryker\Zed\Vertex\Dependency\Facade\VertexToStoreFacadeInterface
     */
    protected $storeFacade;

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
     * @param \Generated\Shared\Transfer\VertexConfigCriteriaTransfer $VertexConfigCriteriaTransfer
     *
     * @throws \Spryker\Zed\Vertex\Business\Exception\VertexConfigurationCouldNotBeDeleted
     *
     * @return void
     */
    public function delete(VertexConfigCriteriaTransfer $VertexConfigCriteriaTransfer): void
    {
        try {
            $this->VertexEntityManager->deleteVertexConfig($VertexConfigCriteriaTransfer);
        } catch (NullValueException $e) {
            $this->logException($e);

            throw new VertexConfigurationCouldNotBeDeleted($e->getMessage());
        }
    }

    /**
     * @param \Exception $e
     *
     * @return void
     */
    protected function logException(Exception $e): void
    {
        $this->getLogger()->error(sprintf(static::LOG_MESSAGE_CONFIG_DELETION_FAILED, $e->getMessage()), ['exception' => $e]);
    }
}
