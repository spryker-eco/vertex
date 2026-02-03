<?php

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerEcoTest\Shared\Vertex\Helper;

use Codeception\Module;
use Generated\Shared\DataBuilder\SaleBuilder;
use Generated\Shared\DataBuilder\SaleItemBuilder;
use Generated\Shared\DataBuilder\ShipmentBuilder;
use Generated\Shared\DataBuilder\ShippingWarehouseBuilder;
use Generated\Shared\DataBuilder\SubmitPaymentTaxInvoiceBuilder;
use Generated\Shared\DataBuilder\TaxamoApiRequestBuilder;
use Generated\Shared\DataBuilder\TaxCalculationRequestBuilder;
use Generated\Shared\DataBuilder\TaxIdValidationRequestBuilder;
use Generated\Shared\DataBuilder\VertexApiAccessTokenBuilder;
use Generated\Shared\DataBuilder\VertexApiAuthResponseBuilder;
use Generated\Shared\DataBuilder\VertexApiCredentialBuilder;
use Generated\Shared\DataBuilder\VertexCalculationRequestBuilder;
use Generated\Shared\DataBuilder\VertexConfigBuilder;
use Generated\Shared\DataBuilder\VertexItemBuilder;
use Generated\Shared\DataBuilder\VertexSaleBuilder;
use Generated\Shared\DataBuilder\VertexShipmentBuilder;
use Generated\Shared\DataBuilder\VertexShippingWarehouseBuilder;
use Generated\Shared\Transfer\SaleItemTransfer;
use Generated\Shared\Transfer\ShippingWarehouseTransfer;
use Generated\Shared\Transfer\SubmitPaymentTaxInvoiceTransfer;
use Generated\Shared\Transfer\TaxamoApiRequestTransfer;
use Generated\Shared\Transfer\TaxCalculationRequestTransfer;
use Generated\Shared\Transfer\TaxIdValidationRequestTransfer;
use Generated\Shared\Transfer\VertexApiAccessTokenTransfer;
use Generated\Shared\Transfer\VertexApiCredentialTransfer;
use Generated\Shared\Transfer\VertexAuthResponseTransfer;
use Generated\Shared\Transfer\VertexCalculationRequestTransfer;
use Generated\Shared\Transfer\VertexConfigTransfer;
use Generated\Shared\Transfer\VertexItemTransfer;
use Generated\Shared\Transfer\VertexShippingWarehouseTransfer;
use Orm\Zed\Vertex\Persistence\SpyVertexApiAccessToken;

class VertexApiHelper extends Module
{
    /**
     * @param array $seed
     *
     * @return \Generated\Shared\Transfer\VertexApiCredentialTransfer
     */
    public function haveVertexApiCredentialTransfer(array $seed = []): VertexApiCredentialTransfer
    {
        return (new VertexApiCredentialBuilder($seed))->build();
    }

    /**
     * @param array $seed
     *
     * @return \Generated\Shared\Transfer\VertexAuthResponseTransfer
     */
    public function haveVertexApiAuthResponseTokenTransfer(array $seed = []): VertexAuthResponseTransfer
    {
        return (new VertexAuthResponseBuilder($seed))->build();
    }

    /**
     * @param array $seed
     *
     * @return \Generated\Shared\Transfer\VertexApiAccessTokenTransfer
     */
    public function haveVertexApiAccessToken(array $seed = []): VertexApiAccessTokenTransfer
    {
        return (new VertexApiAccessTokenBuilder($seed))->build();
    }

    /**
     * @param array $seed
     *
     * @return \Generated\Shared\Transfer\VertexApiAccessTokenTransfer
     */
    public function haveVertexApiAccessTokenPersisted(array $seed = []): VertexApiAccessTokenTransfer
    {
        $vertexApiAccessTokenTransfer = (new VertexApiAccessTokenBuilder($seed))->build();

        $spyVertexApiCredential = new SpyVertexApiAccessToken();
        $spyVertexApiCredential->fromArray($vertexApiAccessTokenTransfer->modifiedToArray());
        $spyVertexApiCredential->save();

        return $vertexApiAccessTokenTransfer;
    }

    /**
     * @param array $seed
     *
     * @return \Generated\Shared\Transfer\VertexCalculationRequestTransfer
     */
    public function haveVertexCalculationRequestTransfer(array $seed = []): VertexCalculationRequestTransfer
    {
        return (new VertexCalculationRequestBuilder($seed))
            ->withSale(
                (new VertexSaleBuilder())
                    ->withItem(
                        (new VertexItemBuilder())
                            ->withShippingAddress()
                            ->withBillingAddress()
                            ->withSellerAddress(),
                    )
                    ->withShipment(
                        (new VertexShipmentBuilder())
                            ->withShippingAddress()
                            ->withBillingAddress(),
                    ),
            )
            ->withVertexConfiguration()
            ->withVertexApiAccessToken()
            ->build();
    }

