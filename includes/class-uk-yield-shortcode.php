<?php
/**
 * Shortcode Handler for UK Yield Rates
 * Renders yield data in different formats
 *
 * @package UK_Yield_Rates
 * @version 1.3.2
 * @license GPL-2.0-or-later
 * @author Orrnob Mahmud
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class UK_Yield_Shortcode {

    /**
     * Single instance
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
        add_shortcode('uk_yield_rates', [$this, 'render_shortcode']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_styles']);
    }

    /**
     * Enqueue frontend styles
     */
    public function enqueue_styles() {
        wp_enqueue_style(
            'uk-yield-rates',
            UK_YIELD_RATES_PLUGIN_URL . 'public/css/yield-rates.css',
            [],
            UK_YIELD_RATES_VERSION
        );
    }

    /**
     * Render shortcode
     */
    public function render_shortcode($atts) {
        $atts = shortcode_atts([
            'maturity'     => 'all',
            'format'       => 'inline',
            'inline'       => 'no',
            'theme'        => get_option('uk_yield_rates_theme', 'light'),
            'show_change'  => get_option('uk_yield_rates_show_change', 'yes'),
            'show_updated' => get_option('uk_yield_rates_show_last_updated', 'yes'),
            'decimal'      => get_option('uk_yield_rates_decimal_places', '2'),
        ], $atts, 'uk_yield_rates');

        // Get yield data
        $cache = UK_Yield_Cache::get_instance();
        $data = $cache->get_yields();

        if (!$data) {
            return '<span class="uk-yield-error">' . esc_html__('Yield rates temporarily unavailable.', 'uk-yield-rates') . '</span>';
        }

        // Parse maturity
        if ($atts['maturity'] === 'all') {
            $yields = $data['yields'];
        } else {
            $maturities = explode(',', $atts['maturity']);
            $yields = array_intersect_key($data['yields'], array_flip($maturities));
        }

        if (empty($yields)) {
            return '<span class="uk-yield-error">' . esc_html__('No yield data available.', 'uk-yield-rates') . '</span>';
        }

        // Sort by maturity
        uksort($yields, function($a, $b) {
            return $this->maturity_sort($a, $b);
        });

        // Determine format
        $format = $atts['inline'] === 'yes' ? 'inline' : $atts['format'];

        // Build data attributes for auto-refresh
        $data_attributes = ' data-uk-yield-rates data-maturity="' . esc_attr($atts['maturity']) . '" data-format="' . esc_attr($format) . '"';

        // Build theme class
        $theme_class = $atts['theme'] === 'dark' ? ' uk-yield-dark' : '';

        // Render based on format
        switch ($format) {
            case 'inline':
                return $this->render_inline($yields, $atts, $data, $data_attributes);
            case 'sidebar':
                return $this->render_sidebar($yields, $atts, $data, $data_attributes);
            case 'table':
                return $this->render_table($yields, $atts, $data, $data_attributes);
            case 'compact':
                return $this->render_compact($yields, $atts, $data, $data_attributes);
            default:
                return $this->render_inline($yields, $atts, $data, $data_attributes);
        }
    }

    /**
     * Render inline format (for paragraphs)
     */
    private function render_inline($yields, $atts, $data, $data_attributes) {
        $theme_class = $atts['theme'] === 'dark' ? ' uk-yield-dark' : '';
        $html = '<span class="uk-yield-inline' . $theme_class . '"' . $data_attributes . '>';

        $decimal = intval($atts['decimal']);
        $first = true;

        foreach ($yields as $maturity => $yield) {
            if (!$first) {
                $html .= ', ';
            }
            $first = false;

            $yield_value = number_format($yield['yield'], $decimal) . '%';
            $change = $yield['change'];
            $change_class = $change > 0 ? 'positive' : ($change < 0 ? 'negative' : 'neutral');
            $change_symbol = $change > 0 ? '↑' : ($change < 0 ? '↓' : '→');
            $change_value = abs($change) > 0 ? $change_symbol . number_format(abs($change), $decimal) : '';

            $html .= '<span class="uk-yield-item">';
            $html .= '<span class="uk-yield-label">' . esc_html($maturity) . ': </span>';
            $html .= '<span class="uk-yield-value">' . esc_html($yield_value) . '</span>';

            if ($atts['show_change'] === 'yes' && !empty($change_value)) {
                $html .= ' <span class="uk-yield-change ' . esc_attr($change_class) . '">' . esc_html($change_value) . '</span>';
            }

            $html .= '</span>';
        }

        if ($atts['show_updated'] === 'yes') {
            $html .= ' <span class="uk-yield-updated">';
            $html .= '<span class="uk-yield-updated-label">Updated: </span>';
            $html .= '<span class="uk-yield-updated-time">' . esc_html($this->format_date($data['date'])) . '</span>';
            $html .= '</span>';
        }

        $html .= '</span>';

        return $html;
    }

    /**
     * Render sidebar format (compact widget)
     */
    private function render_sidebar($yields, $atts, $data, $data_attributes) {
        $theme_class = $atts['theme'] === 'dark' ? ' uk-yield-dark' : '';
        $html = '<div class="uk-yield-sidebar' . $theme_class . '"' . $data_attributes . '>';
        $html .= '<div class="uk-yield-sidebar-header">';
        $html .= '<h3 class="uk-yield-sidebar-title">' . esc_html__('UK Gilt Yields', 'uk-yield-rates') . '</h3>';
        $html .= '</div>';
        $html .= '<div class="uk-yield-sidebar-content">';

        $decimal = intval($atts['decimal']);

        foreach ($yields as $maturity => $yield) {
            $yield_value = number_format($yield['yield'], $decimal) . '%';
            $change = $yield['change'];
            $change_class = $change > 0 ? 'positive' : ($change < 0 ? 'negative' : 'neutral');
            $change_symbol = $change > 0 ? '↑' : ($change < 0 ? '↓' : '→');
            $change_value = abs($change) > 0 ? $change_symbol . number_format(abs($change), $decimal) : '';

            $html .= '<div class="uk-yield-sidebar-row">';
            $html .= '<span class="uk-yield-sidebar-maturity">' . esc_html($maturity) . '-Year</span>';
            $html .= '<span class="uk-yield-sidebar-yield">' . esc_html($yield_value) . '</span>';

            if ($atts['show_change'] === 'yes' && !empty($change_value)) {
                $html .= '<span class="uk-yield-sidebar-change ' . esc_attr($change_class) . '">' . esc_html($change_value) . '</span>';
            }

            $html .= '</div>';
        }

        $html .= '</div>';

        if ($atts['show_updated'] === 'yes') {
            $html .= '<div class="uk-yield-sidebar-footer">';
            $html .= '<span class="uk-yield-updated">';
            $html .= esc_html__('Updated: ', 'uk-yield-rates') . esc_html($this->format_date($data['date']));
            $html .= '</span>';
            $html .= '</div>';
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Render table format
     */
    private function render_table($yields, $atts, $data, $data_attributes) {
        $theme_class = $atts['theme'] === 'dark' ? ' uk-yield-dark' : '';
        $html = '<div class="uk-yield-table-wrapper' . $theme_class . '"' . $data_attributes . '>';
        $html .= '<table class="uk-yield-table' . $theme_class . '">';
        $html .= '<thead>';
        $html .= '<tr>';
        $html .= '<th class="uk-yield-th-maturity">' . esc_html__('Maturity', 'uk-yield-rates') . '</th>';
        $html .= '<th class="uk-yield-th-yield">' . esc_html__('Yield', 'uk-yield-rates') . '</th>';

        if ($atts['show_change'] === 'yes') {
            $html .= '<th class="uk-yield-th-change">' . esc_html__('Change', 'uk-yield-rates') . '</th>';
            $html .= '<th class="uk-yield-th-status">' . esc_html__('Status', 'uk-yield-rates') . '</th>';
        }

        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';

        $decimal = intval($atts['decimal']);

        foreach ($yields as $maturity => $yield) {
            $yield_value = number_format($yield['yield'], $decimal) . '%';
            $change = $yield['change'];
            $change_class = $change > 0 ? 'positive' : ($change < 0 ? 'negative' : 'neutral');
            $change_symbol = $change > 0 ? '↑' : ($change < 0 ? '↓' : '→');
            $change_value = abs($change) > 0 ? $change_symbol . number_format(abs($change), $decimal) : '→ 0.00';

            $html .= '<tr>';
            $html .= '<td class="uk-yield-td-maturity">' . esc_html($maturity) . '</td>';
            $html .= '<td class="uk-yield-td-yield">' . esc_html($yield_value) . '</td>';

            if ($atts['show_change'] === 'yes') {
                $html .= '<td class="uk-yield-td-change ' . esc_attr($change_class) . '">' . esc_html($change_value) . '</td>';
                $html .= '<td class="uk-yield-td-status ' . esc_attr($change_class) . '">';

                if ($change > 0) {
                    $html .= esc_html__('Rising', 'uk-yield-rates');
                } elseif ($change < 0) {
                    $html .= esc_html__('Falling', 'uk-yield-rates');
                } else {
                    $html .= esc_html__('Stable', 'uk-yield-rates');
                }

                $html .= '</td>';
            }

            $html .= '</tr>';
        }

        $html .= '</tbody>';
        $html .= '</table>';

        if ($atts['show_updated'] === 'yes') {
            $html .= '<div class="uk-yield-table-footer">';
            $html .= '<span class="uk-yield-updated">';
            $html .= esc_html__('Last Updated: ', 'uk-yield-rates') . esc_html($this->format_datetime($data['fetched_at']));
            $html .= '</span>';
            $html .= '</div>';
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Render compact format (single line)
     */
    private function render_compact($yields, $atts, $data, $data_attributes) {
        $theme_class = $atts['theme'] === 'dark' ? ' uk-yield-dark' : '';
        $html = '<span class="uk-yield-compact' . $theme_class . '"' . $data_attributes . '>';

        $decimal = intval($atts['decimal']);
        $parts = [];

        foreach ($yields as $maturity => $yield) {
            $yield_value = number_format($yield['yield'], $decimal) . '%';
            $change = $yield['change'];
            $change_symbol = $change > 0 ? '+' : '';
            $change_value = $change_symbol . number_format($change, $decimal);

            $parts[] = esc_html($maturity) . ': ' . esc_html($yield_value) . ' (' . esc_html($change_value) . ')';
        }

        $html .= implode(' | ', $parts);

        if ($atts['show_updated'] === 'yes') {
            $html .= ' | ' . esc_html__('Updated: ', 'uk-yield-rates') . esc_html($this->format_time($data['fetched_at']));
        }

        $html .= '</span>';

        return $html;
    }

    /**
     * Format date for display
     */
    private function format_date($date) {
        $timestamp = strtotime($date);
        if (!$timestamp) {
            return $date;
        }
        return date_i18n('j M Y', $timestamp);
    }

    /**
     * Format datetime for display
     */
    private function format_datetime($datetime) {
        $timestamp = strtotime($datetime);
        if (!$timestamp) {
            return $datetime;
        }
        return date_i18n('j M Y, G:i', $timestamp);
    }

    /**
     * Format time for display
     */
    private function format_time($datetime) {
        $timestamp = strtotime($datetime);
        if (!$timestamp) {
            return $datetime;
        }
        return date_i18n('G:i', $timestamp);
    }

    /**
     * Sort maturities by numeric value
     */
    private function maturity_sort($a, $b) {
        $a_val = $this->maturity_to_months($a);
        $b_val = $this->maturity_to_months($b);
        return $a_val - $b_val;
    }

    /**
     * Convert maturity string to months for sorting
     */
    private function maturity_to_months($maturity) {
        $maturity = strtoupper(trim($maturity));
        
        if (preg_match('/^(\d+)M$/', $maturity, $matches)) {
            return $matches[1];
        } elseif (preg_match('/^(\d+)Y$/', $maturity, $matches)) {
            return $matches[1] * 12;
        } else {
            return 0;
        }
    }
}
