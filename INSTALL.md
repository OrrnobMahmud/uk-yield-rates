# Installation Guide

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher

## Install from ZIP

1. Download the latest release ZIP from [GitHub Releases](https://github.com/OrrnobMahmud/uk-yield-rates/releases)
2. Log in to your WordPress admin dashboard
3. Navigate to **Plugins → Add New**
4. Click **Upload Plugin** button
5. Click **Choose File** and select the downloaded ZIP
6. Click **Install Now**
7. Click **Activate Plugin**

## First-Time Setup

1. Go to **Settings → UK Yield Rates**
2. Choose your data source:
   - **Manual Entry** (default) - Enter yields yourself
   - **API** - Connect to a Yield API for automatic updates
3. Click **Save Changes**

## Manual Entry Mode

1. Search "UK gilt yields today" or check your broker
2. Enter yields for 2Y, 5Y, 10Y, 20Y, 30Y
3. Set the data date
4. Click **Save Changes**

## API Mode

1. Select **API** as data source
2. Enter your API URL
3. Optionally enter an API key (for refresh functionality)
4. Click **Save Changes**
5. Click **Refresh Data** to fetch initial yields

## Verify Installation

Add a shortcode to any page or post:

```
[uk_yield_rates format="table"]
```

If you see a yield table, the plugin is working correctly.

## Troubleshooting

**No data displayed:**
- Check that you've entered yields (Manual mode) or configured API URL (API mode)
- Click **Refresh Data** in settings

**Shortcode shows error:**
- Verify the plugin is activated
- Check **Settings → UK Yield Rates** for configuration

**API connection fails:**
- Verify the API URL is correct
- Check that the API server is running
- Test the URL in your browser: `{API_URL}/api/v1/health`
