<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Zed\Vertex\Persistence;

use Orm\Zed\Vertex\Persistence\SpyVertexApiAccessTokenQuery;
use Spryker\Zed\Kernel\Persistence\AbstractPersistenceFactory;
use SprykerEco\Zed\Vertex\Persistence\Propel\Mapper\VertexApiAccessTokenMapper;
use SprykerEco\Zed\Vertex\Persistence\Propel\Mapper\VertexTaxIdValidationMapper;

/**
 * @method \SprykerEco\Zed\Vertex\VertexConfig getConfig()
 * @method \SprykerEco\Zed\Vertex\Persistence\VertexRepositoryInterface getRepository()
 * @method \SprykerEco\Zed\Vertex\Persistence\VertexEntityManagerInterface getEntityManager()
 */
class VertexPersistenceFactory extends AbstractPersistenceFactory
{
    public function createVertexApiAccessTokenQuery(): SpyVertexApiAccessTokenQuery
    {
        return SpyVertexApiAccessTokenQuery::create();
    }

    public function createVertexApiAccessTokenMapper(): VertexApiAccessTokenMapper
    {
        return new VertexApiAccessTokenMapper();
    }

    public function createVertexTaxIdValidationMapper(): VertexTaxIdValidationMapper
    {
        return new VertexTaxIdValidationMapper();
    }
}
