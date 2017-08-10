<?php $contact = Disciple_Tools_Contacts::get_contact( get_the_ID(), true ); ?>
<?php $channel_list = Disciple_Tools_Contacts::get_channel_list(); ?>
<?php //var_dump($contact->fields) ?>
<?php
function contact_details_status( $id, $verified, $invalid ){
    $buttons = '<img id="'. $id .'-verified" class="details-status" style="display:' . $verified . '" src="'.get_template_directory_uri() . '/assets/images/verified.svg"/>';
    $buttons .= '<img id="'. $id .'-invalid" class="details-status" style="display:' . $invalid . '" src="'.get_template_directory_uri() . '/assets/images/invalid.svg" />';
    return $buttons;
}
?>

<section id="post-<?php the_ID(); ?>" >
    <span id="contact-id" style="display: none"><?php echo get_the_ID()?></span>

    <div class="row item-details-header-row">
            <i class="fi-torso large"></i><span class="item-details-header"><?php the_title_attribute(); ?></span>
            <span class="button alert label">
              Status: <?php echo esc_html( $contact->fields["overall_status"]["label"] ) ?>
            </span>
            <button class="tiny button">Pause</button>
            <button class="tiny button">Close</button>
            <button class="tiny button float-right" onclick="edit_fields()">Edit</button>
    </div>


    <div id="display-fields">
        <div class="row">

            <div class="medium-4 columns">
                <strong><?php echo $channel_list["phone"]["label"] ?></strong>
                <i class="fa fa-plus"></i>
                <ul>
                    <?php
                    foreach($contact->fields[ "contact_phone" ] ?? [] as $field => $value){
                        $verified = isset( $value["verified"] ) && $value["verified"] === true ? "inline" :"none";
                        $invalid = isset( $value["invalid"] ) && $value["invalid"] === true ? "inline" :"none";
                        echo  '<li>' . esc_html( $value["value"] ) .
                        contact_details_status( $value["key"], $verified, $invalid ) .
                        '</li>';
                    }?>
                </ul>
                <strong><?php echo $channel_list["email"]["label"] ?></strong>
                <ul>
                    <?php
                    foreach($contact->fields[ "contact_email" ] ?? [] as $value){
                        $verified = isset( $value["verified"] ) && $value["verified"] === true ? "inline" :"none";
                        $invalid = isset( $value["invalid"] ) && $value["invalid"] === true ? "inline" :"none";
                        echo  '<li>' . esc_html( $value["value"] ) .
                        contact_details_status( $value["key"], $verified, $invalid ) .
                        '</li>';
                    }
                    ?>
                </ul>
            </div>
            <div class="medium-4 columns">
                <strong>Assigned To</strong>
                <ul>
                    <li>
                    <?php
                    if ( isset( $contact->fields["assigned_to"] ) ){
                        echo esc_html( $contact->fields["assigned_to"]["display"] );
                    } else {
                        echo "None Assigned";
                    }
                    ?>
                    </li>
                </ul>
                <strong>Locations</strong>
                <ul>
                    <?php
                    foreach($contact->fields[ "locations" ] ?? [] as $value){
                        echo '<li><a href="' . esc_attr( $value->permalink ) . '">'. esc_html( $value->post_title ) .'</a></li>';
                    }?>
                </ul>

            </div>
            <div class="medium-4 columns">
                <?php
                foreach($contact->fields as $field_key => $values){
                    if ( strpos( $field_key, "contact_" ) === 0 &&
                        strpos( $field_key, "contact_phone" ) === false &&
                        strpos( $field_key, "contact_email" ) === false) {
                        $channel =   explode( '_', $field_key )[1];
                        if ( isset( $channel_list[$channel] ) ) {
                            if ( $values && sizeof( $values ) > 0 ) {
                                echo "<strong>" . $channel_list[$channel]["label"] . "</strong>";
                            }
                            $html = "<ul>";
                            foreach ($values as $value) {
                                $verified = isset( $value["verified"] ) && $value["verified"] === true ? "inline" :"none";
                                $invalid = isset( $value["invalid"] ) && $value["invalid"] === true ? "inline" :"none";
                                echo  '<li>' . esc_html( $value["value"] ) .
                                    contact_details_status( $value["key"], $verified, $invalid ) .
                                    '</li>';
                            }
                            $html .= "</ul>";
                            echo $html;
                        }
                    }
                }
                ?>
            </div>
        </div>

        <div class="row">
            <div id="show-more-content" data-toggler
                 data-animate="fade-in fade-out" aria-expanded="false" style="display:none;">
                <div class="medium-4 columns">
                    <strong>Address</strong>
                    <ul>
                        <?php
                        foreach($contact->fields[ "address" ]  ?? [] as $value){
                            $verified = isset( $value["verified"] ) && $value["verified"] === true ? "inline" :"none";
                            $invalid = isset( $value["invalid"] ) && $value["invalid"] === true ? "inline" :"none";
                            echo  '<li>' . esc_html( $value["value"] ) .
                            contact_details_status( $value["key"], $verified, $invalid ) .
                            '</li>';
                        }?>
                    </ul>
                </div>
            </div>
        </div>
        <div class="row show-more-button" style="text-align: center" >
            <button class="clear" data-toggle="show-more-button show-more-content show-content-button"  href="#">SHOW
                <span id="show-more-button" data-toggler data-animate="fade-in fade-out">MORE <img src="<?php echo get_template_directory_uri() . '/assets/images/small-add.svg' ?>"/></span>
                <span id="show-content-button" data-toggler data-animate="fade-in fade-out" aria-expanded="false" style="display:none;">LESS <i class="fi-minus"></i></span>
            </button>
        </div>
    </div>
    <div id="edit-fields" style="display: none">

        <?php
        foreach( $channel_list as $channel_key => $channel_value ){
            $field_key = "contact_" . $channel_key;
            $new_input_id = "new-" . $channel_key;
            $list_id = $channel_key . "-list";
            ?>
            <strong><?php echo $channel_value["label"] ?></strong>
            <button onclick="add_contact_input(<?php echo get_the_ID() ?>, '<?php echo $new_input_id?>', '<?php echo $list_id?>' )">
             <img src="<?php echo get_template_directory_uri() . '/assets/images/small-add.svg' ?>"/>
            </button>
            <ul id="<?php echo $list_id?>">
            <?php
            if ( isset( $contact->fields[$field_key] )){
                foreach($contact->fields[ $field_key ] ?? [] as $value){
                    $verified = isset( $value["verified"] ) && $value["verified"] === true;
                    $invalid = isset( $value["invalid"] ) && $value["invalid"] === true;
                    $html = '<li>
                        <input id="' . esc_attr( $value["key"] ) . '" value="' . esc_attr( $value["value"] ) . '" onchange="save_field('. esc_attr( get_the_ID() ) . ', \'' . esc_attr( $value["key"] ) . '\')">';
                    if ( !$verified ){
                        $html .= '<button class="details-status-button verify" id="' . esc_attr( $value["key"] ) . '-verify" onclick="verify_contact_method(' . get_the_ID() . ', \'' . esc_attr( $value["key"] ) . '\')">Verify</button>';
                    }
                    if ( !$invalid ){
                        $html .= '<button class="details-status-button invalid" id="' . esc_attr( $value["key"] ) . '-invalidate" onclick="invalidate_contact_method(' . get_the_ID() . ', \'' . esc_attr( $value["key"] ) . '\')">Invalidate</button>';
                    }
                    $html .= '</li>';
                    echo $html;
                }
            }?>
            </ul>

            <?php
        }

        if ( isset( $contact->fields["address"] ) ){
            $type_label = "Address";
            $type = "address";
            $new_input_id = "new-" . $type;
            $list_id = $type . "-list";
            ?>
            <strong><?php echo $type_label?></strong>
            <button onclick="add_contact_input(<?php echo get_the_ID() ?>, '<?php echo $new_input_id?>', '<?php echo $list_id?>' )">
                <img src="<?php echo get_template_directory_uri() . '/assets/images/small-add.svg' ?>"/>
            </button>
            <ul id="<?php echo $list_id?>">
                <?php
                foreach($contact->fields[ "address" ] ?? [] as $value){
                    $verified = isset( $value["verified"] ) && $value["verified"] === true;
                    $invalid = isset( $value["invalid"] ) && $value["invalid"] === true;
                    $html = '<li>';
                    if ( !$verified ){
                        $html .= '<button class="details-status-button verify" id="' . esc_attr( $value["key"] ) . '-verify" onclick="verify_contact_method(' . get_the_ID() . ', \'' . esc_attr( $value["key"] ) . '\')">Verify</button>';
                    }
                    if ( !$invalid ){
                        $html .= '<button class="details-status-button invalid" id="' . esc_attr( $value["key"] ) . '-invalidate" onclick="invalidate_contact_method(' . get_the_ID() . ', \'' . esc_attr( $value["key"] ) . '\')">Invalidate</button>';
                    }
                    $html .= '<textarea id="' . esc_attr( $value["key"] ) . '" onchange="save_field('. esc_attr( get_the_ID() ) . ', \'' . esc_attr( $value["key"] ) . '\')">'
                            . esc_html( $value["value"] ) .
                        '</textarea>
                    </li>';
                    echo $html;
                }?>
            </ul>
            <?php
        }
        ?>
    </div>


</section> <!-- end article -->

