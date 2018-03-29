(function($, wpApiSettings, Foundation) {
  "use strict";
  let items = []; // contacts or groups
  let filterFunctions = [];
  let dataTable;
  const current_username = wpApiSettings.current_user_login;

  const viewFilterFunctions = {
    my_contacts(contact) {
      return _.get(contact, 'assigned_to.user_login') === current_username;
    },
    my_priorities(contact) {
      return (
        _.get(contact, 'assigned_to.user_login') === current_username
        && (contact.requires_update
          || contact.seeker_path === "scheduled"
          || (contact.overall_status === "active" && contact.seeker_path === "none")
        )
      );
    },
    update_needed(contact) {
      return (
        _.get(contact, 'assigned_to.user_login') === current_username
        && contact.requires_update
      );
    },
    meeting_scheduled(contact) {
      return (
        _.get(contact, 'assigned_to.user_login') === current_username
        && contact.seeker_path === "scheduled"
      );
    },
    contact_unattempted(contact) {
      return (
        _.get(contact, 'assigned_to.user_login') === current_username
        && (contact.overall_status === "active" && contact.seeker_path === "none")
      );
    },
    contacts_shared_with_me(contact) {
      return (
        contact.shared_with_user
        && _.get(contact, 'assigned_to.user_login') !== current_username
      );
    },
    all_contacts(contact) {
      return true;
    },
    assignment_needed(contact){
      return (
        _.get(contact, 'overall_status') === "unassigned" &&
        _.get(contact, 'assigned_to.user_login') === current_username
      )
    }
  };

  let gotData = function () {
    $(function() {
      displayRows();
      setUpFilterPane();
      updateFilterFunctions();
      dataTable.draw();
      $(".js-sort-by").on("click", function() {
        sortBy(parseInt($(this).data("column-index")), $(this).data("order"));
      });
    });
  }

  if (typeof(Storage) !== "undefined") {
    let data = localStorage.getItem(wpApiSettings.current_post_type);
    if (data){
      console.log(data.length);
      items = JSON.parse(data)
    }
  }
  gotData()




  function getItems() {
    let most_recent = _.get(_.maxBy(items || [], "last_modified") , 'last_modified') || 0
    $.ajax({
      url: wpApiSettings.root + "dt/v1/" + wpApiSettings.current_post_type + '?most_recent=' + most_recent,
      beforeSend: function(xhr) {
        xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
      },
      success: function(data) {
        items = _.unionBy(data[wpApiSettings.current_post_type], items || [], "ID");
        if (typeof(Storage) !== "undefined") {
          localStorage.setItem(wpApiSettings.current_post_type, JSON.stringify(items));
        }

        let percent = items.length / ( parseInt(data["total"]) + items.length - data[wpApiSettings.current_post_type].length )
        $(".loading-list-progress .progress-meter").css("width", percent * 100 + '%')
        $(".loading-list-progress .progress-meter-text").html(percent.toFixed(2) * 100 + '%')
        if ( data[wpApiSettings.current_post_type].length !== parseInt(data["total"]) && parseInt(data["total"]) !== 0 ){
          $(".loading-list-progress").show()
          getItems()
        } else {
          $(".loading-list-progress").hide()
          if (data[wpApiSettings.current_post_type].length){
            dataTable.clear()
            dataTable.rows.add(getFormattedRows())
            dataTable.draw()
          }
        }
      },
      error: function(jqXHR, textStatus, errorThrown) {
        $(function() {
          $(".js-list-loading > td").html(
            "<div>" + wpApiSettings.txt_error + "</div>" +
            "<div>" + jqXHR.responseText + "</div>"
          );
          console.error(jqXHR.responseText);
        });
      },
    });
  }
  getItems()

  $(function() {
    $(window).resize(function() {
      if (Foundation.MediaQuery.is('small only')) {
        if ($(".js-filters-modal .js-filters-modal-content").length === 0) {
          $(".js-filters-modal").append($(".js-filters-modal-content").detach());
        }
      } else {
        if ($(".js-pane-filters .js-filters-modal-contact").length === 0) {
          $(".js-pane-filters").append($(".js-filters-modal-content").detach());
        }
      }
    }).trigger("resize");
  });

  function sortBy(columnIndex, order) {
    console.assert(order === "asc" || order === "desc", "Unexpected value for order argument");
    dataTable.order([[columnIndex, order]]);
    dataTable.draw();
  }


  function getFormattedRows() {
    let rows = []
    _.forEach(items, function(item, index) {
      if (wpApiSettings.current_post_type === "contacts") {
        rows.push(contactRowArray(item, index))
      } else if (wpApiSettings.current_post_type === "groups") {
        rows.push(groupRowArray(item, index))
      }
    });
    return rows
  }

  function displayRows() {
    const $table = $(".js-list");
    if (! $table.length) {
      return;
    }
    $table.find("> tbody").empty();

    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
      const item = items[dataIndex];
      return _.every(filterFunctions, function(filterFunction) { return filterFunction(item); });
    });

    const dataTableOptions = {
      responsive: true,
      iDisplayLength: 100,
      bLengthChange: false,
      data: getFormattedRows(),
      language: {
        search: "",
        searchPlaceholder: wpApiSettings.txt_search,
        paginate: {
          "next": wpApiSettings.txt_next,
          "previous": wpApiSettings.txt_previous
        },
        info: wpApiSettings.txt_info,
        infoEmpty: wpApiSettings.txt_infoEmpty,
        infoFiltered: wpApiSettings.txt_infoFiltered,
        zeroRecords: wpApiSettings.txt_zeroRecords + ' ' + `<a href="#" class="clear-filters">${wpApiSettings.txt_clearFilters}</a>`
      },
      sDom: '<"  datatable-first-line"fir<"js-list-toolbar">>tlp<"clearfix">',
        /* <"datatable-firstline": div which contains:
         *     f: filtering input
         *     i: information
         *     r: processing
         *     <"js-list-toolbar"> div with class js-list-toolbar
         * >
         * t: table
         * l: length changing
         * p: pagination
         * <"clearfix"> div with class clearfix
         */
      initComplete: function() {
        $(".js-list-toolbar")
          .append($(".js-sort-dropdown").removeAttr("hidden").detach())
          .css("float", "right")
          .foundation();
      },
    };
    if (wpApiSettings.current_post_type == "contacts") {
      _.assign(dataTableOptions, {
        columnDefs: [
          { targets: [0], width: "30%" },
          { targets: [1], width: "20%", },
          { targets: [3], width: "20%", },
          {
            // Hide the last modified column, it's only used for sorting
            targets: [6],
            visible: false,
            searchable: false,
          },
        ],
        order: [[6, 'desc']],
        autoWidth: false,
      });
    } else if (wpApiSettings.current_post_type === "groups") {
      _.assign(dataTableOptions, {
        columnDefs: [
          { targets: [0], width: "30%" },
          { targets: [1], width: "15%" },
          { targets: [2], width: "15%" },
          { targets: [3], width: "5%" },
          {
            // Hide the last modified column, it's only used for sorting
            targets: [6],
            visible: false,
            searchable: false,
          },
        ],
        order: [[6, 'desc']],
        autoWidth: false,
      });
    }
    dataTable = $table.DataTable(dataTableOptions);
  }

  function contactRowArray(contact, index) {
    const ccfs = wpApiSettings.contacts_custom_fields_settings;
    const belief_milestone_key = _.find(
      ['baptizing', 'baptized', 'belief'],
      function(key) { return contact["milestone_" + key]; }
    );
    const sharing_milestone_key = _.find(
      ['planting', 'in_group', 'sharing', 'can_share'],
      function(key) { return contact["milestone_" + key]; }
    );

    let status = ccfs.overall_status.default[contact.overall_status] || contact.overall_status
    if (contact.overall_status === "active" && ccfs.seeker_path.default[contact.seeker_path]) {
      status = ccfs.seeker_path.default[contact.seeker_path] || "";
    }
    const group_links = _.map(contact.groups, function(group) {
        return '<a href="' + _.escape(group.permalink) + '">' + _.escape(group.post_title) + "</a>";
      }).join(", ");
    return [
      `<a href="${_.escape(contact.permalink)}">${_.escape(contact.post_title)}</a>
      <br>
      ${_.escape(contact.phone_numbers.join(", "))}`,
      `<span class="status status--${_.escape(contact.overall_status)}">${_.escape(status)}</span>`,
      `<span class="milestone milestone--${_.escape(sharing_milestone_key)}">${_.escape((ccfs["milestone_" + sharing_milestone_key] || {}).name || "")}</span>
      <br>
      <span class="milestone milestone--${_.escape(belief_milestone_key)}">${_.escape((ccfs["milestone_" + sharing_milestone_key] || {}).name || "")}</span>`,
      _.escape(contact.assigned_to? contact.assigned_to.name :""),
      _.escape(contact.locations.join("")),
      group_links,
      _.escape(contact.last_modified)
    ]
  }

  function groupRowArray(group) {
    const leader_links = _.map(group.leaders, function(leader) {
      return '<a href="' + _.escape(leader.permalink) + '">' + _.escape(leader.post_title) + "</a>";
    }).join(", ");
    const gcfs = wpApiSettings.groups_custom_fields_settings;
    const status = gcfs.group_status.default[group.group_status || "active"];
    const type = gcfs.group_type.default[group.group_type || "active"];
    return [
      `<a href="${group.permalink}">${_.escape(group.post_title)}</a>`,
      `<span class="group-status group-status--${_.escape(group.group_status)}">${_.escape(status)}</span>`,
      `<span class="group-type group-type--${_.escape(group.group_type)}">${_.escape(type)}</span>`,
      _.escape(group.member_count),
      _.escape(leader_links),
      _.escape(group.locations.join("")),
      _.escape(group.last_modified)
    ]
  }



  function countFilteredItems() {
    const counts = {};
    let currentView = $(".js-list-view:checked").val()
    if (wpApiSettings.current_post_type === "contacts") {
      const contacts = items.filter(viewFilterFunctions[currentView])
      _.assign(counts, {
        assigned_login: _.countBy(_(contacts).map('assigned_to.user_login').filter().value()),
        overall_status: _.countBy(_.map(contacts, 'overall_status')),
        locations: _.countBy(_.flatten(_.map(contacts, 'locations'))),
        seeker_path: _.countBy(contacts, 'seeker_path'),
        requires_update: _.countBy(contacts, 'requires_update'),
      });
    } else if (wpApiSettings.current_post_type === 'groups') {
      const groups = items;
      _.assign(counts, {
        group_status: _.countBy(_.map(groups, 'group_status')),
        group_type: _.countBy(_.map(groups, 'group_type')),
        locations: _.countBy(_.flatten(_.map(groups, 'locations'))),
      });
    }

    $(".js-list-view-count").each(function() {
      const $el = $(this);
      const filterFunction = viewFilterFunctions[$el.data('value')];
      $el.text(items.filter(filterFunction).length);
    });

    $(".js-list-filter :not(.js-list-filter-title)").remove();
    Object.keys(counts).forEach(function(filterType) {
      $(".js-list-filter[data-filter='" + filterType + "']")
        .append(createFilterCheckboxes(filterType, counts[filterType]));
    });
  }

  function setUpFilterPane() {
    if (! $(".js-list").length) {
      return;
    }
    countFilteredItems()
    $(".js-list-filter-title").on("click", function() {
      const $title = $(this);
      $title.parents(".js-list-filter").toggleClass("filter--closed");
    }).on("keydown", function(event) {
      if (event.keyCode === 13) {
        $(this).trigger("click");
      }
    });
  }
  $(".js-list-view").on("change", function() {
    reset()
  });

  $(document).on('click', '.clear-filters', function () {
    $("input[value='all_contacts']").prop("checked", true);
    reset()
  })

  function reset() {
    if (!dataTable){
      displayRows();
    }
    countFilteredItems()
    updateFilterFunctions();
    // setUpFilterPane();
    dataTable.draw();
  }

  function createFilterCheckboxes(filterType, counts) {
    const $div = $("<div>");
    const ccfs = wpApiSettings.contacts_custom_fields_settings;
    const gcfs = wpApiSettings.groups_custom_fields_settings;
    const is_dispatcher = _.includes(wpApiSettings.current_user_roles, "dispatcher");
    Object.keys(counts).sort().forEach(function(key) {
      let humanText;
      if (wpApiSettings.current_post_type === 'contacts' && (filterType === 'seeker_path' || filterType === 'overall_status')) {
        humanText = ccfs[filterType].default[key];
      } else if (wpApiSettings.current_post_type === 'contacts' && filterType === 'requires_update') {
        humanText = key === "true" ? wpApiSettings.txt_yes : wpApiSettings.txt_no;
      } else if (wpApiSettings.current_post_type === 'groups' && (filterType === 'group_status' || filterType === 'group_type')) {
        humanText = gcfs[filterType].default[key];
      } else {
        humanText = key;
      }
      const checkbox = $("<input>")
        .attr("type", "checkbox")
        .val(humanText)
        .on("change", function() {
          updateFilterFunctions();
          dataTable.draw();
        });
      $div.append(
        $("<div>").append(
          $("<label>")
            .css("cursor", "pointer")
            .addClass("js-filter-checkbox-label")
            .data("filter-type", filterType)
            .data("filter-value", key)
            .append(checkbox)
            .append(document.createTextNode(humanText))
            .append($("<span>")
              .css("float", "right")
              .append(document.createTextNode(counts[key]))
            )
        )
      );
    });
    if (is_dispatcher) {
      $(".js-list-filter[data-filter='overall_status']").removeClass("filter--closed");
    }
    if ($.isEmptyObject(counts)) {
      $div.append(
          document.createTextNode(wpApiSettings.txt_no_filters)
      );
    }
    return $div;
  }

  function updateFilterFunctions() {
    filterFunctions = [];

    let filterTypes;
    if (wpApiSettings.current_post_type === "contacts") {
      filterTypes = ["overall_status", "locations", "assigned_login", "seeker_path", "requires_update"];
    } else if (wpApiSettings.current_post_type === "groups") {
      filterTypes = ["group_status", "group_type", "locations"];
    }

    if ($(".js-list-view").length > 0) {
      filterFunctions.push(viewFilterFunctions[$(".js-list-view:checked").val()]);
    }

    let filteredTags = []
    let filterTags = $("#current-filters")
    filterTags.empty()
    $(".js-filter-checkbox-label input:checked").each(function(){
      filteredTags.push($(this).val())
      filterTags.append(`<span class="current-filter">${$(this).val()}</span>`)
    })


    filterTypes.forEach(function(filterType) {
      const $checkedLabels = assertAtLeastOne($(".js-filter-checkbox-label"))
        .filter(function() { return $(this).data("filter-type") === filterType; })
        .filter(function() { return $(this).find("input[type=checkbox]")[0].checked; });

      if ($checkedLabels.length <= 0) {
        return;
      }
      if (wpApiSettings.current_post_type === "contacts") {

        if (filterType === "overall_status") {
          filterFunctions.push(function overall_status(contact) {
            return _.some($checkedLabels, function(label) {
              return $(label).data("filter-value") === contact.overall_status;
            });
          });
        } else if (filterType === "locations") {
          filterFunctions.push(function locations(contact) {
            return _.some($checkedLabels, function(label) {
              return _.includes(contact.locations, $(label).data("filter-value"));
            });
          });
        } else if (filterType === "assigned_login") {
          filterFunctions.push(function assigned_login(contact) {
            return _.some($checkedLabels, function(label) {
              return $(label).data("filter-value") === _.get(contact, "assigned_to.user_login");
            });
          });
        } else if (filterType === "seeker_path") {
          filterFunctions.push(function seeker_path(contact) {
            return _.some($checkedLabels, function(label) {
              return $(label).data("filter-value") === contact.seeker_path;
            });
          });
        } else if (filterType === "requires_update") {
          filterFunctions.push(function requires_update(contact) {
            return _.some($checkedLabels, function(label) {
              const value = $(label).data("filter-value") === "true";
              return value === contact.requires_update;
            });
          });
        }

      } else if (wpApiSettings.current_post_type === "groups") {

        if (filterType === "group_status") {
          filterFunctions.push(function group_status(group) {
            return _.some($checkedLabels, function group_status(label) {
              return $(label).data("filter-value") === group.group_status;
            });
          });
        } else if (filterType === "group_type") {
          filterFunctions.push(function group_type(group) {
            return _.some($checkedLabels, function group_type(label) {
              return $(label).data("filter-value") === group.group_type;
            });
          });
        } else if (filterType === "locations") {
          filterFunctions.push(function locations(group) {
            return _.some($checkedLabels, function locations(label) {
              return _.includes(group.locations, $(label).data("filter-value"));
            });
          });
        }

      }
    });


  }

  function tickFilters(filterType, filterValue) {
    $(".js-filter-checkbox-label")
      .filter(function() { return $(this).data("filter-type") == filterType; })
      .each(function() {
        if ($(this).data("filter-value") === filterValue) {
          $(this).find("input[type=checkbox]")[0].checked = true;
        }
      });
    $(".js-list-filter[data-filter=" + filterType + "]").removeClass("filter--closed");
  }

  function clearFilterCheckboxes() {
    $(".js-filter-checkbox-label input[type=checkbox]").each(function() {
      this.checked = false;
    });
  }

  function assertAtLeastOne(collection) {
    // if (! (collection.length > 0)) {
    //   throw new Error("Expected length to be greater than zero");
    // }
    return collection;
  }

})(window.jQuery, window.wpApiSettings, window.Foundation);
