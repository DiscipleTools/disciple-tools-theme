jQuery(document).ready(function ($) {
  function make_admin_request(type, part, data) {
    const options = {
      type: type,
      contentType: "application/json; charset=utf-8",
      dataType: "json",
      url: `${window.dt_admin_scripts.rest_root}dt-admin/scripts/${part}`,
      beforeSend: (xhr) => {
        xhr.setRequestHeader("X-WP-Nonce", window.dt_admin_scripts.nonce);
      },
    };
    if (data && !window.lodash.isEmpty(data)) {
      options.data = type === "GET" ? data : JSON.stringify(data);
    }
    return jQuery.ajax(options);
  }


  $('.reset_count_button').on('click', function (){
    let post_type = $(this).data('post-type')
    let field_key = $(this).data('key')
    $(`#${post_type}_${field_key} .progress .loading-spinner`).addClass( "active" )
    make_admin_request( "POST", "reset_count_field", { post_type, field_key }).then(resp=>{
      let interval = setInterval( ()=>{
        make_admin_request( "GET", 'reset_count_field_progress', { post_type, field_key } ).then(status=>{
          $(`#${post_type}_${field_key} .progress .current`).text(resp.count - status.count)
          if ( status.count === 0 ){
            show_done()
          }
        })
      }, 5000)
      let check_status = function (){
        make_admin_request( "GET", 'reset_count_field_progress', { post_type, field_key, process:true } ).then(status=>{
          $(`#${post_type}_${field_key} .progress .current`).text(resp.count - status.count)
          if ( status.count === 0 ){
            show_done()
          } else {
            check_status()
          }
        }).catch(err=>{
          if ( err?.statusText === "timeout" ){
            check_status();
          }
        })
      }
      check_status();
      let show_done = ()=>{
        $(`#${post_type}_${field_key} .progress .current`).text("done")
        $(`#${post_type}_${field_key} .progress .total`).text("")
        clearInterval( interval )
        $(`#${post_type}_${field_key} .progress .loading-spinner`).removeClass( "active" )
      }
      $(`#${post_type}_${field_key} .progress .current`).text( 0 )
      $(`#${post_type}_${field_key} .progress .total`).text( '/' + resp.count)
    })
  })

  /**
   * FILE UPLOADS
   */
  $('.file-upload-display-uploader').on('click', function (e) {
    e.preventDefault();

    // Fetch handle to key workflow elements
    let parent_form = $("form[name='" + $(e.currentTarget).data('form') + "']");
    let icon_input = $("input[name='" + $(e.currentTarget).data('icon-input') + "']");

    // Only proceed if we have valid handles
    if (parent_form && icon_input) {

      // Build media uploader modal
      let mediaFrame = wp.media({

        // Accepts [ 'select', 'post', 'image', 'audio', 'video' ]
        // Determines what kind of library should be rendered.
        frame: 'select',

        // Modal title.
        title: window.lodash.escape(window.dt_admin_scripts.upload.title),

        // Enable/disable multiple select
        multiple: false,

        // Library wordpress query arguments.
        library: {
          order: 'DESC',

          // [ 'name', 'author', 'date', 'title', 'modified', 'uploadedTo', 'id', 'post__in', 'menuOrder' ]
          orderby: 'date',

          // mime type. e.g. 'image', 'image/jpeg'
          type: ['image'],

          // Searches the attachment title.
          search: null,

          // Includes media only uploaded to the specified post (ID)
          uploadedTo: null // wp.media.view.settings.post.id (for current post ID)
        },

        button: {
          text: window.lodash.escape(window.dt_admin_scripts.upload.button_txt)
        }

      });

      // Handle selected files
      mediaFrame.on('select', function () {

        // Fetch and convert selected into json object
        let selected = mediaFrame.state().get('selection').first().toJSON();

        // Update form icon link
        icon_input.val(selected.url);

        // Auto-submit so as to refresh changes
        parent_form.submit();

      });

      // Open the media uploader.
      mediaFrame.open();
    }
  });
  /**
   * FILE UPLOADS
   */

  /**
   * COLOR PICKER
   */
  $('.color-display-picker').wpColorPicker();

   /**
   * Flyout menu
   */
  const details = [...document.querySelectorAll('details.flyout')];
  document.addEventListener('click', function(e) {
    if (!details.some(f => f.contains(e.target))) {
      details.forEach(f => f.removeAttribute('open'));
    } else {
      details.forEach(f => !f.contains(e.target) ? f.removeAttribute('open') : '');
    }
  })


  /**
   * Roles manager source filter
   */
  const filter = document.querySelector('#role-manager #source-filter')
  if (filter) {
    const capabilities = document.querySelectorAll('#role-manager .capability')
    const showCapsForSource = () => {
      capabilities.forEach((capability) => {
        if (capability.dataset.source === filter.value) {
          capability.classList.remove('hide')
        } else {
          capability.classList.add('hide')
        }
      })
    }
    filter.addEventListener('input', showCapsForSource)
    showCapsForSource()
  }
})
