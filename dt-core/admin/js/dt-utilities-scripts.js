console.log("lege");


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
})
