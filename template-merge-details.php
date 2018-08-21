<?php
/*
Template Name: Merge Details
*/
?>


<?php

get_header();
$nonce = $_POST['dt_contact_nonce'];
if(!wp_verify_nonce($nonce)) {
    header("Location: /contacts");
}
$current_Id =$_POST['currentid'];
$dupe_Id =$_POST['dupeid'];
list($current, $duplicate, $data, $fields) = Disciple_Tools_Contacts::get_merge_data( $current_Id, $dupe_Id );
$contact = Disciple_Tools_Contacts::get_contact( $current_Id, true );
$channel_list = Disciple_Tools_Contacts::get_channel_list();
$current_user = wp_get_current_user();
$contact_fields = Disciple_Tools_Contacts::get_contact_fields();



  $contact_name =$contact['title'] ?? null;
  $contact_address =$contact['contact_address'][0]['value'] ?? null;
  $contact_phone =$contact['contact_phone'][0]['value'] ?? null;
  $contact_email =$contact['contact_email'][0]['value'] ?? null;
  $contact_facebook =$contact['contact_facebook'][0]['value'] ?? null;


  $duplicate_contact = Disciple_Tools_Contacts::get_contact( $dupe_Id, true );

  $duplicate_contact_name =$duplicate_contact['title'] ?? null;
  $duplicate_contact_address =$duplicate_contact['contact_address'][0]['value'] ?? null;
  $duplicate_contact_phone =$duplicate_contact['contact_phone'][0]['value'] ?? null;
  $duplicate_contact_email =$duplicate_contact['contact_email'][0]['value'] ?? null;
  $duplicate_contact_facebook =$duplicate_contact['contact_facebook'][0]['value'] ?? null;

  $used_values = array(
      'contact_phone' => array(),
      'contact_address' => array(),
      'contact_email' => array()
  );

  $edit_Row = "<span class='row-edit'><a onclick='editRow(this, edit);' title='Edit' class='fi-pencil'></a><a class='fi-x hide cancel' title='Cancel'></a><a class='fi-check hide save' title='Save'></a></span>";
    ?>

    <div id="content">

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
                    <?php echo "<span class='contact_name'>".esc_html_e($contact_name, 'disciple_tools')."</span> $edit_Row"; ?><br>
                    <a onclick='selectAll(this);'><?php esc_html_e( "Select All", 'disciple_tools' ) ?></a>
                  </div>
                  <div class="merge-column">
                    <?php echo "<span class='contact_name'>".esc_html_e($duplicate_contact_name, 'disciple_tools')."</span> $edit_Row"; ?><br>
                    <a onclick='selectAll(this);'><?php esc_html_e( "Select All", 'disciple_tools' ) ?></a>
                  </div>
                </div>

                <form id="merge-form" onsubmit="merge(event);" method="post" action="<?php echo esc_url( site_url( '/contacts/' ) ); ?>" >
                    <input type='hidden' name='dt_contact_nonce' value="<?php echo esc_attr(wp_create_nonce()); ?>"/>
                    <input type='hidden' name='duplicateId' value='<?php echo wp_unslash($dupe_Id); ?>'/>
                <div class="line-wrap">
                  <div class="merge-column">
                    <span class="bold"><?php esc_html_e( "Master Record", 'disciple_tools' ) ?></span>
                  </div>
                  <div class="merge-column">
                    <input type="hidden" name="currentid" value="<?php echo wp_unslash($current_Id); ?>">
                    <input type="radio" required name="master-record" value="contact1"> <?php esc_html_e( "Use as master", 'disciple_tools' ) ?>
                  </div>
                  <div class="merge-column">
                    <input type="radio" required name="master-record" value="contact2"> <?php esc_html_e( "Use as master", 'disciple_tools' ) ?>

                  </div>
                </div>

                <?php
                foreach ($fields as $key => $field) {
                    foreach ($data[$key] as $idx => $type) {
                        echo "<div class='line-wrap'>";
                            echo "<div class='merge-column'><span class='bold'>$field</span></div>";
                        foreach ($type as $vals) {
                            $value = $vals['value'];
                            echo "<div class='merge-column'>";
                            if ($value) {
                                echo "<input type='checkbox' name='" . strtolower( $field ) . "[]' value='$value'> $value $edit_Row";
                            } else {
                                echo "<div class='empty'></div>";
                            }
                            echo "</div>";
                        }
                        echo "</div>";
                    }
                }

                echo "<button class='button' name='merge-submit' type='submit' value='Merge'>Merge</button>";
                echo "</form>";
                ?>
            </div>
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
                      var index = $(o).closest('div.merge-column').index();
                      $("#merge-form").find('input[type=checkbox]').each(function(key, el) {
                          var div = $(el).closest('div.merge-column').not('.hide');
                          if(div.index() === index) {
                              $(el).click();
                          }
                      });
                  }

                  $(".merge-column").click(function(e) {
                        var input = $(this).find('input[type=radio], input[type=checkbox]');
                        if($(e.target).is(input) || $(e.target).is('.fi-pencil')) {
                            return;
                        }
                        input.click();
                  });



              function editRow(o, callback) {
                  var line = $(o).closest('.line-wrap');
                  var cell = $(o).closest('.merge-column');
                  var copy = cell.clone();
                  var input = cell.find('input');
                  var checked = input.is(':checked');
                  var value = cell.find('input').val();
                  var field = line.find('.merge-column').eq(0).find('.bold').text();
                  var editRow = cell.find('.row-edit');
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
                        // TODO : cancel editing on outside clicks
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
                        var postData = {};
                        var key;
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
                        var id = o.index() === 1 ? '<?php echo $current_Id; ?>' : o.index() === 2 ? '<?php echo $dupe_Id; ?>' : null;
                        if(id) {
                            var post = API.get_post('contact', id);
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
                                var save = API.save_field_api('contact', id, postData);
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

                function merge(e) {
                    var form = $("#merge-form");
                    var master = form.find('input[name=master-record]:checked').val();
                    var id = master === 'contact1' ? '<?php echo $current_Id; ?>' : '<?php echo $dupe_Id; ?>';
                    form.attr('action', form.attr('action') + id);
                }
              </script>
            </main> <!-- end #main -->

        </div> <!-- end #inner-content -->

    </div> <!-- end #content -->

<?php get_footer(); ?>
