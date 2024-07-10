jQuery(function () {
  if (
    window.wpApiShare.url_path.startsWith('metrics/records/select_tags_charts')
  ) {
    display_select_tags_charts();
  }
});

const CUMULATIVE_PREFIX = 'cumulative_';

const get_time_metrics_by_year = (post_type, field, year) =>
  window.makeRequest(
    'GET',
    `metrics/time_metrics_by_year/${post_type}/${field}/${year}`,
  );

const get_metrics_cumulative_posts = (data) =>
  window.makeRequest('POST', `metrics/cumulative-posts`, data);

const get_field_settings = (post_type) =>
  window.makeRequest('GET', `metrics/field_settings/${post_type}`);

const escapeObject = window.SHAREDFUNCTIONS.escapeObject;

function display_select_tags_charts() {
  const chart = document.querySelector('#chart');
  const {
    title_select_tags_charts,
    description,
    post_type_select_label,
    post_field_select_label,
    date_select_label,
    all_time,
  } = escapeObject(window.dtMetricsProject.translations);

  const post_type_options = escapeObject(
    window.dtMetricsProject.select_options.post_type_select_options,
  );

  jQuery('#metrics-sidemenu').foundation('down', jQuery('#records-menu'));

  chart.innerHTML = `
    <div class="section-header">${title_select_tags_charts}</div>
      <p>${description}</p>
      <section class="chart-controls">
        <label class="section-subheader" for="post_type_select">${post_type_select_label}</label>
        <select class="select-field" id="post_type_select">
          ${Object.entries(post_type_options).map(([value, label]) => `<option value="${value}">${label}</option>`)}
        </select>
        <label class="section-subheader" for="post_field_select">${post_field_select_label}</label>
        <select class="select-field" id="post_field_select">
          ${build_field_select_options()}
        </select>
        <label class="section-subheader" for="date_select">${date_select_label}</label>
        <select class="select-field" id="date_select">
          ${build_date_select_options(all_time)}
        </select>
        <div id="chart_loading_spinner" class="loading-spinner active"></div>
      </section>
      <hr>
      <section id="chart_area">
        <section id="select_tags_chart" style="display: none">
          <div class="timechart"></div>
          <div class="legend"></div>
        </section>
      </section>`;

  const chart_section = document.querySelector('#chart_area');
  const loading_spinner = document.querySelector('#chart_loading_spinner');
  const field_select_element = document.querySelector('#post_field_select');

  chart_section.addEventListener('datachange', () => {
    create_charts();
    loading_spinner.classList.remove('active');
  });

  document
    .querySelector('#post_type_select')
    .addEventListener('change', (e) => {
      const post_type = e.target.value;
      window.dtMetricsProject.state.post_type = post_type;
      get_field_settings(post_type)
        .promise()
        .then((data) => {
          window.dtMetricsProject.field_settings = data;
          field_select_element.innerHTML = build_field_select_options();

          // Update selection based on detected defaults.
          if (e.detail && e.detail.field) {
            jQuery('#post_field_select').val(e.detail.field);
            field_select_element.dispatchEvent(
              new CustomEvent('change', { detail: e.detail }),
            );
          } else {
            field_select_element.dispatchEvent(new Event('change'));
          }
        })
        .catch((error) => {
          console.log(error);
        });
    });

  field_select_element.addEventListener('change', (e) => {
    window.dtMetricsProject.state.field = e.target.value;
    if (!window.dtMetricsProject.field_settings[e.target.value]) {
      console.error(
        e.target.value,
        'not found in',
        window.dtMetricsProject.field_settings,
      );
      return;
    }
    window.dtMetricsProject.state.field_type =
      window.dtMetricsProject.field_settings[e.target.value].type;

    get_data();
  });

  document.querySelector('#date_select').addEventListener('change', (e) => {
    const year = e.target.value;
    window.dtMetricsProject.state.year = year;
    window.dtMetricsProject.state.chart_view =
      year === 'all-time' ? 'year' : 'month';
    get_data();
  });

  // Handle any available request defaults.
  handle_request_defaults();
}

