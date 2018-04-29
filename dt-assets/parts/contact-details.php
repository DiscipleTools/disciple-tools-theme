<?php

( function () {
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


//    <!-- Requires update block -->
    if ( isset( $contact['requires_update'] ) && $contact['requires_update']['key'] === 'yes' ) { ?>
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

    <?php if ( isset( $contact['type']['key'] ) && $contact['type']['key'] === 'user' ) { ?>
    <section class="cell accept-contact" id="contact-is-user">
        <div class="bordered-box">
            <h4><?php esc_html_e( 'This contact represents a user.', 'disciple_tools' )?></h4>
        </div>
    </section>
    <?php } ?>

    <section class="cell">
        <div class="bordered-box">
            <div class="item-details-header-row">
                <button class="float-right">
                    <i class="fi-pencil"></i>
                    <span id="open-edit"><?php esc_html_e( 'Edit', 'disciple_tools' )?></span>
                </button>
                <div class="grid-x grid-margin-x details-list">
                    <h3 class="section-header"><?php esc_html_e( 'Details', 'disciple_tools' ) ?></h3>
                </div>
            </div>

            <div class="display-fields grid-x grid-margin-x">
                <div class="xlarge-4 large-6 medium-6 small-12 cell">
                    <!--Phone-->
                    <div class="section-subheader">
                        <img src="<?php echo esc_url( get_template_directory_uri() ) . '/dt-assets/images/phone.svg' ?>">
                        <?php echo esc_html( $channel_list["phone"]["label"] ) ?>
                    </div>
                    <ul class="phone">
                    </ul>
                </div>
                <div class="xlarge-4 large-6 medium-6 small-12 cell">
                    <!--Email-->
                    <div class="section-subheader">
                        <img src="<?php echo esc_url( get_template_directory_uri() ) . '/dt-assets/images/email.svg' ?>">
                        <?php echo esc_html( $channel_list['email']['label'] ) ?>
                    </div>
                    <ul class="email">
                    </ul>
                </div>

                <!-- Social Media -->
                <div class="xlarge-4 large-6 medium-6 small-12 cell">
                    <div class="section-subheader"><?php esc_html_e( 'Social Media', 'disciple_tools' ) ?></div>
                    <ul class="social"></ul>
                </div>

                <!-- Address -->
                <div class="xlarge-4 large-6 medium-6 small-12 cell">
                    <div class="section-subheader">
                        <img src="<?php echo esc_url( get_template_directory_uri() ) . '/dt-assets/images/house.svg' ?>">
                        <?php esc_html_e( 'Address', 'disciple_tools' )?>
                    </div>
                    <ul class="address"></ul>
                </div>

                <!-- Locations -->
                <div class="xlarge-4 large-6 medium-6 small-12 cell">
                    <div class="section-subheader">
                        <img src="<?php echo esc_url( get_template_directory_uri() ) . '/dt-assets/images/location.svg' ?>">
                        <?php esc_html_e( 'Locations', 'disciple_tools' ) ?>
                    </div>
                    <ul class="locations-list"></ul>
                </div>

                <!-- People Groups -->
                <div class="xlarge-4 large-6 medium-6 small-12 cell">
                    <div class="section-subheader">
                        <img src="<?php echo esc_url( get_template_directory_uri() ) . "/dt-assets/images/people-group.svg" ?>">
                        <?php esc_html_e( 'People Groups', 'disciple_tools' )?>
                    </div>
                    <ul class="people_groups-list details-list"></ul>
                </div>


                <!-- Age -->
                <div class="xlarge-4 large-6 medium-6 small-12 cell">
                    <div class="section-subheader">
                        <img src="<?php echo esc_url( get_template_directory_uri() ) . "/dt-assets/images/contact-age.svg" ?>">
                        <?php esc_html_e( 'Age', 'disciple_tools' )?>
                    </div>
                    <ul class="details-list">
                        <li class="age">
                            <?php
                            if ( !empty( $contact['age']['label'] ) ){
                                echo esc_html( $contact['age']['label'] );
                            } else {
                                esc_html_e( 'No age set', 'disciple_tools' );
                            }
                            ?>
                        </li>
                    </ul>
                </div>

                <!-- Gender -->
                <div class="xlarge-4 large-6 medium-6 small-12 cell">
                    <div class="section-subheader">
                        <img src="<?php echo esc_url( get_template_directory_uri() ) . '/dt-assets/images/gender.svg' ?>">
                        <?php esc_html_e( 'Gender', 'disciple_tools' )?>
                    </div>
                    <ul class="details-list">
                        <li class="gender">
                            <?php
                            if ( !empty( $contact['gender']['label'] ) ){
                                echo esc_html( $contact['gender']['label'] );
                            } else {
                                esc_html_e( 'No gender set', 'disciple_tools' );
                            }
                            ?>
                    </ul>
                </div>

                <!-- Source -->
                <div class="xlarge-4 large-6 medium-6 small-12 cell">
                    <div class="section-subheader">
                        <img src="<?php echo esc_url( get_template_directory_uri() ) . '/dt-assets/images/source.svg' ?>">
                        <?php esc_html_e( 'Source' ); ?>
                    </div>
                    <ul class="sources-list <?php echo esc_html( user_can( get_current_user_id(), 'view_any_contacts' ) ? 'details-list' : '' ) ?>">
                        <?php
                        foreach ($contact['sources'] ?? [] as $value){
                            ?>
                            <li class="<?php echo esc_html( $value )?>">
                                <?php echo esc_html( $contact_fields['sources']['default'][$value] ?? $value ) ?>
                            </li>
                        <?php }
                        if ( !isset( $contact['sources'] ) || sizeof( $contact['sources'] ) === 0){
                            ?> <li id="no-source"><?php esc_html_e( "No source set", 'disciple_tools' ) ?></li><?php
                        }
                        ?>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <div class="reveal" id="contact-details-edit" data-reveal>
        <h1><?php esc_html_e( "Edit Contact", 'disciple_tools' ) ?></h1>
        <div class="display-fields">
            <div class="grid-x">
                <div class="cell section-subheader">
                    <?php esc_html_e( 'Name', 'disciple_tools' ) ?>
                </div>
                <input type="text" id="title" class="text-input" value="<?php the_title_attribute(); ?>">

            </div>

            <div class="grid-x">
                <div class="cell section-subheader">
                    <img src="<?php echo esc_url( get_template_directory_uri() ) . '/dt-assets/images/phone.svg' ?>">
                    <?php echo esc_html( $channel_list["phone"]["label"] ) ?>
                    <button data-list-class="contact_phone" class="add-button">
                        <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/small-add.svg' ) ?>"/>
                    </button>
                </div>

                <ul id="edit-contact_phone" class="cell">

                </ul>
            </div>

            <div class="grid-x">
                <div class="section-subheader cell">
                    <img src="<?php echo esc_url( get_template_directory_uri() ) . '/dt-assets/images/email.svg' ?>">
                    <?php echo esc_html( $channel_list['email']['label'] ) ?>
                    <button data-list-class="contact_email" class="add-button">
                        <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/small-add.svg' ) ?>"/>
                    </button>
                </div>
                <ul id="edit-contact_email" class="cell">

                </ul>
            </div>

            <div class="grix-x">
                <div class="section-subheader cell">
                    <img src="<?php echo esc_url( get_template_directory_uri() ) . '/dt-assets/images/house.svg' ?>">
                    <?php esc_html_e( 'Address', 'disciple_tools' )?>
                    <button id="add-new-address">
                        <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/small-add.svg' ) ?>"/>
                    </button>
                </div>
                <ul id="edit-contact_address" class="cell">
                </ul>
            </div>

            <div class="grix-x">
                <div class="section-subheader cell">
                    <?php esc_html_e( 'Social Media', 'disciple_tools' ) ?>
                </div>
                <ul id="edit-social" class="cell">

                </ul>
                <div class="cell">
                    <label for="social-channels">
                        <?php esc_html_e( 'Add another contact method', 'disciple_tools' )?>
                    </label>
                    <select id="social-channels">
                        <?php
                        foreach ($channel_list as $key => $channel) {
                            if ( $key !== 'phone' && $key !== 'email' && $key !== 'address' ) {
                                ?><option value="<?php echo esc_html( $key ); ?>"> <?php echo esc_html( $channel['label'] ) ?></option><?php
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
            </div>

            <div class="grix-x">
                <div class="section-subheader cell">

                </div>
            </div>

            <div class="grix-x">
                <div class="section-subheader cell">
                    <img src="<?php echo esc_url( get_template_directory_uri() ) . '/dt-assets/images/source.svg' ?>">
                    <?php esc_html_e( 'Source' ); ?>
                </div>
                <?php if ( user_can( get_current_user_id(), 'view_any_contacts' ) ) : ?>
                    <span id="sources-result-container" class="result-container"></span>
                    <div id="sources_t" name="form-sources" class="scrollable-typeahead">
                        <div class="typeahead__container">
                            <div class="typeahead__field">
                                            <span class="typeahead__query">
                                                <input class="js-typeahead-sources"
                                                       name="sources[query]" placeholder="<?php esc_html_e( "Search sources", 'disciple_tools' ) ?>"
                                                       autocomplete="off">
                                            </span>
                            </div>
                        </div>
                    </div>
                <?php else : ?>
                    <ul class="sources-list <?php echo esc_html( user_can( get_current_user_id(), 'view_any_contacts' ) ? 'details-list' : '' ) ?>">
                        <?php
                        foreach ($contact['sources'] ?? [] as $value){
                            ?>
                            <li class="<?php echo esc_html( $value )?>">
                                <?php echo esc_html( $contact_fields['sources']['default'][$value] ?? $value ) ?>
                            </li>
                        <?php }
                        if ( !isset( $contact['sources'] ) || sizeof( $contact['sources'] ) === 0){
                            ?> <li id="no-source"><?php esc_html_e( "No source set", 'disciple_tools' ) ?></li><?php
                        }
                        ?>
                    </ul>
                <?php endif; ?>
            </div>
            <div class="grix-x">
                <div class="section-subheader cell">
                    <img src="<?php echo esc_url( get_template_directory_uri() ) . '/dt-assets/images/gender.svg' ?>">
                    <?php esc_html_e( 'Gender', 'disciple_tools' )?>
                </div>
                <select id="gender" class="select-input">
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
            <div class="grix-x">
                <div class="section-subheader cell">
                    <img src="<?php echo esc_url( get_template_directory_uri() ) . "/dt-assets/images/contact-age.svg" ?>">
                    <?php esc_html_e( 'Age', 'disciple_tools' )?>
                </div>
                <select id="age" class="select-input">
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
            <div class="grix-x">
                <div class="section-subheader cell">
                    <img src="<?php echo esc_url( get_template_directory_uri() ) . "/dt-assets/images/people-group.svg" ?>">
                    <?php esc_html_e( 'People Groups', 'disciple_tools' )?>
                </div>
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


            <div class="grix-x">
                <div class="section-subheader cell">
                    <img src="<?php echo esc_url( get_template_directory_uri() ) . '/dt-assets/images/location.svg' ?>">
                    <?php esc_html_e( 'Locations', 'disciple_tools' ) ?>
                </div>
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
            </div>
        </div>
        <div>
            <button class="button button-cancel clear" data-close aria-label="Close reveal" type="button">
                <?php esc_html_e( 'Cancel', 'disciple_tools' )?>
            </button>
            <button class="button loader" type="button" id="save-edit-details">
                <?php esc_html_e( 'Save', 'disciple_tools' )?>
            </button>
            <button class="close-button" data-close aria-label="Close modal" type="button">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    </div>
<?php } )(); ?>
