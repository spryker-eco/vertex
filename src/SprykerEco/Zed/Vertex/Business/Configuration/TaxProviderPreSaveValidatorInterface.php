<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Zed\Vertex\Business\Configuration;

use Generated\Shared\Transfer\ConfigurationValueCollectionRequestTransfer;

interface TaxProviderPreSaveValidatorInterface
{
    /**
     * Marks a Vertex tax provider selection as invalid when Vertex is not fully configured for the selection scope.
     */
    public function validate(
        ConfigurationValueCollectionRequestTransfer $configurationValueCollectionRequestTransfer,
    ): ConfigurationValueCollectionRequestTransfer;
}
