# UK Yield Rates Live

A WordPress plugin to display UK government bond (gilt) yield rates using shortcodes and Gutenberg blocks. Perfect for estate agents, mortgage brokers, and financial advisors who need to show current yield rates in their website content.

## Features

- **Manual Data Entry** - Most reliable method (FREE)
- **BoE Custom Endpoint** - Free with Cloudflare/Vercel (recommended free auto option)
- **FRED API** - Free tier available
- **Multiple Display Formats** - Inline, sidebar widget, table, and compact layouts
- **Gutenberg Block** - Visual editor with live preview
- **Shortcode Support** - Works with classic editor and page builders
- **Responsive Design** - Looks great on all devices
- **Light/Dark Themes** - Match your site's design

## Data Source Options

### Option A: Manual Entry (Recommended - FREE)
✅ **100% reliable** - no API dependencies  
✅ **No cost** - completely free  
✅ **Easy maintenance** - update once weekly  
✅ **Works immediately** - no setup required  

### Option B: BoE Custom Endpoint (FREE - Best Free Auto Option)
✅ **Official Bank of England data**  
✅ **Free** - no API keys or costs  
✅ **Automatic daily updates**  
✅ **You control the data source**  
⚠️ Requires 30 minutes setup  
⚠️ Need to deploy a small script  

### Option C: FRED API (FREE tier)
✅ Free API key from Federal Reserve  
✅ UK gilt yield data available  
⚠️ Limited free requests per day  
⚠️ Requires API key setup  

### Option D: Apify Scraper (~$0.40/month)
✅ Automated data fetching  
✅ Uses official Bank of England data  
⚠️ Costs $4.98 per 1,000 results  
⚠️ May break if BoE changes website  

## Quick Start

### Option A: Manual Updates (Recommended)

1. Go to **Settings > UK Yield Rates** in WordPress admin
2. Select "Manual Entry" as data source
3. Visit https://www.bankofengland.co.uk/statistics/yield-curves
4. Enter the current yields for 2Y, 5Y, 10Y, 20Y, and 30Y maturities
5. Click "Save Changes"
6. Update when rates change (set a weekly reminder)

### Option B: BoE Custom Endpoint (Best Free Auto Option)

1. Deploy a small script to Cloudflare Workers, Vercel, or Netlify (all free tiers)
2. The script fetches BoE CSV daily and parses yield data
3. Exposes a JSON endpoint like: `https://your-api.workers.dev/yields.json`
4. Go to **Settings > UK Yield Rates** in WordPress admin
5. Select "BoE Custom Endpoint" as data source
6. Enter your endpoint URL and click "Save Changes"

See the admin settings page for sample Cloudflare Worker code and expected JSON format.

### Option C: Automatic Updates with FRED API

1. Go to https://fred.stlouisfed.org/docs/api/api_key.html
2. Sign up for a free API key
3. Go to **Settings > UK Yield Rates** in WordPress admin
4. Select "FRED API" as data source
5. Enter your API key and click "Save Changes"

### Use Shortcodes in Your Content

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

## Requirements

- WordPress 5.0+
- PHP 7.4+

## License

GPL v2 or later - see [LICENSE](LICENSE) for details

## Author

[Orrnob Mahmud Local SEO Strategist](https://orrnobmahmud.com)
