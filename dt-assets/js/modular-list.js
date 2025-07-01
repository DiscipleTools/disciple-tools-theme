'use strict';
(function ($, list_settings, Foundation) {
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
              // Do we have a match...?
              let field = val[field_details.id];
              if (field) {
                let field_values = [];
                $.each(field, function (field_idx, field_val) {
                  if (id !== field_val) {
                    field_values.push(field_val);
                  }
                });

                // Update new fields array, if still populated
                if (field_values.length > 0) {
                  let updated_field = {};
                  updated_field[field_details.id] = field_values;
                  fields.push(updated_field);
                }
              } else {
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
        </a>
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
          $(`.delete-filter-name`).html(filter.name);
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

        // Proceed with displaying of filter label
        html += `<span class="current-filter-list ${excluded_class} ${window.SHAREDFUNCTIONS.escapeHTML(label.field)}">${window.SHAREDFUNCTIONS.escapeHTML(label.name)}`;

        if (label.id && label.field && label.name) {
          html += `<span class="current-filter-list-close">x</span>`;
        } else {
          html += `&nbsp;`;
        }

        html += `</span>`;
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

  $('#choose_fields_to_show_in_table').on('click', function () {
    $('#list_column_picker').toggle();
  });
  $('#save_column_choices').on('click', function () {
    let new_selected = [];
    $('#list_column_picker input:checked').each((index, elem) => {
      new_selected.push($(elem).val());
    });
    fields_to_show_in_table = window.lodash.intersection(
      fields_to_show_in_table,
      new_selected,
    ); // remove unchecked
    fields_to_show_in_table = window.lodash.uniq(
      window.lodash.union(fields_to_show_in_table, new_selected),
    );
    window.SHAREDFUNCTIONS.save_json_cookie(
      'fields_to_show_in_table',
      fields_to_show_in_table,
      list_settings.post_type,
    );
    window.location.reload();
  });
  $('#reset_column_choices').on('click', function () {
    fields_to_show_in_table = [];
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
        let values = [];
        if (field_key === 'name') {
          if (mobile) {
            return;
          }
          values_html = `<a href="${window.SHAREDFUNCTIONS.escapeHTML(record.permalink)}" title="${window.SHAREDFUNCTIONS.escapeHTML(record.post_title)}">${window.SHAREDFUNCTIONS.escapeHTML(record.post_title)}</a>`;
        } else if (list_settings.post_type_settings.fields[field_key]) {
          let field_settings =
            list_settings.post_type_settings.fields[field_key];
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
            } else if (field_settings.type === 'image') {
              values = [`<img src='${field_value.thumb}' class='list-image'>`];
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
            field_value === undefined &&
            field_settings.type === 'boolean' &&
            field_settings.default === true
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
        if ($(window).width() < mobile_breakpoint) {
          row_fields_html += `
            <td>
              <div class="mobile-list-field-name">
                <ul>
                ${window.SHAREDFUNCTIONS.escapeHTML(window.lodash.get(list_settings, `post_type_settings.fields[${field_key}].name`, field_key))}
                </ul>
              </div>
              <div class="mobile-list-field-value">
                <ul style="line-height:20px" dir="auto">
                  ${values.join(', ')}
                </ul>
              </div>

            </td>
          `;
        } else {
          //this looks for the star SVG from the favorited fields and changes the value to a checkmark like other boolean fields to be used in the title element on desktop lists.
          if (
            values[0] ===
            `<svg class='icon-star selected' viewBox="0 0 32 32" data-id=${record.ID}><use xlink:href="${window.wpApiShare.template_dir}/dt-assets/images/star.svg#star"></use></svg>`
          ) {
            values[0] = '&#9734;';
          }
          if (
            values[0] ===
            `<svg class='icon-star' viewBox="0 0 32 32" data-id=${record.ID}><use xlink:href="${window.wpApiShare.template_dir}/dt-assets/images/star.svg#star"></use></svg>`
          ) {
            values[0] = '&#9733;';
          }
          let title = values.join(', ');
          //exclude html tags from title
          if (title.includes('<')) {
            title = '';
          }
          let tmp_html = `
            <td dir="auto" data-id="${field_key}" title="${title}">
              <ul>
                ${values_html}
              </ul>
            </td>
          `;

          if (field_key === 'favorite') {
            row_fields_html = tmp_html + row_fields_html;
          } else {
            row_fields_html += tmp_html;
          }
        }
      });

      if (mobile) {
        table_rows += `<tr data-link="${window.SHAREDFUNCTIONS.escapeHTML(record.permalink)}">
          <td class="bulk_edit_checkbox">
              <input class="bulk_edit_checkbox" type="checkbox" name="bulk_edit_id" value="${record.ID}">
          </td>
          <td>
              <div class="mobile-list-field-name">${index + 1}.</div>
              <div class="mobile-list-field-value">
                  <a href="${window.SHAREDFUNCTIONS.escapeHTML(record.permalink)}">${window.SHAREDFUNCTIONS.escapeHTML(record.post_title)}</a>
              </div>
          </td>
          ${row_fields_html}
        `;
      } else {
        table_rows += `<tr class="dnd-moved" data-link="${window.SHAREDFUNCTIONS.escapeHTML(record.permalink)}">
          <td class="bulk_edit_checkbox" ><input type="checkbox" name="bulk_edit_id" value="${record.ID}"></td>
          <td style="white-space: nowrap" data-id="index" >${index + 1}.</td>
          ${row_fields_html}
        `;
      }
    });
    if (records.length === 0) {
      table_rows = `<tr><td colspan="10">${window.SHAREDFUNCTIONS.escapeHTML(list_settings.translations.empty_list)}</td></tr>`;
    }

    let table_html = `
      ${table_rows}
    `;
    $('#table-content').html(table_html);
    bulk_edit_checkbox_event();
    favorite_edit_event();
  };

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
      if (type === 'connection') {
        const allConnections = $(`#${field} .all-connections`);
        const withoutConnections = $(`#${field} .all-without-connections`);
        if (allConnections.prop('checked') === true) {
          search_query.push({ [field]: [ALL_ID] });
        } else if (withoutConnections.prop('checked') === true) {
          search_query.push({ [field]: [ALL_WITHOUT_ID] });
        } else {
          search_query.push({
            [field]: adjust_search_query_filter_states(
              field,
              type,
              window.lodash.map(
                window.lodash.get(
                  window.Typeahead[`.js-typeahead-${field}`],
                  'items',
                ),
                'ID',
              ),
            ),
          });
        }
      }
      if (type === 'user_select') {
        search_query.push({
          [field]: adjust_search_query_filter_states(
            field,
            type,
            window.lodash.map(
              window.lodash.get(
                window.Typeahead[`.js-typeahead-${field}`],
                'items',
              ),
              'ID',
            ),
          ),
        });
      } else if (type === 'multi_select') {
        search_query.push({
          [field]: adjust_search_query_filter_states(
            field,
            type,
            window.lodash.map(
              window.lodash.get(
                window.Typeahead[`.js-typeahead-${field}`],
                'items',
              ),
              'key',
            ),
          ),
        });
      } else if (type === 'tags') {
        search_query.push({
          [field]: adjust_search_query_filter_states(
            field,
            type,
            window.lodash.map(
              window.lodash.get(
                window.Typeahead[`.js-typeahead-${field}`],
                'items',
              ),
              'key',
            ),
          ),
        });
      } else if (type === 'location' || type === 'location_meta') {
        search_query.push({
          location_grid: adjust_search_query_filter_states(
            'location_grid',
            type,
            window.lodash.map(
              window.lodash.get(
                window.Typeahead[`.js-typeahead-${field}`],
                'items',
              ),
              'ID',
            ),
          ),
        });
      } else if (type === 'date' || type === 'datetime') {
        let date = {};
        let start = $(
          `.dt_date_picker[data-field="${field}"][data-delimit="start"]`,
        ).val();
        if (start) {
          date.start = start;
        }
        let end = $(
          `.dt_date_picker[data-field="${field}"][data-delimit="end"]`,
        ).val();
        if (end) {
          date.end = end;
        }
        search_query.push({ [field]: date });
      } else if (type === 'text' || type === 'communication_channel') {
        let filter = $('#' + field + '_text_comms_filter').val();
        let value = filter;

        switch ($('.filter-by-text-comms-option:checked').val()) {
          case 'all-with-set-value': {
            value = '*';
            break;
          }
          case 'all-without-set-value': {
            value = null;
            break;
          }
          case 'all-with-filtered-value': {
            value = filter;
            break;
          }
          case 'all-without-filtered-value': {
            value = '-' + filter;
            break;
          }
        }

        // Package accordingly based on field type.
        switch (type) {
          case 'text':
          case 'communication_channel': {
            search_query.push({ [field]: value !== null ? [value] : [] });
            break;
          }
        }
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
    const typeaheadQueryElement = tabsPanel.find('.typeahead__query');
    const typeaheadCancelButtons = tabsPanel.find('.typeahead__cancel-button');
    const typeahead = tabsPanel.find(`.js-typeahead-${field}`);

    toggle_all_connection_option(tabsPanel, without);

    if ($(this).prop('checked') === true) {
      typeahead.prop('disabled', true);
      typeaheadQueryElement.addClass('disabled');
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
      typeahead.prop('disabled', false);
      typeaheadQueryElement.removeClass('disabled');
      remove_filter_labels(id, field);
      // clear the typeahead by manually clicking each selected item.
      // This is done at this point as it triggers the typeahead to open which we don't want just after we have disabled it.
      typeaheadCancelButtons.each(function () {
        $(this).trigger('click', { botClick: true });
      });
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

  function create_location_label(field, id, value, listSettings) {
    let name = window.lodash.get(
      listSettings,
      `post_type_settings.fields.location_grid.name`,
      'location_grid',
    );
    return {
      newLabel: { id, name: `${name}: ${value}`, field, type: 'location_grid' },
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
      field_name,
      delimiter_label,
    };
  }

  $('.all-connections').on('click', all_connections_click_handler);

  function without_connections_handler() {
    all_connections_click_handler.call(this, { without: true });
  }

  $('.all-without-connections').on('click', without_connections_handler);

  $('.text-comms-filter-input').on('keyup', function (e) {
    // Ensure to assign default settings accordingly.
    const field = $(e.target).data('field');
    const panel = $(`#${field}.tabs-panel`);
    const field_settings = list_settings?.post_type_settings?.fields[field];
    if (panel && field_settings && field_settings['type']) {
      switch (field_settings['type']) {
        case 'text':
        case 'communication_channel': {
          const checked_options = $(panel).find(
            `.filter-by-text-comms-option:checked`,
          );
          const existing_label = new_filter_labels.find(
            (label) => label['field'] === field,
          );

          // Only apply default settings if unable to detect and previous selections.
          if (checked_options.length === 0 && existing_label === undefined) {
            const default_option = $(panel).find(
              `.filter-by-text-comms-option[value="all-with-filtered-value"]`,
            );
            if (default_option) {
              $(default_option).prop('checked', true);
              $(default_option).trigger('click');
            }
          }

          // Update label with latest filtered value.
          const filtered_value = $(e.target).val();
          const latest_checked_option = $(panel).find(
            `.filter-by-text-comms-option:checked`,
          );
          const latest_existing_label = new_filter_labels.find(
            (label) => label['field'] === field,
          );
          if (
            latest_checked_option.length === 1 &&
            latest_existing_label !== undefined &&
            ['all-with-filtered-value', 'all-without-filtered-value'].includes(
              $(latest_checked_option).val(),
            )
          ) {
            const updated_label_text = `${esc(list_settings.post_type_settings.fields[field] ? list_settings.post_type_settings.fields[field].name : '')}: ${esc(filtered_value)}`;
            $(selected_filters)
              .find(
                `.current-filter[data-id="${$(latest_checked_option).val()}"].${field}`,
              )
              .text(updated_label_text);

            // Update global filter labels array.
            const label_idx = new_filter_labels.findIndex(
              (label) => label['field'] === field,
            );
            new_filter_labels[label_idx]['name'] = updated_label_text;
          }
          break;
        }
      }
    }
  });

  $('.filter-by-text-comms-option').on('click', function (e) {
    handle_filter_by_text_comms({
      id: $(this).val(),
      field: $(this).data('field'),
    });
  });

  function handle_filter_by_text_comms(options) {
    const { id, field } = options || { id: null, field: null };
    if (id && field) {
      // Adjust filter text field state accordingly, based on option selection.
      let filter_text_field = $('#' + field + '_text_comms_filter');
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
          let filtered_value = $(`#${field}_text_comms_filter`).val();
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

  let load_multi_select_typeaheads =
    async function load_multi_select_typeaheads() {
      for (let input of $(
        '#filter-modal .multi_select .typeahead__query input',
      )) {
        let field = $(input).data('field');
        let typeahead_name = `.js-typeahead-${field}`;

        if (window.Typeahead[typeahead_name]) {
          return;
        }

        let source_data = { data: [] };
        let field_options = window.lodash.get(
          list_settings,
          `post_type_settings.fields.${field}.default`,
          {},
        );
        if (Object.keys(field_options).length > 0) {
          window.lodash.forOwn(field_options, (val, key) => {
            if (!val.deleted) {
              source_data.data.push({
                key: key,
                name: key,
                value: val.label || key,
              });
            }
          });
        } else {
          source_data = {
            [field]: {
              display: ['value'],
              ajax: {
                url:
                  window.wpApiShare.root +
                  `dt-posts/v2/${list_settings.post_type}/multi-select-values`,
                data: {
                  s: '{{query}}',
                  field,
                },
                beforeSend: function (xhr) {
                  xhr.setRequestHeader('X-WP-Nonce', window.wpApiShare.nonce);
                },
                callback: {
                  done: function (data) {
                    return (data || []).map((tag) => {
                      let label = window.lodash.get(
                        field_options,
                        tag + '.label',
                        tag,
                      );
                      return { value: label, key: tag };
                    });
                  },
                },
              },
            },
          };
        }
        $.typeahead({
          input: `.js-typeahead-${field}`,
          minLength: 0,
          maxItem: 20,
          searchOnFocus: true,
          template: function (query, item) {
            return `<span>${window.SHAREDFUNCTIONS.escapeHTML(item.value)}</span>`;
          },
          source: source_data,
          display: 'value',
          templateValue: '{{value}}',
          dynamic: true,
          multiselect: {
            matchOn: ['key'],
            data: [],
            callback: {
              onCancel: function (node, item) {
                $(`.current-filter[data-id="${item.key}"].${field}`).remove();
                window.lodash.pullAllBy(
                  new_filter_labels,
                  [{ id: item.key }],
                  'id',
                );
              },
            },
          },
          callback: {
            onClick: function (node, a, item) {
              const { newLabel, name } = create_name_value_label(
                field,
                item.key,
                item.value,
                list_settings,
              );
              selected_filters.append(
                `<span class="current-filter ${window.SHAREDFUNCTIONS.escapeHTML(field)}" data-id="${window.SHAREDFUNCTIONS.escapeHTML(item.key)}">${window.SHAREDFUNCTIONS.escapeHTML(name)}:${window.SHAREDFUNCTIONS.escapeHTML(item.value)}</span>`,
              );
              new_filter_labels.push(newLabel);
            },
            onResult: function (node, query, result, resultCount) {
              let text = window.TYPEAHEADS.typeaheadHelpText(
                resultCount,
                query,
                result,
              );
              $(`#${field}-result-container`).html(text);
            },
            onHideLayout: function () {
              $(`#${field}-result-container`).html('');
            },
          },
        });
      }
    };

  let load_post_type_typeaheads = () => {
    $(".typeahead__query [data-type='connection']").each((key, el) => {
      let field_key = $(el).data('field');
      let post_type = window.lodash.get(
        list_settings,
        `post_type_settings.fields.${field_key}.post_type`,
        field_key,
      );
      if (!window.Typeahead[`.js-typeahead-${field_key}`]) {
        $.typeahead({
          input: `.js-typeahead-${field_key}`,
          minLength: 0,
          accent: true,
          searchOnFocus: true,
          maxItem: 20,
          template: function (query, item) {
            return `<span dir="auto">${window.SHAREDFUNCTIONS.escapeHTML(item.name)} (#${window.SHAREDFUNCTIONS.escapeHTML(item.ID)})</span>`;
          },
          source: window.TYPEAHEADS.typeaheadPostsSource(post_type),
          display: 'name',
          templateValue: '{{name}}',
          dynamic: true,
          multiselect: {
            matchOn: ['ID'],
            data: [],
            callback: {
              onCancel: function (node, item, event) {
                remove_filter_labels(item.ID, field_key);
              },
            },
          },
          callback: {
            onResult: function (node, query, result, resultCount) {
              let text = window.TYPEAHEADS.typeaheadHelpText(
                resultCount,
                query,
                result,
              );
              $(`#${field_key}-result-container`).html(text);
            },
            onHideLayout: function () {
              $(`#${field_key}-result-container`).html('');
            },
            onClick: function (node, a, item) {
              const { newLabel } = create_value_label(
                field_key,
                item.ID,
                item.name,
              );
              new_filter_labels.push(newLabel);
              selected_filters.append(
                `<span class="current-filter ${field_key}" data-id="${window.SHAREDFUNCTIONS.escapeHTML(item.ID)}">${window.SHAREDFUNCTIONS.escapeHTML(item.name)}</span>`,
              );
            },
          },
        });
      }
    });
  };

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

  let load_user_select_typeaheads = () => {
    $(".typeahead__query [data-type='user_select']").each((key, el) => {
      let field_key = $(el).data('field');
      if (!window.Typeahead[`.js-typeahead-${field_key}`]) {
        $.typeahead({
          input: `.js-typeahead-${field_key}`,
          minLength: 0,
          accent: true,
          searchOnFocus: true,
          maxItem: 20,
          template: function (query, item) {
            return `<span dir="auto">${window.SHAREDFUNCTIONS.escapeHTML(item.name)} (#${window.SHAREDFUNCTIONS.escapeHTML(item.ID)})</span>`;
          },
          source: window.TYPEAHEADS.typeaheadUserSource(),
          display: 'name',
          templateValue: '{{name}}',
          dynamic: true,
          multiselect: {
            matchOn: ['ID'],
            data: [],
            callback: {
              onCancel: function (node, item) {
                $(
                  `.current-filter[data-id="${item.ID}"].${field_key}`,
                ).remove();
                window.lodash.pullAllBy(
                  new_filter_labels,
                  [{ id: item.ID }],
                  'id',
                );
              },
            },
          },
          callback: {
            onResult: function (node, query, result, resultCount) {
              let text = window.TYPEAHEADS.typeaheadHelpText(
                resultCount,
                query,
                result,
              );
              $(`#${field_key}-result-container`).html(text);
            },
            onHideLayout: function () {
              $(`#${field_key}-result-container`).html('');
            },
            onClick: function (node, a, item) {
              const { newLabel } = create_value_label(
                field_key,
                item.ID,
                item.name,
              );
              new_filter_labels.push(newLabel);
              selected_filters.append(
                `<span class="current-filter ${field_key}" data-id="${window.SHAREDFUNCTIONS.escapeHTML(item.ID)}">${window.SHAREDFUNCTIONS.escapeHTML(item.name)}</span>`,
              );
            },
          },
        });
      }
    });
  };

  /**
   * Location
   */
  $('#mapbox-clear-autocomplete').click('input', function () {
    delete window.location_data;
  });

  let load_location_typeahead = () => {
    let key = 'location_grid';
    if ($('.js-typeahead-location_grid_meta').length) {
      key = 'location_grid_meta';
    }
    if (!window.Typeahead[`.js-typeahead-${key}`]) {
      // Ensure element is present before proceeding!
      if (
        $('.js-typeahead-' + window.SHAREDFUNCTIONS.escapeHTML(key)).length > 0
      ) {
        $.typeahead({
          input: `.js-typeahead-${window.SHAREDFUNCTIONS.escapeHTML(key)}`,
          minLength: 0,
          accent: true,
          searchOnFocus: true,
          maxItem: 20,
          dropdownFilter: [
            {
              key: 'group',
              value: 'used',
              template: window.SHAREDFUNCTIONS.escapeHTML(
                window.wpApiShare.translations.used_locations,
              ),
              all: window.SHAREDFUNCTIONS.escapeHTML(
                window.wpApiShare.translations.all_locations,
              ),
            },
          ],
          source: {
            used: {
              display: 'name',
              ajax: {
                url:
                  window.wpApiShare.root +
                  'dt/v1/mapping_module/search_location_grid_by_name',
                data: {
                  s: '{{query}}',
                  filter: function () {
                    return window.lodash.get(
                      window.Typeahead[`.js-typeahead-${key}`].filters.dropdown,
                      'value',
                      'all',
                    );
                  },
                },
                beforeSend: function (xhr) {
                  xhr.setRequestHeader('X-WP-Nonce', window.wpApiShare.nonce);
                },
                callback: {
                  done: function (data) {
                    if (typeof window.typeaheadTotals !== 'undefined') {
                      window.typeaheadTotals.field = data.total;
                    }
                    return data.location_grid;
                  },
                },
              },
            },
          },
          display: 'name',
          templateValue: '{{name}}',
          dynamic: true,
          multiselect: {
            matchOn: ['ID'],
            data: [],
            callback: {
              onCancel: function (node, item) {
                $(
                  `.current-filter[data-id="${item.ID}"].location_grid`,
                ).remove();
                window.lodash.pullAllBy(
                  new_filter_labels,
                  [{ id: item.ID }],
                  'id',
                );
              },
            },
          },
          callback: {
            onResult: function (node, query, result, resultCount) {
              let text = window.TYPEAHEADS.typeaheadHelpText(
                resultCount,
                query,
                result,
              );
              $('#location_grid-result-container').html(text);
            },
            onReady() {
              this.filters.dropdown = {
                key: 'group',
                value: 'used',
                template: window.SHAREDFUNCTIONS.escapeHTML(
                  window.wpApiShare.translations.used_locations,
                ),
              };
              this.container
                .removeClass('filter')
                .find('.' + this.options.selector.filterButton)
                .html(
                  window.SHAREDFUNCTIONS.escapeHTML(
                    window.wpApiShare.translations.used_locations,
                  ),
                );
            },
            onHideLayout: function () {
              $('#location_grid-result-container').html('');
            },
            onClick: function (node, a, item) {
              const { name, newLabel } = create_location_label(
                key,
                item.ID,
                item.name,
                list_settings,
              );
              new_filter_labels.push(newLabel);
              selected_filters.append(
                `<span class="current-filter location_grid" data-id="${window.SHAREDFUNCTIONS.escapeHTML(item.ID)}">${window.SHAREDFUNCTIONS.escapeHTML(name)}:${window.SHAREDFUNCTIONS.escapeHTML(item.name)}</span>`,
              );
            },
          },
        });
      }
    }
  };

  /*
   * Setup filter box
   */
  let typeaheads_loaded = null;
  $('#filter-modal').on('open.zf.reveal', function () {
    new_filter_labels = [];
    load_location_typeahead();
    load_post_type_typeaheads();
    load_user_select_typeaheads();
    typeaheads_loaded = load_multi_select_typeaheads().catch((err) => {
      console.error(err);
    });
    $('#new-filter-name').val('');
    $('#filter-modal input.dt_date_picker').each(function () {
      $(this).val('');
    });
    $('#filter-modal input:checked').each(function () {
      $(this).prop('checked', false);
    });
    $('#filter-modal input:disabled').each(function () {
      $(this).prop('disabled', false);
    });
    $('#filter-modal .typeahead__query.disabled').each(function () {
      $(this).removeClass('disabled');
    });
    selected_filters.empty();
    $('.typeahead__query input').each(function () {
      let typeahead =
        window.Typeahead['.' + $(this).attr('class').split(/\s+/)[0]];
      if (typeahead && typeahead.items) {
        for (let i = 0; i < typeahead.items.length; i) {
          typeahead.cancelMultiselectItem(0);
        }
        typeahead.node.trigger('propertychange.typeahead');
      }
    });
    $('#confirm-filter-records').show();
    $('#save-filter-edits').hide();
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
  $('#filter-modal input.dt_date_picker').on('blur', function (e) {
    if (clicked && clicked.closest('.ui-datepicker').length === 1) {
      // we have clicked in the datepicker, so don't run the blur
      return;
    }
    // delay the blur so that if the user has clicked we get the correct date from the input
    setTimeout(() => {
      if (!e.target.value) {
        const clearButton = $(this).prev('.clear-date-picker');
        clearButton.click();
        return;
      }
      $(this).datepicker('setDate', e.target.value);
      $('.ui-datepicker-current-day').click();
    }, 100);
  });

  function edit_saved_filter(filter) {
    $('#filter-modal').foundation('open');
    typeaheads_loaded.then(() => {
      let connectionTypeKeys =
        list_settings.post_type_settings.connection_types;
      connectionTypeKeys.push('location_grid');
      filter.labels.forEach((label) => {
        // Determine exclusion status
        let excluded_class = is_search_query_filter_label_excluded(
          filter,
          label,
        )
          ? 'current-filter-excluded'
          : '';

        // Proceed with displaying of filter modal
        selected_filters.append(
          `<span class="current-filter ${excluded_class} ${window.SHAREDFUNCTIONS.escapeHTML(label.field)}" data-id="${window.SHAREDFUNCTIONS.escapeHTML(label.id)}">${window.SHAREDFUNCTIONS.escapeHTML(label.name)}</span>`,
        );
        let type = window.lodash.get(
          list_settings,
          `post_type_settings.fields.${label.field}.type`,
        );
        if (type === 'key_select' || type === 'boolean') {
          $(
            `#filter-modal #${label.field}-options input[value="${label.id}"]`,
          ).prop('checked', true);
        } else if (type === 'date' || type === 'datetime') {
          $(`#filter-modal #${label.field}-options #${label.id}`).datepicker(
            'setDate',
            label.date,
          );
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
            window.Typeahead[
              `.js-typeahead-${label.field}`
            ].addMultiselectItemLayout({ ID: label.id, name: label.name });
          }
        } else if (type === 'multi_select') {
          window.Typeahead[
            `.js-typeahead-${label.field}`
          ].addMultiselectItemLayout({ key: label.id, value: label.name });
        } else if (type === 'tags') {
          window.Typeahead[
            `.js-typeahead-${label.field}`
          ].addMultiselectItemLayout({ key: label.id, value: label.id });
        } else if (type === 'user_select') {
          window.Typeahead[
            `.js-typeahead-${label.field}`
          ].addMultiselectItemLayout({ name: label.name, ID: label.id });
        }
      });
      // moved this below the forEach as the global new_filter_labels was messing with the loop.
      new_filter_labels = filter.labels;
      (filter.query.combine || []).forEach((c) => {
        $(`#combine_${c}`).prop('checked', true);
      });
      $('#new-filter-name').val(filter.name);
      $('#confirm-filter-records').hide();
      $('#save-filter-edits').data('filter-id', filter.ID).show();
    });
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
    if (field && window.Typeahead[`.js-typeahead-${field}`]) {
      window.Typeahead[`.js-typeahead-${field}`].adjustInputSize();
    }
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

  $('#filter-modal .dt_date_picker').datepicker({
    constrainInput: false,
    dateFormat: 'yy-mm-dd',
    onSelect: function (date) {
      let id = $(this).data('field');
      let delimiter = $(this).data('delimit');
      //remove existing filters
      window.lodash.pullAllBy(
        new_filter_labels,
        [{ id: `${id}_${delimiter}` }],
        'id',
      );
      $(`.current-filter[data-id="${id}_${delimiter}"]`).remove();
      const { newLabel, field_name, delimiter_label } = create_date_label(
        id,
        date,
        delimiter,
      );
      //add new filters
      new_filter_labels.push(newLabel);
      selected_filters.append(`
        <span class="current-filter ${id}_${delimiter}"
              data-id="${id}_${delimiter}">
                ${field_name} ${delimiter_label}:${date}
        </span>
      `);
    },
    changeMonth: true,
    changeYear: true,
    yearRange: '-20:+10',
  });

  $('#filter-modal .clear-date-picker').on('click', function () {
    let id = $(this).data('for');
    $(`#filter-modal #${id}`).datepicker('setDate', null);
    window.lodash.pullAllBy(new_filter_labels, [{ id: `${id}` }], 'id');
    $(`.current-filter[data-id="${id}"]`).remove();
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
    clearSearchButton.css({ display: this.value.length ? 'flex' : 'none' });
    if (e.keyCode === 13) {
      $('#search').trigger('click');
    }
  });

  $('.search-input--mobile').on('keyup', function (e) {
    clearSearchButton.css({ display: this.value.length ? 'flex' : 'none' });
    if (e.keyCode === 13) {
      $('#search-mobile').trigger('click');
    }
  });

  clearSearchButton.on('click', function () {
    $('.search-input').val('');
    clearSearchButton.css({ display: 'none' });
  });

  //toggle show search input on mobile
  $('#open-search').on('click', function () {
    $('.hideable-search').toggle();
  });

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

  /***
   * Bulk Edit
   */

  $('#bulk_edit_controls').on('click', function () {
    $('#bulk_edit_picker').toggle();
    $('#records-table').toggleClass('bulk_edit_on');
  });

  $('#bulk_edit_seeMore').on('click', function () {
    $('#bulk_more').toggle();
    $('#bulk_edit_seeMore').children().toggle();
  });

  function bulk_edit_checkbox_event() {
    $('tbody tr td.bulk_edit_checkbox').on('click', function (e) {
      e.stopImmediatePropagation();
      bulk_edit_count();
    });
  }

  $('#bulk_edit_master').on('click', function (e) {
    e.stopImmediatePropagation();
    let checked = $(this).children('input').is(':checked');
    $('.bulk_edit_checkbox input').each(function () {
      $(this).prop('checked', checked);
      bulk_edit_count();
    });
  });

  /***
   * Bulk Delete
   */

  let bulk_edit_delete_submit_button = $('#bulk_edit_delete_submit');
  bulk_edit_delete_submit_button.on('click', function (e) {
    let bulk_edit_total_checked = $(
      '.bulk_edit_checkbox:not(#bulk_edit_master) input:checked',
    ).length;

    if (bulk_edit_total_checked > 0) {
      let bulk_edit_delete_submit_button_span = $(
        '.bulk_edit_delete_submit_text',
      );
      let confirm_text = `${$(bulk_edit_delete_submit_button_span).data('pretext')} ${bulk_edit_total_checked} ${$(bulk_edit_delete_submit_button_span).data('posttext')}`;
      let confirm_text_capitalise = window.lodash.startCase(
        window.lodash.toLower(confirm_text),
      );

      if (
        list_settings.permissions.delete_any &&
        confirm(confirm_text_capitalise)
      ) {
        bulk_delete_submit();
      } else {
        window.location.reload();
      }
    } else {
      window.location.reload();
    }
  });

  function bulk_delete_submit() {
    // Build queue of post ids.
    let queue = [];
    $('.bulk_edit_checkbox input').each(function () {
      if (this.checked && this.id !== 'bulk_edit_master_checkbox') {
        let postId = parseInt($(this).val());
        queue.push(postId);
      }
    });
    process(queue, 10, do_each, do_done, {}, null, {}, 'delete');
  }

  /**
   * Bulk_Assigned_to
   */
  let bulk_edit_submit_button = $('#bulk_edit_submit');

  bulk_edit_submit_button.on('click', function (e) {
    bulk_edit_submit();
  });

  function bulk_edit_submit() {
    $('#bulk_edit_submit-spinner').addClass('active');
    let allInputs = $(
      '#bulk_edit_picker input, #bulk_edit_picker select, #bulk_edit_picker .button',
    ).not('#bulk_share');
    let multiSelectInputs = $('#bulk_edit_picker .dt_multi_select');
    let shareInput = $('#bulk_share');
    let commentPayload = {};
    commentPayload['commentText'] = $('#bulk_comment-input').val();
    commentPayload['commentType'] = $('#comment_type_selector').val();

    let updatePayload = {};
    let sharePayload;

    allInputs.each(function () {
      let inputData = $(this).data();
      $.each(inputData, function (key, value) {
        if (key.includes('bulk_key_') && value) {
          let field_key = key.replace('bulk_key_', '');
          if (field_key) {
            updatePayload[field_key] = value;
          }
        }
      });
    });
    if (window.location_data) {
      updatePayload['location_grid_meta'] =
        window.location_data.location_grid_meta;
    }

    let multiSelectUpdatePayload = {};
    multiSelectInputs.each(function () {
      let inputData = $(this).data();
      $.each(inputData, function (key, value) {
        if (key.includes('bulk_key_') && value) {
          let field_key = key.replace('bulk_key_', '');
          if (!multiSelectUpdatePayload[field_key]) {
            multiSelectUpdatePayload[field_key] = { values: [] };
          }
          multiSelectUpdatePayload[field_key].values.push(value.values);
        }
      });
    });
    const multiSelectKeys = Object.keys(multiSelectUpdatePayload);

    multiSelectKeys.forEach((key, index) => {
      updatePayload[key] = multiSelectUpdatePayload[key];
    });

    shareInput.each(function () {
      sharePayload = $(this).data('bulk_key_share');
    });

    let shares = {
      users: sharePayload,
      unshare: $('#bulk_share_unshare').prop('checked'),
    };

    let queue = [];
    let count = 0;
    $('.bulk_edit_checkbox input').each(function () {
      if (this.checked && this.id !== 'bulk_edit_master_checkbox') {
        let postId = parseInt($(this).val());
        queue.push(postId);
      }
    });
    process(queue, 10, do_each, do_done, updatePayload, shares, commentPayload);
  }

  function bulk_edit_count() {
    let bulk_edit_total_checked = $(
      '.bulk_edit_checkbox:not(#bulk_edit_master) input:checked',
    ).length;
    let bulk_edit_submit_button_text = $('.bulk_edit_submit_text');
    let bulk_edit_delete_submit_button_text = $(
      '.bulk_edit_delete_submit_text',
    );

    if (bulk_edit_total_checked == 0) {
      bulk_edit_submit_button_text.text(
        `${list_settings.translations.make_selections_below}`,
      );

      if (list_settings.permissions.delete_any) {
        bulk_edit_delete_submit_button_text.text(
          `${list_settings.translations.delete_selections_below}`,
        );
      }
    } else {
      bulk_edit_submit_button_text.each(function (index) {
        let pretext = $(this).data('pretext');
        let posttext = $(this).data('posttext');
        $(this).text(`${pretext} ${bulk_edit_total_checked} ${posttext}`);
      });

      if (list_settings.permissions.delete_any) {
        bulk_edit_delete_submit_button_text.each(function (index) {
          let pretext = $(this).data('pretext');
          let posttext = $(this).data('posttext');
          $(this).text(`${pretext} ${bulk_edit_total_checked} ${posttext}`);
        });
      }
    }
  }

  let bulk_edit_picker_checkboxes = $('#bulk_edit_picker .update-needed');
  bulk_edit_picker_checkboxes.on('click', function (e) {
    if ($(this).is(':checked')) {
      $(this).data('bulk_key_requires_update', true);
    }
  });

  let bulk_edit_picker_select_field = $('#bulk_edit_picker select');
  bulk_edit_picker_select_field.on('change', function (e) {
    let field_key = this.id.replace('bulk_', '');
    $(this).data(`bulk_key_${field_key}`, this.value);
  });

  let bulk_edit_picker_button_groups = $('#bulk_edit_picker .select-button');
  bulk_edit_picker_button_groups.on('click', function (e) {
    let field_key = $(this).data('field-key').replace('bulk_', '');
    let optionKey = $(this).attr('id');

    let fieldValue = {};

    fieldValue.values = { value: optionKey };

    $(this).addClass('selected-select-button');
    $(this).data(`bulk_key_${field_key}`, fieldValue);
  });

  //Bulk Update Queue
  function process(
    q,
    num,
    fn,
    done,
    update,
    share,
    comment,
    event_type = 'update',
    responses = [],
  ) {
    // remove a batch of items from the queue
    let items = q.splice(0, num),
      count = items.length;

    // no more items?
    if (!count) {
      // exec done callback if specified
      done && done(responses);
      // quit
      return;
    }

    // loop over each item
    for (let i = 0; i < count; i++) {
      // call callback, passing item and
      // a "done" callback
      fn(
        items[i],
        function (response) {
          // capture valid response
          if (response) {
            if (Array.isArray(response)) {
              response.forEach((element) => responses.push(element));
            } else {
              responses.push(response);
            }
          }

          // when done, decrement counter and
          // if counter is 0, process next batch
          --count ||
            process(
              q,
              num,
              fn,
              done,
              update,
              share,
              comment,
              event_type,
              responses,
            );
        },
        update,
        share,
        comment,
        event_type,
      );
    }
  }

  // a per-item action
  function do_each(item, done, update, share, comment, event_type) {
    let promises = [];

    switch (event_type) {
      case 'update': {
        if (Object.keys(update).length) {
          promises.push(
            window.API.update_post(list_settings.post_type, item, update).catch(
              (err) => {
                console.error(err);
              },
            ),
          );
        }

        if (share && share['users']) {
          share['users'].forEach(function (value) {
            let promise = share['unshare']
              ? window.API.remove_shared(
                  list_settings.post_type,
                  item,
                  value,
                ).catch((err) => {
                  console.error(err);
                })
              : window.API.add_shared(
                  list_settings.post_type,
                  item,
                  value,
                ).catch((err) => {
                  console.error(err);
                });
            promises.push(promise);
          });
        }

        if (comment.commentText) {
          promises.push(
            window.API.post_comment(
              list_settings.post_type,
              item,
              comment.commentText,
              comment.commentType,
            ).catch((err) => {
              console.error(err);
            }),
          );
        }

        break;
      }
      case 'delete': {
        if (list_settings.permissions.delete_any) {
          promises.push(
            window.API.delete_post(list_settings.post_type, item).catch(
              (err) => {
                console.error(err);
              },
            ),
          );
        }
        break;
      }
      case 'message': {
        if (
          update?.subject &&
          update?.from_name &&
          update?.send_method &&
          update?.message
        ) {
          promises.push(
            window
              .makeRequestOnPosts(
                'POST',
                `${list_settings.post_type}/${item}/post_messaging`,
                update,
              )
              .catch((err) => {
                console.error(err);
              }),
          );
        }
        break;
      }
    }
    Promise.all(promises).then(function (responses) {
      done(responses);
    });
  }

  function do_done() {
    $('#bulk_edit_submit-spinner').removeClass('active');
    $('#bulk_edit_delete_submit-spinner').removeClass('active');
    window.location.reload();
  }

  let bulk_assigned_to_input = $(`.js-typeahead-bulk_assigned_to`);
  if (bulk_assigned_to_input.length) {
    $.typeahead({
      input: '.js-typeahead-bulk_assigned_to',
      minLength: 0,
      maxItem: 0,
      accent: true,
      searchOnFocus: true,
      source: window.TYPEAHEADS.typeaheadUserSource(),
      templateValue: '{{name}}',
      template: function (query, item) {
        return `<div class="assigned-to-row" dir="auto">
        <span>
            <span class="avatar"><img style="vertical-align: text-bottom" src="{{avatar}}"/></span>
            ${window.SHAREDFUNCTIONS.escapeHTML(item.name)}
        </span>
        ${item.status_color ? `<span class="status-square" style="background-color: ${window.SHAREDFUNCTIONS.escapeHTML(item.status_color)};">&nbsp;</span>` : ''}
        ${
          item.update_needed && item.update_needed > 0
            ? `<span>
          <img style="height: 12px;" src="${window.SHAREDFUNCTIONS.escapeHTML(window.wpApiShare.template_dir)}/dt-assets/images/broken.svg"/>
          <span style="font-size: 14px">${window.SHAREDFUNCTIONS.escapeHTML(item.update_needed)}</span>
        </span>`
            : ''
        }
      </div>`;
      },
      dynamic: true,
      hint: true,
      emptyTemplate: window.SHAREDFUNCTIONS.escapeHTML(
        window.wpApiShare.translations.no_records_found,
      ),
      callback: {
        onClick: function (node, a, item) {
          node.data('bulk_key_assigned_to', `user-${item.ID}`);
        },
        onResult: function (node, query, result, resultCount) {
          let text = window.TYPEAHEADS.typeaheadHelpText(
            resultCount,
            query,
            result,
          );
          $('#bulk_assigned_to-result-container').html(text);
        },
        onHideLayout: function () {
          $('.bulk_assigned_to-result-container').html('');
        },
        onReady: function () {},
      },
    });
  }

  /**
   * Bulk Send Message
   */

  $('#bulk_send_msg_submit').on('click', function (e) {
    handle_bulk_send_messages();
  });

  function handle_bulk_send_messages() {
    const spinner = $('#bulk_send_msg_submit-spinner');

    let subject = $('#bulk_send_msg_subject').val().trim();
    let from_name = $('#bulk_send_msg_from_name').val().trim();
    let reply_to = $('#bulk_send_msg_reply_to').val().trim();
    let send_method = 'email';
    let message = $('#bulk_send_msg').val().trim();

    // If multiple options detected, ensure correct selection is made.
    const checked_send_method = $('.bulk-send-msg-method:checked');
    if ($(checked_send_method).length > 0) {
      send_method = $(checked_send_method).val().trim();
    }

    let queue = [];
    $('.bulk_edit_checkbox input').each(function () {
      if (this.checked && this.id !== 'bulk_edit_master_checkbox') {
        queue.push(parseInt($(this).val()));
      }
    });

    // Validate entries.
    if (!subject) {
      $('#bulk_send_msg_subject_support_text').show();
      return;
    } else {
      $('#bulk_send_msg_subject_support_text').hide();
    }

    if (!from_name) {
      $('#bulk_send_msg_from_name_support_text').show();
      return;
    } else {
      $('#bulk_send_msg_from_name_support_text').hide();
    }

    if (!send_method) {
      $('#bulk_send_msg_method_support_text').show();
      return;
    } else {
      $('#bulk_send_msg_method_support_text').hide();
    }

    if (!message) {
      $('#bulk_send_msg_support_text').show();
      return;
    } else {
      $('#bulk_send_msg_support_text').hide();
    }

    if (!queue || queue.length < 1) {
      $('#bulk_send_msg_submit_support_text').show();
      return;
    } else {
      $('#bulk_send_msg_submit_support_text').hide();
    }

    // Proceed with staged-based message send requests.
    $(spinner).addClass('active');
    process(
      queue,
      10,
      do_each,
      function (responses) {
        // If available, extract response summary.
        if (responses && responses.length > 0) {
          let email_queue_link = `<a target="_blank" href="${window.wpApiShare.site_url + '/wp-admin/admin.php?page=dt_utilities&tab=background_jobs'}">${list_settings.translations.see_queue}</a>`;
          let count_sent = 0;
          let count_fails = 0;
          responses.forEach(function (response) {
            if (response && response['sent'] !== undefined) {
              if (response['sent']) {
                count_sent++;
              } else {
                count_fails++;
              }
            }
          });

          $('#bulk_send_msg_submit-message').html(
            `<strong>${count_sent}</strong> ${list_settings.translations.sent}! ${window.wpApiShare.can_manage_dt ? email_queue_link : ''}<br><strong>${count_fails}</strong> ${list_settings.translations.not_sent}`,
          );
        }

        // Reset record selections.
        $(spinner).removeClass('active');
        $('#bulk_edit_master_checkbox').prop('checked', false);
        $('.bulk_edit_checkbox input').prop('checked', false);
        bulk_edit_count();
        // window.location.reload();
      },
      {
        subject: subject,
        from_name: from_name,
        reply_to: reply_to,
        send_method: send_method,
        message: message,
      },
      {},
      {},
      'message',
    );
  }

  /**
   * Bulk share
   */
  $.typeahead({
    input: '#bulk_share',
    minLength: 0,
    maxItem: 0,
    accent: true,
    searchOnFocus: true,
    source: window.TYPEAHEADS.typeaheadUserSource(),
    templateValue: '{{name}}',
    dynamic: true,
    multiselect: {
      matchOn: ['ID'],
      callback: {
        onCancel: function (node, item) {
          $(node).removeData(`bulk_key_bulk_share`);
          $('#share-result-container').html('');
        },
      },
    },
    callback: {
      onClick: function (node, a, item, event) {
        let shareUserArray;
        if (node.data('bulk_key_share')) {
          shareUserArray = node.data('bulk_key_share');
        } else {
          shareUserArray = [];
        }
        shareUserArray.push(item.ID);
        node.data(`bulk_key_share`, shareUserArray);
      },
      onResult: function (node, query, result, resultCount) {
        if (query) {
          let text = window.TYPEAHEADS.typeaheadHelpText(
            resultCount,
            query,
            result,
          );
          $('#share-result-container').html(text);
        }
      },
      onHideLayout: function () {
        $('#share-result-container').html('');
      },
    },
  });

  /**
   * Bulk Typeahead
   */
  let field_settings = window.list_settings.post_type_settings.fields;

  $('#bulk_edit_picker .dt_typeahead').each((key, el) => {
    let element_id = $(el)
      .attr('id')
      .replace(/_connection$/, '');
    let div_id = $(el).attr('id');
    let field_id = $(`#${div_id} input`).data('field');
    if (element_id !== 'bulk_share') {
      let listing_post_type = window.lodash.get(
        window.list_settings.post_type_settings.fields[field_id],
        'post_type',
        'contacts',
      );
      $.typeahead({
        input: `.js-typeahead-${element_id}`,
        minLength: 0,
        accent: true,
        maxItem: 30,
        searchOnFocus: true,
        template: window.TYPEAHEADS.contactListRowTemplate,
        source: window.TYPEAHEADS.typeaheadPostsSource(listing_post_type, {
          field_key: field_id,
        }),
        display: 'name',
        templateValue: '{{name}}',
        dynamic: true,
        multiselect: {
          matchOn: ['ID'],
          data: '',
          callback: {
            onCancel: function (node, item) {
              $(node).removeData(`bulk_key_${field_id}`);
            },
          },
          href: window.wpApiShare.site_url + `/${listing_post_type}/{{ID}}`,
        },
        callback: {
          onClick: function (node, a, item, event) {
            let multiUserArray;
            if (node.data(`bulk_key_${field_id}`)) {
              multiUserArray = node.data(`bulk_key_${field_id}`).values;
            } else {
              multiUserArray = [];
            }
            multiUserArray.push({ value: item.ID });

            node.data(`bulk_key_${field_id}`, { values: multiUserArray });
            this.addMultiselectItemLayout(item);
            event.preventDefault();
            this.hideLayout();
            this.resetInput();
          },
          onResult: function (node, query, result, resultCount) {
            let text = window.TYPEAHEADS.typeaheadHelpText(
              resultCount,
              query,
              result,
            );
            $(`#${element_id}-result-container`).html(text);
          },
          onHideLayout: function (event, query) {
            if (!query) {
              $(`#${element_id}-result-container`).empty();
            }
          },
          onShowLayout() {},
        },
      });
    }
  });

  $('#bulk_edit_picker .dt_location_grid').each(() => {
    let field_id = 'location_grid';
    let typeaheadTotals = {};
    $.typeahead({
      input: '.js-typeahead-bulk_location_grid',
      minLength: 0,
      accent: true,
      searchOnFocus: true,
      maxItem: 20,
      dropdownFilter: [
        {
          key: 'group',
          value: 'focus',
          template: window.SHAREDFUNCTIONS.escapeHTML(
            window.wpApiShare.translations.regions_of_focus,
          ),
          all: window.SHAREDFUNCTIONS.escapeHTML(
            window.wpApiShare.translations.all_locations,
          ),
        },
      ],
      source: {
        focus: {
          display: 'name',
          ajax: {
            url:
              window.wpApiShare.root +
              'dt/v1/mapping_module/search_location_grid_by_name',
            data: {
              s: '{{query}}',
              filter: function () {
                // return window.lodash.get(window.Typeahead['.js-typeahead-location_grid'].filters.dropdown, 'value', 'all')
              },
            },
            beforeSend: function (xhr) {
              xhr.setRequestHeader('X-WP-Nonce', window.wpApiShare.nonce);
            },
            callback: {
              done: function (data) {
                if (typeof window.typeaheadTotals !== 'undefined') {
                  window.typeaheadTotals.field = data.total;
                }
                return data.location_grid;
              },
            },
          },
        },
      },
      display: 'name',
      templateValue: '{{name}}',
      dynamic: true,
      multiselect: {
        matchOn: ['ID'],
        data: '',
        callback: {
          onCancel: function (node, item) {
            $(node).removeData(`bulk_key_${field_id}`);
          },
        },
      },
      callback: {
        onClick: function (node, a, item, event) {
          // $(`#${element_id}-spinner`).addClass('active');
          node.data(`bulk_key_${field_id}`, { values: [{ value: item.ID }] });
        },
        onReady() {
          this.filters.dropdown = {
            key: 'group',
            value: 'focus',
            template: window.SHAREDFUNCTIONS.escapeHTML(
              window.wpApiShare.translations.regions_of_focus,
            ),
          };
          this.container
            .removeClass('filter')
            .find('.' + this.options.selector.filterButton)
            .html(
              window.SHAREDFUNCTIONS.escapeHTML(
                window.wpApiShare.translations.regions_of_focus,
              ),
            );
        },
        onResult: function (node, query, result, resultCount) {
          resultCount = typeaheadTotals.location_grid;
          let text = window.TYPEAHEADS.typeaheadHelpText(
            resultCount,
            query,
            result,
          );
          $('#location_grid-result-container').html(text);
        },
        onHideLayout: function () {
          $('#location_grid-result-container').html('');
        },
      },
    });
  });

  $(
    '#bulk_edit_picker .tags input, #bulk_edit_picker .multi_select input',
  ).each((key, input) => {
    let field = $(input).data('field') || 'tags';
    let field_options = window.lodash.get(
      list_settings,
      `post_type_settings.fields.${field}.default`,
      {},
    );
    $.typeahead({
      input: input,
      minLength: 0,
      maxItem: 20,
      searchOnFocus: true,
      source: {
        tags: {
          display: ['name'],
          ajax: {
            url:
              window.wpApiShare.root +
              `dt-posts/v2/${list_settings.post_type}/multi-select-values`,
            data: {
              s: '{{query}}',
              field: field,
            },
            beforeSend: function (xhr) {
              xhr.setRequestHeader('X-WP-Nonce', window.wpApiShare.nonce);
            },
            callback: {
              done: function (data) {
                return (data || []).map((tag) => {
                  let label = window.lodash.get(
                    field_options,
                    tag + '.label',
                    tag,
                  );
                  return { name: label || tag, key: tag };
                });
              },
            },
          },
        },
      },
      display: 'name',
      templateValue: '{{name}}',
      dynamic: true,
      multiselect: {
        matchOn: ['key'],
        callback: {
          onCancel: function (node, item) {
            $(node).removeData(`bulk_key_${field}`);
          },
        },
      },
      callback: {
        onClick: function (node, a, item, event) {
          let multiUserArray;
          if (node.data(`bulk_key_${field}`)) {
            multiUserArray = node.data(`bulk_key_${field}`).values;
          } else {
            multiUserArray = [];
          }
          multiUserArray.push({ value: item.key });

          node.data(`bulk_key_${field}`, { values: multiUserArray });
          this.addMultiselectItemLayout(item);
          event.preventDefault();
          this.hideLayout();
          this.resetInput();
        },
        onResult: function (node, query, result, resultCount) {
          let text = window.TYPEAHEADS.typeaheadHelpText(
            resultCount,
            query,
            result,
          );
          $(`#${field}-result-container`).html(text);
        },
        onHideLayout: function () {
          $(`#${field}-result-container`).html('');
        },
      },
    });
  });

  $('button.follow').on('click', function () {
    let following = !($(this).data('value') === 'following');
    $(this).data('value', following ? 'following' : '');
    $(this).html(following ? 'Unfollow' : 'Follow');
    $(this).toggleClass('hollow');
    let follow = { values: [{ value: current_user_id, delete: !following }] };

    let unfollow = { values: [{ value: current_user_id, delete: following }] };

    $(this).data('bulk_key_follow', follow);
    $(this).data('bulk_key_unfollow', unfollow);
  });

  $('#bulk_edit_picker input.text-input').change(function () {
    const val = $(this).val();
    let field_key = this.id.replace('bulk_', '');
    $(this).data(`bulk_key_${field_key}`, val);
  });

  $('#bulk_edit_picker .dt_textarea').change(function () {
    const val = $(this).val();
    let field_key = this.id.replace('bulk_', '');
    $(this).data(`bulk_key_${field_key}`, val);
  });

  $('#bulk_edit_picker .dt_date_picker')
    .datepicker({
      constrainInput: false,
      dateFormat: 'yy-mm-dd',
      onClose: function (date) {
        date = window.SHAREDFUNCTIONS.convertArabicToEnglishNumbers(date);

        if (!$(this).val()) {
          date = ' '; //null;
        }

        let formattedDate = window.moment.utc(date).unix();

        let field_key = this.id.replace('bulk_', '');
        $(this).data(`bulk_key_${field_key}`, formattedDate);
      },
      changeMonth: true,
      changeYear: true,
      yearRange: '1900:2050',
    })
    .each(function () {
      if (this.value && window.moment.unix(this.value).isValid()) {
        this.value = window.SHAREDFUNCTIONS.formatDate(this.value);
      }
    });

  let mcleardate = $('#bulk_edit_picker .clear-date-button');
  mcleardate.click(function () {
    let input_id = this.dataset.inputid;
    let date = null;
    // $(`#${input_id}-spinner`).addClass('active')
    let field_key = this.id.replace('bulk_', '');
    $(this).removeData(`bulk_key_${field_key}`);
    $(`#${input_id}`).val('');
  });

  $('#bulk_edit_picker select.select-field').change((e) => {
    const val = $(e.currentTarget).val();

    if (val === 'paused') {
      $('#reason-paused-options').parent().toggle();
    }

    let field_key = e.currentTarget.id.replace('bulk_', '');
    $(e.currentTarget).data(`bulk_key_${field_key}`, val);
  });

  $('#bulk_edit_picker input.number-input').on('blur', function () {
    const id = $(this).attr('id');
    const val = $(this).val();

    let field_key = this.id.replace('bulk_', '');
    $(this).data(`bulk_key_${field_key}`, val);
  });

  $('#bulk_edit_picker .dt_contenteditable').on('blur', function () {
    const id = $(this).attr('id');
    let val = $(this).html();

    let field_key = this.id.replace('bulk_', '');
    $(this).data(`bulk_key_${field_key}`, val);
  });

  $('.list-dropdown-submenu-item-link').on('click', function () {
    // Hide bulk select modals
    $('#records-table').removeClass('bulk_edit_on');

    // Close all open modals
    $('.list-dropdown-submenu-item-link').each(function () {
      let open_modals = $(this).data('modal');
      $('#' + open_modals).hide();
    });

    // Open modal for clicked menu item
    let display_modal = $(this).data('modal');
    $('#' + display_modal).show();

    // Show bulk select checkboxes if applicable
    if ($(this).data('checkboxes') === true) {
      $('#records-table').addClass('bulk_edit_on');
    }
  });

  $('.list-action-close-button').on('click', function () {
    let section = $(this).data('close');
    $(`#${section}`).hide();
  });

  /*****
   * Bulk Send App
   */
  let bulk_send_app_button = $('#bulk_send_app_submit');
  bulk_send_app_button.on('click', function (e) {
    bulk_send_app();
  });

  function bulk_send_app() {
    let subject = $('#bulk_send_app_subject').val();
    let note = $('#bulk_send_app_msg').val();

    let selected_input = jQuery(
      '.bulk_send_app.dt-radio.button-group input:checked',
    );
    if (selected_input.length < 1) {
      $('#bulk_send_app_required_selection').show();
      return;
    } else {
      $('#bulk_send_app_required_selection').hide();
    }

    let root = selected_input.data('root');
    let type = selected_input.data('type');

    let queue = [];
    $('.bulk_edit_checkbox input').each(function () {
      if (this.checked && this.id !== 'bulk_edit_master_checkbox') {
        let postId = parseInt($(this).val());
        queue.push(postId);
      }
    });

    if (queue.length < 1) {
      $('#bulk_send_app_required_elements').show();
      return;
    } else {
      $('#bulk_send_app_required_elements').hide();
    }

    $('#bulk_send_app_submit-spinner').addClass('active');

    let email_queue_link = `<a target="_blank" href="${window.wpApiShare.site_url + '/wp-admin/admin.php?page=dt_utilities&tab=background_jobs'}">See queue</a>`;

    window
      .makeRequest('POST', list_settings.post_type + '/email_magic', {
        root: root,
        type: type,
        subject: subject,
        note: note,
        post_ids: queue,
      })
      .done((data) => {
        $('#bulk_send_app_submit-spinner').removeClass('active');
        $('#bulk_send_app_submit-message').html(
          `<strong>${data.total_sent}</strong> ${list_settings.translations.sent}! ${window.wpApiShare.can_manage_dt ? email_queue_link : ''}<br><strong>${data.total_unsent}</strong> ${list_settings.translations.not_sent}`,
        );
        $('#bulk_edit_master_checkbox').prop('checked', false);
        $('.bulk_edit_checkbox input').prop('checked', false);
        bulk_edit_count();
        // window.location.reload();
      })
      .fail((e) => {
        $('#bulk_send_app_submit-spinner').removeClass('active');
        $('#bulk_send_app_submit-message').html(
          'Oops. Something went wrong! Check log.',
        );
        console.log(e);
      });
  }

  /**
   * Split By Feature
   */

  $('#split_by_current_filter_button').on('click', function () {
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
      delete default_options_filters['fields'];

      // First, always fetch all available options for given field_id.
      window.API.split_by(
        list_settings.post_type,
        field_id,
        default_options_filters,
      ).then(function (default_options) {
        // Next, execute split_by query for given filters.
        window.API.split_by(
          list_settings.post_type,
          field_id,
          split_by_filters,
        ).then(function (split_by_response) {
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
                      <span>${window.SHAREDFUNCTIONS.escapeHTML(option_id_label)}</span>
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

  /**
   * Split By Feature
   */

  /**
   * List Exports
   */

  function export_list_display(type, title, display_function) {
    let modal = null;

    // Adjust export reveal model accordingly, based on incoming type.
    switch (type) {
      case 'csv':
      case 'email':
      case 'phone': {
        modal = $('#modal-large');
        $('#modal-large-title').html(
          title +
            '<span class="loading-spinner" style="margin-left: 10px;"></span>',
        );
        break;
      }
      case 'map': {
        modal = $('#modal-full');
        break;
      }
    }

    if (modal) {
      display_function();
      $(modal).foundation('open');
    }
  }

  $('#export_csv_list').on('click', function (e) {
    export_list_display('csv', $(e.currentTarget).text(), function () {
      // Show spinners.
      const spinner = $('#modal-large-title').find('.loading-spinner');
      $(spinner).addClass('active');

      // Identify fields to be exported; ignoring hidden and private fields.
      let exporting_fields_all = [];
      let exporting_fields_visible = [];

      const magic_link_app_keys = Object.keys(
        window.list_settings.post_type_settings.magic_link_apps,
      );
      Object.keys(window.list_settings.post_type_settings.fields).forEach(
        (field_id) => {
          const field_setting =
            window.list_settings.post_type_settings.fields[field_id];
          if (
            magic_link_app_keys.includes(field_id) ||
            ((field_setting['private'] === undefined ||
              !field_setting['private']) &&
              (field_setting['hidden'] === undefined ||
                !field_setting['hidden']) &&
              !['task', 'array'].includes(field_setting['type']))
          ) {
            let setting = field_setting;
            setting['field_id'] = field_id;
            exporting_fields_all.push(setting);

            // Separately capture currently shown table fields.
            if (fields_to_show_in_table.includes(field_id)) {
              exporting_fields_visible.push(setting);
            }

            // Insert additional locations id column, if needed.
            if (['location', 'location_meta'].includes(setting['type'])) {
              let location_settings = JSON.parse(JSON.stringify(setting));
              location_settings['name'] = `${location_settings['name']} [ID]`;
              location_settings['dynamic_csv_col'] = true;
              exporting_fields_all.push(location_settings);

              // Separately capture currently shown table fields.
              if (fields_to_show_in_table.includes(field_id)) {
                exporting_fields_visible.push(location_settings);
              }
            }
          }
        },
      );

      // Sort identified fields by name into ascending order.
      exporting_fields_all.sort(function (a, b) {
        if (a.name.trim() < b.name.trim()) {
          return -1;
        }
        if (a.name.trim() > b.name.trim()) {
          return 1;
        }
        return 0;
      });

      exporting_fields_visible.sort(function (a, b) {
        if (a.show_in_table === undefined && b.show_in_table !== undefined) {
          return 1;
        }
        if (a.show_in_table !== undefined && b.show_in_table === undefined) {
          return -1;
        }
        if (parseInt(a.show_in_table) < parseInt(b.show_in_table)) {
          return -1;
        }
        if (parseInt(a.show_in_table) > parseInt(b.show_in_table)) {
          return 1;
        }
        return 0;
      });

      // Create shown table fields supporting html.
      let fields_to_show_in_table_html = ``;
      exporting_fields_visible.forEach((field) => {
        fields_to_show_in_table_html += `<span class="current-filter-list" style="padding: 4px;">${window.SHAREDFUNCTIONS.escapeHTML(field['name'])}</span>`;
      });

      // Take a two-step approach; first, display fields to be exported and on-demand, obtain records upon export request.
      let html = `<div class="grid-x">`;

      html += `
        <div class="cell">
          <label>
            <input type="radio" name="csv_exported_list_fields" value="all" checked />
            ${window.SHAREDFUNCTIONS.escapeHTML(window.list_settings['translations']['exports']['csv']['fields_msg_all'].replaceAll('%2$s', exporting_fields_all.length))}
          </label>
          <label>
            <input type="radio" name="csv_exported_list_fields" value="visible" />
            ${window.SHAREDFUNCTIONS.escapeHTML(window.list_settings['translations']['exports']['csv']['fields_msg_visible'].replaceAll('%2$s', exporting_fields_visible.length))}
          </label>
          ${fields_to_show_in_table_html}
        </div>`;

      let record_total = window.records_list['total'];
      html += `<div class="cell"><hr></div>
        <div class="cell">
            <button class="button" id="export_csv_list_download" ${record_total <= 0 ? 'disabled' : ''}>
            <i class="mdi mdi-cloud-download-outline" style="font-size: 22px; margin-right: 10px;"></i>
            <span style="font-size: 20px;">[ ${window.SHAREDFUNCTIONS.escapeHTML(record_total)} ]</span>
            </button>
        </div>
      </div>`;

      $('#modal-large-content').html(html);

      // Terminate any spinners and prepare download button event listener.
      $(spinner).removeClass('active');
      $('#export_csv_list_download').on('click', function () {
        $(spinner).addClass('active');
        $('#export_csv_list_download').prop('disabled', true);

        // Ensure to determine which field groupings to move forward with....? All or Currently Visible?
        export_csv_list_download(
          $('input[name="csv_exported_list_fields"]:checked').val() ===
            'visible'
            ? exporting_fields_visible
            : exporting_fields_all,
          function () {
            $(spinner).removeClass('active');
            $('#modal-large').foundation('close');
          },
        );
      });
    });
  });

  function export_csv_list_download(exporting_fields, callback = null) {
    const exporting_field_ids = exporting_fields.map(
      (field) => field['field_id'],
    );
    if (!exporting_field_ids.includes('name')) {
      exporting_fields.push(
        window.list_settings.post_type_settings.fields.name,
      );
    }
    const magic_link_app_keys = Object.keys(
      window.list_settings.post_type_settings.magic_link_apps,
    );

    // First retrieve all records associated with currently selected filter.
    recursively_fetch_posts(
      0,
      500,
      window.records_list['total'],
      [],
      function (posts) {
        if (posts && posts.length > 0) {
          let csv_export = [];

          // Structure csv shape to be downloaded.
          $.each(posts, function (post_idx, post) {
            let csv_row = [];
            let token_array_delimiter = ';';

            // Capture ID & Name.
            csv_row.push(post['ID']);
            csv_row.push(post['name']);

            // Proceed with extraction of remaining fields.
            $.each(exporting_fields, function (field_idx, field) {
              let field_id = field['field_id'];

              // As well as names, also ignore dynamic csv columns; which are typically populated via other means.
              if (
                !['name'].includes(field_id) &&
                (field['dynamic_csv_col'] === undefined ||
                  !field['dynamic_csv_col'])
              ) {
                // Next, extract post field value accordingly, based on field type.
                switch (field['type']) {
                  case 'text':
                  case 'number':
                  case 'boolean':
                  case 'textarea': {
                    let token = post[field_id] ? post[field_id] : '';
                    csv_row.push(token);
                    break;
                  }
                  case 'user_select': {
                    let token =
                      post[field_id] && post[field_id]['display']
                        ? post[field_id]['display']
                        : '';
                    csv_row.push(token);
                    break;
                  }
                  case 'key_select': {
                    let token =
                      post[field_id] && post[field_id]['label']
                        ? post[field_id]['label']
                        : '';
                    csv_row.push(token);
                    break;
                  }
                  case 'date':
                  case 'datetime': {
                    let token =
                      post[field_id] && post[field_id]['formatted']
                        ? post[field_id]['formatted']
                        : '';
                    csv_row.push(token);
                    break;
                  }
                  case 'multi_select': {
                    let token_array = [];
                    if (post[field_id]) {
                      $.each(post[field_id], function (cell_index, cell_value) {
                        token_array.push(field['default'][cell_value]['label']);
                      });
                    }
                    csv_row.push(token_array.join(token_array_delimiter));
                    break;
                  }
                  case 'connection': {
                    let token_array = [];
                    if (post[field_id]) {
                      $.each(post[field_id], function (cell_index, cell_value) {
                        token_array.push(cell_value['post_title']);
                      });
                    }
                    csv_row.push(token_array.join(token_array_delimiter));
                    break;
                  }
                  case 'communication_channel': {
                    let token_array = [];
                    if (post[field_id]) {
                      $.each(post[field_id], function (cell_index, cell_value) {
                        token_array.push(cell_value['value']);
                      });
                    }
                    csv_row.push(token_array.join(token_array_delimiter));
                    break;
                  }
                  case 'location':
                  case 'location_meta': {
                    let token_array = [];
                    let id_array = [];
                    if (post[field_id]) {
                      $.each(post[field_id], function (cell_index, cell_value) {
                        token_array.push(cell_value['label']);

                        // Extract id accordingly based on value shape; to be appended within dynamic csv column.
                        let grid_id = '';
                        if (cell_value['id']) {
                          grid_id = cell_value['id'];
                        } else if (cell_value['grid_id']) {
                          grid_id = cell_value['grid_id'];
                        }
                        id_array.push(grid_id);
                      });
                    }
                    csv_row.push(token_array.join(token_array_delimiter));
                    csv_row.push(id_array.join(token_array_delimiter));
                    break;
                  }
                  case 'tags': {
                    let token_array = [];
                    if (post[field_id]) {
                      $.each(post[field_id], function (cell_index, cell_value) {
                        token_array.push(cell_value);
                      });
                    }
                    csv_row.push(token_array.join(token_array_delimiter));
                    break;
                  }
                  case 'link': {
                    let token_array = [];
                    if (post[field_id]) {
                      $.each(post[field_id], function (cell_index, cell_value) {
                        if (field['default'][cell_value['type']]) {
                          let category_label =
                            field['default'][cell_value['type']]['label'];
                          let token =
                            category_label + ': ' + cell_value['value'];
                          token_array.push(token);
                        }
                      });
                    }
                    csv_row.push(token_array.join(token_array_delimiter));
                    break;
                  }
                  case 'hash':
                  case 'magic_link':
                    {
                      if (
                        magic_link_app_keys.includes(field_id) &&
                        post[field_id]
                      ) {
                        const token = post[field_id] ? post[field_id] : '';
                        const url =
                          window.wpApiShare.site_url +
                          '/' +
                          window.list_settings.post_type_settings
                            .magic_link_apps[field_id]['url_base'] +
                          '/' +
                          token;
                        csv_row.push(url);
                      }
                    }
                    break;
                  case 'task':
                  case 'array':
                  default: {
                    csv_row.push('');
                    break;
                  }
                }
              }
            });

            // Assuming counts match, assign to parent csv download array.
            if (csv_row.length === exporting_fields.length + 1) {
              csv_export.push(csv_row);
            }
          });

          // Generate export csv headers and prefix to parent csv download array.
          let csv_headers = ['ID', 'Name'].concat(
            exporting_fields
              .filter((field) => !['name'].includes(field['field_id']))
              .map((field) => field['name']),
          );
          csv_export.unshift(csv_headers);

          // Convert csv arrays into raw downloadable data.
          const csv = csv_export
            .map((row) => {
              return row.map((item) => {
                let escapeditem = item;
                //if the string contains a doublequote escape it by doubling the double quoate like "" - https://stackoverflow.com/a/769675
                if (String(item).includes('"')) {
                  escapeditem = item.replaceAll('"', '""');
                }

                return `"${escapeditem}"`;
              });
            })
            .join('\r\n');

          // Finally, automatically execute a download of generated csv data.
          let csv_download_link = document.createElement('a');
          let date = new Date();
          let year = new Intl.DateTimeFormat('en', { year: 'numeric' }).format(
            date,
          );
          let month = new Intl.DateTimeFormat('en', {
            month: 'numeric',
          }).format(date);
          let day = new Intl.DateTimeFormat('en', { day: '2-digit' }).format(
            date,
          );
          let hour = new Intl.DateTimeFormat('en', {
            hour: 'numeric',
            hour12: false,
          }).format(date);
          let minute = new Intl.DateTimeFormat('en', {
            minute: 'numeric',
          }).format(date);
          let second = new Intl.DateTimeFormat('en', {
            second: 'numeric',
          }).format(date);
          csv_download_link.download = `${year}_${month}_${day}_${hour}_${minute}_${second}_${window.list_settings.post_type}_list_export.csv`;
          csv_download_link.href =
            'data:text/csv;charset=utf-8;base64,' + window.Base64.encode(csv);
          csv_download_link.click();
          csv_download_link.remove();
        }

        // Callback to original caller...! ;)
        if (callback) {
          callback();
        }
      },
    );
  }

  function recursively_fetch_posts(
    offset,
    limit,
    total,
    posts,
    callback = null,
  ) {
    // Build query based on current filter, to be executed.
    let query = current_filter.query;
    query['offset'] = offset;
    query['limit'] = limit;
    query['fields_to_return'] = [];

    window
      .makeRequestOnPosts(
        'POST',
        `${window.list_settings.post_type}/list`,
        JSON.parse(JSON.stringify(query)),
      )
      .promise()
      .then((response) => {
        // Concat any returned posts to main parent posts array.
        posts = posts.concat(
          response && response['posts'] ? response['posts'] : [],
        );

        // Recurse if more records are available, otherwise proceed with export callback.
        if (offset + limit < total) {
          recursively_fetch_posts(
            offset + limit,
            limit,
            total,
            posts,
            callback,
          );
        } else if (callback) {
          callback(posts);
        }
      })
      .catch((error) => {
        console.log(error);
        if (callback) {
          callback(posts);
        }
      });
  }

  $('#export_bcc_email_list').on('click', function (e) {
    export_list_display('email', $(e.currentTarget).text(), function () {
      // Show spinners.
      const spinner = $('#modal-large-title').find('.loading-spinner');
      $(spinner).addClass('active');

      let html = `
        <div class="grid-x">
            <div class="cell">
               <table><tbody id="grouping-table"></tbody></table>
            </div>

            <div class="cell">
                <a onclick="jQuery('#email-list-print').toggle();"><strong>${window.SHAREDFUNCTIONS.escapeHTML(window.list_settings['translations']['exports']['bcc']['full_list'])} (<span id="list-count-full"></span>)</strong></a>
                <div class="cell" id="email-list-print" style="display:none;"></div>
            </div>
            <div class="cell">
                <a onclick="jQuery('#contacts-without').toggle();"><strong>${window.SHAREDFUNCTIONS.escapeHTML(window.list_settings['translations']['exports']['bcc']['no_addr'])} (<span id="list-count-without"></span>)</strong></a>
                <div id="contacts-without" style="display:none;"></div>
            </div>
            <div class="cell">
                <a onclick="jQuery('#contacts-with').toggle();"><strong>${window.SHAREDFUNCTIONS.escapeHTML(window.list_settings['translations']['exports']['bcc']['with_addr'])} (<span id="list-count-with"></span>)</strong></a>
                <div id="contacts-with" style="display:none;"></div>
            </div>
        </div>`;

      $('#modal-large-content').html(html);

      // Recursively fetch all filtered list posts, to be processed.
      recursively_fetch_posts(
        0,
        500,
        window.records_list['total'],
        [],
        function (posts) {
          let email_totals = [];
          let list_count = {
            with: 0,
            without: 0,
            full: 0,
          };
          let count = 0;
          let group = 0;
          let contacts_with = jQuery('#contacts-with');
          let contacts_without = jQuery('#contacts-without');

          // Generate totals.
          $.each(posts, function (i, v) {
            let has_email = false;
            if (
              typeof v.contact_email !== 'undefined' &&
              v.contact_email !== ''
            ) {
              if (typeof email_totals[group] === 'undefined') {
                email_totals[group] = [];
              }
              let non_empty_values = v.contact_email.filter((val) => val.value);
              non_empty_values.forEach((vv) => {
                let email = window.SHAREDFUNCTIONS.escapeHTML(vv.value);
                if (validate_email_address(email)) {
                  email_totals[group].push(email);
                  count++;
                  list_count['full']++;
                  has_email = true;
                } else {
                  console.log(`Invalid Email Format: ${email}`);
                }
              });
              if (count > 50) {
                group++;
                count = 0;
              }
              if (non_empty_values.length > 1) {
                contacts_with.append(
                  `<a href="${window.SHAREDFUNCTIONS.escapeHTML(window.wpApiShare.site_url)}/contacts/${window.SHAREDFUNCTIONS.escapeHTML(v.ID)}">${window.SHAREDFUNCTIONS.escapeHTML(v.post_title)}</a><br>`,
                );
                list_count['with']++;
              }
            }
            if (!has_email) {
              contacts_without.append(
                `<a href="${window.SHAREDFUNCTIONS.escapeHTML(window.wpApiShare.site_url)}/contacts/${window.SHAREDFUNCTIONS.escapeHTML(v.ID)}">${window.SHAREDFUNCTIONS.escapeHTML(v.post_title)}</a><br>`,
              );
              list_count['without']++;
            }
          });

          // Update count findings.
          let list_print = jQuery('#email-list-print');
          let all_emails = [];
          $.each(email_totals, function (index, values) {
            all_emails.push(values.join(', '));
          });
          list_print.append(
            window.SHAREDFUNCTIONS.escapeHTML(all_emails.join(', ')),
          );

          jQuery('#list-count-with').html(list_count['with']);
          jQuery('#list-count-without').html(list_count['without']);
          jQuery('#list-count-full').html(list_count['full']);

          // Generate links.
          let email_links = [];
          group = 0;

          $.each(posts, function (i, v) {
            if (
              typeof v.contact_email !== 'undefined' &&
              v.contact_email !== ''
            ) {
              if (typeof email_links[group] === 'undefined') {
                email_links[group] = [];
              }
              $.each(v.contact_email, function (ii, vv) {
                let email = window.SHAREDFUNCTIONS.escapeHTML(vv.value);
                if (validate_email_address(email)) {
                  email_links[group].push(email);
                }
              });
              if (email_links[group].length > 50) {
                group++;
              }
            }
          });

          // loop 50 each
          let grouping_table = $('#grouping-table');
          let email_strings = [];
          $.each(email_links, function (index, values) {
            index++;
            email_strings = [];
            email_strings = window.SHAREDFUNCTIONS.escapeHTML(
              values.join(', '),
            );
            email_strings.replace(/,/g, ', ');

            grouping_table.append(`
            <tr><td style="vertical-align:top; width:50%;"><a href="mailto:?subject=group${index}&bcc=${email_strings}" id="group-link-${index}" class="button expanded export-link-button">${window.SHAREDFUNCTIONS.escapeHTML(window.list_settings['translations']['exports']['bcc']['open_email'])} ${index}</a></td>
            <td><a onclick="jQuery('#group-addresses-${index}').toggle()">${window.SHAREDFUNCTIONS.escapeHTML(window.list_settings['translations']['exports']['bcc']['show_group_addrs'])}</a> <p style="display:none;overflow-wrap: break-word;" id="group-addresses-${index}">${email_strings.replace(/,/g, ', ')}</p></td></tr>
          `);
          });
          grouping_table.append(`
            <tr><td style="vertical-align:top; text-align:center; width:50%;"><a class="button expanded export-link-button" id="open_all">${window.SHAREDFUNCTIONS.escapeHTML(window.list_settings['translations']['exports']['bcc']['open_all'])}</a></td><td></td></tr>
        `);

          $('.export-link-button').on('click', function () {
            $(this).addClass('warning');
          });
          $('#open_all').on('click', function () {
            $('.export-link-button').each(function (i, v) {
              document.getElementById(v.id).click();
            });
          });

          // Hide spinners.
          $(spinner).removeClass('active');
        },
      );
    });
  });

  function validate_email_address(email) {
    const regex =
      /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
    return regex.test(email);
  }

  $('#export_phone_list').on('click', function (e) {
    export_list_display('phone', $(e.currentTarget).text(), function () {
      // Show spinners.
      const spinner = $('#modal-large-title').find('.loading-spinner');
      $(spinner).addClass('active');

      let html = `
        <div class="grid-x">
            <a onclick="jQuery('#email-list-print').toggle();"><strong>${window.SHAREDFUNCTIONS.escapeHTML(window.list_settings['translations']['exports']['phone']['full_list'])} (<span id="list-count-full"></span>)</strong></a>
            <div class="cell" id="email-list-print"></div>
        </div>
        <hr>
        <div class="grid-x">
            <div class="cell">
                <a onclick="jQuery('#contacts-without').toggle();"><strong>${window.SHAREDFUNCTIONS.escapeHTML(window.list_settings['translations']['exports']['phone']['no_phone'])} (<span id="list-count-without"></span>)</strong></a>
                <div id="contacts-without" style="display:none;"></div>
            </div>
            <div class="cell">
                <a onclick="jQuery('#contacts-with').toggle();"><strong>${window.SHAREDFUNCTIONS.escapeHTML(window.list_settings['translations']['exports']['phone']['with_phone'])} (<span id="list-count-with"></span>)</strong></a>
                <div id="contacts-with" style="display:none;"></div>
            </div>
        </div>`;

      $('#modal-large-content').html(html);

      // Recursively fetch all filtered list posts, to be processed.
      recursively_fetch_posts(
        0,
        500,
        window.records_list['total'],
        [],
        function (posts) {
          let phone_list = [];
          let list_count = {
            with: 0,
            without: 0,
            full: 0,
          };
          let group = 0;
          let contacts_with = jQuery('#contacts-with');
          let contacts_without = jQuery('#contacts-without');

          // Generate totals.
          $.each(posts, function (i, v) {
            let has_phone = false;
            if (
              typeof v.contact_phone !== 'undefined' &&
              v.contact_phone !== ''
            ) {
              if (typeof phone_list[group] === 'undefined') {
                phone_list[group] = [];
              }
              let non_empty_values = v.contact_phone.filter((val) => val.value);
              non_empty_values.forEach((vv) => {
                phone_list[group].push(vv.value);
                list_count['full']++;
                has_phone = true;
              });
              if (non_empty_values.length > 1) {
                contacts_with.append(
                  `<a  href="${window.SHAREDFUNCTIONS.escapeHTML(window.wpApiShare.site_url)}/contacts/${window.SHAREDFUNCTIONS.escapeHTML(v.ID)}">${window.SHAREDFUNCTIONS.escapeHTML(v.post_title)}</a><br>`,
                );
                list_count['with']++;
              }
              if (phone_list.length > 50) {
                group++;
              }
            }
            if (!has_phone) {
              contacts_without.append(
                `<a  href="${window.SHAREDFUNCTIONS.escapeHTML(window.wpApiShare.site_url)}/contacts/${window.SHAREDFUNCTIONS.escapeHTML(v.ID)}">${window.SHAREDFUNCTIONS.escapeHTML(v.post_title)}</a><br>`,
              );
              list_count['without']++;
            }
          });

          // Update count findings.
          let list_print = jQuery('#email-list-print');
          let all_numbers = [];
          $.each(phone_list, function (index, values) {
            all_numbers = all_numbers.concat(values);
          });
          list_print.append(
            window.SHAREDFUNCTIONS.escapeHTML(all_numbers.join(', ')),
          );

          // console.log(list_count)
          jQuery('#list-count-with').html(list_count['with']);
          jQuery('#list-count-without').html(list_count['without']);
          jQuery('#list-count-full').html(list_count['full']);

          // Hide spinners.
          $(spinner).removeClass('active');
        },
      );
    });
  });

  $('.export_map_list').on('click', function (e) {
    if (window.list_settings['translations']['exports']['map']['mapbox_key']) {
      const title = $(e.currentTarget).text();
      export_list_display('map', title, function () {
        // Generate modal html.
        let html = `
        <span class="section-header"> ${window.SHAREDFUNCTIONS.escapeHTML(title)} | ${window.SHAREDFUNCTIONS.escapeHTML(window.list_settings['translations']['exports']['map']['mapped_locations'])}: <span id="mapped" class="loading-spinner active"></span> | ${window.SHAREDFUNCTIONS.escapeHTML(window.list_settings['translations']['exports']['map']['without_locations'])}: <span id="unmapped" class="loading-spinner active"></span> </span><br><br>
        <div id="dynamic-styles"></div>
        <div class="grid-x">
          <div class="cell medium-9" id="export_map_container">
            <div id="map-wrapper">
                <div id='map'></div>
            </div>
          </div>
          <div class="cell medium-3" id="export_map_sidebar_menu">
            <!-- details panel -->
            <h3 style="margin-left: 10px;">${esc(window.list_settings.translations.exports.map['records_on_zoomed_map'])}</h3>
            <button id="export_map_sidebar_menu_open_but" class="button" style="min-width: 100%; margin-left: 10px;">${esc(window.list_settings.translations.exports.map['open_zoomed_map'])}</button>
            <div id="export_map_sidebar_menu_details_panel" style="margin-left: 10px; max-height: 500px; overflow-y: scroll;">
              <table id="export_map_sidebar_menu_details_panel_table"></table>
            </div>
          </div>
        </div>`;

        $('#modal-full-content').html(html);

        // Show spinners.
        const spinner = $('#modal-full').find('.loading-spinner');
        $(spinner).addClass('active');

        // Insert dynamic styles.
        let map_content = jQuery('#dynamic-styles');
        map_content.append(`
            <style>
                #map-wrapper {
                    height: ${window.innerHeight - 100}px !important;
                    position:relative;
                }
                #map {
                    height: ${window.innerHeight - 100}px !important;
                }
            </style>
          `);

        // Recursively fetch all filtered list posts, to be processed.
        recursively_fetch_posts(
          0,
          500,
          window.records_list['total'],
          [],
          function (posts) {
            window.mapboxgl.accessToken =
              window.list_settings['translations']['exports']['map'][
                'mapbox_key'
              ];
            var map = new window.mapboxgl.Map({
              container: 'map',
              style: 'mapbox://styles/mapbox/light-v10',
              center: [-30, 20],
              minZoom: 1,
              zoom: 2,
            });

            // Handle open zoomed map records button clicks.
            $('#export_map_sidebar_menu_open_but').on('click', function (e) {
              e.preventDefault();

              // Obtain handle to current map bounds.
              const bounds = map.getBounds();
              const bounds_ne = bounds.getNorthEast();
              const bounds_sw = bounds.getSouthWest();

              // Build query based on current filter and identified map bounds.
              let query = current_filter.query;
              query['offset'] = 0;
              query['limit'] = 500;
              query['fields_to_return'] = [];
              query['map_bounds'] = {
                ne: {
                  lat: bounds_ne['lat'],
                  lng: bounds_ne['lng'],
                },
                sw: {
                  lat: bounds_sw['lat'],
                  lng: bounds_sw['lng'],
                },
              };

              window
                .makeRequestOnPosts(
                  'POST',
                  `${window.list_settings.post_type}/list`,
                  JSON.parse(JSON.stringify(query)),
                )
                .promise()
                .then((response) => {
                  if (response?.posts?.length > 0) {
                    items = response.posts || [];
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
                    $('#load-more').toggle(
                      items.length !== parseInt(response.total),
                    );
                    let result_text = list_settings.translations.txt_info
                      .replace('_START_', items.length)
                      .replace('_TOTAL_', response.total);
                    $('.filter-result-text').html(result_text);
                    build_table(items);

                    // Generate corresponding custom filter.
                    reset_split_by_filters();
                    add_custom_filter(
                      esc(
                        window.list_settings.translations.exports.map[
                          'filter_name'
                        ],
                      ),
                      'custom-filter',
                      query,
                      new_filter_labels,
                      false,
                    );

                    // Capture custom filter label and refresh ui.
                    current_filter['labels'] = [
                      {
                        id: 'map',
                        name: esc(
                          window.list_settings.translations.exports.map[
                            'filter_label'
                          ],
                        ),
                      },
                    ];
                    setup_current_filter_labels();

                    // Persist updated custom filter url parameters.
                    update_url_query(current_filter);

                    // Reset vertical scrollbar to start position.
                    window.setTimeout(function () {
                      $(window).scrollTop(0);
                    }, 0);

                    // Close modal window to display updated records list.
                    $('#modal-full').foundation('close');
                  } else {
                    alert(
                      `${esc(window.list_settings.translations.exports.map['no_records_on_zoomed_map_alert'])}`,
                    );
                  }
                })
                .catch((error) => {
                  console.log(error);
                });
            });

            // disable map rotation using right click + drag
            map.dragRotate.disable();
            map.touchZoomRotate.disableRotation();

            // load sources
            map.on('load', function () {
              let features = [];
              let mapped = 0;
              let unmapped = 0;
              $.each(posts, function (i, v) {
                if (typeof v.location_grid_meta !== 'undefined') {
                  features.push({
                    type: 'Feature',
                    geometry: {
                      type: 'Point',
                      coordinates: [
                        v.location_grid_meta[0].lng,
                        v.location_grid_meta[0].lat,
                      ],
                    },
                    properties: {
                      id: v.ID,
                      post_type: v.post_type,
                      title: v.post_title,
                      label: v.location_grid_meta[0].label,
                    },
                  });
                  mapped++;
                } else {
                  unmapped++;
                }
              });

              $('#mapped').html('(' + mapped + ')');
              $('#unmapped').html('(' + unmapped + ')');

              let geojson = {
                type: 'FeatureCollection',
                features: features,
              };

              map.addSource('dt-records-source', {
                type: 'geojson',
                data: geojson,
                cluster: true,
                clusterMaxZoom: 14,
                clusterRadius: 50,
              });

              const layer_color =
                '#' +
                ((Math.random() * 0xffffff) << 0).toString(16).padStart(6, '0');
              map.addLayer({
                id: 'dt-records-clusters',
                type: 'circle',
                source: 'dt-records-source',
                filter: ['has', 'point_count'],
                paint: {
                  'circle-color': [
                    'step',
                    ['get', 'point_count'],
                    layer_color,
                    100,
                    layer_color,
                    750,
                    layer_color,
                  ],
                  'circle-radius': [
                    'step',
                    ['get', 'point_count'],
                    20,
                    100,
                    30,
                    750,
                    40,
                  ],
                  'circle-stroke-width': 2,
                  'circle-stroke-color': '#fff',
                },
              });

              map.addLayer({
                id: 'dt-records-clusters-count',
                type: 'symbol',
                source: 'dt-records-source',
                filter: ['has', 'point_count'],
                layout: {
                  'text-field': '{point_count_abbreviated}',
                  'text-font': ['DIN Offc Pro Medium', 'Arial Unicode MS Bold'],
                  'text-size': 12,
                },
              });

              map.addLayer({
                id: 'dt-records-unclustered-points',
                type: 'circle',
                source: 'dt-records-source',
                filter: ['!', ['has', 'point_count']],
                paint: {
                  'circle-color': layer_color,
                  'circle-radius': 5,
                  'circle-stroke-width': 2,
                  'circle-stroke-color': '#fff',
                },
              });

              map.on('click', () => {
                render_map_feature_details(
                  map,
                  map.queryRenderedFeatures({
                    layers: [
                      'dt-records-clusters',
                      'dt-records-unclustered-points',
                    ],
                  }),
                );
              });
              map.on('dragend', () => {
                render_map_feature_details(
                  map,
                  map.queryRenderedFeatures({
                    layers: [
                      'dt-records-clusters',
                      'dt-records-unclustered-points',
                    ],
                  }),
                );
              });
              map.on('zoomend', () => {
                render_map_feature_details(
                  map,
                  map.queryRenderedFeatures({
                    layers: [
                      'dt-records-clusters',
                      'dt-records-unclustered-points',
                    ],
                  }),
                );
              });

              map.on('mouseenter', 'dt-records-clusters', () => {
                map.getCanvas().style.cursor = 'pointer';
              });

              map.on('mouseenter', 'dt-records-clusters-count', () => {
                map.getCanvas().style.cursor = 'pointer';
              });

              map.on('mouseenter', 'dt-records-unclustered-points', () => {
                map.getCanvas().style.cursor = 'pointer';
              });

              map.on('mouseleave', 'dt-records-clusters', () => {
                map.getCanvas().style.cursor = '';
              });

              map.on('mouseleave', 'dt-records-clusters-count', () => {
                map.getCanvas().style.cursor = '';
              });

              map.on('mouseleave', 'dt-records-unclustered-points', () => {
                map.getCanvas().style.cursor = '';
              });

              // To avoid unwanted exceptions, ensure valid features are available, prior to rendering.
              if (geojson.features && geojson.features.length > 0) {
                var bounds = new window.mapboxgl.LngLatBounds();
                geojson.features.forEach(function (feature) {
                  if (
                    feature.geometry.coordinates &&
                    !feature.geometry.coordinates.includes(undefined)
                  ) {
                    bounds.extend(feature.geometry.coordinates);
                  }
                });
                map.fitBounds(bounds);

                // Display initial feature details.
                const details_panel = $(
                  '#export_map_sidebar_menu_details_panel',
                );
                const map_height = $(map.getContainer()).height();
                $(details_panel).css('height', map_height - 100 + 'px');
                $(details_panel).css('min-height', map_height - 100 + 'px');
                $(details_panel).css('max-height', map_height - 100 + 'px');
                render_map_feature_details(
                  map.queryRenderedFeatures({
                    layers: [
                      'dt-records-clusters',
                      'dt-records-unclustered-points',
                    ],
                  }),
                );
              }

              // Hide spinners.
              $(spinner).removeClass('active');
            });
          },
        );
      });
    }
  });

  function render_map_feature_details(map, features) {
    if (features) {
      // First, reset details html.
      const details_table = $('#export_map_sidebar_menu_details_panel_table');
      $(details_table).html(`<tbody></tbody>`);

      // Next, proceeding in display points; handling each accordingly based on parent layer.
      features.forEach(function (feature) {
        if (feature?.layer?.id === 'dt-records-unclustered-points') {
          let feature_html = render_map_feature_details_point_html(feature);
          if (feature_html) {
            $(details_table).find(`tbody`).append(feature_html);
          }
        } else if (feature?.layer?.id === 'dt-records-clusters') {
          render_map_feature_details_for_clusters(map, feature);
        }
      });
    }
  }

  function render_map_feature_details_for_clusters(map, cluster) {
    if (cluster?.source) {
      const cluster_source = map.getSource(cluster.source);
      const cluster_id = cluster?.properties.cluster_id;
      const point_count = cluster?.properties.point_count;

      if (cluster_source && cluster_id && point_count) {
        // Get all points under given cluster.
        cluster_source.getClusterLeaves(
          cluster_id,
          point_count,
          0,
          function (err, features) {
            // Ensure feature is a valid sought after record.
            features.forEach(function (feature) {
              let feature_html = render_map_feature_details_point_html(feature);
              if (feature_html) {
                $('#export_map_sidebar_menu_details_panel_table')
                  .find(`tbody`)
                  .append(feature_html);
              }
            });
          },
        );
      }
    }
  }

  function render_map_feature_details_point_html(feature) {
    if (
      feature?.properties?.id &&
      feature?.properties?.post_type &&
      feature?.properties?.title &&
      feature?.properties?.label
    ) {
      return `<tr><td><a target="_blank" href="${window.SHAREDFUNCTIONS.escapeHTML(window.wpApiShare.site_url)}/${window.SHAREDFUNCTIONS.escapeHTML(feature?.properties?.post_type)}/${window.SHAREDFUNCTIONS.escapeHTML(feature?.properties?.id)}">${window.SHAREDFUNCTIONS.escapeHTML(feature?.properties?.title)}</a> <span style="color: #808080;">${window.SHAREDFUNCTIONS.escapeHTML(feature?.properties?.label)}</span></td></tr>`;
    }

    return null;
  }

  /**
   * List Exports
   */
})(window.jQuery, window.list_settings, window.Foundation);
