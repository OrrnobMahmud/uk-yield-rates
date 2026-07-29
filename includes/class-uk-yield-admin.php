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
        register_setting('uk_yield_rates_settings', 'uk_yield_rates_boe_custom_endpoint');
        register_setting('uk_yield_rates_settings', 'uk_yield_rates_fred_api_key');
        register_setting('uk_yield_rates_settings', 'uk_yield_rates_update_interval');

        // Manual Entry Settings
        register_setting('uk_yield_rates_settings', 'uk_yield_rates_manual_date');
        register_setting('uk_yield_rates_settings', 'uk_yield_rates_manual_2_yield');
        register_setting('uk_yield_rates_settings', 'uk_yield_rates_manual_2_change');
        register_setting('uk_yield_rates_settings', 'uk_yield_rates_manual_5_yield');
        register_setting('uk_yield_rates_settings', 'uk_yield_rates_manual_5_change');
        register_setting('uk_yield_rates_settings', 'uk_yield_rates_manual_10_yield');
        register_setting('uk_yield_rates_settings', 'uk_yield_rates_manual_10_change');
        register_setting('uk_yield_rates_settings', 'uk_yield_rates_manual_20_yield');
        register_setting('uk_yield_rates_settings', 'uk_yield_rates_manual_20_change');
        register_setting('uk_yield_rates_settings', 'uk_yield_rates_manual_30_yield');
        register_setting('uk_yield_rates_settings', 'uk_yield_rates_manual_30_change');

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
     * Render settings page
     */
    public function render_settings_page() {
        include UK_YIELD_RATES_PLUGIN_DIR . 'admin/views/settings-page.php';
    }
}
