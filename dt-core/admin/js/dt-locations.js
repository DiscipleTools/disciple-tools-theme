/*
 This javascript file is enqueued on the locations page. These are scripts specific to the locations page.
 @see /includes/functions/enqueue-scripts.php
 @since 0.1.0
 */

function validate_address(address){
    let data = {"address": address };
    return jQuery.ajax({
        type: "POST",
        data: JSON.stringify(data),
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        url: zumeMaps.root + 'zume/v1/locations/validate_address',
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-WP-Nonce', zumeMaps.nonce);
        },
    })
        .done(function (data) {

            // check if multiple results
            if( data.results.length > 1 ) {

                jQuery('#map').empty()
                jQuery('#validate_address_button').val('Validate Another?')

                jQuery('#possible-results').empty().append('<fieldset id="multiple-results"><legend>We found these matches:</legend></fieldset>')


                jQuery.each( data.results, function( index, value ) {
                    let checked = ''
                    if( index === 0 ) {
                        checked = 'checked'
                    }
                    jQuery('#multiple-results').append( '<input type="radio" name="zume_user_address" id="zume_user_address'+index+'" value="'+value.formatted_address+'" '+checked+' /><label for="zume_user_address'+index+'">'+value.formatted_address+'</label><br>')
                })
            }
            else
            {
                jQuery('#map').empty()
                jQuery('#possible-results').empty().append('<fieldset id="multiple-results"><legend>We found this match. Is this correct? If not validate another.</legend><input type="radio" name="zume_user_address" id="zume_user_address" value="'+data.results[0].formatted_address+'" checked/><label for="zume_user_address">'+data.results[0].formatted_address+'</label></fieldset>')
                jQuery('#submit_profile').removeAttr('disabled')
            }

        })
        .fail(function (err) {
            console.log("error")
            console.log(err)
            jQuery("#errors").append(err.responseText)
            jQuery('#map').empty()
            jQuery('#validate_address_button').val('Validate Another?')
            jQuery('#possible-results').empty().append('<fieldset id="multiple-results"><legend>We found no matching locations. Check your address and validate again.</legend></fieldset>')
        })
}