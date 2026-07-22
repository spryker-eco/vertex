<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Zed\Vertex\Business\Configuration;

use Generated\Shared\Transfer\VertexConfigTransfer;

interface VertexConfigTransferBuilderInterface
{
    /**
     * @param array<\Generated\Shared\Transfer\ConfigurationScopeTransfer> $configurationScopeTransfers
     * @param array<string, mixed> $requestValuesBySettingKey
     */
    public function build(
        array $configurationScopeTransfers,
        array $requestValuesBySettingKey = [],
    ): VertexConfigTransfer;

    /**
     * @param array<\Generated\Shared\Transfer\ConfigurationScopeTransfer> $configurationScopeTransfers
     */
    public function getStoredCredentialValue(string $settingKey, array $configurationScopeTransfers): string;

    /**
     * @return array<\Generated\Shared\Transfer\ConfigurationScopeTransfer>
     */
    public function createConfigurationScopeTransfersFromScope(?string $scope, ?string $scopeIdentifier): array;
}
