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

    // ============================================
    // BUG REPORT MODAL FUNCTIONALITY
    // ============================================

    var githubRepoUrl = 'https://github.com/OrrnobMahmud/uk-yield-rates';

    // Pre-fill system information
    function getSystemInfo() {
        return {
            pluginVersion: ukYieldAdmin.pluginVersion || '1.0.0',
            wpVersion: ukYieldAdmin.wpVersion || 'Unknown',
            phpVersion: ukYieldAdmin.phpVersion || 'Unknown',
            theme: ukYieldAdmin.theme || 'Unknown',
            url: window.location.href,
            userAgent: navigator.userAgent
        };
    }

    // Generate bug report template
    function generateBugTemplate(title, description, steps, expected, includeSystem) {
        var systemInfo = getSystemInfo();

        var template = '## Bug Description\n\n' + (description || 'Please describe the bug.') + '\n\n';

        template += '## Steps to Reproduce\n\n' + (steps || '1. \n2. \n3. ') + '\n\n';

        template += '## Expected Behavior\n\n' + (expected || 'What you expected to happen.') + '\n\n';

        if (includeSystem) {
            template += '## System Information\n\n';
            template += '- **Plugin Version:** ' + systemInfo.pluginVersion + '\n';
            template += '- **WordPress Version:** ' + systemInfo.wpVersion + '\n';
            template += '- **PHP Version:** ' + systemInfo.phpVersion + '\n';
            template += '- **Active Theme:** ' + systemInfo.theme + '\n';
            template += '- **Page URL:** ' + systemInfo.url + '\n\n';
        }

        template += '## Additional Context\n\nAdd any other context about the problem here.';

        return template;
    }

    // Generate feature request template
    function generateFeatureTemplate(title, description) {
        var template = '## Feature Description\n\n' + (description || 'Please describe the feature you\'d like to see.') + '\n\n';

        template += '## Use Case\n\nExplain why this feature would be useful and how you would use it.\n\n';

        template += '## Proposed Solution\n\nIf you have ideas on how this could be implemented, describe them here.\n\n';

        template += '## Additional Context\n\nAdd any other context, screenshots, or examples here.';

        return template;
    }

    // Open GitHub issue in new window
    function openGitHubIssue(title, body, labels) {
        var params = {
            title: title,
            body: body
        };

        if (labels && labels.length > 0) {
            params.labels = labels.join(',');
        }

        var queryString = Object.keys(params).map(function(key) {
            return key + '=' + encodeURIComponent(params[key]);
        }).join('&');

        var issueUrl = githubRepoUrl + '/issues/new?' + queryString;

        window.open(issueUrl, '_blank', 'width=800,height=600,scrollbars=yes,resizable=yes');
    }

    // Modal functionality
    function openModal(type) {
        var $modal = $('#uk-yield-bug-report-modal');
        var $title = $('#uk-yield-modal-title');
        var $bugTitle = $('#uk-yield-bug-title');
        var $description = $('#uk-yield-bug-description');

        // Reset form
        $bugTitle.val('');
        $description.val('');
        $('#uk-yield-bug-steps').val('');
        $('#uk-yield-bug-expected').val('');
        $('#uk-yield-bug-include-system').prop('checked', true);

        if (type === 'bug') {
            $title.text(ukYieldAdmin.i18n.reportBug || 'Report a Bug');
            $bugTitle.attr('placeholder', ukYieldAdmin.i18n.bugTitlePlaceholder || 'Brief description of the issue');
            $description.attr('placeholder', ukYieldAdmin.i18n.bugDescPlaceholder || 'Detailed description of the bug...');
        } else {
            $title.text(ukYieldAdmin.i18n.requestFeature || 'Request a Feature');
            $bugTitle.attr('placeholder', ukYieldAdmin.i18n.featureTitlePlaceholder || 'Feature name');
            $description.attr('placeholder', ukYieldAdmin.i18n.featureDescPlaceholder || 'Describe the feature you\'d like...');
        }

        $modal.data('type', type);
        $modal.fadeIn(200);
        $bugTitle.focus();
    }

    function closeModal() {
        $('#uk-yield-bug-report-modal').fadeOut(200);
    }

    // Event handlers for bug report buttons
    $('#uk-yield-report-bug').on('click', function() {
        openModal('bug');
    });

    $('#uk-yield-request-feature').on('click', function() {
        openModal('feature');
    });

    // Close modal
    $('#uk-yield-modal-close, #uk-yield-modal-cancel').on('click', function() {
        closeModal();
    });

    // Close on outside click
    $(document).on('click', '#uk-yield-bug-report-modal', function(e) {
        if ($(e.target).is('#uk-yield-bug-report-modal')) {
            closeModal();
        }
    });

    // Close on ESC key
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape' && $('#uk-yield-bug-report-modal').is(':visible')) {
            closeModal();
        }
    });

    // Submit bug report
    $('#uk-yield-modal-submit').on('click', function() {
        var $modal = $('#uk-yield-bug-report-modal');
        var type = $modal.data('type');
        var title = $('#uk-yield-bug-title').val().trim();
        var description = $('#uk-yield-bug-description').val().trim();
        var steps = $('#uk-yield-bug-steps').val().trim();
        var expected = $('#uk-yield-bug-expected').val().trim();
        var includeSystem = $('#uk-yield-bug-include-system').is(':checked');

        // Validation
        if (!title) {
            alert(ukYieldAdmin.i18n.enterTitle || 'Please enter an issue title.');
            $('#uk-yield-bug-title').focus();
            return;
        }

        if (!description) {
            alert(ukYieldAdmin.i18n.enterDescription || 'Please provide a description.');
            $('#uk-yield-bug-description').focus();
            return;
        }

        var body, labels;

        if (type === 'bug') {
            body = generateBugTemplate(title, description, steps, expected, includeSystem);
            labels = ['bug'];
        } else {
            body = generateFeatureTemplate(title, description);
            labels = ['enhancement'];
        }

        openGitHubIssue(title, body, labels);
        closeModal();

        // Show success message
        var successMsg = $('<div class="uk-yield-success" style="position: fixed; top: 50px; right: 20px; z-index: 100000; max-width: 400px;">' + (ukYieldAdmin.i18n.issueOpened || '✓ GitHub issue page opened! Please submit the issue there.') + '</div>');
        $('body').append(successMsg);
        setTimeout(function() {
            successMsg.fadeOut(500, function() {
                $(this).remove();
            });
        }, 5000);
    });

    // Allow Ctrl+Enter to submit
    $('#uk-yield-bug-description, #uk-yield-bug-steps, #uk-yield-bug-expected').on('keydown', function(e) {
        if (e.ctrlKey && e.key === 'Enter') {
            $('#uk-yield-modal-submit').click();
        }
    });

    // Character count for textareas
    $('textarea').on('input', function() {
        var $textarea = $(this);
        var maxLength = 4000; // GitHub issue body limit
        var currentLength = $textarea.val().length;

        // Remove existing counter
        $textarea.next('.uk-yield-char-count').remove();

        if (currentLength > maxLength * 0.9) {
            var counterClass = currentLength > maxLength ? 'uk-yield-error' : 'uk-yield-warning';
            $textarea.after('<span class="uk-yield-char-count ' + counterClass + '">' + currentLength + '/' + maxLength + ' characters</span>');
        }
    });
});
