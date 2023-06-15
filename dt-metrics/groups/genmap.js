jQuery(document).ready(function($) {
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

    console.log( dtMetricsProject.data.genmap )
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

})
