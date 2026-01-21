<?php

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerEco\Zed\Vertex\Business\SecretsManager;

interface SecretsManagerInterface
{
    /**
     * @param string $tenantIdentifier
     *
     * @return string|null
     */
    public function getPassphrase(string $tenantIdentifier): ?string;
}
