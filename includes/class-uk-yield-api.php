<?php
/**
 * API Handler for UK Yield Rates
 * Supports both manual entry and API modes
 *
 * @package UK_Yield_Rates
 * @version 2.1.0
 * @license GPL-2.0-or-later
 * @author Orrnob Mahmud
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class UK_Yield_API {

    /**
     * Single instance
     */
    private static $instance = null;

    /**
     * Supported maturities for manual entry
     */
    const MANUAL_MATURITIES = ['2Y', '5Y', '10Y', '20Y', '30Y'];

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
    private function __construct() {}

    /**
     * Get data source mode
     */
    public function get_mode() {
        $api_url = $this->get_api_url();
        return !empty($api_url) ? 'api' : 'manual';
    }

    /**
     * Get API URL
     */
    private function get_api_url() {
        return rtrim(get_option('uk_yield_rates_api_url', ''), '/');
    }

    /**
     * Get API Key
     */
    private function get_api_key() {
        return get_option('uk_yield_rates_api_key', '');
    }

    /**
     * Fetch all yields (auto-detects mode)
     */
    public function fetch_yields() {
        if ($this->get_mode() === 'api') {
            return $this->fetch_from_api();
        }
        return $this->fetch_from_manual();
    }

    /**
     * Fetch from API
     */
    private function fetch_from_api() {
        $api_url = $this->get_api_url();

        if (empty($api_url)) {
            return false;
        }

        $response = wp_remote_get($api_url . '/api/v1/yields', [
            'timeout' => 15,
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);

        if (is_wp_error($response)) {
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (!$data || !isset($data['data']['yields']) || !is_array($data['data']['yields'])) {
            return false;
        }

        $yields = [];
        foreach ($data['data']['yields'] as $maturity => $yield_value) {
            $yields[$maturity] = [
                'maturity' => $maturity,
                'yield' => floatval($yield_value),
                'change' => 0,
                'date' => $data['data']['date'] ?? gmdate('Y-m-d'),
            ];
        }

        if (empty($yields)) {
            return false;
        }

        return $this->format_yield_data($yields, 'api');
    }

    /**
     * Fetch from manual entry
     */
    private function fetch_from_manual() {
        $yields = [];
        $date = get_option('uk_yield_rates_manual_date', gmdate('Y-m-d'));

        foreach (self::MANUAL_MATURITIES as $maturity) {
            $yield_value = get_option('uk_yield_rates_manual_' . $maturity . '_yield', '');
            $change_value = get_option('uk_yield_rates_manual_' . $maturity . '_change', '0');

            if ($yield_value !== '') {
                $yields[$maturity] = [
                    'maturity' => $maturity,
                    'yield' => floatval($yield_value),
                    'change' => floatval($change_value),
                    'date' => $date,
                ];
            }
        }

        if (empty($yields)) {
            return false;
        }

        return $this->format_yield_data($yields, 'manual');
    }

    /**
     * Fetch yield for specific maturity
     */
    public function fetch_yield($maturity) {
        if ($this->get_mode() === 'api') {
            return $this->fetch_yield_from_api($maturity);
        }
        return $this->fetch_yield_from_manual($maturity);
    }

    /**
     * Fetch yield from API
     */
    private function fetch_yield_from_api($maturity) {
        $api_url = $this->get_api_url();

        if (empty($api_url)) {
            return false;
        }

        $response = wp_remote_get($api_url . '/api/v1/yields/' . urlencode($maturity), [
            'timeout' => 15,
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);

        if (is_wp_error($response)) {
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (!$data || !isset($data['yield_pct'])) {
            return false;
        }

        return [
            'maturity' => $maturity,
            'yield' => floatval($data['yield_pct']),
            'change' => 0,
            'date' => $data['date'] ?? gmdate('Y-m-d'),
        ];
    }

    /**
     * Fetch yield from manual entry
     */
    private function fetch_yield_from_manual($maturity) {
        $yield_value = get_option('uk_yield_rates_manual_' . $maturity . '_yield', '');
        $change_value = get_option('uk_yield_rates_manual_' . $maturity . '_change', '0');
        $date = get_option('uk_yield_rates_manual_date', gmdate('Y-m-d'));

        if ($yield_value === '') {
            return false;
        }

        return [
            'maturity' => $maturity,
            'yield' => floatval($yield_value),
            'change' => floatval($change_value),
            'date' => $date,
        ];
    }

    /**
     * Fetch yield curve
     */
    public function fetch_curve() {
        if ($this->get_mode() === 'api') {
            return $this->fetch_curve_from_api();
        }
        return $this->fetch_from_manual();
    }

    /**
     * Fetch curve from API
     */
    private function fetch_curve_from_api() {
        $api_url = $this->get_api_url();

        if (empty($api_url)) {
            return false;
        }

        $response = wp_remote_get($api_url . '/api/v1/curve', [
            'timeout' => 15,
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);

        if (is_wp_error($response)) {
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (!$data || !isset($data['curve']) || !is_array($data['curve'])) {
            return false;
        }

        $yields = [];
        foreach ($data['curve'] as $item) {
            $yields[$item['maturity']] = [
                'maturity' => $item['maturity'],
                'yield' => floatval($item['yield']),
                'change' => 0,
                'date' => $data['date'] ?? gmdate('Y-m-d'),
            ];
        }

        if (empty($yields)) {
            return false;
        }

        return $this->format_yield_data($yields, 'api');
    }

    /**
     * Health check
     */
    public function health_check() {
        if ($this->get_mode() === 'manual') {
            return !empty(get_option('uk_yield_rates_manual_date', ''));
        }

        $api_url = $this->get_api_url();

        if (empty($api_url)) {
            return false;
        }

        $response = wp_remote_get($api_url . '/api/v1/health', [
            'timeout' => 10,
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);

        if (is_wp_error($response)) {
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        return $data && isset($data['status']) && $data['status'] === 'healthy';
    }

    /**
     * Trigger refresh (requires API key)
     */
    public function trigger_refresh() {
        if ($this->get_mode() === 'manual') {
            return false;
        }

        $api_url = $this->get_api_url();
        $api_key = $this->get_api_key();

        if (empty($api_url) || empty($api_key)) {
            return false;
        }

        $response = wp_remote_post($api_url . '/api/v1/refresh', [
            'timeout' => 30,
            'headers' => [
                'X-API-Key' => $api_key,
                'Accept' => 'application/json',
            ],
        ]);

        if (is_wp_error($response)) {
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        return $data && isset($data['status']) && $data['status'] === 'success';
    }

    /**
     * Format yield data consistently
     */
    private function format_yield_data($yields, $source) {
        return [
            'yields' => $yields,
            'source' => $source,
            'fetched_at' => current_time('mysql'),
            'date' => reset($yields)['date'] ?? gmdate('Y-m-d'),
        ];
    }
}
