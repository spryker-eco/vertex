<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Vertex\Business\Calculator;

use Generated\Shared\Transfer\CalculableObjectTransfer;
use Generated\Shared\Transfer\VertexConfigTransfer;
use Spryker\Shared\Log\LoggerTrait;
use SprykerEco\Zed\Vertex\Business\AccessTokenProvider\AccessTokenProviderInterface;
use SprykerEco\Zed\Vertex\Business\Aggregator\PriceAggregatorInterface;
use SprykerEco\Zed\Vertex\Business\Config\ConfigReaderInterface;
use SprykerEco\Zed\Vertex\Business\Mapper\Prices\ItemExpensePriceRetriever;
use SprykerEco\Zed\Vertex\Dependency\Facade\VertexToStoreFacadeInterface;

class Calculator implements CalculatorInterface
{
    use LoggerTrait;

    /**
     * @var \Spryker\Zed\Vertex\Dependency\Facade\VertexToStoreFacadeInterface
     */
    protected VertexToStoreFacadeInterface $storeFacade;

    /**
     * @var \Spryker\Zed\Vertex\Business\Config\ConfigReaderInterface
     */
    protected ConfigReaderInterface $configReader;

    /**
     * @var \Spryker\Zed\Vertex\Business\AccessTokenProvider\AccessTokenProviderInterface
     */
    protected AccessTokenProviderInterface $accessTokenProvider;

    /**
     * @var array<int, \Spryker\Zed\VertexExtension\Dependency\Plugin\CalculableObjectVertexExpanderPluginInterface>
     */
    protected array $calculableObjectVertexExpanderPlugins;

    /**
     * @var \Spryker\Zed\Vertex\Business\Aggregator\PriceAggregatorInterface
     */
    protected PriceAggregatorInterface $priceAggregator;

    /**
     * @var \Spryker\Zed\Vertex\Business\Calculator\FallbackCalculatorInterface
     */
    protected FallbackCalculatorInterface $fallbackQuoteCalculator;

    /**
     * @var \Spryker\Zed\Vertex\Business\Calculator\FallbackCalculatorInterface
     */
    protected FallbackCalculatorInterface $fallbackOrderCalculator;

    /**
     * @var \Spryker\Zed\Vertex\Business\Calculator\VertexCalculatorInterface
     */
    protected VertexCalculatorInterface $vertexCalculator;

    /**
     * @param \Spryker\Zed\Vertex\Dependency\Facade\VertexToStoreFacadeInterface $storeFacade
     * @param \Spryker\Zed\Vertex\Business\Config\ConfigReaderInterface $configReader
     * @param \Spryker\Zed\Vertex\Business\Calculator\FallbackCalculatorInterface $fallbackQuoteCalculator
     * @param \Spryker\Zed\Vertex\Business\Calculator\FallbackCalculatorInterface $fallbackOrderCalculator
     * @param \Spryker\Zed\Vertex\Business\Calculator\VertexCalculatorInterface $vertexCalculator
     */
    public function __construct(
        VertexToStoreFacadeInterface $storeFacade,
        ConfigReaderInterface $configReader,
        FallbackCalculatorInterface $fallbackQuoteCalculator,
        FallbackCalculatorInterface $fallbackOrderCalculator,
        VertexCalculatorInterface $vertexCalculator
    ) {
        $this->storeFacade = $storeFacade;
        $this->configReader = $configReader;
        $this->fallbackQuoteCalculator = $fallbackQuoteCalculator;
        $this->fallbackOrderCalculator = $fallbackOrderCalculator;
        $this->vertexCalculator = $vertexCalculator;
    }

    /**
     * @param \Generated\Shared\Transfer\CalculableObjectTransfer $calculableObjectTransfer
     *
     * @return void
     */
    public function recalculate(CalculableObjectTransfer $calculableObjectTransfer): void
    {
        $VertexConfigTransfer = $this->getVertexConfigTransfer($calculableObjectTransfer);

        if ($VertexConfigTransfer === null || !$VertexConfigTransfer->getIsActive()) {
            $this->setHideTaxInCartFlagToFalse($calculableObjectTransfer);

            $this->recalculateUsingFallbackCalculator($calculableObjectTransfer);

            return;
        }

        if ($calculableObjectTransfer->getOriginalQuote()) {
            $calculableObjectTransfer->getOriginalQuoteOrFail()->setTaxVendor($VertexConfigTransfer->getVendorCode());
        }
        $this->setHideTaxInCartFlagToTrue($calculableObjectTransfer);

        $this->vertexCalculator->recalculate($calculableObjectTransfer, $VertexConfigTransfer);
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

        return $this->configReader->getVertexConfigByIdStore($idStore);
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
