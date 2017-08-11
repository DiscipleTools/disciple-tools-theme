<?php $contact = Disciple_Tools_Contacts::get_contact( get_the_ID(), true );
$channel_list = Disciple_Tools_Contacts::get_channel_list();
$users = Disciple_Tools_Contacts::get_assignable_users( get_the_ID() );
$locations = Disciple_Tools_Locations::get_locations();
$contact_fields = Disciple_Tools_Contacts::get_contact_fields();
function contact_details_status( $id, $verified, $invalid ){
    $buttons = '<img id="'. $id .'-verified" class="details-status" style="display:' . $verified . '" src="'.get_template_directory_uri() . '/assets/images/verified.svg"/>';
    $buttons .= '<img id="'. $id .'-invalid" class="details-status" style="display:' . $invalid . '" src="'.get_template_directory_uri() . '/assets/images/broken.svg" />';
    return $buttons;
}
//var_dump($contact->fields["reason_paused"]);
//var_dump($contact->fields["overall_status"]);
?>

<section id="post-<?php the_ID(); ?>" >
    <span id="contact-id" style="display: none"><?php echo get_the_ID()?></span>

    <div class="row item-details-header-row">
            <i class="fi-torso large"></i><span class="item-details-header"><?php the_title_attribute(); ?></span>
            <span class="button alert label">
              Status: <?php echo esc_html( $contact->fields["overall_status"]["label"] ) ?>
            </span>
            <button data-open="pause-contact-modal"class="tiny button">Pause</button>
            <button data-open="close-contact-modal" class="tiny button">Close</button>
            <button class=" float-right" onclick="edit_fields()"><i class="fi-pencil"></i> Edit</button>
    </div>

    <div class="reveal" id="close-contact-modal" data-reveal>
        <h1>Close Contact</h1>
        <p class="lead">Why do you want to close this contact?</p>

        <select id="reason-closed-options">
            <?php
            foreach( $contact_fields["reason_closed"]["default"] as $reason_key => $reason_label ) {
            ?>
                <option value="<?php echo esc_attr( $reason_key )?>"> <?php echo esc_html( $reason_label )?></option>
            <?php
            }
            ?>
        </select>
        <button class="button button-cancel clear" data-close aria-label="Close reveal" type="button">
            Cancel
        </button>
        <button class="button" type="button" id="confirm-close" onclick="close_contact(<?php echo get_the_ID()?>)">
            Confirm
        </button>
        <button class="close-button" data-close aria-label="Close modal" type="button">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    <div class="reveal" id="pause-contact-modal" data-reveal>
        <h1>Pause Contact</h1>
        <p class="lead">Why do you want to pause this contact?</p>

        <select id="reason-paused-options">
            <?php
            foreach( $contact_fields["reason_paused"]["default"] as $reason_key => $reason_label ) {
            ?>
                <option value="<?php echo esc_attr( $reason_key )?>"> <?php echo esc_html( $reason_label )?></option>
            <?php
            }
            ?>
        </select>
        <button class="button button-cancel clear" data-close aria-label="Close reveal" type="button">
            Cancel
        </button>
        <button class="button" type="button" onclick="pause_contact(<?php echo get_the_ID()?>)">
            Confirm
        </button>
        <button class="close-button" data-close aria-label="Close modal" type="button">
            <span aria-hidden="true">&times;</span>
        </button>
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
                <ul class="locations-list">
                    <?php
                    foreach($contact->fields[ "locations" ] ?? [] as $value){
                        echo '<li class="'. $value->ID .'"><a href="' . esc_attr( $value->permalink ) . '">'. esc_html( $value->post_title ) .'</a></li>';
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
        <div class="row">
            <!-- Contact information. Phone, email, etc -->
            <div class="medium-6 columns">
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
            <!-- Contact Fields. Assigned to, location, etc -->
            <div class="medium-6 columns">
                <strong>Assigned To</strong>
                <select id="assigned_to" onchange="save_field(<?php echo get_the_ID();?>, 'assigned_to')">
                    <?php
                    foreach( $users as $user ){
                        if ( isset( $contact->fields["assigned_to"] ) &&
                            $user->ID === (int) $contact->fields["assigned_to"]['id'] ){
                            echo '<option value="user-' . $user->ID. '" selected>' . $user->display_name . '</option>';
                        } else {
                            echo '<option value="user-' . $user->ID. '">' . $user->display_name . '</option>';
                        }
                    }
                    ?>
                </select>

                <strong>Locations</strong>
                <ul class="locations-list">
                <?php
                $location_ids = [];
                foreach($contact->fields[ "locations" ] ?? [] as $value){
                    $location_ids[] = $value->ID;
                    echo '<li class="'. $value->ID .'">
                    <a href="' . esc_attr( $value->permalink ) . '">'. esc_html( $value->post_title ) .'</a>
                    <button class="details-remove-button" onclick="remove_location(' . get_the_ID() . ', \'locations\', ' . $value->ID . ')">Remove</button>
                    </li>';
                }?>
                </ul>
                <select id="locations" onchange="add_location( <?php echo get_the_ID();?>, 'locations')">
                    <?php
                    echo '<option value="0"></option>';
                    foreach( $locations as $location ){
                        if ( !in_array( $location->ID, $location_ids )){
                            echo '<option value="' . $location->ID. '">' . esc_html( $location->post_title ) . '</option>';
                        }
                    }
                    ?>
                </select>
            </div>
        </div>
    </div>


</section> <!-- end article -->

