<?php
/**
 * API Handler for UK Yield Rates
 * Handles data fetching from Bank of England and FRED APIs with auto-failover
 *
 * @package UK_Yield_Rates
 * @version 1.3.1
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
     * API endpoints
     */
    private $fred_base_url = 'https://api.stlouisfed.org/fred/series/observations';
    private $boe_custom_base_url = ''; // Set via admin settings

    /**
     * FRED series IDs for UK gilt yields (backup)
     */
    private $fred_series_ids = [
        '2'  => 'IRLTLT01GBM156N',  // 2-year (UK long-term)
        '5'  => 'IRLTLT05GBM156N',  // 5-year (UK long-term)
        '10' => 'IRLTLT10GBM156N',  // 10-year (UK long-term)
        '20' => 'IRLTLT20GBM156N',  // 20-year (UK long-term)
        '30' => 'IRLTLT30GBM156N',  // 30-year (UK long-term)
    ];

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
     * Fetch yield rates with auto-failover
     */
    public function fetch_yields() {
        $api_source = get_option('uk_yield_rates_api_source', 'manual');

        // Try configured source first
        if ($api_source === 'boe_direct') {
            $data = $this->fetch_from_boe_direct();
            if ($data) return $data;
        } elseif ($api_source === 'boe_custom') {
            $data = $this->fetch_from_boe_custom();
            if ($data) return $data;
        } elseif ($api_source === 'fred') {
            $data = $this->fetch_from_fred();
            if ($data) return $data;
        } elseif ($api_source === 'manual') {
            // Use manually entered data from admin settings
            return $this->fetch_from_manual_entry();
        }

        // Auto-failover: try all APIs
        $data = $this->fetch_from_boe_direct();
        if ($data) return $data;

        $data = $this->fetch_from_boe_custom();
        if ($data) return $data;

        $data = $this->fetch_from_fred();
        if ($data) return $data;

        // Last resort: use manual entry
        $data = $this->fetch_from_manual_entry();
        if ($data) return $data;

        // All sources failed
        return false;
    }

    /**
     * Fetch from BoE direct (automatic ZIP download)
     */
    private function fetch_from_boe_direct() {
        $provider = UK_Yield_BoE_Provider::get_instance();

        if (!$provider->is_available()) {
            return false;
        }

        return $provider->fetch_yields();
    }

    /**
     * Fetch from custom BoE endpoint (user-hosted API)
     */
    private function fetch_from_boe_custom() {
        $endpoint_url = get_option('uk_yield_rates_boe_custom_endpoint', '');

        if (empty($endpoint_url)) {
            return false;
        }

        $response = wp_remote_get($endpoint_url, [
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

        if (!$data || !isset($data['yields']) || !is_array($data['yields'])) {
            return false;
        }

        $yields = [];
        foreach ($data['yields'] as $maturity => $yield_data) {
            if (isset($yield_data['yield'])) {
                $yields[$maturity] = [
                    'maturity' => $maturity,
                    'yield' => floatval($yield_data['yield']),
                    'change' => floatval($yield_data['change'] ?? 0),
                    'date' => $yield_data['date'] ?? gmdate('Y-m-d'),
                ];
            }
        }

        if (empty($yields)) {
            return false;
        }

        return $this->format_yield_data($yields, 'boe_custom');
    }

    /**
     * Fetch from manual data entry (admin settings)
     */
    private function fetch_from_manual_entry() {
        $yields = [];
        $maturities = ['2', '5', '10', '20', '30'];

        foreach ($maturities as $maturity) {
            $yield_value = get_option('uk_yield_rates_manual_' . $maturity . '_yield', '');
            $change_value = get_option('uk_yield_rates_manual_' . $maturity . '_change', '0');
            $date = get_option('uk_yield_rates_manual_date', gmdate('Y-m-d'));

            if (!empty($yield_value)) {
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
     * Fetch from FRED API
     */
    private function fetch_from_fred() {
        $api_key = get_option('uk_yield_rates_fred_api_key', '');

        if (empty($api_key)) {
            return false;
        }

        $yields = [];

        foreach ($this->fred_series_ids as $maturity => $series_id) {
            $url = add_query_arg([
                'series_id' => $series_id,
                'api_key' => $api_key,
                'file_type' => 'json',
                'sort_order' => 'desc',
                'limit' => 2,
            ], $this->fred_base_url);

            $response = wp_remote_get($url, [
                'timeout' => 10,
            ]);

            if (is_wp_error($response)) {
                continue;
            }

            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);

            if (!$data || !isset($data['observations']) || empty($data['observations'])) {
                continue;
            }

            $observations = $data['observations'];
            $latest = $observations[0];
            $previous = count($observations) > 1 ? $observations[1] : null;

            $current_value = floatval($latest['value'] ?? 0);
            $previous_value = $previous ? floatval($previous['value'] ?? 0) : $current_value;
            $change = $current_value - $previous_value;

            $yields[$maturity] = [
                'maturity' => $maturity,
                'yield' => $current_value,
                'change' => $change,
                'date' => $latest['date'] ?? gmdate('Y-m-d'),
            ];
        }

        if (empty($yields)) {
            return false;
        }

        return $this->format_yield_data($yields, 'fred');
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
