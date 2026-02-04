<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Vertex\Business;

use Spryker\Zed\Kernel\Business\AbstractBusinessFactory;
use SprykerEco\Client\Vertex\Validator\VertexAddressValidator;
use SprykerEco\Client\Vertex\Validator\VertexItemValidator;
use SprykerEco\Client\Vertex\Validator\VertexQuotationValidator;
use SprykerEco\Client\Vertex\Validator\VertexInvoiceValidator;
use SprykerEco\Client\Vertex\Validator\VertexSaleValidator;
use SprykerEco\Client\Vertex\Validator\VertexShipmentValidator;
use SprykerEco\Client\Vertex\Validator\VertexShippingWarehouseValidator;
use SprykerEco\Client\Vertex\VertexClientInterface as VertexVertexClientInterface;
use SprykerEco\Shared\Vertex\Dependency\Service\VertexToUtilEncodingServiceInterface;
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
use SprykerEco\Zed\Vertex\Dependency\Facade\VertexToSalesFacadeInterface;
use SprykerEco\Zed\Vertex\Dependency\Facade\VertexToStoreFacadeInterface;
use SprykerEco\Zed\Vertex\VertexDependencyProvider;

/**
 * @method \SprykerEco\Zed\Vertex\Persistence\VertexEntityManagerInterface getEntityManager()()
 * @method \SprykerEco\Zed\Vertex\Persistence\VertexRepositoryInterface getRepository()
 * @method \SprykerEco\Zed\Vertex\VertexConfig getConfig()
 */
class VertexBusinessFactory extends AbstractBusinessFactory
{

    /**
     * @return \SprykerEco\Zed\Vertex\Dependency\Facade\VertexToStoreFacadeInterface
     */
    public function getStoreFacade(): VertexToStoreFacadeInterface
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

    /**
     * @return \SprykerEco\Zed\Vertex\Business\Calculator\CalculatorInterface
     */
    public function createCalculator(): CalculatorInterface
    {
        return new Calculator(
            $this->getStoreFacade(),
            $this->createVertexConfigResolver(),
            $this->createFallbackQuoteCalculator(),
            $this->createFallbackOrderCalculator(),
            $this->createVertexCalculator()
        );
    }

    /**
     * @return \SprykerEco\Zed\Vertex\Business\Calculator\FallbackCalculatorInterface
     */
    public function createFallbackQuoteCalculator(): FallbackCalculatorInterface
    {
        return new FallbackCalculator(
            $this->getFallbackQuoteCalculationPlugins(),
        );
    }

    /**
     * @return \SprykerEco\Zed\Vertex\Business\Calculator\FallbackCalculatorInterface
     */
    public function createFallbackOrderCalculator(): FallbackCalculatorInterface
    {
        return new FallbackCalculator(
            $this->getFallbackOrderCalculationPlugins(),
        );
    }

    /**
     * @return \SprykerEco\Zed\Vertex\Business\AccessTokenProvider\VertexAccessTokenProviderInterface
     */
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

    /**
     * @return \SprykerEco\Zed\Vertex\Business\Calculator\VertexCalculatorInterface
     */
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

    /**
     * @return \SprykerEco\Zed\Vertex\Business\Order\RefundProcessorInterface
     */
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

    /**
     * @return \SprykerEco\Zed\Vertex\Dependency\Facade\VertexToSalesFacadeInterface
     */
    public function getSalesFacade(): VertexToSalesFacadeInterface
    {
        return $this->getProvidedDependency(VertexDependencyProvider::FACADE_SALES);
    }

    /**
     * @return \SprykerEco\Zed\Vertex\Business\Mapper\VertexMapperInterface
     */
    public function createVertexMapper(): VertexMapperInterface
    {
        return new VertexMapper(
            $this->createAddressMapper(),
            $this->createItemExpensePriceRetriever(),
            $this->getStoreFacade(),
            $this->getConfig(),
        );
    }

