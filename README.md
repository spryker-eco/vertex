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

// Optional: Tax ID Validator (requires Vertex Validator, previously known as Taxamo, see https://developer.vertexinc.com/vertex-e-commerce/docs/stand-alone-deployments)
$config[VertexConstants::TAXAMO_API_URL] = getenv('TAXAMO_API_URL');
$config[VertexConstants::TAXAMO_TOKEN] = getenv('TAXAMO_TOKEN');

// Optional: Vendor Code
$config[VertexConstants::VENDOR_CODE] = '';
```

### 3. Override Feature Flags in Config

`isTaxIdValidatorEnabled`, `isTaxAssistEnabled`, and `isInvoicingEnabled` default to `false` and are not driven by constants. Override them in `src/Pyz/Zed/Vertex/VertexConfig.php`:

```php
namespace Pyz\Zed\Vertex;

use SprykerEco\Zed\Vertex\VertexConfig as SprykerEcoVertexConfig;

class VertexConfig extends SprykerEcoVertexConfig
{
    public function isTaxIdValidatorEnabled(): bool
    {
        return true;
    }

    public function isTaxAssistEnabled(): bool
    {
        return true;
    }

    public function isInvoicingEnabled(): bool
    {
        return true;
    }
}
```

### 4. Set Up Database Schema

Install the database schema by running:

```bash
vendor/bin/console propel:install
```

### 5. Generate Transfer Objects

Generate transfer objects for the module:

```bash
vendor/bin/console transfer:generate
```

### 6. Register Plugins

#### 6.1 Register Tax Calculation Plugin

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

#### 6.2 Register CalculableObject Expander Plugins and Order Expander Plugins

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

#### 6.3 Register OMS Plugins (Optional)

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

#### 6.4 Register Glue API Plugin (Optional)

Registers the `POST /tax-id-validate` Glue REST API endpoint that validates a customer's Tax Identification Number (VAT ID) against a given country code via the Vertex Taxamo service. This is useful for B2B storefronts where customers must provide a valid VAT ID during checkout or address management to qualify for tax-exempt or reverse-charge transactions within the EU.

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

### 7. Import Data

The module provides pre-configured data import files for translations.

**Option 1: Import Using Module's Configuration File**

```bash
docker/sdk cli vendor/bin/console data:import --config=vendor/spryker-eco/vertex/data/import/vertex.yml
```

**Option 2: Copy File Content and Import Individually**

Copy file's content from `vendor/spryker-eco/vertex/data/import/*.csv` to the same files in your project `data/import/common/common/`. Then run:

```bash
docker/sdk cli vendor/bin/console data:import glossary
```

**Option 3: Add to Project's Main Import Configuration**

Add the import actions to your project's main data import configuration file and include in your regular import pipeline.

**Customize Translations**

Before importing, you can customize the translation data:

File: `vendor/spryker-eco/vertex/data/import/glossary.csv`

- Customize translations for tax validation messages
- Add additional locales

## Configuration Options

### Required Constants (`config/Shared/config_default.php`)

| Constant | Description |
|----------|-------------|
| `IS_ACTIVE` | Enable or disable Vertex tax calculation |
| `CLIENT_ID` | OAuth client ID for Vertex API |
| `CLIENT_SECRET` | OAuth client secret for Vertex API |
| `SECURITY_URI` | Vertex OAuth security endpoint |
| `TRANSACTION_CALLS_URI` | Vertex transaction calls endpoint |

### Optional Constants (`config/Shared/config_default.php`)

| Constant | Description |
|----------|-------------|
| `TAXAMO_API_URL` | Vertex Validator API URL for tax ID validation. [Details](https://developer.vertexinc.com/vertex-e-commerce/docs/stand-alone-deployments). |
| `TAXAMO_TOKEN` | Vertex Validator API authentication token |
| `VENDOR_CODE` | Vendor code for Vertex tax calculations |
| `DEFAULT_TAXPAYER_COMPANY_CODE` | Default taxpayer company code |

### Config Methods (`src/Pyz/Zed/Vertex/VertexConfig.php`)

The following methods default to `false` or empty string and must be overridden in the project config to enable the respective features:

| Method | Default | Description                                                                                                                                                                       |
|--------|---------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `isTaxIdValidatorEnabled()` | `false` | Enables tax ID validation via [Vertex Validator](https://developer.vertexinc.com/vertex-e-commerce/docs/stand-alone-deployments). Requires `TAXAMO_API_URL` and `TAXAMO_TOKEN` to be set.                                                                  |
| `isTaxAssistEnabled()` | `false` | Enables the tax assist feature. Return Assisted Parameters in the response that will provide more details about the calculation. The logs can be checked in the Vertex Dashboard. |
| `isInvoicingEnabled()` | `false` | Enables invoicing functionality. Requires OMS plugins to be registered (see step 6.3).                                                                                            |
| `getSellerCountryCode()` | `''` | Overrides the default seller country code (2-letter ISO, e.g. `US`). Defaults to the first country of the store.                                                                  |
| `getCustomerCountryCode()` | `''` | Overrides the default customer country code (applied only when no customer billing address is provided).  Defaults to the first country of the store.                             |

## Documentation

[Spryker Documentation](https://docs.spryker.com/docs/pbc/all/tax-management/latest/base-shop/third-party-integrations/vertex/vertex)
