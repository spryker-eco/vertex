<?php

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerEco\Client\Vertex;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Promise\Create;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use SprykerEco\Client\Vertex\Api\V2\Builder\VertexSuppliesApiRequestBuilder;
use SprykerEco\Client\Vertex\Api\V2\Client\SecurityApi;
use SprykerEco\Client\Vertex\Api\V2\Client\SecurityApiInterface;
use SprykerEco\Client\Vertex\Api\V2\Client\SuppliesApi;
use SprykerEco\Client\Vertex\Api\V2\Client\SuppliesApiInterface;
use SprykerEco\Client\Vertex\Api\V2\Client\TaxamoApi;
use SprykerEco\Client\Vertex\Authenticator\VertexApiAuthenticator;
use SprykerEco\Client\Vertex\Authenticator\VertexApiAuthenticatorInterface;
use SprykerEco\Client\Vertex\Builder\LocationMapper;
use SprykerEco\Client\Vertex\Builder\PriceConverter;
use SprykerEco\Client\Vertex\Builder\Supplies\LineItem\VertexLineItemCustomerBuilder;
use SprykerEco\Client\Vertex\Builder\Supplies\LineItem\VertexLineItemDiscountBuilder;
use SprykerEco\Client\Vertex\Builder\Supplies\LineItem\VertexLineItemIdBuilder;
use SprykerEco\Client\Vertex\Builder\Supplies\LineItem\VertexLineItemMetadataBuilder;
use SprykerEco\Client\Vertex\Builder\Supplies\LineItem\VertexLineItemPriceBuilder;
use SprykerEco\Client\Vertex\Builder\Supplies\LineItem\VertexLineItemProductBuilder;
use SprykerEco\Client\Vertex\Builder\Supplies\LineItem\VertexLineItemQuantityBuilder;
use SprykerEco\Client\Vertex\Builder\Supplies\LineItem\VertexLineItemSellerBuilder;
use SprykerEco\Client\Vertex\Builder\Supplies\LineItem\VertexLineItemVendorSkuBuilder;
use SprykerEco\Client\Vertex\Builder\Supplies\VertexSuppliesDefaultsBuilder;
use SprykerEco\Client\Vertex\Builder\Supplies\VertexSuppliesDocumentDateBuilder;
use SprykerEco\Client\Vertex\Builder\Supplies\VertexSuppliesDocumentNumberBuilder;
use SprykerEco\Client\Vertex\Builder\Supplies\VertexSuppliesInvoiceSaleMessageTypeBuilder;
use SprykerEco\Client\Vertex\Builder\Supplies\VertexSuppliesLineItemsBuilder;
use SprykerEco\Client\Vertex\Builder\Supplies\VertexSuppliesMetadataBuilder;
use SprykerEco\Client\Vertex\Builder\Supplies\VertexSuppliesPostingDateBuilder;
use SprykerEco\Client\Vertex\Builder\Supplies\VertexSuppliesQuotationSaleMessageTypeBuilder;
use SprykerEco\Client\Vertex\Builder\Supplies\VertexSuppliesShipmentBuilder;
use SprykerEco\Client\Vertex\Builder\Supplies\VertexSuppliesTransactionIdBuilder;
use SprykerEco\Client\Vertex\Builder\Supplies\VertexSuppliesTransactionTypeBuilder;
use SprykerEco\Client\Vertex\Builder\SuppliesRequestBuilder;
use SprykerEco\Client\Vertex\Builder\VertexLineItemBuilderInterface;
use SprykerEco\Client\Vertex\Builder\VertexSuppliesRequestBuilderInterface;
use SprykerEco\Client\Vertex\HttpClient\FilteringMessageFormatter;
use SprykerEco\Client\Vertex\HttpClient\MessageFormatterInterface;
use SprykerEco\Client\Vertex\ResponseBuilder\VertexSuppliesResponseBuilder;
use SprykerEco\Client\Vertex\ResponseBuilder\VertexSuppliesResponseBuilderInterface;
use SprykerEco\Client\Vertex\TaxCalculator\VertexTaxCalculator;
use SprykerEco\Client\Vertex\TaxCalculator\VertexTaxCalculatorInterface;
use SprykerEco\Client\Vertex\Validator\VertexInvoiceValidator;
use SprykerEco\Client\Vertex\Validator\VertexQuotationValidator;
use SprykerEco\Client\Vertex\Validator\VertexAddressValidator;
use SprykerEco\Client\Vertex\Validator\VertexAddressValidatorInterface;
use SprykerEco\Client\Vertex\Validator\VertexItemValidator;
use SprykerEco\Client\Vertex\Validator\VertexItemValidatorInterface;
use SprykerEco\Client\Vertex\Validator\VertexSaleValidator;
use SprykerEco\Client\Vertex\Validator\VertexSaleValidatorInterface;
use SprykerEco\Client\Vertex\Validator\VertexShipmentValidator;
use SprykerEco\Client\Vertex\Validator\VertexShipmentValidatorInterface;
use SprykerEco\Client\Vertex\Validator\VertexShippingWarehouseValidator;
use SprykerEco\Client\Vertex\Validator\VertexShippingWarehouseValidatorInterface;
use SprykerEco\Client\Vertex\Validator\VertexValidatorInterface;
use SprykerEco\Client\Vertex\Validator\VertexTaxIdValidator;
use Spryker\Service\UtilEncoding\UtilEncodingServiceInterface;
use Spryker\Shared\Log\LoggerTrait;
use Spryker\Client\Kernel\AbstractFactory;
use Spryker\Client\ZedRequest\ZedRequestClientInterface;
use SprykerEco\Client\Vertex\Validator\VertexTaxIdValidatorInterface;
use SprykerEco\Client\Vertex\Zed\VertexStub;
use SprykerEco\Client\Vertex\Zed\VertexStubInterface;

