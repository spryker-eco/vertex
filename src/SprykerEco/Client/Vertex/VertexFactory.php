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
use SprykerEco\Client\Vertex\AccessTokenProvider\AccessTokenProvider;
use SprykerEco\Client\Vertex\AccessTokenProvider\AccessTokenProviderInterface;
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
use SprykerEco\Client\Vertex\MessageBroker\VertexApiMessageHandler;
use SprykerEco\Client\Vertex\MessageBroker\VertexApiMessageHandlerInterface;
use SprykerEco\Client\Vertex\Refund\VertexApiRefunds;
use SprykerEco\Client\Vertex\Refund\VertexApiRefundsInterface;
use SprykerEco\Client\Vertex\ResponseBuilder\VertexSuppliesResponseBuilder;
use SprykerEco\Client\Vertex\ResponseBuilder\VertexSuppliesResponseBuilderInterface;
use SprykerEco\Client\Vertex\TaxCalculator\VertexTaxCalculator;
use SprykerEco\Client\Vertex\TaxCalculator\VertexTaxCalculatorInterface;
use SprykerEco\Client\Vertex\Validator\VertexTaxIdValidator;
// use Pyz\Zed\VertexApi\VertexApiConfig;
// use Pyz\Zed\VertexApi\VertexApiDependencyProvider;
// use Pyz\Zed\VertexConfig\Business\EncryptionConfigurator\TenantPropelEncryptionConfigurator;
// use Pyz\Zed\VertexConfig\Business\EncryptionConfigurator\TenantPropelEncryptionConfiguratorInterface;
// use Pyz\Zed\VertexConfig\Business\SecretsManager\SecretsManager;
// use Pyz\Zed\VertexConfig\Business\SecretsManager\SecretsManagerInterface;
// use Pyz\Zed\VertexConfig\Business\VertexConfigFacadeInterface;
use Spryker\Client\SecretsManager\SecretsManagerClientInterface;
use Spryker\Service\UtilEncoding\UtilEncodingServiceInterface;
use Spryker\Service\UtilText\UtilTextServiceInterface;
use Spryker\Shared\Log\LoggerTrait;
use Spryker\Client\Kernel\AbstractFactory;

/**
 * @method \SprykerEco\Client\Vertex\VertexConfig getConfig()
 */
class VertexFactory extends AbstractFactory
{
    use LoggerTrait;

    /**
     * @return \Spryker\Client\SecretsManager\SecretsManagerClientInterface
     */
    public function getSecretsManagerClient(): SecretsManagerClientInterface
    {
        return $this->getProvidedDependency(VertexDependencyProvider::CLIENT_SECRETS_MANAGER);
    }

    /**
     * @return \Pyz\Zed\VertexConfig\Business\SecretsManager\SecretsManagerInterface
     */
    public function createSecretsManager(): SecretsManagerInterface
    {
        return new SecretsManager($this->getSecretsManagerClient(), $this->getUtilTextService());
    }

    /**
     * @return \Spryker\Service\UtilText\UtilTextServiceInterface
     */
    public function getUtilTextService(): UtilTextServiceInterface
    {
        return $this->getProvidedDependency(VertexDependencyProvider::SERVICE_UTIL_TEXT);
    }

    /**
     * @return \Pyz\Zed\VertexConfig\Business\EncryptionConfigurator\TenantPropelEncryptionConfiguratorInterface
     */
    public function createTenantPropelEncryptionConfigurator(): TenantPropelEncryptionConfiguratorInterface
    {
        return new TenantPropelEncryptionConfigurator(
            $this->createSecretsManager(),
        );
    }

    /**
     * @return \SprykerEco\Client\Vertex\Authenticator\VertexApiAuthenticatorInterface
     */
    public function createVertexApiAuthenticator(): VertexApiAuthenticatorInterface
    {
        return new VertexApiAuthenticator(
            $this->createSecurityApi(),
            $this->getEntityManager(),
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
            $this->getVertexConfigFacade(),
            $this->createAccessTokenProvider(),
        );
    }

