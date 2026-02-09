<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Vertex\Business\Calculator;

use Generated\Shared\Transfer\CalculableObjectTransfer;
use Generated\Shared\Transfer\VertexConfigTransfer;

interface VertexCalculatorInterface
{
    public function recalculate(CalculableObjectTransfer $calculableObjectTransfer, VertexConfigTransfer $vertexConfigTransfer): void;
}
