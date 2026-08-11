<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Zed\Vertex\Business\Configuration;

use Generated\Shared\Transfer\ConfigurationValueCollectionRequestTransfer;

class TaxProviderPreSaveValidator implements TaxProviderPreSaveValidatorInterface
{
    public function __construct(
        protected VertexTaxProviderSelectionGuardInterface $vertexTaxProviderSelectionGuard,
        protected VertexConfigurationCompletenessGuardInterface $vertexConfigurationCompletenessGuard,
    ) {
    }

    public function validate(
        ConfigurationValueCollectionRequestTransfer $configurationValueCollectionRequestTransfer,
    ): ConfigurationValueCollectionRequestTransfer {
        $this->vertexTaxProviderSelectionGuard->guard($configurationValueCollectionRequestTransfer);
        $this->vertexConfigurationCompletenessGuard->guard($configurationValueCollectionRequestTransfer);

        return $configurationValueCollectionRequestTransfer;
    }
}
