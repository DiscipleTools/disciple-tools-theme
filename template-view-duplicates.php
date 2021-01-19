<?php
/*
Template Name: View Duplicates
*/
dt_please_log_in();

if ( ! current_user_can( 'dt_all_access_contacts' ) || !dt_is_module_enabled( "access_module" ) ) {
    wp_safe_redirect( '/settings' );
    exit();
}
get_header();

?>

    <div id="content" class="template-view-duplicates duplicates-page">

        <div id="inner-content" class="grid-x grid-margin-x">

            <main id="main" class="large-12 medium-12 cell" role="main">
                <div class="bordered-box">
                    <h1><?php esc_html_e( 'Duplicate Access Contacts', 'disciple_tools' ) ?>
                        <span id="duplicates-spinner" class="loading-spinner"></span>
                        <button class="help-button float-right" data-section="duplicates-template-help-text">
                            <img class="help-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>"/>
                        </button>
                    </h1>
                    <p>Scanning for duplicates. Starting with the most recently modified contacts. Limiting results to those that have less than 10 exact matches.</p>
                    <p>Scanned <span id="scanned_number">0</span> access contacts. <span class="loading-spinner active"></span> Found <span id="found_text">0</span> duplicates.</p>
                </div>
                <div id="duplicates-content" class="grid-y grid-margin-y" style="margin-top:50px">

                </div>
            </main> <!-- end #main -->
        </div> <!-- end #inner-content -->

        <script type="text/javascript">
          $( document ).on( "click", '.dismiss-all', function (){
            $(this).addClass('loading')
            let id = $(this).data('id')
            makeRequestOnPosts('POST', `contacts/${id}/dismiss-duplicates`, {'id':'all'}).then(()=> {
              $(`#contact_${id}`).remove()
              $(this).removeClass('loading')
            })
          })

          let get_duplicates = (limit=0)=>{
              window.makeRequest("GET", "contacts/all-duplicates", {limit:limit}, "dt-posts/v2/" ).then(response=>{
                  let html = ``
                  response.posts_with_matches.forEach(post=>{
                      let inner_html = ``
                      _.forOwn( post.dups, (dup_values, dup_key)=>{
                        inner_html += `<div style="display: flex"><div style="flex-basis: 100px"><strong>${_.escape(dup_key)}</strong></div><div>`;
                        dup_values.forEach(dup=>{
                            inner_html += `<a target="_blank" href="contacts/${_.escape(dup.ID)}" style="margin: 0 10px 0 5px">
                              ${_.escape(dup.post_title)}: ${dup.field === "post_title" ? "" : _.escape(dup.value)} (#${_.escape(dup.ID)})
                            </a>`;
                        })
                          inner_html += `</div></div>`
                      })
                      let channels = post.info.map(info=>info.value?info.value:null).join( ", ")
                      html += `
                          <div class="bordered-box cell" id="contact_${_.escape(post.ID)}">
                            <a style="color: #3f729b; font-size: 1.5rem;" target="_blank" href="contacts/${_.escape(post.ID)}">
                              ${_.escape(post.post_title)} (#${_.escape(post.ID)})
                            </a>
                            <div class="label" style="display:inline-block; background: ${_.escape(post.overall_status.color)}">${_.escape(post.overall_status.label)}</div>
                            <div style="display:inline-block;">${_.escape(channels)}</div>
                            <h4 style="margin-top:20px">Matches Found:</h4>
                            <div>${inner_html}</div>

                            <button class="button hollow dismiss-all loader" data-id="${_.escape(post.ID)}"  style="margin-top:20px">Dismiss all matches for ${_.escape(post.post_title)}</button>
                          </div>
                      `
                  })
                  $('#duplicates-content').append(html)
                  $('#scanned_number').html(_.escape(response.scanned))
                  let found = $('#duplicates-content .bordered-box').length
                  $('#found_text').html(_.escape(found));
                  if ( found < 100 ){
                    get_duplicates(response.scanned)
                  } else {
                      $('.loading-spinner').removeClass("active")
                  }
              })

          }
          get_duplicates(0)



        </script>

    </div> <!-- end #content -->

<?php get_footer(); ?>
