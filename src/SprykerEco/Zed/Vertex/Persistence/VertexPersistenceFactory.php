<?php

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerEco\Zed\Vertex\Persistence;

use Orm\Zed\Vertex\Persistence\SpyVertexApiAccessTokenQuery;
use SprykerEco\Zed\Vertex\Persistence\Propel\Mapper\VertexApiAccessTokenMapper;
use Spryker\Zed\Kernel\Persistence\AbstractPersistenceFactory;

/**
 * @method \SprykerEco\Zed\VertexApi\VertexApiConfig getConfig()
 * @method \SprykerEco\Zed\VertexApi\Persistence\VertexApiRepositoryInterface getRepository()
 * @method \SprykerEco\Zed\VertexApi\Persistence\VertexApiEntityManagerInterface getEntityManager()
 */
class VertexPersistenceFactory extends AbstractPersistenceFactory
{
    /**
     * @return \Orm\Zed\Vertex\Persistence\SpyVertexApiAccessTokenQuery
     */
    public function createVertexApiAccessTokenQuery(): SpyVertexApiAccessTokenQuery
    {
        return SpyVertexApiAccessTokenQuery::create();
    }

    /**
     * @return \Pyz\Zed\Vertex\Persistence\Propel\Mapper\VertexApiAccessTokenMapper
     */
    public function createVertexApiAccessTokenMapper(): VertexApiAccessTokenMapper
    {
        return new VertexApiAccessTokenMapper();
    }
}
