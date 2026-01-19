<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Vertex\Business\Writer;

interface VertexStoreRelationWriterInterface
{
    /**
     * @return void
     */
    public function refreshVertexStoreRelations(): void;
}
