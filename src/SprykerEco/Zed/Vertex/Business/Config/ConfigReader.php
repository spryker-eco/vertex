<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Vertex\Business\Config;

use Generated\Shared\Transfer\SortTransfer;
use Generated\Shared\Transfer\VertexConfigConditionsTransfer;
use Generated\Shared\Transfer\VertexConfigCriteriaTransfer;
use Generated\Shared\Transfer\VertexConfigTransfer;
use SprykerEco\Zed\Vertex\Dependency\Facade\VertexToStoreFacadeInterface;
use SprykerEco\Zed\Vertex\Persistence\VertexRepositoryInterface;

class ConfigReader implements ConfigReaderInterface
{
    /**
     * @param \Spryker\Zed\Vertex\Persistence\VertexRepositoryInterface $VertexRepository
     * @param \Spryker\Zed\Vertex\Dependency\Facade\VertexToStoreFacadeInterface $storeFacade
     */
    public function __construct(
        protected VertexRepositoryInterface $VertexRepository,
        protected VertexToStoreFacadeInterface $storeFacade
    ) {
    }

    /**
     * @param int $idStore
     *
     * @return \Generated\Shared\Transfer\VertexConfigTransfer|null
     */
    public function getVertexConfigByIdStore(int $idStore): ?VertexConfigTransfer
    {
        $VertexConfigConditionsTransfer = new VertexConfigConditionsTransfer();
        $VertexConfigConditionsTransfer->addFkStore($idStore);

        $VertexConfigCriteriaTransfer = (new VertexConfigCriteriaTransfer())
            ->setVertexConfigConditions($VertexConfigConditionsTransfer)
            ->addSort(
                (new SortTransfer())
                    ->setField(VertexConfigTransfer::IS_ACTIVE)
                    ->setIsAscending(false),
            );

        $VertexConfigCollectionTransfer = $this->VertexRepository->getVertexConfigCollection($VertexConfigCriteriaTransfer);

        if (!$VertexConfigCollectionTransfer->getVertexConfigs()->count()) {
            return null;
        }

        return $VertexConfigCollectionTransfer->getVertexConfigs()->offsetGet(0);
    }

    /**
     * @return \Generated\Shared\Transfer\VertexConfigTransfer|null
     */
    public function findVertexConfigForCurrentStore(): ?VertexConfigTransfer
    {
        $VertexConfigConditionsTransfer = new VertexConfigConditionsTransfer();
        $VertexConfigConditionsTransfer->addFkStore((int)$this->storeFacade->getCurrentStore()->getIdStore());

        $VertexConfigCriteriaTransfer = (new VertexConfigCriteriaTransfer())
            ->setVertexConfigConditions($VertexConfigConditionsTransfer)
            ->addSort(
                (new SortTransfer())
                    ->setField(VertexConfigTransfer::IS_ACTIVE)
                    ->setIsAscending(false),
            );

        $VertexConfigCollectionTransfer = $this->VertexRepository->getVertexConfigCollection($VertexConfigCriteriaTransfer);

        if (!$VertexConfigCollectionTransfer->getVertexConfigs()->count()) {
            return null;
        }

        return $VertexConfigCollectionTransfer->getVertexConfigs()->offsetGet(0);
    }
}
