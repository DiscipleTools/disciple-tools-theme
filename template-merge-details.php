<?php
/*
Template Name: Merge Details
*/

if ( ! current_user_can( 'access_contacts' ) ) {
    wp_die( esc_html( "You do not have permission to access contacts" ), "Permission denied", 403 );
}

get_header();
if ( !isset( $_POST['dt_contact_nonce'] ) || !wp_verify_nonce( sanitize_key( $_POST['dt_contact_nonce'] ) ) || !isset( $_POST['currentid'], $_POST['dupeid'] )) {
    header( "Location: /contacts" );
}
$dt_current_id = sanitize_text_field( wp_unslash( $_POST['currentid'] ) );
$dt_dupe_id = sanitize_text_field( wp_unslash( $_POST['dupeid'] ) );
list($dt_current, $dt_duplicate, $dt_data, $dt_fields) = Disciple_Tools_Contacts::get_merge_data( $dt_current_id, $dt_dupe_id );
$dt_contact = Disciple_Tools_Contacts::get_contact( $dt_current_id, true );
$dt_channel_list = Disciple_Tools_Contacts::get_channel_list();
$dt_current_user = wp_get_current_user();
$dt_contact_fields = Disciple_Tools_Contacts::get_contact_fields();



$dt_contact_name =$dt_contact['title'] ?? null;
$dt_contact_address =$dt_contact['contact_address'][0]['value'] ?? null;
$dt_contact_phone =$dt_contact['contact_phone'][0]['value'] ?? null;
$dt_contact_email =$dt_contact['contact_email'][0]['value'] ?? null;
$dt_contact_facebook =$dt_contact['contact_facebook'][0]['value'] ?? null;


$dt_duplicate_contact = Disciple_Tools_Contacts::get_contact( $dt_dupe_id, true );

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
                    <span><?php esc_html_e( "Created:", 'disciple_tools' ) ?> <?php echo esc_html( $dt_contact["created_date"] ?? "" ) ?></span><br>
                    <a onclick='selectAll(this);'><?php esc_html_e( "Select All", 'disciple_tools' ) ?></a>
                  </div>
                  <div class="merge-column">
                    <a class='contact_name' href="<?php echo esc_html( get_site_url() . "/contacts/" . $dt_dupe_id ) ?>"><?php echo esc_html( $dt_duplicate_contact_name ) ?></a>
                    <span class='row-edit'><a onclick='editRow(this, edit);' title='<?php esc_html_e( "Edit", 'disciple_tools' ) ?>' class='fi-pencil'></a><a class='fi-x hide cancel' title='<?php esc_html_e( "Cancel", 'disciple_tools' ) ?>'></a><a class='fi-check hide save' title='<?php esc_html_e( "Save", 'disciple_tools' ) ?>'></a></span>
                    <br>
                    <span><?php echo esc_html( '#' . $dt_duplicate_contact["ID"] ) ?></span><br>
                    <span><?php esc_html_e( "Status:", 'disciple_tools' ) ?> <?php echo esc_html( $dt_duplicate_contact["overall_status"]["label"] ?? "" ) ?></span><br>
                    <span><?php esc_html_e( "Created:", 'disciple_tools' ) ?> <?php echo esc_html( $dt_duplicate_contact["created_date"] ?? "" ) ?></span><br>
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
                  <div class="merge-column">
                    <input type="radio" required name="master-record" value="<?php echo esc_html( $dt_duplicate_contact["ID"] ); ?>"> <?php esc_html_e( "Use as master", 'disciple_tools' ) ?>

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

                <button class='button' name='merge-submit' type='button' onclick='merge()' value='Merge'><?php esc_html_e( 'Merge', 'disciple_tools' ); ?></button>
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
            })
        }
        </script>

    </div> <!-- end #inner-content -->

</div> <!-- end #content -->

<?php get_footer(); ?>
