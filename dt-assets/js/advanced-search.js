jQuery(document).ready(function ($) {

  let rest_api = window.API
  let template_dir_uri = window.advanced_search_settings.template_dir_uri;

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

  function reset_widgets() {
    $('.advanced-search-modal-form-input').val('');
    $('.advanced-search-modal-results-div').slideUp('fast');
    $('.advanced-search-modal-results').slideUp('fast').html('');
  }

  function execute_search_query() {
    let query = $('.advanced-search-modal-form-input').val();

    if (query.trim() === "") {
      return;
    }

    $('.advanced-search-modal-results').slideUp('fast', function (data) {
      $('.advanced-search-modal-results-div').slideDown('fast', function (data) {

        rest_api.advanced_search(encodeURI(query)).then(api_data => {
          console.log(api_data);

          display_results(api_data, function () {
            $('.advanced-search-modal-results').slideDown('slow');
          })

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

    // Update hits count
    $('.advanced-search-modal-results-total').html(total_hits);

    // Iterate through results, displaying accordingly
    results_html += '<table style="border: none;"><tbody style="border: none;">';
    results.forEach(function (result) {
      result['posts'].forEach(function (post) {
        results_html += '<tr>';
        results_html += '<td style="min-width: 250px;">' + post['post_title'] + '</td>';
        results_html += '<td><img class="dt-icon" src="' + template_dir_uri + '/dt-assets/images/visibility.svg" alt="View Record"/></td>';
        results_html += '</tr>';
      });
    });
    results_html += '</tbody></table>';

    // Update results table
    $('.advanced-search-modal-results').html(results_html);

    // Callback
    callback();
  }
})


