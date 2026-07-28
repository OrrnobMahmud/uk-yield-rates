=== UK Yield Rates Live ===
Contributors: orrnobmahmud
Donate link: https://orrnobmahmud.com
Tags: yield rates, gilts, bonds, finance, uk
Requires at least: 5.0
Tested up to: 6.6
Stable tag: 1.0.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Display live UK government bond (gilt) yield rates using shortcodes and Gutenberg blocks.

== Description ==

UK Yield Rates Live is a WordPress plugin that displays real-time UK government bond (gilt) yield rates on your website. Perfect for financial advisors, mortgage brokers, investment platforms, and financial news sites.

**Features:**

* **Dual API Support** - Automatically fetches data from Bank of England and FRED APIs with failover
* **Multiple Display Formats** - Inline, sidebar widget, table, and compact layouts
* **Gutenberg Block** - Visual editor with live preview
* **Shortcode Support** - Works with classic editor and page builders
* **Auto-Refresh** - Optional AJAX-based automatic updates
* **Responsive Design** - Looks great on all devices
* **Light/Dark Themes** - Match your site's design
* **Translation Ready** - Fully internationalized

**Perfect for:**

* Financial advisor websites
* Mortgage broker service pages
* Investment platform dashboards
* Financial news and blog posts
* Local SEO service pages

**Yield Maturities:**

* 2-Year Gilt Yield
* 5-Year Gilt Yield
* 10-Year Gilt Yield (benchmark)
* 20-Year Gilt Yield
* 30-Year Gilt Yield

== Installation ==

1. Upload the `uk-yield-rates` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings > UK Yield Rates to configure the plugin
4. Use shortcodes or the Gutenberg block to display yield rates

== Frequently Asked Questions ==

= How do I display yield rates? =

Use the shortcode `[uk_yield_rates]` or add the "UK Yield Rates" block in the Gutenberg editor.

= What shortcodes are available? =

* `[uk_yield_rates]` - Default inline display
* `[uk_yield_rates inline="yes" maturity="10"]` - Single maturity inline
* `[uk_yield_rates format="sidebar"]` - Sidebar widget
* `[uk_yield_rates format="table"]` - Full table display
* `[uk_yield_rates format="compact"]` - Compact single line

= Can I customize the display? =

Yes! Use these attributes:

* `maturity` - "all", "2", "5", "10", "20", "30", or comma-separated (e.g., "2,5,10")
* `format` - "inline", "sidebar", "table", or "compact"
* `show_change` - "yes" or "no"
* `decimal` - "2", "3", or "4"
* `theme` - "light" or "dark"

= Do I need an API key? =

The plugin works with the Bank of England API without a key. If you want to use the FRED API as a backup, you'll need a free API key from https://fred.stlouisfed.org/docs/api/api_key.html

= How often is the data updated? =

By default, data is cached for 1 hour during market hours and 24 hours on weekends. You can configure this in the plugin settings.

= Does this affect page load speed? =

No. The plugin uses efficient caching and loads yield data asynchronously when auto-refresh is enabled.

= Is it translation ready? =

Yes. The plugin is fully internationalized and ready for translation.

== Screenshots ==

1. Inline display in paragraph content
2. Sidebar widget format
3. Full table layout
4. Gutenberg block with live preview
5. Admin settings page
6. Mobile responsive design

== Changelog ==

= 1.0.0 =
* Initial release
* Bank of England API integration
* FRED API integration with auto-failover
* Multiple display formats (inline, sidebar, table, compact)
* Gutenberg block with live preview
* Admin settings page
* Responsive design
* Light/Dark themes
* Auto-refresh functionality

== Upgrade Notice ==

= 1.0.0 =
Initial release of UK Yield Rates Live plugin.
