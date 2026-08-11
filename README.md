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

The module can be configured in two ways: via environment configuration (constants in `config/Shared/config_default.php`) or via the Back Office (see [Back Office Configuration](#back-office-configuration)). Both can be used together — when a value is set in the Back Office, it takes priority over the corresponding constant; environment configuration acts as the fallback for anything left unset in the Back Office.

Only set the constants below if you don't want to manage the corresponding value through the Back Office:

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

`isTaxIdValidatorEnabled` defaults to `false` and is not driven by a constant or Back Office Configuration. Override it in `src/Pyz/Zed/Vertex/VertexConfig.php`:

```php
namespace Pyz\Zed\Vertex;

use SprykerEco\Zed\Vertex\VertexConfig as SprykerEcoVertexConfig;

class VertexConfig extends SprykerEcoVertexConfig
{
    public function isTaxIdValidatorEnabled(): bool
    {
        return true;
    }
}
```

`isTaxAssistEnabled` and `isInvoicingEnabled` also default to `false`, but they no longer require a project-level override — see [Back Office Configuration](#back-office-configuration) below to enable them without code changes.

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

### Back Office Configuration

Most Vertex settings can also be managed from the Back Office instead of `config/Shared/config_default.php` or a project-level `VertexConfig` override. Go to **Back Office > Integrations > Vertex** (credentials, Taxamo/tax ID validation, invoicing, tax assist) and **Back Office > Taxes > Tax Provider** (to select Vertex as the active tax provider) to configure these values through the UI.

When a value is set in the Back Office, it takes precedence over the corresponding constant or config method; if it's left empty, the module falls back to the environment configuration described below.

| Back Office setting | Location | Equivalent constant / method |
|---|---|---|
| Tax provider | Taxes > Tax Provider | `IS_ACTIVE` |
| Client ID | Integrations > Vertex > Configuration | `CLIENT_ID` |
| Client secret | Integrations > Vertex > Configuration | `CLIENT_SECRET` |
| Security URI | Integrations > Vertex > Configuration | `SECURITY_URI` |
| Transaction calls URI | Integrations > Vertex > Configuration | `TRANSACTION_CALLS_URI` |
| Default taxpayer company code | Integrations > Vertex > Configuration | `DEFAULT_TAXPAYER_COMPANY_CODE` |
| Vendor code | Integrations > Vertex > Configuration | `VENDOR_CODE` |
| Seller country code | Integrations > Vertex > Configuration | `getSellerCountryCode()` |
| Customer country code | Integrations > Vertex > Configuration | `getCustomerCountryCode()` |
| Vertex Validator (Taxamo) API URL | Integrations > Vertex > Tax ID validation (Taxamo) | `TAXAMO_API_URL` |
| Taxamo token | Integrations > Vertex > Tax ID validation (Taxamo) | `TAXAMO_TOKEN` |
| Submit Tax invoices to Vertex | Integrations > Vertex > Invoicing | `isInvoicingEnabled()` |
| Enable Tax Assist in Vertex | Integrations > Vertex > Tax Assist | `isTaxAssistEnabled()` |

`isTaxIdValidatorEnabled` is the only flag not exposed in the Back Office; it must still be overridden in `src/Pyz/Zed/Vertex/VertexConfig.php` (see step 3).

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

The following methods default to `false` or empty string. `isTaxAssistEnabled()`, `isInvoicingEnabled()`, `getSellerCountryCode()`, and `getCustomerCountryCode()` can be set via the Back Office instead of an override (see [Back Office Configuration](#back-office-configuration) above); `isTaxIdValidatorEnabled()` must still be overridden in the project config to enable the feature:

| Method | Default | Description                                                                                                                                                                       |
|--------|---------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `isTaxIdValidatorEnabled()` | `false` | Enables tax ID validation via [Vertex Validator](https://developer.vertexinc.com/vertex-e-commerce/docs/stand-alone-deployments). Requires `TAXAMO_API_URL` and `TAXAMO_TOKEN` to be set.                                                                  |
| `isTaxAssistEnabled()` | `false` | Enables the tax assist feature. Return Assisted Parameters in the response that will provide more details about the calculation. The logs can be checked in the Vertex Dashboard. |
| `isInvoicingEnabled()` | `false` | Enables invoicing functionality. Requires OMS plugins to be registered (see step 6.3).                                                                                            |
| `getSellerCountryCode()` | `''` | Overrides the default seller country code (2-letter ISO, e.g. `US`). Defaults to the first country of the store.                                                                  |
| `getCustomerCountryCode()` | `''` | Overrides the default customer country code (applied only when no customer billing address is provided).  Defaults to the first country of the store.                             |

## Back Office Configuration

As an alternative to the environment constants above, Vertex can be configured from the **Back Office Configuration** page (per scope: global and per store), backed by the `spryker/configuration` module. This is opt-in and disabled by default.

When enabled, `VertexConfig` reads its values from the Configuration module instead of `config/Shared/config_default.php`, and "Vertex is active" means Vertex is selected as the tax provider for the given scope (see below) rather than `VertexConstants::IS_ACTIVE`.

### 1. Enable the Configuration source

Override `isConfigurationModuleUsed()` to return `true` in `src/Pyz/Shared/Vertex/VertexConfig.php`:

```php
namespace Pyz\Shared\Vertex;

use SprykerEco\Shared\Vertex\VertexConfig as SprykerEcoVertexConfig;

class VertexConfig extends SprykerEcoVertexConfig
{
    public function isConfigurationModuleUsed(): bool
    {
        return true;
    }
}
```

### 2. Register the pre-save validation plugin

Add `VertexTaxProviderPreSavePlugin` to `src/Pyz/Zed/Configuration/ConfigurationDependencyProvider.php`:

```php
use SprykerEco\Zed\Vertex\Communication\Plugin\Configuration\VertexTaxProviderPreSavePlugin;

/**
 * @return array<\Spryker\Zed\ConfigurationExtension\Dependency\Plugin\ConfigurationValuePreSavePluginInterface>
 */
protected function getConfigurationValuePreSavePlugins(): array
{
    return [
        // ... other plugins
        new VertexTaxProviderPreSavePlugin(),
    ];
}
```

### 3. Sync the schema

The module ships its schema in `resources/configuration/vertex.configuration.yml`. Merge it into the settings map:

```bash
docker/sdk cli console configuration:sync
```

### Where the settings live

| Back Office location | Settings |
|----------------------|----------|
| **Integrations > Vertex > Configuration** | Security URI, Transaction calls URI, Client ID, Client secret, Default taxpayer company code, Vendor code, Seller country code, Customer country code |
| **Integrations > Vertex > Tax ID validation (Taxamo)** | Vertex Validator (Taxamo) API URL, Taxamo token |
| **Integrations > Vertex > Invoicing** | Submit Tax invoices to Vertex |
| **Integrations > Vertex > Tax Assist** | Enable Tax Assist in Vertex |
| **Taxes > Tax Provider** | Tax provider (Default (Spryker) / Vertex) |

Client ID, Client secret, and Taxamo token are stored as secrets (encrypted, never published to storage). All settings support the `global` and `store` scopes, with `store` overriding `global`.

### Save-time validation

`VertexTaxProviderPreSavePlugin` guards the save so the Vertex integration cannot be left in a broken state:

- **Tax provider selection** — selecting Vertex under Taxes > Tax Provider is blocked unless the Vertex configuration is complete for that scope.
- **Credential removal / incomplete configuration** — while Vertex is the selected tax provider for a scope, saving a credential change that would leave the configuration incomplete (including clearing a field) is blocked. Global changes are also checked against every store that inherits the global values and has Vertex selected.
- **URL format** — the Security URI, Transaction calls URI, and Taxamo API URL fields must contain a valid URL (including the scheme, e.g. `https://`) whenever a value is present, even if Vertex is not the selected tax provider.

## Documentation

[Spryker Documentation](https://docs.spryker.com/docs/pbc/all/tax-management/latest/base-shop/third-party-integrations/vertex/vertex)
