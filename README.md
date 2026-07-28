# UK Yield Rates Live

A WordPress plugin to display UK government bond (gilt) yield rates using shortcodes and Gutenberg blocks. Perfect for estate agents, mortgage brokers, and financial advisors who need to show current yield rates in their website content.

## Features

- **Manual Data Entry** - Most reliable method - just enter rates from Bank of England
- **API Support** - Bank of England + FRED APIs available (optional)
- **Multiple Display Formats** - Inline, sidebar widget, table, and compact layouts
- **Gutenberg Block** - Visual editor with live preview
- **Shortcode Support** - Works with classic editor and page builders
- **Responsive Design** - Looks great on all devices
- **Light/Dark Themes** - Match your site's design

## Quick Start

### Step 1: Enter Current Yield Rates

1. Go to **Settings > UK Yield Rates** in WordPress admin
2. In the **Data Source** tab, you'll see manual entry fields
3. Visit https://www.bankofengland.co.uk/statistics/yield-curves
4. Enter the current yields for 2Y, 5Y, 10Y, 20Y, and 30Y maturities
5. Click **Save Changes**

### Step 2: Use Shortcodes in Your Content

Add these shortcodes anywhere in your pages or posts:

```php
// For service pages (recommended - flows naturally in paragraphs)
Our mortgage rates are influenced by the [uk_yield_rates inline="yes" maturity="10"] 10-year gilt yield.

// Sidebar widget
[uk_yield_rates format="sidebar"]

// Full table
[uk_yield_rates format="table"]

// Multiple maturities inline
[uk_yield_rates inline="yes" maturity="2,5,10"]
```

### Step 3: Update Rates (When They Change)

1. Check the Bank of England yield curves page periodically
2. Update the rates in the plugin settings
3. All your pages will automatically show the new rates!

**Pro Tip:** Set a calendar reminder to update rates weekly or whenever Bank of England announces changes.

## Shortcode Attributes

| Attribute | Values | Default | Description |
|-----------|--------|---------|-------------|
| `maturity` | all, 2, 5, 10, 20, 30 | all | Which yield maturities to display |
| `format` | inline, sidebar, table, compact | inline | Display format |
| `inline` | yes, no | no | Force inline display in paragraphs |
| `show_change` | yes, no | yes | Show change indicator |
| `decimal` | 2, 3, 4 | 2 | Decimal places |
| `theme` | light, dark | light | Color theme |

## API Configuration (Optional)

The plugin supports automatic data fetching, but manual entry is recommended for reliability:

1. **Manual Entry** (Recommended) - Most reliable, just enter rates manually
2. **Bank of England** - Free, but requires JavaScript execution
3. **FRED** - Free API key required (https://fred.stlouisfed.org/docs/api/api_key.html)

## Use Cases

### Estate Agents

Show current mortgage rates context:

```php
<p>Our fixed-rate mortgages start from 4.25%, influenced by the current [uk_yield_rates inline="yes" maturity="5"] 5-year gilt yield. Contact us today for a personalized quote!</p>
```

### Mortgage Brokers

Display rate comparisons:

```php
[uk_yield_rates format="table"]

<p>The above rates are based on current UK gilt yields. Contact us for personalized mortgage advice.</p>
```

### Financial Advisors

Portfolio context:

```php
<p>Current UK government bond yields: [uk_yield_rates inline="yes" maturity="2,5,10"]</p>
```

## Installation

1. Download the plugin files
2. Upload the `uk-yield-rates` folder to `/wp-content/plugins/`
3. Activate the plugin through the WordPress admin
4. Go to **Settings > UK Yield Rates** and enter current yield rates
5. Add shortcodes to your pages and posts

## Development

### Prerequisites

- Node.js 16+
- npm or yarn

### Setup

```bash
# Install dependencies
npm install

# Build Gutenberg block
npm run build

# Development mode with watch
npm run start
```

## Requirements

- WordPress 5.0+
- PHP 7.4+

## License

GPL v2 or later - see [LICENSE](LICENSE) for details

## Support

For support and documentation, visit https://example.com/support
