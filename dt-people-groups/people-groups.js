jQuery(document).ready(function () {
    "use strict";

    jQuery('#edit-slug-box').hide();


});

function update_setting_options() {
  let settings = {
    'settings': {
      'display_tab': jQuery('#display_people_group_tab').prop('checked')
    }
  };

  jQuery.ajax({
    type: "POST",
    data: JSON.stringify(settings),
    contentType: "application/json; charset=utf-8",
    dataType: "json",
    url: dtPeopleGroupsAPI.root + 'dt/v1/people-groups/update_setting_options',
    beforeSend: function (xhr) {
      xhr.setRequestHeader('X-WP-Nonce', dtPeopleGroupsAPI.nonce);
    },
  }).done(function (data) {
    console.log(data);

  }).fail(function (err) {
    console.log("error");
    console.log(err);
  })
}

function group_search() {
    let sval = jQuery('#group-search').val();
    let data = { "s": sval }
    let search_button = jQuery('#search_button')
    search_button.append(' <img style="height:1em;" src="' + dtPeopleGroupsAPI.images_uri + 'spinner.svg" />');

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
          div.empty();
          search_button.empty().text('Get List')

          div.append(`<dl><dt><strong>` + sval + `</strong></dt>`)

          jQuery.each(data, function (i, v) {
            div.append(`
                <dd>` + v[4] + ` ( ` + v[1] + ` | ` + v[3] + ` ) <button onclick="add_single_people_group('` + v[3] + `','` + v[1] + `','` + v[33] + `')" id="button-` + v[3] + `">add</button> <span id="message-` + v[3] + `"></span></dd>
                `)

            // Check last element for duplicate flag to determine if group has already been installed.
            if (v[v.length - 1]) {
              let button = jQuery('#button-' + v[3]);
              if (button) {
                jQuery(button).text('installed');
                jQuery(button).attr('disabled', 'disabled');
              }
            }
          })
          div.append(`</dl>`)

          // Add listener for select all button, ensuring to delete all stale click listeners.
          jQuery('#add_all_groups').show().off('click').on('click', function () {
            div.prepend('<span><strong>DO NOT NAVIGATE AWAY FROM THIS PAGE UNTIL INSTALL IS COMPLETE!</strong></span><br>')
            jQuery.each(jQuery('#results button'), function (i, v) {
              setTimeout(function () {
                console.log(v.id);
                jQuery('#' + v.id).click()
              }, 700 * i);
            })
          })
        })
        .fail(function (err) {
          console.log("error");
          console.log(err);
        })
}

function import_all_people_groups() {
  if (confirm("Import all country people groups?")) {
    let group_search_select = jQuery('#group-search');
    let search_button = jQuery('#search_button');
    let import_all_button = jQuery('#import_all_button');
    let add_all_groups = jQuery('#add_all_groups');
    let results_div = jQuery('#results');
    group_search_select.attr('disabled', 'disabled');
    search_button.attr('disabled', 'disabled');
    import_all_button.attr('disabled', 'disabled');
    import_all_button.empty().text('Import All Country People Groups').append(' <img style="height:1em;" src="' + dtPeopleGroupsAPI.images_uri + 'spinner.svg" />');
    add_all_groups.hide();
    results_div.empty();

    // First, fetch bulk people groups import batches to be processed.
    jQuery.ajax({
      type: "GET",
      data: JSON.stringify({}),
      contentType: "application/json; charset=utf-8",
      dataType: "json",
      url: dtPeopleGroupsAPI.root + 'dt/v1/people-groups/get_bulk_people_groups_import_batches',
      beforeSend: function (xhr) {
        xhr.setRequestHeader('X-WP-Nonce', dtPeopleGroupsAPI.nonce);
      }

    }).done(function (data) {
      console.log(data);

      // Ensure user is doubly sure they wish to proceed with import.
      if (data['total_batches'] && data['total_records'] && data['batches'] && confirm("Are you sure you wish to import " + data['total_records'] + " records, across " + data['total_batches'] + " batches?")) {

        // Iterate through each item and sequentially call the ajax function.
        let looper = jQuery.Deferred().resolve();
        jQuery.when.apply(jQuery, jQuery.map(data['batches'], function (batch, country) {
          looper = looper.then(function () {
            console.log(country);
            console.log(batch);

            // Trigger ajax call with batch data;
            return add_bulk_people_groups(country, batch);
          });
          return looper;

        })).then(function () {
          // Once all batches have been processed, re-enable buttons and dropdowns.
          import_all_button.prop('disabled', false);
          import_all_button.empty().text('Import All Country People Groups');
          group_search_select.prop('disabled', false);
          search_button.prop('disabled', false);
        });
      } else {
        import_all_button.prop('disabled', false);
        import_all_button.empty().text('Import All Country People Groups');
        group_search_select.prop('disabled', false);
        search_button.prop('disabled', false);
      }
    }).fail(function (err) {
      console.log("error");
      console.log(err);
    });
  }
}

function add_bulk_people_groups(country, people_groups) {
  let deferred = jQuery.Deferred();

  jQuery.ajax({
    type: "POST",
    data: JSON.stringify({'groups': people_groups}),
    contentType: "application/json; charset=utf-8",
    dataType: "json",
    url: dtPeopleGroupsAPI.root + 'dt/v1/people-groups/add_bulk_people_groups',
    beforeSend: function (xhr) {
      xhr.setRequestHeader('X-WP-Nonce', dtPeopleGroupsAPI.nonce);
    },
    success: function (data) {
      console.log(data);
      deferred.resolve(data);

      // Update table with batch import results.
      let html = `<tr><td>${country}: Processed ${data['total_groups_insert_success']} of ${data['total_groups_count']}</td></tr>`;
      jQuery('#import_people_group_table').find('tbody > tr').eq(0).after(html);

    },
    error: function (error) {
      deferred.reject(error);
    }
  });

  return deferred.promise();
}

function add_single_people_group( rop3, country, location_grid ) {
    let data = { "rop3": rop3, "country": country, "location_grid": location_grid }
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
