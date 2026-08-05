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
        $('#uk-yield-manual-section').hide();
        $('#uk-yield-api-section').hide();

        // Show relevant section
        if (source === 'manual') {
            $('#uk-yield-manual-section').show();
        } else if (source === 'api') {
            $('#uk-yield-api-section').show();
        }
    });

    // Initialize display on page load
    $('#uk-yield-data-source').trigger('change');

    // Force refresh cache with confirmation
    $('#uk-yield-refresh-cache').on('click', function() {
        var $button = $(this);
        var originalText = $button.text();

        // Confirmation dialog
        if (!confirm('Are you sure you want to refresh the yield data?')) {
            return;
        }

        // Show loading state with spinner
        $button.html('<span class="uk-yield-spinner" style="display: inline-block; width: 16px; height: 16px; border: 2px solid #f3f3f3; border-top: 2px solid #3498db; border-radius: 50%; animation: uk-yield-spin 1s linear infinite;"></span> Refreshing...').prop('disabled', true);

        // Add CSS animation for spinner
        if (!$('#uk-yield-spinner-style').length) {
            $('head').append('<style id="uk-yield-spinner-style">@keyframes uk-yield-spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }</style>');
        }

        $.ajax({
            url: ukYieldAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'uk_yield_refresh_cache',
                nonce: ukYieldAdmin.nonce
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

    // Force server refresh (requires API key)
    $('#uk-yield-trigger-refresh').on('click', function() {
        var $button = $(this);
        var originalText = $button.text();

        if (!confirm('Force a server-side data refresh? This requires a valid API key.')) {
            return;
        }

        $button.html('<span class="uk-yield-spinner" style="display: inline-block; width: 16px; height: 16px; border: 2px solid #f3f3f3; border-top: 2px solid #3498db; border-radius: 50%; animation: uk-yield-spin 1s linear infinite;"></span> Refreshing...').prop('disabled', true);

        $.ajax({
            url: ukYieldAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'uk_yield_trigger_server_refresh',
                nonce: ukYieldAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    $button.html('✓ Server refresh triggered!');
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    $button.html('✗ Error: ' + response.data);
                    setTimeout(function() {
                        $button.text(originalText).prop('disabled', false);
                    }, 3000);
                }
            },
            error: function() {
                $button.html('✗ Error triggering refresh. Please try again.');
                setTimeout(function() {
                    $button.text(originalText).prop('disabled', false);
                }, 3000);
            }
        });
    });

    // ============================================
    // BUG REPORT MODAL FUNCTIONALITY
    // ============================================

    var githubRepoUrl = 'https://github.com/OrrnobMahmud/uk-yield-rates';

    // Pre-fill system information
    function getSystemInfo() {
        return {
            pluginVersion: ukYieldAdmin.pluginVersion || '2.1.0',
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
