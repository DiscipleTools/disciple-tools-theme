function add_input_fields() {
  "use strict";
  jQuery('#add_input').html('<table width="100%"><tr><td><hr><br><form method="post"><input type="text" name="label" placeholder="label" /><input type="text" name="description" placeholder="description" /><button type="submit">Add</button></td></tr></table>');
}

function import_list() {
    let country = jQuery('#country option:checked').val()
    let list = jQuery('#import-list').val()
    let div = jQuery('#results')

    div.append('<span id="spinner"><img style="height:1.5em;" src="'+ dtOptionAPI.images_uri +'spinner.svg" /></span><br>')

    // create array of items
    let lines = list.split(/\n/);
    let texts = [];
    for (let i=0; i < lines.length; i++) {
        // only push this line if it contains a non whitespace character.
        if (/\S/.test(lines[i])) {
            texts.push(jQuery.trim(lines[i]));
        }
    }

    function delay(increment) {
        if ( 0 === increment) {
            return new Promise(resolve => setTimeout(resolve, 4000));
        } else {
            return new Promise(resolve => setTimeout(resolve, 500));
        }
    }

    async function delayedLog(item, increment) {

        div.append( '<span id="result_'+increment+'">' + item + '</span><br>')

        let data = { "data": item, "type": 'address', "components": {"country": country } }
        jQuery.ajax({
            type: "POST",
            data: JSON.stringify(data),
            contentType: "application/json; charset=utf-8",
            dataType: "json",
            url: dtOptionAPI.root + 'dt/v1/locations/auto_build_location',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', dtOptionAPI.nonce);
            },
        })
            .done(function (data) {
                // check if multiple results
                jQuery('#result_' + increment ).append(' &#x2714;')
                console.log( 'success ' + item )
                console.log( data )
            })
            .fail(function (err) {
                jQuery('#result_' + increment ).append(' Error!')
                console.log("error");
                console.log(err);
                jQuery("#errors").append(err.responseText);
            })

        await delay( increment );
    }

    // process items in order
    async function processArray(array) {
        let i = 0
        for (const item of array) {
            await delayedLog(item, i );
            i++
        }
        console.log('Done!')
        jQuery('#spinner').empty()
    }
    processArray(texts)

    // jQuery.each(texts, function( index, value ) {
    //     jQuery('#results').append( '<span id="result'+index+'">' + value + '</span><br>')
    //
    //     let data = { "data": value, "type": 'address' }
    //     jQuery.ajax({
    //         type: "POST",
    //         data: JSON.stringify(data),
    //         contentType: "application/json; charset=utf-8",
    //         dataType: "json",
    //         url: dtOptionAPI.root + 'dt/v1/locations/auto_build_location',
    //         beforeSend: function(xhr) {
    //             xhr.setRequestHeader('X-WP-Nonce', dtOptionAPI.nonce);
    //         },
    //     })
    //         .done(function (data) {
    //             // check if multiple results
    //             jQuery('#result' + index ).append(' &#x2714;')
    //             console.log( 'success' )
    //             console.log( data )
    //         })
    //         .fail(function (err) {
    //             jQuery('#result' + index ).append(' Error!')
    //             console.log("error");
    //             console.log(err);
    //             jQuery("#errors").append(err.responseText);
    //         })
    // })


}