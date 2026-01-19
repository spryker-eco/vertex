<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\Vertex\Communication;

use Spryker\Zed\Kernel\Communication\AbstractCommunicationFactory;

/**
 * @method \Spryker\Zed\Vertex\VertexConfig getConfig()
 * @method \Spryker\Zed\Vertex\Business\VertexFacadeInterface getFacade()
 * @method \Spryker\Zed\Vertex\Persistence\VertexRepositoryInterface getRepository()
 * @method \Spryker\Zed\Vertex\Persistence\VertexEntityManagerInterface getEntityManager()
 */
class VertexCommunicationFactory extends AbstractCommunicationFactory
{
}
