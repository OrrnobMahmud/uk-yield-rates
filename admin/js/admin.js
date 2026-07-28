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
                    alert('Cache refreshed successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + response.data);
                }
            },
            error: function() {
                alert('Error refreshing cache. Please try again.');
            },
            complete: function() {
                $button.text(originalText).prop('disabled', false);
            }
        });
    });
});
