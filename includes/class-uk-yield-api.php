<?php
/**
 * API Handler for UK Yield Rates
 * Handles data fetching from Bank of England and FRED APIs with auto-failover
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
    private $boe_base_url = 'https://www.bankofengland.co.uk/boeapps/database';
    private $fred_base_url = 'https://api.stlouisfed.org/fred/series/observations';

    /**
     * Bank of England series codes
     */
    private $boe_series_codes = [
        '2'  => 'IUDSOIA',  // 2-year
        '5'  => 'IUDMFLS',  // 5-year
        '10' => 'IUDOEY',   // 10-year
        '20' => 'IUDZQ5',   // 20-year
        '30' => 'IUDUKX',   // 30-year
    ];

    /**
     * FRED series IDs
     */
    private $fred_series_ids = [
        '2'  => 'IRLTLT01GBM156N',  // 2-year
        '5'  => 'IRLTLT04GBM156N',  // 5-year
        '10' => 'IRLTLT10GBM156N',  // 10-year
        '20' => 'IRLTLT20GBM156N',  // 20-year
        '30' => 'IRLTLT30GBM156N',  // 30-year
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
        $api_source = get_option('uk_yield_rates_api_source', 'auto');

        // Try configured source first
        if ($api_source === 'boe') {
            $data = $this->fetch_from_boe();
            if ($data) return $data;
        } elseif ($api_source === 'fred') {
            $data = $this->fetch_from_fred();
            if ($data) return $data;
        }

        // Auto-failover: try both APIs
        $data = $this->fetch_from_boe();
        if ($data) return $data;

        $data = $this->fetch_from_fred();
        if ($data) return $data;

        // All APIs failed
        return false;
    }

    /**
     * Fetch from Bank of England API
     */
    private function fetch_from_boe() {
        $yields = [];

        foreach ($this->boe_series_codes as $maturity => $series_code) {
            $url = add_query_arg([
                '_indx' => $series_code,
                'ession' => 'DAILY',
                'loession' => date('Y-m-d', strtotime('-7 days')),
                'hiession' => date('Y-m-d'),
                'Using' => 'Y',
                'csv' => '1',
            ], $this->boe_base_url);

            $response = wp_remote_get($url, [
                'timeout' => 10,
                'sslverify' => false,
            ]);

            if (is_wp_error($response)) {
                error_log('UK Yield Rates: BoE API error for ' . $maturity . ' year - ' . $response->get_error_message());
                continue;
            }

            $body = wp_remote_retrieve_body($response);
            $data = $this->parse_boe_csv($body, $maturity);

            if ($data) {
                $yields[$maturity] = $data;
            }
        }

        if (empty($yields)) {
            return false;
        }

        return $this->format_yield_data($yields, 'boe');
    }

    /**
     * Parse Bank of England CSV response
     */
    private function parse_boe_csv($csv_data, $maturity) {
        $lines = explode("\n", $csv_data);

        if (count($lines) < 2) {
            return false;
        }

        // Find the data rows (skip headers)
        $data_rows = [];
        for ($i = 1; $i < count($lines); $i++) {
            $line = trim($lines[$i]);
            if (!empty($line)) {
                $data_rows[] = str_getcsv($line);
            }
        }

        if (empty($data_rows)) {
            return false;
        }

        // Get the most recent value
        $latest = end($data_rows);
        $previous = count($data_rows) > 1 ? $data_rows[count($data_rows) - 2] : null;

        $current_value = floatval($latest[1] ?? 0);
        $previous_value = $previous ? floatval($previous[1] ?? 0) : $current_value;
        $change = $current_value - $previous_value;

        return [
            'maturity' => $maturity,
            'yield' => $current_value,
            'change' => $change,
            'date' => $latest[0] ?? date('Y-m-d'),
        ];
    }

    /**
     * Fetch from FRED API
     */
    private function fetch_from_fred() {
        $api_key = get_option('uk_yield_rates_fred_api_key', '');

        if (empty($api_key)) {
            error_log('UK Yield Rates: FRED API key not configured');
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
                error_log('UK Yield Rates: FRED API error for ' . $maturity . ' year - ' . $response->get_error_message());
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
                'date' => $latest['date'] ?? date('Y-m-d'),
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
            'date' => reset($yields)['date'] ?? date('Y-m-d'),
        ];
    }

    /**
     * Get historical yields (optional future feature)
     */
    public function fetch_historical_yields($maturity = '10', $days = 30) {
        // This could be extended to fetch historical data
        // For now, return false
        return false;
    }
}
