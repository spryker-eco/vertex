<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerEco\Client\Vertex\Builder\Supplies;

use Generated\Shared\Transfer\VertexCalculationRequestTransfer;
use Generated\Shared\Transfer\VertexLocationTransfer;
use Generated\Shared\Transfer\VertexSellerTransfer;
use Generated\Shared\Transfer\VertexSuppliesTransfer;
use SprykerEco\Client\Vertex\Builder\VertexSuppliesRequestBuilderInterface;

class VertexSuppliesDefaultsBuilder implements VertexSuppliesRequestBuilderInterface
{
    public function build(
        VertexCalculationRequestTransfer $vertexCalculationRequestTransfer,
        VertexSuppliesTransfer $vertexSuppliesTransfer,
    ): VertexSuppliesTransfer {
        $vertexConfigTransfer = $vertexCalculationRequestTransfer->getVertexConfigurationOrFail();
        $saleTransfer = $vertexCalculationRequestTransfer->getSaleOrFail();

        $seller = $vertexSuppliesTransfer->getSeller() ?? new VertexSellerTransfer();

        if (!$seller->getCompany() && strlen($vertexConfigTransfer->getDefaultTaxpayerCompanyCodeOrFail()) > 0) {
            $seller->setCompany($vertexConfigTransfer->getDefaultTaxpayerCompanyCode());
        }

        if ($saleTransfer->getSellerCountryCode()) {
            //by default these values are USA, https://tax-calc-api.vertexcloud.com/api/docs/index.html#operation/salePost
            $seller->setAdministrativeOrigin((new VertexLocationTransfer())->setCountry($saleTransfer->getSellerCountryCodeOrFail()));
            $seller->setPhysicalOrigin((new VertexLocationTransfer())->setCountry($saleTransfer->getSellerCountryCodeOrFail()));
        }

        $vertexSuppliesTransfer->setSeller($seller);
        $vertexSuppliesTransfer->setReturnAssistedParametersIndicator($vertexCalculationRequestTransfer->getVertexConfigurationOrFail()->getIsTaxAssistEnabledOrFail());

        return $vertexSuppliesTransfer;
    }
}
