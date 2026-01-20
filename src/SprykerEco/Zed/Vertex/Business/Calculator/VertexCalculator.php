<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Vertex\Business\Calculator;

use Generated\Shared\Transfer\ApiErrorMessageTransfer;
use Generated\Shared\Transfer\CalculableObjectTransfer;
use Generated\Shared\Transfer\StoreTransfer;
use Generated\Shared\Transfer\VertexConfigTransfer;
use Generated\Shared\Transfer\VertexSaleTransfer;
use Generated\Shared\Transfer\TaxCalculationRequestTransfer;
use Generated\Shared\Transfer\TaxCalculationResponseTransfer;
use Generated\Shared\Transfer\TaxTotalTransfer;
use SprykerEco\Client\Vertex\VertexClientInterface;
use Spryker\Shared\Log\LoggerTrait;
use SprykerEco\Zed\Vertex\Business\AccessTokenProvider\AccessTokenProviderInterface;
use SprykerEco\Zed\Vertex\Business\Aggregator\PriceAggregatorInterface;
use SprykerEco\Zed\Vertex\Business\Mapper\VertexMapperInterface;

class VertexCalculator implements VertexCalculatorInterface
{
    use LoggerTrait;

    /**
     * @uses \Spryker\Shared\Price\PriceConfig::PRICE_MODE_NET
     *
     * @var string
     */
    protected const PRICE_MODE_NET = 'NET_MODE';

    /**
     * @param \Spryker\Zed\Vertex\Business\Mapper\VertexMapperInterface $vertexMapper
     * @param \SprykerEco\Client\Vertex\VertexClientInterface $vertexClient
     * @param \Spryker\Zed\Vertex\Business\AccessTokenProvider\AccessTokenProviderInterface $accessTokenProvider
     * @param array<\SprykerEco\Zed\VertexExtension\Dependency\Plugin\CalculableObjectVertexExpanderPluginInterface> $calculableObjectVertexExpanderPlugins
     * @param \Spryker\Zed\Vertex\Business\Aggregator\PriceAggregatorInterface $priceAggregator
     */
    public function __construct(
        protected VertexMapperInterface $vertexMapper,
        protected VertexClientInterface $vertexClient,
        protected AccessTokenProviderInterface $accessTokenProvider,
        protected array $calculableObjectVertexExpanderPlugins,
        protected PriceAggregatorInterface $priceAggregator
    ) {}
    public function recalculate(CalculableObjectTransfer $calculableObjectTransfer, VertexConfigTransfer $VertexConfigTransfer): void
    {
        $calculableObjectTransfer = $this->executeCalculableObjectVertexExpanderPlugins($calculableObjectTransfer);

        $VertexSaleTransfer = $this->vertexMapper->mapCalculableObjectToVertexSaleTransfer($calculableObjectTransfer, new VertexSaleTransfer());

        // for correct tax calculation in NET price mode, at least one shipment must be selected, otherwise tax calculation is skipped.
        if ($calculableObjectTransfer->getPriceModeOrFail() === static::PRICE_MODE_NET && $VertexSaleTransfer->getShipments()->count() === 0) {
            $taxTotalTransfer = (new TaxTotalTransfer())->setAmount(0);
            $calculableObjectTransfer->getTotalsOrFail()->setTaxTotal($taxTotalTransfer);
            $this->priceAggregator->calculatePriceAggregation($VertexSaleTransfer, $calculableObjectTransfer);

            return;
        }

        $taxCalculationResponseTransfer = $this->getCachedVertexResponseTransfer($calculableObjectTransfer, $VertexSaleTransfer);

        if (!$taxCalculationResponseTransfer) {
            $taxCalculationResponseTransfer = $this->getTaxCalculationResponse(
                $VertexSaleTransfer,
                $VertexConfigTransfer,
                $calculableObjectTransfer->getStoreOrFail()
            );

            if (!$taxCalculationResponseTransfer->getIsSuccessful()) {
                $apiErrorMessages = array_map(function (ApiErrorMessageTransfer $apiErrorMessageTransfer) {
                    return $apiErrorMessageTransfer->toArray();
                }, $taxCalculationResponseTransfer->getApiErrorMessages()->getArrayCopy());
                $this->getLogger()->error('Tax calculation failed.', ['apiErrorMessages' => $apiErrorMessages]);

                $VertexSaleTransfer = $this->resetVertexSaleTaxTotals($VertexSaleTransfer);
                $taxCalculationResponseTransfer->setSale($VertexSaleTransfer);
            }
        }

        $calculableObjectTransfer = $this->priceAggregator->calculatePriceAggregation($taxCalculationResponseTransfer->getSaleOrFail(), $calculableObjectTransfer);

        if ($taxCalculationResponseTransfer->getIsSuccessful()) {
            $calculableObjectTransfer->setVertexSaleHash($this->getVertexSaleHash($VertexSaleTransfer));
            $calculableObjectTransfer->setTaxCalculationResponse($taxCalculationResponseTransfer);
        }
    }