function get_data() {
  const { post_type, field_type, field, year } = window.dtMetricsProject.state;

  const is_all_time = year === 'all-time';
  const metrics_year = is_all_time ? new Date().getFullYear() : year;
  const data = get_time_metrics_by_year(post_type, field, metrics_year);

  const loading_spinner = document.querySelector('.loading-spinner');
  const chart_element = document.querySelector('#chart_area');
  loading_spinner.classList.add('active');

  // Dynamically update URL parameters.
  const url = new URL(window.location);
  url.searchParams.set('record_type', post_type);
  url.searchParams.set('field', field);
  url.searchParams.set('date', year);
  window.history.pushState(null, document.title, url.search);

  data
    .promise()
    .then((response) => {
      if (!response && !response.data) {
        throw new Error('no data object returned');
      }

      let data = response.data;

      // Capture additional metadata.
      switch (field_type) {
        case 'tags':
        case 'multi_select':
        case 'key_select': {
          window.dtMetricsProject.cumulative_offset =
            response.cumulative_offset !== undefined
              ? response.cumulative_offset
              : 0;
          window.dtMetricsProject.data = format_year_data(data);
          window.dtMetricsProject.data_changes = format_time_units(
            response.changes,
          );
          break;
        }
      }

      // Refresh chart display.
      chart_element.dispatchEvent(new Event('datachange'));
      loading_spinner.classList.remove('active');
    })
    .catch((error) => {
      console.log(error);
      chart_element.dispatchEvent(new Event('datachange'));
      loading_spinner.classList.remove('active');
    });
}

function format_time_units(data) {
  const { year } = window.dtMetricsProject.state;
  const is_all_time = year === 'all-time';
  const count_keys = ['added', 'deleted'];

  let formatted_time_units = {
    added: [],
    deleted: [],
    combined: [],
  };

  if (data) {
    if (is_all_time) {
      jQuery.each(count_keys, function (idx, key) {
        if (data[key] && data[key].length > 0) {
          const min_year = parseInt(data[key][0].time_unit);
          const max_year = parseInt(data[key][data[key].length - 1].time_unit);

          for (let year = min_year; year < max_year + 1; year++) {
            const year_data = data[key].filter(
              (metric) => String(metric.time_unit) === String(year),
            );

            // Ensure null year data hits continue previous counts.
            if (year_data.length === 0) {
              let payload = {
                year: String(year),
                count: 0,
              };
              formatted_time_units[key].push(payload);
            } else {
              jQuery.each(year_data, function (idx, metric) {
                const count = metric.count ? parseInt(metric.count) : 0;

                let payload = {
                  year: String(year),
                  count: count,
                };

                if (metric.selection) {
                  payload[metric.selection] = count;
                }

                formatted_time_units[key].push(payload);
              });
            }
          }
        }
      });
    } else {
      jQuery.each(count_keys, function (idx, key) {
        if (data[key] && data[key].length > 0) {
          const month_labels = window.SHAREDFUNCTIONS.get_months_labels();

          for (let x = 0; x < month_labels.length; x++) {
            const month_number = x + 1;
            const month_data = data[key].filter(
              (metric) => String(metric.time_unit) === String(month_number),
            );

            // Ensure null month data hits continue previous counts.
            if (month_data.length === 0) {
              let payload = {
                month: month_labels[x],
                count: 0,
              };

              formatted_time_units[key].push(payload);
            } else {
              jQuery.each(month_data, function (idx, metric) {
                const count = metric.count ? parseInt(metric.count) : 0;

                let payload = {
                  month: month_labels[x],
                  count: count,
                };

                if (metric.selection) {
                  payload[metric.selection] = count;
                }

                formatted_time_units[key].push(payload);
              });
            }
          }
        }
      });
    }
  }

  // Combine both addition and deletion metrics.
  let combined_time_units = {};
  const time_unit_key = is_all_time ? 'year' : 'month';

  jQuery.each(count_keys, function (idx, key) {
    let count_key = key + '_count';

    jQuery.each(formatted_time_units[key], function (idx, metric) {
      const time_unit = metric[time_unit_key];
      if (!combined_time_units[time_unit]) {
        combined_time_units[time_unit] = {};
      }

      combined_time_units[time_unit][count_key] = metric.count;
    });
  });

  jQuery.each(combined_time_units, function (time_unit, metric) {
    metric[time_unit_key] = time_unit;
    formatted_time_units['combined'].push(metric);
  });

  return formatted_time_units;
}

