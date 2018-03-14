<?php

$contact = Disciple_Tools_Contacts::get_contact( get_the_ID(), true );
$channel_list = Disciple_Tools_Contacts::get_channel_list();
$current_user = wp_get_current_user();
$contact_fields = Disciple_Tools_Contacts::get_contact_fields();
$custom_lists = dt_get_option( 'dt_site_custom_lists' );

function dt_contact_details_status( $id, $verified, $invalid ) { ?>
    <img id="<?php echo esc_html( $id )?>-verified" class="details-status" style="display:<?php echo esc_html( $verified )?>" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/verified.svg' )?>" />
    <img id="<?php echo esc_html( $id ) ?>-invalid" class="details-status" style="display:<?php echo esc_html( $invalid )?>" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/broken.svg' )?>" />
<?php
}

function dt_contact_details_edit( $id, $field_type, $remove = false ) {
?>
    <ul class='dropdown menu' data-click-open='true'
        data-dropdown-menu data-disable-hover='true'
        style='display:inline-block'>
        <li>
            <button class="social-details-options-button">
                <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/menu-dots.svg' ) ?>">
            </button>
            <ul class='menu'>
                <li>
                    <button class='details-status-button field-status verify'
                        data-status='valid'
                        data-field='<?php echo esc_html( $field_type ) ?>'
                        data-id='<?php echo esc_html( $id )?>'>
                        <?php esc_html_e( 'Valid', 'disciple_tools' )?>
                    </button>
                </li>
                <li>
                    <button class='details-status-button field-status invalid'
                        data-status="invalid"
                        data-field='<?php echo esc_html( $field_type ) ?>'
                        data-id='<?php echo esc_html( $id )?>'>
                        <?php esc_html_e( 'Invalid', 'disciple_tools' )?>
                    </button>
                </li>
                <li>
                    <button class='details-status-button field-status'
                        data-status="reset"
                        data-field='<?php echo esc_html( $field_type ) ?>'
                        data-id='<?php echo esc_html( $id ) ?>'>
                        <?php esc_html_e( 'Unconfirmed', 'disciple_tools' )?>
                    </button>
                </li>
                <?php if ( $remove ) { ?>
                    <li>
                        <button class='details-remove-button delete-method'
                            data-field='<?php echo esc_html( $field_type ) ?>'
                            data-id='<?php echo esc_html( $id ) ?>'>
                            <?php esc_html_e( 'Delete item', 'disciple_tools' )?>
                        <button>
                    </li>
                <?php } ?>
            </ul>
        </li>
    </ul>
<?php } ?>

<!-- Requires update block -->
<?php if ( isset( $contact['requires_update'] ) && $contact['requires_update']['key'] === 'yes' ) { ?>
<section class="cell update-needed-notification grid-margin-y">
    <div class="bordered-box">
        <h4><img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/alert-circle-exc.svg' ) ?>"/><?php esc_html_e( 'This contact needs an update', 'disciple_tools' ) ?>.</h4>
        <p><?php esc_html_e( 'It has been a while since this contact was updated. Please do so', 'disciple_tools' )?>.</p>
    </div>
</section>
<?php } ?>

<!-- Assigned to block -->
<?php
if ( isset( $contact['overall_status'] ) && $contact['overall_status']['key'] == 'assigned' &&
    isset( $contact['assigned_to'] ) && $contact['assigned_to']['id'] == $current_user->ID ) { ?>
<section class="cell accept-contact" id="accept-contact">
    <div class="bordered-box">
        <h4><?php esc_html_e( 'This contact has been assigned to you.', 'disciple_tools' )?></h4>
        <button class="accept-button button small" onclick="details_accept_contact(<?php echo get_the_ID() ?>, true)"><?php esc_html_e( 'Accept', 'disciple_tools' )?></button>
        <button class="decline-button button small" onclick="details_accept_contact(<?php echo get_the_ID() ?>, false)"><?php esc_html_e( 'Decline', 'disciple_tools' )?></button>
    </div>
</section>
<?php } ?>

