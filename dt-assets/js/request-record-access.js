jQuery(document).ready(function ($) {
  let rest_api = window.API;
  let postId = window.detailsSettings.post_id;
  let postType = window.detailsSettings.post_type;
  let currentUserId = window.detailsSettings.current_user_id;

  // Open the request record access modal
  $(document).on(
    'click',
    '.open-request-record-access-button-modal',
    function () {
      window.DTFoundation.plugin(() => {
        window.DTFoundation.callMethod('#request-record-access-modal', 'open');
      });
    },
  );

  // Handle request submissions
  $(document).on('submit', '.request-record-access-form', function (e) {
    e.preventDefault();

    $('#request-record-access-modal-button').toggleClass('loading');

    // Post request
    rest_api
      .request_record_access(postType, postId, currentUserId)
      .then((data) => {
        $('#request-record-access-modal-button').toggleClass('loading');
        window.DTFoundation.plugin(() => {
          window.DTFoundation.callMethod(
            '#request-record-access-modal',
            'close',
          );
        });

        window.location = window.wpApiShare.site_url + '/' + postType;
      })
      .catch((err) => {
        // Capture any request exceptions
        console.log('error');
        console.log(err);

        $('#request-record-access-error').append(err.responseText);

        $('#request-record-access-modal-button').toggleClass('loading');
        window.DTFoundation.plugin(() => {
          window.DTFoundation.callMethod(
            '#request-record-access-modal',
            'close',
          );
        });
      });
  });
});
