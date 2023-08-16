/* global dt_user_management_localized:false */
jQuery(document).ready(function($) {
  let escaped_translations = window.SHAREDFUNCTIONS.escapeObject(window.dt_user_management_localized.translations)

  window.open_user_modal = ( user_id )=>{

    // Reset email password button.
    let button_icon = $('#reset_user_pwd_email_icon');
    button_icon.removeClass('active');
    button_icon.removeClass('loading-spinner');
    button_icon.removeClass('mdi mdi-email-check-outline');
    button_icon.removeClass('mdi mdi-email-remove-outline');
    button_icon.css('color', '');
    button_icon.css('margin-left', '');

    $('#user_modal').foundation('open');
    /**
     * Set availability dates
     */
    let unavailable_dates_picker = $('#date_range')
    unavailable_dates_picker.daterangepicker({
      parentEl: "#user_modal",
      "singleDatePicker": false,
      autoUpdateInput: false,
      "locale": {
        "format": "YYYY/MM/DD",
        "separator": " - ",
        "daysOfWeek": window.SHAREDFUNCTIONS.get_days_of_the_week_initials(),
        "monthNames": window.SHAREDFUNCTIONS.get_months_labels(),
      },
      "firstDay": 1,
      "opens": "center",
      "drops": "down"
    }).on('apply.daterangepicker', function (ev, picker) {
      $(this).val(picker.startDate.format('YYYY/MM/DD') + ' - ' + picker.endDate.format('YYYY/MM/DD'));
      let start_date = picker.startDate.format('YYYY/MM/DD')
      let end_date = picker.endDate.format('YYYY/MM/DD')
      $('#add_unavailable_dates_spinner').addClass('active')
      update_user( window.current_user_lookup, 'add_unavailability', {start_date, end_date}).then((resp)=>{
        $('#add_unavailable_dates_spinner').removeClass('active')
        unavailable_dates_picker.val('');
        display_dates_unavailable(resp)
      })
    })

    window.current_user_lookup = user_id

    $('#user-id-reveal').html(window.current_user_lookup)

    $('.users-spinner').addClass("active")

    $('#languages_multi_select .dt_multi_select').addClass('empty-select-button').removeClass('selected-select-button');

    // load spinners
    let spinner = ' <span class="loading-spinner users-spinner active"></span> '
    $("#user_name").html(spinner)
    $('#update_needed_count').html(spinner)
    $('#needs_accepted_count').html(spinner)
    $('#active_contacts').html(spinner)
    $('#unread_notifications').html(spinner)
    $('#assigned_this_month').html(spinner)
    $('#assigned_last_month').html(spinner)
    $('#assigned_this_year').html(spinner)
    $('#assigned_all_time').html(spinner)
    $('#unaccepted_contacts').html(spinner)
    $('#contact_accepts').html(spinner)
    $('#avg_contact_accept').html(spinner)
    $('#unattempted_contacts').html(spinner)
    $('#contact_attempts').html(spinner)
    $('#avg_contact_attempt').html(spinner)
    $('#update_needed_list').html(spinner)
    $('#status_chart_div').html(spinner)
    $('#activity').html(spinner)
    $('#day_activity_chart').html(spinner)
    $('#mapbox-wrapper').html(spinner)
    $('#location-grid-meta-results').html(spinner)
    $('#profile_loading').addClass("active")

    $('#status-select').val('')
    $('#workload-select').val('')

    //clear the locations typeahead of previous values when the modal is opened
    let typeahead = window.Typeahead['.js-typeahead-location_grid']
    if (typeahead) {
      typeahead.items = [];
      typeahead.comparedItems =[];
      typeahead.label.container.empty();
      typeahead.adjustInputSize()
    }

    /* details */
    window.makeRequest( "get", `user?user=${user_id}&section=details`, null , 'user-management/v1/')
    .done(details=>{
      if ( window.current_user_lookup === user_id ) {
        $('#profile_loading').removeClass("active")
        user_details = details
        $("#user_name").html(window.lodash.escape(details.display_name))
        $("#update_display_name").val(details.display_name);
        $("#user_email").html(details.user_email);
        (details.languages || []).forEach(l=>{
          $(`#${l}`).addClass('selected-select-button').removeClass('empty-select-button')
        })

        $('#gender').val(details.gender)
        $('#description').val(details.description)
        details.user_fields.forEach(field=>{
          $(`#${field.key}`).val(field.value)
        })

        $('#user_status').val(window.lodash.escape(details.user_status))
        if ( details.user_status !== "0" ){
        }
        $('#workload_status').val(window.lodash.escape(details.workload_status))


        setup_user_roles( details );

        //availability
        if ( details.dates_unavailable ) {
          display_dates_unavailable( details.dates_unavailable )
        }

        let update_needed_list_html = ``;
        (details.update_needed.contacts||[]).forEach(contact => {
          update_needed_list_html += `<li>
            <a href="${window.wpApiShare.site_url}/contacts/${window.lodash.escape(contact.ID)}" target="_blank">
                ${window.lodash.escape(contact.post_title)}:  ${window.lodash.escape(contact.last_modified_msg)}
            </a>
          </li>`
        })
        $('#update_needed_list').html(update_needed_list_html)


        //locations
        if ( typeof window.dtMapbox !== "undefined" ) {
          window.dtMapbox.post_type = 'users'
          window.dtMapbox.user_id = user_id
          window.dtMapbox.user_location = details.user_location
          window.write_results_box()

          $( '#new-mapbox-search' ).on( "click", function() {
            window.dtMapbox.post_type = 'users'
            window.dtMapbox.user_id = user_id
            window.dtMapbox.user_location = details.user_location
            window.write_input_widget()
          });
        } else {
          //locations
          if (typeahead) {
            typeahead.items = [];
            typeahead.comparedItems =[];
            typeahead.label.container.empty();
            typeahead.adjustInputSize()
          }
          (details.user_location.location_grid || []).forEach(location => {
            typeahead.addMultiselectItemLayout({ID: location.id.toString(), name: location.label})
          })
        }
      }
    }).catch((e)=>{
      console.log( 'error in details')
      console.log( e)
    })

    let loaded_dmm_tab_once = false;
    $('#dmm-label').on( "click", function (){
      if ( !loaded_dmm_tab_once ) {
        /* locations */
        window.makeRequest("get", `user?user=${user_id}&section=stats`, null, 'user-management/v1/')
        .done(details => {
          if (window.current_user_lookup===user_id) {
            //stats
            $('#update_needed_count').html(window.lodash.escape(details.update_needed["total"]))
            $('#needs_accepted_count').html(window.lodash.escape(details.needs_accepted["total"]))
            $('#active_contacts').html(window.lodash.escape(details.active_contacts))
            $('#unread_notifications').html(window.lodash.escape(details.unread_notifications))
            $('#assigned_this_month').text(window.lodash.escape(details.assigned_counts.this_month))
            $('#assigned_last_month').text(window.lodash.escape(details.assigned_counts.last_month))
            $('#assigned_this_year').text(window.lodash.escape(details.assigned_counts.this_year))
            $('#assigned_all_time').text(window.lodash.escape(details.assigned_counts.all_time))

            status_pie_chart(details.contact_statuses)
          }

        }).catch((e) => {
          console.log('error in locations')
          console.log(e)
        })
        /* unaccepted_contacts */
        window.makeRequest("get", `user?user=${user_id}&section=unaccepted_contacts`, null, 'user-management/v1/')
        .done(response => {

          if (window.current_user_lookup===user_id && response.unaccepted_contacts.length > 0) {
            let unaccepted_contacts_html = ``
            response.unaccepted_contacts.forEach(contact => {
              let days = contact.time / 60 / 60 / 24;
              unaccepted_contacts_html += `<li>
          <a href="${window.wpApiShare.site_url}/contacts/${window.lodash.escape(contact.ID)}" target="_blank">
              ${window.lodash.escape(contact.name)} has be waiting to be accepted for ${days.toFixed(1)} days
              </a> </li>`
            })
            $('#unaccepted_contacts').html(unaccepted_contacts_html)
          } else {
            $('#unaccepted_contacts').html('')
          }

        }).catch((e) => {
          console.log('error in unaccepted_contacts')
          console.log(e)
        })

        /* contact_accepts */
        window.makeRequest("get", `user?user=${user_id}&section=contact_accepts`, null, 'user-management/v1/')
        .done(response => {

          if (window.current_user_lookup===user_id && response.contact_accepts.length > 0) {
            // assigned to contact accept
            let accepted_contacts_html = ``
            let avg_contact_accept = 0
            response.contact_accepts.forEach(contact => {
              let days = contact.time / 60 / 60 / 24;
              avg_contact_accept += days
              let accept_line = escaped_translations.accept_time
              .replace('%1$s', contact.name)
              .replace('%2$s', window.moment.unix(contact.date_accepted).format("MMM Do"))
              .replace('%3$s', days.toFixed(1))
              accepted_contacts_html += `<li>
          <a href="${window.wpApiShare.site_url}/contacts/${window.lodash.escape(contact.ID)}" target="_blank">
              ${window.lodash.escape(accept_line)}
          </a> </li>`
            })
            $('#contact_accepts').html(accepted_contacts_html)
            $('#avg_contact_accept').html(avg_contact_accept===0 ? '-':(avg_contact_accept / response.contact_accepts.length).toFixed(1))
          } else {
            $('#contact_accepts').html('')
            $('#avg_contact_accept').html('')
          }

        }).catch((e) => {
          console.log('error in contact_accepts')
          console.log(e)
        })

        /* unattempted_contacts */
        window.makeRequest("get", `user?user=${user_id}&section=unattempted_contacts`, null, 'user-management/v1/')
        .done(response => {

          if (window.current_user_lookup===user_id && response.unattempted_contacts.length > 0) {
            //contacts assigned with no contact attempt
            let unattemped_contacts_html = ``
            response.unattempted_contacts.forEach(contact => {
              let days = contact.time / 60 / 60 / 24;
              let line = escaped_translations.no_contact_attempt_time
              .replace('%1$s', window.lodash.escape(contact.name))
              .replace('%2$s', days.toFixed(1))
              unattemped_contacts_html += `<li>
          <a href="${window.wpApiShare.site_url}/contacts/${window.lodash.escape(contact.ID)}" target="_blank">
              ${window.lodash.escape(line)}
          </a> </li>`
            })
            $('#unattempted_contacts').html(unattemped_contacts_html)
          } else {
            $('#unattempted_contacts').html('')
          }

        }).catch((e) => {
          console.log('error in unattempted_contacts')
          console.log(e)
        })

        /* contact_attempts */
        window.makeRequest("get", `user?user=${user_id}&section=contact_attempts`, null, 'user-management/v1/')
        .done(response => {

          if (window.current_user_lookup===user_id && response.contact_attempts.length > 0) {
            //contact assigned to contact attempt
            let attempted_contacts_html = ``
            let avg_contact_attempt = 0
            response.contact_attempts.forEach(contact => {
              let days = contact.time / 60 / 60 / 24;
              avg_contact_attempt += days
              let line = escaped_translations.contact_attempt_time
              .replace('%1$s', window.lodash.escape(contact.name))
              .replace('%2$s', window.moment.unix(contact.date_attempted).format("MMM Do"))
              .replace('%3$s', days.toFixed(1))
              attempted_contacts_html += `<li>
          <a href="${window.wpApiShare.site_url}/contacts/${window.lodash.escape(contact.ID)}" target="_blank">
              ${window.lodash.escape(line)}
          </a> </li>`
            })
            $('#contact_attempts').html(attempted_contacts_html)
            $('#avg_contact_attempt').html(avg_contact_attempt===0 ? '-':(avg_contact_attempt / response.contact_attempts.length).toFixed(1))
          } else {
            $('#contact_attempts').html('')
            $('#avg_contact_attempt').html('')
          }

        }).catch((e) => {
          console.log('error in contact_attempts')
          console.log(e)
        })
      }
    })

    /* activity */
    window.makeRequest( "get", `user?user=${user_id}&section=activity`, null , 'user-management/v1/')
    .done(activity=>{
      if ( window.current_user_lookup === user_id ) {
        let activity_div = $('#activity')
        let activity_html = window.dtActivityLogs.makeActivityList(activity.user_activity, escaped_translations)
        activity_div.html(activity_html)
      }
    }).catch((e)=>{
      console.log( 'error in activity')
      console.log( e)
    })

    /* days active */
    window.makeRequest( "get", `user?user=${user_id}&section=days_active`, null , 'user-management/v1/')
    .done(days=>{
      if ( window.current_user_lookup === user_id ) {
        let days_of_the_week = window.SHAREDFUNCTIONS.get_days_of_the_week_initials('short')
        const daysActiveTranslated = days.days_active.map((day) => {
          // translations start week with Sun, php gmdate starts week with Monday
          const weekNumber = parseInt(day.weekday_number) === 7 ? 0 : parseInt(day.weekday_number)
          const translatedWeekDay = days_of_the_week[weekNumber]
          return {
            ...day,
            weekday: translatedWeekDay ? translatedWeekDay : day.weekday
          }
        })
        day_activity_chart(daysActiveTranslated)
      }
    }).catch((e)=>{
      console.log( 'error in days active')
      console.log( e)
    })
  }

  if( window.wpApiShare.url_path.includes('user-management/users') ) {
  } else if ( window.wpApiShare.url_path.includes('user-management/user/')){
    window.open_user_modal( window.wpApiShare.url_path.replace( 'user-management/user','').replace('/','') )
  }
  if( window.wpApiShare.url_path.includes('user-management/add-user') ) {
    write_add_user()
  }

  let update_user = ( user_id, key, value )=>{
    let data =  {
      [key]: value
    }
    return window.makeRequest( "POST", `user?user=${user_id}`, data , 'user-management/v1/' )
  }

  let user_details = [];

  function setup_user_roles(user_data){
    $('#user_roles_list input').prop('checked', false);
    if ( user_data.roles ){
      window.lodash.forOwn( user_data.roles, role=>{
        $(`#user_roles_list [value="${role}"]`).prop('checked', true)
        if ( role === "partner" || role === "marketer" ){
          $(`#allowed_sources_options`).show()
          $('#allowed_sources_options input').prop('checked', false);
          user_data.allowed_sources.forEach(source=>{
            $(`#allowed_sources_options [value="${source}"]`).prop('checked', true)
          })
          if ( user_data.allowed_sources.length === 0 ){
            $(`#allowed_sources_options [value="all"]`).prop('checked', true)
          }
        } else {
          $(`#allowed_sources_options`).hide()
        }
      })
    }
  }

  $('#save_roles').on("click", function () {
    $(this).toggleClass('loading', true)
    let roles = [];
    $('#user_roles_list input:checked').each(function () {
      roles.push($(this).val())
    })
    update_user( window.current_user_lookup, 'save_roles', roles).then((roles)=>{
      user_details.roles = roles
      setup_user_roles( user_details )
      $(this).toggleClass('loading', false)
    }).catch(()=>{
      $(this).toggleClass('loading', false)
    })

  })
  $('#save_allowed_sources').on("click", function () {
    $(this).toggleClass('loading', true)
    let sources = [];
    $('#allowed_sources_options input:checked').each(function () {
      sources.push($(this).val())
    })
    update_user( window.current_user_lookup, 'allowed_sources', sources).then((user_data)=>{
      user_details.allowed_sources = user_data
      setup_user_roles( user_details )
      $(this).toggleClass('loading', false)
    }).catch(()=>{
      $(this).toggleClass('loading', false)
    })
  })

  let date_unavailable_table = $('#unavailable-list')
  date_unavailable_table.empty()
  let display_dates_unavailable = (list = [] )=>{
    date_unavailable_table.empty()
    let rows = ``
    list.forEach(range=>{
      rows += `<tr>
        <td>${window.lodash.escape(range.start_date)}</td>
        <td>${window.lodash.escape(range.end_date)}</td>
        <td><button class="button remove_dates_unavailable" data-id="${window.lodash.escape(range.id)}">${ escaped_translations.remove }</button></td>
      </tr>`
    })
    date_unavailable_table.html(rows)
  }
  $( document).on( 'click', '.remove_dates_unavailable', function () {
    let id = $(this).data('id');
    update_user( window.current_user_lookup, 'remove_unavailability', id).then((resp)=>{
      display_dates_unavailable(resp)
    })
  })

  $('#corresponds_to_contact_link').on( "click", function (){
    if ( user_details.corresponds_to_contact ){
      window.open(window.wpApiShare.site_url + "/contacts/" + user_details.corresponds_to_contact, '_blank');
    }
  })
  $('#wp_admin_edit_user').on( "click", function (){
    if ( user_details.user_id ){
      window.open(window.wpApiShare.site_url + "/wp-admin/user-edit.php?user_id=" + user_details.user_id, '_blank');
    }
  })
  $('#reset_user_pwd_email').on("click", function (e) {
    e.preventDefault();

    let button_icon = $('#reset_user_pwd_email_icon');
    button_icon.css('margin-left', '10px');
    button_icon.addClass('active');
    button_icon.addClass('loading-spinner');

    send_user_pwd_reset_email(user_details['user_id'], user_details['user_email']).then((response) => {
      button_icon.removeClass('active');
      button_icon.removeClass('loading-spinner');
      button_icon.css('font-size', '20px');

      if (response && response['sent']) {
        button_icon.addClass('mdi mdi-email-check-outline');
        button_icon.css('color', '#01d701');

      } else {
        button_icon.addClass('mdi mdi-email-remove-outline');
        button_icon.css('color', '#d70101');
      }
    });
  });

  let send_user_pwd_reset_email = (user_id, user_email) => {
    let data = {
      'id': user_id,
      'email': user_email
    }
    return window.makeRequest('POST', `send_pwd_reset_email`, data, 'user-management/v1/');
  }

  /**
   * Locations
   */
  if ( typeof window.dtMapbox === "undefined" && $('.js-typeahead-location_grid').length) {
    let typeaheadTotals = {}
    if (!window.Typeahead['.js-typeahead-location_grid'] ){
      $.typeahead({
        input: '.js-typeahead-location_grid',
        minLength: 0,
        accent: true,
        searchOnFocus: true,
        maxItem: 20,
        dropdownFilter: [{
          key: 'group',
          value: 'focus',
          template: window.lodash.escape(window.wpApiShare.translations.regions_of_focus),
          all: window.lodash.escape(window.wpApiShare.translations.all_locations),
        }],
        source: {
          focus: {
            display: "name",
            ajax: {
              url: window.wpApiShare.root + 'dt/v1/mapping_module/search_location_grid_by_name',
              data: {
                s: "{{query}}",
                filter: function () {
                  return window.lodash.get(window.Typeahead['.js-typeahead-location_grid'].filters.dropdown, 'value', 'all')
                }
              },
              beforeSend: function (xhr) {
                xhr.setRequestHeader('X-WP-Nonce', window.wpApiShare.nonce);
              },
              callback: {
                done: function (data) {
                  if (typeof window.typeaheadTotals !== "undefined") {
                    window.typeaheadTotals.field = data.total
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
          data: function () {
            return [];
          }, callback: {
            onCancel: function (node, item) {
              update_user( window.current_user_lookup, 'remove_location', item.ID)
            }
          }
        },
        callback: {
          onClick: function(node, a, item, event){
            update_user( window.current_user_lookup, 'add_location', item.ID)
          },
          onReady(){
            this.filters.dropdown = {key: "group", value: "focus", template: window.lodash.escape(window.wpApiShare.translations.regions_of_focus)}
            this.container
            .removeClass("filter")
            .find("." + this.options.selector.filterButton)
            .html(window.lodash.escape(window.wpApiShare.translations.regions_of_focus));
          },
          onResult: function (node, query, result, resultCount) {
            resultCount = typeaheadTotals.location_grid
            let text = window.TYPEAHEADS.typeaheadHelpText(resultCount, query, result)
            $('#location_grid-result-container').html(text);
          },
          onHideLayout: function () {
            $('#location_grid-result-container').html("");
          }
        }
      });
    }
  }


  $('textarea.text-input, input.text-input').change(function(){
    const id = $(this).attr('id')
    const val = $(this).val()
    $(`#${id}-spinner`).addClass('active')
    update_user( window.current_user_lookup, id, val ).then(()=> {
      $(`#${id}-spinner`).removeClass('active')
    })
  })
  $('select.select-field').change(e => {
    const id = $(e.currentTarget).attr('id')
    const val = $(e.currentTarget).val()
    $(`#${id}-spinner`).addClass('active')

    update_user( window.current_user_lookup, id, val ).then(()=> {
      $(`#${id}-spinner`).removeClass('active')
    })
  })
  $('button.dt_multi_select').on('click',function () {
    let fieldKey = $(this).data("field-key")
    let optionKey = $(this).attr('id')
    $(`#${fieldKey}-spinner`).addClass("active")
    let field = $(`[data-field-key="${fieldKey}"]#${optionKey}`)
    field.addClass("submitting-select-button")
    let action = "add"
    let update_request = null
    if (field.hasClass("selected-select-button")){
      action = "delete"
      update_request = update_user( window.current_user_lookup,'remove_' + fieldKey, optionKey )
    } else {
      field.removeClass("empty-select-button")
      field.addClass("selected-select-button")
      update_request = update_user( window.current_user_lookup, 'add_' + fieldKey, optionKey )
    }
    update_request.then(()=>{
      field.removeClass("submitting-select-button selected-select-button")
      field.blur();
      field.addClass( action === "delete" ? "empty-select-button" : "selected-select-button");
      $(`#${fieldKey}-spinner`).removeClass("active")
    }).catch(err=>{
      field.removeClass("submitting-select-button selected-select-button")
      field.addClass( action === "add" ? "empty-select-button" : "selected-select-button")
      window.handleAjaxError(err)
    })
  })


  function day_activity_chart( days_active ) {
    window.am4core.ready(function() {

      window.am4core.useTheme(window.am4themes_animated);

      let chart = window.am4core.create("day_activity_chart", window.am4charts.XYChart);
      chart.maskBullets = false;

      let xAxis = chart.xAxes.push(new window.am4charts.CategoryAxis());
      let yAxis = chart.yAxes.push(new window.am4charts.CategoryAxis());

      xAxis.dataFields.category = "week_start";
      yAxis.dataFields.category = "weekday";

      // xAxis.renderer.grid.template.disabled = true;
      xAxis.renderer.minGridDistance = 100;

      // yAxis.renderer.grid.template.disabled = true;
      yAxis.renderer.inversed = true;
      yAxis.renderer.minGridDistance = 10;

      let series = chart.series.push(new window.am4charts.ColumnSeries());
      series.dataFields.categoryY = "weekday";
      series.dataFields.categoryX = "week_start";
      series.dataFields.value = "activity";
      series.sequencedInterpolation = true;
      series.defaultState.transitionDuration = 3000;

      let bgColor = new window.am4core.InterfaceColorSet().getFor("background");

      let columnTemplate = series.columns.template;
      columnTemplate.strokeWidth = 1;
      columnTemplate.strokeOpacity = 0.2;
      // columnTemplate.stroke = bgColor;
      columnTemplate.tooltipText = "{weekday}, {day}: {activity_count}";
      columnTemplate.width = window.am4core.percent(100);
      columnTemplate.height = window.am4core.percent(100);

      series.heatRules.push({
        target: columnTemplate,
        property: "fill",
        // min: window.am4core.color('#deeff8'),
        min: window.am4core.color(bgColor),
        max: chart.colors.getIndex(0)
      });

      chart.data = days_active
    });
  }

  function status_pie_chart(contact_statuses){

    if ( contact_statuses.length === 0 ) {
      $('#status_chart_div').empty()
      return
    }

    window.am4core.useTheme(window.am4themes_animated);

    let container = window.am4core.create("status_chart_div", window.am4core.Container);
    container.width = window.am4core.percent(100);
    container.height = window.am4core.percent(100);
    container.layout = "vertical";


    let chart = container.createChild(window.am4charts.PieChart);

    // Add data
    chart.data = contact_statuses

    // Add and configure Series
    let pieSeries = chart.series.push(new window.am4charts.PieSeries());
    pieSeries.dataFields.value = "count";
    pieSeries.dataFields.category = "status";
    pieSeries.slices.template.states.getKey("active").properties.shiftRadius = 0;
    pieSeries.labels.template.text = "{category}: {value.percent.formatNumber('#.#')}% ({value}) ";

    pieSeries.slices.template.events.on("hit", function(event) {
      selectSlice(event.target.dataItem);
    })

    let chart2 = container.createChild(window.am4charts.PieChart);
    chart2.width = window.am4core.percent(80);
    chart2.radius = window.am4core.percent(80);

    // Add and configure Series
    let pieSeries2 = chart2.series.push(new window.am4charts.PieSeries());
    pieSeries2.dataFields.value = "count";
    pieSeries2.dataFields.category = "reason";
    pieSeries2.slices.template.states.getKey("active").properties.shiftRadius = 0;
    pieSeries2.labels.template.disabled = true;
    pieSeries2.ticks.template.disabled = true;
    pieSeries2.alignLabels = false;
    pieSeries2.events.on("positionchanged", updateLines);

    let interfaceColors = new window.am4core.InterfaceColorSet();

    let line1 = container.createChild(window.am4core.Line);
    line1.strokeDasharray = "2,2";
    line1.strokeOpacity = 0.5;
    line1.stroke = interfaceColors.getFor("alternativeBackground");
    line1.isMeasured = false;

    let line2 = container.createChild(window.am4core.Line);
    line2.strokeDasharray = "2,2";
    line2.strokeOpacity = 0.5;
    line2.stroke = interfaceColors.getFor("alternativeBackground");
    line2.isMeasured = false;

    let selectedSlice;

    function selectSlice(dataItem) {
      selectedSlice = dataItem.slice;
      let fill = selectedSlice.fill;
      let count = dataItem.dataContext.reasons.length;
      pieSeries2.colors.list = [];
      for (let i = 0; i < count; i++) {
        pieSeries2.colors.list.push(fill.brighten(i * 2 / count));
      }
      chart2.data = dataItem.dataContext.reasons;
      pieSeries2.appear();

      let middleAngle = selectedSlice.middleAngle;
      let firstAngle = pieSeries.slices.getIndex(0).startAngle;
      let animation = pieSeries.animate([{ property: "startAngle", to: firstAngle - middleAngle }, { property: "endAngle", to: firstAngle - middleAngle + 360 }], 600, window.am4core.ease.sinOut);
      animation.events.on("animationprogress", updateLines);

      selectedSlice.events.on("transformed", updateLines);
    }

    function updateLines() {
      if (selectedSlice) {
        let p11 = { x: selectedSlice.radius * window.am4core.math.cos(selectedSlice.startAngle), y: selectedSlice.radius * window.am4core.math.sin(selectedSlice.startAngle) };
        let p12 = { x: selectedSlice.radius * window.am4core.math.cos(selectedSlice.startAngle + selectedSlice.arc), y: selectedSlice.radius * window.am4core.math.sin(selectedSlice.startAngle + selectedSlice.arc) };

        p11 = window.am4core.utils.spritePointToSvg(p11, selectedSlice);
        p12 = window.am4core.utils.spritePointToSvg(p12, selectedSlice);

        let p21 = { x: 0, y: -pieSeries2.pixelRadius };
        let p22 = { x: 0, y: pieSeries2.pixelRadius };

        p21 = window.am4core.utils.spritePointToSvg(p21, pieSeries2);
        p22 = window.am4core.utils.spritePointToSvg(p22, pieSeries2);

        line1.x1 = p11.x;
        line1.x2 = p21.x;
        line1.y1 = p11.y;
        line1.y2 = p21.y;

        line2.x1 = p12.x;
        line2.x2 = p22.x;
        line2.y1 = p12.y;
        line2.y2 = p22.y;
      }
    }

  }

  function write_add_user() {
    const showOptionsButton = $('#show-hidden-fields')
    const hideOptionsButton = $('#hide-hidden-fields')
    const hiddenFields = $('.hidden-fields')

    showOptionsButton.on('click', function() {
      hiddenFields.show()
      showOptionsButton.hide()
      hideOptionsButton.show()
    })

    hideOptionsButton.on('click', function() {
      hiddenFields.hide()
      showOptionsButton.show()
      hideOptionsButton.hide()
    })

    const showOptionalFields = $('#show-optional-fields')
    const hideOptionalFields = $('#hide-optional-fields')
    const optionalFields = $('#optional-fields')

    showOptionalFields.on('click', function() {
      showOptionalFields.hide()
      hideOptionalFields.show()
      optionalFields.removeClass('show-for-medium')
    })

    hideOptionalFields.on('click', function() {
      showOptionalFields.show()
      hideOptionalFields.hide()
      optionalFields.addClass('show-for-medium')
    })

    $('#new-user-language-dropdown').html(write_language_dropdown(dt_user_management_localized.language_dropdown, dt_user_management_localized.default_language))

    let result_div = $('#result-link')
    let submit_button = $('#create-user')

    $(document).on("submit", function(ev) {
      ev.preventDefault();
      if ( typeof window.contact_record !== 'undefined' ) {
        $('#confirm-user-upgrade').foundation('open');
      } else {
        create_user()
      }
    });

    $('#continue-user-creation').on('click', function (){
      let corresponds_to_contact = null
      if ( typeof window.contact_record !== 'undefined' ) {
        corresponds_to_contact = window.contact_record.ID
      }
      create_user(corresponds_to_contact)
    })

    $('#continue-archive-comments').on('click',function (){
      let old_corresponds_to_contact = null
      if ( typeof window.contact_record !== 'undefined' ) {
        old_corresponds_to_contact = window.contact_record.ID
      }
      create_user(old_corresponds_to_contact, true)
    })

    let create_user = (corresponds_to_contact, archive_comments = false)=>{

      let name = $('#name').val()
      let email = $('#email').val()
      let locale = $('#locale').val();

      const username = $('#username').val()
      const password = $('#password').val()

      const optionalFields = document.querySelectorAll('[data-optional=""]')
      const optionalValues = {}

      optionalFields.forEach((node) => {
        if (node.value) {
          optionalValues[node.id] = node.value
        }
      })

      let roles = [];
      $('#user_roles_list input:checked').each(function () {
        roles.push($(this).val())
      })

      if ( name !== '' && email !== '' )  {
        $('#create-user').addClass('loading')
        submit_button.prop('disabled', true)

        return window.makeRequest(
          "POST",
          `users/create`,
          {
            "user-email": email,
            "user-display": name,
            "user-username": username || null,
            "user-password": password || null,
            "user-optional-fields": optionalValues !== {} ? optionalValues : null,
            "corresponds_to_contact": corresponds_to_contact,
            "locale": locale,
            'user-roles':roles,
            return_contact: true,
            archive_comments: archive_comments,
          })
        .done(response=>{
          const { user_id, corresponds_to_contact: contact_id } = response
          result_div.html('')
          if ( dt_user_management_localized.has_permission ) {
            result_div.append(`<a href="${window.lodash.escape(window.wpApiShare.site_url)}/user-management/user/${window.lodash.escape(user_id)}">
              ${ escaped_translations.view_new_user }</a>
            `)
          }
          result_div.append(`<br /><a href="${window.lodash.escape(window.wpApiShare.site_url)}/contacts/${window.lodash.escape(contact_id)}">
              ${ escaped_translations.view_new_contact }</a>
            `)
          $('#new-user-form').empty()
          return response
        })
        .catch(err=>{
          $('#create-user').removeClass('loading')
          submit_button.prop('disabled', false)
          if ( err.responseJSON?.code === 'email_exists' ) {
            result_div.html(`${ escaped_translations.email_already_in_system }`)
          } else if ( err.responseJSON?.code === 'username_exists' ) {
            result_div.html(`${ escaped_translations.username_in_system }`)
          } else {
            result_div.html(`Oops. Something went wrong.`)
          }
          return false;
        })
      }
    }

    function getContact(id, isUser = false, overwriteTypeahead = false) {
      $('.loading-spinner').addClass('active')
      window.makeRequest('GET', 'contacts/'+id, null, 'dt-posts/v2/' )
        .done(function(response){

          if (overwriteTypeahead) {
            $(".js-typeahead-subassigned").val(window.lodash.escape(response.name))
          }
          if ( isUser || ( response.corresponds_to_user >= 0 ) ) {
            $('#name').val( window.lodash.escape(response.name) )
            if ( response.contact_email && response.contact_email.length > 0 ) {
              $('#email').val( window.lodash.escape(response.contact_email[0].value) )
            }
            $('#contact-result').html(escaped_translations.already_user)
            if ( window.dt_user_management_localized.has_permission ) {
              $('#contact-result').append(`<br /> <a href="${window.lodash.escape(window.wpApiShare.site_url)}/user-management/user/${window.lodash.escape(response.corresponds_to_user)}">${escaped_translations.view_user}</a>`)
            }
            $('#contact-result').append(`<br /> <a href="${window.lodash.escape(window.wpApiShare.site_url)}/contacts/${id}">${escaped_translations.view_contact}</a>`)
          } else {
            window.contact_record = response
            submit_button.prop('disabled', false)
            $('#name').val( window.lodash.escape(response.title) )
            if ( response.contact_email && response.contact_email[0] !== 'undefined' ) {
              $('#email').val( window.lodash.escape(response.contact_email[0].value) )
            }

          }
          $('.loading-spinner').removeClass('active')
        })
    }

    ["subassigned"].forEach(field_id=>{
      $.typeahead({
        input: `.js-typeahead-${field_id}`,
        minLength: 0,
        accent: true,
        maxItem: 30,
        searchOnFocus: true,
        template: window.TYPEAHEADS.contactListRowTemplate,
        source: window.TYPEAHEADS.typeaheadContactsSource(),
        display: "name",
        templateValue: "{{name}}",
        dynamic: true,
        callback: {
          onClick: function(node, a, item, event){
            submit_button.prop('disabled', true)

            getContact(item.ID, item.user)
          },
          onResult: function (node, query, result, resultCount) {
            let text = window.TYPEAHEADS.typeaheadHelpText(resultCount, query, result)
            $(`#${field_id}-result-container`).html(text);
            submit_button.prop('disabled', false)
            $('#contact-result').html(``)
          },
          onHideLayout: function () {
            $(`#${field_id}-result-container`).html("");
          },
          onReady: function () {
            if (field_id === "subassigned"){
            }
          },
          onShowLayout (){
          }
        }
      })
    })

    // Prefill the form if contact_id is in the query params
    const url = new URL(window.location.href)
    let contactId = url.searchParams.get('contact_id')
    if ( contactId !== null && contactId !== '' && !isNaN(contactId) ) {
      getContact(parseInt(contactId), false, true)
    }
  }

  function write_language_dropdown(translations, default_language) {
      let select = '<select name="locale" id="locale">';
      for ( const translation in translations ) {
        select += `<option value="${window.lodash.escape(translations[translation].language )}" ${(translations[translation].language === default_language) ? 'selected' : '' } > ${(translations[translation].flag ? translations[translation].flag + ' ' : '')} ${window.lodash.escape( translations[translation].native_name )}</option>`
      }
      select += '</select>'
      return select;
  }

})
