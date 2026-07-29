<?php
/**
 * Admin Settings Page
 *
 * @package UK_Yield_Rates
 * @version 1.3.1
 * @license GPL-2.0-or-later
 * @author Orrnob Mahmud
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get current settings
$uk_yield_rates_api_source = get_option('uk_yield_rates_api_source', 'manual');
$uk_yield_rates_fred_api_key = get_option('uk_yield_rates_fred_api_key', '');
$uk_yield_rates_update_interval = get_option('uk_yield_rates_update_interval', '1');
$uk_yield_rates_default_format = get_option('uk_yield_rates_default_format', 'inline');
$uk_yield_rates_decimal_places = get_option('uk_yield_rates_decimal_places', '2');
$uk_yield_rates_show_change = get_option('uk_yield_rates_show_change', 'yes');
$uk_yield_rates_show_last_updated = get_option('uk_yield_rates_show_last_updated', 'yes');
$uk_yield_rates_theme = get_option('uk_yield_rates_theme', 'light');
$uk_yield_rates_cache_duration = get_option('uk_yield_rates_cache_duration', '1');
$uk_yield_rates_auto_refresh = get_option('uk_yield_rates_auto_refresh', 'no');
$uk_yield_rates_refresh_interval = get_option('uk_yield_rates_refresh_interval', '5');

// Get cache info
$uk_yield_rates_cache = UK_Yield_Cache::get_instance();
$uk_yield_rates_cache_info = $uk_yield_rates_cache->get_cache_info();
?>

<div class="wrap">
    <h1><?php echo esc_html__('UK Yield Rates Settings', 'uk-yield-rates'); ?></h1>

    <?php if ($uk_yield_rates_notice = get_transient('uk_yield_rates_settings_notice')): ?>
        <div class="notice notice-success is-dismissible">
            <p><?php echo esc_html__('Settings saved.', 'uk-yield-rates'); ?></p>
        </div>
        <?php delete_transient('uk_yield_rates_settings_notice'); ?>
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
                                    <option value="manual" <?php selected($uk_yield_rates_api_source, 'manual'); ?>><?php echo esc_html__('Manual Entry (Recommended - FREE & Reliable)', 'uk-yield-rates'); ?></option>
                                    <option value="boe_direct" <?php selected($uk_yield_rates_api_source, 'boe_direct'); ?>><?php echo esc_html__('BoE Direct Download (Automatic - FREE)', 'uk-yield-rates'); ?></option>
                                    <option value="boe_custom" <?php selected($uk_yield_rates_api_source, 'boe_custom'); ?>><?php echo esc_html__('BoE Custom Endpoint (Free - requires setup)', 'uk-yield-rates'); ?></option>
                                    <option value="fred" <?php selected($uk_yield_rates_api_source, 'fred'); ?>><?php echo esc_html__('FRED API (Free tier available)', 'uk-yield-rates'); ?></option>
                                    <option value="auto" <?php selected($uk_yield_rates_api_source, 'auto'); ?>><?php echo esc_html__('Auto (try all sources)', 'uk-yield-rates'); ?></option>
                                </select>
                                <p class="description"><?php echo esc_html__('Manual entry is recommended - just update rates once weekly from Bank of England website. 100% reliable, no API costs.', 'uk-yield-rates'); ?></p>
                            </td>
                        </tr>
                    </table>

                    <!-- BoE Direct Download Section -->
                    <div id="uk-yield-boe-direct-settings" class="uk-yield-boe-direct-section" style="display: <?php echo ($uk_yield_rates_api_source === 'boe_direct') ? 'block' : 'none'; ?>;">
                        <h3><?php echo esc_html__('Bank of England Direct Download', 'uk-yield-rates'); ?></h3>
                        <p class="description">
                            <strong><?php echo esc_html__('Automatic updates from official source:', 'uk-yield-rates'); ?></strong>
                            <?php echo esc_html__('The plugin will automatically download the latest yield curve data directly from the Bank of England website. No API key or external service required.', 'uk-yield-rates'); ?>
                        </p>

                        <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px; padding: 15px; margin: 15px 0;">
                            <h4 style="margin-top: 0; color: #166534;"><?php echo esc_html__('How it works:', 'uk-yield-rates'); ?></h4>
                            <ol style="margin: 10px 0; padding-left: 20px;">
                                <li><?php echo esc_html__('Plugin downloads the official BoE yield curve ZIP archive', 'uk-yield-rates'); ?></li>
                                <li><?php echo esc_html__('Extracts the GLC Nominal workbook', 'uk-yield-rates'); ?></li>
                                <li><?php echo esc_html__('Parses yield data for 2Y, 5Y, 10Y, 20Y, and 30Y maturities', 'uk-yield-rates'); ?></li>
                                <li><?php echo esc_html__('Automatically updates all shortcodes and blocks', 'uk-yield-rates'); ?></li>
                            </ol>
                        </div>

                        <p class="description">
                            <strong><?php echo esc_html__('Requirements:', 'uk-yield-rates'); ?></strong>
                            <ul>
                                <li><?php echo esc_html__('PHP Zip extension (usually enabled by default)', 'uk-yield-rates'); ?></li>
                                <li><?php echo esc_html__('Outbound HTTP requests enabled (for downloading from BoE)', 'uk-yield-rates'); ?></li>
                            </ul>
                        </p>
                    </div>

                    <!-- Manual Entry Section -->
                    <div id="uk-yield-manual-entry" class="uk-yield-manual-section" style="display: <?php echo ($uk_yield_rates_api_source === 'manual') ? 'block' : 'none'; ?>;">
                        <h3><?php echo esc_html__('Manual Yield Rate Entry', 'uk-yield-rates'); ?></h3>
                        <p class="description"><?php echo esc_html__('Enter current UK gilt yields. Update these when rates change (usually daily). Find current rates from your broker, financial news sites, or search "UK gilt yields today". The Bank of England publishes yield curves (charts), not individual values.', 'uk-yield-rates'); ?></p>

                        <table class="form-table">
                            <tr>
                                <th scope="row"><?php echo esc_html__('Data Date', 'uk-yield-rates'); ?></th>
                                <td>
                                    <input type="date" name="uk_yield_rates_manual_date" value="<?php echo esc_attr(get_option('uk_yield_rates_manual_date', gmdate('Y-m-d'))); ?>">
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
                            <?php echo esc_html__('Search "UK gilt yields today" for current rates from financial news sites, or check your broker platform. The plugin will automatically display them across all your pages!', 'uk-yield-rates'); ?>
                        </p>
                    </div>

                    <!-- Import Section -->
                    <div id="uk-yield-import-section" class="uk-yield-import-section" style="display: block; margin-top: 20px;">
                        <h3><?php echo esc_html__('Import Yield Data', 'uk-yield-rates'); ?></h3>
                        <p class="description"><?php echo esc_html__('Import yield data from Bank of England ZIP archives, Excel spreadsheets, or CSV files.', 'uk-yield-rates'); ?></p>

                        <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px; padding: 15px; margin: 15px 0;">
                            <h4 style="margin-top: 0; color: #166534;"><?php echo esc_html__('Automatic Download (Recommended)', 'uk-yield-rates'); ?></h4>
                            <p style="margin-bottom: 10px;"><?php echo esc_html__('Download the latest official Bank of England yield curve data automatically.', 'uk-yield-rates'); ?></p>
                            <button type="button" class="button button-primary" id="uk-yield-auto-download">
                                <?php echo esc_html__('Download from Bank of England', 'uk-yield-rates'); ?>
                            </button>
                            <span id="uk-yield-auto-download-status" style="margin-left: 10px;"></span>
                        </div>

                        <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 6px; padding: 15px; margin: 15px 0;">
                            <h4 style="margin-top: 0; color: #92400e;"><?php echo esc_html__('Manual File Upload', 'uk-yield-rates'); ?></h4>
                            <p style="margin-bottom: 10px;"><?php echo esc_html__('Upload a Bank of England ZIP archive, Excel file (.xlsx), or CSV file with yield data.', 'uk-yield-rates'); ?></p>
                            <form id="uk-yield-import-form" enctype="multipart/form-data" style="display: flex; align-items: center; gap: 10px;">
                                <input type="file" name="yield_file" id="uk-yield-file-input" accept=".zip,.xlsx,.xls,.csv" style="flex: 1;">
                                <button type="submit" class="button button-secondary" id="uk-yield-import-btn">
                                    <?php echo esc_html__('Upload & Import', 'uk-yield-rates'); ?>
                                </button>
                            </form>
                            <span id="uk-yield-import-status" style="display: block; margin-top: 10px;"></span>
                            <p class="description" style="margin-top: 10px;">
                                <?php echo esc_html__('Supported formats: ZIP (BoE archive), XLSX, CSV. Maximum file size: 10MB.', 'uk-yield-rates'); ?>
                            </p>
                        </div>
                    </div>

                    <!-- BoE Custom Endpoint Section -->
                    <div id="uk-yield-boe-custom-settings" class="uk-yield-boe-custom-section" style="display: <?php echo ($uk_yield_rates_api_source === 'boe_custom' || $uk_yield_rates_api_source === 'auto') ? 'block' : 'none'; ?>;">
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
                    <div id="uk-yield-fred-settings" class="uk-yield-fred-section" style="display: <?php echo ($uk_yield_rates_api_source === 'fred' || $uk_yield_rates_api_source === 'auto') ? 'block' : 'none'; ?>;">
                        <h3><?php echo esc_html__('FRED API Configuration', 'uk-yield-rates'); ?></h3>
                        <p class="description">
                            <strong><?php echo esc_html__('Get your free API key:', 'uk-yield-rates'); ?></strong>
                            <?php echo esc_html__('Visit https://fred.stlouisfed.org/docs/api/api_key.html to get a free API key from the Federal Reserve.', 'uk-yield-rates'); ?>
                        </p>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><?php echo esc_html__('FRED API Key', 'uk-yield-rates'); ?></th>
                                <td>
                                    <input type="text" name="uk_yield_rates_fred_api_key" value="<?php echo esc_attr($uk_yield_rates_fred_api_key); ?>" class="regular-text" placeholder="Enter your API key">
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
                            <?php echo $uk_yield_rates_cache_info['has_cache'] ? '✅' : '❌'; ?>
                        </p>
                        <p>
                            <strong><?php echo esc_html__('Last Updated:', 'uk-yield-rates'); ?></strong>
                            <?php echo $uk_yield_rates_cache_info['last_updated'] ? esc_html($uk_yield_rates_cache_info['last_updated']) : esc_html__('Never', 'uk-yield-rates'); ?>
                        </p>
                        <p>
                            <strong><?php echo esc_html__('Source:', 'uk-yield-rates'); ?></strong>
                            <?php echo esc_html(ucfirst($uk_yield_rates_cache_info['source'] ?? 'N/A')); ?>
                        </p>
                        <p>
                            <strong><?php echo esc_html__('Stale:', 'uk-yield-rates'); ?></strong>
                            <?php echo $uk_yield_rates_cache_info['is_stale'] ? '⚠️ Yes' : '✅ No'; ?>
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
                                    <option value="inline" <?php selected($uk_yield_rates_default_format, 'inline'); ?>><?php echo esc_html__('Inline (for paragraphs)', 'uk-yield-rates'); ?></option>
                                    <option value="sidebar" <?php selected($uk_yield_rates_default_format, 'sidebar'); ?>><?php echo esc_html__('Sidebar Widget', 'uk-yield-rates'); ?></option>
                                    <option value="table" <?php selected($uk_yield_rates_default_format, 'table'); ?>><?php echo esc_html__('Table', 'uk-yield-rates'); ?></option>
                                    <option value="compact" <?php selected($uk_yield_rates_default_format, 'compact'); ?>><?php echo esc_html__('Compact', 'uk-yield-rates'); ?></option>
                                </select>
                                <p class="description"><?php echo esc_html__('Default display format for shortcodes.', 'uk-yield-rates'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php echo esc_html__('Decimal Places', 'uk-yield-rates'); ?></th>
                            <td>
                                <select name="uk_yield_rates_decimal_places">
                                    <option value="2" <?php selected($uk_yield_rates_decimal_places, '2'); ?>>2</option>
                                    <option value="3" <?php selected($uk_yield_rates_decimal_places, '3'); ?>>3</option>
                                    <option value="4" <?php selected($uk_yield_rates_decimal_places, '4'); ?>>4</option>
                                </select>
                                <p class="description"><?php echo esc_html__('Number of decimal places for yield values.', 'uk-yield-rates'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php echo esc_html__('Show Change', 'uk-yield-rates'); ?></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="uk_yield_rates_show_change" value="yes" <?php checked($uk_yield_rates_show_change, 'yes'); ?>>
                                    <?php echo esc_html__('Show change indicator (↑↓→)', 'uk-yield-rates'); ?>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php echo esc_html__('Show Last Updated', 'uk-yield-rates'); ?></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="uk_yield_rates_show_last_updated" value="yes" <?php checked($uk_yield_rates_show_last_updated, 'yes'); ?>>
                                    <?php echo esc_html__('Show last updated timestamp', 'uk-yield-rates'); ?>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php echo esc_html__('Theme', 'uk-yield-rates'); ?></th>
                            <td>
                                <select name="uk_yield_rates_theme">
                                    <option value="light" <?php selected($uk_yield_rates_theme, 'light'); ?>><?php echo esc_html__('Light', 'uk-yield-rates'); ?></option>
                                    <option value="dark" <?php selected($uk_yield_rates_theme, 'dark'); ?>><?php echo esc_html__('Dark', 'uk-yield-rates'); ?></option>
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
                                    <option value="1" <?php selected($uk_yield_rates_cache_duration, '1'); ?>><?php echo esc_html__('1 hour', 'uk-yield-rates'); ?></option>
                                    <option value="4" <?php selected($uk_yield_rates_cache_duration, '4'); ?>><?php echo esc_html__('4 hours', 'uk-yield-rates'); ?></option>
                                    <option value="12" <?php selected($uk_yield_rates_cache_duration, '12'); ?>><?php echo esc_html__('12 hours', 'uk-yield-rates'); ?></option>
                                    <option value="24" <?php selected($uk_yield_rates_cache_duration, '24'); ?>><?php echo esc_html__('24 hours', 'uk-yield-rates'); ?></option>
                                </select>
                                <p class="description"><?php echo esc_html__('How long to cache yield data.', 'uk-yield-rates'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php echo esc_html__('Auto-Refresh', 'uk-yield-rates'); ?></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="uk_yield_rates_auto_refresh" value="yes" <?php checked($uk_yield_rates_auto_refresh, 'yes'); ?>>
                                    <?php echo esc_html__('Enable AJAX auto-refresh', 'uk-yield-rates'); ?>
                                </label>
                                <p class="description"><?php echo esc_html__('Automatically refresh yield data on the frontend.', 'uk-yield-rates'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php echo esc_html__('Refresh Interval', 'uk-yield-rates'); ?></th>
                            <td>
                                <select name="uk_yield_rates_refresh_interval">
                                    <option value="5" <?php selected($uk_yield_rates_refresh_interval, '5'); ?>><?php echo esc_html__('Every 5 minutes', 'uk-yield-rates'); ?></option>
                                    <option value="15" <?php selected($uk_yield_rates_refresh_interval, '15'); ?>><?php echo esc_html__('Every 15 minutes', 'uk-yield-rates'); ?></option>
                                    <option value="30" <?php selected($uk_yield_rates_refresh_interval, '30'); ?>><?php echo esc_html__('Every 30 minutes', 'uk-yield-rates'); ?></option>
                                    <option value="60" <?php selected($uk_yield_rates_refresh_interval, '60'); ?>><?php echo esc_html__('Every hour', 'uk-yield-rates'); ?></option>
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
                <li><?php echo esc_html__('Search "UK gilt yields today" or check your broker platform for current rates', 'uk-yield-rates'); ?></li>
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

    <div class="uk-yield-support">
        <h2><?php echo esc_html__('Support & Feedback', 'uk-yield-rates'); ?></h2>

        <div class="uk-yield-support-content">
            <div class="uk-yield-support-section">
                <h3><?php echo esc_html__('🐛 Report a Bug', 'uk-yield-rates'); ?></h3>
                <p><?php echo esc_html__('Found a bug? Help us improve the plugin by reporting it on GitHub.', 'uk-yield-rates'); ?></p>
                <button type="button" class="button button-secondary uk-yield-report-bug" id="uk-yield-report-bug">
                    <?php echo esc_html__('Report Bug on GitHub', 'uk-yield-rates'); ?>
                </button>
            </div>

            <div class="uk-yield-support-section">
                <h3><?php echo esc_html__('💡 Feature Request', 'uk-yield-rates'); ?></h3>
                <p><?php echo esc_html__('Have an idea for improvement? Suggest a new feature on GitHub.', 'uk-yield-rates'); ?></p>
                <button type="button" class="button button-secondary uk-yield-report-bug" id="uk-yield-request-feature">
                    <?php echo esc_html__('Request Feature on GitHub', 'uk-yield-rates'); ?>
                </button>
            </div>

            <div class="uk-yield-support-section">
                <h3><?php echo esc_html__('📚 Documentation', 'uk-yield-rates'); ?></h3>
                <p><?php echo esc_html__('View the README for comprehensive documentation.', 'uk-yield-rates'); ?></p>
                <a href="https://github.com/OrrnobMahmud/uk-yield-rates#readme" target="_blank" class="button button-secondary">
                    <?php echo esc_html__('View Documentation', 'uk-yield-rates'); ?>
                </a>
            </div>
        </div>
    </div>

    <!-- Bug Report Modal -->
    <div id="uk-yield-bug-report-modal" class="uk-yield-modal" style="display: none;">
        <div class="uk-yield-modal-content">
            <div class="uk-yield-modal-header">
                <h2 id="uk-yield-modal-title"><?php echo esc_html__('Report a Bug', 'uk-yield-rates'); ?></h2>
                <button type="button" class="uk-yield-modal-close" id="uk-yield-modal-close">&times;</button>
            </div>
            <div class="uk-yield-modal-body">
                <div class="uk-yield-form-group">
                    <label for="uk-yield-bug-title"><?php echo esc_html__('Issue Title', 'uk-yield-rates'); ?></label>
                    <input type="text" id="uk-yield-bug-title" class="regular-text" placeholder="<?php echo esc_attr__('Brief description of the issue', 'uk-yield-rates'); ?>">
                    <p class="description"><?php echo esc_html__('A concise summary of the problem', 'uk-yield-rates'); ?></p>
                </div>

                <div class="uk-yield-form-group">
                    <label for="uk-yield-bug-description"><?php echo esc_html__('Description', 'uk-yield-rates'); ?></label>
                    <textarea id="uk-yield-bug-description" rows="8" class="large-text" placeholder="<?php echo esc_attr__('Detailed description of the bug...', 'uk-yield-rates'); ?>"></textarea>
                    <p class="description"><?php echo esc_html__('Steps to reproduce, expected behavior, and what actually happened', 'uk-yield-rates'); ?></p>
                </div>

                <div class="uk-yield-form-group">
                    <label for="uk-yield-bug-steps"><?php echo esc_html__('Steps to Reproduce', 'uk-yield-rates'); ?></label>
                    <textarea id="uk-yield-bug-steps" rows="5" class="large-text" placeholder="1. Go to Settings > UK Yield Rates&#10;2. Select 'Manual Entry'&#10;3. Enter yield values&#10;4. Click Save Changes"></textarea>
                </div>

                <div class="uk-yield-form-group">
                    <label for="uk-yield-bug-expected"><?php echo esc_html__('Expected Behavior', 'uk-yield-rates'); ?></label>
                    <textarea id="uk-yield-bug-expected" rows="3" class="large-text" placeholder="<?php echo esc_attr__('What you expected to happen', 'uk-yield-rates'); ?>"></textarea>
                </div>

                <div class="uk-yield-form-group">
                    <label><?php echo esc_html__('System Information (Auto-detected)', 'uk-yield-rates'); ?></label>
                    <div class="uk-yield-system-info">
                        <p><strong>Plugin Version:</strong> <?php echo esc_html(UK_YIELD_RATES_VERSION); ?></p>
                        <p><strong>WordPress Version:</strong> <?php echo esc_html(get_bloginfo('version')); ?></p>
                        <p><strong>PHP Version:</strong> <?php echo esc_html(phpversion()); ?></p>
                        <p><strong>Active Theme:</strong> <?php echo esc_html(wp_get_theme()->get('Name')); ?></p>
                        <p><strong>Active Plugins:</strong> <?php echo esc_html(implode(', ', array_map(function($plugin) { return $plugin['Name']; }, get_plugins()))); ?></p>
                    </div>
                </div>

                <div class="uk-yield-form-group">
                    <label>
                        <input type="checkbox" id="uk-yield-bug-include-system" checked>
                        <?php echo esc_html__('Include system information in the report', 'uk-yield-rates'); ?>
                    </label>
                </div>
            </div>
            <div class="uk-yield-modal-footer">
                <button type="button" class="button" id="uk-yield-modal-cancel"><?php echo esc_html__('Cancel', 'uk-yield-rates'); ?></button>
                <button type="button" class="button button-primary" id="uk-yield-modal-submit"><?php echo esc_html__('Open GitHub Issue', 'uk-yield-rates'); ?></button>
            </div>
        </div>
    </div>
</div>
