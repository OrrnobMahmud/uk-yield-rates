<?php
/**
 * Admin Settings Page
 *
 * @package UK_Yield_Rates
 * @version 2.0.0
 * @license GPL-2.0-or-later
 * @author Orrnob Mahmud
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get current settings
$uk_yield_rates_api_url = get_option('uk_yield_rates_api_url', '');
$uk_yield_rates_api_key = get_option('uk_yield_rates_api_key', '');
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
                <a href="#api-settings" class="nav-tab nav-tab-active"><?php echo esc_html__('API Connection', 'uk-yield-rates'); ?></a>
                <a href="#display-settings" class="nav-tab"><?php echo esc_html__('Display Settings', 'uk-yield-rates'); ?></a>
                <a href="#advanced-settings" class="nav-tab"><?php echo esc_html__('Advanced', 'uk-yield-rates'); ?></a>
            </nav>

            <div class="tab-content">
                <!-- API Connection Tab -->
                <div id="api-settings" class="tab-panel active">
                    <h2><?php echo esc_html__('API Configuration', 'uk-yield-rates'); ?></h2>

                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php echo esc_html__('API URL', 'uk-yield-rates'); ?></th>
                            <td>
                                <input type="url" name="uk_yield_rates_api_url" value="<?php echo esc_attr($uk_yield_rates_api_url); ?>" class="regular-text" placeholder="https://yield-api-production-9355.up.railway.app">
                                <p class="description"><?php echo esc_html__('The URL of your UK Yield Rates API instance.', 'uk-yield-rates'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php echo esc_html__('API Key', 'uk-yield-rates'); ?></th>
                            <td>
                                <input type="text" name="uk_yield_rates_api_key" value="<?php echo esc_attr($uk_yield_rates_api_key); ?>" class="regular-text" placeholder="Optional - for refresh endpoint">
                                <p class="description"><?php echo esc_html__('Optional API key for manual refresh functionality.', 'uk-yield-rates'); ?></p>
                            </td>
                        </tr>
                    </table>

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
                        <?php if (!empty($uk_yield_rates_api_key)): ?>
                            <button type="button" class="button button-secondary" id="uk-yield-trigger-refresh" style="margin-left: 10px;">
                                <?php echo esc_html__('Force Server Refresh', 'uk-yield-rates'); ?>
                            </button>
                        <?php endif; ?>
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
                                <p class="description"><?php echo esc_html__('How long to cache yield data from API.', 'uk-yield-rates'); ?></p>
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
            <h3><?php echo esc_html__('Setup (2 minutes)', 'uk-yield-rates'); ?></h3>
            <ol>
                <li><?php echo esc_html__('Enter your API URL in the settings above', 'uk-yield-rates'); ?></li>
                <li><?php echo esc_html__('Click "Save Changes"', 'uk-yield-rates'); ?></li>
                <li><?php echo esc_html__('Click "Refresh Data" to fetch initial data', 'uk-yield-rates'); ?></li>
            </ol>

            <h3><?php echo esc_html__('Use Shortcodes in Your Content', 'uk-yield-rates'); ?></h3>
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

        <div class="uk-yield-shortcodes-help">
            <h2><?php echo esc_html__('Shortcode Reference', 'uk-yield-rates'); ?></h2>
            <div class="uk-yield-examples">
                <h3><?php echo esc_html__('All Available Options', 'uk-yield-rates'); ?></h3>
                <code>[uk_yield_rates inline="yes" maturity="10" format="inline" show_change="yes" show_updated="yes" decimal="2" theme="light"]</code>

                <p><strong><?php echo esc_html__('Attributes:', 'uk-yield-rates'); ?></strong></p>
                <ul>
                    <li><code>maturity</code> - <?php echo esc_html__('all, 6M, 1Y, 2Y, 3Y, 4Y, 5Y, 6Y, 7Y, 8Y, 9Y, 10Y, 15Y, 20Y, 25Y, 30Y, 40Y (or comma-separated)', 'uk-yield-rates'); ?></li>
                    <li><code>format</code> - <?php echo esc_html__('inline, sidebar, table, compact', 'uk-yield-rates'); ?></li>
                    <li><code>show_change</code> - <?php echo esc_html__('yes, no', 'uk-yield-rates'); ?></li>
                    <li><code>show_updated</code> - <?php echo esc_html__('yes, no', 'uk-yield-rates'); ?></li>
                    <li><code>decimal</code> - <?php echo esc_html__('2, 3, 4', 'uk-yield-rates'); ?></li>
                    <li><code>theme</code> - <?php echo esc_html__('light, dark', 'uk-yield-rates'); ?></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="uk-yield-support">
        <h2><?php echo esc_html__('Support & Feedback', 'uk-yield-rates'); ?></h2>

        <div class="uk-yield-support-content">
            <div class="uk-yield-support-section">
                <h3><?php echo esc_html__('Report a Bug', 'uk-yield-rates'); ?></h3>
                <p><?php echo esc_html__('Found a bug? Help us improve the plugin by reporting it on GitHub.', 'uk-yield-rates'); ?></p>
                <button type="button" class="button button-secondary uk-yield-report-bug" id="uk-yield-report-bug">
                    <?php echo esc_html__('Report Bug on GitHub', 'uk-yield-rates'); ?>
                </button>
            </div>

            <div class="uk-yield-support-section">
                <h3><?php echo esc_html__('Feature Request', 'uk-yield-rates'); ?></h3>
                <p><?php echo esc_html__('Have an idea for improvement? Suggest a new feature on GitHub.', 'uk-yield-rates'); ?></p>
                <button type="button" class="button button-secondary uk-yield-report-bug" id="uk-yield-request-feature">
                    <?php echo esc_html__('Request Feature on GitHub', 'uk-yield-rates'); ?>
                </button>
            </div>

            <div class="uk-yield-support-section">
                <h3><?php echo esc_html__('Documentation', 'uk-yield-rates'); ?></h3>
                <p><?php echo esc_html__('View the README for comprehensive documentation.', 'uk-yield-rates'); ?></p>
                <a href="https://github.com/OrrnobMahmud/uk-yield-rates#readme" target="_blank" class="button button-secondary">
                    <?php echo esc_html__('View Documentation', 'uk-yield-rates'); ?>
                </a>
            </div>
        </div>
    </div>
</div>
