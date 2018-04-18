/*
 This javascript file is enqueued on the locations page. These are scripts specific to the locations page.
 @see /includes/functions/enqueue-scripts.php
 @since 0.1.0
 */

jQuery(document).ready(function() {
    jQuery('#publish').val("Save");
    jQuery('#submitdiv h2 span').html('Save');
    jQuery('#misc-publishing-actions').hide();

    jQuery('#search_location_address').focus(function () {
        let field = jQuery('#search_location_address');
        if( field.val() === '') {
            field.removeAttr('required');
            jQuery('#location_address').empty()
        } else {
            field.attr('required', 'required')
        }
    });
});

function validate_address(address){

    jQuery('#possible-results').append('<span><img style="height:1.5em;" src="'+ dtLocAPI.images_uri +'spinner.svg" /></span>');
    let data = {"address": address };
    return jQuery.ajax({
        type: "POST",
        data: JSON.stringify(data),
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        url: dtLocAPI.root + 'dt/v1/locations/validate_address',
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-WP-Nonce', dtLocAPI.nonce);
        },
    })
        .done(function (data) {
            // check if multiple results
            if( data.results.length > 1 ) {
                jQuery('#validate_address_button').html('Validate Another?');
                let possible_results = jQuery('#possible-results');
                possible_results.empty().append('<br><fieldset id="multiple-results"><legend>We found these matches:</legend></fieldset>');

                jQuery.each( data.results, function( index, value ) {
                    let checked = '';
                    if( index === 0 ) {
                        checked = 'checked'
                    }
                    jQuery('#multiple-results').append( '<input type="radio" name="location_address" id="location_address'+index+'" value="'+value.formatted_address+'" '+checked+' />' +
                        '<label for="location_address'+index+'">'+value.formatted_address+'</label>' +
                        '<br>')
                });
                possible_results.append('<br><button type="submit" class="button">Save</button><a href="'+window.location.href+'" class="button">Reset</a> ')
            }
            else
            {
                jQuery('#validate_address_button').html('Validate Another?');
                jQuery('#possible-results').empty().append('<p><fieldset id="multiple-results">' +
                    '<legend>We found this match. Is this correct? If not validate another.</legend>' +
                    '<input type="radio" name="location_address" id="location_address" value="'+data.results[0].formatted_address+'" checked/>' +
                    '<label for="location_address">'+data.results[0].formatted_address+'</label>' +
                    '</fieldset></p>' +
                    '<p><button type="submit" class="button">Save</button><a href="'+window.location.href+'" class="button">Reset</a> </p>' +
                    '')
            }
        })
        .fail(function (err) {
            console.log("error");
            console.log(err);
            jQuery("#errors").append(err.responseText);
            jQuery('#validate_address_button').html('Validate Another?');
            jQuery('#possible-results').empty().append('<fieldset id="multiple-results"><legend>We found no matching locations. Check your address and validate again.</legend></fieldset>')
        })
}

function auto_build_location( post_id ) {

    let data = { "post_id": post_id };
    console.log(data)
    return jQuery.ajax({
        type: "POST",
        data: JSON.stringify(data),
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        url: dtLocAPI.root + 'dt/v1/locations/auto_build_location',
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-WP-Nonce', dtLocAPI.nonce);
        },
    })
        .done(function (data) {
            // check if multiple results
            console.log( 'success' )
            console.log( data )
        })
        .fail(function (err) {
            console.log("error");
            console.log(err);
            jQuery("#errors").append(err.responseText);
        })
}