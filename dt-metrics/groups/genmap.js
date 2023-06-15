jQuery(document).ready(function() {
  if (window.wpApiShare.url_path.startsWith('metrics/groups/genmap')) {
    project_group_genmap()
  }

  function project_group_genmap() {
    "use strict";
    let chart = jQuery('#chart')
    let spinner = ' <span class="loading-spinner active"></span> '

    chart.empty().html(spinner)
    jQuery('#metrics-sidemenu').foundation('down', jQuery('#groups-menu'));

    let translations = dtMetricsProject.data.translations

    chart.empty().html(`
          <span class="section-header">${window.lodash.escape(translations.title_group_genmap)}</span><hr>
          <div class="grid-x grid-padding-x">
              <div class="cell">
                <div id="genmap" style="width: 100%; border: 1px solid lightgrey; "></div>
              </div>
          </div>

           <div id="modal" class="reveal" data-reveal></div>
           <br><br>
       `)

    makeRequest('POST', 'metrics/group/genmap')
      .then(response => {
        console.log(response)
        let container = jQuery('#genmap')
        container.orgchart({
          'data': response,
          'nodeContent': 'title',
          'direction': 'l2r',
        });

        let container_height = jQuery('.orgchart').width() // because it is rotated
        container.height(container_height + 200 + 'px')

        container.on('click', '.node', function () {
          let node = jQuery(this)
          let node_id = node.attr('id')
          open_modal_details(node_id)
        })

        new Foundation.Reveal(jQuery('#modal'))
      })

    /*
    let datasource = {
      'name': 'Lao Lao',
      'title': 'general manager',
      'children': [
        { 'name': 'Bo Miao', 'title': 'department manager',
          'children': [
            { 'name': 'Pang Pang', 'title': 'engineer' },
            { 'name': 'Xiang Xiang', 'title': 'UE engineer' },
            { 'name': 'Yu Jie', 'title': 'department manager' },
            { 'name': 'Yu Li', 'title': 'department manager' },
            { 'name': 'Hong Miao', 'title': 'department manager',
              'children': [
                { 'name': 'Pang Pang', 'title': 'engineer' },
                { 'name': 'Xiang Xiang', 'title': 'UE engineer' },
                { 'name': 'Yu Jie', 'title': 'department manager' },
                { 'name': 'Yu Li', 'title': 'department manager' },
                { 'name': 'Hong Miao', 'title': 'department manager' },
                { 'name': 'Yu Wei', 'title': 'department manager' },
                { 'name': 'Chun Miao', 'title': 'department manager',
                  'children': [
                    { 'name': 'Pang Pang', 'title': 'engineer' },
                    { 'name': 'Xiang Xiang', 'title': 'UE engineer' },
                    { 'name': 'Yu Jie', 'title': 'department manager' },
                    { 'name': 'Yu Li', 'title': 'department manager' },
                    { 'name': 'Hong Miao', 'title': 'department manager' },
                    { 'name': 'Yu Wei', 'title': 'department manager' },
                    { 'name': 'Chun Miao', 'title': 'department manager',
                      'children': [
                        { 'name': 'Pang Pang', 'title': 'engineer' },
                        { 'name': 'Xiang Xiang', 'title': 'UE engineer' },
                        { 'name': 'Yu Jie', 'title': 'department manager' },
                        { 'name': 'Yu Li', 'title': 'department manager' },
                        { 'name': 'Hong Miao', 'title': 'department manager' },
                        { 'name': 'Yu Wei', 'title': 'department manager' },
                        { 'name': 'Chun Miao', 'title': 'department manager' },
                        { 'name': 'Yu Tie', 'title': 'department manager' }
                      ]  },
                    { 'name': 'Yu Tie', 'title': 'department manager' }
                  ]  },
                { 'name': 'Yu Tie', 'title': 'department manager' }
              ]  },
            { 'name': 'Yu Wei', 'title': 'department manager' },
            { 'name': 'Chun Miao', 'title': 'department manager' },
            { 'name': 'Yu Tie', 'title': 'department manager' }
          ]  },
        { 'name': 'Su Miao', 'title': 'department manager',
          'children': [
            { 'name': 'Tie Hua', 'title': 'senior engineer' },
            { 'name': 'Hei Hei', 'title': 'senior engineer',
              'children': [
                { 'name': 'Pang Pang', 'title': 'engineer' },
                { 'name': 'Xiang Xiang', 'title': 'UE engineer',
                  'children': [
                    { 'name': 'Pang Pang', 'title': 'engineer' },
                    { 'name': 'Xiang Xiang', 'title': 'UE engineer',
                      'children': [
                        { 'name': 'Pang Pang', 'title': 'engineer' },
                        { 'name': 'Xiang Xiang', 'title': 'UE engineer',
                          'children': [
                            { 'name': 'Pang Pang', 'title': 'engineer' },
                            { 'name': 'Xiang Xiang', 'title': 'UE engineer' },
                            { 'name': 'Yu Jie', 'title': 'department manager' },
                            { 'name': 'Yu Li', 'title': 'department manager' },
                            { 'name': 'Hong Miao', 'title': 'department manager' },
                            { 'name': 'Yu Wei', 'title': 'department manager' },
                            { 'name': 'Chun Miao', 'title': 'department manager' },
                            { 'name': 'Yu Tie', 'title': 'department manager' }
                          ] }
                      ] }
                  ] }
              ]
            }
          ]
        },
        { 'name': 'Yu Jie', 'title': 'department manager' },
        { 'name': 'Yu Li', 'title': 'department manager' },
        { 'name': 'Hong Miao', 'title': 'department manager',
          'children': [
            { 'name': 'Pang Pang', 'title': 'engineer' },
            { 'name': 'Xiang Xiang', 'title': 'UE engineer' },
            { 'name': 'Yu Jie', 'title': 'department manager' },
            { 'name': 'Yu Li', 'title': 'department manager' },
            { 'name': 'Hong Miao', 'title': 'department manager',
              'children': [
                { 'name': 'Pang Pang', 'title': 'engineer' },
                { 'name': 'Xiang Xiang', 'title': 'UE engineer' },
                { 'name': 'Yu Jie', 'title': 'department manager' },
                { 'name': 'Yu Li', 'title': 'department manager' },
                { 'name': 'Hong Miao', 'title': 'department manager',
                  'children': [
                    { 'name': 'Pang Pang', 'title': 'engineer' },
                    { 'name': 'Xiang Xiang', 'title': 'UE engineer' },
                    { 'name': 'Yu Jie', 'title': 'department manager' },
                    { 'name': 'Yu Li', 'title': 'department manager' },
                    { 'name': 'Hong Miao', 'title': 'department manager' },
                    { 'name': 'Yu Wei', 'title': 'department manager' },
                    { 'name': 'Chun Miao', 'title': 'department manager' },
                    { 'name': 'Yu Tie', 'title': 'department manager' }
                  ]  },
                { 'name': 'Yu Wei', 'title': 'department manager' },
                { 'name': 'Chun Miao', 'title': 'department manager' },
                { 'name': 'Yu Tie', 'title': 'department manager' }
              ]  },
            { 'name': 'Yu Wei', 'title': 'department manager' },
            { 'name': 'Chun Miao', 'title': 'department manager' },
            { 'name': 'Yu Tie', 'title': 'department manager' }
          ]  },
        { 'name': 'Yu Wei', 'title': 'department manager' },
        { 'name': 'Chun Miao', 'title': 'department manager' },
        { 'name': 'Yu Tie', 'title': 'department manager' }
      ]
    }

    var ch = jQuery('#genmap').orgchart({
      'data' : datasource,
      'nodeContent': 'title',
      // 'pan': true,
      // 'zoom': true,
      'direction': 'l2r',
      // 'createNode': function($node, data) {
      //   $node.on('click', function(event) {
      //     if (!$(event.target).is('.edge, .toggleBtn')) {
      //       var $this = $(this);
      //       var $chart = $this.closest('.orgchart');
      //       var newX = window.parseInt(($chart.outerWidth(true)/2) - ($this.offset().left - $chart.offset().left) - ($this.outerWidth(true)/2));
      //       var newY = window.parseInt(($chart.outerHeight(true)/2) - ($this.offset().top - $chart.offset().top) - ($this.outerHeight(true)/2));
      //       $chart.css('transform', 'matrix(1, 0, 0, 1, ' + newX + ', ' + newY + ')');
      //     }
      //   }
      //   );
      // }
    });

    let container_height = jQuery('.orgchart').width() // because it is rotated
    jQuery('#genmap').height(container_height + 200 + 'px')

    ch.$chartContainer.on('touchmove', function(event) {
      event.preventDefault();
    });

    // makeRequest('POST', 'metrics/group/genmap' )
    //   .then(response => {
    //     // console.log(response)
    //     jQuery('#generation_map').empty().html(response)
    //     jQuery('#generation_map li:last-child').addClass('last');
    //     new Foundation.Reveal(jQuery('#modal'))
    //
    //     jQuery('#genmap').empty().orgchart({ 'data': jQuery('#generation_map')})
    //   })

     */

  }

  function open_modal_details(id) {
    let modal = jQuery('#modal')
    let spinner = ' <span class="loading-spinner active"></span> '
    let translations = dtMetricsProject.data.translations

    modal.empty().html(spinner).foundation('open')

    makeRequest('GET', 'groups/' + window.lodash.escape(id), null, 'dt-posts/v2/')
      .then(data => {
        // console.log(data)
        if (data) {
          let list = '<dt>' + window.lodash.escape(translations.members) + '</dt><ul>'
          let assigned_to = ''
          if (typeof data.assigned_to !== 'undefined') {
            assigned_to = data.assigned_to['display']
          }
          jQuery.each(data.members, function (i, v) {
            list += `<li><a href="${window.lodash.escape(window.wpApiShare.site_url)}/contacts/${window.lodash.escape(data.members[i].ID)}">${window.lodash.escape(data.members[i].post_title)}</a></li>`
          })
          list += '</ul>'
          modal.empty().append(`
          <div class="grid-x">
              <div class="cell"><span class="section-header">${window.lodash.escape(data.title)}</span><hr style="max-width:100%;"></div>
              <div class="cell">
                  <dl>
                      <dd><strong>${window.lodash.escape(translations.status) /*Status*/}: </strong>${window.lodash.escape(data.group_status.label)}</dd>
                      <dd><strong>${window.lodash.escape(translations.assigned_to)/*Assigned to*/}: </strong>${window.lodash.escape(assigned_to)}</dd>
                      <dd><strong>${window.lodash.escape(translations.total_members) /*Total Members*/}: </strong>${window.lodash.escape(data.member_count)}</dd>
                      ${list}
                  </dl>
              </div>
              <div class="cell center"><hr><a href="${window.lodash.escape(window.wpApiShare.site_url)}/groups/${window.lodash.escape(id)}">${translations.view_group /*View Group*/}</a></div>
          </div>
          <button class="close-button" data-close aria-label="Close modal" type="button">
              <span aria-hidden="true">&times;</span>
          </button>
        `)
        }
      })
  }

})(jQuery)
