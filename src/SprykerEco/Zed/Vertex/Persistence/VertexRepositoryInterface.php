<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Vertex\Persistence;

use Generated\Shared\Transfer\VertexConfigCollectionTransfer;
use Generated\Shared\Transfer\VertexConfigCriteriaTransfer;

interface VertexRepositoryInterface
{
    /**
     * @param \Generated\Shared\Transfer\VertexConfigCriteriaTransfer $VertexConfigCriteriaTransfer
     *
     * @return \Generated\Shared\Transfer\VertexConfigCollectionTransfer
     */
    public function getVertexConfigCollection(VertexConfigCriteriaTransfer $VertexConfigCriteriaTransfer): VertexConfigCollectionTransfer;
}
