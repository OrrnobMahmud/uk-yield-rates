# UK Yield Rates Live

A WordPress plugin to display live UK government bond (gilt) yield rates using shortcodes and Gutenberg blocks.

## Features

- **Dual API Support** - Bank of England + FRED APIs with automatic failover
- **Multiple Display Formats** - Inline, sidebar widget, table, and compact layouts
- **Gutenberg Block** - Visual editor with live preview
- **Shortcode Support** - Works with classic editor and page builders
- **Auto-Refresh** - Optional AJAX-based automatic updates
- **Responsive Design** - Looks great on all devices
- **Light/Dark Themes** - Match your site's design

## Installation

1. Download the plugin files
2. Upload the `uk-yield-rates` folder to `/wp-content/plugins/`
3. Activate the plugin through the WordPress admin
4. Configure settings at Settings > UK Yield Rates

## Usage

### Shortcodes

```php
// Basic inline display
[uk_yield_rates]

// Specific maturity inline
[uk_yield_rates inline="yes" maturity="10"]

// Sidebar widget
[uk_yield_rates format="sidebar"]

// Full table
[uk_yield_rates format="table"]

// Multiple maturities
[uk_yield_rates inline="yes" maturity="2,5,10"]
```

### Gutenberg Block

1. Add the "UK Yield Rates" block
2. Configure maturity, format, and display options in the sidebar
3. Preview updates live in the editor

## Shortcode Attributes

| Attribute | Values | Default | Description |
|-----------|--------|---------|-------------|
| `maturity` | all, 2, 5, 10, 20, 30 | all | Which yield maturities to display |
| `format` | inline, sidebar, table, compact | inline | Display format |
| `show_change` | yes, no | yes | Show change indicator |
| `decimal` | 2, 3, 4 | 2 | Decimal places |
| `theme` | light, dark | light | Color theme |

## API Configuration

The plugin supports two data sources:

1. **Bank of England** (Primary) - Free, no API key required
2. **FRED** (Fallback) - Free API key required (https://fred.stlouisfed.org/docs/api/api_key.html)

Set to "Auto" for automatic failover between sources.

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