    /**
     * @return \SprykerEco\Client\Vertex\TaxCalculator\VertexTaxCalculatorInterface
     */
    public function createInvoiceVertexTaxCalculator(): VertexTaxCalculatorInterface
    {
        return new VertexTaxCalculator(
            $this->createSuppliesInvoiceRequestBuilder(),
            $this->createSuppliesApi(),
            $this->createVertexSuppliesResponseBuilder(),
            $this->getVertexConfigFacade(),
            $this->createAccessTokenProvider(),
        );
    }

    /**
     * @return \SprykerEco\Client\Vertex\Validator\VertexTaxIdValidator
     */
    public function createVertexTaxIdValidator(): VertexTaxIdValidator
    {
        return new VertexTaxIdValidator(
            $this->createTaxamoApi(),
            $this->getVertexConfigFacade(),
        );
    }

    /**
     * @return \SprykerEco\Client\Vertex\Api\V2\Client\TaxamoApi
     */
    public function createTaxamoApi(): TaxamoApi
    {
        return new TaxamoApi(
            $this->createHttpClient(),
            $this->getUtilEncodingService(),
        );
    }

    /**
     * @return \SprykerEco\Client\Vertex\AccessTokenProvider\AccessTokenProviderInterface
     */
    public function createAccessTokenProvider(): AccessTokenProviderInterface
    {
        return new AccessTokenProvider(
            $this->createVertexApiAuthenticator(),
            $this->getRepository(),
            $this->createTenantPropelEncryptionConfigurator(),
        );
    }

    /**
     * @return \SprykerEco\Client\Vertex\ResponseBuilder\VertexSuppliesResponseBuilderInterface
     */
    public function createVertexSuppliesResponseBuilder(): VertexSuppliesResponseBuilderInterface
    {
        return new VertexSuppliesResponseBuilder($this->createPriceConverter());
    }

    /**
     * @return \SprykerEco\Client\Vertex\Api\V2\Client\SecurityApiInterface
     */
    public function createSecurityApi(): SecurityApiInterface
    {
        return new SecurityApi(
            $this->createHttpClient(),
        );
    }

    /**
     * @return \SprykerEco\Client\Vertex\Api\V2\Client\SuppliesApiInterface
     */
    public function createSuppliesApi(): SuppliesApiInterface
    {
        return new SuppliesApi(
            $this->createHttpClient(),
            $this->createVertexSuppliesApiRequestBuilder(),
            $this->getUtilEncodingService(),
        );
    }

    /**
     * @return \SprykerEco\Client\Vertex\Api\V2\Builder\VertexSuppliesApiRequestBuilder
     */
    protected function createVertexSuppliesApiRequestBuilder(): VertexSuppliesApiRequestBuilder
    {
        return new VertexSuppliesApiRequestBuilder();
    }

    /**
     * @return \Spryker\Service\UtilEncoding\UtilEncodingServiceInterface
     */
    protected function getUtilEncodingService(): UtilEncodingServiceInterface
    {
        return $this->getProvidedDependency(VertexApiDependencyProvider::SERVICE_UTIL_ENCODING);
    }

