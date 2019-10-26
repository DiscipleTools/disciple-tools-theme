<?php
declare( strict_types=1 );

if ( ! current_user_can( 'access_contacts' ) ) {
    wp_safe_redirect( '/settings' );
}

( function () {
    $contact = Disciple_Tools_Contacts::get_contact( get_the_ID(), true, true );
    $contact_fields = Disciple_Tools_Contacts::get_contact_fields();

    if (isset( $_POST['unsure_all'] ) && isset( $_POST['dt_contact_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['dt_contact_nonce'] ) ) ) {
        if (isset( $_POST['id'] ) ) {
            $id = (int) $_POST['id'];
            Disciple_Tools_Contacts::unsure_all( $id );
        }
        header( "location: " . site_url( '/contacts/' . get_the_ID() ) );
    }
    if (isset( $_POST['dismiss_all'] ) && isset( $_POST['dt_contact_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['dt_contact_nonce'] ) ) ) {
        if (isset( $_POST['id'] ) ) {
            $id = (int) $_POST['id'];
            Disciple_Tools_Contacts::dismiss_all( $id );
        }
        header( "location: " . site_url( '/contacts/' . get_the_ID() ) );
    }
    if (isset( $_POST['dismiss'] ) && isset( $_POST['dt_contact_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['dt_contact_nonce'] ) ) ) {
        if (isset( $_POST['currentId'], $_POST['id'] ) ) {
            $current_id = (int) $_POST['currentId'];
            $id = (int) $_POST['id'];
            ( new Disciple_Tools_Contacts() )->dismiss_duplicate( $current_id, $id );
            header( "location: " . site_url( '/contacts/' . $current_id ) );
        }
    }
    if (isset( $_POST['unsure'] ) && isset( $_POST['dt_contact_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['dt_contact_nonce'] ) ) ) {
        if (isset( $_POST['currentId'], $_POST['id'] ) ) {
            $current_id = (int) $_POST['currentId'];
            $id = (int) $_POST['id'];
            ( new Disciple_Tools_Contacts() )->unsure_duplicate( $current_id, $id );
            header( "location: " . site_url( '/contacts/' . $current_id ) );
        }
    }

    if (isset( $_POST['merge-submit'] ) && isset( $_POST['dt_contact_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['dt_contact_nonce'] ) )){
        if (isset( $_POST['currentid'], $_POST['duplicateId'] ) ) {
            $contact_id = (int) sanitize_text_field( wp_unslash( $_POST['currentid'] ) );
            $dupe_id = (int) $_POST['duplicateId'];
            $phones = isset( $_POST['phone'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['phone'] ) ) : array();
            $emails = isset( $_POST['email'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['email'] ) ) : array();
            $addresses = isset( $_POST['address'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['address'] ) ) : array();
            $master = isset( $_POST['master-record'] ) ? sanitize_text_field( wp_unslash( $_POST['master-record'] ) ) : null;

            $master_id = ( $master === 'contact1' ) ? $contact_id : $dupe_id;
            $non_master_id = ( $master_id === $contact_id ) ? $dupe_id : $contact_id;
            $contact = Disciple_Tools_Contacts::get_contact( $master_id, true );
            $non_master = Disciple_Tools_Contacts::get_contact( $non_master_id, true );

            $current = array(
                'contact_phone' => array(),
                'contact_email' => array(),
                'contact_address' => array(),
                // 'contact_facebook' => array()
            );

            foreach ( $contact as $key => $fields ) {
                if ( strpos( $key, "contact_" ) === 0 ) {
                    $split = explode( "_", $key );
                    if ( !isset( $split[1] ) ) {
                        continue;
                    }
                    $new_key = $split[0] . "_" . $split[1];
                    foreach ( $contact[ $new_key ] ?? array() as $values ) {
                        $current[ $new_key ][ $values['key'] ] = $values['value'];
                    }
                }
            }

            $update = array(
                'contact_phone' => array( 'values' => array() ),
                'contact_email' => array( 'values' => array() ),
                'contact_address' => array( 'values' => array() ),
                // 'contact_facebook' => array( 'values' => array() )
            );

            $ignore_keys = array();

            foreach ($phones as $phone) {
                $index = array_search( $phone, $current['contact_phone'] );
                if ($index !== false) { $ignore_keys[] = $index;
                    continue; }
                array_push( $update['contact_phone']['values'], [ 'value' => $phone ] );
            }
            foreach ($emails as $email) {
                $index = array_search( $email, $current['contact_email'] );
                if ($index !== false) { $ignore_keys[] = $index;
                    continue; }
                array_push( $update['contact_email']['values'], [ 'value' => $email ] );
            }
            foreach ($addresses as $address) {
                $index = array_search( $address, $current['contact_address'] );
                if ($index !== false) { $ignore_keys[] = $index;
                    continue; }
                array_push( $update['contact_address']['values'], [ 'value' => $address ] );
            }

            /*
                Merge social media + other contact data
            */
            foreach ( $non_master as $key => $fields ) {
                if ( isset( $contact_fields[$key] ) && $contact_fields[$key]["type"] === "multi_select" ){
                    $update[$key]["values"] = [];
                    foreach ( $fields as $field_value ){
                        $update[$key]["values"][] = [ "value" => $field_value ];
                    }
                }
                if ( isset( $contact_fields[ $key ] ) && $contact_fields[ $key ]["type"] === "key_select" && ( !isset( $contact[ $key ] ) || $key === "none" || $key === "" ) ) {
                    $update[$key] = $fields["key"];
                }
                if ( isset( $contact_fields[$key] ) && $contact_fields[$key]["type"] === "text" && ( !isset( $contact[$key] ) || empty( $contact[$key] ) )){
                    $update[$key] = $fields;
                }
                if ( isset( $contact_fields[$key] ) && $contact_fields[$key]["type"] === "number" && ( !isset( $contact[$key] ) || empty( $contact[$key] ) )){
                    $update[$key] = $fields;
                }
                if ( isset( $contact_fields[$key] ) && $contact_fields[$key]["type"] === "date" && ( !isset( $contact[$key] ) || empty( $contact[$key]["timestamp"] ) )){
                    $update[$key] = $fields["timestamp"] ?? "";
                }
                if ( isset( $contact_fields[$key] ) && $contact_fields[$key]["type"] === "array" && ( !isset( $contact[$key] ) || empty( $contact[$key] ) )){
                    if ( $key != "duplicate_data" ){
                        $update[$key] = $fields;
                    }
                }

                if ( strpos( $key, "contact_" ) === 0 ) {
                    $split = explode( "_", $key );
                    if ( !isset( $split[1] ) ) {
                        continue;
                    }
                    $new_key = $split[0] . "_" . $split[1];
                    if ( in_array( $new_key, array_keys( $update ) ) ) {
                        continue;
                    }
                    $update[ $new_key ] = array(
                        'values' => array()
                    );
                    foreach ( $non_master[ $new_key ] ?? array() as $values ) {
                        $index = array_search( $values['value'], $current[ $new_key ] ?? array() );
                        if ( $index !== false ) {
                            $ignore_keys[] = $index;
                            continue;
                        }
                        array_push( $update[ $new_key ]['values'], array(
                            'value' => $values['value']
                        ) );
                    }
                }
            }

            $delete_fields = array();
            if ($update['contact_phone']['values']) { $delete_fields[] = 'contact_phone'; }
            if ($update['contact_email']['values']) { $delete_fields[] = 'contact_email'; }
            if ($update['contact_address']['values']) { $delete_fields[] = 'contact_address'; }

            if ( !empty( $delete_fields )) {
                Disciple_Tools_Contacts::remove_fields( $master_id, $delete_fields, $ignore_keys );
            }

//            @todo return error if update fails
            Disciple_Tools_Contacts::update_contact( $master_id, $update, true );
            Disciple_Tools_Contacts::merge_p2p( $master_id, $non_master_id );
            Disciple_Tools_Contacts::copy_comments( $master_id, $non_master_id );
            ( new Disciple_Tools_Contacts() )->recheck_duplicates( $master_id );
            ( new Disciple_Tools_Contacts() )->dismiss_duplicate( $master_id, $non_master_id );
            ( new Disciple_Tools_Contacts() )->dismiss_duplicate( $non_master_id, $master_id );
            Disciple_Tools_Contacts::close_duplicate_contact( $non_master_id, $master_id );

            do_action( "dt_contact_merged", $master_id, $non_master_id );
        }
        header( "location: " . site_url( '/contacts/' .get_the_ID() ) );
        exit;
    }

    if ( !Disciple_Tools_Contacts::can_view( 'contacts', get_the_ID() )) {
        get_template_part( "403" );
        die();
    }
    Disciple_Tools_Notifications::process_new_notifications( get_the_ID() ); // removes new notifications for this post


    get_header(); ?>

    <?php
    $current_user_id = get_current_user_id();
    $following = DT_Posts::get_users_following_post( "contacts", get_the_ID() );
    $dispatcher_actions = [];
    if ( current_user_can( "create_users" )){
        $dispatcher_actions[] = "make_user_from_contact";
        $dispatcher_actions[] = "link_to_user";
    }
    if ( current_user_can( "view_any_contacts" )){
        $dispatcher_actions[] = "merge_with_contact";
    }
    dt_print_details_bar(
        true,
        true,
        current_user_can( "assign_any_contacts" ),
        isset( $contact["requires_update"] ) && $contact["requires_update"] === true,
        in_array( $current_user_id, $following ),
        isset( $contact["assigned_to"]["id"] ) ? $contact["assigned_to"]["id"] == $current_user_id : false,
        $dispatcher_actions
    ); ?>

<!--    <div id="errors"></div>-->

    <div id="content">
        <span id="contact-id" style="display: none"><?php echo get_the_ID()?></span>
        <span id="post-id" style="display: none"><?php echo get_the_ID()?></span>
        <span id="post-type" style="display: none">contact</span>

        <div id="inner-content" class="grid-x grid-margin-x grid-margin-y">


            <section class="hide-for-large small-12 cell">
                <div class="bordered-box">
                    <?php get_template_part( 'dt-assets/parts/contact', 'quick-buttons' ); ?>

                    <div style="text-align: center">
                        <a class="button small" href="#comment-activity-section" style="margin-bottom: 0">
                            <?php esc_html_e( 'View Comments', 'disciple_tools' ) ?>
                        </a>
                    </div>
                </div>
            </section>
            <main id="main" class="xlarge-7 large-7 medium-12 small-12 cell" role="main" style="padding:0">

              <div class="cell grid-y grid-margin-y" style="display: block">
                <?php
                if ( current_user_can( "view_any_contacts" ) ){
                    $duplicate_post_meta = get_post_meta( get_the_Id(), 'duplicate_data' );
                    $duplicates = false;
                    foreach ($duplicate_post_meta[0] ?? [] as $key => $array) {
                        if ($key === 'override') { continue; }
                        if ( !empty( $array )) {
                            $duplicates = true;
                        }
                    }
                    if ($duplicates){
                        ?>
                    <section id="duplicates" class="small-12 grid-y grid-margin-y cell">
                        <div class="bordered-box detail-notification-box" style="background-color:#ff9800">
                            <h4 class="section-header" style="color:white;"><?php esc_html_e( "This contact has possible duplicates.", 'disciple_tools' ) ?></h4>
                           <?php get_template_part( 'dt-assets/parts/merge', 'details' ); ?>
                            <button type="button" id="merge-dupe-modal" data-open="merge-dupe-modal" class="button">
                              <?php esc_html_e( "Go to duplicates", 'disciple_tools' ) ?>
                            </button>
                        </div>
                    </section>
                    <?php }
                }
                ?>
                    <div id="contact-details" class="small-12 cell grid-margin-y">
                        <?php get_template_part( 'dt-assets/parts/contact', 'details' ); ?>
                    </div>
                    <div class="cell small-12">
                        <div class="grid-x grid-margin-x grid-margin-y grid">
                            <section id="relationships" class="xlarge-6 large-12 medium-6 cell grid-item">
            <!--                    <div class="bordered-box last-typeahead-in-section">-->
                                <div class="bordered-box"><h3 class="section-header"><?php esc_html_e( "Connections", 'disciple_tools' ) ?></h3>
                                    <div class="section-subheader"><?php esc_html_e( "Groups", 'disciple_tools' ) ?></div>
                                    <var id="groups-result-container" class="result-container"></var>
                                    <div id="groups_t" name="form-groups" class="scrollable-typeahead typeahead-margin-when-active">
                                        <div class="typeahead__container">
                                            <div class="typeahead__field">
                                                <span class="typeahead__query">
                                                    <input class="js-typeahead-groups input-height"
                                                           name="groups[query]" placeholder="<?php esc_html_e( "Search groups", 'disciple_tools' ) ?>"
                                                           autocomplete="off">
                                                </span>
                                                <span class="typeahead__button">
                                                    <button type="button" data-open="create-group-modal" class="create-new-group typeahead__image_button input-height">
                                                        <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/add-group.svg' ) ?>"/>
                                                    </button>
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <?php
                                    $connections = [
                                        "relation" => esc_html__( "Connection or Relation", 'disciple_tools' ),
                                        "baptized_by" => esc_html__( "Baptized By", 'disciple_tools' ),
                                        "baptized" => esc_html__( "Baptized", 'disciple_tools' ),
                                        "coached_by" => esc_html__( "Coached By", 'disciple_tools' ),
                                        "coaching" => esc_html__( "Coaching", 'disciple_tools' )
                                    ];
                                    foreach ( $connections as $connection => $connection_label ) {
                                        ?>
                                        <div id="<?php echo esc_attr( $connection . '_connection' ) ?>">
                                            <div class="section-subheader"><?php echo esc_html( $connection_label ) ?></div>
                                            <var id="<?php echo esc_html( $connection ) ?>-result-container" class="result-container"></var>
                                            <div id="<?php echo esc_html( $connection ) ?>_t" name="form-<?php echo esc_html( $connection ) ?>" class="scrollable-typeahead typeahead-margin-when-active">
                                                <div class="typeahead__container">
                                                    <div class="typeahead__field">
                                                        <span class="typeahead__query">
                                                            <input class="js-typeahead-<?php echo esc_html( $connection ) ?>"
                                                                   name="<?php echo esc_html( $connection ) ?>[query]" placeholder="<?php esc_html_e( "Search multipliers and contacts", 'disciple_tools' ) ?>"
                                                                   autocomplete="off">
                                                        </span>
                <!--                                        <span class="typeahead__button">-->
                <!--                                            <button>-->
                <!--                                                <i class="typeahead__search-icon"></i>-->
                <!--                                            </button>-->
                <!--                                        </span>-->
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                </div>
                            </section>

                            <section id="faith" class="xlarge-6 large-12 medium-6 cell grid-item">
                                <div class="bordered-box">
                                    <label class="section-header"><?php esc_html_e( 'Progress', 'disciple_tools' )?>
            <!--                            <button class="help-button float-right" data-section="contact-progress-help-text">-->
            <!--                                <img class="help-icon" src="--><?php //echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?><!--"/>-->
            <!--                            </button>-->
                                    </label>
                                    <div class="section-subheader">
                                        <?php echo esc_html( $contact_fields["seeker_path"]["name"] )?>
                                        <button class="help-button" data-section="seeker-path-help-text">
                                            <img class="help-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>"/>
                                        </button>
                                    </div>

                                    <select class="select-field" id="seeker_path" style="margin-bottom: 0">
                                    <?php

                                    foreach ($contact_fields["seeker_path"]["default"] as $key => $option){
                                        $value = $option["label"] ?? "";
                                        if ( $contact["seeker_path"]["key"] === $key ) {
                                            ?>
                                            <option value="<?php echo esc_html( $key ) ?>" selected><?php echo esc_html( $value ); ?></option>
                                        <?php } else { ?>
                                            <option value="<?php echo esc_html( $key ) ?>"><?php echo esc_html( $value ); ?></option>
                                        <?php }
                                    }
                                    $keys = array_keys( $contact_fields["seeker_path"]["default"] );
                                    $path_index = array_search( $contact["seeker_path"]["key"], $keys ) ?? 0;
                                    $percentage = $path_index / ( sizeof( $keys ) -1 ) *100
                                    ?>
                                    </select>
                                    <div class="progress" role="progressbar" tabindex="0" aria-valuenow="<?php echo 4 ?>" aria-valuemin="0" aria-valuetext="50 percent" aria-valuemax="100">
                                        <div id="seeker-progress" class="progress-meter" style="width: <?php echo esc_html( $percentage ) ?>%"></div>
                                    </div>

                                    <div class="section-subheader">
                                        <?php echo esc_html( $contact_fields["milestones"]["name"] )?>
                                        <button class="help-button" data-section="faith-milestones-help-text">
                                            <img class="help-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>"/>
                                        </button>
                                    </div>
                                    <div class="small button-group" style="display: inline-block">
                                        <?php foreach ( $contact_fields["milestones"]["default"] as $option_key => $option_value ): ?>
                                            <?php
                                                $class = ( in_array( $option_key, $contact["milestones"] ?? [] ) ) ?
                                                    "selected-select-button" : "empty-select-button"; ?>
                                                <button id="<?php echo esc_html( $option_key ) ?>" data-field-key="milestones"
                                                        class="dt_multi_select <?php echo esc_html( $class ) ?> select-button button ">
                                                    <?php echo esc_html( $contact_fields["milestones"]["default"][$option_key]["label"] ) ?>
                                                </button>
                                        <?php endforeach; ?>
                                    </div>

                                    <div class="baptism_date">
                                        <div class="section-subheader"><?php esc_html_e( 'Baptism Date', 'disciple_tools' )?></div>
                                        <div class="baptism_date">
                                            <input type="text" class="dt_date_picker"
                                                   value="<?php echo esc_html( $contact["baptism_date"]["formatted"] ?? '' )?>"
                                                   id="baptism_date">
                                        </div>
                                    </div>

                                </div>
                            </section>

                            <?php
                            //get sections added by plugins
                            $sections = apply_filters( 'dt_details_additional_section_ids', [], "contacts" );
                            //get custom sections
                            $custom_tiles = dt_get_option( "dt_custom_tiles" );
                            foreach ( $custom_tiles["contacts"] as $tile_key => $tile_options ){
                                if ( !in_array( $tile_key, $sections ) ){
                                    $sections[] = $tile_key;
                                }
                                //remove section if hidden
                                if ( isset( $tile_options["hidden"] ) && $tile_options["hidden"] == true ){
                                    if ( ( $index = array_search( $tile_key, $sections ) ) !== false) {
                                        unset( $sections[ $index ] );
                                    }
                                }
                            }

                            foreach ( $sections as $section ){
                                ?>
                                <section id="<?php echo esc_html( $section ) ?>" class="xlarge-6 large-12 medium-6 cell grid-item">
                                    <div class="bordered-box">
                                        <?php
                                        //setup tile label if see by customizations
                                        if ( isset( $custom_tiles["contacts"][$section]["label"] ) ){ ?>
                                            <h3 class="section-header">
                                                <?php echo esc_html( $custom_tiles["contacts"][$section]["label"] )?>
                                                <button class="section-chevron chevron_down">
                                                    <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/chevron_down.svg' ) ?>"/>
                                                </button>
                                                <button class="section-chevron chevron_up">
                                                    <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/chevron_up.svg' ) ?>"/>
                                                </button>

                                            </h3>
                                        <?php }
                                        // let the plugin add section content
                                        do_action( "dt_details_additional_section", $section, "contacts" );

                                        ?>
                                        <div class="section-body">
                                            <?php
                                            //setup the order of the tile fields
                                            $order = $custom_tiles["contacts"][$section]["order"] ?? [];
                                            foreach ( $contact_fields as $key => $option ){
                                                if ( isset( $option["tile"] ) && $option["tile"] === $section ){
                                                    if ( !in_array( $key, $order )){
                                                        $order[] = $key;
                                                    }
                                                }
                                            }
                                            foreach ( $order as $field_key ) {
                                                if ( !isset( $contact_fields[$field_key] ) ){
                                                    continue;
                                                }

                                                $field = $contact_fields[$field_key];
                                                if ( isset( $field["tile"] ) && $field["tile"] === $section){
                                                    render_field_for_display( $field_key, $contact_fields, $contact );
                                                }
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </section>
                                <?php
                            }
                            ?>

                            <section id="other" class="xlarge-6 large-12 medium-6 cell grid-item">
                                <div class="bordered-box">
                                    <label class="section-header"><?php esc_html_e( 'Other', 'disciple_tools' )?></label>

                                    <div class="section-subheader">
                                        <?php echo esc_html( $contact_fields["tags"]["name"] ) ?>
                                    </div>
                                    <div class="tags">
                                        <var id="tags-result-container" class="result-container"></var>
                                        <div id="tags_t" name="form-tags" class="scrollable-typeahead typeahead-margin-when-active">
                                            <div class="typeahead__container">
                                                <div class="typeahead__field">
                                                    <span class="typeahead__query">
                                                        <input class="js-typeahead-tags input-height"
                                                               name="tags[query]" placeholder="<?php esc_html_e( "Search Tags", 'disciple_tools' ) ?>"
                                                               autocomplete="off">
                                                    </span>
                                                    <span class="typeahead__button">
                                                        <button type="button" data-open="create-tag-modal" class="create-new-tag typeahead__image_button input-height">
                                                            <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/tag-add.svg' ) ?>"/>
                                                        </button>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </section>
                        </div>
                    </div>
                </div>
            </main> <!-- end #main -->

            <aside class="auto cell grid-x">
                <section class="bordered-box comment-activity-section cell"
                         id="comment-activity-section">
                    <?php get_template_part( 'dt-assets/parts/loop', 'activity-comment' ); ?>
                </section>
            </aside>

        </div> <!-- end #inner-content -->

    </div> <!-- end #content -->


    <?php get_template_part( 'dt-assets/parts/modals/modal', 'share' ); ?>
    <?php get_template_part( 'dt-assets/parts/modals/modal', 'new-group' ); ?>
    <?php get_template_part( 'dt-assets/parts/modals/modal', 'revert' ); ?>


    <div class="reveal" id="closed-contact-modal" data-reveal>
        <h1><?php esc_html_e( 'Close Contact', 'disciple_tools' )?></h1>
        <p class="lead"><?php esc_html_e( 'Why do you want to close this contact?', 'disciple_tools' )?></p>

        <select id="reason-closed-options">
            <?php
            foreach ( $contact_fields["reason_closed"]["default"] as $reason_key => $option ) {
                $selected = ( $reason_key === ( $contact["reason_closed"]["key"] ?? "" ) ) ? "selected" : "";
                ?>
                <option value="<?php echo esc_attr( $reason_key )?>" <?php echo esc_html( $selected ) ?>> <?php echo esc_html( $option["label"] ?? "" )?></option>
                <?php
            }
            ?>
        </select>
        <button class="button button-cancel clear" data-close aria-label="Close reveal" type="button">
            <?php esc_html_e( 'Cancel', 'disciple_tools' )?>
        </button>
        <button class="button loader confirm-reason-button" type="button" id="confirm-close" data-field="closed">
            <?php esc_html_e( 'Confirm', 'disciple_tools' )?>
        </button>
        <button class="close-button" data-close aria-label="Close modal" type="button">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    <div class="reveal" id="paused-contact-modal" data-reveal>
        <h1><?php esc_html_e( 'Pause Contact', 'disciple_tools' )?></h1>
        <p class="lead"><?php esc_html_e( 'Why do you want to pause this contact?', 'disciple_tools' )?></p>

        <select id="reason-paused-options">
            <?php
            foreach ( $contact_fields["reason_paused"]["default"] as $reason_key => $option ) {
                ?>
                <option value="<?php echo esc_attr( $reason_key )?>"
                    <?php if ( ( $contact["reason_paused"]["key"] ?? "" ) === $reason_key ){echo "selected";} ?>>
                    <?php echo esc_html( $option["label"] ?? "" )?>
                </option>
                <?php
            }
            ?>
        </select>
        <button class="button button-cancel clear" data-close aria-label="Close reveal" type="button">
            <?php esc_html_e( 'Cancel', 'disciple_tools' )?>
        </button>
        <button class="button loader confirm-reason-button" type="button" id="confirm-pause" data-field="paused">
            <?php esc_html_e( 'Confirm', 'disciple_tools' )?>
        </button>
        <button class="close-button" data-close aria-label="Close modal" type="button">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <div class="reveal" id="unassignable-contact-modal" data-reveal>
        <h1><?php echo esc_html( $contact_fields["reason_unassignable"]["name"] )?></h1>
<!--        <p class="lead">--><?php //esc_html_e( 'How is this contact unassignable', 'disciple_tools' )?><!--</p>-->

        <select id="reason-unassignable-options">
            <?php
            foreach ( $contact_fields["reason_unassignable"]["default"] as $reason_key => $option ) {
                ?>
                <option value="<?php echo esc_attr( $reason_key )?>"
                    <?php if ( ( $contact["unassignable_paused"]["key"] ?? "" ) === $reason_key ){echo "selected";} ?>>
                    <?php echo esc_html( $option["label"] ?? "" )?>
                </option>
                <?php
            }
            ?>
        </select>
        <button class="button button-cancel clear" data-close aria-label="Close reveal" type="button">
            <?php esc_html_e( 'Cancel', 'disciple_tools' )?>
        </button>
        <button class="button loader confirm-reason-button" type="button" id="confirm-unassignable" data-field="unassignable">
            <?php esc_html_e( 'Confirm', 'disciple_tools' )?>
        </button>
        <button class="close-button" data-close aria-label="Close modal" type="button">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    <div class="reveal" id="create-tag-modal" data-reveal data-reset-on-close>

        <p class="lead"><?php esc_html_e( 'Create Tag', 'disciple_tools' )?></p>

        <form class="js-create-tag">
            <label for="title">
                <?php esc_html_e( "Tag", "disciple_tools" ); ?>
            </label>
            <input name="title" id="new-tag" type="text" placeholder="<?php esc_html_e( "tag", "disciple_tools" ); ?>" required aria-describedby="name-help-text">
            <p class="help-text" id="name-help-text"><?php esc_html_e( "This is required", "disciple_tools" ); ?></p>
        </form>

        <div class="grid-x">
            <button class="button button-cancel clear" data-close aria-label="Close reveal" type="button">
                <?php esc_html_e( 'Cancel', 'disciple_tools' )?>
            </button>
            <button class="button" data-close type="button" id="create-tag-return">
                <?php esc_html_e( 'Create and apply tag', 'disciple_tools' ); ?>
            </button>
            <button class="close-button" data-close aria-label="Close modal" type="button">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    </div>

    <div class="reveal" id="baptism-modal" data-reveal>

        <p class="lead"><?php esc_html_e( 'Baptized', 'disciple_tools' )?></p>

        <div>
            <div class="section-subheader">
                <?php esc_html_e( 'Baptized By', 'disciple_tools' )?>
            </div>
            <div class="modal_baptized_by details">
                <var id="modal_baptized_by-result-container" class="result-container modal_baptized_by-result-container"></var>
                <div id="modal_baptized_by_t" name="form-modal_baptized_by" class="scrollable-typeahead typeahead-margin-when-active">
                    <div class="typeahead__container">
                        <div class="typeahead__field">
                            <span class="typeahead__query">
                                <input class="js-typeahead-modal_baptized_by input-height"
                                       name="modal_baptized_by[query]"
                                       placeholder="<?php esc_html_e( "Search multipliers and contacts", 'disciple_tools' ) ?>"
                                       autocomplete="off">
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <span class="section-subheader"><?php esc_html_e( "Baptism Date", 'disciple_tools' ) ?></span>
            <input type="text" data-date-format='yy-mm-dd' value="<?php echo esc_html( $contact["baptism_date"]["formatted"] ?? '' )?>" id="modal-baptism-date-picker">

<!--            <span class="section-subheader">--><?php //esc_html_e( "Baptism Generation", 'disciple_tools' ) ?><!--</span>-->
<!--            <input type="number" value="" id="modal-baptism_generation">-->
        </div>


        <div class="grid-x">
            <button class="button" data-close type="button" id="close-baptism-modal">
                <?php esc_html_e( 'Close', 'disciple_tools' )?>
            </button>
            <button class="close-button" data-close aria-label="Close modal" type="button">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    </div>

    <div class="reveal" id="make_user_from_contact" data-reveal data-reset-on-close>

        <p class="lead"><?php esc_html_e( 'Create User', 'disciple_tools' )?></p>

        <?php if ( isset( $contact['corresponds_to_user'] ) ) : ?>
            <p><?php esc_html_e( "This contact already represents a user", 'disciple_tools' ) ?></p>
        <?php else : ?>

        <p><?php esc_html_e( "This will invite this contact to Disciple.Tools as a multiplier", 'disciple_tools' ) ?></p>

        <form id="create-user-form">
            <label for="user-email">
                <?php esc_html_e( "Email", "disciple_tools" ); ?>
            </label>
            <input name="user-email" id="user-email" type="email" placeholder="user@example.com" required aria-describedby="email-help-text">
            <p class="help-text" id="email-help-text"><?php esc_html_e( "This is required", "disciple_tools" ); ?></p>
            <label for="user-display">
                <?php esc_html_e( "Display Name", "disciple_tools" ); ?>
                <input name="user-display" id="user-display" type="text"
                       value="<?php the_title_attribute(); ?>"
                       placeholder="<?php esc_html_e( "Display name", 'disciple_tools' ) ?>">
            </label>

            <div class="grid-x">
                <p id="create-user-errors" style="color: red"></p>
            </div>
            <div class="grid-x">
                <button class="button button-cancel clear" data-close aria-label="Close reveal" type="button">
                    <?php esc_html_e( 'Cancel', 'disciple_tools' )?>
                </button>
                <button class="button loader" type="submit" id="create-user-return">
                    <?php esc_html_e( 'Create user', 'disciple_tools' ); ?>
                </button>
                <button class="close-button" data-close aria-label="Close modal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </form>
        <?php endif; ?>
    </div>


    <div class="reveal" id="link_to_user" data-reveal data-reset-on-close style="min-height:500px">

        <p class="lead"><?php esc_html_e( 'Link this contact to an existing user', 'disciple_tools' )?></p>

        <?php if ( isset( $contact['corresponds_to_user'] ) ) : ?>
            <p><?php esc_html_e( "This contact already represents a user", 'disciple_tools' ) ?></p>
        <?php else : ?>


        <p><?php esc_html_e( "First, find the user", 'disciple_tools' ) ?></p>

        <div class="user-select details">
            <var id="user-select-result-container" class="result-container user-select-result-container"></var>
            <div id="user-select_t" name="form-user-select">
                <div class="typeahead__container">
                    <div class="typeahead__field">
                        <span class="typeahead__query">
                            <input class="js-typeahead-user-select input-height"
                                   name="user-select[query]" placeholder="<?php esc_html_e( "Search Users", 'disciple_tools' ) ?>"
                                   autocomplete="off">
                        </span>
                        <span class="typeahead__button">
                            <button type="button" class="search_user-select typeahead__image_button input-height" data-id="user-select_t">
                                <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/chevron_down.svg' ) ?>"/>
                            </button>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <br>
        <div class="confirm-merge-with-user" style="display: none">
            <p><?php esc_html_e( "To finish the linking, merge this contact with the existing user details.", 'disciple_tools' ) ?></p>
        </div>

        <?php endif; ?>

        <div class="grid-x">
            <button class="button button-cancel clear" data-close aria-label="Close reveal" type="button">
                <?php esc_html_e( 'Cancel', 'disciple_tools' )?>
            </button>
            <form action='<?php echo esc_url( site_url() );?>/contacts/mergedetails' method='post'>
                <input type='hidden' name='dt_contact_nonce' value='<?php echo esc_attr( wp_create_nonce() ); ?>'/>
                <input type='hidden' name='currentid' value='<?php echo esc_html( $contact["ID"] );?>'/>
                <input id="confirm-merge-with-user-dupe-id" type='hidden' name='dupeid' value=''/>
                <button type='submit' class="button confirm-merge-with-user" style="display: none">
                    <?php esc_html_e( 'Merge', 'disciple_tools' ) ?>
                </button>
            </form>
            <button class="close-button" data-close aria-label="Close modal" type="button">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    </div>

    <div class="reveal" id="merge_with_contact_modal" data-reveal style="min-height:500px">

        <p class="lead"><?php esc_html_e( 'Merge this contact with another contact', 'disciple_tools' )?></p>


            <div class="merge_with details">
                <var id="merge_with-result-container" class="result-container merge_with-result-container"></var>
                <div id="merge_with_t" name="form-merge_with">
                    <div class="typeahead__container">
                        <div class="typeahead__field">
                            <span class="typeahead__query">
                                <input class="js-typeahead-merge_with input-height"
                                       name="merge_with[query]" placeholder="<?php esc_html_e( "Search multipliers and contacts", 'disciple_tools' ) ?>"
                                       autocomplete="off">
                            </span>
                            <span class="typeahead__button">
                            <button type="button" class="search_merge_with typeahead__image_button input-height" data-id="user-select_t">
                                <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/chevron_down.svg' ) ?>"/>
                            </button>
                        </span>
                        </div>
                    </div>
                </div>
            </div>

            <br>
            <div class="confirm-merge-with-contact" style="display: none">
                <p><span  id="name-of-contact-to-merge"></span> <?php esc_html_e( "selected.", 'disciple_tools' ) ?></p>
                <p><?php esc_html_e( "Click merge to continue.", 'disciple_tools' ) ?></p>
            </div>

            <div class="grid-x">
                <button class="button button-cancel clear" data-close aria-label="Close reveal" type="button">
                    <?php esc_html_e( 'Cancel', 'disciple_tools' )?>
                </button>
                <form action='<?php echo esc_url( site_url() );?>/contacts/mergedetails' method='post'>
                    <input type='hidden' name='dt_contact_nonce' value='<?php echo esc_attr( wp_create_nonce() ); ?>'/>
                    <input type='hidden' name='currentid' value='<?php echo esc_html( $contact["ID"] );?>'/>
                    <input id="confirm-merge-with-contact-id" type='hidden' name='dupeid' value=''/>
                    <button type='submit' class="button confirm-merge-with-contact" style="display: none">
                        <?php esc_html_e( 'Merge', 'disciple_tools' ) ?>
                    </button>
                </form>
                <button class="close-button" data-close aria-label="Close modal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
    </div>

    <?php
} )();

if ( isset( $_POST['merge'], $_POST['dt_contact_nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['dt_contact_nonce'] ) ) ?? null ) ) {
    echo "<script type='text/javascript'>$(document).ready(function() { $('#merge-dupe-modal').click(); });</script>";
}

get_footer();
