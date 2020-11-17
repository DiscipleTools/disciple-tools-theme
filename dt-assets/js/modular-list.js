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
  let fields_to_show_in_table = window.SHAREDFUNCTIONS.get_json_cookie( 'fields_to_show_in_table', [] );
  let current_user_id = wpApiNotifications.current_user_id;

  let items = []
  try {
    cached_filter = JSON.parse(cookie)
  } catch (e) {
    cached_filter = {}
  }
  let current_filter = (cached_filter && !_.isEmpty(cached_filter)) ? cached_filter : { query:{} }

  //set up main filters
  setup_filters()

  //set up custom cached filter
  if ( cached_filter && !_.isEmpty(cached_filter) && cached_filter.type === "custom_filter" ){
      add_custom_filter(cached_filter.name, "default", cached_filter.query, cached_filter.labels, false)
  } else {
    //check select filter
    if ( current_filter.ID ){
      //open the filter tabs
      $(`#list-filter-tabs [data-id='${_.escape( current_filter.tab )}'] a`).click()
      let filter_element = $(`input[name=view][data-id="${_.escape( current_filter.ID )}"].js-list-view`)
      if ( filter_element.length ){
        filter_element.prop('checked', true);
      } else {
        $('#list-filter-tabs .accordion-item a')[0].click()
        $($('.js-list-view')[0]).prop('checked', true)
      }
    } else {
      $('#list-filter-tabs .accordion-item a')[0].click()
      $($('.js-list-view')[0]).prop('checked', true)
    }
  }

  //determine list columns
  if ( _.isEmpty(fields_to_show_in_table)){
    _.forOwn( list_settings.post_type_settings.fields, (field_settings, field_key)=> {
      if (_.get(field_settings, 'show_in_table')===true || _.get(field_settings, 'show_in_table') > 0) {
        fields_to_show_in_table.push(field_key)
      }
    })
    fields_to_show_in_table.sort((a,b)=>{
      let a_order = list_settings.post_type_settings.fields[a].show_in_table ? ( list_settings.post_type_settings.fields[a].show_in_table === true ? 50 : list_settings.post_type_settings.fields[a].show_in_table ) : 200
      let b_order = list_settings.post_type_settings.fields[b].show_in_table ? ( list_settings.post_type_settings.fields[b].show_in_table === true ? 50 : list_settings.post_type_settings.fields[b].show_in_table ) : 200
      return a_order > b_order ? 1 : -1
    })
  }

  // get records on load and when a filter is clicked
  get_records_for_current_filter()
  $(document).on('change', '.js-list-view', () => {
    get_records_for_current_filter()
  });

  //load record for the first filter when a tile is clicked
  $(document).on('click', '.accordion-title', function(){
    let selected_filter = $(".js-list-view:checked").data('id')
    let tab = $(this).data('id');
    if ( selected_filter ){
      $(`.accordion-item[data-id='${tab}'] .js-list-view`).first().prop('checked', true);
      get_records_for_current_filter()
    }
  })

  function get_records_for_current_filter(){
    let checked = $(".js-list-view:checked")
    let current_view = checked.val()
    let filter_id = checked.data("id") || current_view || ""
    let sort = current_filter.query.sort || null;
    if ( current_view === "custom_filter" ){
      let filterId = checked.data("id")
      current_filter = _.find(custom_filters, {ID:filterId})
      current_filter.type = current_view
    } else {
      current_filter = _.find(list_settings.filters.filters, {ID:filter_id}) || _.find(list_settings.filters.filters, {ID:filter_id.toString()}) || current_filter
      current_filter.type = 'default'
      current_filter.labels = current_filter.labels || [{ id:filter_id, name:current_filter.name}]
    }
    current_filter.query.sort = sort || current_filter.query.sort;
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
        <a href="#" class="accordion-title" data-id="${_.escape(tab.key)}">
          ${_.escape(tab.label)}
          <span class="tab-count-span" data-tab="${_.escape(tab.key)}">
              ${tab.count || tab.count >= 0 ? `(${_.escape(tab.count)})`: ``}
          </span>
        </a>
        <div class="accordion-content" data-tab-content>
          <div class="list-views">
            ${  list_settings.filters.filters.map( filter =>{
        if (filter.tab===tab.key && filter.tab !== 'custom') {
          let indent = filter.subfilter && Number.isInteger(filter.subfilter) ? 15 * filter.subfilter : 15;
          return `
                  <label class="list-view" style="${ filter.subfilter ? `margin-left:${indent}px` : ''}">
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
        sortLabel = _.get( list_settings, `post_type_settings.fields[${filter.query.sort}].name`, sortLabel)
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
    //check is this is the bulk_edit_master checkbox
    if ( this.id == "bulk_edit_master") {
      return;
    }
    let id = $(this).data('id');
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

  $('#choose_fields_to_show_in_table').on('click', function(){
    $('#list_column_picker').toggle()
  })
  $('#save_column_choices').on('click', function(){
    let new_selected = [];
    $('#list_column_picker input:checked').each((index, elem)=>{
      new_selected.push($(elem).val())
    })
    fields_to_show_in_table = _.intersection( fields_to_show_in_table, new_selected ) // remove unchecked
    fields_to_show_in_table = _.uniq(_.union( fields_to_show_in_table, new_selected ))
    window.SHAREDFUNCTIONS.save_json_cookie('fields_to_show_in_table', fields_to_show_in_table, list_settings.post_type )
    window.location.reload()
  })
  $('#reset_column_choices').on('click', function(){
    fields_to_show_in_table = []
    window.SHAREDFUNCTIONS.save_json_cookie('fields_to_show_in_table', fields_to_show_in_table, list_settings.post_type )
    window.location.reload()
  })


  $('#records-table').dragableColumns({
    drag: true,
    dragClass: 'drag',
    overClass: 'over',
    movedContainerSelector: '.dnd-moved',
    onDragEnd: ()=>{
      fields_to_show_in_table = []
      $('.table-headers th').each((i, e)=>{
        let field = $(e).data('id')
        if ( field ){
          fields_to_show_in_table.push(field)
        }
      })
      window.SHAREDFUNCTIONS.save_json_cookie('fields_to_show_in_table', fields_to_show_in_table, list_settings.post_type )
    }
  }).on('click', 'tbody tr', function(){
    window.location = $(this).data('link')
  })

  let build_table = (records)=>{
    let table_rows = ``
    let mobile = $(window).width() < 640
    records.forEach( ( record, index )=>{
      let row_fields_html = ''
      fields_to_show_in_table.forEach(field_key=>{
        let values_html = '';
        let values = [];
        if ( field_key === "name" ){
          if ( mobile ){ return }
          values_html = `<a href="${ _.escape( record.permalink ) }" title="${ _.escape( record.post_title ) }">${ _.escape( record.post_title ) }</a>`
        } else if ( list_settings.post_type_settings.fields[field_key] ) {
          let field_settings = list_settings.post_type_settings.fields[field_key]
          let field_value = _.get( record, field_key, false )

          if ( field_value !== false ) {
            if (['text', 'number'].includes(field_settings.type)) {
              values = [_.escape(field_value)]
            } else if (field_settings.type === 'date') {
              values = [_.escape(field_value.formatted)]
            } else if (field_settings.type === 'user_select') {
              values = [_.escape(field_value.display)]
            } else if (field_settings.type === 'key_select') {
              values = [_.escape(field_value.label)]
            } else if (field_settings.type === 'multi_select') {
              values = field_value.map(v => {
                return `${_.escape(_.get(field_settings, `default[${v}].label`, v))}`;
              })
            } else if ( field_settings.type === "location" ){
              values = field_value.map(v => {
                return `${_.escape( v.label )}`;
              })
            } else if ( field_settings.type === "communication_channel" ){
              values = field_value.map(v => {
                return `${_.escape( v.value )}`;
              })
            } else if ( field_settings.type === "connection" ){
              values = field_value.map(v => {
                return `${_.escape( v.post_title )}`;
              })
            } else if ( field_settings.type === "boolean" ){
              values = ['&check;']
            }
          }
        } else {
          return;
        }
        values_html += values.map(v=>{
          return `<li>${v}</li>`
        }).join('')

        if ( $(window).width() < 640 ){
          row_fields_html += `
            <td>
              <div class="mobile-list-field-name">
                <ul>
                ${_.escape(_.get(list_settings, `post_type_settings.fields[${field_key}].name`, field_key))}
                </ul>
              </div>
              <div class="mobile-list-field-value">
                <ul style="line-height:20px" >
                  ${values.join(', ')}
                </ul>
              </div>

            </td>
          `
        } else {
          row_fields_html += `
            <td title="${values.join(', ')}">
              <ul>
                ${values_html}
              </ul>
            </td>
          `
        }
      })

      if ( mobile ){
        table_rows += `<tr data-link="${_.escape(record.permalink)}">
          <td class="bulk_edit_checkbox">
          <input type="checkbox" name="bulk_edit_id" value="${record.ID}">
            <div class="mobile-list-field-name">
                ${index+1}.
              </div>
              <div class="mobile-list-field-value">
                  <a href="${ _.escape( record.permalink ) }">${ _.escape( record.post_title ) }</a>
              </div>
          </td>
          ${ row_fields_html }
        `
      } else {
        table_rows += `<tr class="dnd-moved" data-link="${_.escape(record.permalink)}">
          <td class="bulk_edit_checkbox" ><input type="checkbox" name="bulk_edit_id" value="${record.ID}"></td>
          <td style="white-space: nowrap" >${index+1}.</td>
          ${ row_fields_html }
        `
      }
    })
    if ( records.length === 0 ){
      table_rows = `<tr><td colspan="10">${_.escape(list_settings.translations.empty_list)}</td></tr>`
    }

    let table_html = `
      ${table_rows}
    `
    $('#table-content').html(table_html)
    bulk_edit_checkbox_event();
  }

  function get_records( offset = 0, sort = null ){
    loading_spinner.addClass("active")
    let query = current_filter.query
    if ( offset ){
      query["offset"] = offset
    }
    if ( sort ){
      query.sort = sort
      query.offset = 0
    }

    window.SHAREDFUNCTIONS.save_json_cookie(`last_view`, current_filter, list_settings.post_type )
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
      window.records_list = response // adds global access to current list for plugins

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
      } else if ( type === "date" ) {
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
              url: window.wpApiShare.root + `dt-posts/v2/${list_settings.post_type}/multi-select-values`,
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

  let load_user_select_typeaheads = ()=>{
    $(".typeahead__query [data-type='user_select']").each((key, el)=>{
      let field_key = $(el).data('field')
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
          source: TYPEAHEADS.typeaheadUserSource(),
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
    load_user_select_typeaheads()
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
    constrainInput: false,
    dateFormat: 'yy-mm-dd',
    onSelect: function (date) {
      let id = $(this).data('field')
      let delimiter = $(this).data('delimit')
      let delimiter_label = list_settings.translations[`range_${delimiter}`]
      let field_name = _.get( list_settings, `post_type_settings.fields.${id}.name` , id)
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
    let searchText = $("#search-query").val()
    let query = {text:searchText}
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



  /***
   * Bulk Edit
   */
  $('#bulk_edit_controls').on('click', function(){
    $('#bulk_edit_picker').toggle();
    $('#records-table').toggleClass('bulk_edit_on');
  })

  function bulk_edit_checkbox_event() {
    $("tbody tr td.bulk_edit_checkbox").on('click', function(e) {
      e.stopImmediatePropagation();
      bulk_edit_count();
    });
  }

  $('#bulk_edit_master').on('click', function(e){
    e.stopImmediatePropagation();
    let checked = $(this).children('input').is(':checked');
        $('.bulk_edit_checkbox input').each(function() {
        $(this).attr('checked', checked);
    })
  })
  /**
   * Bulk_Assigned_to
   */
  let bulk_edit_submit_button = $('#bulk_edit_submit');

  bulk_edit_submit_button.on('click', function(e) {
    bulk_edit_submit();
  });

  function bulk_edit_submit() {
    let allInputs = $('#bulk_edit_picker input, #bulk_edit_picker select, #bulk_edit_picker .select-button, #bulk_edit_picker .button').not('.js-typeahead-bulk_share');
    let shareInput = $('.js-typeahead-bulk_share');
    let updatePayload = {};
    let sharePayload;

    allInputs.each(function () {
        let inputData = $(this).data();
        $.each(inputData, function (key, value) {
          if (key.includes('bulk_key_') && value) {
            let field_key = key.replace('bulk_key_', '');
            if(field_key) {
              updatePayload[field_key] = value;
            }
          }
        })
    })

    shareInput.each(function () {
      sharePayload = $(this).data('bulk_key_share');
    })

    $('.bulk_edit_checkbox input').each(function () {
      if (this.checked && this.id !== 'bulk_edit_master_checkbox') {
        if (Object.keys(updatePayload).length) {
          API.update_post(list_settings.post_type, $(this).val(), updatePayload).catch(err => { console.error(err) });
        }

        if (sharePayload.length) {
          let postId = parseInt($(this).val());

          sharePayload.forEach(function(value) {
            API.add_shared(list_settings.post_type, postId, value).catch(err => { console.error(err) });
          })
        }
      }
    }).promise().done( function() {
      window.location.reload()
    });
  }

  function bulk_edit_count() {
    let bulk_edit_total_checked = $('.bulk_edit_checkbox input:checked').length;
    bulk_edit_submit_button.text(`Update ${bulk_edit_total_checked} ${list_settings.post_type}`)
  }

  let bulk_edit_picker_checkboxes = $('#bulk_edit_picker #update-needed');
  bulk_edit_picker_checkboxes.on('click', function(e) {
    if ($(this).is(':checked')) {
      $(this).data('bulk_key_requires_update', true);
    }
  })

  let bulk_edit_picker_select_field = $('#bulk_edit_picker select');
  bulk_edit_picker_select_field.on('change', function(e) {
      let field_key = this.id.replace('bulk_', '');
      $(this).data(`bulk_key_${field_key}`, this.value);
  })

  let bulk_edit_picker_button_groups= $('#bulk_edit_picker .select-button');
  bulk_edit_picker_button_groups.on('click', function(e) {
      let field_key = $(this).data('field-key').replace('bulk_', '');

      let value = `{${field_key}: {"values": [{"value": ${this.id},"delete": true}]}}`;

      $(this).addClass('selected-select-button');
      $(this).data(`bulk_key_${field_key}`, value);
  })

  let bulk_assigned_to_input = $(`.js-typeahead-bulk_assigned_to`)
  $.typeahead({
    input: '.js-typeahead-bulk_assigned_to',
    minLength: 0,
    maxItem: 0,
    accent: true,
    searchOnFocus: true,
    source: TYPEAHEADS.typeaheadUserSource(),
    templateValue: "{{name}}",
    template: function (query, item) {
      return `<div class="assigned-to-row" dir="auto">
        <span>
            <span class="avatar"><img style="vertical-align: text-bottom" src="{{avatar}}"/></span>
            ${_.escape( item.name )}
        </span>
        ${ item.status_color ? `<span class="status-square" style="background-color: ${_.escape(item.status_color)};">&nbsp;</span>` : '' }
        ${ item.update_needed && item.update_needed > 0 ? `<span>
          <img style="height: 12px;" src="${_.escape( window.wpApiShare.template_dir )}/dt-assets/images/broken.svg"/>
          <span style="font-size: 14px">${_.escape(item.update_needed)}</span>
        </span>` : '' }
      </div>`
    },
    dynamic: true,
    hint: true,
    emptyTemplate: _.escape(window.wpApiShare.translations.no_records_found),
    callback: {
      onClick: function(node, a, item){
        node.data('bulk_key_assigned_to', `user-${item.ID}`);
      },
      onResult: function (node, query, result, resultCount) {
        let text = TYPEAHEADS.typeaheadHelpText(resultCount, query, result)
        $('#bulk_assigned_to-result-container').html(text);
      },
      onHideLayout: function () {
        $('.bulk_assigned_to-result-container').html("");
      },
      onReady: function () {
      }
    },
  });


/**
 * Bulk share
*/
$.typeahead({
  input: '.js-typeahead-bulk_share',
  minLength: 0,
  maxItem: 0,
  accent: true,
  searchOnFocus: true,
  source: TYPEAHEADS.typeaheadUserSource(),
  templateValue: "{{name}}",
  dynamic: true,
  multiselect: {
    matchOn: ["ID"],
    callback: {
      onCancel: function (node, item) {
        $('#share-result-container').html("");

      }
    },
  },
  callback: {
    onClick: function (node, a, item, event) {
      // window.API.add_shared(post_type, id, item.ID)
      if (node.data('bulk_key_share')) {
        var shareUserArray = node.data('bulk_key_share');
      } else {
        var shareUserArray = [];
      }
      shareUserArray.push(item.ID);
      node.data(`bulk_key_share`, shareUserArray);
    },
    onResult: function (node, query, result, resultCount) {
      if (query) {
        let text = window.TYPEAHEADS.typeaheadHelpText(resultCount, query, result)
        $('#share-result-container').html(text);
      }
    },
    onHideLayout: function () {
      $('#share-result-container').html("");
    }
  }
});

/**
 * Bulk typeaheads
 */

  // let field_settings = window.list_settings.post_type_settings.fields;

  // $('#bulk_edit_picker .dt_typeahead').each((key, el)=>{
  //   let field_id = $(el).attr('id').replace('_connection', '').replace('bulk_', '');
  //   let element_id =  $(el).attr('id').replace('_connection', '');

  //   console.log(field_id);
  //   console.log(element_id);

  //   let listing_post_type = _.get(window.list_settings.post_type_settings.fields[field_id], "post_type", 'contacts')
  //   $.typeahead({
  //     input: `.js-typeahead-${element_id}`,
  //     minLength: 0,
  //     accent: true,
  //     maxItem: 30,
  //     searchOnFocus: true,
  //     template: window.TYPEAHEADS.contactListRowTemplate,
  //     source: window.TYPEAHEADS.typeaheadPostsSource(listing_post_type, {field_key:field_id}),
  //     display: "name",
  //     templateValue: "{{name}}",
  //     dynamic: true,
  //     multiselect: {
  //       matchOn: ["ID"],
  //       data: '',
  //       callback: {
  //         onCancel: function (node, item) {
  //           $(node).removeData( `bulk_key_${field_id}` );
  //         }
  //       },
  //       href: window.wpApiShare.site_url + `/${listing_post_type}/{{ID}}`
  //     },
  //     callback: {
  //       onClick: function(node, a, item, event){
  //         node.data(`bulk_key_${field_id}`, {values:[{"value":item.ID}]});
  //         this.addMultiselectItemLayout(item)
  //         event.preventDefault()
  //         this.hideLayout();
  //         this.resetInput();
  //         //masonGrid.masonry('layout')
  //       },
  //       onResult: function (node, query, result, resultCount) {
  //         let text = TYPEAHEADS.typeaheadHelpText(resultCount, query, result)
  //         $(`#${element_id}-result-container`).html(text);
  //       },
  //       onHideLayout: function (event, query) {
  //         if ( !query ){
  //           $(`#${element_id}-result-container`).empty()
  //         }
  //         //masonGrid.masonry('layout')
  //       },
  //       onShowLayout (){
  //         //masonGrid.masonry('layout')
  //       }
  //     }
  //   })
  // })

  // //multi_select typeaheads
  // for (let input of $(".multi_select .typeahead__query input")) {
  //   let field = $(input).data('field')
  //   let typeahead_name = `.js-typeahead-${field}`

  //   if (window.Typeahead[typeahead_name]) {
  //     return
  //   }

  //   let source_data =  { data: [] }
  //   let field_options = _.get(field_settings, `${field}.default`, {})
  //   if ( Object.keys(field_options).length > 0 ){
  //     _.forOwn(field_options, (val, key)=>{
  //       if ( !val.deleted ){
  //         source_data.data.push({
  //           key: key,
  //           name:key,
  //           value: val.label || key
  //         })
  //       }
  //     })
  //   } else {
  //     source_data = {
  //       [field]: {
  //         display: ["value"],
  //         ajax: {
  //           url: window.wpApiShare.root + `dt-posts/v2/${list_settings.post_type}/multi-select-values`,
  //           data: {
  //             s: "{{query}}",
  //             field
  //           },
  //           beforeSend: function (xhr) {
  //             xhr.setRequestHeader('X-WP-Nonce', window.wpApiShare.nonce);
  //           },
  //           callback: {
  //             done: function (data) {
  //               return (data || []).map(tag => {
  //                 let label = _.get(field_options, tag + ".label", tag)
  //                 return {value: label, key: tag}
  //               })
  //             }
  //           }
  //         }
  //       }
  //     }
  //   }
  //   $.typeahead({
  //     input: `.js-typeahead-${field}`,
  //     minLength: 0,
  //     maxItem: 20,
  //     searchOnFocus: true,
  //     template: function (query, item) {
  //       return `<span>${_.escape(item.value)}</span>`
  //     },
  //     source: source_data,
  //     display: "value",
  //     templateValue: "{{value}}",
  //     dynamic: true,
  //     multiselect: {
  //       matchOn: ["key"],
  //       data: '',
  //       callback: {
  //         onCancel: function (node, item, event) {
  //           // $(`#${field}-spinner`).addClass('active')
  //           // API.update_post(post_type, post_id, {[field]: {values:[{value:item.key, delete:true}]}}).then((new_post)=>{
  //           //   $(`#${field}-spinner`).removeClass('active')
  //           //   this.hideLayout();
  //           //   this.resetInput();
  //           //   $( document ).trigger( "dt_multi_select-updated", [ new_post, field ] );
  //           // }).catch(err => { console.error(err) })
  //         }
  //       }
  //     },
  //     callback: {
  //       onClick: function(node, a, item, event){
  //         let field_id = $(node).attr('id').replace('_connection', '').replace('bulk_', '');

  //         node.data(`bulk_key_${field_id}`, {values:[{"value":item.ID}]});
  //         // $(`#${field}-spinner`).addClass('active')
  //         // API.update_post(post_type, post_id, {[field]: {values:[{"value":item.key}]}}).then(new_post=>{
  //         //   $(`#${field}-spinner`).removeClass('active')
  //         //   $( document ).trigger( "dt_multi_select-updated", [ new_post, field ] );
  //         //   this.addMultiselectItemLayout(item)
  //         //   event.preventDefault()
  //         //   this.hideLayout();
  //         //   this.resetInput();
  //         // }).catch(err => { console.error(err) })
  //       },
  //       onResult: function (node, query, result, resultCount) {
  //         let text = TYPEAHEADS.typeaheadHelpText(resultCount, query, result)
  //         $(`#${field}-result-container`).html(text);
  //       },
  //       onHideLayout: function () {
  //         $(`#${field}-result-container`).html("");
  //       }
  //     }
  //   });
  // }
  // let connection_type = null
  // //new record off a typeahead
  // $('.create-new-record').on('click', function(){
  //   connection_type = $(this).data('connection-key');
  //   $('#create-record-modal').foundation('open');
  //   $('.js-create-record .error-text').empty();
  //   $(".js-create-record-button").attr("disabled", false).removeClass("alert")
  //   $(".reveal-after-record-create").hide()
  //   $(".hide-after-record-create").show()
  //   $(".js-create-record input[name=title]").val('')
  //   //create new record
  // })
  // $(".js-create-record").on("submit", function(e) {
  //   e.preventDefault();
  //   $(".js-create-record-button").attr("disabled", true).addClass("loading");
  //   let title = $(".js-create-record input[name=title]").val()
  //   if ( !connection_type){
  //     $(".js-create-record .error-text").text(
  //       "Something went wrong. Please refresh and try again"
  //     );
  //     return;
  //   }
  //   let update_field = connection_type;
  //   API.create_post( field_settings[update_field].post_type, {
  //     title,
  //   }).then((newRecord)=>{
  //     return API.update_post( post_type, post_id, { [update_field]: { values: [ { value:newRecord.ID }]}}).then(response=>{
  //       $(".js-create-record-button").attr("disabled", false).removeClass("loading");
  //       $(".reveal-after-record-create").show()
  //       $("#new-record-link").html(`<a href="${_.escape( newRecord.permalink )}">${_.escape( title )}</a>`)
  //       $(".hide-after-record-create").hide()
  //       $('#go-to-record').attr('href', _.escape( newRecord.permalink ));
  //       post = response
  //       $( document ).trigger( "dt-post-connection-created", [ post, update_field ] );
  //       if ( Typeahead[`.js-typeahead-${connection_type}`] ){
  //         Typeahead[`.js-typeahead-${connection_type}`].addMultiselectItemLayout({ID:newRecord.ID.toString(), name:title})
  //         //masonGrid.masonry('layout')
  //       }
  //     })
  //   })
  //   .catch(function(error) {
  //     $(".js-create-record-button").removeClass("loading").addClass("alert");
  //     $(".js-create-record .error-text").text(
  //       _.get( error, "responseJSON.message", "Something went wrong. Please refresh and try again" )
  //     );
  //     console.error(error);
  //   });
  // })

  // $('.dt_location_grid').each(()=> {
  //   let field_id = 'location_grid'
  //   $.typeahead({
  //     input: '.js-typeahead-location_grid',
  //     minLength: 0,
  //     accent: true,
  //     searchOnFocus: true,
  //     maxItem: 20,
  //     dropdownFilter: [{
  //       key: 'group',
  //       value: 'focus',
  //       template: _.escape(window.wpApiShare.translations.regions_of_focus),
  //       all: _.escape(window.wpApiShare.translations.all_locations),
  //     }],
  //     source: {
  //       focus: {
  //         display: "name",
  //         ajax: {
  //           url: window.wpApiShare.root + 'dt/v1/mapping_module/search_location_grid_by_name',
  //           data: {
  //             s: "{{query}}",
  //             filter: function () {
  //               return _.get(window.Typeahead['.js-typeahead-location_grid'].filters.dropdown, 'value', 'all')
  //             }
  //           },
  //           beforeSend: function (xhr) {
  //             xhr.setRequestHeader('X-WP-Nonce', window.wpApiShare.nonce);
  //           },
  //           callback: {
  //             done: function (data) {
  //               if (typeof typeaheadTotals!=="undefined") {
  //                 typeaheadTotals.field = data.total
  //               }
  //               return data.location_grid
  //             }
  //           }
  //         }
  //       }
  //     },
  //     display: "name",
  //     templateValue: "{{name}}",
  //     dynamic: true,
  //     multiselect: {
  //       matchOn: ["ID"],
  //       data: '',
  //       callback: {
  //         onCancel: function (node, item) {
  //           // API.update_post(post_type, post_id, {[field_id]: {values:[{value:item.ID, delete:true}]}})
  //           // .catch(err => { console.error(err) })
  //         }
  //       }
  //     },
  //     callback: {
  //       onClick: function (node, a, item, event) {
  //         $(`#${element_id}-spinner`).addClass('active');
  //         node.data(`bulk_key_${field_id}`, {values:[{"value":item.ID}]});
  //       },
  //       onReady() {
  //         this.filters.dropdown = {key: "group", value: "focus", template: _.escape(window.wpApiShare.translations.regions_of_focus)}
  //         this.container
  //         .removeClass("filter")
  //         .find("." + this.options.selector.filterButton)
  //         .html(_.escape(window.wpApiShare.translations.regions_of_focus));
  //       },
  //       onResult: function (node, query, result, resultCount) {
  //         resultCount = typeaheadTotals.location_grid
  //         let text = TYPEAHEADS.typeaheadHelpText(resultCount, query, result)
  //         $('#location_grid-result-container').html(text);
  //       },
  //       onHideLayout: function () {
  //         $('#location_grid-result-container').html("");
  //       }
  //     }
  //   });
  // })

  $('button.follow').on("click", function () {
    let following = !($(this).data('value') === "following")
    $(this).data("value", following ? "following" : "" )
    $(this).html( following ? "Unfollow" : "Follow")
    $(this).toggleClass( "hollow" )
    let follow = { values:[{value:current_user_id, delete:!following}] }

    let unfollow = {values:[{value:current_user_id, delete:following}]}

    $(this).data('bulk_key_follow', follow);
    $(this).data('bulk_key_unfollow', unfollow);
  })

})(window.jQuery, window.list_settings, window.Foundation);

