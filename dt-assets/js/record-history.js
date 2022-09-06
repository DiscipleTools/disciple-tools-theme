jQuery(document).ready(function ($) {

  const date_format_short = 'YYYY-MM-DD';
  const date_format_long = 'MMMM Do, YYYY, hh:mm:ss A';
  const post = window.record_history_settings.post;
  const post_settings = window.record_history_settings.post_settings;

  $(document).on('open.zf.reveal', '#record_history_modal[data-reveal]', function () {
    init_record_history_modal();
  });

  $(document).on('click', '.record-history-activity-block', function () {
    handle_revert_request($(this).find('#record_history_activity_block_timestamp').val());
  });

  function init_record_history_modal() {

    // Remove any previously instantiated litepicker instances
    $('#record_history_modal').find('.litepicker').remove();

    // Refresh activity based on default current month
    handle_activity_history_refresh({}, function (activities) {

      // First, convert and extract activity unix timestamps to usable date formats
      let dates_of_interest = extract_activity_timestamps_as_formatted_dates(activities);

      // Instantiate date picker, based on DoI
      let lite_picker = new Litepicker({
        element: $('#record_history_calendar')[0],
        inlineMode: true,
        singleMode: true,
        highlightedDays: dates_of_interest,
        lockDaysFilter: (date_start, date_end, picked_dates) => {
          return !dates_of_interest.includes(date_start.format(date_format_short));
        },
        setup: (picker) => {
          picker.on('selected', (date_start, date_end) => {
            handle_selected_activity_date(date_start, handle_activities_display);
          });
          picker.on('change:month', (date, calendar_idx) => {
            handle_changed_month(date, picker);
          });
        }
      });

      // Default to most recent updates, assuming valid dates of interest
      if (dates_of_interest.length > 0) {
        lite_picker.gotoDate(dates_of_interest[0]);
        lite_picker.setDate(dates_of_interest[0]);
      }
    });
  }

  function handle_activity_history_refresh(request_args, callback) {

    // Assuming a valid post, refresh activity history
    if (post && post['post_type'] && post['ID']) {
      window.API.get_activity_history(post['post_type'], post['ID'], request_args).then(activities => {

        // Execute callback with returned activities
        callback(activities);

      });
    }
  }

  function extract_activity_timestamps_as_formatted_dates(activities = []) {
    let formatted_dates = window.lodash.map(activities, function (activity) {
      return moment.unix(parseInt(activity['hist_time'])).format(date_format_short);
    });

    // Return formatted dates array, duplicate free...!
    return window.lodash.uniq(formatted_dates);
  }

  function handle_changed_month(date, picker) {

    // Proceed as normal for a selected date
    handle_selected_activity_date(date, function (activities) {

      // Assuming valid activities returned, update picker options state
      if (activities && activities.length > 0) {

        // Convert and extract activity unix timestamps to usable date formats
        let dates_of_interest = extract_activity_timestamps_as_formatted_dates(activities);

        // Determine month & year changed to
        let changed_month = '' + (date.getMonth() + 1);
        changed_month = (changed_month.length < 2) ? '0' + changed_month : changed_month;
        let changed_year = date.getFullYear();

        // Determine if returned dates of interest contain any changed month activities
        let changed_month_dates = window.lodash.filter(dates_of_interest, function (d) {
          // TODO: Update logic if date format is changed...!
          return window.lodash.includes(d, changed_year + '-' + changed_month + '-');
        });

        // Only adjust picker options if we have new dates for changed month
        if (changed_month_dates.length > 0) {

          picker.setOptions({
            highlightedDays: changed_month_dates,
            lockDaysFilter: (date_start, date_end, picked_dates) => {
              return !changed_month_dates.includes(date_start.format(date_format_short));
            }
          });

          // Set month based on first identified changed date
          picker.gotoDate(changed_month_dates[0]);

        }

      }
    });

  }

  function handle_selected_activity_date(date, callback) {

    // Adjust selected date's time to start-of-day
    date.setHours(0, 0, 0, 0);
    let start = date.getTime() / 1000;

    // Retrieve activities from current point to selected date, as point of starting interest
    handle_activity_history_refresh({
      ts_start: start,
      result_order: 'ASC',
      extra_meta: true

    }, callback);

  }

  function handle_activities_display(activities) {
    $('#record_history_activities').fadeOut('fast', function () {

      // Clear-down existing activities list
      $('#record_history_activities').empty();

      // Iterate and display latest list
      $.each(activities, function (idx, activity) {

        // Extract/Format values of interest
        let activity_heading = activity['object_note'];
        let field_label = '---';
        let revert_but_tooltip = window.record_history_settings.translations.revert_but_tooltip;
        let activity_date = moment.unix(parseInt(activity['hist_time'])).format(date_format_long);
        let owner_name = (activity['name']) ? activity['name'] : '';
        let owner_gravatar = (activity['gravatar']) ? `<img src="${activity['gravatar']}"/>` : `<span class="mdi mdi-robot-confused-outline" style="font-size: 20px;"></span>`

        // Field label to be sourced accordingly, based on incoming field type.
        if (window.lodash.includes(['connection to', 'connection from'], activity['field_type'])) {
          activity_heading = activity['action'] + ': ' + activity['meta_key'];
          field_label = 'connection';

        } else if (window.lodash.isEmpty(activity['field_type']) && window.lodash.startsWith(activity['meta_key'], 'contact_')) {
          if (window.lodash.startsWith(activity['meta_key'], 'contact_phone_')) {
            field_label = 'contact_phone';

          } else if (window.lodash.startsWith(activity['meta_key'], 'contact_email_')) {
            field_label = 'contact_email';

          } else if (window.lodash.startsWith(activity['meta_key'], 'contact_address_')) {
            field_label = 'contact_address';

          } else if (window.lodash.startsWith(activity['meta_key'], 'contact_facebook_')) {
            field_label = 'contact_facebook';

          } else if (window.lodash.startsWith(activity['meta_key'], 'contact_twitter_')) {
            field_label = 'contact_twitter';

          } else if (window.lodash.startsWith(activity['meta_key'], 'contact_other_')) {
            field_label = 'contact_other';

          } else {
            field_label = 'communication_channel';

          }

        } else if (!window.lodash.isEmpty(post_settings['fields'][activity['meta_key']])) {
          field_label = post_settings['fields'][activity['meta_key']]['name'];
        }

        // Build activity block html
        let html = `
            <div class="grid-container record-history-activity-block">
                <input type="hidden" id="record_history_activity_block_timestamp" value="${activity['hist_time']}"/>
                <div class="grid-x">
                    <div class="cell small-11 record-history-activity-block-body">
                        <span class="record-history-activity-heading">${window.lodash.escape(activity_heading)} (<span style="color: #989898;">${window.lodash.escape(field_label)}</span>)</span><br>
                        <span class="record-history-activity-gravatar">
                            ${owner_gravatar}
                            <span class="record-history-activity-owner" style="margin-right: 10px;">${window.lodash.escape(owner_name)}</span>
                            <span class="record-history-activity-date">${window.lodash.escape(activity_date)}</span>
                        </span>
                    </div>
                    <div class="cell small-1 record-history-activity-block-controls">
                        <button class="button record-history-activity-block-controls-revert-but" title="${window.lodash.escape(revert_but_tooltip)}"><span class="mdi mdi-history" style="font-size: 20px;"></span></button>
                    </div>
                </div>
            </div>`;

        $('#record_history_activities').append(html);

      });

      // Display latest shape
      $('#record_history_activities').fadeIn('fast');

    });
  }

  function handle_revert_request(timestamp) {
    let timestamp_formatted = moment.unix(parseInt(timestamp)).format(date_format_long);
    let confirm_text = window.lodash.escape(window.record_history_settings.translations.revert_confirm_text).replace('%s', timestamp_formatted);
    if (confirm(confirm_text)) {

      // On confirmation, start revert process
      if (post && post['post_type'] && post['ID']) {
        window.API.revert_activity_history(post['post_type'], post['ID'], {
          ts_start: timestamp,
          result_order: 'DESC',
          extra_meta: false

        }).then(result => {

          // Refresh record view
          window.location = window.record_history_settings.site_url + post['post_type'] + '/' + post['ID'];

        });
      }
    }
  }

});
