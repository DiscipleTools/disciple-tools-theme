<?php
declare( strict_types=1 );

( function () {


    if ( !Disciple_Tools_Contacts::can_view( 'contacts', get_the_ID() )) {
        get_template_part( "403" );
        die();
    }
    Disciple_Tools_Notifications::process_new_notifications( get_the_ID() ); // removes new notifications for this post
    $contact = Disciple_Tools_Contacts::get_contact( get_the_ID(), true );
    $contact_fields = Disciple_Tools_Contacts::get_contact_fields();

    get_header(); ?>

    <?php
    $current_user_id = get_current_user_id();
    $following = Disciple_Tools_Posts::get_users_following_post( "contacts", get_the_ID() );
    dt_print_details_bar(
        true,
        true,
        current_user_can( "assign_any_contacts" ),
        isset( $contact["requires_update"] ) && $contact["requires_update"]["key"] === "yes",
        in_array( $current_user_id, $following ),
        isset( $contact["assigned_to"]["id"] ) ? $contact["assigned_to"]["id"] == $current_user_id : false
    ); ?>

    <div id="errors"></div>

    <div id="content">
        <span id="contact-id" style="display: none"><?php echo get_the_ID()?></span>
        <span id="post-id" style="display: none"><?php echo get_the_ID()?></span>
        <span id="post-type" style="display: none">contact</span>

        <div id="inner-content" class="grid-x grid-margin-x grid-margin-y">

            <div class="small-12 cell bordered-box grid-x grid-margin-x">
                <div class="cell small-12 medium-4">
                    <i class="fi-torso large"></i>
                    <span class="item-details-header title" ><?php the_title_attribute(); ?></span>
                </div>
                <div class="cell small-12 medium-2">
                    <div class="section-subheader">
                        <?php esc_html_e( "Status", 'disciple_tools' ) ?>
                        <button class="help-button" data-section="overall-status-help-text">
                            <img class="help-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>"/>
                        </button>
                    </div>
                    <?php
                    $active_color = "#366184";
                    $current_key = $contact["overall_status"]["key"] ?? "";
                    if ( isset( $contact_fields["overall_status"]["colors"][ $current_key ] )){
                        $active_color = $contact_fields["overall_status"]["colors"][$current_key];
                    }
                    ?>
                    <select id="overall_status" class="select-field" style="width:fit-content; margin-bottom:0px; background-color: <?php echo esc_html( $active_color ) ?>">
                    <?php foreach ($contact_fields["overall_status"]["default"] as $key => $value){
                        if ( $contact["overall_status"]["key"] === $key ) {
                            ?>
                            <option value="<?php echo esc_html( $key ) ?>" selected><?php echo esc_html( $value ); ?></option>
                        <?php } else { ?>
                            <option value="<?php echo esc_html( $key ) ?>"><?php echo esc_html( $value ); ?></option>
                        <?php } ?>
                    <?php } ?>
                    </select>
                    <p>
                        <span id="reason">
                            <?php
                            $hide_edit_button = false;
                            if ( $contact["overall_status"]["key"] === "paused" &&
                                 isset( $contact["reason_paused"] )){
                                echo '(' . esc_html( $contact["reason_paused"]["label"] ) . ')';
                            } else if ( $contact["overall_status"]["key"] === "closed" &&
                                        isset( $contact["reason_closed"] )){
                                echo '(' . esc_html( $contact["reason_closed"]["label"] ) . ')';
                            } else if ( $contact["overall_status"]["key"] === "unassignable" &&
                                        isset( $contact["reason_unassignable"] )){
                                echo '(' . esc_html( $contact["reason_unassignable"]["label"] ) . ')';
                            } else {
                                $hide_edit_button = true;
                            }
                            ?>
                        </span>
                        <button id="edit-reason" <?php if ( $hide_edit_button ) : ?> style="display: none"<?php endif; ?> ><i class="fi-pencil"></i></button>
                    </p>
                </div>

                <div class="cell small-12 medium-3">
                    <!-- Assigned To -->
                    <div class="section-subheader">
                        <img src="<?php echo esc_url( get_template_directory_uri() ) . '/dt-assets/images/assigned-to.svg' ?>">
                        <?php esc_html_e( 'Assigned to', 'disciple_tools' )?>
                    </div>

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
                </div>
                <div class="cell small-12 medium-3">
                    <div class="section-subheader">
                        <img src="<?php echo esc_url( get_template_directory_uri() ) . '/dt-assets/images/subassigned.svg' ?>">
                        <?php esc_html_e( 'Sub-assigned to', 'disciple_tools' )?>
                    </div>
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
            </div>

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
                    <section id="contact-details" class="small-12 grid-y grid-margin-y ">
                        <?php get_template_part( 'dt-assets/parts/contact', 'details' ); ?>
                    </section>
                    <div class="cell small-12">
                        <div class="grid-x grid-margin-x grid-margin-y grid">
                            <section id="relationships" class="xlarge-6 large-12 medium-6 cell grid-item">
            <!--                    <div class="bordered-box last-typeahead-in-section">-->
                                <div class="bordered-box">
                                    <h3 class="section-header"><?php esc_html_e( "Connections", 'disciple_tools' ) ?></h3>
                                    <div class="section-subheader"><?php esc_html_e( "Groups", 'disciple_tools' ) ?></div>
                                    <var id="groups-result-container" class="result-container"></var>
                                    <div id="groups_t" name="form-groups" class="scrollable-typeahead typeahead-margin-when-active">
                                        <div class="typeahead__container">
                                            <div class="typeahead__field">
                                                <span class="typeahead__query">
                                                    <input class="js-typeahead-groups input-height"
                                                           name="groups[query]" placeholder="<?php esc_html_e( "Search Groups", 'disciple_tools' ) ?>"
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
                                        "baptized_by" => esc_html__( "Baptized By", 'disciple_tools' ),
                                        "baptized" => esc_html__( "Baptized", 'disciple_tools' ),
                                        "coached_by" => esc_html__( "Coached By", 'disciple_tools' ),
                                        "coaching" => esc_html__( "Coaching", 'disciple_tools' )
                                    ];
                                    foreach ( $connections as $connection => $connection_label ) {
                                        ?>
                                        <div class="section-subheader"><?php echo esc_html( $connection_label ) ?></div>
                                        <var id="<?php echo esc_html( $connection ) ?>-result-container" class="result-container"></var>
                                        <div id="<?php echo esc_html( $connection ) ?>_t" name="form-<?php echo esc_html( $connection ) ?>" class="scrollable-typeahead typeahead-margin-when-active">
                                            <div class="typeahead__container">
                                                <div class="typeahead__field">
                                                    <span class="typeahead__query">
                                                        <input class="js-typeahead-<?php echo esc_html( $connection ) ?>"
                                                               name="<?php echo esc_html( $connection ) ?>[query]" placeholder="<?php esc_html_e( "Search Contacts", 'disciple_tools' ) ?>"
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
                                        <?php esc_html_e( 'Seeker Path', 'disciple_tools' )?>
                                        <button class="help-button" data-section="seeker-path-help-text">
                                            <img class="help-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>"/>
                                        </button>
                                    </div>

                                    <select class="select-field" id="seeker_path" style="margin-bottom: 0px">
                                    <?php

                                    foreach ($contact_fields["seeker_path"]["default"] as $key => $value){
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
                                        <?php esc_html_e( 'Faith Milestones', 'disciple_tools' )?>
                                        <button class="help-button" data-section="faith-milestones-help-text">
                                            <img class="help-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>"/>
                                        </button>
                                    </div>
                                    <div class="small button-group" style="display: inline-block">

                                        <?php foreach ( $contact_fields as $field => $val ): ?>
                                            <?php
                                            if (strpos( $field, "milestone_" ) === 0) {
                                                $class = ( isset( $contact[ $field ] ) && $contact[ $field ]['key'] === 'yes' ) ?
                                                    "selected-select-button" : "empty-select-button";
                                                ?>
                                                <button onclick="save_seeker_milestones( <?php echo esc_html( get_the_ID() ) ?> , '<?php echo esc_html( $field ) ?>')"
                                                        id="<?php echo esc_html( $field ) ?>"
                                                        class="<?php echo esc_html( $class ) ?> select-button button ">
                                                    <?php echo esc_html( $contact_fields[ $field ]["name"] ) ?>
                                                </button>
                                            <?php }?>
                                        <?php endforeach; ?>
                                    </div>

                                    <div class="baptism_date">
                                        <div class="section-subheader"><?php esc_html_e( 'Baptism Date', 'disciple_tools' )?></div>
                                        <div class="baptism_date">
                                            <input type="text" data-date-format='yy-mm-dd' value="<?php echo esc_html( $contact["baptism_date"] ?? '' )?>" id="baptism-date-picker">
                                        </div>
                                    </div>


                                </div>
                            </section>

                            <?php
                            $sections = apply_filters( 'dt_details_additional_section_ids', [], "contacts" );

                            foreach ( $sections as $section ){
                                ?>
                                <section id="<?php echo esc_html( $section ) ?>" class="xlarge-6 large-12 medium-6 cell grid-item">
                                    <div class="bordered-box">
                                        <?php
                                        do_action( "dt_details_additional_section", $section )
                                        ?>
                                    </div>
                                </section>
                                <?php
                            }
                            ?>
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
            foreach ( $contact_fields["reason_closed"]["default"] as $reason_key => $reason_label ) {
                $selected = ( $reason_key === ( $contact["reason_closed"]["key"] ?? "" ) ) ? "selected" : "";
                ?>
                <option value="<?php echo esc_attr( $reason_key )?>" <?php echo esc_html( $selected ) ?>> <?php echo esc_html( $reason_label )?></option>
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
            foreach ( $contact_fields["reason_paused"]["default"] as $reason_key => $reason_label ) {
                ?>
                <option value="<?php echo esc_attr( $reason_key )?>"
                    <?php if ( ( $contact["reason_paused"]["key"] ?? "" ) === $reason_key ){echo "selected";} ?>>
                    <?php echo esc_html( $reason_label )?>
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
        <h1><?php esc_html_e( 'Contact Unassignable', 'disciple_tools' )?></h1>
        <p class="lead"><?php esc_html_e( 'How is this contact unassignable', 'disciple_tools' )?></p>

        <select id="reason-unassignable-options">
            <?php
            foreach ( $contact_fields["reason_unassignable"]["default"] as $reason_key => $reason_label ) {
                ?>
                <option value="<?php echo esc_attr( $reason_key )?>"
                    <?php if ( ( $contact["unassignable_paused"]["key"] ?? "" ) === $reason_key ){echo "selected";} ?>>
                    <?php echo esc_html( $reason_label )?>
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

    <div class="reveal" id="edit-reason-modal" data-reveal>


        <div class="medium-6 cell reason-field">
            <?php
            $status = $contact['overall_status']['key'] ?? '';
            $has_status = isset( $contact_fields["reason_$status"]['name'] );
            ?>
            <div class="section-subheader">
                <?php
                if ( $has_status ) {
                    echo esc_html( $contact_fields["reason_$status"]['name'] );
                }
                ?>
            </div>
            <?php
            $status_style = !$has_status ? 'display:none;' : '';
            $reason_field = $has_status ? "reason_$status" : '';
            ?>
            <select class="status-reason" style="<?php echo esc_html( $status_style ); ?>" data-field="<?php echo esc_html( $reason_field ) ?>">
                <?php
                if ( $has_status ) {
                    foreach ( $contact_fields["reason_$status"]['default'] as $reason_key => $reason_label ) { ?>
                        <option value="<?php echo esc_attr( $reason_key ) ?>"
                            <?php
                            $selected = $contact["reason_$status"]['key'] ?? '' === $reason_key ? 'selected' : '';
                            echo esc_html( $selected ); ?>>
                            <?php echo esc_html( $reason_label, 'disciple_tools' ); ?>
                        </option>
                        <?php
                    }
                }
                ?>
            </select>
        </div>
    </div>

    <?php
} )();

get_footer();
