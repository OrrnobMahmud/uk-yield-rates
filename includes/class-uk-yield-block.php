<?php
/**
 * Gutenberg Block for UK Yield Rates
 */

if (!defined('ABSPATH')) {
    exit;
}

class UK_Yield_Block {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('init', [$this, 'register_block']);
        add_action('enqueue_block_assets', [$this, 'enqueue_block_assets']);
    }

    /**
     * Register Gutenberg block
     */
    public function register_block() {
        // Register block
        register_block_type('uk-yield-rates/yield-rates', [
            'render_callback' => [$this, 'render_block'],
            'editor_script' => 'uk-yield-block-editor',
            'editor_style' => 'uk-yield-block-editor-style',
            'style' => 'uk-yield-block-style',
        ]);

        // Localize script for block
        wp_localize_script('uk-yield-block-editor', 'ukYieldBlockData', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('uk_yield_rates_nonce'),
        ]);
    }

    /**
     * Enqueue block assets
     */
    public function enqueue_block_assets($hook) {
        // Editor script
        wp_enqueue_script(
            'uk-yield-block-editor',
            UK_YIELD_RATES_PLUGIN_URL . 'blocks/yield-rates/index.js',
            ['wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-data', 'wp-api-fetch'],
            UK_YIELD_RATES_VERSION,
            true
        );

        // Editor style
        wp_enqueue_style(
            'uk-yield-block-editor-style',
            UK_YIELD_RATES_PLUGIN_URL . 'blocks/yield-rates/editor.css',
            [],
            UK_YIELD_RATES_VERSION
        );

        // Frontend style
        wp_enqueue_style(
            'uk-yield-block-style',
            UK_YIELD_RATES_PLUGIN_URL . 'public/css/yield-rates.css',
            [],
            UK_YIELD_RATES_VERSION
        );
    }

    /**
     * Render block on frontend
     */
    public function render_block($attributes) {
        $atts = [
            'maturity' => $attributes['maturity'] ?? 'all',
            'format' => $attributes['format'] ?? 'inline',
            'show_change' => ($attributes['showChange'] ?? true) ? 'yes' : 'no',
            'show_updated' => 'yes',
            'decimal' => $attributes['decimalPlaces'] ?? get_option('uk_yield_rates_decimal_places', '2'),
            'theme' => $attributes['theme'] ?? get_option('uk_yield_rates_theme', 'light'),
        ];

        // Use shortcode renderer
        $shortcode = UK_Yield_Shortcode::get_instance();
        return $shortcode->render_shortcode($atts);
    }
}