    /**
     * @param \Generated\Shared\Transfer\VertexSaleTransfer $VertexSaleTransfer
     * @param \Generated\Shared\Transfer\VertexConfigTransfer $VertexConfigTransfer
     * @param \Generated\Shared\Transfer\StoreTransfer $storeTransfer
     *
     * @return \Generated\Shared\Transfer\TaxCalculationResponseTransfer
     */
    protected function getTaxCalculationResponse(
        VertexSaleTransfer $VertexSaleTransfer,
        VertexConfigTransfer $VertexConfigTransfer,
        StoreTransfer $storeTransfer
    ): TaxCalculationResponseTransfer {
        $taxCalculationRequestTransfer = (new TaxCalculationRequestTransfer())
            ->setSale($VertexSaleTransfer)
            ->setAuthorization($this->accessTokenProvider->getAccessToken());

        return $this->vertexClient->calculateTax($taxCalculationRequestTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\VertexSaleTransfer $VertexSaleTransfer
     *
     * @return string
     */
    protected function getVertexSaleHash(VertexSaleTransfer $VertexSaleTransfer): string
    {
        return md5(json_encode($VertexSaleTransfer->toArray()) ?: '');
    }

    /**
     * @param \Generated\Shared\Transfer\CalculableObjectTransfer $calculableObjectTransfer
     * @param \Generated\Shared\Transfer\VertexSaleTransfer $VertexSaleTransfer
     *
     * @return \Generated\Shared\Transfer\TaxCalculationResponseTransfer|null
     */
    protected function getCachedVertexResponseTransfer(
        CalculableObjectTransfer $calculableObjectTransfer,
        VertexSaleTransfer $VertexSaleTransfer
    ): ?TaxCalculationResponseTransfer {
        $currentTaxRequestHash = null;

        if ($calculableObjectTransfer->getVertexSaleHash()) {
            $currentTaxRequestHash = $this->getVertexSaleHash($VertexSaleTransfer);
        }

        // Quote was not changed since last tax calculation request. Tax calculation is skipped.
        if ($currentTaxRequestHash === $calculableObjectTransfer->getVertexSaleHash() && $calculableObjectTransfer->getTaxCalculationResponse()) {
            return $calculableObjectTransfer->getTaxCalculationResponse();
        }

        return null;
    }

    /**
     * @param \Generated\Shared\Transfer\VertexSaleTransfer $VertexSaleTransfer
     *
     * @return \Generated\Shared\Transfer\VertexSaleTransfer
     */
    protected function resetVertexSaleTaxTotals(VertexSaleTransfer $VertexSaleTransfer): VertexSaleTransfer
    {
        $VertexSaleTransfer->setTaxTotal(0);
        foreach ($VertexSaleTransfer->getItems() as $VertexItemTransfer) {
            $VertexItemTransfer->setTaxTotal(0);
        }
        foreach ($VertexSaleTransfer->getShipments() as $VertexShipmentTransfer) {
            $VertexShipmentTransfer->setTaxTotal(0);
        }

        return $VertexSaleTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\CalculableObjectTransfer $calculableObjectTransfer
     *
     * @return \Generated\Shared\Transfer\CalculableObjectTransfer
     */
    protected function executeCalculableObjectVertexExpanderPlugins(CalculableObjectTransfer $calculableObjectTransfer): CalculableObjectTransfer
    {
        foreach ($this->calculableObjectVertexExpanderPlugins as $calculableObjectVertexExpanderPlugin) {
            $calculableObjectVertexExpanderPlugin->expand($calculableObjectTransfer);
        }

        return $calculableObjectTransfer;
    }
}
