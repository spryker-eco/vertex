<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Glue\VertexRestApi;

use Spryker\Glue\Kernel\AbstractFactory;
use SprykerEco\Glue\VertexRestApi\Dependency\VertexRestApiToGlossaryStorageClientInterface;
use SprykerEco\Glue\VertexRestApi\Dependency\VertexRestApiToVertexClientInterface;
use SprykerEco\Glue\VertexRestApi\Processor\Validator\TaxIdValidator;
use SprykerEco\Glue\VertexRestApi\Processor\Validator\TaxIdValidatorInterface;

class VertexRestApiFactory extends AbstractFactory
{
    /**
     * @return \SprykerEco\Glue\VertexRestApi\Processor\Validator\TaxIdValidatorInterface
     */
    public function createTaxIdValidator(): TaxIdValidatorInterface
    {
        return new TaxIdValidator(
            $this->getResourceBuilder(),
            $this->getVertexClient(),
            $this->getGlossaryStorageClient(),
        );
    }

    /**
     * @return \SprykerEco\Glue\VertexRestApi\Dependency\VertexRestApiToVertexClientInterface
     */
    public function getVertexClient(): VertexRestApiToVertexClientInterface
    {
        return $this->getProvidedDependency(VertexRestApiDependencyProvider::CLIENT_VERTEX);
    }

    /**
     * @return \SprykerEco\Glue\VertexRestApi\Dependency\VertexRestApiToGlossaryStorageClientInterface
     */
    public function getGlossaryStorageClient(): VertexRestApiToGlossaryStorageClientInterface
    {
        return $this->getProvidedDependency(VertexRestApiDependencyProvider::CLIENT_GLOSSARY_STORAGE);
    }
}
