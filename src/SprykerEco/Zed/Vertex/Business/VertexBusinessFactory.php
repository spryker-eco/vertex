<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Zed\Vertex\Business;

use Spryker\Service\UtilEncoding\UtilEncodingServiceInterface;
use Spryker\Zed\Kernel\Business\AbstractBusinessFactory;
use Spryker\Zed\Sales\Business\SalesFacadeInterface;
use Spryker\Zed\Store\Business\StoreFacadeInterface;
use SprykerEco\Client\Vertex\Validator\VertexAddressValidator;
use SprykerEco\Client\Vertex\Validator\VertexAddressValidatorInterface;
use SprykerEco\Client\Vertex\Validator\VertexItemValidator;
use SprykerEco\Client\Vertex\Validator\VertexItemValidatorInterface;
use SprykerEco\Client\Vertex\Validator\VertexSaleValidator;
use SprykerEco\Client\Vertex\Validator\VertexSaleValidatorInterface;
use SprykerEco\Client\Vertex\Validator\VertexShipmentValidator;
use SprykerEco\Client\Vertex\Validator\VertexShipmentValidatorInterface;
use SprykerEco\Client\Vertex\Validator\VertexShippingWarehouseValidator;
use SprykerEco\Client\Vertex\Validator\VertexShippingWarehouseValidatorInterface;
use SprykerEco\Client\Vertex\VertexClientInterface as VertexVertexClientInterface;
use SprykerEco\Zed\Vertex\Business\AccessTokenProvider\VertexAccessTokenProvider;
use SprykerEco\Zed\Vertex\Business\AccessTokenProvider\VertexAccessTokenProviderInterface;
use SprykerEco\Zed\Vertex\Business\Aggregator\PriceAggregator;
use SprykerEco\Zed\Vertex\Business\Aggregator\PriceAggregatorInterface;
use SprykerEco\Zed\Vertex\Business\Calculator\Calculator;
use SprykerEco\Zed\Vertex\Business\Calculator\CalculatorInterface;
use SprykerEco\Zed\Vertex\Business\Calculator\FallbackCalculator;
use SprykerEco\Zed\Vertex\Business\Calculator\FallbackCalculatorInterface;
use SprykerEco\Zed\Vertex\Business\Calculator\VertexCalculator;
use SprykerEco\Zed\Vertex\Business\Calculator\VertexCalculatorInterface;
use SprykerEco\Zed\Vertex\Business\Mapper\Addresses\AddressMapper;
use SprykerEco\Zed\Vertex\Business\Mapper\Addresses\AddressMapperInterface;
use SprykerEco\Zed\Vertex\Business\Mapper\Prices\ItemExpensePriceRetriever;
use SprykerEco\Zed\Vertex\Business\Mapper\Prices\ItemExpensePriceRetrieverInterface;
use SprykerEco\Zed\Vertex\Business\Mapper\VertexMapper;
use SprykerEco\Zed\Vertex\Business\Mapper\VertexMapperInterface;
use SprykerEco\Zed\Vertex\Business\Order\RefundProcessor;
use SprykerEco\Zed\Vertex\Business\Order\RefundProcessorInterface;
use SprykerEco\Zed\Vertex\Business\Payment\PaymentSubmitTaxInvoiceHandler;
use SprykerEco\Zed\Vertex\Business\Payment\PaymentSubmitTaxInvoiceHandlerInterface;
use SprykerEco\Zed\Vertex\Business\Resolver\VertexConfigResolver;
use SprykerEco\Zed\Vertex\Business\Resolver\VertexConfigResolverInterface;
use SprykerEco\Zed\Vertex\Business\Validator\TaxIdValidator;
use SprykerEco\Zed\Vertex\Business\Validator\TaxIdValidatorInterface;
use SprykerEco\Zed\Vertex\Business\Validator\VertexConfigValidator;
use SprykerEco\Zed\Vertex\Business\Validator\VertexConfigValidatorInterface;
use SprykerEco\Zed\Vertex\VertexDependencyProvider;

/**
 * @method \SprykerEco\Zed\Vertex\Persistence\VertexEntityManagerInterface getEntityManager()()
 * @method \SprykerEco\Zed\Vertex\Persistence\VertexRepositoryInterface getRepository()
 * @method \SprykerEco\Zed\Vertex\VertexConfig getConfig()
 */
class VertexBusinessFactory extends AbstractBusinessFactory
{
    public function getStoreFacade(): StoreFacadeInterface
    {
        return $this->getProvidedDependency(VertexDependencyProvider::FACADE_STORE);
    }

    /**
     * @return array<\SprykerEco\Zed\Vertex\Dependency\Plugin\CalculableObjectVertexExpanderPluginInterface|\Spryker\Zed\TaxAppExtension\Dependency\Plugin\CalculableObjectTaxAppExpanderPluginInterface>
     */
    public function getCalculableObjectVertexExpanderPlugins(): array
    {
        return $this->getProvidedDependency(VertexDependencyProvider::PLUGINS_CALCULABLE_OBJECT_VERTEX_EXPANDER);
    }

    /**
     * @return array<\SprykerEco\Zed\Vertex\Dependency\Plugin\OrderVertexExpanderPluginInterface|\Spryker\Zed\TaxAppExtension\Dependency\Plugin\OrderTaxAppExpanderPluginInterface>
     */
    public function getOrderVertexExpanderPlugins(): array
    {
        return $this->getProvidedDependency(VertexDependencyProvider::PLUGINS_ORDER_VERTEX_EXPANDER);
    }

