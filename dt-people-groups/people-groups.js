jQuery(document).ready(function () {
    "use strict";

    jQuery('#edit-slug-box').hide();


});

function group_search() {
    let sval = jQuery('#group-search').val();
    let data = { "s": sval }
    let search_button = jQuery('#search_button')
    search_button.append(' <img style="height:1em;" src="'+ dtPeopleGroupsAPI.images_uri +'spinner.svg" />')

    jQuery.ajax({
        type: "POST",
        data: JSON.stringify(data),
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        url: dtPeopleGroupsAPI.root + 'dt/v1/people-groups/search_csv',
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-WP-Nonce', dtPeopleGroupsAPI.nonce);
        },
    })
        .done(function (data) {
            let div = jQuery('#results')
            div.empty()
            search_button.empty().text('Get List')


            div.append(`<dl><dt><strong>`+sval+`</strong></dt>`)

            jQuery.each(data, function(i, v) {
                div.append(`
                <dd>`+ v[4] +` ( `+ v[1] + ` | ` + v[3] +` ) <button onclick="add_single_people_group('`+v[3]+`','`+v[1]+`')" id="button-`+v[3]+`">add</button> <span id="message-`+v[3]+`"></span></dd>
                `)
            })
            div.append(`</dl>`)

        })
        .fail(function (err) {
            console.log("error");
            console.log(err);
        })
}


function add_single_people_group( rop3, country ) {
    let data = { "rop3": rop3, "country": country }
    let button = jQuery( '#button-' + rop3 )
    let message = jQuery('#message-' + rop3 )


    button.attr('disabled', 'disabled').append(' <img style="height:1em;" src="'+ dtPeopleGroupsAPI.images_uri +'spinner.svg" />')

    jQuery.ajax({
        type: "POST",
        data: JSON.stringify(data),
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        url: dtPeopleGroupsAPI.root + 'dt/v1/people-groups/add_single_people_group',
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-WP-Nonce', dtPeopleGroupsAPI.nonce);
        },
    })
        .done(function (data) {
            if ( data.status === 'Success' ) {
                button.text('installed')
            }
            if ( data.status === 'Duplicate' ) {
                button.text('duplicate')
            }

            console.log(data)
        })
        .fail(function (err) {
            console.log("error");
            console.log(err);
        })
}

function link_search() {
    let country = jQuery('#country').val()
    let rop3 = jQuery('#rop3').val()
    let post_id = jQuery('#post_id').val()
    let search_button = jQuery('#search_button')
    search_button.append(' <img style="height:1em;" src="'+ dtPeopleGroupsAPI.images_uri +'spinner.svg" />')

    if( country ) {
        let data = { "s": country }
        jQuery.ajax({
            type: "POST",
            data: JSON.stringify(data),
            contentType: "application/json; charset=utf-8",
            dataType: "json",
            url: dtPeopleGroupsAPI.root + 'dt/v1/people-groups/search_csv',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', dtPeopleGroupsAPI.nonce);
            },
        })
            .done(function (data) {
                let div = jQuery('#results')
                div.empty()
                search_button.empty().text('Search')

                div.append(`<dl><dt><strong>`+country+`</strong></dt>`)

                jQuery.each(data, function(i, v) {
                    div.append(`
                <dd>`+ v[4] +` ( `+ v[1] + ` | ` + v[3] +` ) <button type="button" onclick="link_or_update('`+v[3]+`','`+v[1]+`','`+post_id+`')" id="button-`+v[3]+`">link</button> <span id="message-`+v[3]+`"></span></dd>
                `)
                })

                div.append(`</dl>`)
            })
            .fail(function (err) {
                console.log("error");
                console.log(err);
            })
    } else {
        let data = { "rop3": rop3 }
        let div = jQuery('#results')
        jQuery.ajax({
            type: "POST",
            data: JSON.stringify(data),
            contentType: "application/json; charset=utf-8",
            dataType: "json",
            url: dtPeopleGroupsAPI.root + 'dt/v1/people-groups/search_csv_by_rop3',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', dtPeopleGroupsAPI.nonce);
            },
        })
            .done(function (data) {

                div.empty()
                search_button.empty().text('Search')

                div.append(`<dl><dt><strong>Matches for `+rop3+`</strong></dt>`)

                jQuery.each(data, function(i, v) {
                    let str = v[1]
                    str = str.replace(/\s+/g, '-').toLowerCase()

                    div.append(`
                <dd>`+ v[4] +` ( `+ v[1] + ` | ` + v[3] +` ) <button type="button" onclick="link_or_update_by_rop3('`+v[3]+`','`+v[1]+`','`+post_id+`')" id="button-`+str+`">link</button> <span id="message-`+str+`"></span></dd>
                `)
                })

                div.append(`</dl>`)
            })
            .fail(function (err) {
                div.empty().text('No match found.')

                console.log("error");
                console.log(err);
            })
    }
}

function link_or_update( rop3, country, post_id ) {
    let button = jQuery( '#button-' + rop3 )
    let message = jQuery('#message-' + rop3 )
    button.attr('disabled', 'disabled').append(' <img style="height:1em;" src="'+ dtPeopleGroupsAPI.images_uri +'spinner.svg" />')

    let data = { "country": country, "rop3": rop3, "post_id": post_id }
    jQuery.ajax({
        type: "POST",
        data: JSON.stringify(data),
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        url: dtPeopleGroupsAPI.root + 'dt/v1/people-groups/link_or_update',
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-WP-Nonce', dtPeopleGroupsAPI.nonce);
        },
    })
        .done(function (data) {
            if ( data.status === 'Success' ) {
                button.text('installed')
            }

            console.log(data)
        })
        .fail(function (err) {
            console.log("error");
            console.log(err);
        })
}

function link_or_update_by_rop3( rop3, country, post_id ) {
    let str = country
    str = str.replace(/\s+/g, '-').toLowerCase()

    let button = jQuery( '#button-' + str )
    let message = jQuery('#message-' + str )
    button.attr('disabled', 'disabled').append(' <img style="height:1em;" src="'+ dtPeopleGroupsAPI.images_uri +'spinner.svg" />')

    let data = { "country": country, "rop3": rop3, "post_id": post_id }
    jQuery.ajax({
        type: "POST",
        data: JSON.stringify(data),
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        url: dtPeopleGroupsAPI.root + 'dt/v1/people-groups/link_or_update',
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-WP-Nonce', dtPeopleGroupsAPI.nonce);
        },
    })
        .done(function (data) {
            if ( data.status === 'Success' ) {
                button.text('installed')
            }

            console.log(data)
        })
        .fail(function (err) {
            console.log("error");
            console.log(err);
        })
}
