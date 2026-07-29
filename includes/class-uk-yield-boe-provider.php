<?php
/**
 * Bank of England Provider
 * Handles automatic BoE ZIP download, extraction, and Excel parsing
 *
 * @package UK_Yield_Rates
 * @version 1.3.1
 * @license GPL-2.0-or-later
 * @author Orrnob Mahmud
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Provider interface for data sources
 */
interface UK_Yield_Provider_Interface {
    /**
     * Fetch yield rates from this provider
     *
     * @return array|false Normalized yield data or false on failure
     */
    public function fetch_yields();

    /**
     * Get provider name
     *
     * @return string Provider name
     */
    public function get_source_name();

    /**
     * Check if provider is available
     *
     * @return bool True if available
     */
    public function is_available();
}

/**
 * Bank of England Provider
 * Downloads and parses official BoE yield curve ZIP archives
 */
class UK_Yield_BoE_Provider implements UK_Yield_Provider_Interface {

    /**
     * BoE ZIP download URL
     */
    const BOE_ZIP_URL = 'https://www.bankofengland.co.uk/-/media/boe/files/statistics/yield-curves/latest-yield-curve-data.zip';

    /**
     * Target maturities in years
     */
    const TARGET_MATURITIES = [
        2  => '2',
        5  => '5',
        10 => '10',
        20 => '20',
        30 => '30',
    ];

    /**
     * GLC Nominal workbook name pattern
     */
    const GLC_NOMINAL_PATTERN = '/GLC Nominal/i';

    /**
     * Yield curve sheet name (supports both old and new BoE workbook formats)
     */
    const YIELD_CURVE_SHEETS = ['4. spot curve', '1. yield curve'];

    /**
     * Maximum rows to scan for headers
     */
    const MAX_HEADER_SCAN_ROWS = 10;

    /**
     * Timeout for HTTP requests in seconds
     */
    const HTTP_TIMEOUT = 30;

