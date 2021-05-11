jQuery(document).ready(function($) {
  let post_id        = window.detailsSettings.post_id
  let post_type      = window.detailsSettings.post_type
  let post           = window.detailsSettings.post_fields

  /**
   * User-select
   */
  if ( !post.corresponds_to_user && $('.js-typeahead-user-select').length) {
    $.typeahead({
      input: '.js-typeahead-user-select',
      minLength: 0,
      accent: true,
      searchOnFocus: true,
      source: TYPEAHEADS.typeaheadUserSource(),
      templateValue: "{{name}}",
      template: function (query, item) {
        return `<span class="row">
          <span class="avatar"><img src="{{avatar}}"/> </span>
          <span>${window.lodash.escape( item.name )}</span>
        </span>`
      },
      dynamic: true,
      hint: true,
      emptyTemplate: window.lodash.escape(window.wpApiShare.translations.no_records_found),
      callback: {
        onClick: function (node, a, item) {
          jQuery.ajax({
            type: "GET",
            data: {"user_id": item.ID},
            contentType: "application/json; charset=utf-8",
            dataType: "json",
            url: window.wpApiShare.root + 'dt/v1/users/contact-id',
            beforeSend: function (xhr) {
              xhr.setRequestHeader('X-WP-Nonce', window.wpApiShare.nonce);
            }
          }).then(user_contact_id => {
            $('.confirm-merge-with-user').show()
            $('#confirm-merge-with-user-dupe-id').val(user_contact_id)
          })
        },
        onResult: function (node, query, result, resultCount) {
          let text = TYPEAHEADS.typeaheadHelpText(resultCount, query, result)
          $('#user-select-result-container').html(text);
        },
        onHideLayout: function () {
          $('.user-select-result-container').html("");
        },
      },
    });
    let user_select_input = $(`.js-typeahead-user-select`)
    $('.search_user-select').on('click', function () {
      user_select_input.val("")
      user_select_input.trigger('input.typeahead')
      user_select_input.focus()
    })
  }

  $("#create-user-return").on("click", function (e) {
    e.preventDefault();
    $(this).toggleClass("loading")
    let $inputs = $('#create-user-form :input');
    let values = {};
    $inputs.each(function() {
        values[this.name] = $(this).val();
    });
    values["corresponds_to_contact"] = post_id;
    window.API.create_user(values).then(()=>{
      $(this).removeClass("loading")
      $(`#make-user-from-contact-modal`).foundation('close')
      location.reload();
    }).catch(err=>{
      $(this).removeClass("loading")
      $('#create-user-errors').html(window.lodash.get(err, "responseJSON.message", "Something went wrong"))
    })
    return false;
  })


  /**
   * Duplicates
   */
  window.makeRequestOnPosts( "GET", `${post_type}/${post_id}/duplicates` ).then(response => {
    if ( response.ids && response.ids.length > 0 ){
      $('.details-title-section').html(`
        <button class="button hollow center-items" id="duplicates-detected-notice" style="margin-bottom: 0; padding: .5em .5em; ">
          <img style="height:20px" src="${window.lodash.escape( window.wpApiShare.template_dir )}/dt-assets/images/broken.svg"/>
          <strong>${window.lodash.escape(window.detailsSettings.translations.duplicates_detected)}</strong>
        </button>
      `)
    }
  })
  let merge_dupe_edit_modal =  $('#merge-dupe-edit-modal')
  $(document).on( 'click', '#duplicates-detected-notice', function(){
    merge_dupe_edit_modal.foundation('open');
  })

  let possible_duplicates = [];
  let openedOnce = false
  merge_dupe_edit_modal.on("open.zf.reveal", function () {
    if ( !openedOnce ){

      let original_contact_html = `<div style='background-color: #f2f2f2; padding:2%; overflow: hidden;'>
        <h5 style='font-weight: bold; color: #3f729b;'>
        <a href="${window.wpApiShare.site_url}/${post_type}/${window.lodash.escape(post_id)}" target=_blank>
        ${ window.lodash.escape(post.name) }
        <span style="font-weight: normal; font-size:16px"> #${post_id} (${window.lodash.get(post, "overall_status.label") ||""}) </span>
        </a>
        </h5>`
      window.lodash.forOwn(window.detailsSettings.post_settings.fields, (field_settings, field_key)=>{
        if ( field_settings.type === "communication_channel" && post[field_key] ){
          post[field_key].forEach( contact_info=>{
            if ( contact_info.value !== '' ){
              original_contact_html +=`<img src='${window.lodash.escape(field_settings.icon)}'><span style="margin-right: 15px;">&nbsp;${window.lodash.escape(contact_info.value)}</span>`
            }
          })
        }
      })
      original_contact_html += `</div>`
      $('#original-contact').append(original_contact_html);

      window.API.get_duplicates_on_post("contacts", post_id).done(dups_with_data=> {
        possible_duplicates = dups_with_data
        $("#duplicates-spinner").removeClass("active")
        loadDuplicates();
      })

      openedOnce = true;
    }
  })
  function loadDuplicates() {
    let dups_with_data = possible_duplicates
    if (dups_with_data) {
      let $duplicates = $('#duplicates_list');
      $duplicates.html("");

      let already_dismissed = window.lodash.get(post, 'duplicate_data.override', []).map(id=>parseInt(id))

      let html = ``
      dups_with_data.sort((a, b) => a.points > b.points ? -1:1).forEach((dupe) => {
        if (!already_dismissed.includes(parseInt(dupe.ID))) {
          html += dup_row(dupe)
        }
      })
      if ( html ){
        $duplicates.append(html);
      } else {
        $('#no_dups_message').show()
      }
      let dismissed_html = ``;
      dups_with_data.sort((a, b) => a.points > b.points ? -1:1).forEach((dupe) => {
        if (already_dismissed.includes(parseInt(dupe.ID))) {
          dismissed_html += dup_row(dupe, true)
        }
      })
      if (dismissed_html) {
        dismissed_html = `<h4 style='text-align: center; font-size: 1.25rem; font-weight: bold; padding:20px 0 0; margin-bottom: 0;'>${window.lodash.escape(window.detailsSettings.translations.dismissed_duplicates)}</h4>`
          + dismissed_html
        $duplicates.append(dismissed_html);
      }
    }
  }
  let dup_row = (dupe, dismissed_row = false)=>{
    let html = ``;
    let dups_on_fields = window.lodash.uniq(dupe.fields.map(field=>{
      return window.lodash.get(window.detailsSettings.post_settings, `fields[${field.field}].name`)
    }))
    let matched_values = dupe.fields.map(f=>f.value)
    html += `<div style='background-color: #f2f2f2; padding:2%; overflow: hidden;'>
      <h5 style='font-weight: bold; color: #3f729b;'>
      <a href="${window.wpApiShare.site_url}/${post_type}/${window.lodash.escape(dupe.ID)}" target=_blank>
      ${ window.lodash.escape(dupe.post.name) }
      <span style="font-weight: normal; font-size:16px"> #${dupe.ID} (${window.lodash.get(dupe.post, "overall_status.label") ||""}) </span>
      </a>
    </h5>`
    html += `${window.lodash.escape(window.detailsSettings.translations.duplicates_on).replace('%s', '<strong>' + window.lodash.escape(dups_on_fields.join( ', ')) + '</strong>' )}<br />`

    window.lodash.forOwn(window.detailsSettings.post_settings.fields, (field_settings, field_key)=>{
      if ( field_settings.type === "communication_channel" && dupe.post[field_key] ){
        dupe.post[field_key].forEach( contact_info=>{
          if ( contact_info.value !== '' ){
            html +=`<img src='${window.lodash.escape(field_settings.icon)}'><span style="margin-right: 15px; ${matched_values.includes(contact_info.value) ? 'font-weight:bold;' : ''}">&nbsp;${window.lodash.escape(contact_info.value)}</span>`
          }
        })
      }
    })
    html += `<br>`
    if (dupe.post.overall_status.key === 'closed' && dupe.post.reason_closed) {
      html += `${window.lodash.escape(window.detailsSettings.post_settings.fields.reason_closed.name)}: <strong>${window.lodash.escape(dupe.post.reason_closed.label)}</strong>`
      html += `<br>`
    }
    if ( !dismissed_row ){
      html += `<button class='mergelinks dismiss-duplicate' data-id='${window.lodash.escape(dupe.ID)}' style='float: right; padding-left: 10%;'><a>${window.lodash.escape(window.detailsSettings.translations.dismiss)}</a></button>`
    }
    html += `
       <button type='submit' class="merge-post" data-dup-id="${window.lodash.escape(dupe.ID)}" style='float:right; padding-left: 10%;'>
          <a>${window.lodash.escape(window.detailsSettings.translations.merge)}</a>
      </button>
    `

    html += `</div>`
    return html;
  }

  $(document).on( "click", ".merge-post", function () {
    let dup_id = $(this).data('dup-id')
    window.location = `${window.wpApiShare.site_url}/${post_type}/mergedetails?dupeid=${dup_id}&currentid=${post_id}`
  })

  $(document).on( "click", ".dismiss-duplicate", function () {
    let id = $(this).data('id');
    makeRequestOnPosts('POST', `${post_type}/${post_id}/dismiss-duplicates`, {'id':id}).then(resp=>{
      post.duplicate_data = resp;
      loadDuplicates()
    })
  })
  $('#dismiss_all_duplicates').on( 'click', function () {
    makeRequestOnPosts('POST', `${post_type}/${post.ID}/dismiss-duplicates`, {'id':'all'}).then(resp=> {
      post.duplicate_data = resp;
      loadDuplicates()
    })
  })
  //open duplicates modal if 'open-duplicates' param is is url
  let open_duplicates = window.SHAREDFUNCTIONS.get_url_param("open-duplicates")
  if ( open_duplicates === '1' ){
    merge_dupe_edit_modal.foundation('open');
  }


  /**
   * Merging
   */
  $('#open_merge_with_contact').on("click", function () {
    if (!window.Typeahead['.js-typeahead-merge_with']) {
      $.typeahead({
        input: '.js-typeahead-merge_with',
        minLength: 0,
        accent: true,
        searchOnFocus: true,
        source: TYPEAHEADS.typeaheadPostsSource( "contacts", { 'include-users': false }),
        templateValue: "{{name}}",
        template: window.TYPEAHEADS.contactListRowTemplate,
        dynamic: true,
        hint: true,
        emptyTemplate: window.lodash.escape(window.wpApiShare.translations.no_records_found),
        callback: {
          onClick: function (node, a, item) {
            $('.confirm-merge-with-contact').show()
            $('#confirm-merge-with-contact-id').val(item.ID)
            $('#name-of-contact-to-merge').html(item.name)
          },
          onResult: function (node, query, result, resultCount) {
            let text = TYPEAHEADS.typeaheadHelpText(resultCount, query, result)
            $('#merge_with-result-container').html(text);
          },
          onHideLayout: function () {
            $('.merge_with-result-container').html("");
          },
        },
      });
    }
    let user_select_input = $(`.js-typeahead-merge_with`)
    $('.search_merge_with').on('click', function () {
      user_select_input.val("")
      user_select_input.trigger('input.typeahead')
      user_select_input.focus()
    })
    $('#merge-with-contact-modal').foundation('open');
  })


  /**
   * Transfer Contact
   */
  $('#transfer_confirm_button').on('click',function() {
    $(this).addClass('loading')
    let siteId = $('#transfer_contact').val()
    if ( ! siteId ) {
      return;
    }
    API.transfer_contact( post_id, siteId )
    .then(data=>{
      if ( data ) {
        location.reload();
      }
    }).catch(err=>{
      console.error(err)
      // try a second time.
      API.transfer_contact( post_id, siteId )
      .then(data=>{
        if ( data ) {
          location.reload();
        }
      }).catch(err=> {
        $(this).removeClass('loading')
        jQuery('#transfer_spinner').empty().append(err.responseJSON.message).append('&nbsp;' + window.detailsSettings.translations.transfer_error)
        console.error(err)
      })
    })
  });

})