/**
 * @method \SprykerEco\Client\Vertex\VertexConfig getConfig()
 */
class VertexFactory extends AbstractFactory
{
    use LoggerTrait;

    /**
     * @return \SprykerEco\Client\Vertex\Authenticator\VertexApiAuthenticatorInterface
     */
    public function createVertexApiAuthenticator(): VertexApiAuthenticatorInterface
    {
        return new VertexApiAuthenticator(
            $this->createSecurityApi(),
        );
    }

    /**
     * @return \SprykerEco\Client\Vertex\TaxCalculator\VertexTaxCalculatorInterface
     */
    public function createVertexTaxCalculator(): VertexTaxCalculatorInterface
    {
        return new VertexTaxCalculator(
            $this->createSuppliesQuotationRequestBuilder(),
            $this->createSuppliesApi(),
            $this->createVertexSuppliesResponseBuilder(),
            $this->createVertexQuotationValidator(),
        );
    }

    public function createVertexQuotationValidator(): VertexValidatorInterface
    {
        return new VertexQuotationValidator($this->createVertexSaleValidator());
    }

    public function createVertexSaleValidator(): VertexSaleValidatorInterface
    {
        return new VertexSaleValidator(
            $this->createVertexItemValidator(),
            $this->createVertexShipmentValidator(),
        );
    }

    public function createVertexItemValidator(): VertexItemValidatorInterface
    {
        return new VertexItemValidator(
            $this->createVertexAddressValidator(),
            $this->createVertexShippingWarehouseValidator(),
        );
    }

    public function createVertexShipmentValidator(): VertexShipmentValidatorInterface
    {
        return new VertexShipmentValidator($this->createVertexAddressValidator());
    }

    public function createVertexAddressValidator(): VertexAddressValidatorInterface
    {
        return new VertexAddressValidator();
    }

    public function createVertexShippingWarehouseValidator(): VertexShippingWarehouseValidatorInterface
    {
        return new VertexShippingWarehouseValidator($this->createVertexAddressValidator());
    }

    public function createInvoiceVertexTaxCalculator(): VertexTaxCalculatorInterface
    {
        return new VertexTaxCalculator(
            $this->createSuppliesInvoiceRequestBuilder(),
            $this->createSuppliesApi(),
            $this->createVertexSuppliesResponseBuilder(),
            $this->createVertexInvoiceValidator(),
        );
    }

    public function createVertexInvoiceValidator(): VertexValidatorInterface
    {
        return new VertexInvoiceValidator($this->createVertexSaleValidator());
    }

    public function createVertexTaxIdValidator(): VertexTaxIdValidatorInterface
    {
        return new VertexTaxIdValidator(
            $this->createTaxamoApi(),
        );
    }

    public function createTaxamoApi(): TaxamoApi
    {
        return new TaxamoApi(
            $this->createHttpClient(),
            $this->getUtilEncodingService(),
        );
    }

    public function createVertexSuppliesResponseBuilder(): VertexSuppliesResponseBuilderInterface
    {
        return new VertexSuppliesResponseBuilder($this->createPriceConverter());
    }

    public function createSecurityApi(): SecurityApiInterface
    {
        return new SecurityApi(
            $this->createHttpClient(),
        );
    }

    public function createSuppliesApi(): SuppliesApiInterface
    {
        return new SuppliesApi(
            $this->createHttpClient(),
            $this->createVertexSuppliesApiRequestBuilder(),
            $this->getUtilEncodingService(),
        );
    }

    protected function createVertexSuppliesApiRequestBuilder(): VertexSuppliesApiRequestBuilder
    {
        return new VertexSuppliesApiRequestBuilder();
    }

