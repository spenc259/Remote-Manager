jQuery(document).ready(function($){
    $('button.base-push-updates').on('click', function(e){
        console.log('pushed');

        e.preventDefault();

        var data = {
            action: 'theme_pusher',
            noonce: 'theme_pusher',
            url: $(this).data('url')
        }

        var button = $(this);

        $('.loader-wrap').show();
        button.text('Updating...');

        var request = $.post(zip_theme.ajaxurl, data, function( response ) {
            console.log('pushed request');
            console.log( response );
        });

         // Callback handler that will be called on success
        request.done(function (response, textStatus, jqXHR){
            // Log a message to the console
            console.log("Hooray, it worked!");
            $('.loader-wrap').hide();
            button.text('Push Updates');         
        });

        // Callback handler that will be called on failure
        request.fail(function (jqXHR, textStatus, errorThrown){
            // Log the error to the console
            console.error(
                "The following error occurred: "+
                textStatus, errorThrown
            );
        });

        
    });
});