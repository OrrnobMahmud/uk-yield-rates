<?php
/**
 * Admin Settings for UK Yield Rates
 *
 * @package UK_Yield_Rates
 * @version 1.3.2
 * @license GPL-2.0-or-later
 * @author Orrnob Mahmud
 */

if (!defined('ABSPATH')) {
    exit;
}

class UK_Yield_Admin {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('admin_menu', [$this, 'add_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_action('admin_init', [$this, 'handle_settings_save']);
    }

    /**
     * Add admin menu
     */
    public function add_menu() {
        add_options_page(
            'UK Yield Rates',
            'UK Yield Rates',
            'manage_options',
            'uk-yield-rates',
            [$this, 'render_settings_page']
        );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        // API Settings
        register_setting('uk_yield_rates_settings', 'uk_yield_rates_api_source', [
            'sanitize_callback' => [$this, 'sanitize_api_source'],
            'default'           => 'manual',
        ]);
        register_setting('uk_yield_rates_settings', 'uk_yield_rates_boe_custom_endpoint', [
            'sanitize_callback' => 'esc_url_raw',
            'default'           => '',
        ]);
        register_setting('uk_yield_rates_settings', 'uk_yield_rates_fred_api_key', [
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '',
        ]);
        register_setting('uk_yield_rates_settings', 'uk_yield_rates_update_interval', [
            'sanitize_callback' => [$this, 'sanitize_interval'],
            'default'           => '1',
        ]);

        // Manual Entry Settings
        register_setting('uk_yield_rates_settings', 'uk_yield_rates_manual_date', [
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => gmdate('Y-m-d'),
        ]);
        register_setting('uk_yield_rates_settings', 'uk_yield_rates_manual_2_yield', [
            'sanitize_callback' => [$this, 'sanitize_yield'],
            'default'           => '',
        ]);
        register_setting('uk_yield_rates_settings', 'uk_yield_rates_manual_2_change', [
            'sanitize_callback' => [$this, 'sanitize_change'],
            'default'           => '0',
        ]);
        register_setting('uk_yield_rates_settings', 'uk_yield_rates_manual_5_yield', [
            'sanitize_callback' => [$this, 'sanitize_yield'],
            'default'           => '',
        ]);
        register_setting('uk_yield_rates_settings', 'uk_yield_rates_manual_5_change', [
            'sanitize_callback' => [$this, 'sanitize_change'],
            'default'           => '0',
        ]);
        register_setting('uk_yield_rates_settings', 'uk_yield_rates_manual_10_yield', [
            'sanitize_callback' => [$this, 'sanitize_yield'],
            'default'           => '',
        ]);
        register_setting('uk_yield_rates_settings', 'uk_yield_rates_manual_10_change', [
            'sanitize_callback' => [$this, 'sanitize_change'],
            'default'           => '0',
        ]);
        register_setting('uk_yield_rates_settings', 'uk_yield_rates_manual_20_yield', [
            'sanitize_callback' => [$this, 'sanitize_yield'],
            'default'           => '',
        ]);
        register_setting('uk_yield_rates_settings', 'uk_yield_rates_manual_20_change', [
            'sanitize_callback' => [$this, 'sanitize_change'],
            'default'           => '0',
        ]);
        register_setting('uk_yield_rates_settings', 'uk_yield_rates_manual_30_yield', [
            'sanitize_callback' => [$this, 'sanitize_yield'],
            'default'           => '',
        ]);
        register_setting('uk_yield_rates_settings', 'uk_yield_rates_manual_30_change', [
            'sanitize_callback' => [$this, 'sanitize_change'],
            'default'           => '0',
        ]);

        // Display Settings
        register_setting('uk_yield_rates_settings', 'uk_yield_rates_default_format', [
            'sanitize_callback' => [$this, 'sanitize_format'],
            'default'           => 'inline',
        ]);
        register_setting('uk_yield_rates_settings', 'uk_yield_rates_decimal_places', [
            'sanitize_callback' => [$this, 'sanitize_decimal_places'],
            'default'           => '2',
        ]);
        register_setting('uk_yield_rates_settings', 'uk_yield_rates_show_change', [
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => 'yes',
        ]);
        register_setting('uk_yield_rates_settings', 'uk_yield_rates_show_last_updated', [
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => 'yes',
        ]);
        register_setting('uk_yield_rates_settings', 'uk_yield_rates_theme', [
            'sanitize_callback' => [$this, 'sanitize_theme'],
            'default'           => 'light',
        ]);

        // Advanced Settings
        register_setting('uk_yield_rates_settings', 'uk_yield_rates_cache_duration', [
            'sanitize_callback' => [$this, 'sanitize_cache_duration'],
            'default'           => '1',
        ]);
        register_setting('uk_yield_rates_settings', 'uk_yield_rates_auto_refresh', [
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => 'no',
        ]);
        register_setting('uk_yield_rates_settings', 'uk_yield_rates_refresh_interval', [
            'sanitize_callback' => [$this, 'sanitize_refresh_interval'],
            'default'           => '5',
        ]);
    }

    /**
     * Sanitize API source
     */
    public function sanitize_api_source($value) {
        $allowed = ['manual', 'boe_direct', 'boe_custom', 'fred', 'auto'];
        return in_array($value, $allowed, true) ? $value : 'manual';
    }

    /**
     * Sanitize interval
     */
    public function sanitize_interval($value) {
        $value = intval($value);
        return ($value >= 1 && $value <= 24) ? (string) $value : '1';
    }

    /**
     * Sanitize yield value
     */
    public function sanitize_yield($value) {
        $value = sanitize_text_field($value);
        return is_numeric($value) ? $value : '';
    }

    /**
     * Sanitize change value
     */
    public function sanitize_change($value) {
        $value = sanitize_text_field($value);
        return is_numeric($value) ? $value : '0';
    }

    /**
     * Sanitize display format
     */
    public function sanitize_format($value) {
        $allowed = ['inline', 'sidebar', 'table', 'compact'];
        return in_array($value, $allowed, true) ? $value : 'inline';
    }

    /**
     * Sanitize decimal places
     */
    public function sanitize_decimal_places($value) {
        $value = intval($value);
        return in_array($value, [2, 3, 4], true) ? (string) $value : '2';
    }

    /**
     * Sanitize theme
     */
    public function sanitize_theme($value) {
        $allowed = ['light', 'dark'];
        return in_array($value, $allowed, true) ? $value : 'light';
    }

    /**
     * Sanitize cache duration
     */
    public function sanitize_cache_duration($value) {
        $value = intval($value);
        return in_array($value, [1, 4, 12, 24], true) ? (string) $value : '1';
    }

    /**
     * Sanitize refresh interval
     */
    public function sanitize_refresh_interval($value) {
        $value = intval($value);
        return in_array($value, [5, 15, 30, 60], true) ? (string) $value : '5';
    }

    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        if ($hook !== 'settings_page_uk-yield-rates') {
            return;
        }

        wp_enqueue_style(
            'uk-yield-admin',
            UK_YIELD_RATES_PLUGIN_URL . 'admin/css/admin.css',
            [],
            UK_YIELD_RATES_VERSION
        );

        wp_enqueue_script(
            'uk-yield-admin',
            UK_YIELD_RATES_PLUGIN_URL . 'admin/js/admin.js',
            ['jquery'],
            UK_YIELD_RATES_VERSION,
            true
        );

        wp_localize_script('uk-yield-admin', 'ukYieldAdmin', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('uk_yield_rates_nonce'),
            'pluginVersion' => UK_YIELD_RATES_VERSION,
            'wpVersion' => get_bloginfo('version'),
            'phpVersion' => phpversion(),
            'theme' => wp_get_theme()->get('Name'),
            'githubRepo' => 'https://github.com/OrrnobMahmud/uk-yield-rates',
            'i18n' => [
                'reportBug' => __('Report a Bug', 'uk-yield-rates'),
                'requestFeature' => __('Request a Feature', 'uk-yield-rates'),
                'bugTitlePlaceholder' => __('Brief description of the issue', 'uk-yield-rates'),
                'bugDescPlaceholder' => __('Detailed description of the bug...', 'uk-yield-rates'),
                'featureTitlePlaceholder' => __('Feature name', 'uk-yield-rates'),
                'featureDescPlaceholder' => __('Describe the feature you\'d like...', 'uk-yield-rates'),
                'enterTitle' => __('Please enter an issue title.', 'uk-yield-rates'),
                'enterDescription' => __('Please provide a description.', 'uk-yield-rates'),
                'issueOpened' => __('✓ GitHub issue page opened! Please submit the issue there.', 'uk-yield-rates'),
            ],
        ]);
    }

    /**
     * Handle settings save - set transient for success notice
     */
    public function handle_settings_save() {
        if (isset($_GET['settings-updated']) && 'true' === sanitize_text_field(wp_unslash($_GET['settings-updated']))) {
            set_transient('uk_yield_rates_settings_notice', 'updated', 30);
        }
    }

    /**
     * Render settings page
     */
    public function render_settings_page() {
        include UK_YIELD_RATES_PLUGIN_DIR . 'admin/views/settings-page.php';
    }
}
