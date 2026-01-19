<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Vertex\Business;

use Spryker\Client\Vertex\VertexClientInterface;
use Spryker\Shared\Vertex\Dependency\Service\VertexToUtilEncodingServiceInterface;
use Spryker\Zed\Kernel\Business\AbstractBusinessFactory;
use SprykerEco\Zed\Vertex\Business\AccessTokenProvider\AccessTokenProvider;
use SprykerEco\Zed\Vertex\Business\AccessTokenProvider\AccessTokenProviderInterface;
use SprykerEco\Zed\Vertex\Business\Aggregator\PriceAggregator;
use SprykerEco\Zed\Vertex\Business\Aggregator\PriceAggregatorInterface;
use SprykerEco\Zed\Vertex\Business\Calculator\Calculator;
use SprykerEco\Zed\Vertex\Business\Calculator\CalculatorInterface;
use SprykerEco\Zed\Vertex\Business\Calculator\FallbackCalculator;
use SprykerEco\Zed\Vertex\Business\Calculator\FallbackCalculatorInterface;
use SprykerEco\Zed\Vertex\Business\Calculator\VertexCalculator;
use SprykerEco\Zed\Vertex\Business\Calculator\VertexCalculatorInterface;
use SprykerEco\Zed\Vertex\Business\Config\ConfigDeleter;
use SprykerEco\Zed\Vertex\Business\Config\ConfigDeleterInterface;
use SprykerEco\Zed\Vertex\Business\Config\ConfigReader;
use SprykerEco\Zed\Vertex\Business\Config\ConfigReaderInterface;
use SprykerEco\Zed\Vertex\Business\Config\ConfigWriter;
use SprykerEco\Zed\Vertex\Business\Config\ConfigWriterInterface;
use SprykerEco\Zed\Vertex\Business\Mapper\Addresses\AddressMapper;
use SprykerEco\Zed\Vertex\Business\Mapper\Addresses\AddressMapperInterface;
use SprykerEco\Zed\Vertex\Business\Mapper\Prices\ItemExpensePriceRetriever;
use SprykerEco\Zed\Vertex\Business\Mapper\Prices\ItemExpensePriceRetrieverInterface;
use SprykerEco\Zed\Vertex\Business\Mapper\VertexMapper;
use SprykerEco\Zed\Vertex\Business\Mapper\VertexMapperInterface;
use SprykerEco\Zed\Vertex\Business\Order\RefundProcessor;
use SprykerEco\Zed\Vertex\Business\Order\RefundProcessorInterface;
use SprykerEco\Zed\Vertex\Business\Sender\PaymentSubmitTaxInvoiceSender;
use SprykerEco\Zed\Vertex\Business\Sender\PaymentSubmitTaxInvoiceSenderInterface;
use SprykerEco\Zed\Vertex\Business\Validator\TaxIdValidator;
use SprykerEco\Zed\Vertex\Business\Validator\TaxIdValidatorInterface;
use SprykerEco\Zed\Vertex\Business\Writer\VertexStoreRelationWriter;
use SprykerEco\Zed\Vertex\Business\Writer\VertexStoreRelationWriterInterface;
use SprykerEco\Zed\Vertex\Dependency\Facade\VertexToKernelAppFacadeInterface;
use SprykerEco\Zed\Vertex\Dependency\Facade\VertexToMessageBrokerFacadeInterface;
use SprykerEco\Zed\Vertex\Dependency\Facade\VertexToOauthClientFacadeInterface;
use SprykerEco\Zed\Vertex\Dependency\Facade\VertexToSalesFacadeInterface;
use SprykerEco\Zed\Vertex\Dependency\Facade\VertexToStoreFacadeInterface;
use SprykerEco\Zed\Vertex\VertexDependencyProvider;

/**
 * @method \Spryker\Zed\Vertex\Persistence\VertexEntityManagerInterface getEntityManager()()
 * @method \Spryker\Zed\Vertex\Persistence\VertexRepositoryInterface getRepository()
 * @method \Spryker\Zed\Vertex\VertexConfig getConfig()
 */