/**
 * Formats the metric data by filling in any blank years and calculating
 * cumulative counts for the charts
 *
 * Deals with data coming back from different types of fields (e.g. multi_select, date etc.)
 */
function format_year_data(yearly_data) {
  const { field_type } = window.dtMetricsProject.state;

  if (window.dtMetricsProject.multi_fields.includes(field_type)) {
    return format_compound_year_data(yearly_data);
  } else {
    return format_simple_year_data(yearly_data);
  }
}

function format_compound_year_data(yearly_data) {
  if (yearly_data.length === 0) return yearly_data;

  const keys = get_data_keys(yearly_data);

  let cumulative_totals = {};
  const cumulative_keys = make_cumulative_keys(keys);

  const min_year = parseInt(yearly_data[0].year);
  const max_year = parseInt(yearly_data[yearly_data.length - 1].year);

  const formatted_yearly_data = [];
  let i = 0;
  for (let year = min_year; year < max_year + 1; year++, i++) {
    const year_data = yearly_data.find(
      (data) => String(data.year) === String(year),
    );

    cumulative_totals = calculate_cumulative_totals(
      keys,
      year_data,
      cumulative_totals,
      cumulative_keys,
    );

    formatted_yearly_data[i] = {
      ...year_data,
      ...cumulative_totals,
      year: String(year),
    };
  }

  return formatted_yearly_data;
}

function format_simple_year_data(yearly_data) {
  if (yearly_data.length === 0) return yearly_data;

  let cumulative_total = 0;
  const min_year = parseInt(yearly_data[0].year);
  const max_year = parseInt(yearly_data[yearly_data.length - 1].year);

  const formatted_yearly_data = [];
  let i = 0;
  for (let year = min_year; year < max_year + 1; year++, i++) {
    const year_data = yearly_data.find(
      (data) => String(data.year) === String(year),
    );
    const count = year_data ? parseInt(year_data.count) : 0;
    cumulative_total += count;

    formatted_yearly_data[i] = {
      year: String(year),
      count: count,
      cumulative_count: cumulative_total,
    };
  }

  return formatted_yearly_data;
}

function make_cumulative_keys(keys) {
  const cumulative_keys = {};
  keys.forEach((key) => {
    cumulative_keys[key] = `${CUMULATIVE_PREFIX}${key}`;
  });
  return cumulative_keys;
}

function calculate_cumulative_totals(
  keys,
  data,
  cumulative_totals,
  cumulative_keys,
) {
  // add onto previous data to get cumulative totals
  // each key always has a value >= 0
  keys.forEach((key) => {
    const count =
      typeof data !== 'undefined' && data[key] ? parseInt(data[key]) : 0;
    const cumulative_key = cumulative_keys[key];
    if (!cumulative_totals[cumulative_key] && count > 0) {
      cumulative_totals[cumulative_key] = count;
      return;
    } else if (cumulative_totals[cumulative_key] && count > 0) {
      cumulative_totals[cumulative_key] =
        cumulative_totals[cumulative_key] + count;
    }
  });

  return cumulative_totals;
}

