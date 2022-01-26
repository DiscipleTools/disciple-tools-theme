$(function () {

  highlight_current_menu_item();

  function highlight_current_menu_item() {
    if (window.wpApiShare.url_path && window.wpApiShare.url_path.includes('metrics')) {

      // Determine actual selected metric menu item
      let selected_metric_name = (window.wpApiShare.url_path === 'metrics') ? 'metrics/personal/overview' : window.wpApiShare.url_path;
      let metric = $('#metrics-side-section a[href$="' + selected_metric_name + '"]').last();

      // Apply class highlight
      metric.parent().addClass('side-menu-item-highlight');
      metric.parent().parent().parent().find('a').first().addClass('side-menu-item-highlight');
    }
  }

});
