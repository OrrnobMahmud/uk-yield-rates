<?php
/**
 * API Handler for UK Yield Rates
 * Fetches data from the UK Yield Rates API
 *
 * @package UK_Yield_Rates
 * @version 2.0.0
 * @license GPL-2.0-or-later
 * @author Orrnob Mahmud
 */

if (!defined('ABSPATH')) {
    exit;
}

class UK_Yield_API {

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
    private function __construct() {}

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
     * Fetch all yields from API
     */
    public function fetch_yields() {
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
     * Fetch yield for specific maturity
     */
    public function fetch_yield($maturity) {
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

        if (!$data || !isset($data['data'])) {
            return false;
        }

        return [
            'maturity' => $maturity,
            'yield' => floatval($data['data']['yield']),
            'change' => 0,
            'date' => $data['data']['date'] ?? gmdate('Y-m-d'),
        ];
    }

    /**
     * Fetch yield curve
     */
    public function fetch_curve() {
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

        if (!$data || !isset($data['data']['curve']) || !is_array($data['data']['curve'])) {
            return false;
        }

        $yields = [];
        foreach ($data['data']['curve'] as $item) {
            $yields[$item['maturity']] = [
                'maturity' => $item['maturity'],
                'yield' => floatval($item['yield']),
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
     * Health check
     */
    public function health_check() {
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
