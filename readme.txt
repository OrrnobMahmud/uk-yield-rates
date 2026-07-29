=== UK Yield Rates Live ===
Contributors: orrnobmahmud
Donate link: https://orrnobmahmud.com
Tags: yield rates, gilts, bonds, finance, uk
Requires at least: 5.0
Tested up to: 7.0
Stable tag: 1.3.1
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Display live UK gilt yield rates using shortcodes and Gutenberg blocks.

== Description ==

UK Yield Rates Live is a WordPress plugin that displays real-time UK government bond (gilt) yield rates on your website. Perfect for financial advisors, mortgage brokers, investment platforms, and financial news sites.

**✨ NEW: Text Style Inheritance** - Shortcodes now automatically inherit text styles from their surrounding context! Place yields in headers, bold text, or any styled element and they'll match perfectly.

**Features:**

* **Text Style Inheritance** - Yields inherit font weight, size, and color from parent elements
* **Multiple Data Sources** - Manual entry (recommended), BoE custom endpoint, or FRED API
* **Multiple Display Formats** - Inline, sidebar widget, table, and compact layouts
* **Gutenberg Block** - Visual editor with live preview and theme preview
* **Shortcode Support** - Works with classic editor and page builders
* **Auto-Refresh** - Optional AJAX-based automatic updates
* **Responsive Design** - Looks great on all devices
* **Light/Dark Themes** - Match your site's design
* **Bug Reporting** - Report issues directly to GitHub from admin
* **Feature Requests** - Suggest improvements via GitHub integration

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

No API key is required. The recommended method is **Manual Entry** - just enter yields from the Bank of England website once a week. For automatic updates, you can set up a free BoE Custom Endpoint (Cloudflare Workers or Vercel) or use the free FRED API.

= How often is the data updated? =

By default, data is cached for 1 hour. You can configure this in the plugin settings (1-24 hours). Auto-refresh can update data every 5-60 minutes on the frontend.

= Does this affect page load speed? =

No. The plugin uses efficient caching and loads yield data asynchronously when auto-refresh is enabled.

= Is it translation ready? =

Yes. The plugin is fully internationalized and ready for translation.

= What is text style inheritance? =

When you place a shortcode inside a styled element (like a header or bold text), the yield values automatically inherit the font weight, size, and color from the parent element. For example, placing the shortcode in an `<h2>` tag will make the yields display in the h2 style.

= How do I report a bug? =

Go to Settings > UK Yield Rates and click "Report Bug on GitHub". A form will open with pre-filled system information. Fill in the details and click "Open GitHub Issue" to submit it directly to GitHub.

= Can I request new features? =

Yes! Go to Settings > UK Yield Rates and click "Request Feature on GitHub". Describe your idea and submit it as a GitHub issue.

== Screenshots ==

1. Inline display in paragraph content with text inheritance
2. Sidebar widget format
3. Full table layout with change indicators
4. Gutenberg block with live preview and theme selection
5. Admin settings page with data source options
6. Mobile responsive design
7. Dark theme preview
8. Bug reporting modal with system info

== Changelog ==

= 1.3.1 =
* Fixed auto-refresh not working for anonymous (logged-out) visitors

= 1.3.0 =
* **NEW:** Automatic BoE ZIP download and Excel parsing
* **NEW:** File upload import for ZIP, XLSX, and CSV files
* **NEW:** Unified provider interface for all data sources
* Cloudflare Worker marked experimental

= 1.2.0 =
* Fixed wp_localize_script handle mismatch (frontend and admin)
* Fixed version constant mismatch
* Fixed build script packaging

= 1.1.0 =
* **NEW:** Text style inheritance - yields inherit font styles from parent elements
* **NEW:** GitHub bug reporting - report bugs directly from admin
* **NEW:** Feature request system - suggest improvements via GitHub
* **NEW:** Block editor preview modes - live data, sample data, custom shortcode
* **NEW:** Theme preview in block editor
* Improved admin UI with validation and loading states
* Added frontend animations and tooltips
* Fixed auto-refresh maturity parameter issue
* Fixed weekend cache duration override
* Eliminated duplicate shortcode builder code
* Added XSS sanitization for block editor
* Improved responsive design
* Enhanced security measures

= 1.0.0 =
* Initial release
* Manual yield rate entry (recommended)
* BoE Custom Endpoint for automatic updates
* FRED API integration
* Multiple display formats (inline, sidebar, table, compact)
* Gutenberg block with live preview
* Admin settings page
* Responsive design
* Light/Dark themes
* Auto-refresh functionality

== Upgrade Notice ==

= 1.3.1 =
Fixes auto-refresh for anonymous visitors.

= 1.3.0 =
Automatic BoE data download and file upload import.

= 1.2.0 =
Critical fixes for script loading and version constants.

= 1.1.0 =
Major update with text inheritance, GitHub integration, and improved UI.

= 1.0.0 =
Initial release of UK Yield Rates Live plugin.

== Author ==

[Orrnob Mahmud Local SEO Strategist](https://orrnobmahmud.com)

== Credits ==

* [Bank of England](https://www.bankofengland.co.uk/) - Official yield data
* [FRED](https://fred.stlouisfed.org/) - Federal Reserve Economic Data
* [WordPress](https://wordpress.org/) - Amazing platform

== Donate ==

If you find this plugin useful, please consider [starring the GitHub repository](https://github.com/OrrnobMahmud/uk-yield-rates) to support continued development.
