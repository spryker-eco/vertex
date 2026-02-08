<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Vertex;

use Spryker\Zed\Kernel\AbstractBundleDependencyProvider;
use Spryker\Zed\Kernel\Container;

/**
 * @method \Spryker\Zed\Vertex\VertexConfig getConfig()
 */
class VertexDependencyProvider extends AbstractBundleDependencyProvider
{
    public const FACADE_SALES = 'FACADE_SALES';

    public const FACADE_STORE = 'FACADE_STORE';

    public const PLUGINS_CALCULABLE_OBJECT_VERTEX_EXPANDER = 'PLUGINS_CALCULABLE_OBJECT_VERTEX_EXPANDER';

    public const PLUGINS_ORDER_VERTEX_EXPANDER = 'PLUGINS_ORDER_VERTEX_EXPANDER';

    public const PLUGINS_FALLBACK_QUOTE_CALCULATION = 'PLUGINS_FALLBACK_QUOTE_CALCULATION';

    public const PLUGINS_FALLBACK_ORDER_CALCULATION = 'PLUGINS_FALLBACK_ORDER_CALCULATION';

    public const CLIENT_VERTEX = 'CLIENT_VERTEX';

    public const CLIENT_SECRETS_MANAGER = 'CLIENT_SECRETS_MANAGER';

    public const SERVICE_UTIL_ENCODING = 'SERVICE_UTIL_ENCODING';

    public function provideBusinessLayerDependencies(Container $container): Container
    {
        $container = parent::provideBusinessLayerDependencies($container);

        $container = $this->addStoreFacade($container);
        $container = $this->addCalculableObjectVertexExpanderPlugins($container);
        $container = $this->addOrderVertexExpanderPlugins($container);
        $container = $this->addSalesFacade($container);
        $container = $this->addVertexClient($container);
        $container = $this->addSecretsManagerClient($container);
        $container = $this->addUtilEncodingService($container);
        $container = $this->addFallbackQuoteCalculationPlugins($container);
        $container = $this->addFallbackOrderCalculationPlugins($container);

        return $container;
    }

    public function providePersistenceLayerDependencies(Container $container): Container
    {
        $container = parent::providePersistenceLayerDependencies($container);
        $container = $this->addUtilEncodingService($container);

        return $container;
    }

    protected function addVertexClient(Container $container): Container
    {
        $container->set(static::CLIENT_VERTEX, function (Container $container) {
            return $container->getLocator()->Vertex()->client();
        });

        return $container;
    }

    protected function addSalesFacade(Container $container): Container
    {
        $container->set(static::FACADE_SALES, function (Container $container) {
            return $container->getLocator()->sales()->facade();
        });

        return $container;
    }

    protected function addStoreFacade(Container $container): Container
    {
        $container->set(static::FACADE_STORE, function (Container $container) {
            return $container->getLocator()->store()->facade();
        });

        return $container;
    }

    protected function addSecretsManagerClient(Container $container): Container
    {
        $container->set(static::CLIENT_SECRETS_MANAGER, function (Container $container) {
            return $container->getLocator()->secretsManager()->client();
        });

        return $container;
    }

    protected function addUtilEncodingService(Container $container): Container
    {
        $container->set(static::SERVICE_UTIL_ENCODING, function (Container $container) {
            return $container->getLocator()->utilEncoding()->service();
        });

        return $container;
    }

    protected function addCalculableObjectVertexExpanderPlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_CALCULABLE_OBJECT_VERTEX_EXPANDER, function () {
            return $this->getCalculableObjectVertexExpanderPlugins();
        });

        return $container;
    }

    protected function addOrderVertexExpanderPlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_ORDER_VERTEX_EXPANDER, function () {
            return $this->getOrderVertexExpanderPlugins();
        });

        return $container;
    }

    protected function addFallbackQuoteCalculationPlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_FALLBACK_QUOTE_CALCULATION, function () {
            return $this->getFallbackQuoteCalculationPlugins();
        });

        return $container;
    }

    protected function addFallbackOrderCalculationPlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_FALLBACK_ORDER_CALCULATION, function () {
            return $this->getFallbackOrderCalculationPlugins();
        });

        return $container;
    }

    /**
     * @return array<\SprykerEco\Zed\Vertex\Dependency\Plugin\CalculableObjectVertexExpanderPluginInterface|\Spryker\Zed\TaxAppExtension\Dependency\Plugin\CalculableObjectTaxAppExpanderPluginInterface>
     */
    protected function getCalculableObjectVertexExpanderPlugins(): array
    {
        return [];
    }

    /**
     * @return array<\SprykerEco\Zed\Vertex\Dependency\Plugin\OrderVertexExpanderPluginInterface|\Spryker\Zed\TaxAppExtension\Dependency\Plugin\OrderTaxAppExpanderPluginInterface>
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
