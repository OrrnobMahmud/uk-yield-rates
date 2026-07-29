<?php
/**
 * Cache Handler for UK Yield Rates
 * Uses WordPress Transients API for caching
 *
 * @package UK_Yield_Rates
 * @version 1.3.1
 * @license GPL-2.0-or-later
 * @author Orrnob Mahmud
 */

if (!defined('ABSPATH')) {
    exit;
}

class UK_Yield_Cache {

    /**
     * Single instance
     */
    private static $instance = null;

    /**
     * Cache key prefix
     */
    const CACHE_KEY = 'uk_yield_rates';

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
     * Get cached yield data
     */
    public function get_cached_data() {
        $data = get_transient(self::CACHE_KEY);

        if ($data === false) {
            return false;
        }

        // Ensure is_stale key always exists
        $data['is_stale'] = false;

        // Check if cache is stale (beyond expected duration)
        $cache_time = $this->get_cache_time();
        $max_age = $this->get_cache_duration() * HOUR_IN_SECONDS;

        if ((time() - $cache_time) > $max_age) {
            $data['is_stale'] = true;
        }

        return $data;
    }

    /**
     * Set cached yield data
     */
    public function set_cached_data($data) {
        $duration = $this->get_cache_duration() * HOUR_IN_SECONDS;
        return set_transient(self::CACHE_KEY, $data, $duration);
    }

    /**
     * Clear cache
     */
    public function clear_cache() {
        return delete_transient(self::CACHE_KEY);
    }

    /**
     * Check if cache exists
     */
    public function has_cache() {
        return false !== get_transient(self::CACHE_KEY);
    }

    /**
     * Get cache timestamp
     */
    public function get_cache_time() {
        $data = get_transient(self::CACHE_KEY);
        return isset($data['fetched_at']) ? strtotime($data['fetched_at']) : 0;
    }

    /**
     * Get cache duration in hours
     */
    private function get_cache_duration() {
        $duration = get_option('uk_yield_rates_cache_duration', 1);

        return max(1, intval($duration));
    }

    /**
     * Get formatted cache info
     */
    public function get_cache_info() {
        $data = $this->get_cached_data();

        if (!$data) {
            return [
                'has_cache' => false,
                'is_stale' => false,
                'last_updated' => null,
                'source' => null,
            ];
        }

        return [
            'has_cache' => true,
            'is_stale' => isset($data['is_stale']) && $data['is_stale'],
            'last_updated' => $data['fetched_at'] ?? null,
            'source' => $data['source'] ?? null,
            'cache_time' => $this->get_cache_time(),
        ];
    }

    /**
     * Get yield data (from cache or fetch fresh)
     */
    public function get_yields() {
        // Try cache first
        $data = $this->get_cached_data();

        if ($data && !$data['is_stale']) {
            return $data;
        }

        // Fetch fresh data
        $api = UK_Yield_API::get_instance();
        $fresh_data = $api->fetch_yields();

        if ($fresh_data) {
            $this->set_cached_data($fresh_data);
            return $fresh_data;
        }

        // Return stale data if available
        if ($data) {
            return $data;
        }

        return false;
    }

    /**
     * Force refresh cache
     */
    public function force_refresh() {
        $api = UK_Yield_API::get_instance();
        $fresh_data = $api->fetch_yields();

        if ($fresh_data) {
            $this->set_cached_data($fresh_data);
            return true;
        }

        return false;
    }
}
