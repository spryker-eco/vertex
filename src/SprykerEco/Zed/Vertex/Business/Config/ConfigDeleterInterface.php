<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Vertex\Business\Config;

use Generated\Shared\Transfer\VertexConfigCriteriaTransfer;

interface ConfigDeleterInterface
{
    /**
     * @param \Generated\Shared\Transfer\VertexConfigCriteriaTransfer $VertexConfigCriteriaTransfer
     *
     * @return void
     */
    public function delete(VertexConfigCriteriaTransfer $VertexConfigCriteriaTransfer): void;
}
