<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Zed\Vertex\Business\Configuration;

interface VertexSentinelEncoderInterface
{
    /**
     * @param array<string> $reasons
     */
    public function encodeIncompleteSentinel(string $case, string $scope, array $reasons): string;

    /**
     * @param array<string> $reasons
     */
    public function encodeTaxProviderNotConfiguredSentinel(array $reasons): string;

    public function getScopeLabel(?string $scope, ?string $scopeIdentifier): string;
}
