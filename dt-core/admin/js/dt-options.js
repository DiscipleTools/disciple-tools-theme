function add_input_fields() {
  "use strict";
  jQuery('#add_input').html('<table width="100%"><tr><td><hr><br><form method="post"><input type="text" name="label" placeholder="label" /><input type="text" name="description" placeholder="description" /><button type="submit">Add</button></td></tr></table>');
}

function import_list() {
    let country = jQuery('#country option:checked').val()
    let list = jQuery('#import-list').val()
    let div = jQuery('#results-import-list')
    let spinner = jQuery('#spinner')

    jQuery('.dt_import_levels').toggle();

    spinner.append('<img style="height:1.5em;" src="'+ dtOptionAPI.images_uri +'spinner.svg" />');

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

        div.prepend( '<span id="result_'+increment+'">' + item + '</span><br>')

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

}

function import_simple_list() {
    let list = jQuery('#import-list').val()
    let div = jQuery('#results')
    let spinner = jQuery('#spinner')
    let form = jQuery('.dt_simple_import_levels')

    form.toggle()

    spinner.append('<img style="height:1.5em;" src="'+ dtOptionAPI.images_uri +'spinner.svg" />');

    // create array of items
    let lines = list.split(/\n/);
    let texts = [];
    for (let i=0; i < lines.length; i++) {
        // only push this line if it contains a non whitespace character.
        if (/\S/.test(lines[i])) {
            texts.push(jQuery.trim(lines[i]));
        }
    }

    jQuery.each(texts, function( index, value ) {

        let data = { "title": value }
        jQuery.ajax({
            type: "POST",
            data: JSON.stringify(data),
            contentType: "application/json; charset=utf-8",
            dataType: "json",
            url: dtOptionAPI.root + 'dt/v1/locations/auto_build_simple_location',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', dtOptionAPI.nonce);
            },
        })
            .done(function (data) {
                // check if multiple results
                div.prepend( '<span id="result_'+index+'">' + value + ' &#x2714;</span><br>')
                console.log( 'success ' + value )
                console.log( data )
            })
            .fail(function (err) {
                jQuery('#result_' + index ).append(' Error!')
                console.log("error");
                console.log(err);
                jQuery("#errors").append(err.responseText);
            })
    })

    spinner.empty()
}

function group_search() {
    let sval = jQuery('#group-search').val();
    let data = { "s": sval }
    jQuery.ajax({
        type: "POST",
        data: JSON.stringify(data),
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        url: dtOptionAPI.root + 'dt/v1/people-groups/search_csv',
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-WP-Nonce', dtOptionAPI.nonce);
        },
    })
        .done(function (data) {
            let div = jQuery('#results')
            div.empty()

            div.append(`<dl><dt><strong>`+sval+`</strong></dt>`)

            jQuery.each(data, function(i, v) {
                div.append(`
                <dd>`+ v[4] +` (`+ v[3] +`) <button onclick="add_single_people_group(`+v[3]+`)">add</button></dd>
                `)
            })
            div.append(`</dl>`)

        })
        .fail(function (err) {
            console.log("error");
            console.log(err);
        })
}

function add_single_people_group( rop3 ) {
    let data = { "rop3": rop3 }
    console.log("add_single_people_group")
    console.log(rop3)

    jQuery.ajax({
        type: "POST",
        data: JSON.stringify(data),
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        url: dtOptionAPI.root + 'dt/v1/people-groups/add_single_people_group',
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-WP-Nonce', dtOptionAPI.nonce);
        },
    })
        .done(function (data) {
            console.log(data)

        })
        .fail(function (err) {
            console.log("error");
            console.log(err);
        })
}