    /**
     * Directory for temporary file storage
     */
    private $temp_dir;

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
        $this->temp_dir = sys_get_temp_dir() . '/uk_yield_rates/';
    }

    /**
     * Get provider name
     */
    public function get_source_name() {
        return 'boe_direct';
    }

    /**
     * Check if provider is available
     */
    public function is_available() {
        return function_exists('zip_open') && class_exists('ZipArchive');
    }

    /**
     * Fetch yield rates from BoE
     */
    public function fetch_yields() {
        if (!$this->is_available()) {
            return false;
        }

        $zip_path = $this->download_zip();

        if (!$zip_path) {
            return false;
        }

        $yields = $this->parse_zip($zip_path);

        $this->cleanup_temp_files($zip_path);

        if (!$yields) {
            return false;
        }

        return $this->format_yield_data($yields, 'boe_direct');
    }

    /**
     * Download ZIP from BoE
     *
     * @return string|false File path or false on failure
     */
    private function download_zip() {
        $response = wp_remote_get(self::BOE_ZIP_URL, [
            'timeout'     => self::HTTP_TIMEOUT,
            'user-agent'  => 'UK-Yield-Rates-Plugin/' . UK_YIELD_RATES_VERSION,
            'headers'     => [
                'Accept' => 'application/zip, application/octet-stream, */*',
            ],
            'stream'      => true,
            'filename'    => $this->temp_dir . 'boe-yield-curve.zip',
        ]);

        if (is_wp_error($response)) {
            error_log('UK Yield Rates: BoE download failed - ' . $response->get_error_message());
            return false;
        }

        $status_code = wp_remote_retrieve_response_code($response);

        if ($status_code !== 200) {
            error_log('UK Yield Rates: BoE download returned status ' . $status_code);
            return false;
        }

        $file_path = $this->temp_dir . 'boe-yield-curve.zip';

        if (!file_exists($file_path) || filesize($file_path) === 0) {
            error_log('UK Yield Rates: Downloaded ZIP file is empty or missing');
            return false;
        }

        return $file_path;
    }

    /**
     * Parse ZIP archive and extract yields
     *
     * @param string $zip_path Path to ZIP file
     * @return array|false Yield data or false on failure
     */
    private function parse_zip($zip_path) {
        $zip = new ZipArchive();

        if ($zip->open($zip_path) !== true) {
            error_log('UK Yield Rates: Failed to open ZIP archive');
            return false;
        }

        $workbook_path = $this->find_glc_nominal_workbook($zip);

        if (!$workbook_path) {
            $zip->close();
            return false;
        }

        $yields = $this->parse_excel_workbook($workbook_path);

        $zip->close();

        return $yields;
    }

    /**
     * Find GLC Nominal workbook in ZIP
     *
     * @param ZipArchive $zip ZIP archive
     * @return string|false Extracted file path or false
     */
    private function find_glc_nominal_workbook($zip) {
        $extract_dir = $this->temp_dir . 'extracted/';

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);

            if (preg_match(self::GLC_NOMINAL_PATTERN, $filename) && preg_match('/\.xlsx?$/i', $filename)) {
                $basename = basename($filename);
                $extract_path = $extract_dir . $basename;

                if (!is_dir($extract_dir)) {
                    wp_mkdir_p($extract_dir);
                }

                $contents = $zip->getFromIndex($i);

                if ($contents === false) {
                    continue;
                }

                file_put_contents($extract_path, $contents);

                return $extract_path;
            }
        }

        error_log('UK Yield Rates: GLC Nominal workbook not found in ZIP');
        return false;
    }

    /**
     * Parse Excel workbook and extract yields
     *
     * @param string $workbook_path Path to Excel file
     * @return array|false Yield data or false on failure
     */
    private function parse_excel_workbook($workbook_path) {
        if (!file_exists($workbook_path)) {
            return false;
        }

        $extension = strtolower(pathinfo($workbook_path, PATHINFO_EXTENSION));

        if ($extension === 'xlsx') {
            return $this->parse_xlsx($workbook_path);
        }

        return false;
    }

    /**
     * Parse XLSX file
     *
     * @param string $file_path Path to XLSX file
     * @return array|false Yield data or false on failure
     */
    private function parse_xlsx($file_path) {
        $zip = new ZipArchive();

        if ($zip->open($file_path) !== true) {
            error_log('UK Yield Rates: Failed to open XLSX file');
            return false;
        }

        $sheet_data = false;
        foreach (self::YIELD_CURVE_SHEETS as $sheet_name) {
            $sheet_data = $this->extract_sheet_data($zip, $sheet_name);
            if ($sheet_data) {
                break;
            }
        }

        $zip->close();

        if (!$sheet_data) {
            return false;
        }

        return $this->extract_yields_from_sheet($sheet_data);
    }

    /**
     * Extract sheet data from XLSX
     *
     * @param ZipArchive $zip XLSX zip archive
     * @param string $sheet_name Sheet name to find
     * @return array|false Sheet data or false
     */
    private function extract_sheet_data($zip, $sheet_name) {
        $shared_strings = $this->get_shared_strings($zip);

        $sheet_index = $this->find_sheet_index($zip, $sheet_name);

        if ($sheet_index === false) {
            error_log('UK Yield Rates: Sheet "' . $sheet_name . '" not found');
            return false;
        }

        $xml_content = $zip->getFromName("xl/worksheets/sheet{$sheet_index}.xml");

        if (!$xml_content) {
            return false;
        }

        return $this->parse_sheet_xml($xml_content, $shared_strings);
    }

    /**
     * Get shared strings from XLSX
     *
     * @param ZipArchive $zip XLSX zip archive
     * @return array Shared strings
     */
    private function get_shared_strings($zip) {
        $strings = [];
        $xml_content = $zip->getFromName('xl/sharedStrings.xml');

        if (!$xml_content) {
            return $strings;
        }

        $xml = simplexml_load_string($xml_content);

        if ($xml === false) {
            return $strings;
        }

        foreach ($xml->si as $si) {
            $t = (string) $si->t;
            $strings[] = $t;
        }

        return $strings;
    }

    /**
     * Find sheet index by name
     *
     * @param ZipArchive $zip XLSX zip archive
     * @param string $sheet_name Sheet name
     * @return int|false Sheet index or false
     */
    private function find_sheet_index($zip, $sheet_name) {
        $workbook_xml = $zip->getFromName('xl/workbook.xml');

        if (!$workbook_xml) {
            return false;
        }

        $xml = simplexml_load_string($workbook_xml);

        if ($xml === false) {
            return false;
        }

        $ns = $xml->getNamespaces(true);
        $sheets = $xml->sheets->sheet;

        $sheet_names = [];

        foreach ($sheets as $sheet) {
            $name = (string) $sheet['name'];
            $sheet_id = (string) $sheet['sheetId'];
            $r_id = (string) $sheet->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships')->id;
            $sheet_names[$r_id] = ['name' => $name, 'sheetId' => $sheet_id];
        }

        $rels_xml = $zip->getFromName('xl/_rels/workbook.xml.rels');

        if (!$rels_xml) {
            return false;
        }

        $rels_xml_obj = simplexml_load_string($rels_xml);

        if ($rels_xml_obj === false) {
            return false;
        }

        foreach ($rels_xml_obj->Relationship as $rel) {
            $r_id = (string) $rel['Id'];
            $target = (string) $rel['Target'];

            if (isset($sheet_names[$r_id]) && $sheet_names[$r_id]['name'] === $sheet_name) {
                if (preg_match('/sheet(\d+)\.xml/', $target, $matches)) {
                    return intval($matches[1]);
                }
            }
        }

        return false;
    }

    /**
     * Parse sheet XML to array
     *
     * @param string $xml_content XML content
     * @param array $shared_strings Shared strings
     * @return array Sheet data as 2D array
     */
    private function parse_sheet_xml($xml_content, $shared_strings) {
        $xml = simplexml_load_string($xml_content);

        if ($xml === false) {
            return [];
        }

        $data = [];

        foreach ($xml->sheetData->row as $row) {
            $row_data = [];
            $row_num = (int) $row['r'];

            foreach ($row->c as $cell) {
                $cell_ref = (string) $cell['r'];
                $cell_type = (string) $cell['t'];
                $value = '';

                if (isset($cell->v)) {
                    $value = (string) $cell->v;
                }

                if ($cell_type === 's' && isset($shared_strings[intval($value)])) {
                    $value = $shared_strings[intval($value)];
                }

                $col_letter = preg_replace('/\d+/', '', $cell_ref);
                $col_index = $this->column_letter_to_index($col_letter);

                $row_data[$col_index] = $value;
            }

            $data[$row_num] = $row_data;
        }

        ksort($data);

        return $data;
    }

    /**
     * Convert column letter to index (A=0, B=1, etc.)
     *
     * @param string $letter Column letter(s)
     * @return int Column index
     */
    private function column_letter_to_index($letter) {
        $letter = strtoupper($letter);
        $index = 0;
        $length = strlen($letter);

        for ($i = 0; $i < $length; $i++) {
            $index = $index * 26 + (ord($letter[$i]) - ord('A') + 1);
        }

        return $index - 1;
    }

    /**
     * Extract yields from parsed sheet data
     *
     * @param array $sheet_data Parsed sheet data
     * @return array|false Yield data or false on failure
     */
    private function extract_yields_from_sheet($sheet_data) {
        $header_row = $this->find_header_row($sheet_data);

        if ($header_row === false) {
            error_log('UK Yield Rates: Could not find header row');
            return false;
        }

        $maturity_map = $this->build_maturity_map($sheet_data[$header_row]);

        if (empty($maturity_map)) {
            error_log('UK Yield Rates: Could not map maturities');
            return false;
        }

        $latest_data = $this->find_latest_data_row($sheet_data, $header_row, $maturity_map);

        if (!$latest_data) {
            error_log('UK Yield Rates: Could not find data rows');
            return false;
        }

        $previous_data = $this->find_previous_data_row($sheet_data, $header_row, $maturity_map, $latest_data['date']);

        $yields = [];

        foreach (self::TARGET_MATURITIES as $years => $key) {
            if (!isset($maturity_map[$years])) {
                continue;
            }

            $col_index = $maturity_map[$years];
            $current_value = $latest_data['values'][$col_index] ?? null;
            $previous_value = $previous_data ? ($previous_data['values'][$col_index] ?? $current_value) : $current_value;

            if ($current_value === null || $current_value === '' || !is_numeric($current_value)) {
                continue;
            }

            $current_value = floatval($current_value);
            $previous_value = $previous_value !== null && is_numeric($previous_value) ? floatval($previous_value) : $current_value;

            $yields[$key] = [
                'maturity' => $key,
                'yield'    => $current_value,
                'change'   => round($current_value - $previous_value, 4),
                'date'     => $latest_data['date'],
            ];
        }

        return !empty($yields) ? $yields : false;
    }

    /**
     * Find header row containing maturity values
     *
     * @param array $sheet_data Parsed sheet data
     * @return int|false Row number or false
     */
    private function find_header_row($sheet_data) {
        $row_numbers = array_keys($sheet_data);

        for ($i = 0; $i < min(self::MAX_HEADER_SCAN_ROWS, count($row_numbers)); $i++) {
            $row_num = $row_numbers[$i];
            $row_data = $sheet_data[$row_num];

            // Old format: first cell contains "years:" label
            if (isset($row_data[0]) && strtolower($row_data[0]) === 'years:') {
                return $row_num;
            }

            // New format: first cell is a number (count of maturities), followed by numeric maturity values
            if (isset($row_data[0]) && is_numeric($row_data[0]) && intval($row_data[0]) > 0) {
                // Check if subsequent cells contain numeric maturity values (years)
                $has_maturities = false;
                foreach ($row_data as $col_index => $value) {
                    if ($col_index === 0) continue;
                    if ($value !== '' && $value !== null && is_numeric($value)) {
                        $years = floatval($value);
                        if ($years > 0 && in_array(intval($years), array_keys(self::TARGET_MATURITIES))) {
                            $has_maturities = true;
                            break;
                        }
                    }
                }
                if ($has_maturities) {
                    return $row_num;
                }
            }
        }

        return false;
    }

    /**
     * Build maturity to column index map
     *
     * @param array $header_row Header row data
     * @return array Maturity years => column index
     */
    private function build_maturity_map($header_row) {
        $map = [];

        foreach ($header_row as $col_index => $value) {
            if ($col_index === 0 || $value === '' || $value === null) {
                continue;
            }

            $years = floatval($value);

            if ($years > 0 && in_array(intval($years), array_keys(self::TARGET_MATURITIES))) {
                $map[intval($years)] = $col_index;
            }
        }

        return $map;
    }

    /**
     * Find the latest data row
     *
     * @param array $sheet_data Parsed sheet data
     * @param int $header_row Header row number
     * @param array $maturity_map Maturity to column map
     * @return array|false Data with 'date' and 'values' keys, or false
     */
    private function find_latest_data_row($sheet_data, $header_row, $maturity_map) {
        $row_numbers = array_keys($sheet_data);
        $start_index = array_search($header_row, $row_numbers);

        if ($start_index === false) {
            return false;
        }

        for ($i = count($row_numbers) - 1; $i > $start_index; $i--) {
            $row_num = $row_numbers[$i];
            $row_data = $sheet_data[$row_num];

            if (empty($row_data)) {
                continue;
            }

            $date_value = $row_data[0] ?? '';

            if (empty($date_value) || !is_string($date_value)) {
                continue;
            }

            $date = $this->parse_date($date_value);

            if (!$date) {
                continue;
            }

            $has_valid_data = false;

            foreach ($maturity_map as $col_index) {
                $value = $row_data[$col_index] ?? null;

                if ($value !== null && $value !== '' && is_numeric($value)) {
                    $has_valid_data = true;
                    break;
                }
            }

            if ($has_valid_data) {
                return [
                    'date'   => $date,
                    'values' => $row_data,
                ];
            }
        }

        return false;
    }

    /**
     * Find the previous data row (for calculating change)
     *
     * @param array $sheet_data Parsed sheet data
     * @param int $header_row Header row number
     * @param array $maturity_map Maturity to column map
     * @param string $current_date Current date to find row before
     * @return array|false Data with 'date' and 'values' keys, or false
     */
    private function find_previous_data_row($sheet_data, $header_row, $maturity_map, $current_date) {
        $row_numbers = array_keys($sheet_data);
        $start_index = array_search($header_row, $row_numbers);

        if ($start_index === false) {
            return false;
        }

        $found_current = false;

        for ($i = count($row_numbers) - 1; $i > $start_index; $i--) {
            $row_num = $row_numbers[$i];
            $row_data = $sheet_data[$row_num];

            if (empty($row_data)) {
                continue;
            }

            $date_value = $row_data[0] ?? '';

            if (empty($date_value) || !is_string($date_value)) {
                continue;
            }

            $date = $this->parse_date($date_value);

            if (!$date) {
                continue;
            }

            if ($date === $current_date) {
                $found_current = true;
                continue;
            }

            if ($found_current) {
                $has_valid_data = false;

                foreach ($maturity_map as $col_index) {
                    $value = $row_data[$col_index] ?? null;

                    if ($value !== null && $value !== '' && is_numeric($value)) {
                        $has_valid_data = true;
                        break;
                    }
                }

                if ($has_valid_data) {
                    return [
                        'date'   => $date,
                        'values' => $row_data,
                    ];
                }
            }
        }

        return false;
    }

    /**
     * Parse date from various formats
     *
     * @param string $date_string Date string
     * @return string|false Y-m-d format or false
     */
    private function parse_date($date_string) {
        $date_string = trim($date_string);

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_string)) {
            return $date_string;
        }

        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $date_string, $matches)) {
            return sprintf('%04d-%02d-%02d', $matches[3], $matches[1], $matches[2]);
        }

        $timestamp = strtotime($date_string);

        if ($timestamp !== false) {
            return date('Y-m-d', $timestamp);
        }

        if (is_numeric($date_string)) {
            $excel_epoch = datetime::createFromFormat('Y-m-d', '1899-12-30');
            $date = clone $excel_epoch;
            $date->modify('+' . intval($date_string) . ' days');
            return $date->format('Y-m-d');
        }

        return false;
    }

    /**
     * Format yield data consistently
     *
     * @param array $yields Yield data
     * @param string $source Data source
     * @return array Formatted yield data
     */
    private function format_yield_data($yields, $source) {
        return [
            'yields'     => $yields,
            'source'     => $source,
            'fetched_at' => current_time('mysql'),
            'date'       => reset($yields)['date'] ?? gmdate('Y-m-d'),
        ];
    }

    /**
     * Clean up temporary files
     *
     * @param string $zip_path Original ZIP path
     */
    private function cleanup_temp_files($zip_path) {
        $extract_dir = $this->temp_dir . 'extracted/';

        if (is_dir($extract_dir)) {
            $files = glob($extract_dir . '*');

            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }

            rmdir($extract_dir);
        }

        if ($zip_path && file_exists($zip_path)) {
            unlink($zip_path);
        }
    }

    /**
     * Parse uploaded ZIP file
     *
     * @param string $file_path Path to uploaded ZIP
     * @return array|false Yield data or false on failure
     */
    public function parse_uploaded_zip($file_path) {
        $yields = $this->parse_zip($file_path);

        if (!$yields) {
            return false;
        }

        return $this->format_yield_data($yields, 'csv_import');
    }

    /**
     * Parse uploaded Excel file directly
     *
     * @param string $file_path Path to uploaded Excel file
     * @return array|false Yield data or false on failure
     */
    public function parse_uploaded_excel($file_path) {
        $yields = $this->parse_excel_workbook($file_path);

        if (!$yields) {
            return false;
        }

        return $this->format_yield_data($yields, 'csv_import');
    }
}
