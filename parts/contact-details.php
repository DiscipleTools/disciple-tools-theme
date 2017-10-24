<?php

( function () {

    $contact = Disciple_Tools_Contacts::get_contact( get_the_ID(), true );
    $channel_list = Disciple_Tools_Contacts::get_channel_list();
    $current_user = wp_get_current_user();
    $contact_fields = Disciple_Tools_Contacts::get_contact_fields();
    $custom_lists = dt_get_option( 'dt_site_custom_lists' );

    function dt_contact_details_status( $id, $verified, $invalid ){
        ?>
        <img id="<?php echo esc_html( $id ) ?>-verified" class="details-status" style="display:<?php echo esc_html( $verified )?>" src="<?php echo esc_html( get_template_directory_uri() . '/assets/images/verified.svg' )?>" />
        <img id="<?php echo esc_html( $id ) ?>-invalid" class="details-status" style="display:<?php echo esc_html( $invalid )?>" src="<?php echo esc_html( get_template_directory_uri() . '/assets/images/broken.svg' )?>" />
        <?php
    }
    function dt_contact_details_edit( $id, $field_type, $remove = false ){
    ?>
        <ul class='dropdown menu' data-click-open='true'
            data-dropdown-menu data-disable-hover='true'
            style='display:inline-block'>
            <li>
                <button class="social-details-options-button">
                    <img src="<?php echo esc_html( get_template_directory_uri() . '/assets/images/menu-dots.svg' )?>" style='padding:3px 3px'>
                </button>
                <ul class='menu'>
                    <li>
                        <button class='details-status-button field-status verify'
                                data-status='valid'
                                data-id='<?php echo esc_html( $id ) ?>'>
                            Valid
                        </button>
                    </li>
                    <li>
                        <button class='details-status-button field-status invalid'
                                data-status="invalid"
                                data-id='<?php echo esc_html( $id ) ?>'>
                            Invalid
                        </button>
                    </li>
                    <li>
                        <button class='details-status-button field-status'
                                data-status="reset"
                                data-id='<?php echo esc_html( $id ) ?>'>
                            Unconfirmed
                        </button>
                    </li>
                    <?php if ($remove){ ?>
                        <li>
                            <button class='details-remove-button delete-method'
                                    data-field='<?php echo esc_html( $field_type ) ?>'
                                    data-id='<?php echo esc_html( $id ) ?>'>
                                Delete item
                            <button>
                        </li>
                    <?php } ?>
                </ul>
            </li>
        </ul>
    <?php } ?>

    <?php if (isset( $contact->fields["requires_update"] ) && $contact->fields["requires_update"]["key"] === "yes"){ ?>
    <div class="callout alert">
        <h4>This contact needs an update.</h4>
        <p>It has been a while since this contact seen an update. Please do so.</p>
    </div>
    <?php } ?>
    <?php if (isset( $contact->fields["overall_status"] ) &&
        $contact->fields["overall_status"]["key"] == "assigned" &&
        $contact->fields["assigned_to"]['id'] == $current_user->ID
    ) { ?>
    <div id="accept-contact" class="callout alert small-12 cell">
        <h4 style="display: inline-block">This contact has been assigned to you</h4>
        <span class="float-right">
            <button onclick="details_accept_contact(<?php echo get_the_ID() ?>, true)" class="button small">Accept</button>
            <button onclick="details_accept_contact(<?php echo get_the_ID() ?>, false)" class="button small alert">Decline</button>
        </span>
    </div>
    <?php } ?>


    <?php if (current_user_can( "assign_any_contacts" )){?>
    <section class="small-12 cell">
        <div class="bordered-box">
            <p class="section-header">Dispatch Section</p>
            <div class="grid-x grid-margin-x">
                <div class="medium-6 cell">
                    <div class="section-subheader">Assigned To:
                        <span class="current-assigned">
                            <?php
                            if ( isset( $contact->fields["assigned_to"] ) ){
                                echo esc_html( $contact->fields["assigned_to"]["display"] );
                            } else {
                                echo "Nobody";
                            }
                            ?>
                        </span>
                    </div>
                    <div class="assigned_to">
                        <input class="typeahead" type="text" placeholder="Type to search users">
                    </div>
                </div>
                <div class="medium-6 cell">
                    <div class="section-subheader">Set Unassignable Reason:</div>
                    <select id="reason_unassignable" class="select-field">
                        <?php
                        foreach ( $contact_fields["reason_unassignable"]["default"] as $reason_key => $reason_value ) {
                            if ( isset( $contact->fields["reason_unassignable"] ) &&
                                $contact->fields["reason_unassignable"]["key"] === $reason_key ){
                                echo '<option value="'. esc_html( $reason_key ) . '" selected>' . esc_html( $reason_value ) . '</option>';
                            } else {
                                echo '<option value="'. esc_html( $reason_key ) . '">' . esc_html( $reason_value ). '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>
        </div>
    </section>
    <?php } ?>

    <section class="cell">
        <div class="bordered-box">
            <span id="contact-id" style="display: none"><?php echo get_the_ID()?></span>

            <div class="item-details-header-row">
                <i class="fi-torso large"></i>
                <span class="item-details-header details-list title" ><?php the_title_attribute(); ?></span>
                <input id="title" class="text-field details-edit" value="<?php the_title_attribute(); ?>">
                <span class="button alert label">
                    Status: <span id="overall-status"><?php echo esc_html( $contact->fields["overall_status"]["label"] ) ?></span>
                    <span id="reason">
                        <?php
                        if ( $contact->fields["overall_status"]["key"] === "paused" &&
                            isset( $contact->fields["reason_paused"] )){
                            echo '(' . esc_html( $contact->fields["reason_paused"]["label"] ) . ')';
                        } else if ( $contact->fields["overall_status"]["key"] === "closed" &&
                            isset( $contact->fields["reason_closed"] )){
                            echo '(' . esc_html( $contact->fields["reason_closed"]["label"] ) . ')';
                        }
                        ?>
                    </span>
                </span>
                <button data-open="pause-contact-modal" class="button">Pause</button>
                <button data-open="close-contact-modal" class="button">Close</button>
                <button id="return-active" onclick="make_active( <?php echo get_the_ID() ?> )"
                        style="display: <?php echo esc_html( ($contact->fields["overall_status"]["key"] === "paused" || $contact->fields["overall_status"]["key"] === "closed") ? "" : "none" ); ?>"
                        class="button">
                    Return to Active
                </button>
                <button class="float-right" id="edit-details">
                    <i class="fi-pencil"></i>
                    <span id="edit-button-label">Edit</span>
                </button>
            </div>

            <div class="reveal" id="close-contact-modal" data-reveal>
                <h1>Close Contact</h1>
                <p class="lead">Why do you want to close this contact?</p>

                <select id="reason-closed-options">
                    <?php
                    foreach ( $contact_fields["reason_closed"]["default"] as $reason_key => $reason_label ) {
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
                    foreach ( $contact_fields["reason_paused"]["default"] as $reason_key => $reason_label ) {
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
            <div class="grid-x grid-margin-x">

                <!--Phone-->
                <!--Email-->
                <div class="xlarge-4 large-6 medium-6 small-12 cell">
                    <div class="section-subheader"><?php echo esc_html( $channel_list["phone"]["label"] ) ?>
                        <button data-id="phone" class="details-edit add-button">
                            <img src="<?php echo esc_html( get_template_directory_uri() . '/assets/images/small-add.svg' ) ?>"/>
                        </button>
                    </div>
                    <ul class="phone details-list">
                        <?php
                        if (sizeof( $contact->fields["contact_phone"] ?? [] ) === 0 ){
                            ?> <li id="no-phone">No phone set</li> <?php
                        }
                        foreach ($contact->fields["contact_phone"] ?? [] as $field => $value){
                            $verified = isset( $value["verified"] ) && $value["verified"] === true ? "inline" :"none";
                            $invalid = isset( $value["invalid"] ) && $value["invalid"] === true ? "inline" :"none";
                            ?>
                            <li class="<?php echo esc_html( $value["key"] ) ?>"><?php echo esc_html( $value["value"] );
                            dt_contact_details_status( $value["key"], $verified, $invalid );  ?></li>
                        <?php } ?>
                    </ul>
                    <ul id="phone-list" class="details-edit">
                    <?php
                    if ( isset( $contact->fields["contact_phone"] )){
                        foreach ($contact->fields["contact_phone"] ?? [] as $value){
                            $verified = isset( $value["verified"] ) && $value["verified"] === true;
                            $invalid = isset( $value["invalid"] ) && $value["invalid"] === true;
                            ?>
                            <li class="<?php echo esc_attr( $value["key"] )?>">
                                <input id="<?php echo esc_attr( $value["key"] )?>"
                                       value="<?php echo esc_attr( $value["value"] )?>"
                                       class="contact-input">
                                <?php dt_contact_details_edit( $value["key"], "phone", true ) ?>
                            </li>

                        <?php }
                    }?>
                    </ul>

                    <div class="section-subheader"><?php echo esc_html( $channel_list["email"]["label"] ) ?>
                        <button data-id="email" class="details-edit add-button">
                            <img src="<?php echo esc_html( get_template_directory_uri() . '/assets/images/small-add.svg' ) ?>"/>
                        </button>
                    </div>
                    <ul class="email details-list">
                        <?php
                        if (sizeof( $contact->fields["contact_email"] ?? [] ) === 0 ){
                            ?> <li id="no-email">No email set</li> <?php
                        }
                        foreach ($contact->fields["contact_email"] ?? [] as $field => $value){
                            $verified = isset( $value["verified"] ) && $value["verified"] === true ? "inline" :"none";
                            $invalid = isset( $value["invalid"] ) && $value["invalid"] === true ? "inline" :"none";
                            ?>
                            <li class="<?php echo esc_html( $value["key"] ) ?>">
                                <?php echo esc_html( $value["value"] );
                                dt_contact_details_status( $value["key"], $verified, $invalid ); ?>
                            </li>
                        <?php }?>
                    </ul>
                    <ul id="email-list" class="details-edit">
                        <?php
                        if ( isset( $contact->fields["contact_email"] )){
                            foreach ($contact->fields["contact_email"] ?? [] as $value){
                                $verified = isset( $value["verified"] ) && $value["verified"] === true;
                                $invalid = isset( $value["invalid"] ) && $value["invalid"] === true;
                                ?>
                                <li>
                                    <input id="<?php echo esc_attr( $value["key"] )?>" value="<?php echo esc_attr( $value["value"] ) ?>" class="contact-input">
                                    <?php dt_contact_details_edit( $value["key"], "email", true ) ?>
                                </li>
                                <?php
                            }
                        }?>
                    </ul>

                </div>
                <!-- Locations -->
                <!-- Assigned To -->
                <div class="xlarge-4 large-6 medium-6 small-12 cell">
                    <div class="section-subheader">Locations</div>
                    <ul class="locations-list">
                        <?php
                        foreach ($contact->fields["locations"] ?? [] as $value){
                            ?>
                            <li class="<?php echo esc_html( $value->ID )?>">
                                <a href="<?php echo esc_url( $value->permalink ) ?>"><?php echo esc_html( $value->post_title ) ?></a>
                                <button class="details-remove-button connection details-edit"
                                        data-field="locations" data-id="<?php echo esc_html( $value->ID ) ?>"
                                        data-name="<?php echo esc_html( $value->post_title ) ?>">
                                    Remove
                                </button>
                            </li>
                        <?php }
                        if (sizeof( $contact->fields["locations"] ) === 0){
                            echo '<li id="no-location">No location set</li>';
                        }
                        ?>
                    </ul>
                    <div class="locations details-edit">
                        <input class="typeahead" type="text" placeholder="Type to search locations">
                    </div>

                    <div class="section-subheader">Assigned to
                        <span class="assigned_to details-edit">:
                    </span> <span class="assigned_to details-edit current-assigned">:</span> </div>
                    <ul class="details-list assigned_to">
                        <li class="current-assigned">
                            <?php
                            if ( isset( $contact->fields["assigned_to"] ) ){
                                echo esc_html( $contact->fields["assigned_to"]["display"] );
                            } else {
                                echo "None Assigned";
                            }
                            ?>
                        </li>
                    </ul>
                    <div class="assigned_to details-edit">
                        <input class="typeahead" type="text" placeholder="Type to search users">
                    </div>
                </div>
                <!-- Social Media -->
                <div class="xlarge-4 large-6 medium-6 small-12 cell">
                    <div class="section-subheader"><?php echo esc_html( 'Social Media' ) ?></div>
                    <ul class='social details-list'>
                    <?php
                    $number_of_social = 0;
                    foreach ($contact->fields as $field_key => $values){
                        if ( strpos( $field_key, "contact_" ) === 0 &&
                            strpos( $field_key, "contact_phone" ) === false &&
                            strpos( $field_key, "contact_email" ) === false) {
                            $channel = explode( '_', $field_key )[1];
                            if ( isset( $channel_list[ $channel ] ) ) {
                                foreach ($values as $value) {
                                    $number_of_social++;
                                    $verified = isset( $value["verified"] ) && $value["verified"] === true ? "inline" :"none";
                                    $invalid = isset( $value["invalid"] ) && $value["invalid"] === true ? "inline" :"none";
                                    ?>
                                    <li class="<?php echo esc_html( $value['key'] )?>">
                                    <?php
                                    if ( $values && sizeof( $values ) > 0 ) {
                                        ?>
                                        <span><?php echo esc_html( $channel_list[ $channel ]["label"] )?>:</span>
                                    <?php } ?>

                                    <span class='social-text'><?php echo esc_html( $value["value"] ) ?></span>
                                    <?php dt_contact_details_status( $value["key"], $verified, $invalid ) ?>
                                    </li>
                                    <?php
                                }
                            }
                        }
                    }
                    if ($number_of_social === 0 ){
                        ?> <li id="no-social">None set</li> <?php
                    }
                    ?>
                    </ul>
                    <ul class="social details-edit">
                    <?php

                    foreach ($contact->fields as $field_key => $values){
                        if ( strpos( $field_key, "contact_" ) === 0 &&
                            strpos( $field_key, "contact_phone" ) === false &&
                            strpos( $field_key, "contact_email" ) === false) {
                            $channel = explode( '_', $field_key )[1];
                            if ( isset( $channel_list[ $channel ] ) ) {
                                foreach ($values as $value) {
                                    $verified = isset( $value["verified"] ) && $value["verified"] === true;
                                    $invalid = isset( $value["invalid"] ) && $value["invalid"] === true;
                                    ?>
                                    <li class='<?php echo esc_html( $value['key'] ) ?>'>
                                        <?php
                                        if ( $values && sizeof( $values ) > 0 ) {
                                            ?><span><?php echo esc_html( $channel_list[ $channel ]["label"] )?></span>
                                        <?php } ?>
                                        <input id='<?php echo esc_html( $value["key"] ) ?>' class='details-edit social-input' value='<?php echo esc_html( $value["value"] ) ?>'>
                                        <?php dt_contact_details_edit( $value["key"], "social", true ) ?>
                                    </li>
                                    <?php
                                }
                            }
                        }
                    }
                    ?>

                    </ul>
                    <div class="details-edit">
                        <label for="social-channels">Add another contact method</label>
                        <select id="social-channels">
                            <?php
                            foreach ($channel_list as $key => $channel){
                                if ($key != "phone" && $key != "email"){
                                    ?><option value="<?php echo esc_html( $key ) ?>"> <?php echo esc_html( $channel["label"] ) ?></option><?php
                                }
                            }
                            ?>
                        </select>
                        <div class="new-social-media">
                            <input type="text" id="new-social-media" placeholder="facebook.com/user1">
                            <button id="add-social-media" class="button small loader">
                                Add
                            </button>
                        </div>
                    </div>



                    <div class="section-subheader">People Groups</div>
                    <ul class="people_groups-list">
                        <?php
                        foreach ($contact->fields["people_groups"] ?? [] as $value){
                            ?>
                            <li class="<?php echo esc_html( $value->ID )?>">
                                <a href="<?php echo esc_url( $value->permalink ) ?>"><?php echo esc_html( $value->post_title ) ?></a>
                                <button class="details-remove-button connection details-edit"
                                        data-field="people_groups" data-id="<?php echo esc_html( $value->ID ) ?>"
                                        data-name="<?php echo esc_html( $value->post_title ) ?>">
                                    Remove
                                </button>
                            </li>
                        <?php }
                        if (sizeof( $contact->fields["people_groups"] ) === 0){
                            echo '<li id="no-people-group">No people group set</li>';
                        }
                        ?>
                    </ul>
                    <div class="people-groups details-edit">
                        <input class="typeahead" type="text" placeholder="Type to search people groups">
                    </div>
                </div>
            </div>


            <div id="show-more-content" class="grid-x grid-margin-x show-content" style="display:none;">
                <div class="xlarge-4 large-6 medium-6 small-12 cell">
                    <div class="section-subheader">Address
                        <button id="add-new-address" class="details-edit">
                            <img src="<?php echo esc_html( get_template_directory_uri() . '/assets/images/small-add.svg' ) ?>"/>
                        </button>
                    </div>
                    <ul class="address details-list">
                        <?php
                        if (sizeof( $contact->fields["address"] ?? [] ) === 0 ){
                            ?> <li id="no-address">No address set</li> <?php
                        }
                        foreach ($contact->fields["address"] ?? [] as $value){
                            $verified = isset( $value["verified"] ) && $value["verified"] === true ? "inline" :"none";
                            $invalid = isset( $value["invalid"] ) && $value["invalid"] === true ? "inline" :"none";
                            ?>
                            <li class="<?php echo esc_html( $value["key"] ) ?> address-row">
                                <div class="address-text"><?php echo esc_html( $value["value"] );?></div><?php dt_contact_details_status( $value["key"], $verified, $invalid ) ?>
                            </li>
                        <?php } ?>
                    </ul>
                    <ul id="address-list" class="details-edit">
                    <?php
                    if ( isset( $contact->fields["address"] )){
                        foreach ($contact->fields["address"] ?? [] as $value){
                            $verified = isset( $value["verified"] ) && $value["verified"] === true;
                            $invalid = isset( $value["invalid"] ) && $value["invalid"] === true;
                            ?>
                            <div class="<?php echo esc_attr( $value["key"] )?>">
                                <textarea rows="3" id="<?php echo esc_attr( $value["key"] )?>"><?php echo esc_attr( $value["value"] )?></textarea>
                                <?php dt_contact_details_edit( $value["key"], "address", true ) ?>
                            </div>
                            <hr>

                        <?php }
                    }?>
                    </ul>
                </div>

                <div class="xlarge-4 large-6 medium-6 small-12 cell">
                    <div class="section-subheader">Age:</div>
                    <ul class="details-list">
                        <li class="current-age"><?php echo esc_html( $contact->fields['age']['label'] ?? "No age set" ) ?></li>
                    </ul>
                    <select id="age" class="details-edit select-field">
                        <?php
                        foreach ( $contact_fields["age"]["default"] as $age_key => $age_value ) {
                            if ( isset( $contact->fields["age"] ) &&
                                $contact->fields["age"]["key"] === $age_key){
                                echo '<option value="'. esc_html( $age_key ) . '" selected>' . esc_html( $age_value ) . '</option>';
                            } else {
                                echo '<option value="'. esc_html( $age_key ) . '">' . esc_html( $age_value ). '</option>';

                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="xlarge-4 large-6 medium-6 small-12 cell">
                    <div class="section-subheader">Gender:</div>
                    <ul class="details-list">
                        <li class="current-gender"><?php echo esc_html( $contact->fields['gender']['label'] ?? "No gender set" ) ?></li>
                    </ul>
                    <select id="gender" class="details-edit select-field">
                        <?php
                        foreach ( $contact_fields["gender"]["default"] as $gender_key => $gender_value ) {
                            if ( isset( $contact->fields["gender"] ) &&
                                $contact->fields["gender"]["key"] === $gender_key){
                                echo '<option value="'. esc_html( $gender_key ) . '" selected>' . esc_html( $gender_value ) . '</option>';
                            } else {
                                echo '<option value="'. esc_html( $gender_key ) . '">' . esc_html( $gender_value ). '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="xlarge-4 large-6 medium-6 small-12 cell">
                    <div class="section-subheader"><?php esc_html_e( "Source" ); ?></div>
                    <ul class="details-list">
                        <li class="current-sources">
                            <?php
                            if (isset( $contact->fields["sources"] )) {
                                echo esc_html( $contact->fields["sources"]["label"] );
                            } else {
                                esc_html_e( "No source set" );
                            }
                            ?>
                        </li>
                    </ul>
                    <select id="sources" class="details-edit select-field">
                        <option value=""></option>
                        <?php
                        foreach ( $custom_lists["sources"] as $sources_key => $sources_value ) {
                            if ( isset( $contact->fields["sources"] ) &&
                                $contact->fields["sources"]["key"] === $sources_key){
                                echo '<option value="'. esc_html( $sources_key ) . '" selected>' . esc_html( $sources_value["label"] ) . '</option>';
                            } else {
                                echo '<option value="'. esc_html( $sources_key ) . '">' . esc_html( $sources_value["label"] ). '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="row show-more-button" style="text-align: center" >
                <button class="clear show-button"  href="#">Show
                    <span class="show-content show-more">more <img src="<?php echo esc_html( get_template_directory_uri() . '/assets/images/chevron_down.svg' )?>"/></span>
                    <span class="show-content" style="display:none;">less <img src="<?php echo esc_html( get_template_directory_uri() . '/assets/images/chevron_up.svg' )?>"></span>
                </button>
            </div>
        </div>
        </div>
    </section>

<?php
})();