    /**
     * @return \Pyz\Zed\VertexConfig\Business\VertexConfigFacadeInterface
     */
    protected function getVertexConfigFacade(): VertexConfigFacadeInterface
    {
        return $this->getProvidedDependency(VertexApiDependencyProvider::FACADE_VERTEX_CONFIG);
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
            RequestOptions::TIMEOUT => VertexApiConfig::REQUEST_TIMEOUT,
            RequestOptions::CONNECT_TIMEOUT => VertexApiConfig::REQUEST_CONNECT_TIMEOUT,
        ]);
    }

    /**
     * @return \SprykerEco\Client\Vertex\HttpClient\MessageFormatterInterface
     */
    public function createFilteringMessageFormatter(): MessageFormatterInterface
    {
        return new FilteringMessageFormatter();
    }

    /**
     * @return \SprykerEco\Client\Vertex\Builder\LocationMapper
     */
    protected function createLocationMapper(): LocationMapper
    {
        return new LocationMapper();
    }

    /**
     * @return \SprykerEco\Client\Vertex\Builder\PriceConverter
     */
    protected function createPriceConverter(): PriceConverter
    {
        return new PriceConverter();
    }

    /**
     * Quotation Request Builder, SaleMessageType = "QUOTATION"
     */
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

    /**
     * Invoice Request Builder, SaleMessageType = "INVOICE"
     */
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

    /**
     * @return \SprykerEco\Client\Vertex\Builder\VertexSuppliesRequestBuilderInterface
     */
    protected function createVertexSuppliesDefaultsBuilder(): VertexSuppliesRequestBuilderInterface
    {
        return new VertexSuppliesDefaultsBuilder();
    }

    /**
     * @return \SprykerEco\Client\Vertex\Builder\VertexSuppliesRequestBuilderInterface
     */
    protected function createVertexSuppliesTransactionTypeBuilder(): VertexSuppliesRequestBuilderInterface
    {
        return new VertexSuppliesTransactionTypeBuilder();
    }

    /**
     * @return \SprykerEco\Client\Vertex\Builder\VertexSuppliesRequestBuilderInterface
     */
    protected function createVertexSuppliesTransactionIdBuilder(): VertexSuppliesRequestBuilderInterface
    {
        return new VertexSuppliesTransactionIdBuilder();
    }

    /**
     * @return \SprykerEco\Client\Vertex\Builder\VertexSuppliesRequestBuilderInterface
     */
    protected function createVertexSuppliesDocumentNumberBuilder(): VertexSuppliesRequestBuilderInterface
    {
        return new VertexSuppliesDocumentNumberBuilder();
    }

    /**
     * @return \SprykerEco\Client\Vertex\Builder\VertexSuppliesRequestBuilderInterface
     */
    protected function createVertexSuppliesDocumentDateBuilder(): VertexSuppliesRequestBuilderInterface
    {
        return new VertexSuppliesDocumentDateBuilder();
    }

    /**
     * @return \SprykerEco\Client\Vertex\Builder\VertexSuppliesRequestBuilderInterface
     */
    protected function createVertexSuppliesPostingDateBuilder(): VertexSuppliesRequestBuilderInterface
    {
        return new VertexSuppliesPostingDateBuilder();
    }

    /**
     * @return \SprykerEco\Client\Vertex\Builder\VertexSuppliesRequestBuilderInterface
     */
    protected function createVertexSuppliesQuotationSaleMessageTypeBuilder(): VertexSuppliesRequestBuilderInterface
    {
        return new VertexSuppliesQuotationSaleMessageTypeBuilder();
    }

    /**
     * @return \SprykerEco\Client\Vertex\Builder\VertexSuppliesRequestBuilderInterface
     */
    protected function createVertexSuppliesInvoiceSaleMessageTypeBuilder(): VertexSuppliesRequestBuilderInterface
    {
        return new VertexSuppliesInvoiceSaleMessageTypeBuilder();
    }

    /**
     * @return \SprykerEco\Client\Vertex\Builder\VertexSuppliesRequestBuilderInterface
     */
    protected function createVertexSuppliesMetadataBuilder(): VertexSuppliesRequestBuilderInterface
    {
        return new VertexSuppliesMetadataBuilder();
    }

    /**
     * @return \SprykerEco\Client\Vertex\Builder\VertexSuppliesRequestBuilderInterface
     */
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

    /**
     * @return \SprykerEco\Client\Vertex\Builder\VertexSuppliesRequestBuilderInterface
     */
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

    /**
     * @return \SprykerEco\Client\Vertex\Builder\VertexLineItemBuilderInterface
     */
    protected function createVertexLineItemIdBuilder(): VertexLineItemBuilderInterface
    {
        return new VertexLineItemIdBuilder();
    }

    /**
     * @return \SprykerEco\Client\Vertex\Builder\VertexLineItemBuilderInterface
     */
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
     * @return \SprykerEco\Client\Vertex\MessageBroker\VertexApiMessageHandlerInterface
     */
    public function createVertexApiMessageHandler(): VertexApiMessageHandlerInterface
    {
        return new VertexApiMessageHandler(
            $this->getVertexConfigFacade(),
            $this->createInvoiceVertexTaxCalculator(),
        );
    }

    /**
     * @return \SprykerEco\Client\Vertex\Refund\VertexApiRefundsInterface
     */
    public function createVertexApiRefunds(): VertexApiRefundsInterface
    {
        return new VertexApiRefunds(
            $this->getVertexConfigFacade(),
            $this->createInvoiceVertexTaxCalculator(),
        );
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
}
