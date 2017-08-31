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

<?php if (isset( $contact->fields["requires_update"] ) && $contact->fields["requires_update"]["key"] === "yes"){ ?>
<div class="callout alert">
    <h4>This contact needs and update.</h4>
    <p>It has been a while since this contact seen an update. Please do so.</p>
</div>
<?php } ?>
<?php if (isset( $contact->fields["overall_status"] ) && $contact->fields["overall_status"]["key"] == "assigned"){ ?>
<div id="accept-contact" class="callout alert">
    <h4 style="display: inline-block">This contact has been assigned to you</h4>
    <span class="float-right">
        <button onclick="details_accept_contact(<?php echo get_the_ID() ?>, true)" class="button small ">Accept</button>
        <button onclick="details_accept_contact(<?php echo get_the_ID() ?>, false)" class="button small ">Decline</button>
    </span>
</div>
<?php } ?>


<?php if (current_user_can( "assign_any_contact" )){?>
<section class="bordered-box">
    <p class="section-header">Dispatch</p>
    <div class="grid-x grid-margin-x">
        <div class="medium-6 cell">
            <strong>Assigned To:</strong>
            <select class="assigned_to_select" id="dispatcher_assigned_to" onchange="save_field(<?php echo get_the_ID();?>, 'assigned_to', 'dispatcher_assigned_to')">
                <option value="0"></option>
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
        </div>
        <div class="medium-6 cell">
            <strong>Set Unassignable Reason:</strong>
            <select id="reason_unassignable" onchange="save_field(<?php echo get_the_ID();?>, 'reason_unassignable')">
                <?php
                foreach( $contact_fields["reason_unassignable"]["default"] as $reason_key => $reason_value ) {
                    if ( isset( $contact->fields["reason_unassignable"] ) &&
                        $contact->fields["reason_unassignable"]["key"] === $reason_key ){
                        echo '<option value="'.$reason_key . '" selected>' . $reason_value . '</option>';
                    } else {
                        echo '<option value="'.$reason_key . '">' . $reason_value . '</option>';

                    }
                }
                ?>
            </select>
        </div>
    </div>
</section>
<?php } ?>

<section class="bordered-box">
    <span id="contact-id" style="display: none"><?php echo get_the_ID()?></span>

    <div class=" item-details-header-row">
        <i class="fi-torso large"></i><span class="item-details-header"><?php the_title_attribute(); ?></span>
        <span class="button alert label">
            Status: <span id="overall-status"><?php echo esc_html( $contact->fields["overall_status"]["label"] ) ?></span>
            <span id="reason">
                <?php
                if ( $contact->fields["overall_status"]["key"] === "paused" &&
                    isset( $contact->fields["reason_paused"] )){
                    echo '(' . $contact->fields["reason_paused"]["label"] . ')';
                } else if ( $contact->fields["overall_status"]["key"] === "closed" &&
                    isset( $contact->fields["reason_closed"] )){
                    echo '(' . $contact->fields["reason_closed"]["label"] . ')';
                }
                ?>
            </span>
        </span>
        <button data-open="pause-contact-modal" class="tiny button">Pause</button>
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
        <button class="button" type="button" id="confirm-pause" onclick="pause_contact(<?php echo get_the_ID()?>)">
            Confirm
        </button>
        <button class="close-button" data-close aria-label="Close modal" type="button">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    <div class="display-fields">
        <div class="grid-x">

            <div class="medium-4 cell">
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
            <div class="medium-4 cell">
                <strong>Assigned To</strong>
                <ul>
                    <li id="assigned-to">
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
            <div class="medium-4 cell">
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

        <div class="grid-x">
            <div id="show-more-content" data-toggler
                 data-animate="fade-in fade-out" aria-expanded="false" style="display:none;">
                <div class="medium-4 cell">
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
    <div class="edit-fields" style="display: none">
        <div class="grid-x">
            <!-- Contact information. Phone, email, etc -->
            <div class="medium-6 cell">
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
            <div class="medium-6 cell">
                <strong>Assigned To</strong>
                <select class="assigned_to_select" id="assigned_to" onchange="save_field(<?php echo get_the_ID();?>, 'assigned_to', 'assigned_to')">
                    <option value="0"></option>
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
                    <button class="details-remove-button" onclick="remove_item(' . get_the_ID() . ', \'locations\', ' . $value->ID . ')">Remove</button>
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




    <div class="reveal" id="share-contact-modal" data-reveal>

        <p class="lead">Share settings</p>

        <h6>Already sharing with</h6>

        <ul>
            <?php
            foreach( ["contact1", "contact2", "contact3"] as $contact ) {
                ?>
                <li> <?php echo $contact?></li>
            <?php } ?>
        </ul>

        <p>
            <label>Share this contact with the following email address:
            <input type="text" placeholder="Enter an email address">
            </label>
        </p>

        <div class="grid-x">
            <button class="button button-cancel clear" data-close aria-label="Close reveal" type="button">
                Cancel
            </button>
            <button class="button" type="button" id="confirm-pause" onclick="share_contact(<?php echo get_the_ID()?>)">
                Share
            </button>
            <button class="close-button" data-close aria-label="Close modal" type="button">
                <span aria-hidden="true">&times;</span>
            </button>

        </div>
    </div>

</section> <!-- end article -->

