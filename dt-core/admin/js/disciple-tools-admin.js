//if the facebook error notice is dismissed. Call the facebook integration function

"use strict";
jQuery(document).on('click', '.dt-facebook-notice .notice-dismiss', function () {

  jQuery.ajax({
    url: ajaxurl,
    data: {
      action: 'dt-facebook-notice-dismiss'
    }
  });
});
