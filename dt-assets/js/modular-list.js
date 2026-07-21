'use strict';
(function ($, list_settings, Foundation) {
  $(document).ready(function () {
    if (window.DtWebComponents && window.DtWebComponents.ComponentService) {
      const service = new window.DtWebComponents.ComponentService(
        window.list_settings.post_type,
        null,
        window.wpApiShare.nonce,
        window.wpApiShare.root,
      );
      window.componentService = service;

      service.attachLoadEvents();
    }
  });
  let selected_filters = $('#selected-filters');
  let new_filter_labels = [];
  let custom_filters = [];
  let filter_to_save = '';
  let filter_to_delete = '';
  let filterToEdit = '';
  let filter_accordions = $('#list-filter-tabs');
  let currentFilters = $('#current-filters');
  let split_by_filter_labels = $('#split_by_current_filter_select_labels');
  let cookie = window.SHAREDFUNCTIONS.get_json_from_local_storage(
    'last_view',
    {},
    list_settings.post_type,
  );
  let cached_filter;
  let get_records_promise = null;
  let loading_spinner = $('#list-loading-spinner');
  let old_filters = JSON.stringify(list_settings.filters);
  let table_header_row = $('.js-list thead .sortable th');
  let fields_to_show_in_table = window.SHAREDFUNCTIONS.get_json_cookie(
    'fields_to_show_in_table',
    [],
  );
  let fields_to_search = window.SHAREDFUNCTIONS.get_json_cookie(
    'fields_to_search',
    [],
  );
  let current_user_id = window.wpApiNotifications.current_user_id;
  let mobile_breakpoint = 1024;
  let clearSearchButton = $('.search-input__clear-button');
  let getFilterCountsPromise = null;
  const { status_field } = list_settings.post_type_settings;
  const { status_key, archived_key } = status_field ? status_field : {};
  const filterOutArchivedItemsKey = `-${archived_key}`;
  const archivedSwitch = $('#archivedToggle');
  let archivedSwitchStatus = window.SHAREDFUNCTIONS.get_json_from_local_storage(
    'list_archived_switch_status',
    false,
    list_settings.post_type,
  );
  window.post_type_fields = list_settings.post_type_settings.fields;
  window.records_list = { posts: [], total: 0 };
  const esc = window.SHAREDFUNCTIONS.escapeHTML;

  const ALL_ID = '*';
  const ALL_WITHOUT_ID = '-*';

  let items = [];
  let current_filter;

  // Initialize DT_List namespace early for bulk/exports modules
  // Functions will be populated later after they're defined
  window.DT_List = {
    // State getters/setters (can be defined early since variables exist)
    get current_filter() {
      return current_filter;
    },
    set current_filter(value) {
      current_filter = value;
    },
    get items() {
      return items;
    },
    set items(value) {
      items = value;
    },
    // Placeholder for bulk module to register itself
    bulk: null,
    // Placeholder for exports module to register itself
    exports: null,
    // Wrapper that checks if bulk module is loaded
    bulk_edit_count: function () {
      if (window.DT_List.bulk && window.DT_List.bulk.bulk_edit_count) {
        window.DT_List.bulk.bulk_edit_count();
      }
    },
  };

  on_load();

  function on_load() {
    let cached_filter = cookie;

    const query_param_custom_filter = create_custom_filter_from_query_params();

    setup_archived_switch_position(archivedSwitchStatus);

    current_filter = get_current_filter(
      query_param_custom_filter,
      cached_filter,
    );

    setup_filters();

    setup_custom_cached_filter(
      query_param_custom_filter,
      cached_filter,
      current_filter,
    );

    determine_list_columns(fields_to_show_in_table);

    get_records_for_current_filter();

    collapse_filters();

    get_filter_counts(old_filters);

    reset_sorting_in_table_header(current_filter);

    // Initialize bulk edit button visibility (will be called by bulk module when loaded)
    window.DT_List.bulk_edit_count();
  }

  function get_current_filter(urlCustomFilter, cachedFilter) {
    const { filterID, filterTab, query } = get_url_query_params();

    if (filterID && is_in_filter_list(filterID)) {
      const currentFilter = { ID: filterID, query: query || {} };
      if (filterTab) {
        currentFilter.tab = filterTab;
      } else {
        currentFilter.tab = list_settings.filters.filters.find(
          (filter) => filterID === filter.ID,
        )?.tab;
      }
      return currentFilter;
    } else if (urlCustomFilter && !window.lodash.isEmpty(urlCustomFilter)) {
      return urlCustomFilter;
    } else if (cachedFilter && !window.lodash.isEmpty(cachedFilter)) {
      return cachedFilter;
    }
    return { query: {} };
  }

  function setup_custom_cached_filter(
    urlCustomFilter,
    cachedFilter,
    currentFilter,
  ) {
    const { filterID } = get_url_query_params();

    if (
      !is_in_filter_list(filterID) &&
      urlCustomFilter &&
      !window.lodash.isEmpty(urlCustomFilter) &&
      urlCustomFilter.type === 'custom_filter'
    ) {
      urlCustomFilter.query.offset = 0;
      add_custom_filter(
        urlCustomFilter.name,
        'default',
        urlCustomFilter.query,
        urlCustomFilter.labels,
        false,
      );
    } else if (
      !is_in_filter_list(filterID) &&
      cachedFilter &&
      !window.lodash.isEmpty(cachedFilter) &&
      cachedFilter.type === 'custom_filter'
    ) {
      cachedFilter.query.offset = 0;
      add_custom_filter(
        cachedFilter.name,
        'default',
        cachedFilter.query,
        cachedFilter.labels,
        false,
      );
    } else {
      //check select filter
      if (currentFilter.ID) {
        //open the filter tabs
        $(
          `#list-filter-tabs [data-id='${window.SHAREDFUNCTIONS.escapeHTML(currentFilter.tab)}'] a`,
        ).click();
        let filter_element = $(
          `input[name=view][data-id="${window.SHAREDFUNCTIONS.escapeHTML(currentFilter.ID)}"].js-list-view`,
        );
        if (filter_element.length) {
          filter_element.prop('checked', true);
        } else {
          check_first_filter();
        }
      } else {
        check_first_filter();
      }
    }
  }

  function check_first_filter() {
    $('#list-filter-tabs .accordion-item a')[0].click();
    $($('.js-list-view')[0]).prop('checked', true);
  }

  function determine_list_columns(fieldsToShowInTable) {
    if (window.lodash.isEmpty(fieldsToShowInTable)) {
      fields_to_show_in_table = list_settings.fields_to_show_in_table;
    }
  }

  // get records when a filter is clicked
  $(document).on('change', '.js-list-view', () => {
    reset_split_by_filters();
    get_records_for_current_filter();
  });

  //load record for the first filter when a tile is clicked
  $(document).on('click', '.accordion-title', function () {
    let selected_filter = $('.js-list-view:checked').data('id');
    let tab = $(this).data('id');
    if (selected_filter) {
      $(`.accordion-item[data-id='${tab}'] .js-list-view`)
        .first()
        .prop('checked', true);
      get_records_for_current_filter();
    }
  });

  // Support field name filtering
  let searchable_filter_field_objects = build_searchable_filter_field_objects();
  function build_searchable_filter_field_objects() {
    let searchable_objs = [];

    $('#filter-tabs')
      .children()
      .each(function (idx, li) {
        searchable_objs.push({
          id: $(li).find('a').attr('id'),
          name: $(li).find('a').text().trim(),
        });
      });

    return searchable_objs;
  }

  $(document).on('search', '#field-filter-name', function () {
    execute_searchable_filter_field_query($(this).val());
  });

  $(document).on('keyup', '#field-filter-name', function () {
    execute_searchable_filter_field_query($(this).val());
  });

  function execute_searchable_filter_field_query(query) {
    // Search across field objects...
    let hits = window.lodash.filter(
      searchable_filter_field_objects,
      function (field) {
        return window.lodash.includes(
          field.name.trim().toLowerCase(),
          query.trim().toLowerCase(),
        );
      },
    );

    // Refresh filter fields list
    refresh_searchable_filter_field_objects(hits);
  }

  function refresh_searchable_filter_field_objects(fields) {
    $('#filter-tabs').fadeOut('fast', function () {
      // Iterate over filter tab element's children
      $('#filter-tabs')
        .children()
        .each(function (idx, li) {
          let id = $(li).find('a').attr('id');
          let name = $(li).find('a').text().trim();

          // Determine visibility state to adopt
          if (window.lodash.find(fields, { id: id, name: name })) {
            $(li).show();
          } else {
            $(li).hide();
          }
        });

      // Default to selecting first field within refreshed list
      let selected_li = $('#filter-tabs li').not('[style*="display"]').first();
      $(selected_li).find('a').trigger('click');

      // Display refreshed fields
      $('#filter-tabs').fadeIn('fast');
    });
  }

  // Remove filter labels
  $(document).on('click', '.current-filter-list-close', function () {
    let label = $(this).parent();
    remove_current_filter_label(
      label,
      get_current_filter_label_field_details(label),
    );
  });

  // Collapse filter tile for mobile view
  function collapse_filters() {
    if (window.Foundation.MediaQuery.only('small')) {
      $('#list-filters .bordered-box').addClass('collapsed');
    } else {
      $('#list-filters .bordered-box').removeClass('collapsed');
    }
  }

  $(window).resize(function () {
    collapse_filters();
  });

  function get_current_filter_label_field_details(label) {
    let field_id = null;
    let field_name = $(label).children().remove().end().text();

    let label_classes = $(label).attr('class').split(/\s+/);
    $.each(label_classes, function (idx, cls) {
      if (cls !== 'current-filter') {
        field_id = cls;
      }
    });

    return {
      id: field_id,
      name: field_name,
    };
  }

  function normalize_filter_field_key(field_key) {
    let safe_key = String(field_key || '');
    return safe_key.replace(/_(start|end)$/, '');
  }

  function remove_current_filter_label(label, field_details) {
    if (current_filter && current_filter.labels) {
      if (field_details && field_details.id && field_details.name) {
        // Update current filter's labels
        let id = null;
        let labels = [];
        $.each(current_filter.labels, function (idx, val) {
          if (
            field_details.id === val.field &&
            field_details.name === val.name
          ) {
            id = val.id;
          } else {
            labels.push(val);
          }
        });
        current_filter.labels = labels;

        // Update current filter's query object
        if (id) {
          // Determine query object shape
          if (current_filter.query['fields']) {
            let fields = [];
            $.each(current_filter.query['fields'], function (idx, val) {
              let field_values = [];

              // filter out only the specific ID
              if (!val[field_details.id]) {
                // push the filter if it's not our field
                fields.push(val);
              } else if (field_details.id === normalize_filter_field_key(id)) {
                // filter val to exclude val[start] or val[end] based on id's _start or _end suffix
                let suffix = id.replace(field_details.id, '');

                if (suffix == '_start' && val[field_details.id].end) {
                  val[field_details.id] = {
                    end: val[field_details.id].end, // Removed the extra ')' here
                  };
                  fields.push(val);
                } else if (suffix == '_end' && val[field_details.id].start) {
                  val[field_details.id] = {
                    start: val[field_details.id].start, // Added the missing logic here
                  };
                  fields.push(val);
                }
              } else if (field_details.id !== id) {
                // loop through connection fields
                // filter the selected option if it's an array (connections)
                $.each(val[field_details.id], function (i, v) {
                  if (v !== id.toString()) {
                    field_values.push(v);
                  }
                });
              }

              // if there are still connections left for this field, keep them
              if (field_values.length > 0) {
                val[field_details.id] = field_values;
                fields.push(val);
              }
            });
            current_filter.query['fields'] = fields;
          } else if (current_filter.query[field_details.id]) {
            let field_values = [];
            $.each(current_filter.query[field_details.id], function (idx, val) {
              if (id !== val) {
                field_values.push(val);
              }
            });

            // Update query field, if still populated
            if (field_values.length > 0) {
              current_filter.query[field_details.id] = field_values;
            } else {
              delete current_filter.query[field_details.id];
            }
          } else if (current_filter.query['text']) {
            // Remove text property, to force a return to all filtered view
            delete current_filter.query['text'];

            // Locate and select corresponding all radio button
            $('.list-views')
              .find('.js-list-view')
              .each(function (idx, input) {
                if (
                  $.inArray($(input).data('id'), ['all_my_contacts', 'all']) !==
                  -1
                ) {
                  $(input).prop('checked', true);
                }
              });
          }
        }
      }
    }

    // Remove label from view
    $(label).remove();

    // Refresh view records
    get_records_for_current_filter(current_filter, true);
  }

  /**
   * Creates a custom filter from the query and labels in the encoded url
   */
  function create_custom_filter_from_query_params() {
    const { query, labels, filterName } = get_url_query_params();

    if (!query) return {};

    /* Creating object the same shape as cached_filter */
    let query_custom_filter = {
      ID: Date.now() / 1000,
      name: filterName ? filterName : 'Custom Filter',
      type: 'custom_filter',
      labels: [],
      query: {},
    };

    if (Object.prototype.hasOwnProperty.call(query, 'offset')) {
      query.offset = 0;
    }
    if (Object.prototype.hasOwnProperty.call(query, 'sort')) {
      query.sort = 'name';
    }

    if (query) {
      query_custom_filter.query = query;
    }

    if (labels) {
      query_custom_filter.labels = labels;
    }

    return query_custom_filter;
  }

  function get_url_query_params() {
    const url = new URL(window.location);
    const encodedQuery = url.searchParams.get('query');
    const encodedLabels = url.searchParams.get('labels');
    const filterID = url.searchParams.get('filter_id');
    const filterTab = url.searchParams.get('filter_tab');
    const filterName = url.searchParams.get('filter_name');
    const query =
      encodedQuery && window.SHAREDFUNCTIONS.decodeJSON(encodedQuery);
    const labels =
      encodedLabels && window.SHAREDFUNCTIONS.decodeJSON(encodedLabels);
    return {
      query,
      labels,
      filterID,
      filterTab,
      filterName,
    };
  }

  function is_in_filter_list(filterID) {
    return list_settings.filters.filters.some(
      (filter) => filterID === filter.ID,
    );
  }

  function update_url_query(currentFilter) {
    const encodedQuery = window.SHAREDFUNCTIONS.encodeJSON(currentFilter.query);
    const encodedLabels = window.SHAREDFUNCTIONS.encodeJSON(
      currentFilter.labels,
    );

    const url = new URL(window.location);

    url.searchParams.set('query', encodedQuery);
    url.searchParams.set('labels', encodedLabels);
    url.searchParams.set('filter_id', currentFilter.ID);
    url.searchParams.set('filter_tab', currentFilter.tab || '');
    url.searchParams.set('filter_name', currentFilter.name || '');

    window.history.pushState(null, document.title, url.search);
  }

  function get_records_for_current_filter(
    custom_filter = null,
    remove_all_split_by_checked_options = false,
  ) {
    let checked = $('.js-list-view:checked');
    let current_view = checked.val();
    let filter_id = checked.data('id') || current_view || '';
    let sort = current_filter.query.sort || null;

    // Determine if default resets are required?
    if (custom_filter) {
      current_filter = custom_filter;

      // If specified, ensure to uncheck all split by option filters, to avoid infinity loops!
      if (remove_all_split_by_checked_options) {
        $('.js-list-view-split-by').prop('checked', false);
      }
    } else if (current_view === 'custom_filter') {
      let filterId = checked.data('id');
      current_filter = window.lodash.find(custom_filters, { ID: filterId });
      current_filter.type = current_view;
    } else {
      current_filter =
        window.lodash.find(list_settings.filters.filters, { ID: filter_id }) ||
        window.lodash.find(list_settings.filters.filters, {
          ID: filter_id.toString(),
        }) ||
        current_filter;
      current_filter.type = 'default';
      current_filter.labels = current_filter.labels || [
        { id: filter_id, name: current_filter.name },
      ];
    }
    if (current_filter.query === undefined) {
      current_filter.query = {};
    }
    sort = sort || current_filter.query.sort;
    current_filter.query.sort = typeof sort === 'string' ? sort : '-post_date';

    // Conduct a deep copy (clone) of filter, to support future returns to default
    current_filter = $.extend(true, {}, current_filter);

    // Determine if any split by filters are to be applied.
    let checked_split_by = $('.js-list-view-split-by:checked');
    if (checked_split_by && checked_split_by.length > 0) {
      current_filter = apply_split_by_filters(
        current_filter,
        checked_split_by.data('field_id'),
        checked_split_by.data('field_option_id'),
        checked_split_by.data('field_option_label'),
      );
    }

    clear_search_query();

    get_records();
  }

  function clear_search_query() {
    // clear query if the current_filter is not a search query with the same text as the search-query
    const searchLabel = current_filter.labels.find(
      (label) => label.id === 'search',
    );
    if (
      searchLabel &&
      (searchLabel.name === $('#search-query').val() ||
        searchLabel.name === $('#search-query-mobile').val())
    ) {
      return;
    }
    if ($('#search-query').val() !== '') {
      $('#search-query').val('');
    }
    if ($('#search-query-mobile').val() !== '') {
      $('#search-query-mobile').val('');
    }
  }

  function setup_filters() {
    if (!list_settings.filters.tabs) {
      return;
    }
    list_settings.filters.tabs.push({
      key: 'split_by',
      label: list_settings.translations.split_by,
      order: 98, // right before Custom Filters
    });
    list_settings.filters.tabs.sort((a, b) => (a.order || 0) - (b.order || 0));

    let selected_tab = $('.accordion-item.is-active').data('id');
    let selected_filter = $('.js-list-view:checked').data('id');
    let html = ``;
    list_settings.filters.tabs.forEach((tab) => {
      html += `
      <li class="accordion-item" data-accordion-item data-id="${window.SHAREDFUNCTIONS.escapeHTML(tab.key)}">
        <a href="#" class="accordion-title" data-id="${window.SHAREDFUNCTIONS.escapeHTML(tab.key)}">
          ${window.SHAREDFUNCTIONS.escapeHTML(tab.label)}
          <span class="tab-count-span" data-tab="${window.SHAREDFUNCTIONS.escapeHTML(tab.key)}">
              ${Number.isInteger(tab.count) ? `(${window.SHAREDFUNCTIONS.escapeHTML(tab.count)})` : ``}
          </span>
        </a>`;

      if (tab.key === 'split_by') {
        const split_by_content = document
          .getElementById('template-split-by-filter')
          .cloneNode(true);
        html += `
        <div class="accordion-content" data-tab-content>
            ${split_by_content.innerHTML}
        </div>
        </li>
        `;
      } else {
        html += `
        <div class="accordion-content" data-tab-content>
          <div class="list-views">
            ${list_settings.filters.filters
              .map((filter) => {
                if (filter.tab === tab.key && filter.tab !== 'custom') {
                  let indent =
                    filter.subfilter && Number.isInteger(filter.subfilter)
                      ? 15 * filter.subfilter
                      : 15;
                  return `
                        <label class="list-view" style="${filter.subfilter ? `margin-left:${indent}px` : ''}">
                          <input type="radio" name="view" value="${window.SHAREDFUNCTIONS.escapeHTML(filter.ID)}" data-id="${window.SHAREDFUNCTIONS.escapeHTML(filter.ID)}" class="js-list-view" autocomplete="off">
                          <span class="list-view__text" id="total_filter_label" title="${window.SHAREDFUNCTIONS.escapeHTML(filter.name)}">${window.SHAREDFUNCTIONS.escapeHTML(filter.name)}</span>
                          <span class="list-view__count js-list-view-count" data-value="${window.SHAREDFUNCTIONS.escapeHTML(filter.ID)}">${window.SHAREDFUNCTIONS.escapeHTML(filter.count)}</span>
                        </label>
                        `;
                }
              })
              .join('')}
          </div>
        </div>
      </li>
      `;
      }
    });
    filter_accordions.html(html);

    let saved_filters_list = $(
      `#list-filter-tabs [data-id='custom'] .list-views`,
    );
    saved_filters_list.empty();
    if (
      list_settings.filters.filters.filter((t) => t.tab === 'custom').length ===
      0
    ) {
      saved_filters_list.html(
        `<span>${window.SHAREDFUNCTIONS.escapeHTML(list_settings.translations.empty_custom_filters)}</span>`,
      );
    }
    list_settings.filters.filters
      .filter((t) => t.tab === 'custom')
      .forEach((filter) => {
        if (filter && filter.visible === '') {
          return;
        }
        let delete_filter =
          $(`<span style="float:right" data-filter="${window.SHAREDFUNCTIONS.escapeHTML(filter.ID)}">
        <img style="padding: 0 4px" src="${window.wpApiShare.template_dir}/dt-assets/images/trash.svg">
      </span>`);
        delete_filter.on('click', function () {
          $(`.delete-filter-name`).text(filter.name);
          $('#delete-filter-modal').foundation('open');
          filter_to_delete = filter.ID;
        });
        let edit_filter =
          $(`<span style="float:right" data-filter="${window.SHAREDFUNCTIONS.escapeHTML(filter.ID)}">
          <img style="padding: 0 4px" src="${window.wpApiShare.template_dir}/dt-assets/images/edit.svg">
      </span>`);
        edit_filter.on('click', function () {
          edit_saved_filter(filter);
          filterToEdit = filter.ID;
        });
        let filterName = `<span class="filter-list-name" data-filter="${window.SHAREDFUNCTIONS.escapeHTML(filter.ID)}">${window.SHAREDFUNCTIONS.escapeHTML(filter.name)}</span>`;
        const radio = $(
          `<input name='view' class='js-list-view' autocomplete='off' data-id="${window.SHAREDFUNCTIONS.escapeHTML(filter.ID)}" >`,
        )
          .attr('type', 'radio')
          .val('saved-filters')
          .on('change', function () {});
        saved_filters_list.append(
          $('<div>').append(
            $('<label>')
              .css('cursor', 'pointer')
              .addClass('js-filter-checkbox-label')
              .data('filter-value', status)
              .append(radio)
              .append(filterName)
              .append(delete_filter)
              .append(edit_filter),
          ),
        );
      });
    new window.Foundation.Accordion(filter_accordions, {
      slideSpeed: 100,
      allowAllClosed: true,
    });
    if (selected_tab) {
      $(
        `#list-filter-tabs [data-id='${window.SHAREDFUNCTIONS.escapeHTML(selected_tab)}'] a`,
      ).click();
    }
    if (selected_filter) {
      $(
        `[data-id="${window.SHAREDFUNCTIONS.escapeHTML(selected_filter)}"].js-list-view`,
      ).prop('checked', true);
    }
  }

  function get_filter_counts(oldFilters) {
    if (
      getFilterCountsPromise &&
      window.lodash.get(getFilterCountsPromise, 'readyState') !== 4
    ) {
      getFilterCountsPromise.abort();
    }
    getFilterCountsPromise = $.ajax({
      url: `${window.wpApiShare.root}dt/v1/users/get_filters?post_type=${list_settings.post_type}&force_refresh=1`,
      beforeSend: function (xhr) {
        xhr.setRequestHeader('X-WP-Nonce', window.wpApiShare.nonce);
      },
    });
    getFilterCountsPromise
      .then((filters) => {
        if (oldFilters !== JSON.stringify(filters)) {
          list_settings.filters = filters;
          setup_filters();
        }
      })
      .catch((err) => {
        if (window.lodash.get(err, 'statusText') !== 'abort') {
          console.error(err);
        }
      });
  }

  function setup_current_filter_labels() {
    let html = '';
    let filter = current_filter;
    if (filter && filter.labels) {
      filter.labels.forEach((label) => {
        // Determine exclusion status
        let excluded_class = is_search_query_filter_label_excluded(
          filter,
          label,
        )
          ? 'current-filter-list-excluded'
          : '';

        if (label?.name) {
          // Proceed with displaying of filter label
          html += `<span class="current-filter-list ${excluded_class} ${window.SHAREDFUNCTIONS.escapeHTML(label.field)}">${window.SHAREDFUNCTIONS.escapeHTML(label.name)}`;

          if (label.id && label.field && label.name) {
            html += `<span class="current-filter-list-close">x</span>`;
          } else {
            html += `&nbsp;`;
          }

          html += `</span>`;
        }
      });
    } else {
      let query = filter.query;
      window.lodash.forOwn(query, (query_key) => {
        if (Array.isArray(query[query_key])) {
          query[query_key].forEach((q) => {
            html += `<span class="current-filter-list ${window.SHAREDFUNCTIONS.escapeHTML(query_key)}">${window.SHAREDFUNCTIONS.escapeHTML(q)}&nbsp;</span>`;
          });
        } else {
          html += `<span class="current-filter-list search">${window.SHAREDFUNCTIONS.escapeHTML(query[query_key])}&nbsp;</span>`;
        }
      });
    }

    // Capture available filters, ensuring to ignore any sort labels below.
    split_by_filter_labels.html(html);

    if (filter.query.sort) {
      let sortLabel = filter.query.sort;
      if (sortLabel.includes('last_modified')) {
        sortLabel = list_settings.translations.date_modified;
      } else if (sortLabel.includes('post_date')) {
        sortLabel = list_settings.translations.creation_date;
      } else {
        // remove leading dash from sort filter key when reverse sorting
        const leadingDashSearch = new RegExp('^-');
        const querySortKey =
          sortLabel.search(leadingDashSearch) > -1
            ? sortLabel.replace(leadingDashSearch, '')
            : sortLabel;
        sortLabel = window.lodash.get(
          list_settings,
          `post_type_settings.fields[${querySortKey}].name`,
          sortLabel,
        );
      }
      html += `<span class="current-filter-list" data-id="sort">
          ${window.SHAREDFUNCTIONS.escapeHTML(list_settings.translations.sorting_by)}: ${window.SHAREDFUNCTIONS.escapeHTML(sortLabel)}
      &nbsp;</span>`;
    }
    currentFilters.html(html);
  }

  function reset_sorting_in_table_header(currentFilter) {
    let sort_field = window.lodash.get(
      currentFilter,
      'query.sort',
      '-post_date',
    );
    //reset sorting in table header
    table_header_row.removeClass('sorting_asc');
    table_header_row.removeClass('sorting_desc');
    let header_cell = $(
      `.js-list thead .sortable th[data-id="${window.SHAREDFUNCTIONS.escapeHTML(sort_field.replace('-', ''))}"]`,
    );
    header_cell.addClass(
      `sorting_${sort_field.startsWith('-') ? 'desc' : 'asc'}`,
    );
    table_header_row.data('sort', '');
    header_cell.data('sort', 'asc');
  }

  $('.js-sort-by').on('click', function () {
    table_header_row.removeClass('sorting_asc');
    table_header_row.removeClass('sorting_desc');
    let dir = $(this).data('order');
    let field = $(this).data('field');
    get_records(0, (dir === 'asc' ? '' : '-') + field);
  });

  //sort the table by clicking the header
  $('.js-list th').on('click', function () {
    //check is this is the bulk_edit_master checkbox
    if (this.id == 'bulk_edit_master') {
      return;
    }
    let id = $(this).data('id');
    let sort = $(this).data('sort');
    table_header_row.removeClass('sorting_asc');
    table_header_row.removeClass('sorting_desc');
    table_header_row.data('sort', '');
    if (!sort || sort === 'desc') {
      $(this).data('sort', 'asc');
      $(this).addClass('sorting_asc');
      $(this).removeClass('sorting_desc');
    } else {
      $(this).data('sort', 'desc');
      $(this).removeClass('sorting_asc');
      $(this).addClass('sorting_desc');
      id = `-${id}`;
    }
    get_records(0, id);
  });

  $('#save_column_choices').on('click', function () {
    let selectedFields = $('#field_search_input').val() || '[]';

    fields_to_show_in_table = selectedFields.filter(
      (label) => label.charAt(0) !== '-',
    );
    window.SHAREDFUNCTIONS.save_json_cookie(
      'fields_to_show_in_table',
      fields_to_show_in_table,
      list_settings.post_type,
    );
    window.location.reload();
  });

  archivedSwitch.on('click', function () {
    const showArchived = this.checked;

    archivedSwitchStatus = showArchived;
    window.SHAREDFUNCTIONS.save_json_to_local_storage(
      'list_archived_switch_status',
      showArchived,
      list_settings.post_type,
    );

    get_records();
  });

  function setup_archived_switch_position(switchStatus) {
    archivedSwitch.prop('checked', switchStatus);
  }

  function apply_archived_toggle_to_current_filter() {
    if (!list_settings?.post_type_settings?.status_field) return;
    const showArchived = archivedSwitchStatus;
    let status = get_filtered_status();

    if (showArchived && status && status.includes(filterOutArchivedItemsKey)) {
      const index = status.indexOf(filterOutArchivedItemsKey);
      status.splice(index, 1);

      // Remove status property from query if empty.
      if (status.length === 0) {
        if (is_custom_filter()) {
          current_filter.query.fields = current_filter.query.fields.filter(
            (item) => !Object.prototype.hasOwnProperty.call(item, status_key),
          );
        } else {
          delete current_filter.query[status_key];
        }
      }
    }

    if (!showArchived && (!status || status.length === 0)) {
      set_filtered_status([filterOutArchivedItemsKey]);
    }
  }

  function is_custom_filter() {
    return !!current_filter.query.fields;
  }

  function get_filtered_status() {
    return is_custom_filter()
      ? get_status_field_in_custom_filter()
      : current_filter.query[status_key];
  }

  function set_filtered_status(newStatus) {
    if (is_custom_filter()) {
      set_status_field_in_custom_filter(newStatus);
    } else {
      current_filter.query[status_key] = newStatus;
    }
  }

  function get_status_field_in_custom_filter() {
    const query = current_filter.query;
    const fields = query.fields;

    if (!fields || !Array.isArray(fields)) return [];

    const filterItem = fields.find((item) =>
      Object.prototype.hasOwnProperty.call(item, status_key),
    );
    return filterItem && filterItem[status_key];
  }

  function set_status_field_in_custom_filter(newStatus) {
    const fields = current_filter.query.fields;
    if (!fields || !Array.isArray(fields)) return;

    const index = fields.findIndex((item) =>
      Object.prototype.hasOwnProperty.call(item, status_key),
    );
    if (index === -1) {
      fields.push({ [status_key]: newStatus });
    } else {
      fields[index][status_key] = newStatus;
    }
  }

  $('#records-table')
    .dragableColumns({
      drag: true,
      dragClass: 'drag',
      overClass: 'over',
      movedContainerSelector: '.dnd-moved',
      onDragEnd: () => {
        fields_to_show_in_table = [];
        $('.table-headers th').each((i, e) => {
          let field = $(e).data('id');
          if (field) {
            fields_to_show_in_table.push(field);
          }
        });
        window.SHAREDFUNCTIONS.save_json_cookie(
          'fields_to_show_in_table',
          fields_to_show_in_table,
          list_settings.post_type,
        );
      },
    })
    .on('click', 'tbody tr', function (event) {
      //open the record if the row is clicked. Give priority to normal browser behavior with links.
      if (!event.target.href) {
        window.location = $(this).data('link');
      }
    });

  let build_table = (records) => {
    let table_rows = ``;
    let mobile = $(window).width() < mobile_breakpoint;
    records.forEach((record, index) => {
      let row_fields_html = '';
      fields_to_show_in_table.forEach((field_key) => {
        let values_html = '';
        let data_type = '';
        let values = [];
        if (field_key === 'name') {
          /*if (mobile) {
            return;
          }*/
          values_html = `<li><a href="${window.SHAREDFUNCTIONS.escapeHTML(record.permalink)}" title="${window.SHAREDFUNCTIONS.escapeHTML(record.post_title)}">${window.SHAREDFUNCTIONS.escapeHTML(record.post_title)}</a></li>`;
        } else if (field_key === 'record_picture') {
          return; // we are always including this, so skip it
        } else if (list_settings.post_type_settings.fields[field_key]) {
          let field_settings =
            list_settings.post_type_settings.fields[field_key];
          if (field_settings.type) {
            data_type = field_settings.type;
          }
          let field_value = window.lodash.get(record, field_key, false);
          if (field_key !== 'favorite' && field_settings.type === 'boolean') {
            field_value = window.lodash.get(record, field_key);
          }

          /* breadcrumb: new-field-type Display field in table */
          if (field_value !== false) {
            if (['text', 'textarea', 'number'].includes(field_settings.type)) {
              values = [window.SHAREDFUNCTIONS.escapeHTML(field_value)];
            } else if (field_settings.type === 'date') {
              values = [
                window.SHAREDFUNCTIONS.escapeHTML(
                  window.SHAREDFUNCTIONS.formatDate(field_value.timestamp),
                ),
              ];
            } else if (field_settings.type === 'datetime') {
              values = [
                window.SHAREDFUNCTIONS.escapeHTML(
                  window.SHAREDFUNCTIONS.formatDate(
                    field_value.timestamp,
                    true,
                  ),
                ),
              ];
            } else if (field_settings.type === 'user_select') {
              values = [window.SHAREDFUNCTIONS.escapeHTML(field_value.display)];
            } else if (field_settings.type === 'key_select') {
              values = [window.SHAREDFUNCTIONS.escapeHTML(field_value.label)];
            } else if (field_settings.type === 'multi_select') {
              values = field_value.map((v) => {
                return `${window.SHAREDFUNCTIONS.escapeHTML(window.lodash.get(field_settings, `default[${v}].label`, v))}`;
              });
            } else if (field_settings.type === 'link') {
              values = field_value.map((link) => {
                return window.SHAREDFUNCTIONS.escapeHTML(link.value);
              });
            } else if (field_settings.type === 'tags') {
              values = field_value.map((v) => {
                return `${window.SHAREDFUNCTIONS.escapeHTML(window.lodash.get(field_settings, `default[${v}].label`, v))}`;
              });
            } else if (
              field_settings.type === 'location' ||
              field_settings.type === 'location_meta'
            ) {
              values = field_value.map((v) => {
                return `${window.SHAREDFUNCTIONS.escapeHTML(v.label)}`;
              });
            } else if (field_settings.type === 'communication_channel') {
              values = field_value.map((v) => {
                return `${window.SHAREDFUNCTIONS.escapeHTML(v.value)}`;
              });
            } else if (field_settings.type === 'connection') {
              values = field_value.map((v) => {
                let meta = [];
                if (field_settings.meta_fields) {
                  Object.keys(field_settings.meta_fields).forEach((key) => {
                    if (v.meta && v.meta[key]) {
                      meta.push(v.meta[key]);
                    }
                  });
                }
                return `${window.SHAREDFUNCTIONS.escapeHTML(v.post_title)}${meta.length ? ` (${meta.join(',')})` : ''}`;
              });
            } else if (field_settings.type === 'boolean') {
              if (field_key === 'favorite') {
                values = [
                  `<svg class='icon-star${field_value === true ? ' selected' : ''}' viewBox="0 0 32 32" data-id=${record.ID}><use xlink:href="${window.wpApiShare.template_dir}/dt-assets/images/star.svg#star"></use></svg>`,
                ];
              } else if (field_value === true) {
                values = ['&check;'];
              }
            } else if (field_settings.type === 'task') {
              values = field_value
                .filter((v) => {
                  return v.value && v.value.note && v.value.note !== '';
                })
                .map((v) => {
                  return `${window.SHAREDFUNCTIONS.escapeHTML(v.value.note)}`;
                });
            } else if (field_settings.type === 'datetime_series') {
              if (Array.isArray(field_value)) {
                values = field_value.map((v) => {
                  return window.SHAREDFUNCTIONS.escapeHTML(v.formatted);
                });
              }
            } else if (field_settings.type === 'image') {
              values = [`<img src='${field_value.thumb}' class='list-image'>`];
            } else if (field_settings.type === 'file_upload') {
              const fileCount = Array.isArray(field_value)
                ? field_value.length
                : Array.isArray(field_value?.values)
                  ? field_value.values.length
                  : 0;
              if (fileCount > 0) {
                values = [
                  `${window.SHAREDFUNCTIONS.escapeHTML(String(fileCount))} ${fileCount === 1 ? 'file' : 'files'}`,
                ];
              }
            }
          } else if (
            !field_value &&
            field_settings.type === 'boolean' &&
            field_key === 'favorite'
          ) {
            values = [
              `<svg class='icon-star' viewBox="0 0 32 32" data-id=${record.ID}><use xlink:href="${window.wpApiShare.template_dir}/dt-assets/images/star.svg#star"></use></svg>`,
            ];
          } else if (
            field_settings.type === 'boolean' &&
            field_settings.default === true &&
            (field_value === undefined || field_value === null)
          ) {
            values = ['&check;'];
          } else if (field_settings.type === 'image') {
            values = [
              `<i class='${window.SHAREDFUNCTIONS.escapeHTML(list_settings.default_icon)} medium list-image'></i>`,
            ];
          }
        } else {
          return;
        }
        values_html += values
          .map((v, index) => {
            return `<li>${v}</li>`;
          })
          .join('');

        let title = values
          .map((val) => {
            // replace star svg with html entity for valid title attribute
            if (
              val &&
              typeof val === 'string' &&
              val.includes('<svg') &&
              val.includes('icon-star')
            ) {
              return val.includes('selected') ? '&#9734;' : '&#9733;';
            }
            return val;
          })
          .join(', ');

        //exclude html tags from title
        if (title.includes('<')) {
          title = '';
        }
        const tmp_html = `
        <td dir="auto" data-id="${field_key}" data-type="${data_type}" title="${title}">
          <div class="field-label">
            ${window.SHAREDFUNCTIONS.escapeHTML(window.lodash.get(list_settings, `post_type_settings.fields[${field_key}].name`, field_key))}
          </div>
          <div class="field-value">
            <ul dir="auto">${values_html}</ul>
          </div>
        </td>`;

        if (field_key === 'favorite') {
          row_fields_html = tmp_html + row_fields_html;
        } else {
          row_fields_html += tmp_html;
        }
      });

      const record_img =
        record.record_picture && record.record_picture.thumb
          ? `<img src='${record.record_picture.thumb}' class='list-image'>`
          : `<i class='${window.SHAREDFUNCTIONS.escapeHTML(list_settings.default_icon)} medium list-image'></i>`;
      table_rows += `<tr class="dnd-moved" data-link="${window.SHAREDFUNCTIONS.escapeHTML(record.permalink)}">
        <td class="index bulk_edit_checkbox" data-id="record_picture" data-type="image">
          <div class="record_picture">${record_img}</div>
          <input type="checkbox" name="bulk_edit_id" value="${record.ID}">
        </td>
        ${row_fields_html}
      </tr>`;
    });
    if (records.length === 0) {
      table_rows = `<tr><td colspan="10">${window.SHAREDFUNCTIONS.escapeHTML(list_settings.translations.empty_list)}</td></tr>`;
    }

    let table_html = `
      ${table_rows}
    `;
    $('#table-content').html(table_html);
    // Call bulk module's checkbox event setup if available
    if (window.DT_List.bulk && window.DT_List.bulk.setupCheckboxEvent) {
      window.DT_List.bulk.setupCheckboxEvent();
    }
    favorite_edit_event();
  };

  window.SHAREDFUNCTIONS['empty_list'] = empty_list;

  function empty_list() {
    $('#table-content').html(
      `<tr><td colspan="10">${window.SHAREDFUNCTIONS.escapeHTML(list_settings.translations.empty_list)}</td></tr>`,
    );
  }

  function get_records(offset = 0, sort = null) {
    loading_spinner.addClass('active');
    let query = current_filter.query;
    if (offset) {
      query.offset = offset;
      query.limit = 500;
    }
    if (sort) {
      query.sort = sort;
      query.offset = 0;
    }

    update_url_query(current_filter);
    apply_archived_toggle_to_current_filter();

    window.SHAREDFUNCTIONS.save_json_to_local_storage(
      `last_view`,
      current_filter,
      list_settings.post_type,
    );
    if (
      get_records_promise &&
      window.lodash.get(get_records_promise, 'readyState') !== 4
    ) {
      get_records_promise.abort();
    }
    query.fields_to_return = fields_to_show_in_table;
    // if (window.wpApiShare.features.storage) {
    //   query.fields_to_return.unshift('record_picture');
    // }
    get_records_promise = window.makeRequestOnPosts(
      'POST',
      `${list_settings.post_type}/list`,
      JSON.parse(JSON.stringify(query)),
    );
    return get_records_promise
      .then((response) => {
        if (offset) {
          items = window.lodash.unionBy(items, response.posts || [], 'ID');
        } else {
          items = response.posts || [];
        }
        window.records_list.posts = items; // adds global access to current list for plugins
        window.records_list.total = response.total;

        // save
        if (
          Object.prototype.hasOwnProperty.call(response, 'posts') &&
          response.posts.length > 0
        ) {
          let records_list_ids_and_type = [];

          $.each(items, function (id, post_object) {
            records_list_ids_and_type.push({ ID: post_object.ID });
          });

          window.SHAREDFUNCTIONS.save_json_cookie(
            `records_list`,
            records_list_ids_and_type,
            list_settings.post_type,
          );
        }

        $('#bulk_edit_master_checkbox').prop('checked', false); //unchecks the bulk edit master checkbox when the list reloads.

        $('#load-more').toggle(items.length !== parseInt(response.total));
        let result_text = list_settings.translations.txt_info
          .replace('_START_', items.length)
          .replace('_TOTAL_', response.total);
        $('.filter-result-text').html(result_text);
        build_table(items);
        setup_current_filter_labels();
        loading_spinner.removeClass('active');
      })
      .catch((err) => {
        loading_spinner.removeClass('active');
        if (window.lodash.get(err, 'statusText') !== 'abort') {
          console.error(err);
        }
      });
  }

  $('#load-more').on('click', function () {
    $(this).addClass('loading');
    get_records(items.length).then(() => {
      $(this).removeClass('loading');
    });
  });

  /**
   * Modal options
   */

  // Promote as a shared function.
  window.SHAREDFUNCTIONS['add_custom_filter'] = add_custom_filter;

  //add the new filter in the filters list
  function add_custom_filter(name, type, query, labels, load_records = true) {
    query = query || current_filter.query;
    let ID = new Date().getTime() / 1000;
    current_filter = {
      ID,
      type,
      name: window.SHAREDFUNCTIONS.escapeHTML(name),
      query: JSON.parse(JSON.stringify(query)),
      labels: labels,
    };
    custom_filters.push(JSON.parse(JSON.stringify(current_filter)));

    let save_filter =
      $(`<a style="float:right" data-filter="${window.SHAREDFUNCTIONS.escapeHTML(ID.toString())}">
        ${window.SHAREDFUNCTIONS.escapeHTML(list_settings.translations.save)}
    </a>`).on('click', function () {
        $('#filter-name').val(name);
        $('#save-filter-modal').foundation('open');
        filter_to_save = ID;
      });
    let filterRow = $(
      `<label class='list-view ${window.SHAREDFUNCTIONS.escapeHTML(ID.toString())}'>`,
    )
      .append(
        `
      <input type="radio" name="view" value="custom_filter" data-id="${window.SHAREDFUNCTIONS.escapeHTML(ID.toString())}" class="js-list-view" checked autocomplete="off">
        ${window.SHAREDFUNCTIONS.escapeHTML(name)}
    `,
      )
      .append(save_filter);
    $('.custom-filters').append(filterRow);
    if (load_records) {
      get_records_for_current_filter();
    }
  }

  let get_custom_filter_search_query = () => {
    let search_query = [];
    let fields_filtered = window.lodash.uniq(
      new_filter_labels.map((f) => f.field),
    );
    fields_filtered.forEach((field) => {
      let type = window.lodash.get(
        list_settings,
        `post_type_settings.fields.${field}.type`,
      );
      let customComponent = document.querySelector(`[name="${field}"]`);

      if (customComponent) {
        let val = [];

        if (type === 'connection') {
          const allConnections = $(`#${field} .all-connections`);
          const withoutConnections = $(`#${field} .all-without-connections`);
          if (allConnections.prop('checked') === true) {
            search_query.push({ [field]: [ALL_ID] });
          } else if (withoutConnections.prop('checked') === true) {
            search_query.push({ [field]: [ALL_WITHOUT_ID] });
          } else {
            val = customComponent.value
              .filter((label) => !label.delete)
              .map((item) => item.id);
            search_query.push({
              [field]: adjust_search_query_filter_states(field, type, val),
            });
          }
        } else if (type === 'text' || type === 'communication_channel') {
          val = customComponent.value;

          switch (
            $(
              `#filter_by_text_comms_option_${field} .filter-by-text-comms-option:checked`,
            ).val()
          ) {
            case 'all-with-set-value': {
              val = '*';
              break;
            }
            case 'all-without-set-value': {
              val = null;
              break;
            }
            case 'all-with-filtered-value': {
              val = customComponent.value;
              break;
            }
            case 'all-without-filtered-value': {
              val = '-' + customComponent.value;
              break;
            }
          }

          search_query.push({ [field]: val !== null ? [val] : [] });
        } else {
          // Extract the value from each field type
          if (
            type === 'user_select' ||
            type === 'location' ||
            type === 'location_meta'
          ) {
            val = customComponent.value
              .filter((label) => !label.delete)
              .map((item) => item.id);
          } else if (type === 'multi_select' || type === 'tags') {
            val = customComponent.value.filter(
              (label) => label.charAt(0) !== '-',
            );
          } else {
            // For single selects or toggles
            val = customComponent.value ? [customComponent.value] : [];
          }

          if (val && val.length > 0) {
            if (type === 'location_meta' || type === 'location') {
              field = 'location_grid';
            }
            search_query.push({
              [field]: adjust_search_query_filter_states(field, type, val),
            });
          }
        }
      } else if (type === 'file_upload') {
        const selectedOption = $(
          `#${field}-options .filter-by-file-upload-option:checked`,
        ).val();
        if (selectedOption === 'all-with-files') {
          search_query.push({ [field]: ['*'] });
        } else if (selectedOption === 'all-without-files') {
          search_query.push({ [field]: [] });
        }
      } else if (type === 'date' || type === 'datetime') {
        let date = {};
        let start = $(`#${field}_start`).val();
        if (start) {
          date.start = start;
        }
        let end = $(`#${field}_end`).val();
        if (end) {
          date.end = end;
        }
        search_query.push({ [field]: date });
      } else {
        let options = [];
        $(`#${field}-options input:checked`).each(function () {
          options.push($(this).val());
        });
        if (options.length) {
          search_query.push({
            [field]: adjust_search_query_filter_states(field, type, options),
          });
        }
      }
    });
    search_query = {
      fields: search_query,
    };
    if (list_settings.post_type === 'contacts') {
      if ($('#combine_subassigned').is(':checked')) {
        let assigned_to = search_query.fields.filter((a) => a.assigned_to);
        let subassigned = search_query.fields.filter((a) => a.subassigned);
        search_query.fields = search_query.fields.filter((a) => {
          return !a.assigned_to && !a.subassigned;
        });
        search_query.fields.push([assigned_to[0], subassigned[0]]);
        search_query.combine = ['subassigned']; // to select checkbox in filter modal
      }
    }

    return search_query;
  };
  $('#confirm-filter-records').on('click', function () {
    let search_query = get_custom_filter_search_query();
    let filterName = $('#new-filter-name').val();
    reset_split_by_filters();
    add_custom_filter(
      filterName || 'Custom Filter',
      'custom-filter',
      search_query,
      new_filter_labels,
    );
    if (window.Foundation.MediaQuery.only('small')) {
      $('#tile-filters').addClass('collapsed');
    }
  });

  $(document).on('click', '.current-filter-label-button', function () {
    if (is_custom_filter_modal_visible()) {
      $(this).parent().toggleClass('current-filter-excluded');
    }
  });

  // Detect selected custom filter additions and alter shape accordingly
  new MutationObserver(function (mutation_list, observer) {
    if (
      is_custom_filter_modal_visible() &&
      mutation_list[0] &&
      $(mutation_list[0].target).attr('id') == 'selected-filters'
    ) {
      // Iterate over latest selected filters list
      $(mutation_list[0].target)
        .find('.current-filter')
        .each(function () {
          let filter_label = $(this);

          // Only add exclusion button, if required
          if (
            $(filter_label).find('.current-filter-label-button').length == 0 &&
            is_custom_filter_field_type_supported_for_exclusion(filter_label)
          ) {
            $(filter_label).append(
              `<span title="${window.SHAREDFUNCTIONS.escapeHTML(list_settings.translations.exclude_item)}" class="current-filter-label-button mdi mdi-minus-circle-multiple-outline"></span>`,
            );
          }
        });
    }
  }).observe($('#selected-filters').get(0), {
    attributes: true,
    childList: true,
    subtree: true,
  });

  function is_custom_filter_modal_visible() {
    return $('#filter-modal').is(':visible');
  }

  function is_custom_filter_field_type_supported_for_exclusion(filter_label) {
    let is_supported = false;

    // Attempt to locate corresponding field settings
    $.each(list_settings.post_type_settings.fields, function (id, field) {
      if (window.lodash.includes($(filter_label).attr('class'), id)) {
        // Determine if identified setting has supported field type
        is_supported = window.lodash.includes(
          [
            'connection',
            'user_select',
            'multi_select',
            'tags',
            'location',
            'location_meta',
            'key_select',
          ],
          field.type,
        );
      }
    });

    // Ensure wildcard (All) based filters are enforced, with exclusion option disabled
    if (window.lodash.includes(['*', '-*'], $(filter_label).data('id'))) {
      is_supported = false;
    }

    return is_supported;
  }

  function adjust_search_query_filter_states(field_id, field_type, filters) {
    // Adjust accordingly, by field type
    if (
      window.lodash.includes(
        [
          'connection',
          'user_select',
          'multi_select',
          'tags',
          'location',
          'location_meta',
          'key_select',
        ],
        field_type,
      ) ||
      !window.lodash.includes(
        [
          'date',
          'datetime',
          'boolean',
          'communication_channel',
          'text',
          'textarea',
          'array',
          'number',
          'task',
        ],
        field_type,
      )
    ) {
      // Start adjustment of sarch query filters
      let adjusted_filters = window.lodash.map(filters, function (value) {
        // Determine it's current exclusion state
        let excluded = $(
          '.current-filter.current-filter-excluded.' + field_id,
        ).filter(function () {
          return $(this).data('id') == value;
        });

        // Prefix exclusion flag, accordingly
        return (excluded.length > 0 ? '-' : '') + value;
      });
      return adjusted_filters;
    }

    // By default, return filters untouched!
    return filters;
  }

  function is_search_query_filter_label_excluded(filter, label) {
    let excluded = false;
    if (
      window.lodash.has(filter, 'query.fields') &&
      Array.isArray(filter.query.fields)
    ) {
      filter.query.fields.forEach((field) => {
        if (field[label.field]) {
          excluded = window.lodash.includes(field[label.field], '-' + label.id);
        }
      });
    }

    return excluded;
  }

  function toggle_all_connection_option(tabsPanel, without) {
    const allConnectionsElement = tabsPanel.find('.all-connections');
    const withoutConnectionsElement = tabsPanel.find(
      '.all-without-connections',
    );

    without
      ? allConnectionsElement.prop('checked', false)
      : withoutConnectionsElement.prop('checked', false);
  }

  function all_connections_click_handler(options) {
    const { without } = options || { without: false };
    const id = without ? ALL_WITHOUT_ID : ALL_ID;
    const tabsPanel = $(this).closest('.tabs-panel');
    const field = tabsPanel.length === 1 ? tabsPanel[0].id : '';
    const connectionElement = tabsPanel.find(`#${field}`);

    toggle_all_connection_option(tabsPanel, without);

    if ($(this).prop('checked') === true) {
      // disable connection field's input when this happens (no longer typeahead)
      connectionElement.prop('disabled', true);
      connectionElement.val('');
      // remove the current filters and leave anything in the typeahead as it is
      remove_all_filter_labels(field);
      const { newLabel, filterName } = create_label_all(
        field,
        without,
        id,
        list_settings,
      );
      selected_filters.append(
        `<span class="current-filter ${esc(field)}" data-id="${id}">${filterName}</span>`,
      );
      new_filter_labels.push(newLabel);
    } else {
      connectionElement.prop('disabled', false);
      remove_filter_labels(id, field);
    }
  }

  /* Label creation */

  function create_label_all(field, without, id, listSettings) {
    const fieldLabel = listSettings.post_type_settings.fields[field]
      ? listSettings.post_type_settings.fields[field].name
      : '';
    const allLabel = without
      ? esc(listSettings.translations.without)
      : esc(listSettings.translations.all);
    const filterName = `${esc(fieldLabel)}: ${allLabel}`;

    return {
      newLabel: {
        id: id,
        name: filterName,
        field: field,
      },
      filterName,
    };
  }

  function create_value_label(field, key, value) {
    return { newLabel: { id: key, name: value, field } };
  }

  // Promote as a shared function.
  window.SHAREDFUNCTIONS['create_name_value_label'] = create_name_value_label;
  function create_name_value_label(field, id, value, listSettings) {
    let name = window.lodash.get(
      listSettings,
      `post_type_settings.fields.${field}.name`,
      field,
    );
    const filterName = `${name}: ${value}`;
    return {
      newLabel: { id, name: filterName, field },
      name,
    };
  }

  function create_date_label(field, date, delimiter) {
    let field_name = window.lodash.get(
      list_settings,
      `post_type_settings.fields.${field}.name`,
      field,
    );

    let delimiter_label = list_settings.translations[`range_${delimiter}`];

    return {
      newLabel: {
        id: `${field}_${delimiter}`,
        name: `${field_name} ${delimiter_label}: ${date}`,
        field,
        date: date,
      },
      field_name: `${field_name} ${delimiter_label}`,
    };
  }

  function create_location_label(field, id, value, listSettings) {
    let name = window.lodash.get(
      listSettings,
      `post_type_settings.fields.location_grid.name`,
      field,
    );
    return {
      newLabel: { id, name: `${name}: ${value}`, field, type: 'location_grid' },
      name,
    };
  }

  $('.all-connections').on('click', all_connections_click_handler);

  function without_connections_handler() {
    all_connections_click_handler.call(this, { without: true });
  }

  $('.all-without-connections').on('click', without_connections_handler);

  $('.filter-by-text-comms-option').on('click', function (e) {
    handle_filter_by_text_comms({
      id: $(this).val(),
      field: $(this).data('field'),
    });
  });

  $('.filter-by-file-upload-option').on('click', function (e) {
    handle_filter_by_file_upload({
      id: $(this).val(),
      field: $(this).data('field'),
    });
  });

  function handle_filter_by_text_comms(options) {
    const { id, field } = options || { id: null, field: null };
    if (id && field) {
      // Adjust filter text field state accordingly, based on option selection.
      let filter_text_field = document.querySelector(`[name="${field}"]`);
      $(filter_text_field).prop(
        'disabled',
        ['all-with-set-value', 'all-without-set-value'].includes(id),
      );

      // Ensure duplicates are avoided.
      const existing_label = new_filter_labels.find(
        (label) => label['id'] === id && label['field'] === field,
      );
      if (existing_label === undefined) {
        // Identify stale labels to be deleted.
        let removed_old_filter_labels = [];
        new_filter_labels.forEach((label) => {
          if (label['field'] === field) {
            if (!(label['id'] === id)) {
              removed_old_filter_labels.push(label);
            }
          }
        });

        // Remove stale labels, if detected.
        if (removed_old_filter_labels.length > 0) {
          new_filter_labels = new_filter_labels.filter((existing_label) => {
            let filtered = false;
            removed_old_filter_labels.forEach((stale_label) => {
              if (
                existing_label['id'] !== stale_label['id'] &&
                existing_label['name'] !== stale_label['name'] &&
                existing_label['field'] !== stale_label['field']
              ) {
                filtered = true;
              }
            });

            return filtered;
          });

          // Remove associated ui labels.
          removed_old_filter_labels.forEach((label) => {
            $(selected_filters)
              .find(
                `.current-filter[data-id="${label['id']}"].${label['field']}`,
              )
              .remove();
          });
        }

        // Create new generic filter label.
        let { newLabel, filterName } = create_label_all(
          field,
          ['all-without-set-value', 'all-without-filtered-value'].includes(id),
          id,
          list_settings,
        );

        // Adjust label to reflect filtered text.
        if (
          ['all-with-filtered-value', 'all-without-filtered-value'].includes(id)
        ) {
          let filtered_value = document.querySelector(
            `dt-multi-text#${field}, dt-text#${field}`,
          ).value;
          if (Array.isArray(filtered_value)) {
            filtered_value = filtered_value
              .filter((label) => label.value.length > 0)
              .map((item) => item.value);
          }
          newLabel['name'] =
            filterName = `${esc(list_settings.post_type_settings.fields[field] ? list_settings.post_type_settings.fields[field].name : '')}: ${esc(filtered_value)}`;
        }

        selected_filters.append(
          `<span class="current-filter ${esc(field)}" data-id="${id}">${filterName}</span>`,
        );
        new_filter_labels.push(newLabel);
      }
    }
  }

  function handle_filter_by_file_upload(options) {
    const { id, field } = options || { id: null, field: null };
    if (!id || !field) {
      return;
    }

    const without = id === 'all-without-files';
    const { newLabel, filterName } = create_label_all(
      field,
      without,
      id,
      list_settings,
    );

    // Replace existing label for this file_upload field.
    new_filter_labels = new_filter_labels.filter(
      (label) => label.field !== field,
    );
    $(selected_filters).find(`.current-filter.${field}`).remove();
  }
  // attach .on('change') to all dt-* fields
  $(document).on(
    'change',
    'dt-location, dt-toggle, dt-tags, dt-date, dt-users-connection, dt-connection, dt-single-select, dt-multi-select, dt-multi-select-button-group, dt-multi-text, dt-text',
    function (e) {
      const element = e.target;
      const tagName = element.tagName.toLowerCase();

      let val = [];
      const isConnectionLike =
        tagName === 'dt-connection' ||
        tagName === 'dt-users-connection' ||
        tagName === 'dt-location';
      const isMultiSelectLike =
        tagName === 'dt-multi-select' ||
        tagName === 'dt-multi-select-button-group' ||
        tagName === 'dt-tags';

      // If the element creates multiple separate items (connections or multi-selects)
      if (isConnectionLike || isMultiSelectLike) {
        if (isConnectionLike) {
          // Get array of values without 'delete' property
          val = Array.isArray(e.target.value)
            ? e.target.value.filter((label) => !label.delete)
            : [];
        } else {
          // Get array of values for multi-selects and tags (filtering out negative '-' deletions)
          const rawVal = Array.isArray(e.target.value) ? e.target.value : [];
          val = rawVal
            .filter((id) => typeof id === 'string' && id.charAt(0) !== '-')
            .map((id) => {
              const selectedOption = Array.from(e.target.options || []).find(
                (option) => option.id === id,
              );
              return {
                id: id,
                label: selectedOption ? selectedOption.label : id,
              };
            });
        }

        const fieldName = e.target.name;

        // Find labels that are no longer in the component's value
        const labelsToRemove = new_filter_labels.filter(
          (label) =>
            label.field === fieldName &&
            !val.some((item) => item.id === label.id),
        );

        labelsToRemove.forEach((label) => {
          // Remove the specific span
          $(
            `.current-filter[data-id="${window.SHAREDFUNCTIONS.escapeHTML(label.id)}"].${window.SHAREDFUNCTIONS.escapeHTML(fieldName)}`,
          ).remove();
          new_filter_labels = new_filter_labels.filter(
            (existingLabel) =>
              existingLabel.id !== label.id ||
              existingLabel.field !== fieldName,
          );
        });

        // Loop through newly added labels, creating one label for each value
        val.forEach((item) => {
          const exists = new_filter_labels.some(
            (label) => label.field === fieldName && label.id === item.id,
          );

          if (!exists) {
            // Create correct label based on the tag
            let newLabel, displayLabel;

            if (tagName === 'dt-location') {
              const locLabel = create_location_label(
                fieldName,
                item.id,
                item.label,
                list_settings,
              );
              newLabel = locLabel.newLabel;
              displayLabel = locLabel.newLabel.name;
            } else if (isMultiSelectLike) {
              const nameValLabel = create_name_value_label(
                fieldName,
                item.id,
                item.label,
                list_settings,
              );
              newLabel = nameValLabel.newLabel;
              displayLabel = nameValLabel.newLabel.name; // prefixes with field name
            } else {
              const valLabel = create_value_label(
                fieldName,
                item.id,
                item.label,
              );
              newLabel = valLabel.newLabel;
              displayLabel = item.label;
            }

            selected_filters.append(
              `<span class="current-filter ${window.SHAREDFUNCTIONS.escapeHTML(fieldName)}" data-id="${window.SHAREDFUNCTIONS.escapeHTML(item.id)}">${window.SHAREDFUNCTIONS.escapeHTML(displayLabel)}</span>`,
            );
            new_filter_labels.push(newLabel);
          }
        });
      } else if (tagName == 'dt-date') {
        // Else, if the element is a date
        val = e.target.value;

        const fieldName = e.target.name || '';
        const delimiter = fieldName.endsWith('_start')
          ? 'start'
          : fieldName.endsWith('_end')
            ? 'end'
            : '';
        const id = fieldName.replace(/_(start|end)$/, '');

        const { newLabel, field_name } = create_date_label(id, val, delimiter);
        remove_all_filter_labels(fieldName);

        if (val) {
          selected_filters.append(
            `<span class="current-filter ${window.SHAREDFUNCTIONS.escapeHTML(newLabel.id)}" data-id="${window.SHAREDFUNCTIONS.escapeHTML(newLabel.id)}">${window.SHAREDFUNCTIONS.escapeHTML(field_name)}: ${window.SHAREDFUNCTIONS.escapeHTML(val)}</span>`,
          );
          new_filter_labels.push(newLabel);
        }
      } else {
        // All other standard elements (e.g., dt-text, dt-single-select)
        val = e.target.value;

        if (!val || val.length == 0) {
          val = false;
        }

        const { newLabel, name } = create_name_value_label(
          e.target.name,
          e.target.name,
          val,
          list_settings,
        );

        remove_all_filter_labels(e.target.name);
        if (val) {
          selected_filters.append(
            `<span class="current-filter ${window.SHAREDFUNCTIONS.escapeHTML(e.target.name)}" data-id="${window.SHAREDFUNCTIONS.escapeHTML(e.target.name)}">${window.SHAREDFUNCTIONS.escapeHTML(name)}: ${window.SHAREDFUNCTIONS.escapeHTML(val)}</span>`,
          );
          new_filter_labels.push(newLabel);
        }
      }
    },
  );

  const remove_filter_labels = (id, field_key) => {
    $(`.current-filter[data-id="${id}"].${field_key}`).remove();
    window.lodash.pullAllBy(new_filter_labels, [{ id: id }], 'id');
  };

  const remove_all_filter_labels = (field_key) => {
    // get all id's for this field_key
    let ids = [];
    document
      .querySelectorAll(`.current-filter.${field_key}`)
      .forEach((element) => {
        ids.push(element.dataset.id);
      });
    ids.forEach((id) => remove_filter_labels(id, field_key));
  };

  /**
   * Location
   */
  $('#mapbox-clear-autocomplete').click('input', function () {
    delete window.location_data;
  });

  /*
   * Setup filter box
   */
  $('#filter-modal').on('open.zf.reveal', function () {
    new_filter_labels = [];
    $('#new-filter-name').val('');
    $('#filter-modal input:checked').each(function () {
      $(this).prop('checked', false);
    });
    $('#filter-modal input:disabled').each(function () {
      $(this).prop('disabled', false);
    });
    selected_filters.empty();
  });
  // On close, reset all field values
  $('#filter-modal').on('closed.zf.reveal', function () {
    $(this)
      .find(
        'dt-location, dt-toggle, dt-tags, dt-date, dt-users-connection, dt-connection, dt-single-select, dt-multi-select, dt-multi-select-button-group, dt-multi-text, dt-text',
      )
      .each(function () {
        this.reset();
      });

    // hide edit filters again & remove associated filter-id
    $('#save-filter-edits').hide().removeData('filter-id');
    $('#confirm-filter-records').show();
  });

  var clicked;
  $(document).mousedown(function (e) {
    // The latest element clicked
    clicked = $(e.target);
  });
  // when 'clicked == null' on blur, we know it was not caused by a click
  // but maybe by pressing the tab key
  $(document).mouseup(function (e) {
    clicked = null;
  });

  function edit_saved_filter(filter) {
    $('#filter-modal').foundation('open');

    let connectionTypeKeys = [
      ...list_settings.post_type_settings.connection_types,
      'location_grid',
    ];

    // Helper function to handle the new dt-* component state updates
    const appendValueToDtComponent = (field, itemObject) => {
      const dtComponent = document.querySelector(`[name="${field}"]`);

      if (dtComponent) {
        // Assuming the dt-* component's value expects an array of selected objects
        const currentValue = Array.isArray(dtComponent.value)
          ? dtComponent.value
          : [];
        dtComponent.value = [...currentValue, itemObject];
      } else {
        console.warn(`Could not find dt-* component for field: ${field}`);
      }
    };

    filter.labels.forEach((label) => {
      // Determine exclusion status
      let excluded_class = is_search_query_filter_label_excluded(filter, label)
        ? 'current-filter-excluded'
        : '';

      let type = window.lodash.get(
        list_settings,
        `post_type_settings.fields.${label.field}.type`,
      );

      const displayField = type === 'date' ? label.id : label.field;

      // Proceed with displaying of filter modal
      selected_filters.append(
        `<span class="current-filter ${excluded_class} ${window.SHAREDFUNCTIONS.escapeHTML(displayField)}" data-id="${window.SHAREDFUNCTIONS.escapeHTML(label.id)}">${window.SHAREDFUNCTIONS.escapeHTML(label.name)}</span>`,
      );

      if (
        type === 'key_select' ||
        type === 'boolean' ||
        type === 'file_upload'
      ) {
        // Find all checkboxes for this specific field
        $(
          `#filter-modal #${label.field}-options input[data-field="${label.field}"]`,
        )
          // Filter down to the one where the JS value exactly matches label.id
          .filter(function () {
            return this.value === label.id;
          })
          .prop('checked', true);
      } else if (type === 'date' || type === 'datetime') {
        const dateComponent = document.querySelector(`[name="${label.id}"]`);
        if (dateComponent) {
          dateComponent.value = label.date;
        }
      } else if (connectionTypeKeys.includes(label.field)) {
        if (label.id === '*') {
          const fieldAllConnectionsElement = document.querySelector(
            `#filter-modal #${label.field} .all-connections`,
          );
          const boundAllConnectionsClickHandler =
            all_connections_click_handler.bind(fieldAllConnectionsElement);
          $(fieldAllConnectionsElement).prop('checked', true);
          boundAllConnectionsClickHandler();
        } else {
          appendValueToDtComponent(label.field, {
            id: label.id,
            label: label.name,
          });
        }
      } else if (type === 'multi_select' || type === 'tags') {
        appendValueToDtComponent(label.field, label.id);
      } else if (type === 'user_select') {
        appendValueToDtComponent(label.field, {
          label: label.name,
          id: label.id,
        });
      } else if (
        type === 'text' ||
        type === 'communication_channel' ||
        type === 'textarea' ||
        type === 'number'
      ) {
        const dtComponent = document.querySelector(`[name="${label.field}"]`);

        let textValue = '';
        let queryArray = [];

        if (filter.query && filter.query.fields) {
          const queryField = filter.query.fields.find(
            (f) => f[label.field] !== undefined,
          );
          if (queryField && queryField[label.field]) {
            queryArray = queryField[label.field];
          }
        } else if (filter.query && filter.query[label.field]) {
          queryArray = filter.query[label.field];
        }

        // Handle '-' and '*' signs
        if (queryArray && queryArray.length > 0) {
          let queryVal = queryArray[0];
          if (typeof qVal === 'string') {
            if (queryVal.startsWith('-') && queryVal !== '-*') {
              textValue = queryVal.substring(1);
            } else if (queryVal !== '*' && queryVal !== '-*') {
              textValue = queryVal;
            }
          }
        }

        if (dtComponent) {
          dtComponent.value = textValue;

          if (
            ['all-with-set-value', 'all-without-set-value'].includes(label.id)
          ) {
            dtComponent.disabled = true;
          } else {
            dtComponent.disabled = false;
          }
        }

        if (type === 'text' || type === 'communication_channel') {
          $(
            `#filter-modal #filter_by_text_comms_option_${label.field} input[data-field="${label.field}"]`,
          )
            .filter(function () {
              return this.value === label.id;
            })
            .prop('checked', true);
        }
      }
    });

    new_filter_labels = filter.labels;
    (filter.query.combine || []).forEach((c) => {
      $(`#combine_${c}`).prop('checked', true);
    });
    $('#new-filter-name').val(filter.name);
    $('#confirm-filter-records').hide();
    $('#save-filter-edits').data('filter-id', filter.ID).show();
  }

  $('#save-filter-edits').on('click', function () {
    let search_query = get_custom_filter_search_query();
    let filter_id = $('#save-filter-edits').data('filter-id');
    let filter = window.lodash.find(list_settings.filters.filters, {
      ID: filter_id,
    });
    filter.name = $('#new-filter-name').val();
    $(`.filter-list-name[data-filter="${filter_id}"]`).text(filter.name);
    filter.query = search_query;
    filter.labels = new_filter_labels;
    window.API.save_filters(list_settings.post_type, filter);
    get_records_for_current_filter();
  });

  $('#filter-tabs').on('change.zf.tabs', function (a, b) {
    let field = $(b).data('field');
    const panel = $(`#${field}.tabs-panel`);
    $(`.tabs-panel`).removeClass('is-active');
    $(panel).addClass('is-active');
  });

  //watch all other checkboxes
  $('#filter-modal .key_select_options input').on('change', function () {
    let field_key = $(this).data('field');
    let option_id = $(this).val();
    if ($(this).is(':checked')) {
      let field_options = window.lodash.get(
        list_settings,
        `post_type_settings.fields.${field_key}.default`,
      );
      let option_name = field_options[option_id]
        ? field_options[option_id]['label']
        : '';
      const { name, newLabel } = create_name_value_label(
        field_key,
        $(this).val(),
        option_name,
        list_settings,
      );
      new_filter_labels.push(newLabel);
      selected_filters.append(
        `<span class="current-filter ${window.SHAREDFUNCTIONS.escapeHTML(field_key)}" data-id="${window.SHAREDFUNCTIONS.escapeHTML(option_id)}">${window.SHAREDFUNCTIONS.escapeHTML(name)}:${window.SHAREDFUNCTIONS.escapeHTML(option_name)}</span>`,
      );
    } else {
      $(`.current-filter[data-id="${$(this).val()}"].${field_key}`).remove();
      window.lodash.pullAllBy(new_filter_labels, [{ id: option_id }], 'id');
    }
  });
  //watch bool checkboxes
  $('#filter-modal .boolean_options input').on('change', function () {
    let field_key = $(this).data('field');
    let option_id = $(this).val();
    let label = $(this).data('label');
    if ($(this).is(':checked')) {
      const { name, newLabel } = create_name_value_label(
        field_key,
        $(this).val(),
        label,
        list_settings,
      );
      new_filter_labels.push(newLabel);
      selected_filters.append(
        `<span class="current-filter ${window.SHAREDFUNCTIONS.escapeHTML(field_key)}" data-id="${window.SHAREDFUNCTIONS.escapeHTML(option_id)}">${window.SHAREDFUNCTIONS.escapeHTML(name)}:${window.SHAREDFUNCTIONS.escapeHTML(label)}</span>`,
      );
    } else {
      $(`.current-filter[data-id="${$(this).val()}"].${field_key}`).remove();
      window.lodash.pullAllBy(new_filter_labels, [{ id: option_id }], 'id');
    }
  });

  //save the filter in the user meta
  $(`#confirm-filter-save`).on('click', function () {
    let filterName = $('#filter-name').val();
    let filter = window.lodash.find(custom_filters, { ID: filter_to_save });
    filter.name = window.SHAREDFUNCTIONS.escapeHTML(filterName);
    filter.tab = 'custom';
    if (filter.query) {
      list_settings.filters.filters.push(filter);
      window.API.save_filters(list_settings.post_type, filter)
        .then(() => {
          $(`.custom-filters [class*="list-view ${filter_to_save}`).remove();
          setup_filters();
          let active_tab = $('.accordion-item.is-active ').data('id');
          if (active_tab !== 'custom') {
            $(`#list-filter-tabs [data-id='custom'] a`).click();
          }
          $(
            `input[name="view"][value="saved-filters"][data-id='${filter_to_save}']`,
          ).prop('checked', true);
          get_records_for_current_filter();
          $('#filter-name').val('');
        })
        .catch((err) => {
          console.error(err);
        });
    }
  });

  //delete a filter
  $(`#confirm-filter-delete`).on('click', function () {
    let filter = window.lodash.find(list_settings.filters.filters, {
      ID: filter_to_delete,
    });
    if (filter && (filter.visible === true || filter.visible === '1')) {
      filter.visible = false;
      window.API.save_filters(list_settings.post_type, filter)
        .then(() => {
          window.lodash.pullAllBy(
            list_settings.filters.filters,
            [{ ID: filter_to_delete }],
            'ID',
          );
          setup_filters();
          $(`#list-filter-tabs [data-id='custom'] a`).click();
        })
        .catch((err) => {
          console.error(err);
        });
    } else {
      window.API.delete_filter(list_settings.post_type, filter_to_delete)
        .then(() => {
          window.lodash.pullAllBy(
            list_settings.filters.filters,
            [{ ID: filter_to_delete }],
            'ID',
          );
          setup_filters();
          check_first_filter();
          get_records_for_current_filter();
        })
        .catch((err) => {
          console.error(err);
        });
    }
  });

  $('#advanced_search').on('click', function () {
    $('#advanced_search_picker').toggle();
  });

  $('#advanced_search_mobile').on('click', function () {
    $('#advanced_search_picker_mobile').toggle();
  });

  $('#advanced_search_reset').on('click', function () {
    let fields_to_search = [];
    window.SHAREDFUNCTIONS.save_json_cookie(
      'fields_to_search',
      fields_to_search,
      list_settings.post_type,
    );

    //clear all checkboxes
    $('#advanced_search_picker ul li input:checked').each(function (index) {
      $(this).prop('checked', false);
    });
    $('#search').click();
  });

  $('#advanced_search_reset_mobile').on('click', function () {
    let fields_to_search = [];
    window.SHAREDFUNCTIONS.save_json_cookie(
      'fields_to_search',
      fields_to_search,
      list_settings.post_type,
    );

    //clear all checkboxes
    $('#advanced_search_picker_mobile ul li input:checked').each(
      function (index) {
        $(this).prop('checked', false);
      },
    );
    $('#search-mobile').click();
  });

  $('#save_advanced_search_choices').on('click', function () {
    let fields_to_search = [];
    $('#advanced_search_picker ul li input:checked').each(function (index) {
      fields_to_search.push($(this).val());
    });
    window.SHAREDFUNCTIONS.save_json_cookie(
      'fields_to_search',
      fields_to_search,
      list_settings.post_type,
    );
    if ($('#search-query').val() !== '') {
      $('#search').click();
    } else {
      $('#advanced_search_picker').hide();
    }
  });

  $('#save_advanced_search_choices_mobile').on('click', function () {
    let fields_to_search = [];
    $('#advanced_search_picker_mobile ul li input:checked').each(
      function (index) {
        fields_to_search.push($(this).val());
      },
    );
    window.SHAREDFUNCTIONS.save_json_cookie(
      'fields_to_search',
      fields_to_search,
      list_settings.post_type,
    );
    if ($('#search-query-mobile').val() !== '') {
      $('#search-mobile').click();
    } else {
      $('#advanced_search_picker_mobile').hide();
    }
  });
  $('#search').on('click', function () {
    let searchText = $('#search-query').val();
    let fieldsToSearch = [];
    $('#advanced_search_picker ul li input:checked').each(function (index) {
      fieldsToSearch.push($(this).val());
    });
    window.SHAREDFUNCTIONS.save_json_cookie(
      'fields_to_search',
      fieldsToSearch,
      list_settings.post_type,
    );

    if (fieldsToSearch.length > 0) {
      $('.advancedSearch-count')
        .text(fieldsToSearch.length)
        .css('display', 'inline-block');
    } else {
      $('.advancedSearch-count').text('fields_to_search.length').hide();
    }

    let query = { text: searchText };
    query.sort = current_filter?.query?.sort || '-post_date';

    if (fieldsToSearch.length !== 0) {
      query.fields_to_search = fieldsToSearch;
    }

    let labels = [{ id: 'search', name: searchText, field: 'search' }];
    add_custom_filter(searchText, 'search', query, labels);

    $('#advanced_search_picker').hide();
  });

  $('#search-mobile').on('click', function () {
    let searchText = window.SHAREDFUNCTIONS.escapeHTML(
      $('#search-query-mobile').val(),
    );
    let fieldsToSearch = [];
    $('#advanced_search_picker_mobile ul li input:checked').each(
      function (index) {
        fieldsToSearch.push($(this).val());
      },
    );
    window.SHAREDFUNCTIONS.save_json_cookie(
      'fields_to_search',
      fieldsToSearch,
      list_settings.post_type,
    );

    if (fieldsToSearch.length > 0) {
      $('.advancedSearch-count')
        .text(fieldsToSearch.length)
        .css('display', 'inline-block');
    } else {
      $('.advancedSearch-count').text('fields_to_search.length').hide();
    }

    let query = { text: searchText };

    if (fieldsToSearch.length !== 0) {
      query.fields_to_search = fieldsToSearch;
    }

    let labels = [{ id: 'search', name: searchText, field: 'search' }];
    add_custom_filter(searchText, 'search', query, labels);

    $('#advanced_search_picker_mobile').hide();
  });

  $('.search-input--desktop').on('keyup', function (e) {
    if (e.keyCode === 13) {
      $('#search').trigger('click');
    }
  });

  $('.search-input--mobile').on('keyup', function (e) {
    if (e.keyCode === 13) {
      $('#search-mobile').trigger('click');
    }
  });

  clearSearchButton.on('click', function () {
    $('.search-input').val('');
  });

  // ============================================
  // Extend DT_List with additional state and functions
  // (now that all functions are defined)
  // ============================================
  Object.defineProperties(window.DT_List, {
    fields_to_show_in_table: {
      get: function () {
        return fields_to_show_in_table;
      },
    },
    new_filter_labels: {
      get: function () {
        return new_filter_labels;
      },
    },
    bulkEditSelectedFields: {
      get: function () {
        return window.DT_List.bulk?.getBulkEditSelectedFields
          ? window.DT_List.bulk.getBulkEditSelectedFields()
          : [];
      },
    },
    current_user_id: {
      get: function () {
        return current_user_id;
      },
    },
  });

  // Add function references (these must be added after functions are defined)
  window.DT_List.get_records_for_current_filter =
    get_records_for_current_filter;
  window.DT_List.add_custom_filter = add_custom_filter;
  window.DT_List.setup_current_filter_labels = setup_current_filter_labels;
  window.DT_List.reset_split_by_filters = reset_split_by_filters;
  window.DT_List.build_table = build_table;
  window.DT_List.update_url_query = update_url_query;

  /***
   * Favorite from List
   */
  function favorite_edit_event() {
    $('svg.icon-star').on('click', function (e) {
      e.stopImmediatePropagation();
      let post_id = this.dataset.id;
      let favoritedValue;
      if ($(this).hasClass('selected')) {
        favoritedValue = false;
      } else {
        favoritedValue = true;
      }
      window.API.update_post(list_settings.post_type, post_id, {
        favorite: favoritedValue,
      }).then((new_post) => {
        $(this).toggleClass('selected');
      });
    });
  }

  /**
   * Split By Feature — delegate so clicks work after setup_filters() replaces
   * #list-filter-tabs HTML (e.g. when get_filter_counts refreshes filter payload).
   */
  $(document).on('click', '#split_by_current_filter_button', function () {
    refresh_split_by_view();
  });

  $(document).on('change', '.js-list-view-split-by', () => {
    get_records_for_current_filter(current_filter);
  });

  function refresh_split_by_view() {
    let field_id = $('#split_by_current_filter_select').val();
    if (!field_id) {
      return;
    }

    const split_by_current_filter_button = $('#split_by_current_filter_button');
    const split_by_accordion = $('.split-by-current-filter-accordion');
    const split_by_results = $('#split_by_current_filter_results');
    const split_by_no_results_msg = $(
      '#split_by_current_filter_no_results_msg',
    );

    $(split_by_current_filter_button).addClass('loading');

    $(split_by_no_results_msg).fadeOut('fast');

    $(split_by_results).slideUp('fast', function () {
      let split_by_filters =
        current_filter.query !== undefined ? current_filter.query : [];

      // Create filter for all available field options.
      let default_options_filters = JSON.parse(
        JSON.stringify(split_by_filters),
      );

      // First, always fetch all available options for given field_id.
      window.API.split_by(
        list_settings.post_type,
        field_id,
        default_options_filters,
      ).then(function (default_options) {
        $(split_by_current_filter_button).removeClass('loading');
        let summary_displayed = false;
        if (default_options && default_options.length > 0) {
          let html = '';

          // Iterate over default options and highlight selected filters.
          $.each(default_options, function (idx, result) {
            if (result['value']) {
              summary_displayed = true;
              let option_id = result['value'];
              let option_id_label =
                result['label'] !== '' ? result['label'] : result['value'];

              // Determine if option should be selected.
              let option_selected = false;
              if (split_by_filters['fields']) {
                if (
                  split_by_filters['fields'].filter(
                    (option) =>
                      option[field_id] !== undefined &&
                      option[field_id].includes(option_id),
                  ).length > 0
                ) {
                  option_selected = true;
                }
              }

              html += `
                    <label class="list-view">
                      <input class="js-list-view-split-by" type="radio" name="split_by_list_view" ${option_selected ? 'checked' : ''} value="${window.SHAREDFUNCTIONS.escapeHTML(option_id)}" data-field_id="${window.SHAREDFUNCTIONS.escapeHTML(field_id)}" data-field_option_id="${window.SHAREDFUNCTIONS.escapeHTML(option_id)}" data-field_option_label="${window.SHAREDFUNCTIONS.escapeHTML(option_id_label)}" autocomplete="off">
                      <span class="list-view__text">${window.SHAREDFUNCTIONS.escapeHTML(option_id_label)}</span>
                      <span class="list-view__count js-list-view-count" data-value="${window.SHAREDFUNCTIONS.escapeHTML(option_id)}">${window.SHAREDFUNCTIONS.escapeHTML(result['count'])}</span>
                    </label>
                    `;
            }
          });

          $(split_by_accordion).slideDown('fast', function () {
            $(split_by_results).html(html);
            $(split_by_results).slideDown('fast');
          });
        }

        if (!summary_displayed) {
          $(split_by_accordion).slideUp('fast', function () {
            $(split_by_no_results_msg).fadeIn('fast');
          });
        }
      });
    });
  }

  function apply_split_by_filters(filter, field_id, option_id, option_label) {
    if (filter && field_id && option_id && option_label) {
      // Fetch field and option display labels.
      let field_id_label = field_id;
      let option_id_label = option_label;
      let setting_fields = window.list_settings.post_type_settings.fields;
      if (setting_fields[field_id] && setting_fields[field_id]['name']) {
        field_id_label = setting_fields[field_id]['name'];
      }

      // Ensure a fields array is available.
      if (filter['query']['fields'] === undefined) {
        filter['query']['fields'] = [];
      }

      // Ensure to enforce toggling of options of the same field, instead of tacking onto any previous selections.
      filter['query']['fields'] = filter['query']['fields'].filter(
        (field) => field[field_id] === undefined,
      );
      filter['labels'] = filter['labels'].filter((label) => {
        if (label['id'] && label['field']) {
          return label['id'] !== option_id && label['field'] !== field_id;
        }

        return true;
      });

      // Add new label.
      filter['labels'].push({
        id: option_id,
        field: field_id,
        name: `${window.SHAREDFUNCTIONS.escapeHTML(field_id_label)}: ${window.SHAREDFUNCTIONS.escapeHTML(option_id_label)}`,
      });

      let query_field_obj = {};
      query_field_obj[field_id] = option_id !== 'NULL' ? [option_id] : [];
      if (filter['query']['fields'].push !== undefined) {
        filter['query']['fields'].push(query_field_obj);
      }
    }

    return filter;
  }

  // Promote as a shared function.
  window.SHAREDFUNCTIONS['reset_split_by_filters'] = reset_split_by_filters;
  function reset_split_by_filters() {
    let split_by_filter_select = $('#split_by_current_filter_select');
    if (current_filter && current_filter['query']['fields'] !== undefined) {
      let field_id = $(split_by_filter_select).val();
      $.each(current_filter['query']['fields'], function (field_idx, field) {
        // Identify selected split by filters to be removed from main current global filter.
        if (field[field_id] !== undefined) {
          $('.current-filter-list.' + field_id)
            .find('.current-filter-list-close')
            .click();
        }
      });
    }

    // Clear down split-by area.
    $(split_by_filter_select).val('');
    $('#split_by_current_filter_no_results_msg').fadeOut('fast');
    $('.split-by-current-filter-accordion').slideUp('fast', function () {});
  }
})(window.jQuery, window.list_settings, window.Foundation);
