"use strict";
jQuery(document).ready(function() {
  if ( window.wpApiShare.url_path.startsWith( 'metrics/personal/group-tree' )) {
    group_tree()
  }

  function group_tree() {
    let chart = jQuery('#chart')
    let spinner = ' <span class="loading-spinner active"></span> '

    chart.empty().html(spinner)
    jQuery('#metrics-sidemenu').foundation('down', jQuery('#personal-menu'));

    let translations = dtMetricsProject.data.translations

    chart.empty().html(`
          <span class="section-header">${window.lodash.escape(translations.title_group_tree)}</span><hr>
           <div class="grid-x grid-padding-x">
           <div class="cell">
               <span>
                  <button class="button hollow toggle-singles" id="highlight-active" onclick="highlight_active();">${window.lodash.escape(translations.highlight_active)/*Highlight Active*/}</button>
               </span>
              <span>
                  <button class="button hollow toggle-singles" id="highlight-churches" onclick="highlight_churches();">${window.lodash.escape(translations.highlight_churches)/*Highlight Churches*/}</button>
              </span>
          </div>
              <div class="cell">
                  <div class="scrolling-wrapper" id="generation_map"><img src="${dtMetricsProject.theme_uri}/dt-assets/images/ajax-loader.gif" width="20px" /></div>
              </div>
          </div>
           <div id="modal" class="reveal" data-reveal></div>
           <br><br>
       `)

    makeRequest('POST', 'metrics/my/group_tree' )
      .then(response => {
        // console.log(response)
        jQuery('#generation_map').empty().html(response)
        jQuery('#generation_map li:last-child').addClass('last');
        new Foundation.Reveal(jQuery('#modal'))
      })
  }
})

function open_modal_details( id ) {
  let modal = jQuery('#modal')
  let spinner = ' <span class="loading-spinner active"></span> '
  let translations = dtMetricsProject.data.translations

  modal.empty().html(spinner).foundation('open')

  makeRequest('GET', 'groups/'+id, null, 'dt-posts/v2/' )
    .then(data => {
      // console.log(data)
      if( data ) {
        let list = '<dt>'+window.lodash.escape( translations.members )+'</dt><ul>'
        jQuery.each(data.members, function(i, v)  { list += `<li><a href="${window.lodash.escape(window.wpApiShare.site_url)}/contacts/${window.lodash.escape( data.members[i].ID )}">${window.lodash.escape( data.members[i].post_title )}</a></li>` } )
        list += '</ul>'
        let content = `
                <div class="grid-x">
                    <div class="cell"><span class="section-header">${window.lodash.escape( data.title )}</span><hr style="max-width:100%;"></div>
                    <div class="cell">
                        <dl>
                            <dd><strong>${window.lodash.escape( translations.status ) /*Status*/}: </strong>${window.lodash.escape( data.group_status.label )}</dd>
                      <dd><strong>${window.lodash.escape( translations.assigned_to )/*Assigned to*/}: </strong>${window.lodash.escape( data.assigned_to['display'] )}</dd>
                      <dd><strong>${window.lodash.escape( translations.total_members ) /*Total Members*/}: </strong>${window.lodash.escape( data.member_count )}</dd>
                            ${list}
                        </dl>
                    </div>
                    <div class="cell center"><hr><a href="${window.lodash.escape(window.wpApiShare.site_url)}/groups/${window.lodash.escape( id )}">${window.lodash.escape(translations.view_group)}</a></div>
                </div>
                <button class="close-button" data-close aria-label="Close modal" type="button">
                    <span aria-hidden="true">&times;</span>
                  </button>
                `
        modal.empty().html(content)
      }
    })
}

function toggle_multiplying_only () {
  let list = jQuery('#generation_map .li-gen-1:not(:has(li.li-gen-2))')
  let button = jQuery('#multiplying-only')
  if( button.hasClass('hollow') ) {
    list.hide()
    button.removeClass('hollow')
  } else {
    button.addClass('hollow')
    list.show()
  }
}

function highlight_active() {
  let list = jQuery('.inactive')
  let button = jQuery('#highlight-active')
  if( button.hasClass('hollow') ) {
    list.addClass('inactive-gray')
    button.removeClass('hollow')
  } else {
    button.addClass('hollow')
    list.removeClass('inactive-gray')
  }
}

function highlight_churches() {
  let list = jQuery('#generation_map span:not(.church)')
  let button = jQuery('#highlight-churches')
  if( button.hasClass('hollow') ) {
    list.addClass('not-church-gray')
    button.removeClass('hollow')
  } else {
    button.addClass('hollow')
    list.removeClass('not-church-gray')
  }
}