    /**
     * @param array $seed
     *
     * @return \Generated\Shared\Transfer\VertexCalculationRequestTransfer
     */
    public function haveVertexCalculationRequestTransferWithWarehouseMapping(array $seed = []): VertexCalculationRequestTransfer
    {
        return (new VertexCalculationRequestBuilder($seed))
            ->withSale(
                (new VertexSaleBuilder())
                    ->withItem(
                        (new VertexItemBuilder([
                            VertexItemTransfer::QUANTITY => 3,
                        ]))
                            ->withShippingAddress()
                            ->withBillingAddress()
                            ->withSellerAddress()
                            ->withVertexShippingWarehouse(
                                (new VertexShippingWarehouseBuilder([
                                    VertexShippingWarehouseTransfer::QUANTITY => 2,
                                ]))
                                    ->withWarehouseAddress(),
                            )
                            ->withAnotherVertexShippingWarehouse(
                                (new VertexShippingWarehouseBuilder([
                                    VertexShippingWarehouseTransfer::QUANTITY => 1,
                                ]))
                                    ->withWarehouseAddress(),
                            ),
                    )
                    ->withShipment(
                        (new VertexShipmentBuilder())
                            ->withShippingAddress()
                            ->withBillingAddress(),
                    ),
            )
            ->withVertexConfiguration()
            ->build();
    }

    /**
     * @param array $seed
     *
     * @return \Generated\Shared\Transfer\SubmitPaymentTaxInvoiceTransfer
     */
    public function haveSubmitPaymentTaxInvoiceTransfer(array $seed = []): SubmitPaymentTaxInvoiceTransfer
    {
        return (new SubmitPaymentTaxInvoiceBuilder($seed))
            ->withSale(
                (new SaleBuilder())
                    ->withItem(
                        (new SaleItemBuilder())
                            ->withShippingAddress()
                            ->withBillingAddress()
                            ->withSellerAddress(),
                    )
                    ->withShipment(
                        (new ShipmentBuilder())
                            ->withShippingAddress()
                            ->withBillingAddress(),
                    ),
            )
            ->build();
    }

    /**
     * @param array $seed
     *
     * @return \Generated\Shared\Transfer\VertexCalculationRequestTransfer
     */
    public function haveVertexCalculationRequestTransferForRefunds(array $seed = []): VertexCalculationRequestTransfer
    {
        $taxCalculationRequestTransfer = (new VertexCalculationRequestBuilder($seed))
            ->withSale(
                (new VertexSaleBuilder())
                    ->withItem(
                        (new VertexItemBuilder())
                            ->withShippingAddress()
                            ->withBillingAddress()
                            ->withSellerAddress(),
                    )
                    ->withShipment(
                        (new VertexShipmentBuilder())
                            ->withShippingAddress()
                            ->withBillingAddress(),
                    ),
            )
            ->withVertexConfiguration()
            ->build();

        $taxCalculationRequestTransfer->setReportingDate('2021-01-01');

        foreach ($taxCalculationRequestTransfer->getSale()->getItems() as $item) {
            $item->setRefundableAmount($item->getPriceAmount());
        }

        foreach ($taxCalculationRequestTransfer->getSale()->getShipments() as $shipment) {
            $shipment->setRefundableAmount($shipment->getPriceAmount());
        }

        return $taxCalculationRequestTransfer;
    }

    
    /**
     * @param array $seed
     *
     * @return \Generated\Shared\Transfer\VertexConfigTransfer
     */
    public function haveVertexConfig(array $seed = []): VertexConfigTransfer
    {
        return (new VertexConfigBuilder($seed))
            ->build();
    }
    
    /**
     * @param array $seed
     *
     * @return \Generated\Shared\Transfer\TaxIdValidationRequestTransfer
     */
    public function haveTaxIdValidationRequestTransfer(array $seed = []): TaxIdValidationRequestTransfer
    {
        return (new TaxIdValidationRequestBuilder($seed))->build();
    }

    /**
     * @param array $seed
     *
     * @return \Generated\Shared\Transfer\TaxamoApiRequestTransfer
     */
    public function createTaxamoApiRequestTransfer(array $seed = []): TaxamoApiRequestTransfer
    {
        return (new TaxamoApiRequestBuilder($seed))->build();
    }
}
