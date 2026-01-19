<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Vertex\Persistence;

use Orm\Zed\Vertex\Persistence\SpyVertexConfigQuery;
use Spryker\Shared\Vertex\Dependency\Service\VertexToUtilEncodingServiceInterface;
use Spryker\Zed\Kernel\Persistence\AbstractPersistenceFactory;
use Spryker\Zed\Vertex\Persistence\Mapper\VertexConfigMapper;
use Spryker\Zed\Vertex\VertexDependencyProvider;

/**
 * @method \Spryker\Zed\Vertex\Persistence\VertexRepositoryInterface getRepository()
 * @method \Spryker\Zed\Vertex\Persistence\VertexEntityManagerInterface getEntityManager()
 * @method \Spryker\Zed\Vertex\VertexConfig getConfig()
 */
class VertexPersistenceFactory extends AbstractPersistenceFactory
{
    /**
     * @return \Orm\Zed\Vertex\Persistence\SpyVertexConfigQuery
     */
    public function createVertexConfigQuery(): SpyVertexConfigQuery
    {
        return SpyVertexConfigQuery::create();
    }

    /**
     * @return \Spryker\Zed\Vertex\Persistence\Mapper\VertexConfigMapper
     */
    public function createVertexConfigMapper(): VertexConfigMapper
    {
        return new VertexConfigMapper($this->getUtilEncodingService());
    }

    /**
     * @return \Spryker\Shared\Vertex\Dependency\Service\VertexToUtilEncodingServiceInterface
     */
    public function getUtilEncodingService(): VertexToUtilEncodingServiceInterface
    {
        return $this->getProvidedDependency(VertexDependencyProvider::SERVICE_UTIL_ENCODING);
    }
}