    /**
     * @return \SprykerEco\Zed\Vertex\Business\Mapper\Addresses\AddressMapperInterface
     */
    public function createAddressMapper(): AddressMapperInterface
    {
        return new AddressMapper();
    }

    /**
     * @return \SprykerEco\Zed\Vertex\Business\Mapper\Prices\ItemExpensePriceRetrieverInterface
     */
    public function createItemExpensePriceRetriever(): ItemExpensePriceRetrieverInterface
    {
        return new ItemExpensePriceRetriever();
    }

    /**
     * @return \SprykerEco\Zed\Vertex\Business\Validator\TaxIdValidatorInterface
     */
    public function createTaxIdValidator(): TaxIdValidatorInterface
    {
        return new TaxIdValidator(
            $this->createVertexConfigResolver(),
            $this->getEntityManager(),
            $this->getUtilEncodingService(),
            $this->getVertexClient(),
        );
    }

    /**
     * @return \SprykerEco\Client\Vertex\Validator\VertexAddressValidator
     */
    public function createAddressValidator(): VertexAddressValidator
    {
        return new VertexAddressValidator();
    }

    /**
     * @return \SprykerEco\Client\Vertex\Validator\VertexShippingWarehouseValidator
     */
    public function createShippingWarehouseValidator(): VertexShippingWarehouseValidator
    {
        return new VertexShippingWarehouseValidator($this->createAddressValidator());
    }

    /**
     * @return \SprykerEco\Client\Vertex\Validator\VertexItemValidator
     */
    public function createItemValidator(): VertexItemValidator
    {
        return new VertexItemValidator(
            $this->createAddressValidator(),
            $this->createShippingWarehouseValidator()
        );
    }

    /**
     * @return \SprykerEco\Client\Vertex\Validator\VertexShipmentValidator
     */
    public function createShipmentValidator(): VertexShipmentValidator
    {
        return new VertexShipmentValidator($this->createAddressValidator());
    }

    /**
     * @return \SprykerEco\Client\Vertex\Validator\VertexSaleValidator
     */
    public function createSaleValidator(): VertexSaleValidator
    {
        return new VertexSaleValidator(
            $this->createItemValidator(),
            $this->createShipmentValidator()
        );
    }

    /**
     * @return \SprykerEco\Client\Vertex\Validator\VertexQuotationValidator
     */
    public function createQuotationValidator(): VertexQuotationValidator
    {
        return new VertexQuotationValidator($this->createSaleValidator());
    }

    /**
     * @return \SprykerEco\Client\Vertex\Validator\VertexInvoiceValidator
     */
    public function createRefundsValidator(): VertexInvoiceValidator
    {
        return new VertexInvoiceValidator($this->createSaleValidator());
    }

    /**
     * @return \SprykerEco\Client\Vertex\VertexClientInterface
     */
    public function getVertexClient(): VertexVertexClientInterface
    {
        return $this->getProvidedDependency(VertexDependencyProvider::CLIENT_VERTEX);
    }

    /**
     * @return \SprykerEco\Zed\Vertex\Business\Aggregator\PriceAggregatorInterface
     */
    public function createPriceAggregator(): PriceAggregatorInterface
    {
        return new PriceAggregator();
    }

    public function createVertexConfigResolver(): VertexConfigResolverInterface
    {
        return new VertexConfigResolver(
            $this->getConfig(),
            $this->getStoreFacade(),
            $this->createVertexConfigValidator()
        );
    }

    /**
     * @return \SprykerEco\Zed\Vertex\Business\Validator\VertexConfigValidator
     */
    public function createVertexConfigValidator(): VertexConfigValidator
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

    /**
     * @return \SprykerEco\Shared\Vertex\Dependency\Service\VertexToUtilEncodingServiceInterface
     */
    public function getUtilEncodingService(): VertexToUtilEncodingServiceInterface
    {
        return $this->getProvidedDependency(VertexDependencyProvider::SERVICE_UTIL_ENCODING);
    }
}
