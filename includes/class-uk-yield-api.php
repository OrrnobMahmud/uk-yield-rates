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
    private $boe_base_url = 'https://www.bankofengland.co.uk/boeapps/database/FromShowColumns.asp';
    private $fred_base_url = 'https://api.stlouisfed.org/fred/series/observations';
    private $dmo_base_url = 'https://www.dmo.gov.uk/responsibilities/gilt-market/market-information/real-yields';

    /**
     * Bank of England series codes for gilt yields
     */
    private $boe_series_codes = [
        '2'  => 'IUDGILS02',  // 2-year
        '5'  => 'IUDGILS05',  // 5-year
        '10' => 'IUDGILS10',  // 10-year
        '20' => 'IUDGILS20',  // 20-year
        '30' => 'IUDGILS30',  // 30-year
    ];

    /**
     * FRED series IDs for UK gilt yields
     */
    private $fred_series_ids = [
        '2'  => 'IRLTLT01GBM156N',  // 2-year (UK long-term)
        '5'  => 'IRLTLT05GBM156N',  // 5-year (UK long-term)
        '10' => 'IRLTLT10GBM156N',  // 10-year (UK long-term)
        '20' => 'IRLTLT20GBM156N',  // 20-year (UK long-term)
        '30' => 'IRLTLT30GBM156N',  // 30-year (UK long-term)
    ];

    /**
     * DMO CSV download URLs
     */
    private $dmo_csv_urls = [
        'real' => 'https://www.dmo.gov.uk/responsibilities/gilt-market/market-information/real-yields',
        'nominal' => 'https://www.dmo.gov.uk/responsibilities/gilt-market/market-information/nominal-yields',
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
        } elseif ($api_source === 'manual') {
            // Use manually entered data from admin settings
            return $this->fetch_from_manual_entry();
        }

        // Auto-failover: try all APIs
        $data = $this->fetch_from_boe();
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
     * Fetch from manual data entry (admin settings)
     */
    private function fetch_from_manual_entry() {
        $yields = [];
        $maturities = ['2', '5', '10', '20', '30'];

        foreach ($maturities as $maturity) {
            $yield_value = get_option('uk_yield_rates_manual_' . $maturity . '_yield', '');
            $change_value = get_option('uk_yield_rates_manual_' . $maturity . '_change', '0');
            $date = get_option('uk_yield_rates_manual_date', date('Y-m-d'));

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
     * Save manual yield data to cache
     */
    public function save_manual_data($yield_data) {
        if (empty($yield_data) || !isset($yield_data['yields'])) {
            return false;
        }

        $cache = UK_Yield_Cache::get_instance();
        return $cache->save_manual_data($yield_data);
    }

    /**
     * Fetch from Bank of England API
     */
    private function fetch_from_boe() {
        $yields = [];

        // Build series codes string
        $series_codes = implode(',', array_values($this->boe_series_codes));

        // Calculate date range (last 7 days to ensure we get data)
        $date_from = date('d/M/Y', strtotime('-7 days'));
        $date_to = date('d/M/Y');

        $url = add_query_arg([
            'SeriesCodes' => $series_codes,
            'UsingCodes' => 'Y',
            'CSVF' => 'TN',
            'DateFrom' => $date_from,
            'DateTo' => $date_to,
            'Format' => 'CSV',
            'Period' => 'Daily',
        ], $this->boe_base_url);

        $response = wp_remote_get($url, [
            'timeout' => 15,
            'sslverify' => false,
        ]);

        if (is_wp_error($response)) {
            error_log('UK Yield Rates: BoE API error - ' . $response->get_error_message());
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $yields = $this->parse_boe_csv($body);

        if (empty($yields)) {
            return false;
        }

        return $this->format_yield_data($yields, 'boe');
    }

    /**
     * Parse Bank of England CSV response
     */
    private function parse_boe_csv($csv_data) {
        $lines = explode("\n", $csv_data);

        if (count($lines) < 3) {
            return false;
        }

        // Parse header to find series columns
        $header = str_getcsv($lines[0]);
        $series_map = [];

        // Map series codes to column indices
        foreach ($this->boe_series_codes as $maturity => $code) {
            $key = array_search($code, $header);
            if ($key !== false) {
                $series_map[$maturity] = $key;
            }
        }

        if (empty($series_map)) {
            error_log('UK Yield Rates: Could not find series codes in CSV header');
            return false;
        }

        // Find data rows (skip header rows)
        $data_rows = [];
        for ($i = 1; $i < count($lines); $i++) {
            $line = trim($lines[$i]);
            if (!empty($line) && !preg_match('/^[A-Z]/', $line)) {
                $data_rows[] = str_getcsv($line);
            }
        }

        if (empty($data_rows)) {
            return false;
        }

        $yields = [];

        // Extract yield data for each maturity
        foreach ($series_map as $maturity => $column_index) {
            // Get last two values for change calculation
            $values = [];
            foreach ($data_rows as $row) {
                if (isset($row[$column_index]) && is_numeric($row[$column_index])) {
                    $values[] = floatval($row[$column_index]);
                }
            }

            if (empty($values)) {
                continue;
            }

            $current_value = end($values);
            $previous_value = count($values) > 1 ? $values[count($values) - 2] : $current_value;
            $change = $current_value - $previous_value;

            // Get date from first column
            $date = reset($data_rows)[0] ?? date('Y-m-d');

            $yields[$maturity] = [
                'maturity' => $maturity,
                'yield' => $current_value,
                'change' => $change,
                'date' => $date,
            ];
        }

        return $yields;
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