class VertexBusinessFactory extends AbstractBusinessFactory
{
    /**
     * @return \Spryker\Zed\Vertex\Business\Config\ConfigWriterInterface
     */
    public function createConfigWriter(): ConfigWriterInterface
    {
        return new ConfigWriter($this->getEntityManager(), $this->getStoreFacade());
    }

    /**
     * @return \Spryker\Zed\Vertex\Business\Config\ConfigDeleterInterface
     */
    public function createConfigDeleter(): ConfigDeleterInterface
    {
        return new ConfigDeleter($this->getEntityManager(), $this->getStoreFacade());
    }

    /**
     * @return \Spryker\Zed\Vertex\Business\Config\ConfigReaderInterface
     */
    public function createConfigReader(): ConfigReaderInterface
    {
        return new ConfigReader($this->getRepository(), $this->getStoreFacade());
    }

    /**
     * @return \Spryker\Zed\Vertex\Business\Writer\VertexStoreRelationWriterInterface
     */
    public function createVertexStoreRelationWriter(): VertexStoreRelationWriterInterface
    {
        return new VertexStoreRelationWriter($this->getRepository(), $this->createConfigWriter());
    }

    /**
     * @return \Spryker\Zed\Vertex\Dependency\Facade\VertexToStoreFacadeInterface
     */
    public function getStoreFacade(): VertexToStoreFacadeInterface
    {
        return $this->getProvidedDependency(VertexDependencyProvider::FACADE_STORE);
    }

    /**
     * @return array<\Spryker\Zed\VertexExtension\Dependency\Plugin\CalculableObjectVertexExpanderPluginInterface>
     */
    public function getCalculableObjectVertexExpanderPlugins(): array
    {
        return $this->getProvidedDependency(VertexDependencyProvider::PLUGINS_CALCULABLE_OBJECT_TAX_APP_EXPANDER);
    }

    /**
     * @return array<\Spryker\Zed\VertexExtension\Dependency\Plugin\OrderVertexExpanderPluginInterface>
     */
    public function getOrderVertexExpanderPlugins(): array
    {
        return $this->getProvidedDependency(VertexDependencyProvider::PLUGINS_ORDER_TAX_APP_EXPANDER);
    }

    /**
     * @return \Spryker\Zed\Vertex\Business\Calculator\CalculatorInterface
     */
    public function createCalculator(): CalculatorInterface
    {
        return new Calculator(
            $this->getStoreFacade(),
            $this->createConfigReader(),
            $this->createFallbackQuoteCalculator(),
            $this->createFallbackOrderCalculator(),
            $this->createVertexCalculator(),
        );
    }

    /**
     * @return \Spryker\Zed\Vertex\Business\Calculator\FallbackCalculatorInterface
     */
    public function createFallbackQuoteCalculator(): FallbackCalculatorInterface
    {
        return new FallbackCalculator(
            $this->getFallbackQuoteCalculationPlugins(),
        );
    }

    /**
     * @return \Spryker\Zed\Vertex\Business\Calculator\FallbackCalculatorInterface
     */
    public function createFallbackOrderCalculator(): FallbackCalculatorInterface
    {
        return new FallbackCalculator(
            $this->getFallbackOrderCalculationPlugins(),
        );
    }

    /**
     * @return \Spryker\Zed\Vertex\Business\Calculator\VertexCalculatorInterface
     */
    public function createVertexCalculator(): VertexCalculatorInterface
    {
        return new VertexCalculator(
            $this->createVertexMapper(),
            $this->getVertexClient(),
            $this->createAccessTokenProvider(),
            $this->getCalculableObjectVertexExpanderPlugins(),
            $this->createPriceAggregator(),
        );
    }

    /**
     * @return \Spryker\Zed\Vertex\Business\AccessTokenProvider\AccessTokenProviderInterface
     */
    public function createAccessTokenProvider(): AccessTokenProviderInterface
    {
        return new AccessTokenProvider(
            $this->getOauthClientFacade(),
            $this->getConfig(),
        );
    }

