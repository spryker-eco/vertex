<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Zed\Vertex\Business\Configuration;

use Spryker\Shared\Store\StoreConstants;
use SprykerEco\Shared\Vertex\VertexConfig as SharedVertexConfig;

class VertexSentinelEncoder implements VertexSentinelEncoderInterface
{
    /**
     * @param array<string> $reasons
     */
    public function encodeIncompleteSentinel(string $case, string $scope, array $reasons): string
    {
        return SharedVertexConfig::VERTEX_CONFIGURATION_INCOMPLETE_SENTINEL . json_encode([
            SharedVertexConfig::VERTEX_CONFIGURATION_INCOMPLETE_PAYLOAD_KEY_CASE => $case,
            SharedVertexConfig::VERTEX_CONFIGURATION_INCOMPLETE_PAYLOAD_KEY_SCOPE => $scope,
            SharedVertexConfig::VERTEX_CONFIGURATION_INCOMPLETE_PAYLOAD_KEY_REASONS => array_values($reasons),
        ]);
    }

    /**
     * @param array<string> $reasons
     */
    public function encodeTaxProviderNotConfiguredSentinel(array $reasons): string
    {
        return SharedVertexConfig::TAX_PROVIDER_NOT_CONFIGURED_SENTINEL . json_encode(array_values($reasons));
    }

    public function getScopeLabel(?string $scope, ?string $scopeIdentifier): string
    {
        if ($scope === StoreConstants::SCOPE_STORE && $scopeIdentifier !== null && $scopeIdentifier !== '') {
            return 'store ' . $scopeIdentifier;
        }

        return 'the global (Default) scope';
    }
}
