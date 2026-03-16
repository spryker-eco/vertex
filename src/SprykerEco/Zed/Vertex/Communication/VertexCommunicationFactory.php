<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Zed\Vertex\Communication;

use Spryker\Zed\Kernel\Communication\AbstractCommunicationFactory;
use SprykerEco\Zed\Vertex\Communication\Expander\CustomerWithVertexSpecificFieldsExpander;
use SprykerEco\Zed\Vertex\Communication\Expander\ExpensesWithVertexCodeExpander;
use SprykerEco\Zed\Vertex\Communication\Expander\ItemWithVertexSpecificFieldsExpander;
use SprykerEco\Zed\Vertex\Communication\Expander\ProductOptionWithVertexCodeExpander;
use SprykerEco\Zed\Vertex\Communication\Mapper\VertexCodeMapper;

/**
 * @method \SprykerEco\Zed\Vertex\VertexConfig getConfig()
 * @method \SprykerEco\Zed\Vertex\Business\VertexFacadeInterface getFacade()
 * @method \SprykerEco\Zed\Vertex\Persistence\VertexRepositoryInterface getRepository()
 * @method \SprykerEco\Zed\Vertex\Persistence\VertexEntityManagerInterface getEntityManager()
 */
class VertexCommunicationFactory extends AbstractCommunicationFactory
{
    /**
     * @return \SprykerEco\Zed\Vertex\Communication\Expander\CustomerWithVertexSpecificFieldsExpander
     */
    public function createCustomerWithVertexSpecificFieldsMapper(): CustomerWithVertexSpecificFieldsExpander
    {
        return new CustomerWithVertexSpecificFieldsExpander(
            $this->createVertexCodeMapper(),
        );
    }

    /**
     * @return \SprykerEco\Zed\Vertex\Communication\Expander\ExpensesWithVertexCodeExpander
     */
    public function createExpensesWithVertexCodeExpander(): ExpensesWithVertexCodeExpander
    {
        return new ExpensesWithVertexCodeExpander(
            $this->createVertexCodeMapper(),
        );
    }

    /**
     * @return \SprykerEco\Zed\Vertex\Communication\Expander\ItemWithVertexSpecificFieldsExpander
     */
    public function createItemWithVertexTaxCodeExpander(): ItemWithVertexSpecificFieldsExpander
    {
        return new ItemWithVertexSpecificFieldsExpander(
            $this->createVertexCodeMapper(),
        );
    }

    /**
     * @return \SprykerEco\Zed\Vertex\Communication\Expander\ProductOptionWithVertexCodeExpander
     */
    public function createProductOptionWithVertexCodeExpander(): ProductOptionWithVertexCodeExpander
    {
        return new ProductOptionWithVertexCodeExpander(
            $this->createVertexCodeMapper(),
        );
    }

    /**
     * This mapper is just example, please implement your own logic to map your tax codes to vertex codes.
     *
     * @return \SprykerEco\Zed\Vertex\Communication\Mapper\VertexCodeMapper
     */
    public function createVertexCodeMapper(): VertexCodeMapper
    {
        return new VertexCodeMapper();
    }
}
