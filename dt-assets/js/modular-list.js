"use strict";
(function($, list_settings, Foundation) {
  let selected_filters = $("#selected-filters")
  let new_filter_labels = []
  let custom_filters = []
  let filter_to_save = "";
  let filter_to_delete = "";
  let filterToEdit = "";
  let filter_accordions = $('#list-filter-tabs')
  let currentFilters = $("#current-filters")
  let cookie = window.SHAREDFUNCTIONS.getCookie("last_view");
  let cached_filter
  let get_records_promise = null
  let loading_spinner = $("#list-loading-spinner")
  let old_filters = JSON.stringify(list_settings.filters)
  let table_header_row = $('.js-list thead .sortable th')

  let items = []
  try {
    cached_filter = JSON.parse(cookie)
  } catch (e) {
    cached_filter = {}
  }
  let current_filter = {
    query:{},
  }
  if ( cached_filter && !_.isEmpty(cached_filter)){
    if ( cached_filter.type === "custom_filter" ){
      add_custom_filter(cached_filter.name, "default", cached_filter.query, cached_filter.labels, false)
    }
    current_filter = cached_filter
  }

  setup_filters()
  let fields_to_show_in_table = [];
  _.forOwn( list_settings.post_type_settings.fields, (field_settings, field_key)=> {
    if (_.get(field_settings, 'show_in_table')===true) {
      fields_to_show_in_table.push(field_key)
    }
  })

  //open the filter tabs
  $(`#list-filter-tabs [data-id='${_.escape( current_filter.tab )}'] a`).click()
  if ( current_filter.ID ){
    $(`.is-active input[name=view][data-id="${_.escape( current_filter.ID )}"].js-list-view`).prop('checked', true);
  } else {
    $('#list-filter-tabs .accordion-item a')[0].click()
    $($('.js-list-view')[0]).prop('checked', true)
  }
  get_records_for_current_filter()



  $(document).on('change', '.js-list-view', () => {
    get_records_for_current_filter()
  });

  function get_records_for_current_filter(){
    let checked = $(".js-list-view:checked")
    let current_view = checked.val()
    let filter_id = checked.data("id") || current_view || ""
    if ( current_view === "custom_filter" ){
      let filterId = checked.data("id")
      current_filter = _.find(custom_filters, {ID:filterId})
      current_filter.type = current_view
    } else {
      current_filter = _.find(list_settings.filters.filters, {ID:filter_id}) || _.find(list_settings.filters.filters, {ID:filter_id.toString()}) || current_filter
      current_filter.type = 'default'
      current_filter.labels = current_filter.labels || [{ id:filter_id, name:current_filter.name}]
    }
    if ( Array.isArray(current_filter.query) ){
      current_filter.query = {}; //make sure query is an object instead of an array.
    }

    get_records()
  }

  function setup_filters(){
    if ( !list_settings.filters.tabs){
      return;
    }
    let selected_tab = $('.accordion-item.is-active').data('id');
    let selected_filter = $(".js-list-view:checked").data('id')
    let html = ``;
    list_settings.filters.tabs.forEach( tab =>{
      html += `
      <li class="accordion-item" data-accordion-item data-id="${_.escape(tab.key)}">
        <a href="#" class="accordion-title">
          ${_.escape(tab.label)}
          <span class="tab-count-span" data-tab="${_.escape(tab.key)}">
              ${tab.count || tab.count >= 0 ? `(${_.escape(tab.count)})`: ``} 
          </span>
        </a>
        <div class="accordion-content" data-tab-content>
          <div class="list-views">
            ${  list_settings.filters.filters.map( filter =>{
              if (filter.tab===tab.key && filter.tab !== 'custom') {
                return `
                      <label class="list-view" style="${ filter.subfilter ? 'margin-left:15px' : ''}">
                        <input type="radio" name="view" value="${_.escape(filter.ID)}" data-id="${_.escape(filter.ID)}" class="js-list-view" autocomplete="off">
                        <span id="total_filter_label">${_.escape(filter.name)}</span>
                        <span class="list-view__count js-list-view-count" data-value="${_.escape(filter.ID)}">${_.escape(filter.count )}</span>
                      </label>
                      `
              }
            }).join('')}
          </div>
        </div>
      </li>
      `
    } )
    filter_accordions.html(html)

    let saved_filters_list = $(`#list-filter-tabs [data-id='custom'] .list-views`)
    saved_filters_list.empty()
    if ( list_settings.filters.filters.filter(t=>t.tab==='custom').length === 0 ) {
      saved_filters_list.html(`<span>${_.escape(list_settings.translations.empty_custom_filters)}</span>`)
    }
    list_settings.filters.filters.filter(t=>t.tab==='custom').forEach(filter=>{
      if ( filter && filter.visible === ''){
        return
      }
      let delete_filter = $(`<span style="float:right" data-filter="${_.escape( filter.ID )}">
        <img style="padding: 0 4px" src="${window.wpApiShare.template_dir}/dt-assets/images/trash.svg">
      </span>`)
      delete_filter.on("click", function () {
        $(`.delete-filter-name`).html(filter.name)
        $('#delete-filter-modal').foundation('open');
        filter_to_delete = filter.ID;
      })
      let edit_filter = $(`<span style="float:right" data-filter="${_.escape( filter.ID )}">
          <img style="padding: 0 4px" src="${window.wpApiShare.template_dir}/dt-assets/images/edit.svg">
      </span>`)
      edit_filter.on("click", function () {
        edit_saved_filter( filter )
        filterToEdit = filter.ID;
      })
      let filterName =  `<span class="filter-list-name" data-filter="${_.escape( filter.ID )}">${_.escape( filter.name )}</span>`
      const radio = $(`<input name='view' class='js-list-view' autocomplete='off' data-id="${_.escape( filter.ID )}" >`)
      .attr("type", "radio")
      .val("saved-filters")
      .on("change", function() {
      });
      saved_filters_list.append(
        $("<div>").append(
          $("<label>")
          .css("cursor", "pointer")
          .addClass("js-filter-checkbox-label")
          .data("filter-value", status)
          .append(radio)
          .append(filterName)
          .append(delete_filter)
          .append(edit_filter)
        )
      )
    })
    new Foundation.Accordion(filter_accordions, {
      slideSpeed: 100,
      allowAllClosed: true
    });
    if ( selected_tab ){
      $(`#list-filter-tabs [data-id='${_.escape( selected_tab )}'] a`).click()
    }
    if ( selected_filter ){
      $(`[data-id="${_.escape( selected_filter )}"].js-list-view`).prop('checked', true);
    }
  }

  let getFilterCountsPromise = null
  let get_filter_counts = ()=>{
    if ( getFilterCountsPromise && _.get( getFilterCountsPromise, "readyState") !== 4 ){
      getFilterCountsPromise.abort()
    }
    getFilterCountsPromise = $.ajax({
      url: `${window.wpApiShare.root}dt/v1/users/get_filters?post_type=${list_settings.post_type}&force_refresh=1`,
      beforeSend: function (xhr) {
        xhr.setRequestHeader('X-WP-Nonce', window.wpApiShare.nonce);
      }
    })
    getFilterCountsPromise.then(filters=>{
      if ( old_filters !== JSON.stringify(filters) ){
        list_settings.filters = filters
        setup_filters()
      }
    }).catch(err => {
      if ( _.get( err, "statusText" ) !== "abort" ){
        console.error(err)
      }
    })
  }
  get_filter_counts()


  function setup_current_filter_labels() {
    let html = ""
    let filter = current_filter
    if (filter && filter.labels){
      filter.labels.forEach(label=>{
        html+= `<span class="current-filter ${_.escape( label.field )}">${_.escape( label.name )}</span>`
      })
    } else {
      let query = filter.query
      _.forOwn( query, query_key=> {
        if (Array.isArray(query[query_key])) {

          query[query_key].forEach(q => {

            html += `<span class="current-filter ${_.escape( query_key )}">${_.escape( q )}</span>`
          })
        } else {
          html += `<span class="current-filter search">${_.escape( query[query_key] )}</span>`
        }
      })
    }

    if ( filter.query.sort ){
      let sortLabel = filter.query.sort
      if ( sortLabel.includes('last_modified') ){
        sortLabel = list_settings.translations.date_modified
      } else if (  sortLabel.includes('post_date') ) {
        sortLabel = list_settings.translations.creation_date
      } else  {
        //get label for table header
        sortLabel = $(`.sortable [data-id="${_.escape( sortLabel.replace('-', '') )}"]`).text()
      }
      html += `<span class="current-filter" data-id="sort">
          ${_.escape( list_settings.translations.sorting_by )}: ${_.escape( sortLabel )}
      </span>`
    }
    currentFilters.html(html)
  }

  
  let sort_field = _.get( current_filter, "query.sort", "name" )
  //reset sorting in table header
  table_header_row.removeClass("sorting_asc")
  table_header_row.removeClass("sorting_desc")
  let header_cell = $(`.js-list thead .sortable th[data-id="${_.escape( sort_field.replace("-", "") )}"]`)
  header_cell.addClass(`sorting_${ sort_field.startsWith('-') ? 'desc' : 'asc'}`)
  table_header_row.data("sort", '')
  header_cell.data("sort", 'asc')
  
  $('.js-sort-by').on("click", function () {
    table_header_row.removeClass("sorting_asc")
    table_header_row.removeClass("sorting_desc")
    let dir = $(this).data('order')
    let field = $(this).data('field')
    get_records( 0, (dir === "asc" ? "" : '-') + field )
  })

  //sort the table by clicking the header
  $('.js-list th').on("click", function () {
    let id = $(this).data('id')
    let sort = $(this).data('sort')
    table_header_row.removeClass("sorting_asc")
    table_header_row.removeClass("sorting_desc")
    table_header_row.data("sort", '')
    if ( !sort || sort === 'desc' ){
      $(this).data('sort', 'asc')
      $(this).addClass("sorting_asc")
      $(this).removeClass("sorting_desc")
    } else {
      $(this).data('sort', 'desc')
      $(this).removeClass("sorting_asc")
      $(this).addClass("sorting_desc")
      id = `-${id}`
    }
    get_records(0, id)
  })

  let build_table = (records)=>{
    let table_rows = ``

    records.forEach( ( record, index )=>{
      let row_fields_html = ''
      _.forOwn( list_settings.post_type_settings.fields, (field_settings, field_key)=>{
        if ( _.get( field_settings, 'show_in_table' ) === true ) {
          let field_value = _.get( record, field_key, false )
          let values_html = '';
          if ( field_value !== false ) {
            if (field_settings.type === 'text') {
              values_html = _.escape(field_value)
            } else if (field_settings.type === 'date') {
              values_html = _.escape(field_value.formatted)
            } else if (field_settings.type === 'key_select') {
              values_html = _.escape(field_value.label)
            } else if (field_settings.type === 'multi_select') {
              values_html = field_value.map(v => {
                return `<li>${_.escape(_.get(field_settings, `default[${v}].label`, v))}</li>`;
              }).join('')
            } else if ( field_settings.type === "location" ){
              values_html = field_value.map(v => {
                return `<li>${_.escape( v.label )}</li>`;
              }).join('')
            }
          }

          row_fields_html += `
            <td>
              <ul style="margin: 0; list-style: none">
                ${values_html}
              </ul>
            </td>
          `
        }
      })

      table_rows += `<tr>
        <td>${index+1}.</td>
        <td><a href="${ _.escape( record.permalink ) }">${ _.escape( record.post_title ) }</a></td>
        ${ row_fields_html }
      `
    })
    if ( records.length === 0 ){
      table_rows = `<tr><td colspan="10">${_.escape(list_settings.translations.empty_list)}</td></tr>`
    }

    let table_html = `
      ${table_rows}
    `
    $('#table-content').html(table_html)
  }


  function get_records( offset = 0, sort = null ){
    loading_spinner.addClass("active")
    let query = current_filter.query

    document.cookie = `last_view=${JSON.stringify(current_filter)}`
    if ( offset ){
      query["offset"] = offset
    }
    if ( sort ){
      query.sort = sort
      query.offset = 0
    }
    if ( get_records_promise && _.get(get_records_promise, "readyState") !== 4){
      get_records_promise.abort()
    }
    query.fields_to_return = fields_to_show_in_table
    get_records_promise = window.makeRequestOnPosts( 'GET', `${list_settings.post_type}`, JSON.parse(JSON.stringify(query)))
    get_records_promise.then(response=>{
      if (offset){
        items = _.unionBy(items, response.posts || [], "ID")
      } else  {
        items = response.posts || []
      }
      $('#load-more').toggle(items.length !== parseInt( response.total ))
      let result_text = list_settings.translations.txt_info.replace("_START_", items.length).replace("_TOTAL_", response.total)
      $('.filter-result-text').html(result_text)
      build_table(items)
      setup_current_filter_labels()
      loading_spinner.removeClass("active")
    }).catch(err => {
      loading_spinner.removeClass("active")
      if ( _.get( err, "statusText" ) !== "abort" ) {
        console.error(err)
      }
    })
  }

  $('#load-more').on('click', function () {
    get_records( items.length )
  })


  /**
   * Modal options
   */


  //add the new filter in the filters list
  function add_custom_filter(name, type, query, labels, load_records = true) {
    query = query || current_filter.query
    let ID = new Date().getTime() / 1000;
    current_filter = {ID, type, name: _.escape( name ), query:JSON.parse(JSON.stringify(query)), labels:labels}
    custom_filters.push(JSON.parse(JSON.stringify(current_filter)))

    let save_filter = $(`<a style="float:right" data-filter="${_.escape( ID.toString() )}">
        ${_.escape( list_settings.translations.save )}
    </a>`).on("click", function () {
      $("#filter-name").val(name)
      $('#save-filter-modal').foundation('open');
      filter_to_save = ID;
    })
    let filterRow = $(`<label class='list-view ${_.escape( ID.toString() )}'>`).append(`
      <input type="radio" name="view" value="custom_filter" data-id="${_.escape( ID.toString() )}" class="js-list-view" checked autocomplete="off">
        ${_.escape( name )}
    `).append(save_filter)
    $(".custom-filters").append(filterRow)
    $(".custom-filters input").on("change", function () {
      get_records_for_current_filter()
    })
    if ( load_records ){
      get_records_for_current_filter()
    }
  }
  let get_custom_filter_search_query = ()=>{
    let search_query = {}
    let fields_filtered = new_filter_labels.map(f=>f.field)
    fields_filtered.forEach(field=>{
      search_query[field] =[]
      let type = _.get(list_settings, `post_type_settings.fields.${field}.type` )
      if ( type === "connection" || type === "user_select" ){
        search_query[field] = _.map(_.get(Typeahead[`.js-typeahead-${field}`], "items"), "ID")
      }  if ( type === "multi_select" ){
        search_query[field] = _.map(_.get(Typeahead[`.js-typeahead-${field}`], "items"), "key")
      } if ( type === "location" ){
        search_query[field] = _.map( _.get(Typeahead[`.js-typeahead-${field}`], "items"), 'ID')
      } else if ( type === "date" || field === "created_on" ) {
        search_query[field] = {}
        let start = $(`.dt_date_picker[data-field="${field}"][data-delimit="start"]`).val()
        if ( start ){
          search_query[field]["start"] = start
        }
        let end = $(`.dt_date_picker[data-field="${field}"][data-delimit="end"]`).val()
        if ( end ){
          search_query[field]["end"]  = end
        }
      } else {
        $(`#${field}-options input:checked`).each(function(){
          search_query[field].push($(this).val())
        })
      }
    })
    return search_query
  }
  $("#confirm-filter-records").on("click", function () {
    let search_query = get_custom_filter_search_query()
    let filterName = _.escape( $('#new-filter-name').val() )
    add_custom_filter( filterName || "Custom Filter", "custom-filter", search_query, new_filter_labels)
  })


  let load_multi_select_typeaheads = async function load_multi_select_typeaheads() {
    for (let input of $(".multi_select .typeahead__query input")) {
      let field = $(input).data('field')
      let typeahead_name = `.js-typeahead-${field}`

      if (window.Typeahead[typeahead_name]) {
        return
      }

      let source_data =  { data: [] }
      let field_options = _.get(list_settings, `post_type_settings.fields.${field}.default`, {})
      if ( Object.keys(field_options).length > 0 ){
        _.forOwn(field_options, (val, key)=>{
          if ( !val.deleted ){
            source_data.data.push({
              key: key,
              name:key,
              value: val.label || key
            })
          }
        })
      } else {
        source_data = {
          [field]: {
            display: ["value"],
            ajax: {
              url: `${list_settings.root}dt-posts/v2/contacts/multi-select-values`,
              data: {
                s: "{{query}}",
                field
              },
              beforeSend: function (xhr) {
                xhr.setRequestHeader('X-WP-Nonce', window.wpApiShare.nonce);
              },
              callback: {
                done: function (data) {
                  return (data || []).map(tag => {
                    let label = _.get(field_options, tag + ".label", tag)
                    return {value: label, key: tag}
                  })
                }
              }
            }
          }
        }
      }
      $.typeahead({
        input: `.js-typeahead-${field}`,
        minLength: 0,
        maxItem: 20,
        searchOnFocus: true,
        template: function (query, item) {
          return `<span>${_.escape(item.value)}</span>`
        },
        source: source_data,
        display: "value",
        templateValue: "{{value}}",
        dynamic: true,
        multiselect: {
          matchOn: ["key"],
          data: [],
          callback: {
            onCancel: function (node, item) {
              $(`.current-filter[data-id="${item.key}"].${field}`).remove()
              _.pullAllBy(new_filter_labels, [{id:item.key}], "id")
            }
          }
        },
        callback: {
          onClick: function(node, a, item){
            let name = _.get(list_settings, `post_type_settings.fields.${field}.name`, field)
            selected_filters.append(`<span class="current-filter ${_.escape( field )}" data-id="${_.escape( item.key )}">${_.escape( name )}:${_.escape( item.value )}</span>`)
            new_filter_labels.push({id:item.key, name:`${name}:${item.value}`, field})
          },
          onResult: function (node, query, result, resultCount) {
            let text = TYPEAHEADS.typeaheadHelpText(resultCount, query, result)
            $(`#${field}-result-container`).html(text);
          },
          onHideLayout: function () {
            $(`#${field}-result-container`).html("");
          }
        }
      });
    }
  }

  let load_post_type_typeaheads = ()=>{
    $(".typeahead__query [data-type='connection']").each((key, el)=>{
      let field_key = $(el).data('field')
      let post_type = _.get( list_settings, `post_type_settings.fields.${field_key}.post_type`, field_key)
      if (!window.Typeahead[`.js-typeahead-${field_key}`]) {
        $.typeahead({
          input: `.js-typeahead-${field_key}`,
          minLength: 0,
          accent: true,
          searchOnFocus: true,
          maxItem: 20,
          template: function (query, item) {
            return `<span dir="auto">${_.escape(item.name)} (#${_.escape( item.ID )})</span>`
          },
          source: TYPEAHEADS.typeaheadPostsSource(post_type),
          display: "name",
          templateValue: "{{name}}",
          dynamic: true,
          multiselect: {
            matchOn: ["ID"],
            data: [],
            callback: {
              onCancel: function (node, item) {
                $(`.current-filter[data-id="${item.ID}"].${field_key}`).remove()
                _.pullAllBy(new_filter_labels, [{id: item.ID}], "id")
              }
            }
          },
          callback: {
            onResult: function (node, query, result, resultCount) {
              let text = TYPEAHEADS.typeaheadHelpText(resultCount, query, result)
              $(`#${field_key}-result-container`).html(text);
            },
            onHideLayout: function () {
              $(`#${field_key}-result-container`).html("");
            },
            onClick: function (node, a, item) {
              new_filter_labels.push({id: item.ID, name: item.name, field: field_key})
              selected_filters.append(`<span class="current-filter ${field_key}" data-id="${_.escape( item.ID )}">${_.escape( item.name )}</span>`)
            }
          }
        });
      }
    })
  }

  /**
   * location_grid
   */
  let loadLocationTypeahead = ()=> {
    if (!window.Typeahead['.js-typeahead-location_grid']) {
      $.typeahead({
        input: '.js-typeahead-location_grid',
        minLength: 0,
        accent: true,
        searchOnFocus: true,
        maxItem: 20,
        dropdownFilter: [{
          key: 'group',
          value: 'used',
          template: _.escape(window.wpApiShare.translations.used_locations),
          all: _.escape(window.wpApiShare.translations.all_locations)
        }],
        source: {
          used: {
            display: "name",
            ajax: {
              url: window.wpApiShare.root + 'dt/v1/mapping_module/search_location_grid_by_name',
              data: {
                s: "{{query}}",
                filter: function () {
                  return _.get(window.Typeahead['.js-typeahead-location_grid'].filters.dropdown, 'value', 'all')
                }
              },
              beforeSend: function (xhr) {
                xhr.setRequestHeader('X-WP-Nonce', window.wpApiShare.nonce);
              },
              callback: {
                done: function (data) {
                  if (typeof typeaheadTotals !== "undefined") {
                    typeaheadTotals.field = data.total
                  }
                  return data.location_grid
                }
              }
            }
          }
        },
        display: "name",
        templateValue: "{{name}}",
        dynamic: true,
        multiselect: {
          matchOn: ["ID"],
          data: [],
          callback: {
            onCancel: function (node, item) {
              $(`.current-filter[data-id="${item.ID}"].location_grid`).remove()
              _.pullAllBy(new_filter_labels, [{id: item.ID}], "id")
            }
          }
        },
        callback: {
          onResult: function (node, query, result, resultCount) {
            let text = TYPEAHEADS.typeaheadHelpText(resultCount, query, result)
            $('#location_grid-result-container').html(text);
          },
          onReady(){
            this.filters.dropdown = {key: "group", value: "used", template: "Used Locations"}
            this.container
            .removeClass("filter")
            .find("." + this.options.selector.filterButton)
            .html("Used Locations");
          },
          onHideLayout: function () {
            $('#location_grid-result-container').html("");
          },
          onClick: function (node, a, item) {
            let name = _.get(list_settings, `post_type_settings.fields.location_grid.name`, 'location_grid')
            new_filter_labels.push({id: item.ID, name: `${name}:${item.name}`, field: "location_grid"})
            selected_filters.append(`<span class="current-filter location_grid" data-id="${_.escape( item.ID )}">${_.escape( name )}:${_.escape( item.name )}</span>`)
          }
        }
      });
    }
  }

  /*
   * Setup filter box
   */
  let typeaheads_loaded = null
  $('#filter-modal').on("open.zf.reveal", function () {
    new_filter_labels=[]
    loadLocationTypeahead()
    load_post_type_typeaheads()
    typeaheads_loaded = load_multi_select_typeaheads().catch(err => { console.error(err) })
    $('#new-filter-name').val('')
    $("#filter-modal input.dt_date_picker").each(function () {
      $(this).val('')
    })
    $("#filter-modal input:checked").each(function () {
      $(this).prop('checked', false)
    })
    selected_filters.empty();
    $(".typeahead__query input").each(function () {
      let typeahead = Typeahead['.'+$(this).attr("class").split(/\s+/)[0]]
      if ( typeahead ){
        for (let i = 0; i < typeahead.items.length; i ){
          typeahead.cancelMultiselectItem(0)
        }
        typeahead.node.trigger('propertychange.typeahead')
      }
    })
    $('#confirm-filter-records').show()
    $('#save-filter-edits').hide()
  })

  let edit_saved_filter = function( filter ){
    $('#filter-modal').foundation('open');
    typeaheads_loaded.then(()=>{
      new_filter_labels = filter.labels
      let connectionTypeKeys = list_settings.post_type_settings.connection_types
      connectionTypeKeys.push("location_grid")
      new_filter_labels.forEach(label=>{
        selected_filters.append(`<span class="current-filter ${_.escape( label.field )}" data-id="${_.escape( label.id )}">${_.escape( label.name )}</span>`)
        let type = _.get(list_settings, `post_type_settings.fields.${label.field}.type`)
        if ( type === "key_select" || type === "boolean" ){
          $(`#filter-modal #${label.field}-options input[value="${label.id}"]`).prop('checked', true)
        } else if ( type === "date" ){
          $(`#filter-modal #${label.field}-options #${label.id}`).datepicker('setDate', label.date)
        } else if ( connectionTypeKeys.includes( label.field ) ){
          Typeahead[`.js-typeahead-${label.field}`].addMultiselectItemLayout({ID:label.id, name:label.name})
        } else if ( type === "multi_select" ){
          Typeahead[`.js-typeahead-${label.field}`].addMultiselectItemLayout({key:label.id, value:label.name})
        }
      })
      ;(filter.query.combine || []).forEach(c=>{
        $(`#combine_${c}`).prop('checked', true)
      })
      $('#new-filter-name').val(filter.name)
      $('#confirm-filter-records').hide()
      $('#save-filter-edits').data("filter-id", filter.ID).show()
    })
  }

  $('#save-filter-edits').on('click', function () {
    let search_query = get_custom_filter_search_query()
    let filter_id = $('#save-filter-edits').data("filter-id")
    let filter = _.find(list_settings.filters.filters, {ID:filter_id})
    filter.name = $('#new-filter-name').val()
    $(`.filter-list-name[data-filter="${filter_id}"]`).text(filter.name)
    filter.query = search_query
    filter.labels = new_filter_labels
    API.save_filters( list_settings.post_type, filter )
    get_records_for_current_filter()
  })

  $('#filter-tabs').on('change.zf.tabs', function (a, b) {
    let field = $(b).data("field")
    $(`.tabs-panel`).removeClass('is-active')
    $(`#${field}.tabs-panel`).addClass('is-active')
    if (field &&  Typeahead[`.js-typeahead-${field}`]){
      Typeahead[`.js-typeahead-${field}`].adjustInputSize()
    }
  })

  //watch all other checkboxes
  $('#filter-modal .key_select_options input').on("change", function() {
    let field_key = $(this).data('field');
    let option_id = $(this).val()
    if ($(this).is(":checked")){
      let field_options = _.get( list_settings, `post_type_settings.fields.${field_key}.default` )
      let option_name = field_options[option_id]["label"]
      let name = _.get(list_settings, `post_type_settings.fields.${field_key}.name`, field_key)
      new_filter_labels.push({id:$(this).val(), name:`${name}:${option_name}`, field:field_key})
      selected_filters.append(`<span class="current-filter ${_.escape( field_key )}" data-id="${_.escape( option_id )}">${_.escape( name )}:${_.escape( option_name )}</span>`)
    } else {
      $(`.current-filter[data-id="${$(this).val()}"].${field_key}`).remove()
      _.pullAllBy(new_filter_labels, [{id:option_id}], "id")
    }
  })
  //watch bool checkboxes
  $('#filter-modal .boolean_options input').on("change", function() {
    let field_key = $(this).data('field');
    let option_id = $(this).val()
    let label = $(this).data('label');
    if ($(this).is(":checked")){
      let field = _.get( list_settings, `post_type_settings.fields.${field_key}` )
      new_filter_labels.push({id:$(this).val(), name:`${field.name}:${label}`, field:field_key})
      selected_filters.append(`<span class="current-filter ${_.escape( field_key )}" data-id="${_.escape( option_id )}">${_.escape( field.name )}:${_.escape( label )}</span>`)
    } else {
      $(`.current-filter[data-id="${$(this).val()}"].${field_key}`).remove()
      _.pullAllBy(new_filter_labels, [{id:option_id}], "id")
    }
  })

  $('#filter-modal .dt_date_picker').datepicker({
    dateFormat: 'yy-mm-dd',
    onSelect: function (date) {
      let id = $(this).data('field')
      let delimiter = $(this).data('delimit')
      let delimiter_label = list_settings.translations[`range_${delimiter}`]
      let field_name = _.get( list_settings, `post_type_settings.fields.${id}.name` , id)
      if ( id === "created_on" ){
        field_name = list_settings.translations.creation_date
      }
      //remove existing filters
      _.pullAllBy(new_filter_labels, [{id:`${id}_${delimiter}`}], "id")
      $(`.current-filter[data-id="${id}_${delimiter}"]`).remove()
      //add new filters
      new_filter_labels.push({id:`${id}_${delimiter}`, name:`${field_name} ${delimiter_label}:${date}`, field:id, date:date})
      selected_filters.append(`
        <span class="current-filter ${id}_${delimiter}"
              data-id="${id}_${delimiter}">
                ${field_name} ${delimiter_label}:${date}
        </span>
      `)
    },
    changeMonth: true,
    changeYear: true
  })

  $('#filter-modal .clear-date-picker').on('click', function () {
    let id = $(this).data('for')
    $(`#filter-modal #${id}`).datepicker('setDate', null)
    _.pullAllBy(new_filter_labels, [{id:`${id}`}], "id")
    $(`.current-filter[data-id="${id}"]`).remove()
  })

  //save the filter in the user meta
  $(`#confirm-filter-save`).on('click', function () {
    let filterName = $('#filter-name').val()
    let filter = _.find(custom_filters, {ID:filter_to_save})
    filter.name = _.escape( filterName )
    filter.tab = 'custom'
    if (filter.query){
      list_settings.filters.filters.push(filter)
      API.save_filters(list_settings.post_type,filter).then(()=>{
        $(`.custom-filters [class*="list-view ${filter_to_save}`).remove()
        setup_filters()
        let active_tab = $('.accordion-item.is-active ').data('id');
        if ( active_tab !== 'custom' ){
          $(`#list-filter-tabs [data-id='custom'] a`).click()
        }
        $(`input[name="view"][value="saved-filters"][data-id='${filter_to_save}']`).prop('checked', true);
        get_records_for_current_filter()
        $('#filter-name').val("")
      }).catch(err => { console.error(err) })
    }
  })

  //delete a filter
  $(`#confirm-filter-delete`).on('click', function () {

    let filter = _.find(list_settings.filters.filters, {ID:filter_to_delete})
    if ( filter && ( filter.visible === true || filter.visible === '1' ) ){
      filter.visible = false;
      API.save_filters(list_settings.post_type,filter).then(()=>{
        _.pullAllBy(list_settings.filters.filters, [{ID:filter_to_delete}], "ID")
        setup_filters()
        $(`#list-filter-tabs [data-id='custom'] a`).click()
      }).catch(err => { console.error(err) })
    } else {
      API.delete_filter(list_settings.post_type, filter_to_delete).then(()=>{
        _.pullAllBy(list_settings.filters.filters, [{ID:filter_to_delete}], "ID")
        setup_filters()
        $(`#list-filter-tabs [data-id='custom'] a`).click()
      }).catch(err => { console.error(err) })
    }
  })

  $("#search").on("click", function () {
    let searchText = _.escape( $("#search-query").val() )
    let query = {text:searchText, assigned_to:["all"]}
    let labels = [{ id:"search", name:searchText, field: "search"}]
    add_custom_filter(searchText, "search", query, labels)
  })

  $("#search-mobile").on("click", function () {
    let searchText = _.escape( $("#search-query-mobile").val() )
    let query = {text:searchText, assigned_to:["all"]}
    let labels = [{ id:"search", name:searchText, field: "search"}]
    add_custom_filter(searchText, "search", query, labels)
  })

  $('.search-input').on('keyup', function (e) {
    if ( e.keyCode === 13 ){
      $("#search").trigger("click")
    }
  })

  $('.search-input-mobile').on('keyup', function (e) {
    if ( e.keyCode === 13 ){
      $("#search-mobile").trigger("click")
    }
  })

  //toggle show search input on mobile
  $("#open-search").on("click", function () {
    $(".hideable-search").toggle()
  })


})(window.jQuery, window.list_settings, window.Foundation);
