# Vertex Module

[![Latest Stable Version](https://poser.pugx.org/spryker-eco/vertex/v/stable.svg)](https://packagist.org/packages/spryker-eco/vertex)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%208.3-8892BF.svg)](https://php.net/)

Vertex module is responsible for handling tax calculation.

## Installation

### 1. Install the Module

```bash
composer require spryker-eco/vertex
```

### 2. Configure the Module

Add the following configuration to your `config/Shared/config_default.php` file:

```php
use SprykerEco\Shared\Vertex\VertexConstants;

// Enable Vertex tax calculation
$config[VertexConstants::IS_ACTIVE] = true;

// Vertex API Authentication
$config[VertexConstants::CLIENT_ID] = getenv('VERTEX_CLIENT_ID');
$config[VertexConstants::CLIENT_SECRET] = getenv('VERTEX_CLIENT_SECRET');
$config[VertexConstants::SECURITY_URI] = getenv('VERTEX_SECURITY_URI');
$config[VertexConstants::TRANSACTION_CALLS_URI] = getenv('VERTEX_TRANSACTION_CALLS_URI');

// Optional: Tax ID Validator (requires Taxamo)
$config[VertexConstants::IS_TAX_ID_VALIDATOR_ENABLED] = false;
$config[VertexConstants::TAXAMO_API_URL] = getenv('TAXAMO_API_URL');
$config[VertexConstants::TAXAMO_TOKEN] = getenv('TAXAMO_TOKEN');

// Optional: Tax Assist Feature
$config[VertexConstants::IS_TAX_ASSIST_ENABLED] = false;

// Optional: Invoicing Feature
$config[VertexConstants::IS_INVOICING_ENABLED] = false;

// Optional: Vendor Code
$config[VertexConstants::VENDOR_CODE] = '';
```

### 3. Set Up Database Schema

Install the database schema by running:

```bash
vendor/bin/console propel:install
```

### 4. Generate Transfer Objects

Generate transfer objects for the module:

```bash
vendor/bin/console transfer:generate
```

### 5. Register Plugins

#### 5.1 Register Tax Calculation Plugin

Add the Vertex calculation plugin to `src/Pyz/Zed/Calculation/CalculationDependencyProvider.php`:

```php
use SprykerEco\Zed\Vertex\Communication\Plugin\Calculation\VertexCalculationPlugin;

protected function getQuoteCalculatorPluginStack(Container $container): array
{
    return [
        // ... other plugins
        new VertexCalculationPlugin(),
        // ... total calculation plugins
    ];
}

protected function getOrderCalculatorPluginStack(Container $container): array
{
    return [
        // ... other plugins
        new VertexCalculationPlugin(),
        // ... total calculation plugins
    ];
}
```

#### 5.2 Register CalculableObject Expander Plugins and Order Expander Plugins

Add order and CalculableObject expander plugins to `src/Pyz/Zed/Vertex/VertexDependencyProvider.php`:
Proposed plugins are examples, you can choose which ones to register based on your requirements or create custom ones if needed.

```php
use SprykerEco\Zed\Vertex\Communication\Plugin\Order\OrderCustomerWithVertexCodeExpanderPlugin;
use SprykerEco\Zed\Vertex\Communication\Plugin\Order\OrderExpensesWithVertexCodeExpanderPlugin;
use SprykerEco\Zed\Vertex\Communication\Plugin\Order\OrderItemProductOptionWithVertexCodeExpanderPlugin;
use SprykerEco\Zed\Vertex\Communication\Plugin\Order\OrderItemWithVertexSpecificFieldsExpanderPlugin;
use SprykerEco\Zed\Vertex\Communication\Plugin\Quote\CalculableObjectCustomerWithVertexCodeExpanderPlugin;
use SprykerEco\Zed\Vertex\Communication\Plugin\Quote\CalculableObjectExpensesWithVertexCodeExpanderPlugin;
use SprykerEco\Zed\Vertex\Communication\Plugin\Quote\CalculableObjectItemProductOptionWithVertexCodeExpanderPlugin;
use SprykerEco\Zed\Vertex\Communication\Plugin\Quote\CalculableObjectItemWithVertexSpecificFieldsExpanderPlugin;

protected function getCalculableObjectVertexExpanderPlugins(): array
{
    return [
        // ... other plugins
        new CalculableObjectCustomerWithVertexCodeExpanderPlugin(),
        new CalculableObjectExpensesWithVertexCodeExpanderPlugin(),
        new CalculableObjectItemProductOptionWithVertexCodeExpanderPlugin(),
        new CalculableObjectItemWithVertexSpecificFieldsExpanderPlugin(),
    ];
}

protected function getOrderVertexExpanderPlugins(): array
{
    return [
        // ... other plugins
        new OrderCustomerWithVertexCodeExpanderPlugin(),
        new OrderExpensesWithVertexCodeExpanderPlugin(),
        new OrderItemProductOptionWithVertexCodeExpanderPlugin(),
        new OrderItemWithVertexSpecificFieldsExpanderPlugin(),
    ];
}
```

#### 5.3 Register OMS Plugins (Optional)

If you want to use invoicing functionality, add OMS plugins to `src/Pyz/Zed/Oms/OmsDependencyProvider.php`:

```php
use SprykerEco\Zed\Vertex\Communication\Plugin\Oms\Command\VertexSubmitPaymentTaxInvoicePlugin;
use SprykerEco\Zed\Vertex\Communication\Plugin\Oms\VertexOrderRefundedEventListenerPlugin;

protected function extendCommandPlugins(Container $container): Container
    {
        $container->extend(self::COMMAND_PLUGINS, function (CommandCollectionInterface $commandCollection) {
            // ... other command plugins
            $commandCollection->add(new VertexSubmitPaymentTaxInvoicePlugin(), 'Vertex/SubmitPaymentTaxInvoice');

            return $commandCollection;
        });

        return $container;
    }

protected function getOmsEventTriggeredListenerPlugins(Container $container): array
{
    return [
        // ... other plugins
        new VertexOrderRefundedEventListenerPlugin(),
    ];
}
```

#### 5.4 Register Glue API Plugin (Optional)

If you want to expose tax validation via REST API, add the Glue plugin to `src/Pyz/Glue/GlueApplication/GlueApplicationDependencyProvider.php`:

```php
use SprykerEco\Glue\Vertex\Plugin\VertexTaxValidateIdResourceRoutePlugin;

protected function getResourceRoutePlugins(): array
{
    return [
        // ... other plugins
        new VertexTaxValidateIdResourceRoutePlugin(),
    ];
}
```

## Configuration Options

### Required Configuration
- `IS_ACTIVE`: Enable or disable Vertex tax calculation
- `CLIENT_ID`: OAuth client ID for Vertex API
- `CLIENT_SECRET`: OAuth client secret for Vertex API
- `SECURITY_URI`: Vertex OAuth security endpoint
- `TRANSACTION_CALLS_URI`: Vertex transaction calls endpoint

### Optional Configuration
- `IS_TAX_ID_VALIDATOR_ENABLED`: Enable tax ID validation via Taxamo
- `TAXAMO_API_URL`: Taxamo API URL for tax ID validation
- `TAXAMO_TOKEN`: Taxamo API authentication token
- `IS_TAX_ASSIST_ENABLED`: Enable tax assist feature
- `IS_INVOICING_ENABLED`: Enable invoicing functionality
- `VENDOR_CODE`: Vendor code for Vertex tax calculations

## Documentation

[Spryker Documentation](https://docs.spryker.com/docs/pbc/all/tax-management/latest/base-shop/third-party-integrations/vertex/connect-vertex#prerequisites)
