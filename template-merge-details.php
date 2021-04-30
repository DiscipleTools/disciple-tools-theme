<?php
/*
 *  Name: Merge Details
*/

dt_please_log_in();

if ( ! current_user_can( 'access_contacts' ) ) {
    wp_die( esc_html( "You do not have permission to access contacts" ), "Permission denied", 403 );
}

if ( !isset( $_GET['currentid'], $_GET['dupeid'] ) ) {
    header( "Location: /contacts" );
}
$dt_current_id = sanitize_text_field( wp_unslash( $_GET['currentid'] ) );
$dt_dupe_id = sanitize_text_field( wp_unslash( $_GET['dupeid'] ) );

$dt_contact = DT_Posts::get_post( "contacts", $dt_current_id, true );
$dt_duplicate_contact = DT_Posts::get_post( "contacts", $dt_dupe_id, true );
if ( is_wp_error( $dt_contact ) || is_wp_error( $dt_duplicate_contact ) ) {
    get_template_part( "403", null, is_wp_error( $dt_contact ) ? $dt_contact : $dt_duplicate_contact );
    die();
}
get_header();

$dt_channel_list = DT_Posts::get_post_settings( "contacts" )["channels"];
$dt_current_user = wp_get_current_user();
$dt_contact_fields = DT_Posts::get_post_field_settings( "contacts" );


$dt_fields = array(
    'contact_phone' => isset( $dt_channel_list['phone']["label"] ) ? $dt_channel_list['phone']["label"] : "Phone",
    'contact_email' => isset( $dt_channel_list['email']["label"] ) ? $dt_channel_list['email']["label"] : "Email",
    'contact_address' => isset( $dt_channel_list['address']["label"] ) ? $dt_channel_list['address']["label"] : "Address",
    'contact_facebook' => isset( $dt_channel_list['facebook']["label"] ) ? $dt_channel_list['facebook']["label"] : "Facebook",
);

$c_fields = array();
$d_fields = array();

$dt_data = array(
    'contact_phone' => array(),
    'contact_email' => array(),
    'contact_address' => array(),
    'contact_facebook' => array()
);

foreach (array_keys( $dt_fields ) as $key) {
    foreach ($dt_contact[$key] ?? [] as $vals) {
        if ( !isset( $c_fields[$key] )) {
            $c_fields[$key] = array();
        }
        array_push( $c_fields[$key], $vals['value'] );
    }
    foreach ($dt_duplicate_contact[$key] ?? [] as $vals) {
        if ( !isset( $d_fields[$key] )) {
            $d_fields[$key] = array();
        }
        array_push( $d_fields[$key], $vals['value'] );
    }
}

foreach (array_keys( $dt_fields ) as $field) {
    $max = max( array( count( $c_fields[$field] ?? [] ), count( $d_fields[$field] ?? [] ) ) );
    for ($i = 0; $i < $max; $i++) {
        $hide = false;
        $o_value = $c_fields[$field][$i] ?? null;
        $d_value = $d_fields[$field][$i] ?? null;
        if (in_array( $o_value, $d_fields[$field] ?? [] )) { $hide = true; }
        array_push($dt_data[$field], array(
            'original' => array(
                'hide' => $hide,
                'value' => $o_value
            ),
            'duplicate' => array(
                'hide' => $hide,
                'value' => $d_value
            )
        ));
    }
}


$dt_contact_name =$dt_contact['title'] ?? null;
$dt_contact_address =$dt_contact['contact_address'][0]['value'] ?? null;
$dt_contact_phone =$dt_contact['contact_phone'][0]['value'] ?? null;
$dt_contact_email =$dt_contact['contact_email'][0]['value'] ?? null;
$dt_contact_facebook =$dt_contact['contact_facebook'][0]['value'] ?? null;

$dt_duplicate_contact_name =$dt_duplicate_contact['title'] ?? null;
$dt_duplicate_contact_address =$dt_duplicate_contact['contact_address'][0]['value'] ?? null;
$dt_duplicate_contact_phone =$dt_duplicate_contact['contact_phone'][0]['value'] ?? null;
$dt_duplicate_contact_email =$dt_duplicate_contact['contact_email'][0]['value'] ?? null;
$dt_duplicate_contact_facebook =$dt_duplicate_contact['contact_facebook'][0]['value'] ?? null;

