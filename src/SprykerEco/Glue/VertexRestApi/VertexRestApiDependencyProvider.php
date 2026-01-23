<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Glue\VertexRestApi;

use Spryker\Glue\Kernel\AbstractBundleDependencyProvider;
use Spryker\Glue\Kernel\Container;
use SprykerEco\Glue\VertexRestApi\Dependency\VertexRestApiToGlossaryStorageClientBridge;
use SprykerEco\Glue\VertexRestApi\Dependency\VertexRestApiToGlossaryStorageClientInterface;
use SprykerEco\Glue\VertexRestApi\Dependency\VertexRestApiToVertexClientBridge;

/**
 * @method \SprykerEco\Glue\VertexRestApi\VertexRestApiConfig getConfig()
 */
class VertexRestApiDependencyProvider extends AbstractBundleDependencyProvider
{
    /**
     * @var string
     */
    public const CLIENT_VERTEX = 'CLIENT_VERTEX';

    /**
     * @var string
     */
    public const CLIENT_GLOSSARY_STORAGE = 'CLIENT_GLOSSARY_STORAGE';

    /**
     * @param \Spryker\Glue\Kernel\Container $container
     *
     * @return \Spryker\Glue\Kernel\Container
     */
    public function provideDependencies(Container $container): Container
    {
        $container = parent::provideDependencies($container);
        $container = $this->addVertexClient($container);
        $container = $this->addGlossaryStorageClient($container);

        return $container;
    }

    /**
     * @param \Spryker\Glue\Kernel\Container $container
     *
     * @return \Spryker\Glue\Kernel\Container
     */
    protected function addVertexClient(Container $container): Container
    {
        $container->set(static::CLIENT_VERTEX, function (Container $container) {
            return new VertexRestApiToVertexClientBridge(
                $container->getLocator()->vertex()->client(),
            );
        });

        return $container;
    }

    /**
     * @param \Spryker\Glue\Kernel\Container $container
     *
     * @return \Spryker\Glue\Kernel\Container
     */
    protected function addGlossaryStorageClient(Container $container): Container
    {
        $container->set(static::CLIENT_GLOSSARY_STORAGE, function (Container $container): VertexRestApiToGlossaryStorageClientInterface {
            return new VertexRestApiToGlossaryStorageClientBridge(
                $container->getLocator()->glossaryStorage()->client(),
            );
        });

        return $container;
    }
}