    protected function getUtilEncodingService(): UtilEncodingServiceInterface
    {
        return $this->getProvidedDependency(VertexDependencyProvider::SERVICE_UTIL_ENCODING);
    }

    /**
     * @codeCoverageIgnore We can't use the real client for any of the tests.
     *
     * @return \GuzzleHttp\ClientInterface
     */
    public function createHttpClient(): ClientInterface
    {
        $handlerStack = HandlerStack::create();
        $handlerStack->push(
            $this->getLogMiddleware(
                $this->getLogger(),
                $this->createFilteringMessageFormatter(),
            ),
        );

        return new Client([
            'handler' => $handlerStack,
            RequestOptions::TIMEOUT => VertexConfig::REQUEST_TIMEOUT,
            RequestOptions::CONNECT_TIMEOUT => VertexConfig::REQUEST_CONNECT_TIMEOUT,
        ]);
    }

    public function createFilteringMessageFormatter(): MessageFormatterInterface
    {
        return new FilteringMessageFormatter();
    }

    protected function createLocationMapper(): LocationMapper
    {
        return new LocationMapper();
    }

    protected function createPriceConverter(): PriceConverter
    {
        return new PriceConverter();
    }

    public function createSuppliesQuotationRequestBuilder(): SuppliesRequestBuilder
    {
        return new SuppliesRequestBuilder([
            $this->createVertexSuppliesDefaultsBuilder(),
            $this->createVertexSuppliesTransactionIdBuilder(),
            $this->createVertexSuppliesTransactionTypeBuilder(),
            $this->createVertexSuppliesQuotationSaleMessageTypeBuilder(),
            $this->createVertexSuppliesMetadataBuilder(),
            $this->createVertexSuppliesItemsBuilder(),
            $this->createVertexSuppliesShipmentBuilder(),
        ]);
    }

    public function createSuppliesInvoiceRequestBuilder(): SuppliesRequestBuilder
    {
        return new SuppliesRequestBuilder([
            $this->createVertexSuppliesDefaultsBuilder(),
            $this->createVertexSuppliesDocumentNumberBuilder(),
            $this->createVertexSuppliesDocumentDateBuilder(),
            $this->createVertexSuppliesPostingDateBuilder(),
            $this->createVertexSuppliesTransactionIdBuilder(),
            $this->createVertexSuppliesTransactionTypeBuilder(),
            $this->createVertexSuppliesInvoiceSaleMessageTypeBuilder(),
            $this->createVertexSuppliesMetadataBuilder(),
            $this->createVertexSuppliesItemsBuilder(),
            $this->createVertexSuppliesShipmentBuilder(),
        ]);
    }

    protected function createVertexSuppliesDefaultsBuilder(): VertexSuppliesRequestBuilderInterface
    {
        return new VertexSuppliesDefaultsBuilder();
    }

    protected function createVertexSuppliesTransactionTypeBuilder(): VertexSuppliesRequestBuilderInterface
    {
        return new VertexSuppliesTransactionTypeBuilder();
    }

    protected function createVertexSuppliesTransactionIdBuilder(): VertexSuppliesRequestBuilderInterface
    {
        return new VertexSuppliesTransactionIdBuilder();
    }

    protected function createVertexSuppliesDocumentNumberBuilder(): VertexSuppliesRequestBuilderInterface
    {
        return new VertexSuppliesDocumentNumberBuilder();
    }

    protected function createVertexSuppliesDocumentDateBuilder(): VertexSuppliesRequestBuilderInterface
    {
        return new VertexSuppliesDocumentDateBuilder();
    }

    protected function createVertexSuppliesPostingDateBuilder(): VertexSuppliesRequestBuilderInterface
    {
        return new VertexSuppliesPostingDateBuilder();
    }

    protected function createVertexSuppliesQuotationSaleMessageTypeBuilder(): VertexSuppliesRequestBuilderInterface
    {
        return new VertexSuppliesQuotationSaleMessageTypeBuilder();
    }

    protected function createVertexSuppliesInvoiceSaleMessageTypeBuilder(): VertexSuppliesRequestBuilderInterface
    {
        return new VertexSuppliesInvoiceSaleMessageTypeBuilder();
    }

    protected function createVertexSuppliesMetadataBuilder(): VertexSuppliesRequestBuilderInterface
    {
        return new VertexSuppliesMetadataBuilder();
    }

    protected function createVertexSuppliesItemsBuilder(): VertexSuppliesRequestBuilderInterface
    {
        return new VertexSuppliesLineItemsBuilder([
            $this->createVertexLineItemIdBuilder(),
            $this->createVertexLineItemVendorSkuBuilder(),
            $this->createVertexLineItemProductBuilder(),
            $this->createVertexLineItemPriceBuilder(),
            $this->createVertexLineItemDiscountBuilder(),
            $this->createVertexLineItemQuantityBuilder(),
            $this->createVertexLineItemCustomerBuilder(),
            $this->createVertexLineItemSellerBuilder(),
            $this->createVertexLineItemMetadataBuilder(),
        ]);
    }

