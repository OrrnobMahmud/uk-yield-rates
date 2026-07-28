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
        $('#uk-yield-fred-settings').hide();
        $('#uk-yield-financeflow-settings').hide();

        // Show relevant section
        if (source === 'manual') {
            $('#uk-yield-manual-entry').show();
        } else if (source === 'financeflow') {
            $('#uk-yield-financeflow-settings').show();
        } else if (source === 'fred') {
            $('#uk-yield-fred-settings').show();
        } else if (source === 'auto') {
            // Show all API options in auto mode
            $('#uk-yield-financeflow-settings').show();
            $('#uk-yield-fred-settings').show();
        }
    });

    // Force refresh cache
    $('#uk-yield-refresh-cache').on('click', function() {
        var $button = $(this);
        var originalText = $button.text();

        $button.text('Refreshing...').prop('disabled', true);

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'uk_yield_refresh_cache',
                nonce: $('[name="uk_yield_rates_settings[_wpnonce]"]').val()
            },
            success: function(response) {
                if (response.success) {
                    alert('Data refreshed successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + response.data);
                }
            },
            error: function() {
                alert('Error refreshing data. Please try again.');
            },
            complete: function() {
                $button.text(originalText).prop('disabled', false);
            }
        });
    });

    // Initialize display on page load
    $('#uk-yield-data-source').trigger('change');
});
