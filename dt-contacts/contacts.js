jQuery(document).ready(function ($) {
  let post_id = window.detailsSettings.post_id;
  let post_type = window.detailsSettings.post_type;
  let post = window.detailsSettings.post_fields;

  /**
   * User-select
   */
  if (!post.corresponds_to_user && $('.js-typeahead-user-select').length) {
    $.typeahead({
      input: '.js-typeahead-user-select',
      minLength: 0,
      accent: true,
      searchOnFocus: true,
      source: window.TYPEAHEADS.typeaheadUserSource(),
      templateValue: '{{name}}',
      template: function (query, item) {
        return `<span class="row">
          <span class="avatar"><img src="{{avatar}}"/> </span>
          <span>${window.SHAREDFUNCTIONS.escapeHTML(item.name)}</span>
        </span>`;
      },
      dynamic: true,
      hint: true,
      emptyTemplate: window.SHAREDFUNCTIONS.escapeHTML(
        window.wpApiShare.translations.no_records_found,
      ),
      callback: {
        onClick: function (node, a, item) {
          jQuery
            .ajax({
              type: 'GET',
              data: { user_id: item.ID },
              contentType: 'application/json; charset=utf-8',
              dataType: 'json',
              url: window.wpApiShare.root + 'dt/v1/users/contact-id',
              beforeSend: function (xhr) {
                xhr.setRequestHeader('X-WP-Nonce', window.wpApiShare.nonce);
              },
            })
            .then((user_contact_id) => {
              $('.confirm-merge-with-user').show();
              $('#confirm-merge-with-user-dupe-id').val(user_contact_id);
            });
        },
        onResult: function (node, query, result, resultCount) {
          let text = window.TYPEAHEADS.typeaheadHelpText(
            resultCount,
            query,
            result,
          );
          $('#user-select-result-container').html(text);
        },
        onHideLayout: function () {
          $('.user-select-result-container').html('');
        },
      },
    });
    let user_select_input = $(`.js-typeahead-user-select`);
    $('.search_user-select').on('click', function () {
      user_select_input.val('');
      user_select_input.trigger('input.typeahead');
      user_select_input.focus();
    });
  }

  $('#create-user-return').on('click', function (e) {
    e.preventDefault();
    $(this).toggleClass('loading');
    let $inputs = $('#create-user-form :input');
    let values = {};
    $inputs.each(function () {
      values[this.name] = $(this).val();
    });
    values['corresponds_to_contact'] = post_id;
    window.API.create_user(values)
      .then(() => {
        $(this).removeClass('loading');
        $(`#make-user-from-contact-modal`).foundation('close');
        location.reload();
      })
      .catch((err) => {
        $(this).removeClass('loading');
        $('#create-user-errors').html(
          window.lodash.get(
            err,
            'responseJSON.message',
            'Something went wrong',
          ),
        );
      });
    return false;
  });

  /**
   * Duplicates
   */
  window
    .makeRequestOnPosts('GET', `${post_type}/${post_id}/duplicates`)
    .then((response) => {
      console.log(response);
      if (response.ids && response.ids.length > 0) {
        document.querySelector('#see-duplicates').style.display = 'block';
      }
    });

  let merge_dupe_edit_modal = $('#merge-dupe-edit-modal');
  let possible_duplicates = [];
  let openedOnce = false;
  $(document).on('click , mouseenter', '.duplicate-detected', function () {
    if (!openedOnce) {
      let original_contact_html = `<div class="merge-modal-contact-row">
        <h5>
        <a href="${window.wpApiShare.site_url}/${post_type}/${window.SHAREDFUNCTIONS.escapeHTML(post_id)}" class="merge-modal-contact-name" target=_blank>
        ${window.SHAREDFUNCTIONS.escapeHTML(post.name)}
        <span class="merge-modal-contact-info"> #${post_id} (${window.lodash.get(post, 'overall_status.label') || ''}) </span>
        </a>
        </h5>`;
      window.lodash.forOwn(
        window.detailsSettings.post_settings.fields,
        (field_settings, field_key) => {
          if (
            field_settings.type === 'communication_channel' &&
            post[field_key]
          ) {
            post[field_key].forEach((contact_info) => {
              if (contact_info.value !== '') {
                original_contact_html += `<img src='${window.SHAREDFUNCTIONS.escapeHTML(field_settings.icon)}'><span style="font-size: .9375rem;margin-right: 15px;">&nbsp;${window.SHAREDFUNCTIONS.escapeHTML(contact_info.value)}</span>`;
              }
            });
          }
        },
      );
      original_contact_html += `</div>`;
      $('#original-contact').append(original_contact_html);

      window.API.get_duplicates_on_post('contacts', post_id).done(
        (dups_with_data) => {
          possible_duplicates = dups_with_data;
          $('#duplicates-spinner').removeClass('active');
          loadDuplicates();
        },
      );

      openedOnce = true;
    }
  });

  // merge_dupe_edit_modal.on('open.zf.reveal', function () {
  //   if (!openedOnce) {
  //     let original_contact_html = `<div class="merge-modal-contact-row">
  //       <h5>
  //       <a href="${window.wpApiShare.site_url}/${post_type}/${window.SHAREDFUNCTIONS.escapeHTML(post_id)}" class="merge-modal-contact-name" target=_blank>
  //       ${window.SHAREDFUNCTIONS.escapeHTML(post.name)}
  //       <span class="merge-modal-contact-info"> #${post_id} (${window.lodash.get(post, 'overall_status.label') || ''}) </span>
  //       </a>
  //       </h5>`;
  //     window.lodash.forOwn(
  //       window.detailsSettings.post_settings.fields,
  //       (field_settings, field_key) => {
  //         if (
  //           field_settings.type === 'communication_channel' &&
  //           post[field_key]
  //         ) {
  //           post[field_key].forEach((contact_info) => {
  //             if (contact_info.value !== '') {
  //               original_contact_html += `<img src='${window.SHAREDFUNCTIONS.escapeHTML(field_settings.icon)}'><span style="font-size: .9375rem;margin-right: 15px;">&nbsp;${window.SHAREDFUNCTIONS.escapeHTML(contact_info.value)}</span>`;
  //             }
  //           });
  //         }
  //       },
  //     );
  //     original_contact_html += `</div>`;
  //     $('#original-contact').append(original_contact_html);

  //     window.API.get_duplicates_on_post('contacts', post_id).done(
  //       (dups_with_data) => {
  //         possible_duplicates = dups_with_data;
  //         $('#duplicates-spinner').removeClass('active');
  //         loadDuplicates();
  //       },
  //     );

  //     openedOnce = true;
  //   }
  // });

  function loadDuplicates() {
    let dups_with_data = possible_duplicates;
    if (dups_with_data) {
      let $duplicates = $('#duplicates_list');
      $duplicates.html('');

      let already_dismissed = window.lodash
        .get(post, 'duplicate_data.override', [])
        .map((id) => parseInt(id));

      let html = ``;
      dups_with_data
        .sort((a, b) => (a.points > b.points ? -1 : 1))
        .forEach((dupe) => {
          if (!already_dismissed.includes(parseInt(dupe.ID))) {
            html += dup_row(dupe);
          }
        });
      if (html) {
        $duplicates.append(html);
      } else {
        $('#no_dups_message').show();
      }
      let dismissed_html = ``;
      dups_with_data
        .sort((a, b) => (a.points > b.points ? -1 : 1))
        .forEach((dupe) => {
          if (already_dismissed.includes(parseInt(dupe.ID))) {
            dismissed_html += dup_row(dupe, true);
          }
        });
      if (dismissed_html) {
        dismissed_html =
          `<h4 class="merge-modal-subheading">${window.SHAREDFUNCTIONS.escapeHTML(window.detailsSettings.translations.dismissed_duplicates)}</h4>` +
          dismissed_html;
        $duplicates.append(dismissed_html);
      }
    }
  }
  let dup_row = (dupe, dismissed_row = false) => {
    let html = ``;
    let dups_on_fields = window.lodash.uniq(
      dupe.fields.map((field) => {
        return window.lodash.get(
          window.detailsSettings.post_settings,
          `fields[${field.field}].name`,
        );
      }),
    );
    let matched_values = dupe.fields.map((f) => f.value);
    html += `<div class="merge-modal-contact-row">
      <h5>
      <a href="${window.wpApiShare.site_url}/${post_type}/${window.SHAREDFUNCTIONS.escapeHTML(dupe.ID)}" class="merge-modal-contact-name" target=_blank>
      ${window.SHAREDFUNCTIONS.escapeHTML(dupe.post.name)}
      <span class="merge-modal-contact-info"> #${dupe.ID} (${window.lodash.get(dupe.post, 'overall_status.label') || ''}) </span>
      </a>
    </h5>`;
    html += `${window.SHAREDFUNCTIONS.escapeHTML(window.detailsSettings.translations.duplicates_on).replace('%s', '<strong>' + window.SHAREDFUNCTIONS.escapeHTML(dups_on_fields.join(', ')) + '</strong>')}<br />`;

    window.lodash.forOwn(
      window.detailsSettings.post_settings.fields,
      (field_settings, field_key) => {
        if (
          field_settings.type === 'communication_channel' &&
          dupe.post[field_key]
        ) {
          dupe.post[field_key].forEach((contact_info) => {
            if (contact_info.value !== '') {
              html += `<img src='${window.SHAREDFUNCTIONS.escapeHTML(field_settings.icon)}'><span style="font-size: .9375rem;margin-right: 15px; ${matched_values.includes(contact_info.value) ? 'font-weight:bold;' : ''}">&nbsp;${window.SHAREDFUNCTIONS.escapeHTML(contact_info.value)}</span>`;
            }
          });
        }
      },
    );
    html += `<br>`;
    if (
      dupe.post.overall_status?.key === 'closed' &&
      dupe.post.reason_closed &&
      window.detailsSettings.post_settings.fields.reason_closed
    ) {
      html += `${window.SHAREDFUNCTIONS.escapeHTML(window.detailsSettings.post_settings.fields.reason_closed?.name)}: <strong>${window.SHAREDFUNCTIONS.escapeHTML(dupe.post.reason_closed.label)}</strong>`;
      html += `<br>`;
    }
    if (!dismissed_row) {
      html += `<button class='mergelinks dismiss-duplicate merge-modal-button' data-id='${window.SHAREDFUNCTIONS.escapeHTML(dupe.ID)}'><a>${window.SHAREDFUNCTIONS.escapeHTML(window.detailsSettings.translations.dismiss)}</a></button>`;
    }
    html += `
       <button type='submit' class="merge-post merge-modal-button" data-dup-id="${window.SHAREDFUNCTIONS.escapeHTML(dupe.ID)}">
          <a>${window.SHAREDFUNCTIONS.escapeHTML(window.detailsSettings.translations.merge)}</a>
      </button>
    `;

    html += `</div>`;
    return html;
  };

  $(document).on('click', '.merge-post', function () {
    let dup_id = $(this).data('dup-id');
    window.location = `${window.wpApiShare.site_url}/${post_type}/mergedetails?dupeid=${dup_id}&currentid=${post_id}`;
  });

  $(document).on('click', '.dismiss-duplicate', function () {
    let id = $(this).data('id');
    window
      .makeRequestOnPosts(
        'POST',
        `${post_type}/${post_id}/dismiss-duplicates`,
        { id: id },
      )
      .then((resp) => {
        post.duplicate_data = resp;
        loadDuplicates();
        adjust_duplicates_detected_notice_display(post.ID);
      });
  });
  $('#dismiss_all_duplicates').on('click', function () {
    window
      .makeRequestOnPosts(
        'POST',
        `${post_type}/${post.ID}/dismiss-duplicates`,
        { id: 'all' },
      )
      .then((resp) => {
        post.duplicate_data = resp;
        loadDuplicates();
        adjust_duplicates_detected_notice_display(post.ID);
      });
  });

  function adjust_duplicates_detected_notice_display(orig_post_id) {
    window
      .makeRequestOnPosts('GET', `contacts/${orig_post_id}/duplicates`)
      .then((response) => {
        if (response.ids && response.ids.length === 0) {
          $('#duplicates-detected-notice').hide();
        }
      });
  }

  //open duplicates modal if 'open-duplicates' param is is url
  let open_duplicates = window.SHAREDFUNCTIONS.get_url_param('open-duplicates');
  if (open_duplicates === '1') {
    merge_dupe_edit_modal.foundation('open');
  }

  /**
   * Transfer Contact
   */
  $('#transfer_confirm_button').on('click', function () {
    $(this).addClass('loading');
    let siteId = $('#transfer_contact').val();
    if (!siteId) {
      return;
    }
    window.API.transfer_contact(post_id, siteId)
      .then((data) => {
        if (data) {
          location.reload();
        }
      })
      .catch((err) => {
        console.error(err);
        // try a second time.
        window.API.transfer_contact(post_id, siteId)
          .then((data) => {
            if (data) {
              location.reload();
            }
          })
          .catch((err) => {
            $(this).removeClass('loading');
            jQuery('#transfer_spinner')
              .empty()
              .append(err.responseJSON.message)
              .append(
                '&nbsp;' + window.detailsSettings.translations.transfer_error,
              );
            console.error(err);
          });
      });
  });

  /**
   * Transfer Contact Summary Update
   */
  $('#transfer_contact_summary_update_button').on('click', function () {
    $(this).addClass('loading');
    let comments = $('#transfer_contact_summary_update_comment');

    let update = comments.val().trim();
    if (!update) {
      $(this).removeClass('loading');
      return;
    }

    window.API.transfer_contact_summary_update(post_id, update)
      .then((data) => {
        $(this).removeClass('loading');
        transfer_contact_summary_update_results(data);
      })
      .catch((err) => {
        console.error(err);
        // try a second time.
        window.API.transfer_contact_summary_update(post_id, update)
          .then((data) => {
            $(this).removeClass('loading');
            transfer_contact_summary_update_results(data);
          })
          .catch((err) => {
            console.error(err);
            $(this).removeClass('loading');
            $('#transfer_contact_summary_update_message')
              .fadeOut('fast')
              .html(window.detailsSettings.translations.transfer_update_error)
              .fadeIn('fast');
          });
      });

    // Clear comments textarea
    comments.val('');
  });

  function transfer_contact_summary_update_results(data) {
    let message = $('#transfer_contact_summary_update_message');

    if (data['success']) {
      message
        .fadeOut('fast')
        .html(window.detailsSettings.translations.transfer_update_success)
        .fadeIn('fast');
    } else {
      message
        .fadeOut('fast')
        .html(window.detailsSettings.translations.transfer_update_error)
        .fadeIn('fast');
    }
  }
});