    protected function createVertexSuppliesShipmentBuilder(): VertexSuppliesRequestBuilderInterface
    {
        return new VertexSuppliesShipmentBuilder([
            $this->createVertexLineItemIdBuilder(),
            $this->createVertexLineItemProductBuilder(),
            $this->createVertexLineItemPriceBuilder(),
            $this->createVertexLineItemDiscountBuilder(),
            $this->createVertexLineItemCustomerBuilder(),
        ]);
    }

    protected function createVertexLineItemIdBuilder(): VertexLineItemBuilderInterface
    {
        return new VertexLineItemIdBuilder();
    }

    protected function createVertexLineItemVendorSkuBuilder(): VertexLineItemBuilderInterface
    {
        return new VertexLineItemVendorSkuBuilder();
    }

    /**
     * @return \SprykerEco\Client\Vertex\Builder\VertexLineItemBuilderInterface
     */
    protected function createVertexLineItemProductBuilder(): VertexLineItemBuilderInterface
    {
        return new VertexLineItemProductBuilder();
    }

    /**
     * @return \SprykerEco\Client\Vertex\Builder\VertexLineItemBuilderInterface
     */
    protected function createVertexLineItemPriceBuilder(): VertexLineItemBuilderInterface
    {
        return new VertexLineItemPriceBuilder($this->createPriceConverter());
    }

    /**
     * @return \SprykerEco\Client\Vertex\Builder\VertexLineItemBuilderInterface
     */
    protected function createVertexLineItemDiscountBuilder(): VertexLineItemBuilderInterface
    {
        return new VertexLineItemDiscountBuilder($this->createPriceConverter());
    }

    /**
     * @return \SprykerEco\Client\Vertex\Builder\VertexLineItemBuilderInterface
     */
    protected function createVertexLineItemQuantityBuilder(): VertexLineItemBuilderInterface
    {
        return new VertexLineItemQuantityBuilder();
    }

    /**
     * @return \SprykerEco\Client\Vertex\Builder\VertexLineItemBuilderInterface
     */
    protected function createVertexLineItemCustomerBuilder(): VertexLineItemBuilderInterface
    {
        return new VertexLineItemCustomerBuilder($this->createLocationMapper());
    }

    /**
     * @return \SprykerEco\Client\Vertex\Builder\VertexLineItemBuilderInterface
     */
    protected function createVertexLineItemSellerBuilder(): VertexLineItemBuilderInterface
    {
        return new VertexLineItemSellerBuilder($this->createLocationMapper());
    }

    /**
     * @return \SprykerEco\Client\Vertex\Builder\VertexLineItemBuilderInterface
     */
    protected function createVertexLineItemMetadataBuilder(): VertexLineItemBuilderInterface
    {
        return new VertexLineItemMetadataBuilder();
    }


    /**
     * @codeCoverageIgnore We can't use the real client for any of the tests.
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @param \SprykerEco\Client\Vertex\HttpClient\MessageFormatterInterface $messageFormatter
     *
     * @return callable
     */
    protected function getLogMiddleware(LoggerInterface $logger, MessageFormatterInterface $messageFormatter): callable
    {
        return static function (callable $handler) use ($logger, $messageFormatter): callable {
            return static function (RequestInterface $request, array $options = []) use ($handler, $logger, $messageFormatter) {
                return $handler($request, $options)->then(
                    static function ($response) use ($logger, $request, $messageFormatter): ResponseInterface {
                        $message = $messageFormatter->format($request, $response);
                        $context = $messageFormatter->extractContext($request, $response);
                        $logger->info($message, $context);

                        return $response;
                    },
                    static function ($reason) use ($logger, $request, $messageFormatter): PromiseInterface {
                        $response = $reason instanceof RequestException ? $reason->getResponse() : null;
                        $exception = Create::exceptionFor($reason);
                        $message = $messageFormatter->format($request, $response, $exception);
                        $context = $messageFormatter->extractContext($request, $response, $exception);
                        $logger->error($message, $context);

                        return Create::rejectionFor($reason);
                    },
                );
            };
        };
    }

    /**
     * @return \Spryker\Client\ZedRequest\ZedRequestClientInterface
     */
    public function getZedRequestClient(): ZedRequestClientInterface
    {
        return $this->getProvidedDependency(VertexDependencyProvider::CLIENT_ZED_REQUEST);
    }

    /**
     * @return \SprykerEco\Client\Vertex\Zed\VertexStubInterface
     */
    public function createZedStub(): VertexStubInterface
    {
        return new VertexStub(
            $this->getZedRequestClient(),
        );
    }
}
