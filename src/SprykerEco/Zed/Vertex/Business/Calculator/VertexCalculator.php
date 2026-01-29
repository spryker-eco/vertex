<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Vertex\Business\Calculator;

use Generated\Shared\Transfer\ApiErrorMessageTransfer;
use Generated\Shared\Transfer\CalculableObjectTransfer;
use Generated\Shared\Transfer\VertexConfigTransfer;
use Generated\Shared\Transfer\VertexSaleTransfer;
use Generated\Shared\Transfer\VertexCalculationRequestTransfer;
use Generated\Shared\Transfer\VertexCalculationResponseTransfer;
use Generated\Shared\Transfer\TaxTotalTransfer;
use SprykerEco\Client\Vertex\VertexClientInterface;
use Spryker\Shared\Log\LoggerTrait;
use SprykerEco\Zed\Vertex\Business\AccessTokenProvider\VertexAccessTokenProviderInterface;
use SprykerEco\Zed\Vertex\Business\Aggregator\PriceAggregatorInterface;
use SprykerEco\Zed\Vertex\Business\Mapper\VertexMapperInterface;

class VertexCalculator implements VertexCalculatorInterface
{
    use LoggerTrait;

    /**
     * @uses \Spryker\Shared\Shipment\ShipmentConfig::SHIPMENT_EXPENSE_TYPE
     *
     * @var string
     */
    public const SHIPMENT_EXPENSE_TYPE = 'SHIPMENT_EXPENSE_TYPE';

    /**
     * @uses \Spryker\Shared\Price\PriceConfig::PRICE_MODE_NET
     *
     * @var string
     */
    protected const PRICE_MODE_NET = 'NET_MODE';

    /**
     * @param \SprykerEco\Zed\Vertex\Business\Mapper\VertexMapperInterface $vertexMapper
     * @param \SprykerEco\Client\Vertex\VertexClientInterface $vertexClient
     * @param array<\SprykerEco\Zed\VertexExtension\Dependency\Plugin\CalculableObjectVertexExpanderPluginInterface> $calculableObjectVertexExpanderPlugins
     * @param \SprykerEco\Zed\Vertex\Business\Aggregator\PriceAggregatorInterface $priceAggregator
     * @param \SprykerEco\Zed\Vertex\Business\AccessTokenProvider\VertexAccessTokenProviderInterface $vertexAccessTokenProvider
     */
    public function __construct(
        protected VertexMapperInterface $vertexMapper,
        protected VertexClientInterface $vertexClient,
        protected array $calculableObjectVertexExpanderPlugins,
        protected PriceAggregatorInterface $priceAggregator,
        protected VertexAccessTokenProviderInterface $vertexAccessTokenProvider,
    ) {}

    public function recalculate(CalculableObjectTransfer $calculableObjectTransfer, VertexConfigTransfer $vertexConfigTransfer): void
    {
        $calculableObjectTransfer = $this->executeCalculableObjectVertexExpanderPlugins($calculableObjectTransfer);

        $vertexSaleTransfer = $this->vertexMapper->mapCalculableObjectToVertexSaleTransfer($calculableObjectTransfer, new VertexSaleTransfer());

        // for correct tax calculation in NET price mode, at least one shipment must be selected, otherwise tax calculation is skipped.
        if ($calculableObjectTransfer->getPriceModeOrFail() === static::PRICE_MODE_NET && $this->getShipmentExpenses($calculableObjectTransfer) === 0) {
            $calculableObjectTransfer->getTotalsOrFail()->setTaxTotal((new TaxTotalTransfer())->setAmount(0));
            $this->priceAggregator->calculatePriceAggregation($vertexSaleTransfer, $calculableObjectTransfer);

            return;
        }

        $vertexCalculationResponseTransfer = $this->getCachedVertexResponseTransfer($calculableObjectTransfer, $vertexSaleTransfer);

        if (!$vertexCalculationResponseTransfer) {
            $vertexCalculationResponseTransfer = $this->getVertexCalculationResponse($vertexSaleTransfer, $vertexConfigTransfer);

            if (!$vertexCalculationResponseTransfer->getIsSuccessful()) {
                $apiErrorMessages = array_map(function (ApiErrorMessageTransfer $apiErrorMessageTransfer) {
                    return $apiErrorMessageTransfer->toArray();
                }, $vertexCalculationResponseTransfer->getApiErrorMessages()->getArrayCopy());
                $this->getLogger()->error('Tax calculation failed.', ['apiErrorMessages' => $apiErrorMessages]);

                $vertexSaleTransfer = $this->resetVertexSaleTaxTotals($vertexSaleTransfer);
                $vertexCalculationResponseTransfer->setSale($vertexSaleTransfer);
            }
        }

        $calculableObjectTransfer = $this->priceAggregator->calculatePriceAggregation($vertexCalculationResponseTransfer->getSaleOrFail(), $calculableObjectTransfer);

        if ($vertexCalculationResponseTransfer->getIsSuccessful()) {
            $calculableObjectTransfer->setVertexSaleHash($this->getVertexSaleHash($vertexSaleTransfer));
            $calculableObjectTransfer->setVertexCalculationResponse($vertexCalculationResponseTransfer);
        }
    }

