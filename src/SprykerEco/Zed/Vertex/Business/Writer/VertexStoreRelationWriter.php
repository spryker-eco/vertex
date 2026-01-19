<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Vertex\Business\Writer;

use Generated\Shared\Transfer\VertexConfigCriteriaTransfer;
use SprykerEco\Zed\Vertex\Business\Config\ConfigWriterInterface;
use SprykerEco\Zed\Vertex\Persistence\VertexRepositoryInterface;

class VertexStoreRelationWriter implements VertexStoreRelationWriterInterface
{
    /**
     * @param \Spryker\Zed\Vertex\Persistence\VertexRepositoryInterface $VertexRepository
     * @param \Spryker\Zed\Vertex\Business\Config\ConfigWriterInterface $configWriter
     */
    public function __construct(
        protected VertexRepositoryInterface $VertexRepository,
        protected ConfigWriterInterface $configWriter
    ) {
    }

    /**
     * @return void
     */
    public function refreshVertexStoreRelations(): void
    {
        $VertexConfigCollectionTransfer = $this->VertexRepository
            ->getVertexConfigCollection(new VertexConfigCriteriaTransfer());

        foreach ($VertexConfigCollectionTransfer->getVertexConfigs() as $VertexConfigTransfer) {
            $this->configWriter->write($VertexConfigTransfer);
        }
    }
}
