<?php
/**
 * Plugin Name: UK Yield Rates Live
 * Plugin URI: https://orrnobmahmud.com/uk-yield-rates
 * Description: Display live UK government bond (gilt) yield rates using shortcodes and Gutenberg blocks. Perfect for financial advisors, mortgage brokers, and investment platforms.
 * Version: 1.0.0
 * Author: Orrnob Mahmud Local SEO Strategist
 * Author URI: https://orrnobmahmud.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: uk-yield-rates
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Plugin constants
define('UK_YIELD_RATES_VERSION', '1.0.0');
define('UK_YIELD_RATES_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('UK_YIELD_RATES_PLUGIN_URL', plugin_dir_url(__FILE__));
define('UK_YIELD_RATES_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main plugin class
 */
final class UK_Yield_Rates {

    /**
     * Single instance of the class
     */
    private static $instance = null;

    /**
     * Get single instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->includes();
        $this->init_hooks();
    }

    /**
     * Include required files
     */
    private function includes() {
        require_once UK_YIELD_RATES_PLUGIN_DIR . 'includes/class-uk-yield-api.php';
        require_once UK_YIELD_RATES_PLUGIN_DIR . 'includes/class-uk-yield-cache.php';
        require_once UK_YIELD_RATES_PLUGIN_DIR . 'includes/class-uk-yield-shortcode.php';
        require_once UK_YIELD_RATES_PLUGIN_DIR . 'includes/class-uk-yield-admin.php';
        require_once UK_YIELD_RATES_PLUGIN_DIR . 'includes/class-uk-yield-block.php';
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Initialize components
        add_action('init', [$this, 'init']);

        // Activation/Deactivation hooks
        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);

        // Load text domain
        add_action('plugins_loaded', [$this, 'load_textdomain']);

        // AJAX handlers
        add_action('wp_ajax_uk_yield_refresh_cache', [$this, 'ajax_refresh_cache']);
        add_action('wp_ajax_uk_yield_refresh', [$this, 'ajax_refresh_yields']);
        add_action('wp_ajax_uk_yield_render_preview', [$this, 'ajax_render_preview']);

