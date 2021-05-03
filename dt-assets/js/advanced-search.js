jQuery(document).ready(function ($) {

  let rest_api = window.API
  let template_dir_uri = window.advanced_search_settings.template_dir_uri;
  let fetch_more_text = window.advanced_search_settings.fetch_more_text;

  // Open the advanced search modal
  $(document).on("click", '.advanced-search-nav-button', function () {
    reset_widgets();
    $('#advanced-search-modal').foundation('open');
  })

  // Process search queries
  $(document).on("keypress", '.advanced-search-modal-form-input', function (e) {
    if (e.which === 13) {
      e.preventDefault();
      execute_search_query();
    }
  })
  $(document).on("click", '.advanced-search-modal-form-button', function () {
    execute_search_query();
  })

  $(document).on("click", '.advanced-search-modal-results-table-row-clickable', function (e) {
    let post_type = e.currentTarget.querySelector("#advanced-search-modal-results-table-row-hidden-post-type").getAttribute("value");
    let post_id = e.currentTarget.querySelector("#advanced-search-modal-results-table-row-hidden-post-id").getAttribute("value");
    display_record(post_type, post_id);
  })

  $(document).on("click", '.advanced-search-modal-post-types', function (e) {
    execute_search_query();
  })

  $(document).on("click", '.advanced-search-modal-results-table-row-section-head-load-more', function (e) {
    execute_search_query_by_offset(e, $(this));
  })

  // Mobile view - Toggle searchable post types display
  $(document).on("click", '.advanced-search-modal-results-post-types-view-at-top-collapsible-button', function () {
    let collapsible_button = $('.advanced-search-modal-results-post-types-view-at-top-collapsible-button');
    let collapsible_content = $('.advanced-search-modal-results-post-types-view-at-top-collapsible-content');

    collapsible_content.slideToggle('fast', function () {
      let img = window.lodash.escape(template_dir_uri) + '/dt-assets/images/';
      img += collapsible_content.is(':visible') ? 'chevron_up.svg' : 'chevron_down.svg';

      collapsible_button.find('img').attr('src', img);
    });
  })

  function execute_search_query_by_offset(evt, current_section_head) {
    let query = $('.advanced-search-modal-form-input').val();
    let offset = evt.currentTarget.parentNode.parentNode.querySelector("#advanced-search-modal-results-table-row-section-head-hidden-offset").getAttribute("value");
    let post_type = evt.currentTarget.parentNode.parentNode.querySelector("#advanced-search-modal-results-table-row-section-head-hidden-post-type").getAttribute("value");

    rest_api.advanced_search(encodeURI(query), post_type, offset).then(api_data => {
      /*
       * As by offset search is on a per post_type basis, there should
       * only be a single result element returned.
       */

      if (api_data && (parseInt(api_data['total_hits']) > 0)) {
        let results = api_data['hits'];

        // Update global hits count
        let results_total = $('.advanced-search-modal-results-total');
        let new_global_hits_count = parseInt(results_total.html()) + parseInt(api_data['total_hits']);
        results_total.html(window.lodash.escape(new_global_hits_count));

        // Update section offset value
        evt.currentTarget.parentNode.parentNode.querySelector("#advanced-search-modal-results-table-row-section-head-hidden-offset").setAttribute("value", window.lodash.escape(results[0]['offset']));

        // Insert latest finds...!
        results[0]['posts'].forEach(function (post) {
          current_section_head.closest('tr').after(build_result_table_row(post)).next('tr').slideDown('fast');
        });
      } else {
        // Hide more search option when there are no further hits to be returned.
        evt.currentTarget.style.display = 'none';
      }

    }).catch(error => {
      console.log(error);
    });
  }

  function reset_widgets() {
    $('.advanced-search-modal-form-input').val('');
    $('.advanced-search-modal-results-total').html('');
    $('.advanced-search-modal-results-div').slideUp('fast');
    $('.advanced-search-modal-results').html('').fadeOut('fast');
    $('input[name=advanced-search-modal-post-types-at-side][value=all]').prop('checked', true);

    // Mobile view
    $('.advanced-search-modal-results-post-types-view-at-top-collapsible-content').slideUp('fast');
    $('.advanced-search-modal-results-post-types-view-at-top-collapsible-button').find('img').attr('src', window.lodash.escape(template_dir_uri) + '/dt-assets/images/chevron_down.svg');
    $('input[name=advanced-search-modal-post-types-at-top][value=all]').prop('checked', true);
  }

  function execute_search_query() {
    let query = $('.advanced-search-modal-form-input').val();
    let collapsible_button = $('.advanced-search-modal-results-post-types-view-at-top-collapsible-button'); // Mobile view indicator
    let selected_post_type = $(collapsible_button.is(':visible') ? "input[name=advanced-search-modal-post-types-at-top]:checked" : "input[name=advanced-search-modal-post-types-at-side]:checked").val();

    if (query.trim() === "") {
      return;
    }

    // Dispatch search query and display api response accordingly
    $('.advanced-search-modal-results-div').slideDown('fast', function (data) {
      let spinner = '<span class="loading-spinner active"></span>';
      $('.advanced-search-modal-results').html(spinner).fadeIn('slow', function () {
        rest_api.advanced_search(encodeURI(query), selected_post_type, 0).then(api_data => {
          display_results(api_data, function () {
            $('.advanced-search-modal-results').fadeIn('fast');
          });
        }).catch(error => {
          console.log(error);
        });
      });
    });
  }

  function display_results(api_data, callback) {
    let results = api_data['hits'];
    let total_hits = api_data['total_hits'];
    let results_html = "";

    // Update global hits count
    $('.advanced-search-modal-results-total').html(window.lodash.escape(total_hits));

    // Iterate through results, displaying accordingly
    results_html += '<table class="advanced-search-modal-results-table"><tbody>';
    results.forEach(function (result) {

      results_html += '<tr>';
      results_html += '<td class="advanced-search-modal-results-table-section-head-options"><a class="advanced-search-modal-results-table-row-section-head-load-more button hollow">' + window.lodash.escape(fetch_more_text) + '</a></td>';
      results_html += '<td class="advanced-search-modal-results-table-section-head-post-type">';
      results_html += '<b>' + window.lodash.escape(result['post_type']) + '</b></td>';
      results_html += '<input type="hidden" id="advanced-search-modal-results-table-row-section-head-hidden-offset" value="' + window.lodash.escape(result['offset']) + '">';
      results_html += '<input type="hidden" id="advanced-search-modal-results-table-row-section-head-hidden-post-type" value="' + window.lodash.escape(result['post_type']) + '">';
      results_html += '</td>';
      results_html += '</tr>';

      result['posts'].forEach(function (post) {
        results_html += build_result_table_row(post);
      });
    });
    results_html += '</tbody></table>';

    // Update results table
    $('.advanced-search-modal-results').fadeOut('fast', function () {
      let results_div = $('.advanced-search-modal-results');

      // Reposition scrollbar to avoid blank views following a previous large result set
      results_div.scrollTop();

      // Update html and execute specified callback()
      results_div.html(results_html);
      callback();
    });
  }

  function build_result_table_row(post) {
    // Determine hidden values
    let hidden_post_id = post['ID'];
    let hidden_post_type = post['post_type'];

    // Determine available hit types
    let is_post_hit = (post['post_hit'] && (post['post_hit'] === 'Y'));
    let is_comment_hit = (post['comment_hit'] && (post['comment_hit'] === 'Y'));
    let is_meta_hit = (post['meta_hit'] && (post['meta_hit'] === 'Y'));
    let is_default_hit = (!is_post_hit && !is_comment_hit && !is_meta_hit);

    let results_html = '<tr class="advanced-search-modal-results-table-row-clickable">';

    results_html += '<td class="advanced-search-modal-results-table-col-hits"><b>' + window.lodash.escape(post['post_title']) + '</b><br><span>';

    if (is_comment_hit) {
      results_html += window.lodash.escape((String(post['comment_hit_content']).length > 100) ? String(post['comment_hit_content']).substring(0, 100) + "..." : post['comment_hit_content']);
    } else if (is_meta_hit) {
      results_html += window.lodash.escape(post['meta_hit_value']);
    }
    results_html += '</span>';

    results_html += '<input type="hidden" id="advanced-search-modal-results-table-row-hidden-post-id" value="' + window.lodash.escape(hidden_post_id) + '">';
    results_html += '<input type="hidden" id="advanced-search-modal-results-table-row-hidden-post-type" value="' + window.lodash.escape(hidden_post_type) + '">';

    results_html += '</td>';

    // Determine hit type icon to be displayed
    results_html += '<td class="advanced-search-modal-results-table-col-hits-type">';
    results_html += (is_post_hit || is_default_hit) ? '<img class="dt-icon" src="' + window.lodash.escape(template_dir_uri) + '/dt-assets/images/contact-generation.svg" alt="Record Hit"/>&nbsp;' : '';
    results_html += (is_comment_hit) ? '<img class="dt-icon" src="' + window.lodash.escape(template_dir_uri) + '/dt-assets/images/comment.svg" alt="Comment Hit"/>&nbsp;' : '';
    results_html += (is_meta_hit) ? '<img class="dt-icon" src="' + window.lodash.escape(template_dir_uri) + '/dt-assets/images/socialmedia.svg" alt="Meta Hit"/>&nbsp;' : '';
    results_html += '</td>';

    results_html += '</tr>';

    return results_html;
  }

  function display_record(post_type, post_id) {
    window.location = window.wpApiShare.site_url + '/' + post_type + "/" + post_id;
  }
})


