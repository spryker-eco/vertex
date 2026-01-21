<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Vertex;

use SprykerEco\Shared\Vertex\Dependency\Service\VertexToUtilEncodingServiceBridge;
use Spryker\Zed\Kernel\AbstractBundleDependencyProvider;
use Spryker\Zed\Kernel\Container;
use SprykerEco\Zed\Vertex\Dependency\Facade\VertexToKernelAppFacadeBridge;
use SprykerEco\Zed\Vertex\Dependency\Facade\VertexToKernelAppFacadeInterface;
use SprykerEco\Zed\Vertex\Dependency\Facade\VertexToMessageBrokerFacadeBridge;
use SprykerEco\Zed\Vertex\Dependency\Facade\VertexToOauthClientFacadeBridge;
use SprykerEco\Zed\Vertex\Dependency\Facade\VertexToSalesFacadeBridge;
use SprykerEco\Zed\Vertex\Dependency\Facade\VertexToStoreFacadeBridge;

/**
 * @method \Spryker\Zed\Vertex\VertexConfig getConfig()
 */
class VertexDependencyProvider extends AbstractBundleDependencyProvider
{
    /**
     * @var string
     */
    public const FACADE_SALES = 'FACADE_SALES';

    /**
     * @var string
     */
    public const FACADE_KERNEL_APP = 'FACADE_KERNEL_APP';

    /**
     * @var string
     */
    public const FACADE_STORE = 'FACADE_STORE';

    /**
     * @var string
     */
    public const FACADE_MESSAGE_BROKER = 'FACADE_MESSAGE_BROKER';

    /**
     * @var string
     */
    public const PLUGINS_CALCULABLE_OBJECT_VERTEX_EXPANDER = 'PLUGINS_CALCULABLE_OBJECT_VERTEX_EXPANDER';

    /**
     * @var string
     */
    public const PLUGINS_ORDER_VERTEX_EXPANDER = 'PLUGINS_ORDER_VERTEX_EXPANDER';

    /**
     * string
     *
     * @var string
     */
    public const PLUGINS_FALLBACK_QUOTE_CALCULATION = 'PLUGINS_FALLBACK_QUOTE_CALCULATION';

    /**
     * string
     *
     * @var string
     */
    public const PLUGINS_FALLBACK_ORDER_CALCULATION = 'PLUGINS_FALLBACK_ORDER_CALCULATION';

    /**
     * @var string
     */
    public const CLIENT_VERTEX = 'CLIENT_VERTEX';

    /**
     * @var string
     */
    public const CLIENT_SECRETS_MANAGER = 'CLIENT_SECRETS_MANAGER';

    /**
     * @var string
     */
    public const FACADE_OAUTH_CLIENT = 'FACADE_OAUTH_CLIENT';