        // Enqueue frontend scripts
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_scripts']);
    }

    /**
     * Initialize components
     */
    public function init() {
        // Initialize shortcode
        UK_Yield_Shortcode::get_instance();

        // Initialize admin
        if (is_admin()) {
            UK_Yield_Admin::get_instance();
        }

        // Initialize Gutenberg block
        UK_Yield_Block::get_instance();
    }

    /**
     * Enqueue frontend scripts
     */
    public function enqueue_frontend_scripts() {
        wp_enqueue_script(
            'uk-yield-rates',
            UK_YIELD_RATES_PLUGIN_URL . 'public/js/yield-rates.js',
            [],
            UK_YIELD_RATES_VERSION,
            true
        );

        wp_localize_script('uk_yield_rates', 'ukYieldRates', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('uk_yield_rates_nonce'),
            'auto_refresh' => get_option('uk_yield_rates_auto_refresh', 'no'),
            'refresh_interval' => get_option('uk_yield_rates_refresh_interval', '5'),
        ]);
    }

    /**
     * AJAX: Refresh cache
     */
    public function ajax_refresh_cache() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'uk_yield_rates_nonce')) {
            wp_send_json_error(__('Security check failed.', 'uk-yield-rates'));
        }

        // Check user permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions.', 'uk-yield-rates'));
        }

        $cache = UK_Yield_Cache::get_instance();
        $success = $cache->force_refresh();

        if ($success) {
            wp_send_json_success(__('Cache refreshed successfully.', 'uk-yield-rates'));
        } else {
            wp_send_json_error(__('Failed to refresh cache. Please check API settings.', 'uk-yield-rates'));
        }
    }

    /**
     * AJAX: Refresh yields (for frontend auto-refresh)
     */
    public function ajax_refresh_yields() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'uk_yield_rates_nonce')) {
            wp_send_json_error(__('Security check failed.', 'uk-yield-rates'));
        }

        $cache = UK_Yield_Cache::get_instance();
        $data = $cache->get_yields();

        if (!$data) {
            wp_send_json_error(__('Failed to fetch yield data.', 'uk-yield-rates'));
        }

        // Render shortcodes
        $shortcode_atts = [
            'maturity' => sanitize_text_field($_POST['maturity'] ?? 'all'),
            'format' => sanitize_text_field($_POST['format'] ?? 'inline'),
            'show_change' => 'yes',
            'show_updated' => 'yes',
            'decimal' => get_option('uk_yield_rates_decimal_places', '2'),
        ];

        $shortcode = UK_Yield_Shortcode::get_instance();
        $html = $shortcode->render_shortcode($shortcode_atts);

        wp_send_json_success(['html' => $html]);
    }

    /**
     * AJAX: Render preview (for Gutenberg block)
     */
    public function ajax_render_preview() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'uk_yield_rates_nonce')) {
            wp_send_json_error(__('Security check failed.', 'uk-yield-rates'));
        }

        $shortcode = sanitize_text_field($_POST['shortcode'] ?? '');

        if (empty($shortcode)) {
            wp_send_json_error(__('No shortcode provided.', 'uk-yield-rates'));
        }

        // Parse shortcode
        $shortcode_atts = shortcode_parse_atts($shortcode);

        if (!$shortcode_atts || !is_array($shortcode_atts)) {
            wp_send_json_error(__('Invalid shortcode.', 'uk-yield-rates'));
        }

        // Remove the shortcode tag
        unset($shortcode_atts[0]);

        // Add defaults
        $shortcode_atts = shortcode_atts([
            'maturity' => 'all',
            'format' => 'inline',
            'show_change' => 'yes',
            'show_updated' => 'yes',
            'decimal' => get_option('uk_yield_rates_decimal_places', '2'),
            'theme' => get_option('uk_yield_rates_theme', 'light'),
        ], $shortcode_atts);

        $shortcode_handler = UK_Yield_Shortcode::get_instance();
        $html = $shortcode_handler->render_shortcode($shortcode_atts);

        wp_send_json_success(['html' => $html]);
    }

    /**
     * Plugin activation
     */
    public function activate() {
        // Set default options
        $defaults = [
            'api_source' => 'financeflow', // financeflow, manual, auto, fred
            'update_interval' => '1', // hours
            'cache_duration' => '1', // hours
            'default_format' => 'inline',
            'decimal_places' => '2',
            'show_change' => 'yes',
            'show_last_updated' => 'yes',
            'theme' => 'light',
            'auto_refresh' => 'no',
            'refresh_interval' => '5', // minutes
            'manual_date' => date('Y-m-d'),
        ];

        foreach ($defaults as $key => $value) {
            if (get_option('uk_yield_rates_' . $key) === false) {
                update_option('uk_yield_rates_' . $key, $value);
            }
        }

        // Set default manual yield values
        $manual_defaults = [
            '2' => ['yield' => '', 'change' => '0'],
            '5' => ['yield' => '', 'change' => '0'],
            '10' => ['yield' => '', 'change' => '0'],
            '20' => ['yield' => '', 'change' => '0'],
            '30' => ['yield' => '', 'change' => '0'],
        ];

        foreach ($manual_defaults as $maturity => $values) {
            if (get_option('uk_yield_rates_manual_' . $maturity . '_yield') === false) {
                update_option('uk_yield_rates_manual_' . $maturity . '_yield', $values['yield']);
            }
            if (get_option('uk_yield_rates_manual_' . $maturity . '_change') === false) {
                update_option('uk_yield_rates_manual_' . $maturity . '_change', $values['change']);
            }
        }

        // Initial data fetch
        $cache = UK_Yield_Cache::get_instance();
        $cache->clear_cache();

        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Clear cache
        $cache = UK_Yield_Cache::get_instance();
        $cache->clear_cache();

        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Load plugin textdomain
     */
    public function load_textdomain() {
        load_plugin_textdomain('uk-yield-rates', false, dirname(UK_YIELD_RATES_PLUGIN_BASENAME) . '/languages');
    }
}

// Initialize plugin
UK_Yield_Rates::get_instance();
