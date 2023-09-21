jQuery(function () {
  if (window.wpApiShare.url_path.startsWith('metrics/records/generation_tree')) {
    project_generation_tree();
  }
});

const getGenerationTree = (data) =>
  window.makeRequest('POST', `metrics/generation_tree`, data)

const escapeObject = window.SHAREDFUNCTIONS.escapeObject

function project_generation_tree() {
  const chartDiv = document.querySelector('#chart');

  const {
    title_date_range_activity,
    post_type_select_label,
    post_field_select_label,
    submit_button_label,
    show_all_button_label
  } = escapeObject(window.dtMetricsProject.translations);

  const postTypeOptions = escapeObject(window.dtMetricsProject.select_options.post_type_select_options);

  jQuery('#metrics-sidemenu').foundation('down', jQuery('#records-menu'));

  // Display initial controls.
  chartDiv.innerHTML = `
    <div class="section-header">${title_date_range_activity}</div>
    <section class="chart-controls">
        <label class="section-subheader" for="post-type-select">${post_type_select_label}</label>
        <select class="select-field" id="post-type-select">
            ${Object.entries(postTypeOptions).map(([value, label]) => `
                <option value="${value}"> ${label} </option>
            `)}
        </select>

        <label class="section-subheader" for="post-field-select">${post_field_select_label}</label>
        <select class="select-field" id="post-field-select">
            ${buildFieldSelectOptions()}
        </select>

        <br>
        <button class="button" id="post-field-submit-button">${submit_button_label}</button>
        <button class="button" id="post-field-show-all-button" style="display: none;">${show_all_button_label}</button>
        <div id="chart-loading-spinner" class="loading-spinner"></div>
    </section>
    <hr>
    <div id="generation_map" class="scrolling-wrapper" style="display: none;"></div>
    <br>
  `;

  // Add post type event listener.
  const fieldSelectElement = document.querySelector('#post-field-select')
  document.querySelector('#post-type-select').addEventListener('change', (e) => {
    window.dtMetricsProject.state.post_type = e.target.value;
    fieldSelectElement.innerHTML = buildFieldSelectOptions();
  });

  // Add submit button event listener.
  document.querySelector('#post-field-submit-button').addEventListener('click', (e) => {
    handle_build_generation_tree_request();
  });

  // Add show all button event listener.
  document.querySelector('#post-field-show-all-button').addEventListener('click', (e) => {
    handle_build_generation_tree_request();
  });
}

function buildFieldSelectOptions() {

  // Detect and filter out suitable connection fields by selected post type.
  window.dtMetricsProject.field_settings = {};
  let post_type = window.dtMetricsProject.state.post_type;

  if (window.dtMetricsProject.all_post_types[post_type] && window.dtMetricsProject.all_post_types[post_type]['fields']) {
    jQuery.each(window.dtMetricsProject.all_post_types[post_type]['fields'], function (field_id, field_settings) {
      if ((field_settings['type'] === 'connection') && (field_settings['post_type'] === post_type)) {
        window.dtMetricsProject.field_settings[field_id] = field_settings;
      }
    });
  }

  // Proceed with select options html generation.
  const unescapedOptions = Object.entries(window.dtMetricsProject.field_settings)
  .reduce((options, [key, setting]) => {
    options[key] = setting.name
    return options
  }, {})
  const postFieldOptions = escapeObject(unescapedOptions)
  const sortedOptions = Object.entries(postFieldOptions).sort(([key1, value1], [key2, value2]) => {
    if (value1 < value2) return -1
    if (value1===value2) return 0
    if (value1 > value2) return 1
  })
  return sortedOptions.map(([value, label]) => `
        <option value="${value}"> ${label} </option>
    `)
}

jQuery(document).on('mouseover', '#generation_map strong', function (e) {
  let controls_span = jQuery(e.currentTarget).find('.gen-node-controls');
  jQuery(controls_span).show();
});

jQuery(document).on('mouseleave', '#generation_map strong', function (e) {
  let controls_span = jQuery(e.currentTarget).find('.gen-node-controls');
  jQuery(controls_span).hide();
});

jQuery(document).on('click', '#generation_map .gen-node-control-add-child', function (e) {
  let control = jQuery(e.currentTarget);
  display_add_child_modal(jQuery(control).data('post_type'), jQuery(control).data('post_id'), jQuery(control).data('post_name'));
});

jQuery(document).on('click', '#generation_map .gen-node-control-focus', function (e) {
  let control = jQuery(e.currentTarget);
  display_focus_modal(jQuery(control).data('post_type'), jQuery(control).data('post_id'), jQuery(control).data('post_name'));
});

jQuery(document).on('click', '#gen_tree_add_child_but', function (e) {
  handle_add_child();
});

jQuery(document).on('click', '#gen_tree_focus_but', function (e) {
  let post_type = jQuery('#gen_tree_focus_post_type').val();
  let post_id = jQuery('#gen_tree_focus_post_id').val();
  let field_id = jQuery('#post-field-select').val();
  handle_focus(post_type, post_id, field_id);
});

