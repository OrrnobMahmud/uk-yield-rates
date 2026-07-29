<?php
/**
 * Import Handler for UK Yield Rates
 * Handles file uploads (ZIP, XLSX, XLS, CSV)
 *
 * @package UK_Yield_Rates
 * @version 1.3.1
 * @license GPL-2.0-or-later
 * @author Orrnob Mahmud
 */

if (!defined('ABSPATH')) {
    exit;
}

class UK_Yield_Import_Handler {

    /**
     * Single instance
     */
    private static $instance = null;

    /**
     * BoE provider instance
     */
    private $boe_provider;

    /**
     * Allowed file types
     */
    private $allowed_types = [
        'zip'  => 'application/zip',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'xls'  => 'application/vnd.ms-excel',
        'csv'  => 'text/csv',
    ];

    /**
     * Maximum file size (10MB)
     */
    const MAX_FILE_SIZE = 10 * 1024 * 1024;

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
        $this->boe_provider = UK_Yield_BoE_Provider::get_instance();
    }

    /**
     * Handle file upload
     *
     * @param array $file $_FILES array element
     * @return array Result with 'success', 'data', and 'message' keys
     */
    public function handle_upload($file) {
        $validation = $this->validate_upload($file);

        if (!$validation['success']) {
            return $validation;
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        switch ($extension) {
            case 'zip':
                return $this->process_zip_upload($file['tmp_name']);

            case 'xlsx':
            case 'xls':
                return $this->process_excel_upload($file['tmp_name'], $extension);

            case 'csv':
                return $this->process_csv_upload($file['tmp_name']);

            default:
                return [
                    'success' => false,
                    'message' => __('Unsupported file type.', 'uk-yield-rates'),
                ];
        }
    }

    /**
     * Validate upload file
     *
     * @param array $file $_FILES array element
     * @return array Validation result
     */
    private function validate_upload($file) {
        if (!isset($file['error']) || is_array($file['error'])) {
            return [
                'success' => false,
                'message' => __('Invalid file upload.', 'uk-yield-rates'),
            ];
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $error_messages = [
                UPLOAD_ERR_INI_SIZE   => __('File exceeds server size limit.', 'uk-yield-rates'),
                UPLOAD_ERR_FORM_SIZE  => __('File exceeds form size limit.', 'uk-yield-rates'),
                UPLOAD_ERR_PARTIAL    => __('File was only partially uploaded.', 'uk-yield-rates'),
                UPLOAD_ERR_NO_FILE    => __('No file was uploaded.', 'uk-yield-rates'),
                UPLOAD_ERR_NO_TMP_DIR => __('Missing temporary folder.', 'uk-yield-rates'),
                UPLOAD_ERR_CANT_WRITE => __('Failed to write file to disk.', 'uk-yield-rates'),
                UPLOAD_ERR_EXTENSION  => __('Upload stopped by extension.', 'uk-yield-rates'),
            ];

            $message = $error_messages[$file['error']] ?? __('Upload error.', 'uk-yield-rates');

            return [
                'success' => false,
                'message' => $message,
            ];
        }

        if ($file['size'] > self::MAX_FILE_SIZE) {
            return [
                'success' => false,
                'message' => __('File exceeds maximum size of 10MB.', 'uk-yield-rates'),
            ];
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!isset($this->allowed_types[$extension])) {
            return [
                'success' => false,
                'message' => __('File type not allowed. Accepted: ZIP, XLSX, XLS, CSV.', 'uk-yield-rates'),
            ];
        }

        return ['success' => true];
    }

    /**
     * Process ZIP upload
     *
     * @param string $tmp_path Temporary file path
     * @return array Result
     */
    private function process_zip_upload($tmp_path) {
        $yields = $this->boe_provider->parse_uploaded_zip($tmp_path);

        if (!$yields) {
            return [
                'success' => false,
                'message' => __('Could not parse ZIP file. Ensure it contains a Bank of England GLC Nominal workbook.', 'uk-yield-rates'),
            ];
        }

        return $this->store_imported_data($yields);
    }

    /**
     * Process Excel upload
     *
     * @param string $tmp_path Temporary file path
     * @param string $extension File extension
     * @return array Result
     */
    private function process_excel_upload($tmp_path, $extension) {
        if ($extension === 'xls') {
            return [
                'success' => false,
                'message' => __('XLS format is not supported. Please convert to XLSX or CSV.', 'uk-yield-rates'),
            ];
        }

        $yields = $this->boe_provider->parse_uploaded_excel($tmp_path);

        if (!$yields) {
            return [
                'success' => false,
                'message' => __('Could not parse Excel file. Ensure it follows the BoE yield curve format.', 'uk-yield-rates'),
            ];
        }

        return $this->store_imported_data($yields);
    }

    /**
     * Process CSV upload
     *
     * @param string $tmp_path Temporary file path
     * @return array Result
     */
    private function process_csv_upload($tmp_path) {
        $handle = fopen($tmp_path, 'r');

        if (!$handle) {
            return [
                'success' => false,
                'message' => __('Could not read CSV file.', 'uk-yield-rates'),
            ];
        }

        $headers = fgetcsv($handle);

        if (!$headers) {
            fclose($handle);
            return [
                'success' => false,
                'message' => __('CSV file is empty or invalid.', 'uk-yield-rates'),
            ];
        }

        $maturity_map = $this->map_csv_headers($headers);

        if (empty($maturity_map)) {
            fclose($handle);
            return [
                'success' => false,
                'message' => __('Could not find maturity columns in CSV. Expected: Date, 2Y, 5Y, 10Y, 20Y, 30Y.', 'uk-yield-rates'),
            ];
        }

        $rows = [];

        while (($row = fgetcsv($handle)) !== false) {
            $rows[] = $row;
        }

        fclose($handle);

        if (empty($rows)) {
            return [
                'success' => false,
                'message' => __('CSV file contains no data rows.', 'uk-yield-rates'),
            ];
        }

        $yields = $this->extract_yields_from_csv($rows, $maturity_map);

        if (!$yields) {
            return [
                'success' => false,
                'message' => __('Could not extract valid yield data from CSV.', 'uk-yield-rates'),
            ];
        }

        return $this->store_imported_data($yields);
    }

    /**
     * Map CSV headers to maturity columns
     *
     * @param array $headers CSV headers
     * @return array Maturity => column index
     */
    private function map_csv_headers($headers) {
        $map = [];
        $maturity_patterns = [
            2  => '/\b2\s*(-?\s*year|yr|y)\b/i',
            5  => '/\b5\s*(-?\s*year|yr|y)\b/i',
            10 => '/\b10\s*(-?\s*year|yr|y)\b/i',
            20 => '/\b20\s*(-?\s*year|yr|y)\b/i',
            30 => '/\b30\s*(-?\s*year|yr|y)\b/i',
        ];

        foreach ($headers as $index => $header) {
            $header = trim($header);

            foreach ($maturity_patterns as $years => $pattern) {
                if (preg_match($pattern, $header)) {
                    $map[$years] = $index;
                    break;
                }
            }

            if (preg_match('/^IUDM421$/i', $header)) {
                $map[2] = $index;
            } elseif (preg_match('/^IUDM423$/i', $header)) {
                $map[5] = $index;
            } elseif (preg_match('/^IUDM425$/i', $header)) {
                $map[10] = $index;
            } elseif (preg_match('/^IUDM427$/i', $header)) {
                $map[20] = $index;
            } elseif (preg_match('/^IUDM429$/i', $header)) {
                $map[30] = $index;
            }
        }

        return $map;
    }

    /**
     * Extract yields from CSV rows
     *
     * @param array $rows CSV data rows
     * @param array $maturity_map Maturity to column index
     * @return array|false Yield data or false
     */
    private function extract_yields_from_csv($rows, $maturity_map) {
        $latest_row = null;
        $previous_row = null;

        for ($i = count($rows) - 1; $i >= 0; $i--) {
            $row = $rows[$i];

            if (empty($row[0])) {
                continue;
            }

            $date = $this->parse_csv_date($row[0]);

            if (!$date) {
                continue;
            }

            $has_data = false;

            foreach ($maturity_map as $col_index) {
                if (isset($row[$col_index]) && $row[$col_index] !== '' && is_numeric($row[$col_index])) {
                    $has_data = true;
                    break;
                }
            }

            if (!$has_data) {
                continue;
            }

            if ($latest_row === null) {
                $latest_row = ['date' => $date, 'row' => $row];
            } elseif ($previous_row === null) {
                $previous_row = ['date' => $date, 'row' => $row];
                break;
            }
        }

        if (!$latest_row) {
            return false;
        }

        $yields = [];

        foreach (UK_Yield_BoE_Provider::TARGET_MATURITIES as $years => $key) {
            if (!isset($maturity_map[$years])) {
                continue;
            }

            $col_index = $maturity_map[$years];
            $current_value = $latest_row['row'][$col_index] ?? null;
            $previous_value = $previous_row ? ($previous_row['row'][$col_index] ?? $current_value) : $current_value;

            if ($current_value === null || $current_value === '' || !is_numeric($current_value)) {
                continue;
            }

            $current_value = floatval($current_value);
            $previous_value = $previous_value !== null && is_numeric($previous_value) ? floatval($previous_value) : $current_value;

            $yields[$key] = [
                'maturity' => $key,
                'yield'    => $current_value,
                'change'   => round($current_value - $previous_value, 4),
                'date'     => $latest_row['date'],
            ];
        }

        return !empty($yields) ? [
            'yields'     => $yields,
            'source'     => 'csv_import',
            'fetched_at' => current_time('mysql'),
            'date'       => reset($yields)['date'] ?? gmdate('Y-m-d'),
        ] : false;
    }

    /**
     * Parse date from CSV
     *
     * @param string $date_string Date string
     * @return string|false Y-m-d format or false
     */
    private function parse_csv_date($date_string) {
        $date_string = trim($date_string, ' "');

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

        return false;
    }

    /**
     * Store imported data to options and cache
     *
     * @param array $yields Formatted yield data
     * @return array Result
     */
    private function store_imported_data($yields) {
        if (empty($yields['yields'])) {
            return [
                'success' => false,
                'message' => __('No valid yield data found.', 'uk-yield-rates'),
            ];
        }

        foreach ($yields['yields'] as $maturity => $data) {
            update_option('uk_yield_rates_manual_' . $maturity . '_yield', $data['yield']);
            update_option('uk_yield_rates_manual_' . $maturity . '_change', $data['change']);
        }

        update_option('uk_yield_rates_manual_date', $yields['date']);

        $cache = UK_Yield_Cache::get_instance();
        $cache->set_cached_data($yields);

        return [
            'success' => true,
            'data'    => $yields,
            'message' => sprintf(
                __('Successfully imported yields for %s. Data saved and cache refreshed.', 'uk-yield-rates'),
                $yields['date']
            ),
        ];
    }

    /**
     * Trigger automatic BoE download
     *
     * @return array Result
     */
    public function auto_download_boe() {
        if (!$this->boe_provider->is_available()) {
            return [
                'success' => false,
                'message' => __('Required PHP extensions not available. Please install zip extension.', 'uk-yield-rates'),
            ];
        }

        $yields = $this->boe_provider->fetch_yields();

        if (!$yields) {
            return [
                'success' => false,
                'message' => __('Failed to download or parse BoE data. Please check your network connection and try again.', 'uk-yield-rates'),
            ];
        }

        return $this->store_imported_data($yields);
    }
}