    /**
     * @var string
     */
    public const SERVICE_UTIL_ENCODING = 'SERVICE_UTIL_ENCODING';

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    public function provideBusinessLayerDependencies(Container $container): Container
    {
        $container = parent::provideBusinessLayerDependencies($container);
        $container = $this->addStoreFacade($container);
        $container = $this->addCalculableObjectVertexExpanderPlugins($container);
        $container = $this->addOrderVertexExpanderPlugins($container);
        $container = $this->addSalesFacade($container);
        $container = $this->addMessageBrokerFacade($container);
        $container = $this->addVertexClient($container);
        $container = $this->addSecretsManagerClient($container);
        $container = $this->addOauthClientFacade($container);
        $container = $this->addUtilEncodingService($container);
        $container = $this->addFallbackQuoteCalculationPlugins($container);
        $container = $this->addFallbackOrderCalculationPlugins($container);
        $container = $this->provideKernelAppFacade($container);

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    public function providePersistenceLayerDependencies(Container $container): Container
    {
        $container = parent::providePersistenceLayerDependencies($container);
        $container = $this->addUtilEncodingService($container);

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function provideKernelAppFacade(Container $container): Container
    {
        $container->set(static::FACADE_KERNEL_APP, function (Container $container): VertexToKernelAppFacadeInterface {
            return new VertexToKernelAppFacadeBridge($container->getLocator()->kernelApp()->facade());
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addVertexClient(Container $container): Container
    {
        $container->set(static::CLIENT_VERTEX, function (Container $container) {
            return $container->getLocator()->Vertex()->client();
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addOauthClientFacade(Container $container): Container
    {
        $container->set(static::FACADE_OAUTH_CLIENT, function (Container $container) {
            return new VertexToOauthClientFacadeBridge($container->getLocator()->oauthClient()->facade());
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addSalesFacade(Container $container): Container
    {
        $container->set(static::FACADE_SALES, function (Container $container) {
            return new VertexToSalesFacadeBridge($container->getLocator()->sales()->facade());
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addStoreFacade(Container $container): Container
    {
        $container->set(static::FACADE_STORE, function (Container $container) {
            return new VertexToStoreFacadeBridge($container->getLocator()->store()->facade());
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addSecretsManagerClient(Container $container): Container
    {
        $container->set(static::CLIENT_SECRETS_MANAGER, function (Container $container) {
            return $container->getLocator()->secretsManager()->client();
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addUtilEncodingService(Container $container): Container
    {
        $container->set(static::SERVICE_UTIL_ENCODING, function (Container $container) {
            return new VertexToUtilEncodingServiceBridge($container->getLocator()->utilEncoding()->service());
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addMessageBrokerFacade(Container $container): Container
    {
        $container->set(static::FACADE_MESSAGE_BROKER, function (Container $container) {
            return new VertexToMessageBrokerFacadeBridge($container->getLocator()->messageBroker()->facade());
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addCalculableObjectVertexExpanderPlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_CALCULABLE_OBJECT_VERTEX_EXPANDER, function () {
            return $this->getCalculableObjectVertexExpanderPlugins();
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addOrderVertexExpanderPlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_ORDER_VERTEX_EXPANDER, function () {
            return $this->getOrderVertexExpanderPlugins();
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addFallbackQuoteCalculationPlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_FALLBACK_QUOTE_CALCULATION, function () {
            return $this->getFallbackQuoteCalculationPlugins();
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addFallbackOrderCalculationPlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_FALLBACK_ORDER_CALCULATION, function () {
            return $this->getFallbackOrderCalculationPlugins();
        });

        return $container;
    }

    /**
     * @return array<\Spryker\Zed\VertexExtension\Dependency\Plugin\CalculableObjectVertexExpanderPluginInterface>
     */
    protected function getCalculableObjectVertexExpanderPlugins(): array
    {
        return [];
    }

    /**
     * @return array<\Spryker\Zed\VertexExtension\Dependency\Plugin\OrderVertexExpanderPluginInterface>
     */
    protected function getOrderVertexExpanderPlugins(): array
    {
        return [];
    }

    /**
     * This calculation stack is executed as a fallback during quote recalculation when tax app is not configured or is disabled.
     * Please see the descriptions of those plugins in {@link \Spryker\Zed\Calculation\CalculationDependencyProvider::getQuoteCalculatorPluginStack}.
     * This plugin stack should include all plugins present between extracted tax calculation plugins. They will be executed instead of VertexCalculationPlugin.
     *
     * @return array<\Spryker\Zed\CalculationExtension\Dependency\Plugin\CalculationPluginInterface>
     */
    protected function getFallbackQuoteCalculationPlugins(): array
    {
        return [];
    }

    /**
     * This calculation stack is executed as a fallback during order recalculation when tax app is not configured or is disabled.
     * Please see the descriptions of those plugins in {@link \Spryker\Zed\Calculation\CalculationDependencyProvider::getOrderCalculatorPluginStack}.
     * This plugin stack should include all plugins present between extracted tax calculation plugins. They will be executed instead of VertexCalculationPlugin.
     *
     * @return array<\Spryker\Zed\CalculationExtension\Dependency\Plugin\CalculationPluginInterface>
     */
    protected function getFallbackOrderCalculationPlugins(): array
    {
        return [];
    }
}