    public function createCalculator(): CalculatorInterface
    {
        return new Calculator(
            $this->getStoreFacade(),
            $this->createVertexConfigResolver(),
            $this->createFallbackQuoteCalculator(),
            $this->createFallbackOrderCalculator(),
            $this->createVertexCalculator(),
        );
    }

    public function createFallbackQuoteCalculator(): FallbackCalculatorInterface
    {
        return new FallbackCalculator(
            $this->getFallbackQuoteCalculationPlugins(),
        );
    }

    public function createFallbackOrderCalculator(): FallbackCalculatorInterface
    {
        return new FallbackCalculator(
            $this->getFallbackOrderCalculationPlugins(),
        );
    }

    public function createVertexAccessTokenProvider(): VertexAccessTokenProviderInterface
    {
        return new VertexAccessTokenProvider(
            $this->getVertexClient(),
            $this->getRepository(),
            $this->getEntityManager(),
        );
    }

    public function createPaymentSubmitTaxInvoiceHandler(): PaymentSubmitTaxInvoiceHandlerInterface
    {
        return new PaymentSubmitTaxInvoiceHandler(
            $this->getStoreFacade(),
            $this->getSalesFacade(),
            $this->createVertexMapper(),
            $this->getOrderVertexExpanderPlugins(),
            $this->createVertexConfigResolver(),
            $this->createVertexAccessTokenProvider(),
            $this->getVertexClient(),
        );
    }

    public function createVertexCalculator(): VertexCalculatorInterface
    {
        return new VertexCalculator(
            $this->createVertexMapper(),
            $this->getVertexClient(),
            $this->getCalculableObjectVertexExpanderPlugins(),
            $this->createPriceAggregator(),
            $this->createVertexAccessTokenProvider(),
        );
    }

    public function createRefundProcessor(): RefundProcessorInterface
    {
        return new RefundProcessor(
            $this->getVertexClient(),
            $this->getStoreFacade(),
            $this->getSalesFacade(),
            $this->createVertexMapper(),
            $this->getOrderVertexExpanderPlugins(),
            $this->createVertexAccessTokenProvider(),
            $this->createVertexConfigResolver(),
        );
    }

    public function getSalesFacade(): SalesFacadeInterface
    {
        return $this->getProvidedDependency(VertexDependencyProvider::FACADE_SALES);
    }

    public function createVertexMapper(): VertexMapperInterface
    {
        return new VertexMapper(
            $this->createAddressMapper(),
            $this->createItemExpensePriceRetriever(),
            $this->getStoreFacade(),
            $this->getConfig(),
        );
    }

    public function createAddressMapper(): AddressMapperInterface
    {
        return new AddressMapper();
    }

    public function createItemExpensePriceRetriever(): ItemExpensePriceRetrieverInterface
    {
        return new ItemExpensePriceRetriever();
    }

    public function createTaxIdValidator(): TaxIdValidatorInterface
    {
        return new TaxIdValidator(
            $this->createVertexConfigResolver(),
            $this->getEntityManager(),
            $this->getUtilEncodingService(),
            $this->getVertexClient(),
        );
    }

    public function createAddressValidator(): VertexAddressValidatorInterface
    {
        return new VertexAddressValidator();
    }

    public function createShippingWarehouseValidator(): VertexShippingWarehouseValidatorInterface
    {
        return new VertexShippingWarehouseValidator($this->createAddressValidator());
    }

    public function createItemValidator(): VertexItemValidatorInterface
    {
        return new VertexItemValidator(
            $this->createAddressValidator(),
            $this->createShippingWarehouseValidator(),
        );
    }

    public function createShipmentValidator(): VertexShipmentValidatorInterface
    {
        return new VertexShipmentValidator($this->createAddressValidator());
    }

    public function createSaleValidator(): VertexSaleValidatorInterface
    {
        return new VertexSaleValidator(
            $this->createItemValidator(),
            $this->createShipmentValidator(),
        );
    }

    public function getVertexClient(): VertexVertexClientInterface
    {
        return $this->getProvidedDependency(VertexDependencyProvider::CLIENT_VERTEX);
    }

    public function createPriceAggregator(): PriceAggregatorInterface
    {
        return new PriceAggregator();
    }

    public function createVertexConfigResolver(): VertexConfigResolverInterface
    {
        return new VertexConfigResolver(
            $this->getConfig(),
            $this->getStoreFacade(),
            $this->createVertexConfigValidator(),
        );
    }

    public function createVertexConfigValidator(): VertexConfigValidatorInterface
    {
        return new VertexConfigValidator($this->getVertexClient());
    }

    /**
     * @return array<\Spryker\Zed\CalculationExtension\Dependency\Plugin\CalculationPluginInterface>
     */
    public function getFallbackQuoteCalculationPlugins(): array
    {
        return $this->getProvidedDependency(VertexDependencyProvider::PLUGINS_FALLBACK_QUOTE_CALCULATION);
    }

    /**
     * @return array<\Spryker\Zed\CalculationExtension\Dependency\Plugin\CalculationPluginInterface>
     */
    public function getFallbackOrderCalculationPlugins(): array
    {
        return $this->getProvidedDependency(VertexDependencyProvider::PLUGINS_FALLBACK_ORDER_CALCULATION);
    }

    public function getUtilEncodingService(): UtilEncodingServiceInterface
    {
        return $this->getProvidedDependency(VertexDependencyProvider::SERVICE_UTIL_ENCODING);
    }
}
