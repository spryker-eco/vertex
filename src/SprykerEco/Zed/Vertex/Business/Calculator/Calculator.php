<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Zed\Vertex\Business\Calculator;

use Generated\Shared\Transfer\CalculableObjectTransfer;
use Spryker\Shared\Log\LoggerTrait;
use Spryker\Zed\Store\Business\StoreFacadeInterface;
use SprykerEco\Zed\Vertex\Business\Mapper\Prices\ItemExpensePriceRetriever;
use SprykerEco\Zed\Vertex\Business\Resolver\VertexConfigResolverInterface;

class Calculator implements CalculatorInterface
{
    use LoggerTrait;

    public function __construct(
        protected StoreFacadeInterface $storeFacade,
        protected VertexConfigResolverInterface $vertexConfigResolver,
        protected FallbackCalculatorInterface $fallbackQuoteCalculator,
        protected FallbackCalculatorInterface $fallbackOrderCalculator,
        protected VertexCalculatorInterface $vertexCalculator,
    ) {
    }

    public function recalculate(CalculableObjectTransfer $calculableObjectTransfer): void
    {
        $vertexConfigTransfer = $this->vertexConfigResolver->resolve();

        if (!$vertexConfigTransfer || !$vertexConfigTransfer->getIsActive()) {
            $this->setHideTaxInCartFlagToFalse($calculableObjectTransfer);

            $this->recalculateUsingFallbackCalculator($calculableObjectTransfer);

            return;
        }

        if ($calculableObjectTransfer->getOriginalQuote()) {
            $calculableObjectTransfer->getOriginalQuoteOrFail()->setTaxVendor($vertexConfigTransfer->getVendorCode());
        }
        $this->setHideTaxInCartFlagToTrue($calculableObjectTransfer);

        $this->vertexCalculator->recalculate($calculableObjectTransfer, $vertexConfigTransfer);
    }

    protected function recalculateUsingFallbackCalculator(CalculableObjectTransfer $calculableObjectTransfer): void
    {
        if ($calculableObjectTransfer->getOriginalQuote()) {
            $this->fallbackQuoteCalculator->recalculate($calculableObjectTransfer);

            return;
        }

        if (!$calculableObjectTransfer->getOriginalOrder()) {
            return;
        }

        $this->fallbackOrderCalculator->recalculate($calculableObjectTransfer);
    }

    protected function setHideTaxInCartFlagToTrue(CalculableObjectTransfer $calculableObjectTransfer): CalculableObjectTransfer
    {
        if ($calculableObjectTransfer->getOriginalQuote() !== null && $calculableObjectTransfer->getPriceMode() === ItemExpensePriceRetriever::PRICE_MODE_NET) {
            $calculableObjectTransfer->getOriginalQuote()->setHideTaxInCart(true);
        }

        return $calculableObjectTransfer;
    }

    protected function setHideTaxInCartFlagToFalse(CalculableObjectTransfer $calculableObjectTransfer): void
    {
        if ($calculableObjectTransfer->getOriginalQuote() === null) {
            return;
        }

        $calculableObjectTransfer->getOriginalQuote()->setHideTaxInCart(false);
    }
}
