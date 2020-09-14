jQuery(document).ready(function($) {
  let post_id = window.detailsSettings.post_id
  let post_type = window.detailsSettings.post_type
  let post = window.detailsSettings.post_fields

  /**
   * User-select
   */
  if ( !post.corresponds_to_user ) {
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
          <span>${_.escape( item.name )}</span>
        </span>`
      },
      dynamic: true,
      hint: true,
      emptyTemplate: _.escape(window.wpApiShare.translations.no_records_found),
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
})
