/* global jQuery:false, List:false, wpApiSettings:false */


jQuery(document).ready(function($) {
  if (! $("#my-contacts").length || ! $("#my-contacts .list").length) {
    return;
  }
  var myContacts = new List('my-contacts', {
    valueNames: [
      'post_title',
      'assigned_name',
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
      myContacts.clear();
      myContacts.add(data);
      setUpFilters(data);
    },
    error: function() {
      $(".js-list-contacts-loading").text(wpApiSettings.txt_error);
    },
    complete: function() {
      $("#my-contacts .js-search-tools")
        .removeClass("faded-out")
        .find("button.sort[data-sort]")
          .removeAttr("disabled")
        .end()
        .find("input.search")
          .removeAttr("disabled");
    },
  });

  function setUpFilters(data) {
    var names = wpApiSettings.contacts_custom_fields_settings.overall_status.default;
    var counts = {};
    for (var i = 0; i < data.length; i++) {
      var contact = data[i];
      if (! counts.hasOwnProperty("s" + contact.status_number)) {
        counts["s" + contact.status_number] = 0;
      }
      counts["s" + contact.status_number]++;
    }
    $(".js-contacts-filters").empty().append($("<table>"));
    Object.keys(counts).forEach(function(key) {
      var keyName = names[parseInt(key.substring(1))];
      $(".js-contacts-filters table").append(
        $("<tr>")
          .append($("<th>").append(document.createTextNode(keyName)))
          .append($("<td>").append(document.createTextNode(counts[key])))
      );
    });
  }

});