function display_add_child_modal(post_type, post_id, post_name) {
  let list_html = `
    <input id="gen_tree_add_child_post_type" type="hidden" value="${post_type}" />
    <input id="gen_tree_add_child_post_id" type="hidden" value="${post_id}" />
    <label>
      ${window.lodash.escape(window.dtMetricsProject.translations.modal.add_child_name_title)}
      <input id="gen_tree_add_child_name" type="text" />
    </label>`;

  let buttons_html = `<button id="gen_tree_add_child_but" class="button" type="button">${window.lodash.escape(window.dtMetricsProject.translations.modal.add_child_but)}</button>`;

  let modal = jQuery('#template_metrics_modal');
  let modal_buttons = jQuery('#template_metrics_modal_buttons');
  let title = window.dtMetricsProject.translations.modal.add_child_title + ` [ ${post_name} ]`;
  let content = jQuery('#template_metrics_modal_content');

  jQuery(modal_buttons).empty().html(buttons_html);

  jQuery('#template_metrics_modal_title').empty().html(window.lodash.escape(title));
  jQuery(content).css('max-height', '300px');
  jQuery(content).css('overflow', 'auto');
  jQuery(content).empty().html(list_html);
  jQuery(modal).foundation('open');
}

function handle_add_child() {
  let post_type = jQuery('#gen_tree_add_child_post_type').val();
  let parent_id = jQuery('#gen_tree_add_child_post_id').val();
  let child_title = jQuery('#gen_tree_add_child_name').val();
  let field_id = jQuery('#post-field-select').val();

  if (post_type && parent_id && child_title && field_id) {
    window.API.create_post(post_type, {
      'title': child_title,
        'additional_meta': {
          'created_from': parent_id,
          'add_connection': field_id
        }
    }).then(new_post => {

      // Close modal and refresh generation tree, accordingly, based on focussed state.
      jQuery('#template_metrics_modal').foundation('close');

      let root_node = jQuery('#generation_map .li-gen-0-id');
      if ((root_node === undefined) || (jQuery(root_node).length > 1)) {
        jQuery('#post-field-submit-button').trigger('click');
      } else {
        handle_focus(post_type, jQuery(root_node).val(), field_id);
      }

    }).catch(function (error) {
      console.error(error);
    });

  }
}

function display_focus_modal(post_type, post_id, post_name) {
  let list_html = `
    <input id="gen_tree_focus_post_type" type="hidden" value="${post_type}" />
    <input id="gen_tree_focus_post_id" type="hidden" value="${post_id}" />
    ${window.lodash.escape(window.dtMetricsProject.translations.modal.focus_are_you_sure_question)}`;

  let buttons_html = `<button id="gen_tree_focus_but" class="button" type="button">${window.lodash.escape(window.dtMetricsProject.translations.modal.focus_yes)}</button>`;

  let modal = jQuery('#template_metrics_modal');
  let modal_buttons = jQuery('#template_metrics_modal_buttons');
  let title = window.dtMetricsProject.translations.modal.focus_title + ` [ ${post_name} ]`;
  let content = jQuery('#template_metrics_modal_content');

  jQuery(modal_buttons).empty().html(buttons_html);

  jQuery('#template_metrics_modal_title').empty().html(window.lodash.escape(title));
  jQuery(content).css('max-height', '300px');
  jQuery(content).css('overflow', 'auto');
  jQuery(content).empty().html(list_html);
  jQuery(modal).foundation('open');
}

function handle_focus(post_type, post_id, field_id) {
  if (post_type && post_id && field_id) {
    getGenerationTree({
      'post_type': post_type,
      'field': field_id,
      'focus_id': post_id
    })
    .promise()
    .then((response) => {
      let generation_map = jQuery('#generation_map');

      jQuery('#post-field-show-all-button').show();

      // Refresh generation tree results.
      generation_map.fadeOut('fast', function () {
        generation_map.html(response);

        // Ensure end nodes are capped.
        let last_node = generation_map.find('li:last-child.li-gen-0');
        if (last_node) {
          last_node.addClass('last');
          last_node.find('li').addClass('last');
        }

        // Close modal and refresh generation tree.
        jQuery('#template_metrics_modal').foundation('close');
        generation_map.fadeIn('fast');
      });

    })
    .catch((error) => {
      console.log(error);
    });
  }
}

function handle_build_generation_tree_request() {
  let post_type = jQuery('#post-type-select').val();
  let field_id = jQuery('#post-field-select').val();
  let loadingSpinner = document.querySelector('#chart-loading-spinner');
  loadingSpinner.classList.add('active');

  jQuery('#post-field-show-all-button').hide();

  // Request new generational tree.
  getGenerationTree({
    'post_type': post_type,
    'field': field_id
  })
  .promise()
  .then((response) => {
    let generation_map = jQuery('#generation_map');

    // Refresh generation tree results.
    generation_map.fadeOut('fast', function () {
      generation_map.html(response);

      // Ensure end nodes are capped.
      let last_node = generation_map.find('li:last-child.li-gen-0');
      if (last_node) {
        last_node.addClass('last');
        last_node.find('li').addClass('last');
      }

      // Display result findings.
      generation_map.fadeIn('fast');
      loadingSpinner.classList.remove('active');
    });
  })
  .catch((error) => {
    console.log(error);
  });
}
