# UK Yield Rates

**Contributors:** orrnobmahmud
**Tags:** yield rates, gilts, bonds, finance, uk
**Requires at least:** 5.0
**Tested up to:** 6.8
**Stable tag:** 2.1.0
**Requires PHP:** 7.4
**License:** GPLv2 or later
**License URI:** https://www.gnu.org/licenses/gpl-2.0.html

Display live UK Government Bond (Gilt) yield rates in WordPress. Perfect for mortgage brokers, financial advisors, and property websites.

## The Problem

Mortgage and property service pages need current gilt yields to stay relevant. Without automation, this means manually updating dozens of pages every time rates change.

## The Solution

A WordPress plugin that displays Bank of England yield data via shortcodes. Two modes:

- **Manual Entry (Free)** - Enter yields yourself. No API required.
- **API Mode (Automatic)** - Connect to a Yield API for automatic updates.

## Installation

1. Download the plugin ZIP from [GitHub Releases](https://github.com/OrrnobMahmud/uk-yield-rates/releases)
2. In WordPress admin, go to **Plugins → Add New → Upload Plugin**
3. Upload the ZIP file and click **Install Now**
4. Click **Activate Plugin**

## Configuration

Go to **Settings → UK Yield Rates**:

### Manual Entry Mode (Default)

1. Select **Manual Entry** as data source
2. Enter current yields for 2Y, 5Y, 10Y, 20Y, 30Y
3. Click **Save Changes**

Find current rates by searching "UK gilt yields today" or checking your broker platform.

### API Mode (Automatic)

1. Select **API** as data source
2. Enter your API URL
3. Click **Save Changes**
4. Click **Refresh Data** to fetch initial yields

## Usage

### Shortcodes

**Single maturity (inline):**
```
[uk_yield_rates format="inline" maturity="10Y"]
```

**Multiple maturities:**
```
[uk_yield_rates format="inline" maturity="2Y,5Y,10Y,20Y,30Y"]
```

**Full table:**
```
[uk_yield_rates format="table"]
```

**Key maturities table:**
```
[uk_yield_rates format="table" maturity="2Y,5Y,10Y,20Y,30Y"]
```

**Compact single line:**
```
[uk_yield_rates format="compact"]
```

**Sidebar widget:**
```
[uk_yield_rates format="sidebar"]
```

### Options

| Attribute | Values | Default | Description |
|-----------|--------|---------|-------------|
| `format` | inline, table, compact, sidebar | inline | Display format |
| `maturity` | all, 2Y, 5Y, 10Y, 20Y, 30Y | all | Which maturities to show |
| `show_change` | yes, no | yes | Show change indicator |
| `show_updated` | yes, no | yes | Show last updated time |
| `decimal` | 2, 3, 4 | 2 | Decimal places |
| `theme` | light, dark | light | Color theme |

### Example in Content

> Our mortgage rates are influenced by the [uk_yield_rates format="inline" maturity="10Y"] 10-year gilt yield.

## Available Maturities

- **Short-term:** 6M, 1Y, 2Y, 3Y, 4Y, 5Y
- **Medium-term:** 6Y, 7Y, 8Y, 9Y, 10Y
- **Long-term:** 11Y, 12Y, 13Y, 14Y, 15Y, 20Y, 25Y, 30Y, 40Y

Manual entry mode supports: 2Y, 5Y, 10Y, 20Y, 30Y

## Requirements

- WordPress 5.0+
- PHP 7.4+

## Support

- [Report a Bug](https://github.com/OrrnobMahmud/uk-yield-rates/issues/new?labels=bug)
- [Request a Feature](https://github.com/OrrnobMahmud/uk-yield-rates/issues/new?labels=enhancement)

## License

GPL v2 or later. See [LICENSE](LICENSE) for details.
