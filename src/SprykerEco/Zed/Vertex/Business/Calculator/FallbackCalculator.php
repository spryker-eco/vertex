<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Vertex\Business\Calculator;

use Generated\Shared\Transfer\CalculableObjectTransfer;

class FallbackCalculator implements FallbackCalculatorInterface
{
    /**
     * @param array<\Spryker\Zed\CalculationExtension\Dependency\Plugin\CalculationPluginInterface> $fallbackCalculationPlugins
     */
    public function __construct(protected array $fallbackCalculationPlugins) {}

    /**
     * @param \Generated\Shared\Transfer\CalculableObjectTransfer $calculableObjectTransfer
     *
     * @return void
     */
    public function recalculate(CalculableObjectTransfer $calculableObjectTransfer): void
    {
        foreach ($this->fallbackCalculationPlugins as $fallbackCalculationPlugin) {
            $fallbackCalculationPlugin->recalculate($calculableObjectTransfer);
        }
    }
}
