/**
 * UK Yield Rates - Frontend JavaScript
 * Handles auto-refresh functionality, animations, and tooltips
 */

(function() {
    'use strict';

    // Check if auto-refresh is enabled
    var autoRefreshEnabled = typeof ukYieldRates !== 'undefined' && ukYieldRates.auto_refresh === 'yes';

    if (!autoRefreshEnabled) {
        // Still initialize tooltips even without auto-refresh
        initTooltips();
        initYieldAnimations();
        return;
    }

    var refreshInterval = (ukYieldRates.refresh_interval || 5) * 60 * 1000; // Convert to milliseconds

    /**
     * Initialize tooltips for yield items
     */
    function initTooltips() {
        var yieldItems = document.querySelectorAll('.uk-yield-item, .uk-yield-sidebar-row');

        yieldItems.forEach(function(item) {
            // Add tooltip attributes if not present
            if (!item.getAttribute('title')) {
                var label = item.querySelector('.uk-yield-label, .uk-yield-sidebar-maturity');
                var value = item.querySelector('.uk-yield-value, .uk-yield-sidebar-yield');
                var change = item.querySelector('.uk-yield-change, .uk-yield-sidebar-change');

                if (label && value) {
                    var tooltipText = label.textContent.trim() + ': ' + value.textContent.trim();
                    if (change && change.textContent.trim()) {
                        tooltipText += ' (' + change.textContent.trim() + ')';
                    }
                    item.setAttribute('title', tooltipText);
                    item.classList.add('uk-yield-tooltip');
                }
            }
        });
    }

    /**
     * Initialize yield change animations
     */
    function initYieldAnimations() {
        var yieldValues = document.querySelectorAll('.uk-yield-value, .uk-yield-sidebar-yield, .uk-yield-td-yield');

        yieldValues.forEach(function(value) {
            // Add transition class
            value.classList.add('uk-yield-animated');

            // Store initial value for comparison
            value.setAttribute('data-initial-value', value.textContent.trim());
        });
    }

    /**
     * Animate yield value changes
     */
    function animateYieldChange(element, newValue) {
        var currentValue = element.textContent.trim();
        var initialValue = element.getAttribute('data-initial-value') || currentValue;

        // Only animate if value changed
        if (currentValue !== newValue) {
            element.classList.add('uk-yield-changing');

            // Flash effect
            setTimeout(function() {
                element.classList.remove('uk-yield-changing');
                element.classList.add('uk-yield-changed');
            }, 150);

            setTimeout(function() {
                element.classList.remove('uk-yield-changed');
            }, 1000);

            // Update stored value
            element.setAttribute('data-initial-value', newValue);
        }
    }

    /**
     * Add loading state to container
     */
    function addLoadingState(container) {
        container.classList.add('uk-yield-loading');

        // Add loading overlay
        var overlay = document.createElement('div');
        overlay.className = 'uk-yield-loading-overlay';
        overlay.innerHTML = '<div class="uk-yield-loading-spinner"></div>';
        container.style.position = 'relative';
        container.appendChild(overlay);
    }

    /**
     * Remove loading state from container
     */
    function removeLoadingState(container) {
        container.classList.remove('uk-yield-loading');

        var overlay = container.querySelector('.uk-yield-loading-overlay');
        if (overlay) {
            overlay.remove();
        }
    }

    /**
     * Refresh yield data
     */
    function refreshYieldData() {
        var containers = document.querySelectorAll('[data-uk-yield-rates]');

        if (containers.length === 0) {
            return;
        }

        containers.forEach(function(container) {
            addLoadingState(container);

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
                            // Parse new values for animation
                            var tempDiv = document.createElement('div');
                            tempDiv.innerHTML = response.data.html;

                            // Find yield values in new content and animate changes
                            var newYieldValues = tempDiv.querySelectorAll('.uk-yield-value, .uk-yield-sidebar-yield, .uk-yield-td-yield');
                            var currentYieldValues = container.querySelectorAll('.uk-yield-value, .uk-yield-sidebar-yield, .uk-yield-td-yield');

                            // Animate each value change
                            newYieldValues.forEach(function(newValue, index) {
                                if (currentYieldValues[index]) {
                                    animateYieldChange(currentYieldValues[index], newValue.textContent.trim());
                                }
                            });

                            // Apply new content with fade effect
                            container.style.opacity = '0.5';
                            setTimeout(function() {
                                container.innerHTML = response.data.html;
                                container.style.opacity = '1';

                                // Re-initialize tooltips and animations for new content
                                initTooltips();
                                initYieldAnimations();
                            }, 200);
                        }
                    } catch (e) {
                        console.error('UK Yield Rates: Error parsing response');
                    }
                }
                removeLoadingState(container);
            };

            xhr.onerror = function() {
                console.error('UK Yield Rates: AJAX error');
                removeLoadingState(container);
            };

            xhr.send('action=uk_yield_refresh&nonce=' + ukYieldRates.nonce + '&maturity=' + encodeURIComponent(maturity) + '&format=' + encodeURIComponent(format));
        });
    }

    // Initialize tooltips and animations
    initTooltips();
    initYieldAnimations();

    // Start auto-refresh
    if (refreshInterval > 0) {
        setInterval(refreshYieldData, refreshInterval);
    }
})();
