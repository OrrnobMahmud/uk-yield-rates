<?php
/**
 * Admin Settings for UK Yield Rates
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
        register_setting('uk_yield_rates_settings', 'uk_yield_rates_api_source');
        register_setting('uk_yield_rates_settings', 'uk_yield_rates_fred_api_key');
        register_setting('uk_yield_rates_settings', 'uk_yield_rates_update_interval');

        // Display Settings
        register_setting('uk_yield_rates_settings', 'uk_yield_rates_default_format');
        register_setting('uk_yield_rates_settings', 'uk_yield_rates_decimal_places');
        register_setting('uk_yield_rates_settings', 'uk_yield_rates_show_change');
        register_setting('uk_yield_rates_settings', 'uk_yield_rates_show_last_updated');
        register_setting('uk_yield_rates_settings', 'uk_yield_rates_theme');

        // Advanced Settings
        register_setting('uk_yield_rates_settings', 'uk_yield_rates_cache_duration');
        register_setting('uk_yield_rates_settings', 'uk_yield_rates_auto_refresh');
        register_setting('uk_yield_rates_settings', 'uk_yield_rates_refresh_interval');
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

        wp_localize_script('uk_yield_admin', 'ukYieldAdmin', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('uk_yield_rates_nonce'),
        ]);
    }

    /**
     * Render settings page
     */
    public function render_settings_page() {
        include UK_YIELD_RATES_PLUGIN_DIR . 'admin/views/settings-page.php';
    }
}
