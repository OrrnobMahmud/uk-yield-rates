<?php
/**
 * Admin Settings Page
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get current settings
$api_source = get_option('uk_yield_rates_api_source', 'auto');
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
                <a href="#api-settings" class="nav-tab nav-tab-active"><?php echo esc_html__('API Settings', 'uk-yield-rates'); ?></a>
                <a href="#display-settings" class="nav-tab"><?php echo esc_html__('Display Settings', 'uk-yield-rates'); ?></a>
                <a href="#advanced-settings" class="nav-tab"><?php echo esc_html__('Advanced', 'uk-yield-rates'); ?></a>
            </nav>

            <div class="tab-content">
                <!-- API Settings Tab -->
                <div id="api-settings" class="tab-panel active">
                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php echo esc_html__('API Source', 'uk-yield-rates'); ?></th>
                            <td>
                                <select name="uk_yield_rates_api_source">
                                    <option value="auto" <?php selected($api_source, 'auto'); ?>><?php echo esc_html__('Auto (Bank of England + FRED)', 'uk-yield-rates'); ?></option>
                                    <option value="boe" <?php selected($api_source, 'boe'); ?>><?php echo esc_html__('Bank of England Only', 'uk-yield-rates'); ?></option>
                                    <option value="fred" <?php selected($api_source, 'fred'); ?>><?php echo esc_html__('FRED Only', 'uk-yield-rates'); ?></option>
                                </select>
                                <p class="description"><?php echo esc_html__('Choose which API to use for fetching yield data.', 'uk-yield-rates'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php echo esc_html__('FRED API Key', 'uk-yield-rates'); ?></th>
                            <td>
                                <input type="text" name="uk_yield_rates_fred_api_key" value="<?php echo esc_attr($fred_api_key); ?>" class="regular-text">
                                <p class="description"><?php echo esc_html__('Required if using FRED API. Get your free API key from https://fred.stlouisfed.org/docs/api/api_key.html', 'uk-yield-rates'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php echo esc_html__('Update Interval', 'uk-yield-rates'); ?></th>
                            <td>
                                <select name="uk_yield_rates_update_interval">
                                    <option value="1" <?php selected($update_interval, '1'); ?>><?php echo esc_html__('Every hour', 'uk-yield-rates'); ?></option>
                                    <option value="4" <?php selected($update_interval, '4'); ?>><?php echo esc_html__('Every 4 hours', 'uk-yield-rates'); ?></option>
                                    <option value="12" <?php selected($update_interval, '12'); ?>><?php echo esc_html__('Every 12 hours', 'uk-yield-rates'); ?></option>
                                    <option value="24" <?php selected($update_interval, '24'); ?>><?php echo esc_html__('Daily', 'uk-yield-rates'); ?></option>
                                </select>
                                <p class="description"><?php echo esc_html__('How often to fetch new yield data.', 'uk-yield-rates'); ?></p>
                            </td>
                        </tr>
                    </table>

                    <div class="uk-yield-cache-info">
                        <h3><?php echo esc_html__('Cache Status', 'uk-yield-rates'); ?></h3>
                        <p>
                            <strong><?php echo esc_html__('Has Cache:', 'uk-yield-rates'); ?></strong>
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
                            <?php echo esc_html__('Force Refresh Cache', 'uk-yield-rates'); ?>
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
        <h2><?php echo esc_html__('Shortcode Usage', 'uk-yield-rates'); ?></h2>
        <div class="uk-yield-examples">
            <h3><?php echo esc_html__('Inline (for paragraphs)', 'uk-yield-rates'); ?></h3>
            <code>[uk_yield_rates inline="yes" maturity="10"]</code>

            <h3><?php echo esc_html__('Sidebar Widget', 'uk-yield-rates'); ?></h3>
            <code>[uk_yield_rates format="sidebar"]</code>

            <h3><?php echo esc_html__('Full Table', 'uk-yield-rates'); ?></h3>
            <code>[uk_yield_rates format="table"]</code>

            <h3><?php echo esc_html__('Multiple Maturities Inline', 'uk-yield-rates'); ?></h3>
            <code>[uk_yield_rates inline="yes" maturity="2,5,10"]</code>
        </div>
    </div>
</div>
