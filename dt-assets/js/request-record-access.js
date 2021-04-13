jQuery(document).ready(function ($) {

  let rest_api = window.API
  let postId = window.detailsSettings.post_id;
  let postType = window.detailsSettings.post_type;
  let currentUserId = window.detailsSettings.current_user_id;

  // Open the request record access modal
  $(document).on("click", '.open-request-record-access-button-modal', function () {
    $('#request-record-access-modal').foundation('open');
  })

  // Handle request submissions
  $(document).on("submit", '.request-record-access-form', function (e) {
    e.preventDefault();

    $('#request-record-access-modal-button').toggleClass('loading');

    // Post request
    rest_api.request_record_access(postType, postId, currentUserId).then(data => {
      console.log(data)

      $('#request-record-access-modal-button').toggleClass('loading');
      $('#request-record-access-modal').foundation('close');

      history.back();

    }).catch(err => {

      // Capture any request exceptions
      console.log("error")
      console.log(err)

      $('#request-record-access-error').append(err.responseText)

      $('#request-record-access-modal-button').toggleClass('loading');
      $('#request-record-access-modal').foundation('close');
    })
  })

})