$dt_used_values = array(
  'contact_phone' => array(),
  'contact_address' => array(),
  'contact_email' => array()
);

$dt_edit_row = "<span class='row-edit'><a onclick='editRow(this, edit);' title='".esc_html__( 'Edit', 'disciple_tools' )."' class='fi-pencil'></a><a class='fi-x hide cancel' title='".esc_html__( 'Cancel', 'disciple_tools' )."'></a><a class='fi-check hide save' title='".esc_html__( 'Save', 'disciple_tools' )."'></a></span>";
?>

<div id="content"  class="template-merge-details">

    <div id="inner-content" class="grid-x grid-margin-x">

        <main id="main" class="large-12 medium-12 cell" role="main">
          <div class="bordered-box">
            <h2 class="center"><?php esc_html_e( "Merge Duplicate Contacts", 'disciple_tools' ) ?></h2>
            <p class="center"><?php esc_html_e( "When you merge, the master record is updated with the values you choose, and relationships to other items are shifted to the master record", 'disciple_tools' ) ?></p>
            <div class="merge-wrap">
                <div class="label-wrap">
                  <div class="merge-column">
                    &nbsp;
                  </div>
                  <div class="merge-column">
                    <a class='contact_name' href="<?php echo esc_html( get_site_url() . "/contacts/" . $dt_current_id ) ?>"><?php echo esc_html( $dt_contact_name ) ?></a>
                    <span class='row-edit'>
                        <a onclick='editRow(this, edit);' title='<?php esc_html_e( "Edit", 'disciple_tools' ) ?>' class='fi-pencil'></a><a class='fi-x hide cancel' title='<?php esc_html_e( "Cancel", 'disciple_tools' ) ?>'></a>
                        <a class='fi-check hide save' title='<?php esc_html_e( "Save", 'disciple_tools' ) ?>'></a>
                    </span>
                    <br>
                    <span><?php echo esc_html( '#' . $dt_contact["ID"] ) ?></span><br>
                    <span><?php esc_html_e( "Status:", 'disciple_tools' ) ?> <?php echo esc_html( $dt_contact["overall_status"]["label"] ?? "" ) ?></span><br>
                    <span><?php echo esc_html( sprintf( _x( 'Created on %s', 'Created on the 21st of August', 'disciple_tools' ), dt_format_date( $dt_contact["post_date"]["timestamp"] ) ) ); ?></span><br>
                    <a onclick='selectAll(this);'><?php esc_html_e( "Select All", 'disciple_tools' ) ?></a>
                  </div>
                  <div class="merge-column">
                    <a class='contact_name' href="<?php echo esc_html( get_site_url() . "/contacts/" . $dt_dupe_id ) ?>"><?php echo esc_html( $dt_duplicate_contact_name ) ?></a>
                    <span class='row-edit'><a onclick='editRow(this, edit);' title='<?php esc_html_e( "Edit", 'disciple_tools' ) ?>' class='fi-pencil'></a><a class='fi-x hide cancel' title='<?php esc_html_e( "Cancel", 'disciple_tools' ) ?>'></a><a class='fi-check hide save' title='<?php esc_html_e( "Save", 'disciple_tools' ) ?>'></a></span>
                    <br>
                    <span><?php echo esc_html( '#' . $dt_duplicate_contact["ID"] ) ?></span><br>
                    <span><?php esc_html_e( "Status:", 'disciple_tools' ) ?> <?php echo esc_html( $dt_duplicate_contact["overall_status"]["label"] ?? "" ) ?></span><br>
                    <span><?php echo esc_html( sprintf( _x( 'Created on %s', 'Created on the 21st of August', 'disciple_tools' ), dt_format_date( $dt_duplicate_contact["post_date"]["timestamp"] ) ) ); ?></span><br>
                    <a onclick='selectAll(this);'><?php esc_html_e( "Select All", 'disciple_tools' ) ?></a>
                  </div>
                </div>

                <form id="merge-form">

                <div class="line-wrap">
                  <div class="merge-column">
                    <span class="bold"><?php esc_html_e( "Master Record", 'disciple_tools' ) ?></span>
                  </div>
                  <div class="merge-column">
                    <input type="hidden" name="contact1" value="<?php echo esc_html( $dt_current_id ); ?>">
                    <input type="hidden" name="contact2" value="<?php echo esc_html( $dt_duplicate_contact["ID"] ); ?>">
                    <input type="radio" required name="master-record" value="<?php echo esc_html( $dt_current_id ); ?>"> <?php esc_html_e( "Use as master", 'disciple_tools' ) ?>
                  </div>
                  <div class="merge-column blueBackground">
                    <input type="radio" checked required name="master-record" value="<?php echo esc_html( $dt_duplicate_contact["ID"] ); ?>"> <?php esc_html_e( "Use as master", 'disciple_tools' ) ?>
                  </div>
                </div>

                <?php
                foreach ($dt_fields as $dt_key => $dt_field) {
                    foreach ( $dt_data[$dt_key] as $dt_idx => $dt_type ) : ?>
                        <div class='line-wrap'>
                            <div class='merge-column'><span class='bold'><?php echo esc_html( $dt_field ); ?></span></div>
                            <?php foreach ( $dt_type as $dt_vals ) {
                                $dt_value = $dt_vals['value']; ?>
                                <div class='merge-column'>
                                    <?php if ( $dt_value ): ?>
                                    <input type='checkbox' name='<?php echo esc_html( strtolower( $dt_field ) ); ?>[]' value='<?php echo esc_html( $dt_value ); ?>'><?php echo esc_html( $dt_value ); ?>
                                    <span class='row-edit'><a onclick='editRow(this, edit);' title='<?php esc_html_e( 'Edit', 'disciple_tools' ); ?>' class='fi-pencil'></a>
                                        <a class='fi-x hide cancel' title='<?php esc_html_e( 'Cancel', 'disciple_tools' ); ?>'></a>
                                        <a class='fi-check hide save' title='<?php esc_html_e( 'Save', 'disciple_tools' ); ?>'></a>
                                    </span>

                                    <?php else : ?>
                                        <div class='empty'></div>
                                    <?php endif; ?>
                                </div>
                            <?php } ?>
                        </div>
                    <?php endforeach;
                } ?>

                <span id="merge_errors" style="margin-top: 30px; color: red; text-align: right;"></span>
                <button class='button loader' id="submit-merge" name='merge-submit' type='button' onclick='merge()' value='Merge'><?php esc_html_e( 'Merge', 'disciple_tools' ); ?></button>
                </form>
            </div>
          </div>
        </main> <!-- end #main -->
        <style>
        .blueBackground{
          background-color:#30c2ff;
          border-radius:7px;
        }
        .merge-column{
          width:33%;
          float:left;
          min-height: 45px;
        }
        .bold{
          font-weight:bold;
        }
        .line-wrap{
          overflow:hidden;
          border-bottom:1px solid grey;
          clear:both;
        }
        .line-wrap:last-of-type{
          margin-bottom:20px;
        }
        .merge-column input{
          margin-top:15px;
        }
        .merge-column .bold{
          position:relative;
          top:10px;
        }
        #merge-form{
          display:inline-block;
          width:100%;
        }
        #merge-form button {
          margin-top:20px;
          float:right;
          display:inline-block;
        }
        .shortText {
            max-width: 250px;
        }
        .row-edit a {
            margin-left: 10px;
        }
        .empty {
            display: inline-block;
            visibility: hidden;
        }
        </style>
        <script>
          bindInputs($("#merge-form input[type='checkbox']"));
          function selectAll(o) {
              let index = $(o).closest('div.merge-column').index();
              $("#merge-form").find('input[type=checkbox]').each(function(key, el) {
                  let div = $(el).closest('div.merge-column').not('.hide');
                  if(div.index() === index) {
                      $(el).click();
                  }
              });
          }

          $(".merge-column").click(function(e) {
                let input = $(this).find('input[type=radio], input[type=checkbox]');
                if($(e.target).is(input) || $(e.target).is('.fi-pencil')) {
                    return;
                }
                input.click();
          });



        function editRow(o, callback) {
          let line = $(o).closest('.line-wrap');
          let cell = $(o).closest('.merge-column');
          let copy = cell.clone();
          let input = cell.find('input');
          let checked = input.is(':checked');
          let value = cell.find('input').val();
          let field = line.find('.merge-column').eq(0).find('.bold').text();
          let editRow = cell.find('.row-edit');
          if(cell.find('.contact_name').text()) {
              value = cell.find('.contact_name').text();
              field = 'Name';
          }
          $input = $("<input class='shortText' type='text' value='"+value+"'/>").css({
            margin: 0,
            display: 'inline-block'
          });
          cell.html($input);
          cell.append(editRow);
          toggleRowEdit(o);
          return callback(cell, copy, checked, field);
        }

        function edit(o, copy, checked, field) {
          $('html').click(function(e) {
            if($(e.target).is('.cancel') || $(e.target).is(o.find('input'))) {
                $(this).unbind();
                return;
            }
            if(!o.has($(e.target)).length && !$(e.target).is(o)) {
                $(this).unbind('click');
                o.find('.cancel').click();
            }
          });
          o.find(".cancel").click(function(e) {
            o.html(copy.html());
            if(!checked) {
                o.find('input').click();
            }
            bindInputs(o.find('input'));
          });

          o.find('.save').click(function() {
                let postData = {};
                let key;
                switch(field.toLowerCase()) {
                    case 'email':
                        key = 'contact_email';
                        break;
                    case 'phone':
                        key = 'contact_phone';
                        break;
                    case 'address':
                        key = 'contact_address';
                        break;
                    case 'name':
                        key = 'title';
                        break;
                    default: return;
                }
                let id = o.index() === 1 ? '<?php echo esc_html( $dt_current_id ); ?>' : o.index() === 2 ? '<?php echo esc_html( $dt_dupe_id ); ?>' : null;
                if(id) {
                    let post = API.get_post('contacts', id);
                    post.done(function(res) {
                        if(key !== 'title') {
                            $.each(res[key], function(idx, val) {
                                if(val.value == copy.find('input').val()) {
                                    res[key][idx].value = o.find('input').val();
                                }
                            });
                            postData[key] = {};
                            postData[key].values = res[key];
                        } else {
                            postData[key] = o.find('input').val();
                        }
                        let save = API.update_post( 'contacts', id, postData);
                        save.done(function(res) {
                            o.find('input').attr({
                                type : 'checkbox',
                                'class' : [],
                                style : ''
                            });
                            o.find('input').after(" " + o.find('input').val());
                            toggleRowEdit(o.find('.row-edit a'));
                            bindInputs(o.find('input'));
                        });
                    });
                }
          });
        }

        function toggleRowEdit(o) {
          $(o).closest('.row-edit').find('a').toggleClass('hide');
        }

        function bindInputs(inputs) {
            $(inputs).change(function(){
                if($(this).is(":checked")){
                  $(this).parent().addClass("blueBackground");
                }else{
                  $(this).parent().removeClass("blueBackground");
                }
            });
            $("#merge-form input[type='radio']").change(function(){
                $("#merge-form input[type='radio']").each(function() {
                    if($(this).is(":checked")){
                      $(this).parent().addClass("blueBackground");
                    }else{
                      $(this).parent().removeClass("blueBackground");
                    }
                });
            });
        }

        function merge() {
            $('#submit-merge').toggleClass('loading').attr("disabled", true)

            let form = $("#merge-form");
            let master = form.find('input[name=master-record]:checked').val();

            let values = {};
            $.each($('#merge-form').serializeArray(), function(i, field) {
                if ( field.name.includes('[]')){
                  let name = field.name.replace('[]', '')
                  if ( values[name] === undefined ){
                    values[name] = []
                  }
                  values[name].push(field.value)
                } else {
                    values[field.name] = field.value;
                }
            });

            window.makeRequestOnPosts( "POST", 'contacts/merge', values ).then(resp=>{
                window.location = master
            }).catch(err=>{
                console.error(err);
                $('#submit-merge').toggleClass('loading').attr("disabled", false)
                $('#merge_errors').html( '<?php esc_html_e( "Sorry, something went wrong", 'disciple_tools' ) ?>: ' + window.lodash.escape(window.lodash.get(err, 'responseJSON.message', err) ))
            })
        }
        </script>

    </div> <!-- end #inner-content -->

</div> <!-- end #content -->

<?php get_footer(); ?>
