<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Glue\Vertex;

use Spryker\Client\GlossaryStorage\GlossaryStorageClientInterface;
use Spryker\Glue\Kernel\AbstractFactory;
use SprykerEco\Client\Vertex\VertexClientInterface;
use SprykerEco\Glue\Vertex\Processor\Validator\TaxIdValidator;
use SprykerEco\Glue\Vertex\Processor\Validator\TaxIdValidatorInterface;

class VertexFactory extends AbstractFactory
{
    /**
     * @return \SprykerEco\Glue\Vertex\Processor\Validator\TaxIdValidatorInterface
     */
    public function createTaxIdValidator(): TaxIdValidatorInterface
    {
        return new TaxIdValidator(
            $this->getResourceBuilder(),
            $this->getVertexClient(),
            $this->getGlossaryStorageClient(),
        );
    }

    public function getVertexClient(): VertexClientInterface
    {
        return $this->getProvidedDependency(VertexDependencyProvider::CLIENT_VERTEX);
    }

    public function getGlossaryStorageClient(): GlossaryStorageClientInterface
    {
        return $this->getProvidedDependency(VertexDependencyProvider::CLIENT_GLOSSARY_STORAGE);
    }
}