<?php if ( isset( $contact['is_a_user']['key'] ) && $contact['is_a_user']['key'] === 'yes' ) { ?>
<section class="cell accept-contact" id="contact-is-user">
    <div class="bordered-box">
        <h4><?php esc_html_e( 'This contact represents a user.', 'disciple_tools' )?></h4>
    </div>
</section>
<?php } ?>

<section class="cell">
    <div class="bordered-box">
        <div class="item-details-header-row">
            <button class="float-right" id="edit-details">
                <i class="fi-pencil"></i>
                <span id="edit-button-label"><?php esc_html_e( 'Edit', 'disciple_tools' )?></span>
            </button>
            <h3 class="section-header"><?php esc_html_e( 'Details', 'disciple_tools' ) ?></h3>

            <div class="grid-x grid-margin-x details-edit">
                <div class="medium-6 cell">
                    <label for="title" class="section-subheader"><?php esc_html_e('Name', 'disciple_tools') ?></label>
                    <input type="text" id="title" class="text-field details-edit" value="<?php the_title_attribute(); ?>">
                </div>
                <div class="medium-6 cell reason-field">
                    <?php
                    $status = $contact['overall_status']['key'] ?? '';
                    $hasStatus = isset( $contact_fields["reason_$status"]['name'] );
                    ?>
                    <div class="section-subheader"><?php echo $hasStatus ? esc_html( $contact_fields["reason_$status"]['name'] ) : '' ?></div>
                    <select class="status-reason" style="<?php echo $hasStatus ? '' : 'display:none;' ?>" data-field="<?php echo $hasStatus ? esc_html( "reason_$status", 'disciple_tools' ) : '' ?>">
                    <?php
                    if ( $hasStatus ) {
                        foreach ( $contact_fields["reason_$status"]['default'] as $reason_key => $reason_label ) { ?>
                            <option value="<?php echo esc_attr( $reason_key ) ?>"
                                <?php echo ( $contact["reason_$status"]['key'] ?? '' ) === $reason_key ? 'selected' : ''; ?>>
                                <?php echo esc_html( $reason_label, 'disciple_tools' )?>
                            </option>
                        <?php
                        }
                    }
                    ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="display-fields grid-x grid-margin-x">

            <div class="xlarge-4 large-6 medium-6 small-12 cell">
                <!--Phone-->
                <div class="section-subheader">
                    <img src="<?php echo esc_url( get_template_directory_uri() ) . '/dt-assets/images/phone.svg' ?>">
                    <?php echo esc_html( $channel_list["phone"]["label"] ) ?>
                    <button data-list-class="phone" class="details-edit add-button">
                        <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/small-add.svg' ) ?>"/>
                    </button>
                </div>

                <ul class="phone">
                <?php if ( count( $contact['contact_phone'] ?? [] ) === 0 ) { ?>
                    <li id="no-phone"><?php esc_html_e( 'No phone set', 'disciple_tools' ) ?></li>
                <?php
                    }

                    foreach ($contact['contact_phone'] ?? [] as $field => $value) {
                        $verified = isset( $value['verified'] ) && $value['verified'] === true ? 'inline' : 'none';
                        $invalid = isset( $value['invalid'] ) && $value['invalid'] === true ? 'inline' : 'none';
                        ?>
                        <li class="details-list <?php echo esc_html( $value['key'] ) ?>">
                            <span class="details-text"><?php echo esc_html( $value['value'] ); ?></span>
                            <?php dt_contact_details_status( $value['key'], $verified, $invalid );  ?>
                        </li>
                        <li class="details-edit has-options <?php echo esc_attr($value['key'], 'disciple_tools') ?>">
                            <input type="text" id="<?php echo esc_attr($value['key'], 'disciple_tools') ?>"
                                value="<?php echo esc_attr($value['value'], 'disciple_tools') ?>"
                                class="contact-input">
                            <?php dt_contact_details_edit($value['key'], 'phone', true) ?>
                        </li>
                    <?php } ?>
                </ul>

                <!--Email-->
                <div class="section-subheader">
                    <img src="<?php echo esc_url( get_template_directory_uri() ) . '/dt-assets/images/email.svg' ?>">
                    <?php echo esc_html( $channel_list['email']['label'] ) ?>
                    <button data-list-class="email" class="details-edit add-button">
                        <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/small-add.svg' ) ?>"/>
                    </button>
                </div>

                <ul class="email">
                <?php if ( count( $contact['contact_email'] ?? [] ) === 0 ) { ?>
                    <li id="no-email"><?php esc_html_e( 'No email set', 'disciple_tools' ) ?></li>
                <?php
                    }

                    foreach ( $contact['contact_email'] ?? [] as $field => $value) {
                        $verified = isset( $value['verified'] ) && $value['verified'] === true ? 'inline' : 'none';
                        $invalid = isset( $value['invalid'] ) && $value['invalid'] === true ? 'inline' :' none';
                        ?>
                        <li class="details-list <?php echo esc_html( $value['key'] ) ?>">
                            <?php echo esc_html( $value['value'] );
                            dt_contact_details_status( $value['key'], $verified, $invalid ); ?>
                        </li>
                        <li class="details-edit has-options">
                            <input type="email" id="<?php echo esc_attr($value['key'], 'disciple_tools') ?>" value="<?php echo esc_attr($value['value'], 'disciple_tools') ?>" class="contact-input">
                            <?php dt_contact_details_edit($value['key'], esc_html__('email', 'disciple_tools'), true) ?>
                        </li>
                    <?php } ?>
                </ul>
            </div>

            <div class="xlarge-4 large-6 medium-6 small-12 cell">
                <!-- Locations -->
                <div class="section-subheader">
                    <img src="<?php echo esc_url( get_template_directory_uri() ) . '/dt-assets/images/location.svg' ?>">
                    <?php esc_html_e( 'Locations', 'disciple_tools' ) ?>
                </div>
                <ul class="locations-list">
                <?php foreach ( $contact['locations'] ?? [] as $value ) { ?>
                    <li class="<?php echo esc_html( $value->ID )?>">
                        <?php echo esc_html( $value->post_title ) ?>
                    </li>
                <?php } ?>
                <?php if ( count( $contact["locations"] ) === 0 ) { ?>
                    <li id="no-location"><?php esc_html_e( 'No location set', 'disciple_tools' ) ?></li>
                <?php } ?>
                </ul>
                <div class="locations">
                    <var id="locations-result-container" class="result-container"></var>
                    <div id="locations_t" name="form-locations" class="scrollable-typeahead">
                        <div class="typeahead__container">
                            <div class="typeahead__field">
                                <span class="typeahead__query">
                                    <input class="js-typeahead-locations"
                                        name="locations[query]" placeholder="<?php esc_html_e( "Search Locations", 'disciple_tools' ) ?>"
                                        autocomplete="off">
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Assigned To -->
                <div class="section-subheader">
                    <img src="<?php echo esc_url( get_template_directory_uri() ) . '/dt-assets/images/assigned-to.svg' ?>">
                    <?php esc_html_e( 'Assigned to', 'disciple_tools' )?>
                    <span class="assigned_to details-edit">:</span>
                    <span class="assigned_to details-edit current-assigned"></span>
                </div>
                <ul class="details-list assigned_to">
                    <li class="current-assigned">
                        <?php
                        if ( isset( $contact["assigned_to"] ) ){
                            echo esc_html( $contact["assigned_to"]["display"] );
                        } else {
                            esc_html_e( 'None Assigned', 'disciple_tools' );
                        }
                        ?>
                    </li>
                </ul>

                <div class="assigned_to details">
                    <var id="assigned_to-result-container" class="result-container assigned_to-result-container"></var>
                    <div id="assigned_to_t" name="form-assigned_to">
                        <div class="typeahead__container">
                            <div class="typeahead__field">
                                <span class="typeahead__query">
                                    <input class="js-typeahead-assigned_to input-height"
                                            name="assigned_to[query]" placeholder="<?php esc_html_e( "Search Users", 'disciple_tools' ) ?>"
                                            autocomplete="off">
                                </span>
                                <span class="typeahead__button">
                                    <button type="button" class="search_assigned_to typeahead__image_button input-height" data-id="assigned_to_t">
                                        <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/chevron_down.svg' ) ?>"/>
                                    </button>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="section-subheader">
                    <?php esc_html_e( 'Sub-assigned to', 'disciple_tools' )?>
                </div>
                <ul class="details-list subassigned-list">
                    <?php
                    foreach ($contact["subassigned"] ?? [] as $value){
                        ?>
                        <li class="<?php echo esc_html( $value->ID )?>">
                            <a href="<?php echo esc_url( $value->permalink ) ?>"><?php echo esc_html( $value->post_title ) ?></a>
                        </li>
                    <?php }
                    if (sizeof( $contact["subassigned"] ) === 0){
                        ?> <li id="no-subassigned"><?php esc_html_e( "No subassigned set", 'disciple_tools' ) ?></li>
                    <?php
                    }
                    ?>
                </ul>

                <div class="subassigned details">
                    <var id="subassigned-result-container" class="result-container subassigned-result-container"></var>
                    <div id="subassigned_t" name="form-subassigned">
                        <div class="typeahead__container">
                            <div class="typeahead__field">
                                <span class="typeahead__query">
                                    <input class="js-typeahead-subassigned input-height"
                                        name="subassigned[query]" placeholder="<?php esc_html_e( "Search Contacts", 'disciple_tools' ) ?>"
                                        autocomplete="off">
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Social Media -->
            <div class="xlarge-4 large-6 medium-6 small-12 cell">
                <div class="section-subheader"><?php esc_html_e( 'Social Media', 'disciple_tools' ) ?></div>
                <ul class="social">
                <?php
                // Filter only the social contact items that exist in $channel_list
                $socialContacts = array_filter( $contact, function ($value, $key) use ($channel_list) {
                    return strpos( $key, 'contact_' ) === 0 &&
                        array_search( $key, ['contact_address', 'contact_phone', 'contact_email'] ) === false &&
                        isset( $channel_list[ explode( '_', $key )[1] ] );
                }, ARRAY_FILTER_USE_BOTH );

                foreach ($socialContacts as $key => $values) {
                    $channel = explode( '_', $key )[1];

                    foreach ($values as $value) {
                        $verified = isset( $value['verified'] ) && $value['verified'] === true ? 'inline' : 'none';
                        $invalid = isset( $value['invalid'] ) && $value['invalid'] === true ? 'inline' : 'none'; ?>
                        <li class="details-list <?php esc_html_e( $value['key'] ) ?>">
                            <?php
                            if ( file_exists( get_template_directory() . "/dt-assets/images/$channel.svg" ) ) { ?>
                                <img src="<?php echo esc_url( get_template_directory_uri() ) . "/dt-assets/images/$channel.svg" ?>">
                            <?php } else { ?>
                                <span><?php esc_html_e( $channel_list[$channel]['label'] )?>:</span>
                            <?php } ?>

                            <span class="social-text"><?php esc_html_e( $value['value'] ) ?></span>
                            <?php dt_contact_details_status( $value['key'], $verified, $invalid ) ?>
                        </li>
                    <?php if ( $values && sizeof( $values ) > 0 ) { ?>
                        <li class="details-edit"><?php esc_html_e( $channel_list[$channel]['label'] )?></li>
                    <?php } ?>
                        <li class="details-edit has-options <?php esc_html_e( $value['key'] ) ?>">
                            <input type="text" id="<?php esc_html_e( $value['key'] ) ?>" class="social-input" value="<?php echo esc_html( $value['value'] ) ?>">
                            <?php dt_contact_details_edit( $value['key'], $channel, true ) ?>
                        </li>
                <?php
                    }
                }

                if ( count($socialContacts) === 0 ) { ?>
                    <li id="no-social"><?php esc_html_e( 'None set', 'disciple_tools' )?></li>
                <?php } ?>
                </ul>

                <div class="details-edit">
                    <label for="social-channels">
                        <?php esc_html_e( 'Add another contact method', 'disciple_tools' )?>
                    </label>
                    <select id="social-channels">
                        <?php
                        foreach ($channel_list as $key => $channel) {
                            if ($key != "phone" && $key != "email" && $key != "address"){
                                ?><option value="<?php esc_html_e( $key ) ?>"> <?php esc_html_e( $channel["label"] ) ?></option><?php
                            }
                        }
                        ?>
                    </select>
                    <div class="new-social-media">
                        <input type="text" id="new-social-media" placeholder="facebook.com/user1">
                        <button id="add-social-media" class="button small loader">
                            <?php esc_html_e( 'Add', 'disciple_tools' ) ?>
                        </button>
                    </div>
                </div>

                <div class="section-subheader">
                    <img src="<?php echo esc_url( get_template_directory_uri() ) . "/dt-assets/images/people-group.svg" ?>">
                    <?php esc_html_e( 'People Groups', 'disciple_tools' )?>
                </div>
                <ul class="people_groups-list details-list">
                    <?php
                    foreach ($contact["people_groups"] ?? [] as $value){
                        ?>
                        <li class="<?php echo esc_html( $value->ID )?>">
                            <?php echo esc_html( $value->post_title ) ?>
                        </li>
                    <?php }
                    if ( count( $contact["people_groups"] ) === 0 ) {
                        ?> <li id="no-people-group"><?php esc_html_e( "No people group set", 'disciple_tools' ) ?></li><?php
                    }
                    ?>
                </ul>
                <div class="people_groups">
                    <var id="people_groups-result-container" class="result-container"></var>
                    <div id="people_groups_t" name="form-people_groups" class="scrollable-typeahead">
                        <div class="typeahead__container">
                            <div class="typeahead__field">
                                <span class="typeahead__query">
                                    <input class="js-typeahead-people_groups"
                                        name="people_groups[query]" placeholder="<?php esc_html_e( "Search People_groups", 'disciple_tools' ) ?>"
                                        autocomplete="off">
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div id="show-more-content" class="grid-x grid-margin-x show-content">
            <!-- Address -->
            <div class="xlarge-4 large-6 medium-6 small-12 cell">
                <div class="section-subheader">
                    <img src="<?php echo esc_url( get_template_directory_uri() ) . '/dt-assets/images/house.svg' ?>">
                    <?php esc_html_e( 'Address', 'disciple_tools' )?>
                    <button id="add-new-address" class="details-edit">
                        <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/small-add.svg' ) ?>"/>
                    </button>
                </div>
                <ul class="address">
                <?php if ( count( $contact['contact_address'] ?? [] ) === 0 ) { ?>
                    <li id="no-address"><?php esc_html_e( 'No address set', 'disciple_tools' ) ?></li>
                <?php
                    }
                    foreach ( $contact['contact_address'] ?? [] as $value ) {
                        $verified = isset( $value['verified'] ) && $value['verified'] === true ? 'inline' : 'none';
                        $invalid = isset( $value['invalid'] ) && $value['invalid'] === true ? 'inline' : 'none';
                        ?>
                        <li class="details-list <?php echo esc_html( $value['key'] ) ?> address-row">
                            <div class="address-text"><?php echo esc_html( $value['value'] );?></div><?php dt_contact_details_status( $value["key"], $verified, $invalid ) ?>
                        </li>
                        <li class="details-edit has-options <?php echo esc_attr($value['key'], 'disciple_tools') ?>">
                            <textarea rows="3" id="<?php echo esc_attr($value['key'], 'disciple_tools') ?>">
                                <?php echo esc_attr($value['value'], 'disciple_tools') ?>
                            </textarea>
                            <?php dt_contact_details_edit($value['key'], 'address', true) ?>
                        </li>
                    <?php } ?>
                </ul>
            </div>

            <div class="xlarge-4 large-6 medium-6 small-12 cell">
                <div class="section-subheader">
                    <img src="<?php echo esc_url( get_template_directory_uri() ) . "/dt-assets/images/contact-age.svg" ?>">
                    <?php esc_html_e( 'Age', 'disciple_tools' )?>
                </div>
                <ul class="details-list">
                    <li class="current-age">
                        <?php
                        if ( isset( $contact['age']['label'] ) ){
                            echo esc_html( $contact['age']['label'] );
                        } else {
                            esc_html_e( 'No age set', 'disciple_tools' );
                        }
                        ?>
                    </li>
                </ul>
                <select id="age" class="details-edit select-field">
                    <?php
                    foreach ( $contact_fields["age"]["default"] as $age_key => $age_value ) {
                        if ( isset( $contact["age"] ) &&
                            $contact["age"]["key"] === $age_key){
                            echo '<option value="'. esc_html( $age_key ) . '" selected>' . esc_html( $age_value ) . '</option>';
                        } else {
                            echo '<option value="'. esc_html( $age_key ) . '">' . esc_html( $age_value ). '</option>';
                        }
                    }
                    ?>
                </select>
            </div>
            <div class="xlarge-4 large-6 medium-6 small-12 cell">
                <div class="section-subheader">
                    <img src="<?php echo esc_url( get_template_directory_uri() ) . '/dt-assets/images/gender.svg' ?>">
                    <?php esc_html_e( 'Gender', 'disciple_tools' )?>
                </div>
                <ul class="details-list">
                    <li class="current-gender">
                        <?php
                        if ( isset( $contact['gender']['label'] ) ){
                            echo esc_html( $contact['gender']['label'] );
                        } else {
                            esc_html_e( 'No gender set', 'disciple_tools' );
                        }
                        ?>
                </ul>
                <select id="gender" class="details-edit select-field">
                    <?php
                    foreach ( $contact_fields['gender']['default'] as $gender_key => $gender_value ) {
                        if ( isset( $contact['gender'] ) &&
                            $contact['gender']['key'] === $gender_key){
                            echo '<option value="'. esc_html( $gender_key ) . '" selected>' . esc_html( $gender_value ) . '</option>';
                        } else {
                            echo '<option value="'. esc_html( $gender_key ) . '">' . esc_html( $gender_value ). '</option>';
                        }
                    }
                    ?>
                </select>
            </div>
            <div class="xlarge-4 large-6 medium-6 small-12 cell">
                <div class="section-subheader">
                    <img src="<?php echo esc_url( get_template_directory_uri() ) . '/dt-assets/images/source.svg' ?>">
                    <?php esc_html_e( 'Source' ); ?>
                </div>
                <ul class="details-list">
                    <li class="current-sources">
                        <?php
                        if (isset( $contact['sources'] )) {
                            echo esc_html( $contact['sources']['label'] );
                        } else {
                            esc_html_e( 'No source set' );
                        }
                        ?>
                    </li>
                </ul>
                <select id="sources" class="details-edit select-field">
                    <option value=""></option>
                    <?php
                    foreach ( $custom_lists['sources'] as $sources_key => $sources_value ) {
                        if ( isset( $contact['sources'] ) &&
                            $contact['sources']['key'] === $sources_key){
                            echo '<option value="'. esc_html( $sources_key ) . '" selected>' . esc_html( $sources_value['label'] ) . '</option>';
                        } else {
                            echo '<option value="'. esc_html( $sources_key ) . '">' . esc_html( $sources_value['label'] ). '</option>';
                        }
                    }
                    ?>
                </select>
            </div>
        </div>

        <div class="row show-more-button">
            <button class="clear show-button">
                <?php esc_html_e( 'Show', 'disciple_tools' )?>
                <span class="show-more"><?php esc_html_e( 'more', 'disciple_tools' )?> <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/chevron_down.svg' )?>"/></span>
                <span class="show-less"><?php esc_html_e( 'less', 'disciple_tools' )?> <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/chevron_up.svg' )?>"></span>
            </button>
        </div>
    </div>
</section>
