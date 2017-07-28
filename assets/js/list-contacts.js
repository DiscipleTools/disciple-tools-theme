/* global jQuery:false, List:false, wpApiSettings:false */


jQuery(document).ready(function($) {
  if (! $("#my-contacts").length || ! $("#my-contacts .list").length) {
    return;
  }
  var myContacts = new List('my-contacts', {
    valueNames: [
      'post_title',
      'team',
      { name: 'permalink', attr: 'href' },
    ],
    page: 30,
    pagination: true,
  });

  $.ajax({
    url: wpApiSettings.root + "dt-hooks/v1/user/1/contacts",
    beforeSend: function(xhr) {
      xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
    },
    success: function(data) {
      $(".js-list-contacts-loading").remove();
      myContacts.clear();
      myContacts.add(data);
    },
    error: function() {
      $(".js-list-contacts-loading").text(wpApiSettings.txt_error);
    }
  });

});
