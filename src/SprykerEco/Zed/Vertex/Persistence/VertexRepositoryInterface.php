<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Vertex\Persistence;

use Generated\Shared\Transfer\VertexApiAccessTokenCriteriaTransfer;
use Generated\Shared\Transfer\VertexApiAccessTokenTransfer;

interface VertexRepositoryInterface
{
    public function findAccessToken(VertexApiAccessTokenCriteriaTransfer $vertexApiAccessTokenCriteriaTransfer): VertexApiAccessTokenTransfer;
}
