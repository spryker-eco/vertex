<?php

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerEco\Client\Vertex\Builder\Supplies;

use Generated\Shared\Transfer\TaxCalculationRequestTransfer;
use Generated\Shared\Transfer\VertexLocationTransfer;
use Generated\Shared\Transfer\VertexSellerTransfer;
use Generated\Shared\Transfer\VertexSuppliesTransfer;
use SprykerEco\Client\Vertex\Builder\VertexSuppliesRequestBuilderInterface;

class VertexSuppliesDefaultsBuilder implements VertexSuppliesRequestBuilderInterface
{
    /**
     * @param \Generated\Shared\Transfer\TaxCalculationRequestTransfer $taxCalculationRequestTransfer
     * @param \Generated\Shared\Transfer\VertexSuppliesTransfer $vertexSuppliesTransfer
     *
     * @return \Generated\Shared\Transfer\VertexSuppliesTransfer
     */
    public function build(
        TaxCalculationRequestTransfer $taxCalculationRequestTransfer,
        VertexSuppliesTransfer $vertexSuppliesTransfer
    ): VertexSuppliesTransfer {
        $vertexConfigTransfer = $taxCalculationRequestTransfer->getVertexConfigurationOrFail();
        $saleTransfer = $taxCalculationRequestTransfer->getSaleOrFail();

        $seller = $vertexSuppliesTransfer->getSeller() ?? new VertexSellerTransfer();

        if (!$seller->getCompany() && strlen($vertexConfigTransfer->getDefaultTaxpayerCompanyCode()) > 0) {
            $seller->setCompany($vertexConfigTransfer->getDefaultTaxpayerCompanyCode());
        }

        if ($saleTransfer->getSellerCountryCode()) {
            //by default these values are USA, https://tax-calc-api.vertexcloud.com/api/docs/index.html#operation/salePost
            $seller->setAdministrativeOrigin((new VertexLocationTransfer())->setCountry($saleTransfer->getSellerCountryCodeOrFail()));
            $seller->setPhysicalOrigin((new VertexLocationTransfer())->setCountry($saleTransfer->getSellerCountryCodeOrFail()));
        }

        $vertexSuppliesTransfer->setSeller($seller);
        $vertexSuppliesTransfer->setReturnAssistedParametersIndicator($taxCalculationRequestTransfer->getVertexConfigurationOrFail()->getIsTaxAssistEnabledOrFail());

        return $vertexSuppliesTransfer;
    }
}