/**
 * Formats the metric data by filling in any blank months and calculating
 * cumulative counts for the charts
 *
 * Deals with data coming back from different types of fields (e.g. multi_select, date etc.)
 */
function fetch_url_search_params() {
  const url_search_params = new URLSearchParams(window.location.search);

  let request_params = {};
  for (const param of url_search_params) {
    if (Array.isArray(param) && param.length === 2) {
      request_params[param[0]] = param[1];
    }
  }

  return request_params;
}

function handle_request_defaults() {
  const request_params = fetch_url_search_params();

  // Ensure required parts are present, in order to proceed.
  if (request_params && request_params.record_type && request_params.field) {
    const post_type = request_params.record_type;
    const field_id = request_params.field;

    jQuery('#post_type_select').val(post_type);
    window.dtMetricsProject.state.post_type = post_type;

    jQuery('#post_field_select').val(field_id);
    window.dtMetricsProject.state.field = field_id;

    if (request_params.date) {
      const year = request_params.date;
      window.dtMetricsProject.state.year = year;
      window.dtMetricsProject.state.chart_view =
        year === 'all-time' ? 'year' : 'month';
      jQuery('#date_select').val(year);
    }
    document
      .querySelector('#post_type_select')
      .dispatchEvent(new CustomEvent('change', { detail: request_params }));
  } else {
    // trigger the first get of data on page load
    document
      .querySelector('#post_field_select')
      .dispatchEvent(new Event('change'));
  }
}

function build_field_select_options() {
  const unescaped_options = Object.entries(
    window.dtMetricsProject.field_settings,
  ).reduce((options, [key, setting]) => {
    options[key] = setting.name;
    return options;
  }, {});
  const post_field_options = escapeObject(unescaped_options);
  const sorted_options = Object.entries(post_field_options).sort(
    ([key1, value1], [key2, value2]) => {
      if (value1 < value2) return -1;
      if (value1 === value2) return 0;
      if (value1 > value2) return 1;
    },
  );
  return sorted_options.map(
    ([value, label]) => `
        <option value="${value}"> ${label} </option>
    `,
  );
}

function build_date_select_options(all_time_label) {
  const { earliest_year } = window.dtMetricsProject.state;

  const now = new Date();
  const current_year = now.getUTCFullYear();

  let options = '';
  for (let year = current_year; year > earliest_year - 1; year--) {
    options += `<option value="${year}">${year}</option>`;
  }
  options += `<option value="all-time">${all_time_label}</option>`;
  return options;
}

function create_charts() {
  const { field_type } = window.dtMetricsProject.state;
  const { added_label, total_label } = escapeObject(
    window.dtMetricsProject.translations,
  );
  const data = window.dtMetricsProject.data;

  const keys = get_data_keys(data);
  const total_keys = keys.filter((key) => !key.includes(CUMULATIVE_PREFIX));
  const cumulative_keys = keys.filter((key) => key.includes(CUMULATIVE_PREFIX));
  create_chart('select_tags_chart', cumulative_keys, {
    single: true,
  });
}

function show_chart(id) {
  const chart_section = document.getElementById(id);
  chart_section.style.display = 'block';
}

