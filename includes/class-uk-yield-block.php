<?php
/**
 * Gutenberg Block for UK Yield Rates
 *
 * @package UK_Yield_Rates
 * @version 1.3.1
 * @license GPL-2.0-or-later
 * @author Orrnob Mahmud
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
        // Register block — block.json handles editorScript, editorStyle, style
        register_block_type('uk-yield-rates/yield-rates', [
            'render_callback' => [$this, 'render_block'],
        ]);
    }

    /**
     * Register block assets (block.json handles enqueuing)
     */
    public function enqueue_block_assets() {
        $asset_file = UK_YIELD_RATES_PLUGIN_DIR . 'blocks/yield-rates/dist/index.asset.php';
        $asset = file_exists($asset_file) ? require $asset_file : ['dependencies' => [], 'version' => UK_YIELD_RATES_VERSION];

        // Register editor script (block.json enqueues via editorScript handle)
        wp_register_script(
            'uk-yield-block-editor',
            UK_YIELD_RATES_PLUGIN_URL . 'blocks/yield-rates/dist/index.js',
            $asset['dependencies'],
            $asset['version'],
            true
        );

        // Localize script for block
        wp_localize_script('uk-yield-block-editor', 'ukYieldBlockData', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('uk_yield_rates_nonce'),
        ]);

        // Register editor style (block.json enqueues via editorStyle handle)
        wp_register_style(
            'uk-yield-block-editor-style',
            UK_YIELD_RATES_PLUGIN_URL . 'blocks/yield-rates/editor.css',
            [],
            UK_YIELD_RATES_VERSION
        );

        // Register frontend style (block.json enqueues via style handle)
        wp_register_style(
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
