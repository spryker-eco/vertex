# Migration from ACP TaxApp to Vertex ECO Module

If migrating from the MessageBroker-based [TaxApp ACP](https://docs-archive.spryker.com/docs/pbc/all/tax-management/202507.0/base-shop/third-party-integrations/vertex/vertex) integration to the direct `spryker-eco/vertex` module:

> **Note:** The tax calculation logic remains the same. This ECO module communicates directly with the Vertex API from your application.

## Step 1: Install and Integrate the Module

Follow the [Vertex Integration Guide](../README.md) to install and set up the module.

## Step 2: Remove Old ACP Plugins and Configuration

### 2a. Remove TaxApp MessageBroker Handler

File: `src/Pyz/Zed/MessageBroker/MessageBrokerDependencyProvider.php`

Remove the following import and plugin registration:

```php
// Remove this use statement:
use Spryker\Zed\TaxApp\Communication\Plugin\MessageBroker\TaxAppMessageHandlerPlugin;

// Remove from getMessageHandlerPlugins():
new TaxAppMessageHandlerPlugin(),
```

### 2b. Remove TaxApp Publisher Plugin (if you're running a DMS project)

File: `src/Pyz/Zed/Publisher/PublisherDependencyProvider.php`

Remove the following import and plugin registration:

```php
// Remove this use statement:
use Spryker\Zed\TaxApp\Communication\Plugin\Publisher\Store\RefreshTaxAppStoreRelationPublisherPlugin;

// In getTaxAppPlugins(), return an empty array:
protected function getTaxAppPlugins(): array
{
    return [];
}
```

### 2c. Replace TaxApp Calculation Plugin

File: `src/Pyz/Zed/Calculation/CalculationDependencyProvider.php`

Replace `TaxAppCalculationPlugin` with `VertexCalculationPlugin` in **both** quote and order calculator stacks:

```php
// Remove:
use Spryker\Zed\TaxApp\Communication\Plugin\Calculation\TaxAppCalculationPlugin;

// Add:
use SprykerEco\Zed\Vertex\Communication\Plugin\Calculation\VertexCalculationPlugin;
```

In `getQuoteCalculatorPluginStack()` and `getOrderCalculatorPluginStack()`, replace:
```php
// Before:
new TaxAppCalculationPlugin(),

// After:
new VertexCalculationPlugin(),
```

### 2d. Replace TaxApp OMS Plugins

File: `src/Pyz/Zed/Oms/OmsDependencyProvider.php`

```php
// Remove:
use Spryker\Zed\TaxApp\Communication\Plugin\Oms\Command\SubmitPaymentTaxInvoicePlugin;
use Spryker\Zed\TaxApp\Communication\Plugin\Oms\OrderRefundedEventListenerPlugin;

// Add:
use SprykerEco\Zed\Vertex\Communication\Plugin\Oms\Command\VertexSubmitPaymentTaxInvoicePlugin;
use SprykerEco\Zed\Vertex\Communication\Plugin\Oms\VertexOrderRefundedEventListenerPlugin;
```

In `extendCommandPlugins()`, replace:
```php
// Before:
$commandCollection->add(new SubmitPaymentTaxInvoicePlugin(), 'TaxApp/SubmitPaymentTaxInvoice');

// After:
$commandCollection->add(new VertexSubmitPaymentTaxInvoicePlugin(), 'Vertex/SubmitPaymentTaxInvoice');
```

In `getOmsEventTriggeredListenerPlugins()`, replace:
```php
// Before:
new OrderRefundedEventListenerPlugin(),

// After:
new VertexOrderRefundedEventListenerPlugin(),
```

### 2e. Replace TaxApp Glue Storefront API Plugin

File: `src/Pyz/Glue/GlueApplication/GlueApplicationDependencyProvider.php`

```php
// Remove:
use Spryker\Glue\TaxAppRestApi\Plugin\TaxValidateIdResourceRoutePlugin;

// Add:
use SprykerEco\Glue\Vertex\Plugin\VertexTaxValidateIdResourceRoutePlugin;
```

In `getResourceRoutePlugins()`, replace:
```php
// Before:
new TaxValidateIdResourceRoutePlugin(),

// After:
new VertexTaxValidateIdResourceRoutePlugin(),
```

### 2f. Update OMS State Machine XML

Update your OMS process XML files (e.g., `config/Zed/oms/DummyPayment01.xml`) to reference the new command name:

```xml
<!-- Before: -->
<event name="submit tax invoice" onEnter="true" command="TaxApp/SubmitPaymentTaxInvoice"/>

<!-- After: -->
<event name="submit tax invoice" onEnter="true" command="Vertex/SubmitPaymentTaxInvoice"/>
```

### 2g. Clean Up config_default.php

Remove TaxApp-specific configuration and MessageBroker channel mappings:

```php
// Remove these transfer use statements:
use Generated\Shared\Transfer\ConfigureTaxAppTransfer;
use Generated\Shared\Transfer\DeleteTaxAppTransfer;
use Generated\Shared\Transfer\SubmitPaymentTaxInvoiceTransfer;

// Remove this use statement:
use Spryker\Shared\TaxApp\TaxAppConstants;

// Remove TaxAppConstants from OAuth/tenant assignments:
// Remove: $config[TaxAppConstants::OAUTH_PROVIDER_NAME] = ...
// Remove: $config[TaxAppConstants::OAUTH_GRANT_TYPE] = ...
// Remove: $config[TaxAppConstants::OAUTH_OPTION_AUDIENCE] = ...
// Remove: $config[TaxAppConstants::TENANT_IDENTIFIER] = ...

// Remove TaxApp MessageBroker channel mappings:
// Remove from $config[MessageBrokerConstants::CHANNEL_TO_RECEIVER_TRANSPORT_MAP]:
//   ConfigureTaxAppTransfer::class => 'tax-commands',
//   DeleteTaxAppTransfer::class => 'tax-commands',
//   SubmitPaymentTaxInvoiceTransfer::class => 'payment-tax-invoice-commands',
```

## Step 3: Add New Vertex Configuration

### 3a. Add Vertex Credentials

File: `config/Shared/config_default.php`

```php
use SprykerEco\Shared\Vertex\VertexConstants;

// Vertex
$config[VertexConstants::IS_ACTIVE] = getenv('VERTEX_IS_ACTIVE') ?: null;
$config[VertexConstants::CLIENT_ID] = getenv('VERTEX_CLIENT_ID') ?: null;
$config[VertexConstants::CLIENT_SECRET] = getenv('VERTEX_CLIENT_SECRET') ?: null;
$config[VertexConstants::SECURITY_URI] = getenv('VERTEX_SECURITY_URI') ?: null;
$config[VertexConstants::TRANSACTION_CALLS_URI] = getenv('VERTEX_TRANSACTION_CALLS_URI') ?: null;

// Optional: Tax ID Validator (Vertex Validator / Taxamo)
$config[VertexConstants::TAXAMO_API_URL] = getenv('TAXAMO_API_URL') ?: null;
$config[VertexConstants::TAXAMO_TOKEN] = getenv('TAXAMO_TOKEN') ?: null;
```

### 3b. Override Feature Flags

Create `src/Pyz/Zed/Vertex/VertexConfig.php`:

```php
<?php

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

### 3c. Register Expander and Fallback Plugins

Create `src/Pyz/Zed/Vertex/VertexDependencyProvider.php`:

```php
<?php

namespace Pyz\Zed\Vertex;

use Spryker\Zed\Calculation\Communication\Plugin\Calculator\ItemTaxAmountFullAggregatorPlugin;
use Spryker\Zed\Calculation\Communication\Plugin\Calculator\PriceToPayAggregatorPlugin;
use Spryker\Zed\MerchantProfile\Communication\Plugin\TaxApp\MerchantProfileAddressCalculableObjectTaxAppExpanderPlugin;
use Spryker\Zed\MerchantProfile\Communication\Plugin\TaxApp\MerchantProfileAddressOrderTaxAppExpanderPlugin;
use Spryker\Zed\ProductOfferAvailability\Communication\Plugin\TaxApp\ProductOfferAvailabilityCalculableObjectTaxAppExpanderPlugin;
use Spryker\Zed\ProductOfferAvailability\Communication\Plugin\TaxApp\ProductOfferAvailabilityOrderTaxAppExpanderPlugin;
use Spryker\Zed\Tax\Communication\Plugin\Calculator\TaxAmountAfterCancellationCalculatorPlugin;
use Spryker\Zed\Tax\Communication\Plugin\Calculator\TaxAmountCalculatorPlugin;
use Spryker\Zed\Tax\Communication\Plugin\Calculator\TaxRateAverageAggregatorPlugin;
use SprykerEco\Zed\Vertex\Communication\Plugin\Order\OrderCustomerWithVertexCodeExpanderPlugin;
use SprykerEco\Zed\Vertex\Communication\Plugin\Order\OrderExpensesWithVertexCodeExpanderPlugin;
use SprykerEco\Zed\Vertex\Communication\Plugin\Order\OrderItemProductOptionWithVertexCodeExpanderPlugin;
use SprykerEco\Zed\Vertex\Communication\Plugin\Order\OrderItemWithVertexSpecificFieldsExpanderPlugin;
use SprykerEco\Zed\Vertex\Communication\Plugin\Quote\CalculableObjectCustomerWithVertexCodeExpanderPlugin;
use SprykerEco\Zed\Vertex\Communication\Plugin\Quote\CalculableObjectExpensesWithVertexCodeExpanderPlugin;
use SprykerEco\Zed\Vertex\Communication\Plugin\Quote\CalculableObjectItemProductOptionWithVertexCodeExpanderPlugin;
use SprykerEco\Zed\Vertex\Communication\Plugin\Quote\CalculableObjectItemWithVertexSpecificFieldsExpanderPlugin;
use SprykerEco\Zed\Vertex\VertexDependencyProvider as SprykerVertexDependencyProvider;

class VertexDependencyProvider extends SprykerVertexDependencyProvider
{
    /**
     * @return array<\SprykerEco\Zed\Vertex\Dependency\Plugin\CalculableObjectVertexExpanderPluginInterface|\Spryker\Zed\TaxAppExtension\Dependency\Plugin\CalculableObjectTaxAppExpanderPluginInterface>
     */
    protected function getCalculableObjectVertexExpanderPlugins(): array
    {
        return [
            new CalculableObjectCustomerWithVertexCodeExpanderPlugin(),
            new CalculableObjectExpensesWithVertexCodeExpanderPlugin(),
            new CalculableObjectItemProductOptionWithVertexCodeExpanderPlugin(),
            new CalculableObjectItemWithVertexSpecificFieldsExpanderPlugin(),
            new MerchantProfileAddressCalculableObjectTaxAppExpanderPlugin(),
            new ProductOfferAvailabilityCalculableObjectTaxAppExpanderPlugin(),
        ];
    }

    /**
     * @return array<\SprykerEco\Zed\Vertex\Dependency\Plugin\OrderVertexExpanderPluginInterface|\Spryker\Zed\TaxAppExtension\Dependency\Plugin\OrderTaxAppExpanderPluginInterface>
     */
    protected function getOrderVertexExpanderPlugins(): array
    {
        return [
            new OrderCustomerWithVertexCodeExpanderPlugin(),
            new OrderExpensesWithVertexCodeExpanderPlugin(),
            new OrderItemProductOptionWithVertexCodeExpanderPlugin(),
            new OrderItemWithVertexSpecificFieldsExpanderPlugin(),
            new MerchantProfileAddressOrderTaxAppExpanderPlugin(),
            new ProductOfferAvailabilityOrderTaxAppExpanderPlugin(),
        ];
    }

    /**
     * @return array<\Spryker\Zed\CalculationExtension\Dependency\Plugin\CalculationPluginInterface>
     */
    protected function getFallbackQuoteCalculationPlugins(): array
    {
        return [
            new TaxAmountCalculatorPlugin(),
            new ItemTaxAmountFullAggregatorPlugin(),
            new PriceToPayAggregatorPlugin(),
            new TaxRateAverageAggregatorPlugin(),
        ];
    }

    /**
     * @return array<\Spryker\Zed\CalculationExtension\Dependency\Plugin\CalculationPluginInterface>
     */
    protected function getFallbackOrderCalculationPlugins(): array
    {
        return [
            new TaxAmountCalculatorPlugin(),
            new ItemTaxAmountFullAggregatorPlugin(),
            new PriceToPayAggregatorPlugin(),
            new TaxAmountAfterCancellationCalculatorPlugin(),
        ];
    }
}
```

## Step 4: Set Up Database and Transfers

```bash
vendor/bin/console propel:install
vendor/bin/console transfer:generate
```

## Step 5: Import Glossary Data

The module provides translation data for tax validation messages.

**Option 1: Import Using Module's Configuration File**

```bash
vendor/bin/console data:import --config=vendor/spryker-eco/vertex/data/import/vertex.yml
```

**Option 2: Copy File Content and Import Individually**

Copy content from `vendor/spryker-eco/vertex/data/import/*.csv` to the corresponding files in `data/import/common/common/`. Then run:

```bash
vendor/bin/console data:import glossary
```

**Option 3: Add to Project's Main Import Configuration**

Add the import actions to your project's main data import configuration file and include in your regular import pipeline.

## Step 6: Verify

1. Clear caches: `vendor/bin/console cache:empty-all`
2. Place a test order and verify tax calculation works
3. If invoicing is enabled, verify the `Vertex/SubmitPaymentTaxInvoice` OMS command triggers correctly
4. If tax ID validation is enabled, test the `POST /tax-id-validate` Glue Storefront API endpoint

## Summary of Changes

| Component | ACP (Before) | ECO (After) |
|-----------|-------------|-------------|
| Tax calculation plugin | `TaxAppCalculationPlugin` | `VertexCalculationPlugin` |
| OMS invoice command | `SubmitPaymentTaxInvoicePlugin` (`TaxApp/...`) | `VertexSubmitPaymentTaxInvoicePlugin` (`Vertex/...`) |
| OMS refund listener | `OrderRefundedEventListenerPlugin` | `VertexOrderRefundedEventListenerPlugin` |
| Glue tax validation | `TaxValidateIdResourceRoutePlugin` | `VertexTaxValidateIdResourceRoutePlugin` |
| MessageBroker handler | `TaxAppMessageHandlerPlugin` | Removed (not needed) |
| Publisher plugin | `RefreshTaxAppStoreRelationPublisherPlugin` | Removed (not needed) |
| Configuration | `TaxAppConstants` + OAuth | `VertexConstants` + direct API credentials |
| Communication | Via MessageBroker (async) | Direct Vertex API calls (sync) |

## Benefits of Migration

- Direct API integration (no MessageBroker overhead)
- Simpler architecture with fewer moving parts
- Synchronous tax calculation during checkout
- Full control over Vertex API configuration and feature flags
- Built-in fallback calculation plugins when Vertex is unavailable
