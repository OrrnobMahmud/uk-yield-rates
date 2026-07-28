jQuery(document).ready(function($) {
    // Tab navigation
    $('.nav-tab-wrapper .nav-tab').on('click', function(e) {
        e.preventDefault();
        var target = $(this).attr('href');

        // Update active tab
        $('.nav-tab').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');

        // Show target panel
        $('.tab-panel').removeClass('active');
        $(target).addClass('active');
    });

    // Show/hide sections based on data source selection
    $('#uk-yield-data-source').on('change', function() {
        var source = $(this).val();

        // Hide all sections first
        $('#uk-yield-manual-entry').hide();
        $('#uk-yield-boe-custom-settings').hide();
        $('#uk-yield-fred-settings').hide();

        // Show relevant section
        if (source === 'manual') {
            $('#uk-yield-manual-entry').show();
        } else if (source === 'boe_custom') {
            $('#uk-yield-boe-custom-settings').show();
        } else if (source === 'fred') {
            $('#uk-yield-fred-settings').show();
        } else if (source === 'auto') {
            // Show manual entry in auto mode (primary source)
            $('#uk-yield-manual-entry').show();
        }
    });

    // Manual entry field validation
    function validateManualEntry() {
        var isValid = true;
        var errors = [];

        // Check if at least one yield value is entered
        var hasAnyYield = false;
        var maturities = ['2', '5', '10', '20', '30'];

        maturities.forEach(function(maturity) {
            var yieldValue = $('input[name="uk_yield_rates_manual_' + maturity + '_yield"]').val();
            if (yieldValue !== '' && yieldValue !== null) {
                hasAnyYield = true;

                // Validate numeric value
                if (isNaN(parseFloat(yieldValue)) || !isFinite(yieldValue)) {
                    errors.push('Invalid yield value for ' + maturity + '-Year');
                    isValid = false;
                } else if (parseFloat(yieldValue) < 0 || parseFloat(yieldValue) > 100) {
                    errors.push('Yield value for ' + maturity + '-Year must be between 0 and 100');
                    isValid = false;
                }

                // Validate change value
                var changeValue = $('input[name="uk_yield_rates_manual_' + maturity + '_change"]').val();
                if (changeValue !== '' && changeValue !== null && changeValue !== '0') {
                    if (isNaN(parseFloat(changeValue)) || !isFinite(changeValue)) {
                        errors.push('Invalid change value for ' + maturity + '-Year');
                        isValid = false;
                    }
                }
            }
        });

        // Check date field
        var dateValue = $('input[name="uk_yield_rates_manual_date"]').val();
        if (!dateValue) {
            errors.push('Data date is required');
            isValid = false;
        }

        if (!hasAnyYield) {
            errors.push('At least one yield value must be entered');
            isValid = false;
        }

        return {
            isValid: isValid,
            errors: errors
        };
    }

    // Show validation errors
    function showValidationErrors(errors) {
        // Remove existing error messages
        $('.uk-yield-validation-errors').remove();

        if (errors.length > 0) {
            var errorHtml = '<div class="uk-yield-validation-errors" style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 6px; padding: 12px 16px; margin: 12px 0; color: #dc2626;">';
            errorHtml += '<strong style="display: block; margin-bottom: 8px;">⚠️ Validation Errors:</strong>';
            errorHtml += '<ul style="margin: 0; padding-left: 20px;">';
            errors.forEach(function(error) {
                errorHtml += '<li>' + error + '</li>';
            });
            errorHtml += '</ul></div>';

            // Insert before submit button
            $('.uk-yield-manual-section').append(errorHtml);

            // Scroll to errors
            $('html, body').animate({
                scrollTop: $('.uk-yield-validation-errors').offset().top - 50
            }, 500);
        }
    }

    // Form submission with validation
    $('form').on('submit', function(e) {
        var source = $('#uk-yield-data-source').val();

        // Only validate manual entry if it's selected
        if (source === 'manual' || source === 'auto') {
            var validation = validateManualEntry();

            if (!validation.isValid) {
                e.preventDefault();
                showValidationErrors(validation.errors);
                return false;
            }
        }

        // Show saving indicator
        var $submitBtn = $(this).find('input[type="submit"], button[type="submit"]');
        var originalText = $submitBtn.val() || $submitBtn.text();
        $submitBtn.prop('disabled', true).val('Saving...').text('Saving...');

        // Re-enable after 3 seconds in case of error
        setTimeout(function() {
            $submitBtn.prop('disabled', false).val(originalText).text(originalText);
        }, 3000);
    });

    // Real-time validation on blur
    $('input[name*="yield"]').on('blur', function() {
        var $input = $(this);
        var value = $input.val();
        var maturity = $input.attr('name').match(/manual_(\d+)_yield/);

        if (maturity && value !== '' && value !== null) {
            if (isNaN(parseFloat(value)) || !isFinite(value)) {
                $input.css('border-color', '#dc2626');
                $input.next('.uk-yield-field-error').remove();
                $input.after('<span class="uk-yield-field-error" style="color: #dc2626; font-size: 12px; display: block; margin-top: 4px;">Must be a valid number</span>');
            } else if (parseFloat(value) < 0 || parseFloat(value) > 100) {
                $input.css('border-color', '#dc2626');
                $input.next('.uk-yield-field-error').remove();
                $input.after('<span class="uk-yield-field-error" style="color: #dc2626; font-size: 12px; display: block; margin-top: 4px;">Must be between 0 and 100</span>');
            } else {
                $input.css('border-color', '');
                $input.next('.uk-yield-field-error').remove();
            }
        } else {
            $input.css('border-color', '');
            $input.next('.uk-yield-field-error').remove();
        }
    });

    // Clear validation errors when input changes
    $('input[name*="yield"], input[name*="change"]').on('input', function() {
        $(this).css('border-color', '');
        $(this).next('.uk-yield-field-error').remove();

        // Clear global errors if they exist
        if ($('.uk-yield-validation-errors').length) {
            $('.uk-yield-validation-errors').remove();
        }
    });

    // Force refresh cache with confirmation
    $('#uk-yield-refresh-cache').on('click', function() {
        var $button = $(this);
        var originalText = $button.text();

        // Confirmation dialog
        if (!confirm('Are you sure you want to refresh the yield data? This will fetch fresh data from the configured source.')) {
            return;
        }

        // Show loading state with spinner
        $button.html('<span class="uk-yield-spinner" style="display: inline-block; width: 16px; height: 16px; border: 2px solid #f3f3f3; border-top: 2px solid #3498db; border-radius: 50%; animation: uk-yield-spin 1s linear infinite;"></span> Refreshing...').prop('disabled', true);

        // Add CSS animation for spinner
        if (!$('#uk-yield-spinner-style').length) {
            $('head').append('<style id="uk-yield-spinner-style">@keyframes uk-yield-spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }</style>');
        }

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'uk_yield_refresh_cache',
                nonce: $('[name="uk_yield_rates_settings[_wpnonce]"]').val()
            },
            success: function(response) {
                if (response.success) {
                    // Show success message
                    $button.html('✓ Data refreshed successfully!');
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    // Show error message
                    $button.html('✗ Error: ' + response.data);
                    setTimeout(function() {
                        $button.text(originalText).prop('disabled', false);
                    }, 3000);
                }
            },
            error: function() {
                // Show error message
                $button.html('✗ Error refreshing data. Please try again.');
                setTimeout(function() {
                    $button.text(originalText).prop('disabled', false);
                }, 3000);
            }
        });
    });

    // Initialize display on page load
    $('#uk-yield-data-source').trigger('change');
});
