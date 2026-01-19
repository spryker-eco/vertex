<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Vertex\Business\Config;

use Generated\Shared\Transfer\VertexConfigTransfer;

interface ConfigReaderInterface
{
    /**
     * @param int $idStore
     *
     * @return \Generated\Shared\Transfer\VertexConfigTransfer|null
     */
    public function getVertexConfigByIdStore(int $idStore): ?VertexConfigTransfer;

    /**
     * @return \Generated\Shared\Transfer\VertexConfigTransfer|null
     */
    public function findVertexConfigForCurrentStore(): ?VertexConfigTransfer;
}
