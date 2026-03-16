<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Glue\Vertex;

use Spryker\Client\GlossaryStorage\GlossaryStorageClientInterface;
use Spryker\Glue\Kernel\AbstractFactory;
use SprykerEco\Client\Vertex\VertexClientInterface;
use SprykerEco\Glue\Vertex\Processor\Validator\TaxIdValidator;
use SprykerEco\Glue\Vertex\Processor\Validator\TaxIdValidatorInterface;

/**
 * @method \SprykerEco\Glue\Vertex\VertexConfig getConfig()
 */
class VertexFactory extends AbstractFactory
{
    public function createTaxIdValidator(): TaxIdValidatorInterface
    {
        return new TaxIdValidator(
            $this->getResourceBuilder(),
            $this->getVertexClient(),
            $this->getGlossaryStorageClient(),
            $this->getConfig(),
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
