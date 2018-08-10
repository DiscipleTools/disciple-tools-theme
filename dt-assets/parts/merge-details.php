<?php

( function () {

    $contact = Disciple_Tools_Contacts::get_contact( get_the_ID(), true );
    $channel_list = Disciple_Tools_Contacts::get_channel_list();
    $current_user = wp_get_current_user();
    $contact_fields = Disciple_Tools_Contacts::get_contact_fields();

?>
    <div class="reveal" id="merge-dupe-edit" style="border-radius:10px; padding:0px; padding-bottom:20px; border: 1px solid #3f729b;;" data-reveal>
      <div class="merge-modal-header" style="background-color:#3f729b; color:white; text-align:center;">
        <h1 style="font-size:1.5rem; padding:10px 0px;"><?php esc_html_e( "Duplicate Contacts", 'disciple_tools' ) ?></h1>
      </div>
        <div class="display-fields" style="padding:10px;">
          <h4 style="text-align:center; font-size:1.25rem; font-weight:bold; padding:10px 0px 0px; margin-bottom:0px;"><?php esc_html_e( "Original Contact", 'disciple_tools' ) ?></h4>
          <?php
          $contact_name=$contact['title'] ?? null;
//          $contact_address=$contact['contact_address'][0]['value'] ?? null;
//          $contact_phone=$contact['contact_phone'][0]['value'] ?? null;
//          $contact_email=$contact['contact_email'][0]['value'] ?? null;
          $contact_facebook=$contact['contact_facebook'][0]['value'] ?? null;
          
          $fields = array(
              'contact_phone' => array(),
              'contact_address' => array(),
              'contact_email' => array(),
              'contact_facebook' => array()
          );

          echo "<div style='background-color:#e1f5fe; padding:2%;'>";
          echo "<h5 style='font-weight:bold; color:#3f729b'>".$contact_name."</h5>";
          foreach($contact['contact_phone'] ?? array() as $phone) {
            if ($phone['value'] !=''){
              echo "<img src='".esc_url( get_template_directory_uri() )."/dt-assets/images/phone.svg'>&nbsp;".$phone['value']."<br>";
              array_push($fields['contact_phone'], $phone['value']);
            }
          }
          foreach($contact['contact_address'] ?? array() as $address) {
            if ($address['value']!=''){
              echo "<img src='".esc_url( get_template_directory_uri() )."/dt-assets/images/house.svg'>&nbsp;".$address['value']."<br>";
              array_push($fields['contact_address'], $address['value']);
            }
          }
          foreach($contact['contact_email'] ?? array() as $email) {
            if ($email['value']!=''){
              echo "<img src='".esc_url( get_template_directory_uri() )."/dt-assets/images/email.svg'>&nbsp;".$email['value']."<br>";
              array_push($fields['contact_email'], $email['value']);
            }
          }
          if ($contact_facebook!=''){
            echo "<img src='".esc_url( get_template_directory_uri() )."/dt-assets/images/facebook.svg'>&nbsp;".$contact_facebook."<br>";
            array_push($fields['contact_facebook'], $contact_facebook);
          }
          echo"</div>";
          ?>
          <h4 style="text-align:center; font-size:1.25rem; font-weight:bold; padding:20px 0px 0px; margin-bottom:0px;"><?php esc_html_e( "Possible Duplicates", 'disciple_tools' ) ?></h4>
          <div style='display: inline-block; width: 100%;'>
              <form method='POST' id='form-unsure-dismiss' action='/contacts/<?php echo get_the_Id(); ?>'>
                <input type='hidden' name='id' value='<?php echo get_the_Id(); ?>'/>
                <a style='float: right; margin-left: 10%;' onclick='dismiss_all();'>Dismiss All</a>
                <a style='float: right;' onclick='unsure_all();'>Unsure All</a>
                <input type='submit' id='unsure-dismiss-submit' style='display: none;' value='submit'/>
              </form>
          </div>
          <?php
          $duplicate_post_meta = get_post_meta(get_the_Id(), 'duplicate_data');
          $duplicate_ids = array();
          foreach($duplicate_post_meta as $array) {
              foreach($array as $key => $vals) {
                  if($key == 'override' || $key == 'unsure') { continue; }
                  foreach($vals as $val) {
                      array_push($duplicate_ids, $val);
                  }
              }
          }
          $duplicate_ids = array_merge(array_unique($duplicate_ids));
          foreach ($duplicate_ids as $value){
            if(in_array($value, $duplicate_post_meta['override'] ?? [])) { continue; }
            $duplicate_phone=$value;



            // var_dump($duplicate_post_meta);
            $duplicate_phone_clean=str_replace('int(', '',$duplicate_phone);
            $possible_duplicate= get_post_meta($duplicate_phone_clean);
            $duplicate_contact = Disciple_Tools_Contacts::get_contact( $duplicate_phone_clean, true );

            $duplicate_contact_name=$duplicate_contact['title'] ?? null;
            $duplicate_contact_address=$duplicate_contact['contact_address'][0]['value'] ?? null;
            $duplicate_contact_phone=$duplicate_contact['contact_phone'][0]['value'] ?? null;
            $duplicate_contact_email=$duplicate_contact['contact_email'][0]['value'] ?? null;
            $duplicate_contact_facebook=$duplicate_contact['contact_facebook'][0]['value'] ?? null;

            echo "<div style='background-color:#f2f2f2; padding:2%; overflow:hidden;'>";

            echo "<h5 style='font-weight:bold; color:#3f729b'>".$duplicate_contact_name."</h5>";
            foreach($duplicate_contact['contact_phone'] ?? array() as $dphone) {
                if (in_array($dphone['value'], $fields['contact_phone'])){
                  echo "<img src='".esc_url( get_template_directory_uri() )."/dt-assets/images/phone.svg'>&nbsp;".$dphone['value']."<br>";
                }
            }
            foreach($duplicate_contact['contact_address'] ?? array() as $daddress) {
                if (in_array($daddress['value'], $fields['contact_address'])){
                  echo "<img src='".esc_url( get_template_directory_uri() )."/dt-assets/images/house.svg'>&nbsp;".$daddress['value']."<br>";
                }
            }
            foreach($duplicate_contact['contact_email'] ?? array() as $demail) {
                if(in_array($demail['value'], $fields['contact_email'])){
                  echo "<img src='".esc_url( get_template_directory_uri() )."/dt-assets/images/email.svg'>&nbsp;".$demail['value']."<br>";
                }
            }
            foreach($duplicate_contact['contact_facebook'] ?? array() as $dfacebook) {
                if(in_array($dfacebook['value'], $fields['contact_facebook'])){
                  echo "<img src='".esc_url( get_template_directory_uri() )."/dt-assets/images/facebook.svg'>&nbsp;".$dfacebook['value']."<br>";
                }
            }
            ?>

          <button class='mergelinks' onclick="$('#dismiss-id').val('<?php echo $value; ?>'); $('#form-dismiss input[type=submit]').click();" style='float:right; padding-left:10%;'><a><?php esc_html_e( "Dismiss", 'disciple_tools' ) ?></a></button>
          <button class='mergelinks' onclick="$('#unsure-id').val('<?php echo $value; ?>'); $('#form-unsure input[type=submit]').click();" style='float:right; padding-left:10%;'><a><?php esc_html_e( "Unsure", 'disciple_tools' ) ?></a></button>

          <form action="/contacts/mergedetails" method="post"><input type='hidden' name='currentid' value='<?php echo $contact['ID'];?>'/><input type='hidden' name='dupeid' value='<?php echo $duplicate_contact['ID']; ?>'/><button type='submit' style='float:right; padding-left:10%;'><a><?php esc_html_e( "Merge", 'disciple_tools' ) ?></a></button></form></div>

        <?php
          }
          if($duplicate_post_meta[0]['unsure'] ?? array()) {
          ?>
           <h4 style="text-align:center; font-size:1.25rem; font-weight:bold; padding:20px 0px 0px; margin-bottom:0px;"><?php esc_html_e( "Unsure Duplicates", 'disciple_tools' ) ?></h4>
           <?php
          }
           foreach ($duplicate_post_meta[0]['unsure'] ?? [] as $value){
            if(in_array($value, $duplicate_post_meta['override'] ?? [])) { continue; }
            $duplicate_phone=$value;



            // var_dump($duplicate_post_meta);
            $duplicate_phone_clean=str_replace('int(', '',$duplicate_phone);
            $possible_duplicate= get_post_meta($duplicate_phone_clean);
            $duplicate_contact = Disciple_Tools_Contacts::get_contact( $duplicate_phone_clean, true );

            $duplicate_contact_name=$duplicate_contact['title'] ?? null;
            $duplicate_contact_address=$duplicate_contact['contact_address'][0]['value'] ?? null;
            $duplicate_contact_phone=$duplicate_contact['contact_phone'][0]['value'] ?? null;
            $duplicate_contact_email=$duplicate_contact['contact_email'][0]['value'] ?? null;
            $duplicate_contact_facebook=$duplicate_contact['contact_facebook'][0]['value'] ?? null;

            echo "<div style='background-color:#f2f2f2; padding:2%; overflow:hidden;'>";

            echo "<h5 style='font-weight:bold; color:#3f729b'>".$duplicate_contact_name."</h5>";
            foreach($duplicate_contact['contact_phone'] ?? array() as $dphone) {
                if (in_array($dphone['value'], $fields['contact_phone'])){
                  echo "<img src='".esc_url( get_template_directory_uri() )."/dt-assets/images/phone.svg'>&nbsp;".$dphone['value']."<br>";
                }
            }
            foreach($duplicate_contact['contact_address'] ?? array() as $daddress) {
                if (in_array($daddress['value'], $fields['contact_address'])){
                  echo "<img src='".esc_url( get_template_directory_uri() )."/dt-assets/images/house.svg'>&nbsp;".$daddress['value']."<br>";
                }
            }
            foreach($duplicate_contact['contact_email'] ?? array() as $demail) {
                if(in_array($demail['value'], $fields['contact_email'])){
                  echo "<img src='".esc_url( get_template_directory_uri() )."/dt-assets/images/email.svg'>&nbsp;".$demail['value']."<br>";
                }
            }
            foreach($duplicate_contact['contact_facebook'] ?? array() as $dfacebook) {
                if(in_array($dfacebook['value'], $fields['contact_facebook'])){
                  echo "<img src='".esc_url( get_template_directory_uri() )."/dt-assets/images/facebook.svg'>&nbsp;".$dfacebook['value']."<br>";
                }
            }
            ?>

          <button class='mergelinks' onclick="$('#dismiss-id').val('<?php echo $value; ?>'); $('#form-dismiss input[type=submit]').click();" style='float:right; padding-left:10%;'><a><?php esc_html_e( "Dismiss", 'disciple_tools' ) ?></a></button>

          <form action="/contacts/mergedetails" method="post"><input type='hidden' name='currentid' value='<?php echo $contact['ID'];?>'/><input type='hidden' name='dupeid' value='<?php echo $duplicate_contact['ID']; ?>'/><button type='submit' style='float:right; padding-left:10%;'><a><?php esc_html_e( "Merge", 'disciple_tools' ) ?></a></button></form></div>

        <?php
          }
          ?>

        <form action="/contacts/mergedetails" id='form-dismiss' method="POST">
            <input type='hidden' name='dismiss' value='1'/>
            <input type='hidden' id="dismiss-id" name='id' value='<?php echo $value; ?>'/>
            <input type='hidden' name='currentId' value='<?php echo get_the_ID(); ?>'/>
            <input type='submit' style='display: none' value='Dismiss'/>
        </form>
        <form action="/contacts/mergedetails" id='form-unsure' method="POST">
            <input type='hidden' name='unsure' value='1'/>
            <input type='hidden' id="unsure-id" name='id' value='<?php echo $value; ?>'/>
            <input type='hidden' name='currentId' value='<?php echo get_the_ID(); ?>'/>
            <input type='submit' style='display: none' value='Unsure'/>
        </form>
        </div>
    </div>
    
    <script type='text/javascript'>
        function unsure_all() {
            var form = $("#form-unsure-dismiss");
            var submit = form.find('input[type=submit]');
            submit.attr('name', 'unsure_all');
            submit.click();
        }
        
        function dismiss_all() {
            var form = $("#form-unsure-dismiss");
            var submit = form.find('input[type=submit]');
            submit.attr('name', 'dismiss_all');
            submit.click();
        }
    </script>
<?php } )(); ?>
