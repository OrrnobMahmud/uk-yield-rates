<?php
/**
 * Admin Settings Page
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get current settings
$api_source = get_option('uk_yield_rates_api_source', 'manual');
$fred_api_key = get_option('uk_yield_rates_fred_api_key', '');
$update_interval = get_option('uk_yield_rates_update_interval', '1');
$default_format = get_option('uk_yield_rates_default_format', 'inline');
$decimal_places = get_option('uk_yield_rates_decimal_places', '2');
$show_change = get_option('uk_yield_rates_show_change', 'yes');
$show_last_updated = get_option('uk_yield_rates_show_last_updated', 'yes');
$theme = get_option('uk_yield_rates_theme', 'light');
$cache_duration = get_option('uk_yield_rates_cache_duration', '1');
$auto_refresh = get_option('uk_yield_rates_auto_refresh', 'no');
$refresh_interval = get_option('uk_yield_rates_refresh_interval', '5');

// Get cache info
$cache = UK_Yield_Cache::get_instance();
$cache_info = $cache->get_cache_info();
?>

<div class="wrap">
    <h1><?php echo esc_html__('UK Yield Rates Settings', 'uk-yield-rates'); ?></h1>

    <?php if (isset($_GET['settings-updated'])): ?>
        <div class="notice notice-success">
            <p><?php echo esc_html__('Settings saved.', 'uk-yield-rates'); ?></p>
        </div>
    <?php endif; ?>

    <form method="post" action="options.php">
        <?php settings_fields('uk_yield_rates_settings'); ?>

        <div class="uk-yield-settings-tabs">
            <nav class="nav-tab-wrapper">
                <a href="#api-settings" class="nav-tab nav-tab-active"><?php echo esc_html__('Data Source', 'uk-yield-rates'); ?></a>
                <a href="#display-settings" class="nav-tab"><?php echo esc_html__('Display Settings', 'uk-yield-rates'); ?></a>
                <a href="#advanced-settings" class="nav-tab"><?php echo esc_html__('Advanced', 'uk-yield-rates'); ?></a>
            </nav>

            <div class="tab-content">
                <!-- Data Source Settings Tab -->
                <div id="api-settings" class="tab-panel active">
                    <h2><?php echo esc_html__('Data Source Configuration', 'uk-yield-rates'); ?></h2>

                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php echo esc_html__('Data Source', 'uk-yield-rates'); ?></th>
                            <td>
                                <select name="uk_yield_rates_api_source" id="uk-yield-data-source">
                                    <option value="manual" <?php selected($api_source, 'manual'); ?>><?php echo esc_html__('Manual Entry (Recommended - FREE & Reliable)', 'uk-yield-rates'); ?></option>
                                    <option value="boe_custom" <?php selected($api_source, 'boe_custom'); ?>><?php echo esc_html__('BoE Custom Endpoint (Free - requires setup)', 'uk-yield-rates'); ?></option>
                                    <option value="fred" <?php selected($api_source, 'fred'); ?>><?php echo esc_html__('FRED API (Free tier available)', 'uk-yield-rates'); ?></option>
                                    <option value="auto" <?php selected($api_source, 'auto'); ?>><?php echo esc_html__('Auto (try all sources)', 'uk-yield-rates'); ?></option>
                                </select>
                                <p class="description"><?php echo esc_html__('Manual entry is recommended - just update rates once weekly from Bank of England website. 100% reliable, no API costs.', 'uk-yield-rates'); ?></p>
                            </td>
                        </tr>
                    </table>

                    <!-- Manual Entry Section -->
                    <div id="uk-yield-manual-entry" class="uk-yield-manual-section" style="display: <?php echo ($api_source === 'manual') ? 'block' : 'none'; ?>;">
                        <h3><?php echo esc_html__('Manual Yield Rate Entry', 'uk-yield-rates'); ?></h3>
                        <p class="description"><?php echo esc_html__('Enter current UK gilt yields. Update these when rates change (usually daily). You can find current rates at https://www.bankofengland.co.uk/statistics/yield-curves', 'uk-yield-rates'); ?></p>

                        <table class="form-table">
                            <tr>
                                <th scope="row"><?php echo esc_html__('Data Date', 'uk-yield-rates'); ?></th>
                                <td>
                                    <input type="date" name="uk_yield_rates_manual_date" value="<?php echo esc_attr(get_option('uk_yield_rates_manual_date', date('Y-m-d'))); ?>">
                                    <p class="description"><?php echo esc_html__('Date of the yield rates you are entering.', 'uk-yield-rates'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php echo esc_html__('2-Year Gilt Yield (%)', 'uk-yield-rates'); ?></th>
                                <td>
                                    <input type="number" step="0.01" name="uk_yield_rates_manual_2_yield" value="<?php echo esc_attr(get_option('uk_yield_rates_manual_2_yield', '')); ?>" class="small-text" placeholder="e.g., 4.25">
                                    <input type="number" step="0.01" name="uk_yield_rates_manual_2_change" value="<?php echo esc_attr(get_option('uk_yield_rates_manual_2_change', '0')); ?>" class="small-text" placeholder="Change (e.g., 0.02)">
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php echo esc_html__('5-Year Gilt Yield (%)', 'uk-yield-rates'); ?></th>
                                <td>
                                    <input type="number" step="0.01" name="uk_yield_rates_manual_5_yield" value="<?php echo esc_attr(get_option('uk_yield_rates_manual_5_yield', '')); ?>" class="small-text" placeholder="e.g., 4.15">
                                    <input type="number" step="0.01" name="uk_yield_rates_manual_5_change" value="<?php echo esc_attr(get_option('uk_yield_rates_manual_5_change', '0')); ?>" class="small-text" placeholder="Change (e.g., -0.01)">
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php echo esc_html__('10-Year Gilt Yield (%)', 'uk-yield-rates'); ?></th>
                                <td>
                                    <input type="number" step="0.01" name="uk_yield_rates_manual_10_yield" value="<?php echo esc_attr(get_option('uk_yield_rates_manual_10_yield', '')); ?>" class="small-text" placeholder="e.g., 4.05">
                                    <input type="number" step="0.01" name="uk_yield_rates_manual_10_change" value="<?php echo esc_attr(get_option('uk_yield_rates_manual_10_change', '0')); ?>" class="small-text" placeholder="Change (e.g., 0.00)">
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php echo esc_html__('20-Year Gilt Yield (%)', 'uk-yield-rates'); ?></th>
                                <td>
                                    <input type="number" step="0.01" name="uk_yield_rates_manual_20_yield" value="<?php echo esc_attr(get_option('uk_yield_rates_manual_20_yield', '')); ?>" class="small-text" placeholder="e.g., 4.35">
                                    <input type="number" step="0.01" name="uk_yield_rates_manual_20_change" value="<?php echo esc_attr(get_option('uk_yield_rates_manual_20_change', '0')); ?>" class="small-text" placeholder="Change (e.g., 0.03)">
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php echo esc_html__('30-Year Gilt Yield (%)', 'uk-yield-rates'); ?></th>
                                <td>
                                    <input type="number" step="0.01" name="uk_yield_rates_manual_30_yield" value="<?php echo esc_attr(get_option('uk_yield_rates_manual_30_yield', '')); ?>" class="small-text" placeholder="e.g., 4.45">
                                    <input type="number" step="0.01" name="uk_yield_rates_manual_30_change" value="<?php echo esc_attr(get_option('uk_yield_rates_manual_30_change', '0')); ?>" class="small-text" placeholder="Change (e.g., -0.02)">
                                </td>
                            </tr>
                        </table>

                        <p class="description">
                            <strong><?php echo esc_html__('Quick Update Tip:', 'uk-yield-rates'); ?></strong>
                            <?php echo esc_html__('Check https://www.bankofengland.co.uk/statistics/yield-curves for current rates, then enter them above. The plugin will automatically display them across all your pages!', 'uk-yield-rates'); ?>
                        </p>
                    </div>

                    <!-- BoE Custom Endpoint Section -->
                    <div id="uk-yield-boe-custom-settings" class="uk-yield-boe-custom-section" style="display: <?php echo ($api_source === 'boe_custom' || $api_source === 'auto') ? 'block' : 'none'; ?>;">
                        <h3><?php echo esc_html__('Bank of England Custom Endpoint', 'uk-yield-rates'); ?></h3>
                        <p class="description">
                            <strong><?php echo esc_html__('Recommended free option with some setup:', 'uk-yield-rates'); ?></strong>
                            <?php echo esc_html__('The Bank of England publishes gilt yield data as CSV but has no API. You can host a simple script that fetches the CSV and exposes it as JSON via a free cloud service.', 'uk-yield-rates'); ?>
                        </p>

                        <div style="background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 6px; padding: 15px; margin: 15px 0;">
                            <h4 style="margin-top: 0; color: #0369a1;"><?php echo esc_html__('How it works:', 'uk-yield-rates'); ?></h4>
                            <ol style="margin: 10px 0; padding-left: 20px;">
                                <li><?php echo esc_html__('Deploy a small script to Cloudflare Workers, Vercel, or Netlify (all free tiers)', 'uk-yield-rates'); ?></li>
                                <li><?php echo esc_html__('The script fetches BoE CSV daily and parses yield data', 'uk-yield-rates'); ?></li>
                                <li><?php echo esc_html__('Exposes a JSON endpoint like: https://your-api.workers.dev/yields.json', 'uk-yield-rates'); ?></li>
                                <li><?php echo esc_html__('Enter that URL below and the plugin auto-fetches rates', 'uk-yield-rates'); ?></li>
                            </ol>
                        </div>

                        <table class="form-table">
                            <tr>
                                <th scope="row"><?php echo esc_html__('Endpoint URL', 'uk-yield-rates'); ?></th>
                                <td>
                                    <input type="url" name="uk_yield_rates_boe_custom_endpoint" value="<?php echo esc_attr(get_option('uk_yield_rates_boe_custom_endpoint', '')); ?>" class="regular-text" placeholder="https://your-api.workers.dev/yields.json">
                                    <p class="description"><?php echo esc_html__('The URL of your custom BoE yield data endpoint.', 'uk-yield-rates'); ?></p>
                                </td>
                            </tr>
                        </table>

                        <p class="description">
                            <strong><?php echo esc_html__('Expected JSON format:', 'uk-yield-rates'); ?></strong>
                        </p>
                        <pre style="background: #1e293b; color: #e2e8f0; padding: 15px; border-radius: 6px; overflow-x: auto; font-size: 13px;">{
  "yields": {
    "2": {"yield": 4.25, "change": 0.02, "date": "2026-07-28"},
    "5": {"yield": 4.15, "change": -0.01, "date": "2026-07-28"},
    "10": {"yield": 4.05, "change": 0.00, "date": "2026-07-28"},
    "20": {"yield": 4.35, "change": 0.03, "date": "2026-07-28"},
    "30": {"yield": 4.45, "change": -0.02, "date": "2026-07-28"}
  }
}</pre>

                        <p class="description">
                            <strong><?php echo esc_html__('Sample Cloudflare Worker code:', 'uk-yield-rates'); ?></strong>
                        </p>
                        <pre style="background: #1e293b; color: #e2e8f0; padding: 15px; border-radius: 6px; overflow-x: auto; font-size: 12px;">export default {
  async fetch(request, env, ctx) {
    const csvUrl = 'https://www.bankofengland.co.uk/boeapps/database/_iad-downloadseries.asp?SeriesCodes=IUDM421,IUDM423,IUDM425,IUDM427,IUDM429&CSVF=TN&UsingCodes=Y&Period=Daily';
    const response = await fetch(csvUrl);
    const csv = await response.text();
    // Parse CSV and extract yields...
    return new Response(JSON.stringify({yields: {...}}), {
      headers: {'Content-Type': 'application/json'}
    });
  }
};</pre>

                        <p class="description">
                            <strong><?php echo esc_html__('Benefits:', 'uk-yield-rates'); ?></strong>
                            <ul>
                                <li><?php echo esc_html__('Free - no API keys or costs', 'uk-yield-rates'); ?></li>
                                <li><?php echo esc_html__('Official Bank of England data', 'uk-yield-rates'); ?></li>
                                <li><?php echo esc_html__('Automatic daily updates', 'uk-yield-rates'); ?></li>
                                <li><?php echo esc_html__('You control the data source', 'uk-yield-rates'); ?></li>
                            </ul>
                        </p>
                    </div>

                    <!-- FRED API Section -->
                    <div id="uk-yield-fred-settings" class="uk-yield-fred-section" style="display: <?php echo ($api_source === 'fred' || $api_source === 'auto') ? 'block' : 'none'; ?>;">
                        <h3><?php echo esc_html__('FRED API Configuration', 'uk-yield-rates'); ?></h3>
                        <p class="description">
                            <strong><?php echo esc_html__('Get your free API key:', 'uk-yield-rates'); ?></strong>
                            <?php echo esc_html__('Visit https://fred.stlouisfed.org/docs/api/api_key.html to get a free API key from the Federal Reserve.', 'uk-yield-rates'); ?>
                        </p>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><?php echo esc_html__('FRED API Key', 'uk-yield-rates'); ?></th>
                                <td>
                                    <input type="text" name="uk_yield_rates_fred_api_key" value="<?php echo esc_attr($fred_api_key); ?>" class="regular-text" placeholder="Enter your API key">
                                    <p class="description"><?php echo esc_html__('Paste your API key from FRED dashboard.', 'uk-yield-rates'); ?></p>
                                </td>
                            </tr>
                        </table>
                        <p class="description">
                            <strong><?php echo esc_html__('What you get (Free Tier):', 'uk-yield-rates'); ?></strong>
                            <ul>
                                <li><?php echo esc_html__('UK gilt yield data from Federal Reserve', 'uk-yield-rates'); ?></li>
                                <li><?php echo esc_html__('Multiple maturity options', 'uk-yield-rates'); ?></li>
                                <li><?php echo esc_html__('No credit card required', 'uk-yield-rates'); ?></li>
                            </ul>
                        </p>
                    </div>

                    <div class="uk-yield-cache-info">
                        <h3><?php echo esc_html__('Current Status', 'uk-yield-rates'); ?></h3>
                        <p>
                            <strong><?php echo esc_html__('Has Data:', 'uk-yield-rates'); ?></strong>
                            <?php echo $cache_info['has_cache'] ? '✅' : '❌'; ?>
                        </p>
                        <p>
                            <strong><?php echo esc_html__('Last Updated:', 'uk-yield-rates'); ?></strong>
                            <?php echo $cache_info['last_updated'] ? esc_html($cache_info['last_updated']) : esc_html__('Never', 'uk-yield-rates'); ?>
                        </p>
                        <p>
                            <strong><?php echo esc_html__('Source:', 'uk-yield-rates'); ?></strong>
                            <?php echo esc_html(ucfirst($cache_info['source'] ?? 'N/A')); ?>
                        </p>
                        <p>
                            <strong><?php echo esc_html__('Stale:', 'uk-yield-rates'); ?></strong>
                            <?php echo $cache_info['is_stale'] ? '⚠️ Yes' : '✅ No'; ?>
                        </p>
                        <button type="button" class="button button-secondary" id="uk-yield-refresh-cache">
                            <?php echo esc_html__('Refresh Data', 'uk-yield-rates'); ?>
                        </button>
                    </div>
                </div>

                <!-- Display Settings Tab -->
                <div id="display-settings" class="tab-panel">
                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php echo esc_html__('Default Format', 'uk-yield-rates'); ?></th>
                            <td>
                                <select name="uk_yield_rates_default_format">
                                    <option value="inline" <?php selected($default_format, 'inline'); ?>><?php echo esc_html__('Inline (for paragraphs)', 'uk-yield-rates'); ?></option>
                                    <option value="sidebar" <?php selected($default_format, 'sidebar'); ?>><?php echo esc_html__('Sidebar Widget', 'uk-yield-rates'); ?></option>
                                    <option value="table" <?php selected($default_format, 'table'); ?>><?php echo esc_html__('Table', 'uk-yield-rates'); ?></option>
                                    <option value="compact" <?php selected($default_format, 'compact'); ?>><?php echo esc_html__('Compact', 'uk-yield-rates'); ?></option>
                                </select>
                                <p class="description"><?php echo esc_html__('Default display format for shortcodes.', 'uk-yield-rates'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php echo esc_html__('Decimal Places', 'uk-yield-rates'); ?></th>
                            <td>
                                <select name="uk_yield_rates_decimal_places">
                                    <option value="2" <?php selected($decimal_places, '2'); ?>>2</option>
                                    <option value="3" <?php selected($decimal_places, '3'); ?>>3</option>
                                    <option value="4" <?php selected($decimal_places, '4'); ?>>4</option>
                                </select>
                                <p class="description"><?php echo esc_html__('Number of decimal places for yield values.', 'uk-yield-rates'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php echo esc_html__('Show Change', 'uk-yield-rates'); ?></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="uk_yield_rates_show_change" value="yes" <?php checked($show_change, 'yes'); ?>>
                                    <?php echo esc_html__('Show change indicator (↑↓→)', 'uk-yield-rates'); ?>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php echo esc_html__('Show Last Updated', 'uk-yield-rates'); ?></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="uk_yield_rates_show_last_updated" value="yes" <?php checked($show_last_updated, 'yes'); ?>>
                                    <?php echo esc_html__('Show last updated timestamp', 'uk-yield-rates'); ?>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php echo esc_html__('Theme', 'uk-yield-rates'); ?></th>
                            <td>
                                <select name="uk_yield_rates_theme">
                                    <option value="light" <?php selected($theme, 'light'); ?>><?php echo esc_html__('Light', 'uk-yield-rates'); ?></option>
                                    <option value="dark" <?php selected($theme, 'dark'); ?>><?php echo esc_html__('Dark', 'uk-yield-rates'); ?></option>
                                </select>
                                <p class="description"><?php echo esc_html__('Color theme for yield display.', 'uk-yield-rates'); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Advanced Settings Tab -->
                <div id="advanced-settings" class="tab-panel">
                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php echo esc_html__('Cache Duration', 'uk-yield-rates'); ?></th>
                            <td>
                                <select name="uk_yield_rates_cache_duration">
                                    <option value="1" <?php selected($cache_duration, '1'); ?>><?php echo esc_html__('1 hour', 'uk-yield-rates'); ?></option>
                                    <option value="4" <?php selected($cache_duration, '4'); ?>><?php echo esc_html__('4 hours', 'uk-yield-rates'); ?></option>
                                    <option value="12" <?php selected($cache_duration, '12'); ?>><?php echo esc_html__('12 hours', 'uk-yield-rates'); ?></option>
                                    <option value="24" <?php selected($cache_duration, '24'); ?>><?php echo esc_html__('24 hours', 'uk-yield-rates'); ?></option>
                                </select>
                                <p class="description"><?php echo esc_html__('How long to cache yield data.', 'uk-yield-rates'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php echo esc_html__('Auto-Refresh', 'uk-yield-rates'); ?></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="uk_yield_rates_auto_refresh" value="yes" <?php checked($auto_refresh, 'yes'); ?>>
                                    <?php echo esc_html__('Enable AJAX auto-refresh', 'uk-yield-rates'); ?>
                                </label>
                                <p class="description"><?php echo esc_html__('Automatically refresh yield data on the frontend.', 'uk-yield-rates'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php echo esc_html__('Refresh Interval', 'uk-yield-rates'); ?></th>
                            <td>
                                <select name="uk_yield_rates_refresh_interval">
                                    <option value="5" <?php selected($refresh_interval, '5'); ?>><?php echo esc_html__('Every 5 minutes', 'uk-yield-rates'); ?></option>
                                    <option value="15" <?php selected($refresh_interval, '15'); ?>><?php echo esc_html__('Every 15 minutes', 'uk-yield-rates'); ?></option>
                                    <option value="30" <?php selected($refresh_interval, '30'); ?>><?php echo esc_html__('Every 30 minutes', 'uk-yield-rates'); ?></option>
                                    <option value="60" <?php selected($refresh_interval, '60'); ?>><?php echo esc_html__('Every hour', 'uk-yield-rates'); ?></option>
                                </select>
                                <p class="description"><?php echo esc_html__('How often to refresh data when auto-refresh is enabled.', 'uk-yield-rates'); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <?php submit_button(); ?>
    </form>

    <div class="uk-yield-shortcodes-help">
        <h2><?php echo esc_html__('Quick Start Guide', 'uk-yield-rates'); ?></h2>

        <div class="uk-yield-quickstart">
            <h3><?php echo esc_html__('Option A: Manual Updates (Recommended - FREE & Reliable)', 'uk-yield-rates'); ?></h3>
            <ol>
                <li><?php echo esc_html__('Select "Manual Entry" as data source above', 'uk-yield-rates'); ?></li>
                <li><?php echo esc_html__('Go to https://www.bankofengland.co.uk/statistics/yield-curves', 'uk-yield-rates'); ?></li>
                <li><?php echo esc_html__('Enter current yields for 2Y, 5Y, 10Y, 20Y, and 30Y', 'uk-yield-rates'); ?></li>
                <li><?php echo esc_html__('Click "Save Changes"', 'uk-yield-rates'); ?></li>
                <li><?php echo esc_html__('Update once weekly - all pages auto-update!', 'uk-yield-rates'); ?></li>
            </ol>

            <h3><?php echo esc_html__('Option B: Automatic Updates with FRED API (Free)', 'uk-yield-rates'); ?></h3>
            <ol>
                <li><?php echo esc_html__('Go to https://fred.stlouisfed.org/docs/api/api_key.html', 'uk-yield-rates'); ?></li>
                <li><?php echo esc_html__('Sign up for a free API key', 'uk-yield-rates'); ?></li>
                <li><?php echo esc_html__('Select "FRED API" as data source above', 'uk-yield-rates'); ?></li>
                <li><?php echo esc_html__('Paste your API key and click "Save Changes"', 'uk-yield-rates'); ?></li>
            </ol>

            <h3><?php echo esc_html__('Step 2: Use Shortcodes in Your Content', 'uk-yield-rates'); ?></h3>
            <p><?php echo esc_html__('Add these shortcodes anywhere in your pages or posts:', 'uk-yield-rates'); ?></p>
        </div>

        <div class="uk-yield-examples">
            <h3><?php echo esc_html__('For Service Pages (Recommended)', 'uk-yield-rates'); ?></h3>
            <code>[uk_yield_rates inline="yes" maturity="10"]</code>
            <p><?php echo esc_html__('Example: "Our mortgage rates are influenced by the [uk_yield_rates inline="yes" maturity="10"] 10-year gilt yield."', 'uk-yield-rates'); ?></p>

            <h3><?php echo esc_html__('Sidebar Widget', 'uk-yield-rates'); ?></h3>
            <code>[uk_yield_rates format="sidebar"]</code>

            <h3><?php echo esc_html__('Full Table', 'uk-yield-rates'); ?></h3>
            <code>[uk_yield_rates format="table"]</code>

            <h3><?php echo esc_html__('Multiple Maturities Inline', 'uk-yield-rates'); ?></h3>
            <code>[uk_yield_rates inline="yes" maturity="2,5,10"]</code>
        </div>

        <div class="uk-yield-quickstart">
            <h3><?php echo esc_html__('Step 3: Update Rates (When They Change)', 'uk-yield-rates'); ?></h3>
            <ol>
                <li><?php echo esc_html__('Check the Bank of England yield curves page periodically', 'uk-yield-rates'); ?></li>
                <li><?php echo esc_html__('Update the rates in the plugin settings', 'uk-yield-rates'); ?></li>
                <li><?php echo esc_html__('All your pages will automatically show the new rates!', 'uk-yield-rates'); ?></li>
            </ol>

            <p><strong><?php echo esc_html__('Pro Tip:', 'uk-yield-rates'); ?></strong> <?php echo esc_html__('Set a calendar reminder to update rates weekly or whenever Bank of England announces changes.', 'uk-yield-rates'); ?></p>
        </div>
    </div>

    <div class="uk-yield-shortcodes-help">
        <h2><?php echo esc_html__('Shortcode Reference', 'uk-yield-rates'); ?></h2>
        <div class="uk-yield-examples">
            <h3><?php echo esc_html__('All Available Options', 'uk-yield-rates'); ?></h3>
            <code>[uk_yield_rates inline="yes" maturity="10" format="inline" show_change="yes" decimal="2" theme="light"]</code>

            <p><strong><?php echo esc_html__('Attributes:', 'uk-yield-rates'); ?></strong></p>
            <ul>
                <li><code>maturity</code> - <?php echo esc_html__('all, 2, 5, 10, 20, 30 (or comma-separated)', 'uk-yield-rates'); ?></li>
                <li><code>format</code> - <?php echo esc_html__('inline, sidebar, table, compact', 'uk-yield-rates'); ?></li>
                <li><code>show_change</code> - <?php echo esc_html__('yes, no', 'uk-yield-rates'); ?></li>
                <li><code>decimal</code> - <?php echo esc_html__('2, 3, 4', 'uk-yield-rates'); ?></li>
                <li><code>theme</code> - <?php echo esc_html__('light, dark', 'uk-yield-rates'); ?></li>
            </ul>
        </div>
    </div>
</div>
