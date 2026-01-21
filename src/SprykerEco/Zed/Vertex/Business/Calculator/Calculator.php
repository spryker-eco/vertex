<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Vertex\Business\Calculator;

use Generated\Shared\Transfer\CalculableObjectTransfer;
use Generated\Shared\Transfer\VertexConfigTransfer;
use Spryker\Shared\Log\LoggerTrait;
use SprykerEco\Zed\Vertex\Business\Mapper\Prices\ItemExpensePriceRetriever;
use SprykerEco\Zed\Vertex\Business\Resolver\VertexConfigResolverInterface;
use SprykerEco\Zed\Vertex\Dependency\Facade\VertexToStoreFacadeInterface;

class Calculator implements CalculatorInterface
{
    use LoggerTrait;

    public function __construct(
        protected VertexToStoreFacadeInterface $storeFacade,
        protected VertexConfigResolverInterface $vertexConfigResolver,
        protected FallbackCalculatorInterface $fallbackQuoteCalculator,
        protected FallbackCalculatorInterface $fallbackOrderCalculator,
        protected VertexCalculatorInterface $vertexCalculator
    ) {}

    /**
     * @param \Generated\Shared\Transfer\CalculableObjectTransfer $calculableObjectTransfer
     *
     * @return void
     */
    public function recalculate(CalculableObjectTransfer $calculableObjectTransfer): void
    {
        $vertexConfigTransfer = $this->getVertexConfigTransfer($calculableObjectTransfer);

        if ($vertexConfigTransfer === null || !$vertexConfigTransfer->getIsActive()) {
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

    /**
     * @param \Generated\Shared\Transfer\CalculableObjectTransfer $calculableObjectTransfer
     *
     * @return \Generated\Shared\Transfer\VertexConfigTransfer|null
     */
    protected function getVertexConfigTransfer(CalculableObjectTransfer $calculableObjectTransfer): ?VertexConfigTransfer
    {
        $storeTransfer = $calculableObjectTransfer->getStoreOrFail();
        $idStore = $storeTransfer->getIdStore();

        if (!$idStore) {
            $idStore = $this->storeFacade->getStoreByName($storeTransfer->getNameOrFail())->getIdStoreOrFail();
        }

        return $this->vertexConfigResolver->resolve($idStore);
    }

    /**
     * @param \Generated\Shared\Transfer\CalculableObjectTransfer $calculableObjectTransfer
     *
     * @return void
     */
    protected function recalculateUsingFallbackCalculator(CalculableObjectTransfer $calculableObjectTransfer): void
    {
        if ($calculableObjectTransfer->getOriginalQuote()) {
            $this->fallbackQuoteCalculator->recalculate($calculableObjectTransfer);

            return;
        }

        if ($calculableObjectTransfer->getOriginalOrder()) {
            $this->fallbackOrderCalculator->recalculate($calculableObjectTransfer);
        }
    }

    /**
     * @param \Generated\Shared\Transfer\CalculableObjectTransfer $calculableObjectTransfer
     *
     * @return \Generated\Shared\Transfer\CalculableObjectTransfer
     */
    protected function setHideTaxInCartFlagToTrue(CalculableObjectTransfer $calculableObjectTransfer): CalculableObjectTransfer
    {
        if ($calculableObjectTransfer->getOriginalQuote() !== null && $calculableObjectTransfer->getPriceMode() === ItemExpensePriceRetriever::PRICE_MODE_NET) {
            $calculableObjectTransfer->getOriginalQuote()->setHideTaxInCart(true);
        }

        return $calculableObjectTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\CalculableObjectTransfer $calculableObjectTransfer
     *
     * @return void
     */
    protected function setHideTaxInCartFlagToFalse(CalculableObjectTransfer $calculableObjectTransfer): void
    {
        if ($calculableObjectTransfer->getOriginalQuote() !== null) {
            $calculableObjectTransfer->getOriginalQuote()->setHideTaxInCart(false);
        }
    }
}
