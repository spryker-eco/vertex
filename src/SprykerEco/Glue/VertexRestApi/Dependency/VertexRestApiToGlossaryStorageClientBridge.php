<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Glue\VertexRestApi\Dependency;

use Spryker\Client\GlossaryStorage\GlossaryStorageClientInterface;

class VertexRestApiToGlossaryStorageClientBridge implements VertexRestApiToGlossaryStorageClientInterface
{
    /**
     * @param \Spryker\Client\GlossaryStorage\GlossaryStorageClientInterface $glossaryStorageClient
     */
    public function __construct(protected GlossaryStorageClientInterface $glossaryStorageClient)
    {
    }

    /**
     * @param string $id
     * @param string $localeName
     * @param array<string, mixed> $parameters
     *
     * @return string
     */
    public function translate(string $id, string $localeName, array $parameters = []): string
    {
        return $this->glossaryStorageClient->translate($id, $localeName, $parameters);
    }
}
