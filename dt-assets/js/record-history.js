jQuery(document).ready(function ($) {

  // Global Variables
  const date_format_short = 'YYYY-MM-DD';
  const date_format_long = 'MMMM Do, YYYY, hh:mm:ss A';
  const date_format_pretty_short = 'MMMM Do, YYYY';
  const post = window.record_history_settings.post;
  const post_settings = window.record_history_settings.post_settings;

  // Event Listeners
  $(document).on('open.zf.reveal', '#record_history_modal[data-reveal]', function () {
    init_record_history_modal();
  });

  $(document).on('click', '.record-history-activity-block-controls', function () {
    handle_revert_request($(this).find('#record_history_activity_block_timestamp_id').val(), $(this).find('#record_history_activity_block_timestamp').val());
  });

  $(document).on('click', '#record_history_all_activities_switch', function () {
    handle_show_all_activities();
  });

  $(document).on('change', '#record_history_calendar', function () {
    handle_filtered_date_selection();
  });

  // Helper Functions
  function init_record_history_modal() {
    let record_history_select = $('#record_history_calendar');

    // Fetch initial corresponding activities, to be used in date filter select.
    handle_activity_history_refresh({result_order: 'DESC'}, function (activities) {

      // Package filtered dates.
      let filtered_dates = {};
      $.each(activities, function (idx, activity) {
        let hist_time = moment.unix(parseInt(activity['hist_time']));

        // Default to midnight.
        hist_time.second(0);
        hist_time.minute(0);
        hist_time.hour(0);

        filtered_dates[hist_time.format(date_format_pretty_short)] = hist_time;
      });

      // Clear down any previous entries, apart from the first option.
      record_history_select.find('option').not(':first').remove();

      // Populate history select widget.
      $.each(filtered_dates, function (idx, filtered_date) {
        record_history_select.append(`<option value="${filtered_date.unix()}">${idx}</option>`);
      });
    });

    // By default, and if selected, show all-time activities.
    handle_show_all_activities();
  }

  function handle_filtered_date_selection() {
    let record_history_select = $('#record_history_calendar');

    if (record_history_select.val()) {
      let ts_start = record_history_select.val();
      let ts_end = moment.unix(record_history_select.val()).add(24, 'hours').unix();

      handle_selected_activity_date(ts_start, ts_end, handle_activities_display);
      reset_show_all_activities_switch();
    }
  }

  function is_show_all_activities_switch_checked() {
    return $('#record_history_all_activities_switch').is(':checked');
  }

  function reset_show_all_activities_switch() {
    $('#record_history_all_activities_switch').prop('checked', false);
  }

  function handle_show_all_activities() {
    if (is_show_all_activities_switch_checked()) {
      handle_activity_history_refresh({
        ts_start: 0,
        result_order: 'DESC',
        extra_meta: true

      }, handle_activities_display);

      // Default filter select widget.
      $('#record_history_calendar').val('');
    }
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

  function handle_selected_activity_date(ts_start, ts_end, callback) {

    // Retrieve activities from specified starting point to current date.
    handle_activity_history_refresh({
      ts_start: ts_start,
      ts_end: ts_end,
      result_order: 'DESC',
      extra_meta: true

    }, callback);

  }

  function handle_activities_display(activities) {
    let record_history_activities = $('#record_history_activities');
    record_history_activities.fadeOut('fast', function () {

      // Clear-down existing activities list
      record_history_activities.empty();

      // Iterate and display the latest list
      $.each(activities, function (idx, activity) {

        // Extract/Format values of interest
        let activity_heading = window.lodash.unescape(activity['object_note']);
        let field_label = '---';
        let revert_but_tooltip = window.record_history_settings.translations.revert_but_tooltip;
        let activity_date = moment.unix(parseInt(activity['hist_time'])).format(date_format_long);
        let owner_name = (activity['name']) ? window.lodash.unescape(activity['name']):'';
        let owner_gravatar = (activity['gravatar']) ? `<img src="${activity['gravatar']}"/>` : `<span class="mdi mdi-robot-confused-outline" style="font-size: 20px;"></span>`

        // Enable activity heading url links.
        let urls = activity_heading.match(/(((ftp|https?):\/\/)[\-\w@:%_\+.~#?,&\/\/=]+)/g);
        if (urls!=null) {
          $.each(urls, function (url_idx, url) {

            // Identify first associated link label.
            let url_labels = activity_heading.match(/\[(.*?)\]/);

            // Replace raw link with converted html link.
            if (url_labels!=null) {
              let raw_link = url_labels[0] + '(' + url + ')';
              activity_heading = window.lodash.replace(activity_heading, raw_link, `<a href="${window.lodash.escape(url)}" target="_blank">${window.lodash.escape(url_labels[1])}</a>`);
            } else {
              activity_heading = window.lodash.replace(activity_heading, new RegExp(url, 'g'), `<a href="${window.lodash.escape(url)}" target="_blank">${window.lodash.escape(url)}</a>`);
            }
          });
        }

        // Convert activity heading epoch timestamps to readable dates.
        if (activity['field_type']==='date') {
          let timestamps = activity_heading.match(/\d{10}/g);
          if (timestamps!=null) {
            $.each(timestamps, function (ts_idx, ts) {
              activity_heading = window.lodash.replace(activity_heading, new RegExp(ts, 'g'), moment.unix(parseInt(ts)).format(date_format_pretty_short));
            });
          }
        }

        // Field label to be sourced accordingly, based on incoming field type.
        if (window.lodash.includes(['connection to', 'connection from'], activity['field_type'])) {
          field_label = 'connection';

        } else if (window.lodash.isEmpty(activity['field_type']) && window.lodash.startsWith(activity['meta_key'], 'contact_')) {
          let meta_key = activity['meta_key'];
          field_label = meta_key.substring(0, meta_key.indexOf('_', 'contact_'.length));

        } else if (!window.lodash.isEmpty(post_settings['fields'][activity['meta_key']])) {
          field_label = post_settings['fields'][activity['meta_key']]['name'];
        }

        // Build activity block html
        let html = `
            <div class="grid-container record-history-activity-block">
                <div class="grid-x">
                    <div class="cell small-11 record-history-activity-block-body">
                        <span class="record-history-activity-heading">${activity_heading} (<span style="color: #989898;">${window.lodash.escape(field_label)}</span>)</span><br>
                        <span class="record-history-activity-gravatar">
                            ${owner_gravatar}
                            <span class="record-history-activity-owner" style="margin-right: 10px;">${owner_name}</span>
                            <span class="record-history-activity-date">${window.lodash.escape(activity_date)}</span>
                        </span>
                    </div>
                    <div class="cell small-1 record-history-activity-block-controls">
                        <input type="hidden" id="record_history_activity_block_timestamp_id" value="${activity['histid']}"/>
                        <input type="hidden" id="record_history_activity_block_timestamp" value="${activity['hist_time']}"/>
                        <button class="button record-history-activity-block-controls-revert-but" title="${window.lodash.escape(revert_but_tooltip)}"><span class="mdi mdi-history" style="font-size: 20px;"></span></button>
                    </div>
                </div>
            </div>`;

        record_history_activities.append(html);

      });

      // Inform user if no activities found!
      if (window.lodash.isEmpty(activities)) {
        record_history_activities.empty();
        record_history_activities.append(`<div style="text-align: center;"><span style="font-size: 50px;"><i class="mdi mdi-clock-remove-outline"/></span></div>`);
      }

      // Display the latest shape
      record_history_activities.fadeIn('fast');

    });
  }

  function handle_revert_request(start_id, timestamp) {
    let timestamp_formatted = moment.unix(parseInt(timestamp)).format(date_format_long);
    let confirm_text = window.lodash.escape(window.record_history_settings.translations.revert_confirm_text).replace('%s', timestamp_formatted);
    if (confirm(confirm_text)) {

      // On confirmation, start revert process
      if (post && post['post_type'] && post['ID']) {
        window.API.revert_activity_history(post['post_type'], post['ID'], {
          ts_start_id: start_id,
          ts_start: timestamp,
          result_order: 'DESC',
          extra_meta: false

        }).then(result => {

          // Refresh record view
          window.location = window.record_history_settings.site_url + post['post_type'] + '/' + post['ID'];

        }).fail(failure => {
          console.log(failure);

          // Display detected exception text before returning back to post type record.
          if (failure['responseText']) {
            alert(failure['responseText']);
          }

          // Refresh record view
          window.location = window.record_history_settings.site_url + post['post_type'] + '/' + post['ID'];
        });
      }
    }
  }

});
