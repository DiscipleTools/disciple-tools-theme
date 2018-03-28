(function($, wpApiSettings, Foundation) {
  "use strict";
  let items; // contacts or groups
  let filterFunctions = [];
  let dataTable;
  const current_username = wpApiSettings.current_user_login;

  const templates = {
    contacts: _.template(`<tr>
      <!--<td><img src="<%- template_directory_uri %>/dt-assets/images/star.svg" width=13 height=12></td>-->
      <td></td>
      <td>
        <a href="<%- permalink %>"><%- post_title %></a>
        <br>
        <%- phone_numbers.join(", ") %>
      </td>
      <td><span class="status status--<%- overall_status %>"><%- status %></span></td>
      <td>
        <span class="milestone milestone--<%- sharing_milestone_key %>"><%- sharing_milestone %></span>
        <br>
        <span class="milestone milestone--<%- belief_milestone_key %>"><%- belief_milestone %></span>
      </td>
      <td><%- assigned_to ? assigned_to.name : "" %></td>
      <td><%= locations.join(", ") %></td>
      <td><%= group_links %></td>
      <td><%- last_modified %></td>
    </tr>`),
    groups: _.template(`<tr>
      <!--<td><img src="<%- template_directory_uri %>/dt-assets/images/green_flag.svg" width=10 height=12></td>-->
      <td></td>
      <td><a href="<%- permalink %>"><%- post_title %></a></td>
      <td><span class="group-status group-status--<%- group_status %>"><%- status %></span></td>
      <td><span class="group-type group-type--<%- group_type %>"><%- type %></span></td>
      <td style="text-align: right"><%- member_count %></td>
      <td><%= leader_links %></td>
      <td><%- locations.join(", ") %></td>
      <td><%- last_modified %></td>
    </tr>`),
  };

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

  $.ajax({
    url: wpApiSettings.root + "dt/v1/" + wpApiSettings.current_post_type,
    beforeSend: function(xhr) {
      xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
    },
    success: function(data) {
      items = data;
      $(function() {
        displayRows();
        setUpFilterPane();
        updateFilterFunctions();
        dataTable.draw();
        $(".js-sort-by").on("click", function() {
          sortBy(parseInt($(this).data("column-index")), $(this).data("order"));
        });
      });
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


  function displayRows() {
    const $table = $(".js-list");
    if (! $table.length) {
      return;
    }
    $table.find("> tbody").empty();
    _.forEach(items, function(item, index) {
      if (wpApiSettings.current_post_type === "contacts") {
        $table.append(buildContactRow(item, index));
      } else if (wpApiSettings.current_post_type === "groups") {
        $table.append(buildGroupRow(item, index));
      }
    });
    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
      const item = items[dataIndex];
      return _.every(filterFunctions, function(filterFunction) { return filterFunction(item); });
    });
    const dataTableOptions = {
      responsive: true,
      iDisplayLength: 100,
      bLengthChange: false,
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
          { targets: [0], width: "2%" },
          { targets: [1], width: "30%", },
          { targets: [2], width: "5%", },
          {
            // Hide the last modified column, it's only used for sorting
            targets: [7],
            visible: false,
            searchable: false,
          },
        ],
        order: [[7, 'desc']],
        autoWidth: false,
      });
    } else if (wpApiSettings.current_post_type === "groups") {
      _.assign(dataTableOptions, {
        columnDefs: [
          { targets: [0], width: "2%" },
          { targets: [1], width: "30%" },
          { targets: [2], width: "15%" },
          { targets: [3], width: "15%" },
          { targets: [4], width: "5%" },
          {
            // Hide the last modified column, it's only used for sorting
            targets: [7],
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

  function buildContactRow(contact, index) {
    const template = templates[wpApiSettings.current_post_type];
    const ccfs = wpApiSettings.contacts_custom_fields_settings;
    const belief_milestone_key = _.find(
      ['baptizing', 'baptized', 'belief'],
      function(key) { return contact["milestone_" + key]; }
    );
    const sharing_milestone_key = _.find(
      ['planting', 'in_group', 'sharing', 'can_share'],
      function(key) { return contact["milestone_" + key]; }
    );
    let status = "";
    if (contact.overall_status === "active") {
      status = ccfs.seeker_path.default[contact.seeker_path];
    } else {
      status = ccfs.overall_status.default[contact.overall_status];
    }
    const group_links = _.map(contact.groups, function(group) {
        return '<a href="' + _.escape(group.permalink) + '">' + _.escape(group.post_title) + "</a>";
      }).join(", ");
    const context = _.assign({last_modified: 0}, contact, wpApiSettings, {
      index,
      status,
      belief_milestone_key,
      sharing_milestone_key,
      belief_milestone: (ccfs["milestone_" + belief_milestone_key] || {}).name || "",
      sharing_milestone: (ccfs["milestone_" + sharing_milestone_key] || {}).name || "",
      group_links,
    });
    context.assigned_to = context.assigned_to;
    return $.parseHTML(template(context));
  }

  function buildGroupRow(group, index) {
    const template = templates[wpApiSettings.current_post_type];
    const leader_links = _.map(group.leaders, function(leader) {
      return '<a href="' + _.escape(leader.permalink) + '">' + _.escape(leader.post_title) + "</a>";
    }).join(", ");
    const gcfs = wpApiSettings.groups_custom_fields_settings;
    const status = gcfs.group_status.default[group.group_status || "active"];
    const type = gcfs.group_type.default[group.group_type || "active"];
    const context = _.assign({}, group, wpApiSettings, {
      leader_links,
      status,
      type
    });
    return $.parseHTML(template(context));
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
    countFilteredItems()
    clearFilterCheckboxes();
    updateFilterFunctions();
    dataTable.draw();
  });

  $(document).on('click', '.clear-filters', function () {
    $("input[value='all_contacts']").prop("checked", true);
    countFilteredItems()
    updateFilterFunctions();
    setUpFilterPane();
    dataTable.draw();
  })

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
    if (! (collection.length > 0)) {
      throw new Error("Expected length to be greater than zero");
    }
    return collection;
  }

})(window.jQuery, window.wpApiSettings, window.Foundation);
