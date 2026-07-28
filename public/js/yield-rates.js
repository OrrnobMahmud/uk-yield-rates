/**
 * UK Yield Rates - Frontend JavaScript
 * Handles auto-refresh functionality
 */

(function() {
    'use strict';

    // Check if auto-refresh is enabled
    var autoRefreshEnabled = typeof ukYieldRates !== 'undefined' && ukYieldRates.auto_refresh === 'yes';

    if (!autoRefreshEnabled) {
        return;
    }

    var refreshInterval = (ukYieldRates.refresh_interval || 5) * 60 * 1000; // Convert to milliseconds

    /**
     * Refresh yield data
     */
    function refreshYieldData() {
        var containers = document.querySelectorAll('[data-uk-yield-rates]');

        if (containers.length === 0) {
            return;
        }

        containers.forEach(function(container) {
            container.classList.add('uk-yield-loading');

            // Get current shortcode attributes from container
            var maturity = container.getAttribute('data-maturity') || 'all';
            var format = container.getAttribute('data-format') || 'inline';

            // Make AJAX request
            var xhr = new XMLHttpRequest();
            xhr.open('POST', ukYieldRates.ajax_url, true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        var response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            container.innerHTML = response.data.html;
                        }
                    } catch (e) {
                        console.error('UK Yield Rates: Error parsing response');
                    }
                }
                container.classList.remove('uk-yield-loading');
            };

            xhr.onerror = function() {
                console.error('UK Yield Rates: AJAX error');
                container.classList.remove('uk-yield-loading');
            };

            xhr.send('action=uk_yield_refresh&nonce=' + ukYieldRates.nonce);
        });
    }

    // Start auto-refresh
    if (refreshInterval > 0) {
        setInterval(refreshYieldData, refreshInterval);
    }
})();