    protected function getShipmentExpenses(CalculableObjectTransfer $calculableObjectTransfer): \ArrayObject
    {
        $shipmentExpenses = new \ArrayObject();

        foreach ($calculableObjectTransfer->getExpenses() as $hash => $expenseTransfer) {
            if ($expenseTransfer->getType() !== static::SHIPMENT_EXPENSE_TYPE) {
                $shipmentExpenses->append($expenseTransfer);
            }
        }

        return $shipmentExpenses;
    }

    /**
     * @param \Generated\Shared\Transfer\VertexSaleTransfer $vertexSaleTransfer
     * @param \Generated\Shared\Transfer\VertexConfigTransfer $vertexConfigTransfer
     * @param \Generated\Shared\Transfer\StoreTransfer $storeTransfer
     *
     * @return \Generated\Shared\Transfer\VertexCalculationResponseTransfer
     */
    protected function getVertexCalculationResponse(
        VertexSaleTransfer $vertexSaleTransfer,
        VertexConfigTransfer $vertexConfigTransfer
    ): VertexCalculationResponseTransfer {
        $vertexApiAccessTokenTransfer = $this->vertexAccessTokenProvider->provideVertexAccessToken($vertexConfigTransfer);

        //TODO: Add an early return if the access token is not available
        return $this->vertexClient->calculateTax(
            (new VertexCalculationRequestTransfer())
                ->setSale($vertexSaleTransfer)
                ->setVertexApiAccessToken($vertexApiAccessTokenTransfer),
            $vertexConfigTransfer
        );
    }

    protected function getVertexSaleHash(VertexSaleTransfer $vertexSaleTransfer): string
    {
        return md5(json_encode($vertexSaleTransfer->toArray()) ?: '');
    }

    protected function getCachedVertexResponseTransfer(
        CalculableObjectTransfer $calculableObjectTransfer,
        VertexSaleTransfer $vertexSaleTransfer
    ): ?VertexCalculationResponseTransfer {
        $currentTaxRequestHash = null;

        if ($calculableObjectTransfer->getVertexSaleHash()) {
            $currentTaxRequestHash = $this->getVertexSaleHash($vertexSaleTransfer);
        }

        // Quote was not changed since last tax calculation request. Tax calculation is skipped.
        if ($currentTaxRequestHash === $calculableObjectTransfer->getVertexSaleHash() && $calculableObjectTransfer->getVertexCalculationResponse()) {
            return $calculableObjectTransfer->getVertexCalculationResponse();
        }

        return null;
    }

    protected function resetVertexSaleTaxTotals(VertexSaleTransfer $vertexSaleTransfer): VertexSaleTransfer
    {
        $vertexSaleTransfer->setTaxTotal(0);
        foreach ($vertexSaleTransfer->getItems() as $VertexItemTransfer) {
            $VertexItemTransfer->setTaxTotal(0);
        }
        foreach ($vertexSaleTransfer->getShipments() as $VertexShipmentTransfer) {
            $VertexShipmentTransfer->setTaxTotal(0);
        }

        return $vertexSaleTransfer;
    }

    protected function executeCalculableObjectVertexExpanderPlugins(CalculableObjectTransfer $calculableObjectTransfer): CalculableObjectTransfer
    {
        foreach ($this->calculableObjectVertexExpanderPlugins as $calculableObjectVertexExpanderPlugin) {
            $calculableObjectVertexExpanderPlugin->expand($calculableObjectTransfer);
        }

        return $calculableObjectTransfer;
    }
}