function create_chart(id, keys, options) {
  show_chart(id);
  const [chart, value_axis] = initialise_chart(id);
  const chart_section = document.getElementById(id);

  const legend_div = chart_section.querySelector('.legend');
  const legend_container = window.am4core.create(
    legend_div,
    window.am4core.Container,
  );
  legend_container.width = window.am4core.percent(100);
  legend_container.height = window.am4core.percent(100);
  chart.legend = new window.am4charts.Legend();
  chart.legend.minHeight = 36;
  chart.legend.scrollable = true;
  chart.legend.parent = legend_container;

  // Create series
  let series = chart.series.push(new window.am4charts.ColumnSeries());
  series.dataFields.valueY = 'value';
  series.dataFields.categoryX = 'label';
  series.name =
    window.dtMetricsProject.field_settings[
      window.dtMetricsProject.state['field']
    ]['name'];
  series.columns.template.tooltipText = '{categoryX}: [bold]{valueY}[/]';
  series.columns.template.fillOpacity = 0.8;

  let columnTemplate = series.columns.template;
  columnTemplate.strokeWidth = 2;
  columnTemplate.strokeOpacity = 1;

  // Capture series event clicks.
  series.columns.template.events.on('hit', function (e) {
    let target = e.target;
    let label_key = target.dataItem.component.dataFields.categoryX;
    let value_key = target.dataItem.component.dataFields.valueY;
    let data = target.dataItem.dataContext;

    display_post_list_modal_for_cumulative_posts(data['metric']);
  });

  // Legend resizing handler function.
  const resize_legend = (e) => {
    const legend_style =
      legend_div.computedStyle || window.getComputedStyle(legend_div);
    const padding_top = parseInt(legend_style.paddingTop);
    const padding_bottom = parseInt(legend_style.paddingBottom);
    const new_height =
      chart.legend.contentHeight + padding_top + padding_bottom;
    legend_div.style.height = `${new_height}px`;
    legend_div.style.paddingBottom = '10px';
  };

  // Chart events.
  chart.events.on('datavalidated', resize_legend);
  chart.events.on('maxsizechanged', resize_legend);

  // Chart legend events.
  chart.legend.events.on('datavalidated', resize_legend);
  chart.legend.events.on('maxsizechanged', resize_legend);
}

function display_post_list_modal_for_cumulative_posts(metric_key) {
  // Determine click display parameters.
  const { post_type, field, field_type, year, earliest_year } =
    window.dtMetricsProject.state;
  const is_all_time = year === 'all-time';
  const limit = 100;

  // Build request payload.
  let payload = {
    post_type: post_type,
    field: field,
    key: metric_key,
    limit: limit,
  };

  // Determine request query date range.
  payload['ts_start'] = window
    .moment()
    .year(earliest_year)
    .month(0)
    .date(1)
    .hour(0)
    .minute(0)
    .second(0)
    .unix();
  payload['ts_end'] = window
    .moment()
    .year(is_all_time ? new Date().getFullYear() : year)
    .month(11)
    .date(31)
    .hour(23)
    .minute(59)
    .second(59)
    .unix();

  // Dispatch request and process response accordingly.
  get_metrics_cumulative_posts(payload)
    .promise()
    .then((response) => {
      display_post_list_modal_records_handler(response, limit);
    })
    .catch((error) => {
      console.log(error);
    });
}

function display_post_list_modal_records_handler(records, limit) {
  if (records && records.data) {
    let selected_posts = [];
    let posts = records.data;

    // Limit to first X elements.
    selected_posts = posts.slice(0, limit);

    // Proceed with displaying post list.
    let sorted_posts = window.lodash.orderBy(selected_posts, ['name'], ['asc']);
    let list_html = `
          <br>
          ${(function (posts_to_filter) {
            let post_list_html = `
          <table>
            <thead>
                <tr>
                    <th></th>
                    <th>${window.lodash.escape(window.dtMetricsProject.translations.modal_table_head_title)}</th>
                </tr>
            </thead>
            <tbody>
          `;
            let counter = 0;
            jQuery.each(posts_to_filter, function (idx, post) {
              let url =
                window.dtMetricsProject.site +
                window.dtMetricsProject.state.post_type +
                '/' +
                post['id'];

              post_list_html += `
                <tr>
                    <td>${++counter}</td>
                    <td><a href="${url}" target="_blank">${post['name'] ? post['name'] : post['id']}</a></td>
                </tr>
                `;
            });

            post_list_html += `
            </tbody>
          </table>
          `;

            return posts_to_filter.length > 0
              ? post_list_html
              : window.dtMetricsProject.translations.modal_no_records;
          })(sorted_posts)}
          <br>
          `;

    // Determine overall total value.
    let total = sorted_posts.length;
    if (records.total) {
      total = parseInt(records.total);
    }

    // Render post html list.
    let title =
      window.dtMetricsProject.translations.modal_title +
      (total > 0 ? ` [ ${total} ]` : '');
    let content = jQuery('#template_metrics_modal_content');
    jQuery('#template_metrics_modal_title')
      .empty()
      .html(window.lodash.escape(title));
    jQuery(content).css('max-height', '300px');
    jQuery(content).css('overflow', 'auto');
    jQuery(content).empty().html(list_html);
    jQuery('#template_metrics_modal').foundation('open');
  }
}

