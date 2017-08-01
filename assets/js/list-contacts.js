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
    var counts = {
      status_number: {},
      locations: {},
    };
    for (var i = 0; i < data.length; i++) {
      var contact = data[i];
      var status = names[contact.status_number];
      if (! counts.status_number.hasOwnProperty(status)) {
        counts.status_number[status] = 0;
      }
      counts.status_number[status]++;
      for (var j = 0; j < contact.locations.length; j++) {
        var location = contact.locations[j];
        if (! counts.locations.hasOwnProperty(location)) {
          counts.locations[location] = 0;
        }
        counts.locations[location]++;
      }
    }

    $(".js-contacts-filter :not(summary)").remove();
    $(".js-contacts-filter[data-filter='status']")
      .append(createFilterTable(counts.status_number));
    $(".js-contacts-filter[data-filter='locations']")
      .append(createFilterTable(counts.locations));

  }

  function createFilterTable(counts) {
    var $table = $("<table>");
    Object.keys(counts).forEach(function(key) {
      $table.append(
        $("<tr>")
          .append($("<th>").append(document.createTextNode(key)))
          .append($("<td>").append(document.createTextNode(counts[key])))
      );
    });
    if ($.isEmptyObject(counts)) {
      $table.append(
        $("<tr>")
          .append($("<td>").append(document.createTextNode(wpApiSettings.txt_no_records)))
      );
    }
    return $table;
  }

});