    /**
     * @return \Spryker\Zed\Vertex\Business\Sender\PaymentSubmitTaxInvoiceSenderInterface
     */
    public function createPaymentSubmitTaxInvoiceSender(): PaymentSubmitTaxInvoiceSenderInterface
    {
        return new PaymentSubmitTaxInvoiceSender(
            $this->getMessageBrokerFacade(),
            $this->getStoreFacade(),
            $this->getSalesFacade(),
            $this->createVertexMapper(),
            $this->getOrderVertexExpanderPlugins(),
        );
    }

    /**
     * @return \Spryker\Zed\Vertex\Business\Order\RefundProcessorInterface
     */
    public function createRefundProcessor(): RefundProcessorInterface
    {
        return new RefundProcessor(
            $this->getVertexClient(),
            $this->getStoreFacade(),
            $this->getSalesFacade(),
            $this->createVertexMapper(),
            $this->createAccessTokenProvider(),
            $this->createConfigReader(),
            $this->getOrderVertexExpanderPlugins(),
        );
    }

    /**
     * @return \Spryker\Zed\Vertex\Dependency\Facade\VertexToMessageBrokerFacadeInterface
     */
    public function getMessageBrokerFacade(): VertexToMessageBrokerFacadeInterface
    {
        return $this->getProvidedDependency(VertexDependencyProvider::FACADE_MESSAGE_BROKER);
    }

    /**
     * @return \Spryker\Zed\Vertex\Dependency\Facade\VertexToSalesFacadeInterface
     */
    public function getSalesFacade(): VertexToSalesFacadeInterface
    {
        return $this->getProvidedDependency(VertexDependencyProvider::FACADE_SALES);
    }

    /**
     * @return \Spryker\Zed\Vertex\Business\Mapper\VertexMapperInterface
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
     * @return \Spryker\Zed\Vertex\Business\Mapper\Addresses\AddressMapperInterface
     */
    public function createAddressMapper(): AddressMapperInterface
    {
        return new AddressMapper();
    }

    /**
     * @return \Spryker\Zed\Vertex\Business\Mapper\Prices\ItemExpensePriceRetrieverInterface
     */
    public function createItemExpensePriceRetriever(): ItemExpensePriceRetrieverInterface
    {
        return new ItemExpensePriceRetriever();
    }

    /**
     * @return \Spryker\Zed\Vertex\Business\Validator\TaxIdValidatorInterface
     */
    public function createTaxIdValidator(): TaxIdValidatorInterface
    {
        return new TaxIdValidator(
            $this->createConfigReader(),
            $this->createAccessTokenProvider(),
            $this->getKernelAppFacade(),
            $this->getEntityManager(),
            $this->getUtilEncodingService(),
        );
    }

    /**
     * @return \Spryker\Client\Vertex\VertexClientInterface
     */
    public function getVertexClient(): VertexClientInterface
    {
        return $this->getProvidedDependency(VertexDependencyProvider::CLIENT_TAX_APP);
    }

    /**
     * @return \Spryker\Zed\Vertex\Dependency\Facade\VertexToOauthClientFacadeInterface
     */
    public function getOauthClientFacade(): VertexToOauthClientFacadeInterface
    {
        return $this->getProvidedDependency(VertexDependencyProvider::FACADE_OAUTH_CLIENT);
    }

    /**
     * @return \Spryker\Zed\Vertex\Business\Aggregator\PriceAggregatorInterface
     */
    public function createPriceAggregator(): PriceAggregatorInterface
    {
        return new PriceAggregator();
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
     * @return \Spryker\Zed\Vertex\Dependency\Facade\VertexToKernelAppFacadeInterface
     */
    public function getKernelAppFacade(): VertexToKernelAppFacadeInterface
    {
        return $this->getProvidedDependency(VertexDependencyProvider::FACADE_KERNEL_APP);
    }

    /**
     * @return \Spryker\Shared\Vertex\Dependency\Service\VertexToUtilEncodingServiceInterface
     */
    public function getUtilEncodingService(): VertexToUtilEncodingServiceInterface
    {
        return $this->getProvidedDependency(VertexDependencyProvider::SERVICE_UTIL_ENCODING);
    }
}
