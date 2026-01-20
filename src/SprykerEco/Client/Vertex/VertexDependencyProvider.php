<?php

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerEco\Client\Vertex;

use Pyz\Zed\VertexConfig\VertexConfigDependencyProvider;
use Spryker\Client\Kernel\AbstractDependencyProvider;
use Spryker\Client\Kernel\Container;

class VertexDependencyProvider extends AbstractDependencyProvider
{
    /**
     * @var string
     */
    public const CLIENT_SECRETS_MANAGER = 'CLIENT_SECRETS_MANAGER';

    /**
     * @var string
     */
    public const SERVICE_UTIL_ENCODING = 'SERVICE_UTIL_ENCODING';

    /**
     * @var string
     */
    // public const FACADE_VERTEX_CONFIG = 'FACADE_VERTEX_CONFIG';

    /**
     * @var string
     */
    // public const FACADE_MESSAGE_BROKER = 'FACADE_MESSAGE_BROKER';

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    public function provideBusinessLayerDependencies(Container $container): Container
    {
        $container = parent::provideBusinessLayerDependencies($container);

        $container = $this->addUtilEncodingService($container);
        $container = $this->addSecretsManagerClient($container);
        // $container = $this->addVertexConfigFacade($container);
        $container = $this->addUtilTextService($container);
        // $container = $this->addMessageBrokerFacade($container);

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
            return $container->getLocator()->utilEncoding()->service();
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

    // /**
    //  * @param \Spryker\Zed\Kernel\Container $container
    //  *
    //  * @return \Spryker\Zed\Kernel\Container
    //  */
    // protected function addVertexConfigFacade(Container $container): Container
    // {
    //     $container->set(static::FACADE_VERTEX_CONFIG, function (Container $container) {
    //         return $container->getLocator()->vertexConfig()->facade();
    //     });

    //     return $container;
    // }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addUtilTextService(Container $container): Container
    {
        $container->set(VertexConfigDependencyProvider::SERVICE_UTIL_TEXT, function (Container $container) {
            return $container->getLocator()->utilText()->service();
        });

        return $container;
    }

    // /**
    //  * @param \Spryker\Zed\Kernel\Container $container
    //  *
    //  * @return \Spryker\Zed\Kernel\Container
    //  */
    // protected function addMessageBrokerFacade(Container $container): Container
    // {
    //     $container->set(static::FACADE_MESSAGE_BROKER, function (Container $container) {
    //         return $container->getLocator()->messageBroker()->facade();
    //     });

    //     return $container;
    // }
}
