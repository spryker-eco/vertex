<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Glue\Vertex;

use Spryker\Glue\Kernel\AbstractBundleDependencyProvider;
use Spryker\Glue\Kernel\Container;

/**
 * @method \SprykerEco\Glue\Vertex\VertexConfig getConfig()
 */
class VertexDependencyProvider extends AbstractBundleDependencyProvider
{
    public const CLIENT_VERTEX = 'CLIENT_VERTEX';

    public const CLIENT_GLOSSARY_STORAGE = 'CLIENT_GLOSSARY_STORAGE';

    public function provideDependencies(Container $container): Container
    {
        $container = parent::provideDependencies($container);

        $container = $this->addVertexClient($container);
        $container = $this->addGlossaryStorageClient($container);

        return $container;
    }

    protected function addVertexClient(Container $container): Container
    {
        $container->set(static::CLIENT_VERTEX, function (Container $container) {
            return $container->getLocator()->vertex()->client();
        });

        return $container;
    }

    protected function addGlossaryStorageClient(Container $container): Container
    {
        $container->set(static::CLIENT_GLOSSARY_STORAGE, function (Container $container) {
            return $container->getLocator()->glossaryStorage()->client();
        });

        return $container;
    }
}