function initialise_chart(id) {
  const { year, chart_view: view, field } = window.dtMetricsProject.state;
  const { all_time } = escapeObject(window.dtMetricsProject.translations);

  window.am4core.options.autoDispose = true;

  const chart_section = document.getElementById(id);
  const timechart_div = chart_section.querySelector('.timechart');

  const chart = window.am4core.create(timechart_div, window.am4charts.XYChart);
  const data = window.dtMetricsProject.data;

  const category_axis = chart.xAxes.push(new window.am4charts.CategoryAxis());
  category_axis.dataFields.category = 'label';
  category_axis.renderer.grid.template.location = 0;
  category_axis.renderer.minGridDistance = 30;
  category_axis.renderer.labels.template.adapter.add(
    'dy',
    function (dy, target) {
      if (target.dataItem && target.dataItem.index & (2 == 2)) {
        return dy + 25;
      }
      return dy;
    },
  );

  const value_axis = chart.yAxes.push(new window.am4charts.ValueAxis());
  value_axis.maxPrecision = 0;

  // Reshape cumulative data structure, to capture most recent entries.
  let cumulative_data = {};
  let cumulative_keys = [];
  data.forEach(function (cumulative_item) {
    const keys = Object.keys(cumulative_item).filter(
      (key) =>
        key !== 'month' && key !== 'year' && key.startsWith(CUMULATIVE_PREFIX),
    );
    if (Object.keys(keys).length !== 0) {
      keys.forEach(function (key) {
        // Capture any new cumulative keys.
        if (!cumulative_keys.includes(key)) {
          cumulative_keys.push(key);
        }

        // Update cumulative key values.
        cumulative_data[key] = cumulative_item[key];
      });
    }
  });

  // Reshape cumulative data into suitable chart data.
  let reshaped_data = [];
  cumulative_keys.forEach(function (cumulative_key) {
    reshaped_data.push({
      metric: cumulative_key.substring(CUMULATIVE_PREFIX.length),
      label: get_default_name_from_cumulative_key(field, cumulative_key),
      value: cumulative_data[cumulative_key],
    });
  });

  chart.data = reshaped_data;

  return [chart, value_axis];
}

function get_data_keys(data) {
  if (data.length === 0) return [];

  let data_with_keys = {};
  // loop over all data and merge them all to make sure we get an object with the keys in
  // (as some months/years might be empty)
  data.forEach((d) => {
    data_with_keys = {
      ...data_with_keys,
      ...d,
    };
  });
  const keys = Object.keys(data_with_keys).filter(
    (key) => key !== 'month' && key !== 'year',
  );
  if (Object.keys(keys).length === 0) {
    return [];
  }
  return keys;
}

function get_default_name_from_cumulative_key(field, cumulative_key) {
  const field_settings = window.dtMetricsProject.field_settings[field];
  const key = cumulative_key.substring(CUMULATIVE_PREFIX.length);

  // Ensure option label exists.
  if (
    field_settings &&
    field_settings['default'] &&
    field_settings['default'][key] &&
    field_settings['default'][key]['label']
  ) {
    return field_settings['default'][key]['label'];
  }

  return key;
}
