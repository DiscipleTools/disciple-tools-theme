<?php
declare( strict_types=1 );

if ( ! current_user_can( 'access_contacts' ) ) {
    wp_safe_redirect( '/settings' );
}

( function () {
    if ( !Disciple_Tools_Contacts::can_view( 'contacts', get_the_ID() )) {
        get_template_part( "403" );
        die();
    }
    $contact = Disciple_Tools_Contacts::get_contact( get_the_ID(), true, true );
    $contact_fields = Disciple_Tools_Contacts::get_contact_fields();

    Disciple_Tools_Notifications::process_new_notifications( get_the_ID() ); // removes new notifications for this post


    get_header(); ?>

    <?php
    $current_user_id = get_current_user_id();
    $following = DT_Posts::get_users_following_post( "contacts", get_the_ID() );
    $dispatcher_actions = [];
    if ( current_user_can( "create_users" )){
        $dispatcher_actions[] = "make-user-from-contact-modal";
        $dispatcher_actions[] = "link-to-user-modal";
    }
    if ( current_user_can( "access_contacts" )){
        $dispatcher_actions[] = "merge_with_contact";
        $dispatcher_actions[] = "duplicates-modal";
    }
    dt_print_details_bar(
        true,
        true,
        current_user_can( "assign_any_contacts" ),
        isset( $contact["requires_update"] ) && $contact["requires_update"] === true,
        in_array( $current_user_id, $following ),
        isset( $contact["assigned_to"]["id"] ) ? $contact["assigned_to"]["id"] == $current_user_id : false,
        $dispatcher_actions,
        true
    ); ?>

<!--    <div id="errors"></div>-->
    <div id="content" class="single-contacts">
        <span id="contact-id" style="display: none"><?php echo get_the_ID()?></span>
        <span id="post-id" style="display: none"><?php echo get_the_ID()?></span>
        <span id="post-type" style="display: none">contact</span>

        <div id="inner-content" class="grid-x grid-margin-x grid-margin-y">

            <?php do_action( 'dt_record_top_full_with', 'contacts', $contact ) ?>

            <section id="mobile-quick-actions" class="hide-for-large small-12 cell">
                <div class="bordered-box">
                    <h3 class="section-header"><?php esc_html_e( 'Quick Actions', 'disciple_tools' ) ?>
                        <button class="help-button float-right" data-section="quick-action-help-text">
                            <img class="help-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>"/>
                        </button>
                        <button class="section-chevron chevron_down">
                            <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/chevron_down.svg' ) ?>"/>
                        </button>
                        <button class="section-chevron chevron_up">
                            <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/chevron_up.svg' ) ?>"/>
                        </button>
                    </h3>
                    <div class="section-body"><!-- start collapse -->
                    <?php get_template_part( 'dt-assets/parts/contact', 'quick-buttons' ); ?>
                    <div style="text-align: center">
                        <a class="button small" href="#comment-activity-section" style="margin-bottom: 0">
                            <?php esc_html_e( 'View Comments', 'disciple_tools' ) ?>
                        </a>
                    </div>
                    <!-- end collapse --></div>
                </div>
            </section>
            <main id="main" class="xlarge-7 large-7 medium-12 small-12 cell" role="main" style="padding:0">

                <div class="cell grid-y grid-margin-y">
                    <section id="duplicates" class="small-12 grid-y grid-margin-y cell" style="display: none">
                        <div class="bordered-box detail-notification-box" style="background-color:#ff9800">
                            <h4><?php esc_html_e( "This contact has possible duplicates.", 'disciple_tools' ) ?></h4>
                            <button type="button" id="merge-dupe-modal" data-open="merge-dupe-edit-modal" class="button">
                              <?php esc_html_e( "Go to duplicates", 'disciple_tools' ) ?>
                            </button>
                        </div>
                    </section>


                <?php get_template_part( 'dt-assets/parts/merge', 'details' );
                get_template_part( 'dt-assets/parts/contact', 'details' ); ?>

                <!-- CONNECTIONS TILE -->
                    <div class="cell small-12">
                        <div class="grid-x grid-margin-x grid-margin-y grid">

                            <!-- CONNECTIONS -->
                            <section id="relationships" class="xlarge-6 large-12 medium-6 cell grid-item">
            <!--                    <div class="bordered-box last-typeahead-in-section">-->

                                <div class="bordered-box" id="connections-tile">
                                    <h3 class="section-header"><?php esc_html_e( "Connections", 'disciple_tools' ) ?>
                                        <button class="help-button float-right" data-section="connections-help-text">
                                            <img class="help-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>"/>
                                        </button>
                                        <button class="section-chevron chevron_down">
                                            <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/chevron_down.svg' ) ?>"/>
                                        </button>
                                        <button class="section-chevron chevron_up">
                                            <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/chevron_up.svg' ) ?>"/>
                                        </button>
                                    </h3>
                                    <div class="section-body"><!-- start collapse -->
                                    <div class="section-subheader"><?php echo esc_html( $contact_fields["groups"]['name'] ) ?></div>

                                        <?php do_action( 'dt_pre_contacts_connections_section', $contact_fields, $contact ) ?>

                                        <!-- groups -->
                                        <var id="groups-result-container" class="result-container"></var>
                                        <div id="groups_t" name="form-groups" class="scrollable-typeahead typeahead-margin-when-active">
                                            <div class="typeahead__container">
                                                <div class="typeahead__field">
                                                    <span class="typeahead__query">
                                                        <input class="js-typeahead-groups input-height"
                                                               name="groups[query]"
                                                               placeholder="<?php echo esc_html( sprintf( _x( "Search %s", "Search 'something'", 'disciple_tools' ), $contact_fields["groups"]['name'] ) )?>"
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
                                            "relation" => $contact_fields['relation']["name"],
                                            "baptized_by" => $contact_fields['baptized_by']["name"],
                                            "baptized" => $contact_fields['baptized']["name"],
                                            "coached_by" => $contact_fields['coached_by']["name"],
                                            "coaching" => $contact_fields['coaching']["name"]
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
                                                                <input class="js-typeahead-<?php echo esc_html( $connection ) ?> input-height"
                                                                       name="<?php echo esc_html( $connection ) ?>[query]" placeholder="<?php echo esc_html_x( "Search multipliers and contacts", 'input field placeholder', 'disciple_tools' ) ?>"
                                                                       autocomplete="off">
                                                            </span>
                                                            <span class="typeahead__button">
                                                                <button type="button" data-connection-key="<?php echo esc_html( $connection ) ?>" class="create-new-contact typeahead__image_button input-height">
                                                                    <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/add-contact.svg' ) ?>"/>
                                                                </button>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php
                                        }
                                        ?>

                                        <?php do_action( 'dt_post_contacts_progress_section', $contact_fields, $contact ) ?>
                                </div><!-- end collapse --></div>
                            </section>

                        <!-- PROGRESS TILE -->
                        <section id="faith" class="xlarge-6 large-12 medium-6 cell grid-item">
                            <div class="bordered-box" id="progress-tile">
                                <h3 class="section-header"><?php esc_html_e( 'Progress', 'disciple_tools' )?>
                                    <button class="help-button float-right" data-section="contact-progress-help-text">
                                        <img class="help-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>"/>
                                    </button>
                                    <button class="section-chevron chevron_down">
                                        <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/chevron_down.svg' ) ?>"/>
                                    </button>
                                    <button class="section-chevron chevron_up">
                                        <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/chevron_up.svg' ) ?>"/>
                                    </button>
                                </h3>
                                <div class="section-body"><!-- start collapse -->

                                    <?php do_action( 'dt_pre_contacts_progress_section', $contact_fields, $contact ) ?>

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
                                            if ( $contact["seeker_path"]["key"] === $key ) :
                                                ?>
                                                <option value="<?php echo esc_html( $key ) ?>" selected><?php echo esc_html( $value ); ?></option>
                                            <?php else : ?>
                                                <option value="<?php echo esc_html( $key ) ?>"><?php echo esc_html( $value ); ?></option>
                                            <?php endif;
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
                                        <?php
                                        echo esc_html( $contact_fields["milestones"]["name"] )?>
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

                                    <!-- Baptism Date-->
                                    <div class="section-subheader"><?php echo esc_html( $contact_fields["baptism_date"]["name"] )?></div>
                                    <div class="baptism_date">
                                        <div class="baptism_date input-group">
                                            <input id="baptism_date" class="input-group-field dt_date_picker" type="text" autocomplete="off"
                                                   value="<?php echo esc_html( $contact["baptism_date"]["timestamp"] ?? '' )?>" >
                                            <div class="input-group-button">
                                                <button id="baptism-date-clear-button" class="button alert clear-date-button" data-inputid="baptism_date" title="Delete Date">x</button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- post action -->
                                    <?php do_action( 'dt_post_contacts_progress_section', $contact_fields, $contact ) ?>

                                </div><!-- end collapse --></div>
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
                                    $index = array_search( $tile_key, $sections );
                                    if ( $index !== false) {
                                        unset( $sections[ $index ] );
                                    }
                                }
                            }

                            foreach ( $sections as $section ){
                                ?>
                                <section id="<?php echo esc_html( $section ) ?>" class="xlarge-6 large-12 medium-6 cell grid-item">
                                    <div class="bordered-box" id="<?php echo esc_html( $section )?>-tile">
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
                        <!-- OTHER TILE -->
                            <section id="other" class="xlarge-6 large-12 medium-6 cell grid-item">
                                <div class="bordered-box" id="other-tile">
                                    <h3 class="section-header"><?php esc_html_e( 'Other', 'disciple_tools' )?>
                                        <button class="help-button" data-section="other-tile-help-text">
                                            <img class="help-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>"/>
                                        </button>
                                        <button class="section-chevron chevron_down">
                                            <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/chevron_down.svg' ) ?>"/>
                                        </button>
                                        <button class="section-chevron chevron_up">
                                            <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/chevron_up.svg' ) ?>"/>
                                        </button>
                                    </h3>
                                    <div class="section-body"><!-- start collapse -->

                                        <?php do_action( 'dt_pre_contacts_other_section', $contact_fields, $contact ) ?>

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
                                                               name="tags[query]"
                                                               placeholder="<?php echo esc_html( sprintf( _x( "Search %s", "Search 'something'", 'disciple_tools' ), $contact_fields["tags"]['name'] ) )?>"
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

                                    <?php do_action( 'dt_post_contacts_other_section', $contact_fields, $contact ) ?>
                                <!-- end collapse --></div>
                            </section>
                        </div>
                    </div>
                </div>
            </main> <!-- end #main -->

            <aside class="auto cell grid-x">
                <section class="comment-activity-section cell"
                         id="comment-activity-section">
                    <?php get_template_part( 'dt-assets/parts/loop', 'activity-comment' ); ?>
                </section>
            </aside>

        </div> <!-- end #inner-content -->

    </div> <!-- end #content -->


    <?php get_template_part( 'dt-assets/parts/modals/modal', 'share' ); ?>
    <?php get_template_part( 'dt-assets/parts/modals/modal', 'new-group' ); ?>
    <?php get_template_part( 'dt-assets/parts/modals/modal', 'new-contact' ); ?>
    <?php get_template_part( 'dt-assets/parts/modals/modal', 'revert' ); ?>
    <?php get_template_part( 'dt-assets/parts/modals/modal', 'tasks' ); ?>


    <div class="reveal" id="closed-contact-modal" data-reveal>
        <h3><?php echo esc_html( $contact_fields["reason_closed"]["name"] ?? '' )?></h3>
        <p><?php echo esc_html( $contact_fields["reason_closed"]["description"] ?? '' )?></p>
        <p><?php esc_html_e( 'Choose an option:', 'disciple_tools' )?></p>

        <select id="reason-closed-options">
            <?php
            foreach ( $contact_fields["reason_closed"]["default"] as $reason_key => $option ) {
                if ( !empty( $option["label"] ) ) {
                    $selected = ( $reason_key === ( $contact["reason_closed"]["key"] ?? "" ) ) ? "selected" : "";
                    ?>
                    <option
                        value="<?php echo esc_attr( $reason_key ) ?>" <?php echo esc_html( $selected ) ?>> <?php echo esc_html( $option["label"] ?? "" ) ?></option>
                    <?php
                }
            }
            ?>
        </select>
        <button class="button button-cancel clear" data-close aria-label="Close reveal" type="button">
            <?php echo esc_html__( 'Cancel', 'disciple_tools' )?>
        </button>
        <button class="button loader confirm-reason-button" type="button" id="confirm-close" data-field="closed">
            <?php echo esc_html__( 'Confirm', 'disciple_tools' )?>
        </button>
        <button class="close-button" data-close aria-label="Close modal" type="button">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    <div class="reveal" id="paused-contact-modal" data-reveal>
        <h3><?php echo esc_html( $contact_fields["reason_paused"]["name"] ?? '' )?></h3>
        <p><?php echo esc_html( $contact_fields["reason_paused"]["description"] ?? '' )?></p>
        <p><?php esc_html_e( 'Choose an option:', 'disciple_tools' )?></p>

        <select id="reason-paused-options">
            <?php
            foreach ( $contact_fields["reason_paused"]["default"] as $reason_key => $option ) {
                if ( $option["label"] ) {
                    ?>
                    <option value="<?php echo esc_attr( $reason_key ) ?>"
                        <?php if ( ( $contact["reason_paused"]["key"] ?? "" ) === $reason_key ) {
                            echo "selected";
                        } ?>>
                        <?php echo esc_html( $option["label"] ?? "" ) ?>
                    </option>
                    <?php
                }
            }
            ?>
        </select>
        <button class="button button-cancel clear" data-close aria-label="Close reveal" type="button">
            <?php echo esc_html__( 'Cancel', 'disciple_tools' )?>
        </button>
        <button class="button loader confirm-reason-button" type="button" id="confirm-pause" data-field="paused">
            <?php echo esc_html__( 'Confirm', 'disciple_tools' )?>
        </button>
        <button class="close-button" data-close aria-label="Close modal" type="button">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <div class="reveal" id="unassignable-contact-modal" data-reveal>
        <h3><?php echo esc_html( $contact_fields["reason_unassignable"]["name"] ?? '' )?></h3>
        <p><?php echo esc_html( $contact_fields["reason_unassignable"]["description"] ?? '' )?></p>
        <p><?php esc_html_e( 'Choose an option:', 'disciple_tools' )?></p>

        <select id="reason-unassignable-options">
            <?php
            foreach ( $contact_fields["reason_unassignable"]["default"] as $reason_key => $option ) {
                if ( isset( $option["label"] ) ) {
                    ?>
                    <option value="<?php echo esc_attr( $reason_key ) ?>"
                        <?php if ( ( $contact["unassignable_paused"]["key"] ?? "" ) === $reason_key ) {
                            echo "selected";
                        } ?>>
                        <?php echo esc_html( $option["label"] ?? "" ) ?>
                    </option>
                    <?php
                }
            }
            ?>
        </select>
        <button class="button button-cancel clear" data-close aria-label="Close reveal" type="button">
            <?php echo esc_html__( 'Cancel', 'disciple_tools' )?>
        </button>
        <button class="button loader confirm-reason-button" type="button" id="confirm-unassignable" data-field="unassignable">
            <?php echo esc_html__( 'Confirm', 'disciple_tools' )?>
        </button>
        <button class="close-button" data-close aria-label="Close modal" type="button">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    <div class="reveal" id="create-tag-modal" data-reveal data-reset-on-close>
        <h3><?php esc_html_e( 'Create Tag', 'disciple_tools' )?></h3>
        <p><?php esc_html_e( 'Create a tag and apply it to this contact.', 'disciple_tools' )?></p>

        <form class="js-create-tag">
            <label for="title">
                <?php esc_html_e( "Tag", "disciple_tools" ); ?>
            </label>
            <input name="title" id="new-tag" type="text" placeholder="<?php esc_html_e( "Tag", 'disciple_tools' ); ?>" required aria-describedby="name-help-text">
            <p class="help-text" id="name-help-text"><?php esc_html_e( "This is required", "disciple_tools" ); ?></p>
        </form>

        <div class="grid-x">
            <button class="button button-cancel clear" data-close aria-label="Close reveal" type="button">
                <?php echo esc_html__( 'Cancel', 'disciple_tools' )?>
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

        <h3><?php echo esc_html( $contact_fields["baptized"]["name"] )?></h3>
        <p><?php esc_html_e( "Who was this contact baptized by and when?", 'disciple_tools' )?></p>

        <div>
            <div class="section-subheader">
                <?php echo esc_html( $contact_fields["baptized_by"]["name"] )?>
            </div>
            <div class="modal_baptized_by details">
                <var id="modal_baptized_by-result-container" class="result-container modal_baptized_by-result-container"></var>
                <div id="modal_baptized_by_t" name="form-modal_baptized_by" class="scrollable-typeahead typeahead-margin-when-active">
                    <div class="typeahead__container">
                        <div class="typeahead__field">
                            <span class="typeahead__query">
                                <input class="js-typeahead-modal_baptized_by input-height"
                                       name="modal_baptized_by[query]"
                                       placeholder="<?php echo esc_html_x( "Search multipliers and contacts", 'input field placeholder', 'disciple_tools' ) ?>"
                                       autocomplete="off">
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <span class="section-subheader"><?php echo esc_html( $contact_fields["baptism_date"]["name"] )?></span>
            <input type="text" data-date-format='yy-mm-dd' value="<?php echo esc_html( $contact["baptism_date"]["timestamp"] ?? '' );?>" id="modal-baptism-date-picker" autocomplete="off">

        </div>


        <div class="grid-x">
            <button class="button" data-close type="button" id="close-baptism-modal">
                <?php echo esc_html__( 'Close', 'disciple_tools' )?>
            </button>
            <button class="close-button" data-close aria-label="Close modal" type="button">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    </div>

    <div class="reveal" id="make-user-from-contact-modal" data-reveal data-reset-on-close>
        <h3><?php echo esc_html_x( 'Make User From Contact', 'Make user modal', 'disciple_tools' )?></h3>

        <?php if ( isset( $contact['corresponds_to_user'] ) ) : ?>
            <p><strong><?php echo esc_html_x( "This contact is already connected to a user.", 'Make user modal', 'disciple_tools' ) ?></strong></p>
        <?php else : ?>

        <p><?php echo esc_html_x( "This will invite this contact to become a user of this system. By default, they will be given the role of a 'multiplier'.", 'Make user modal', 'disciple_tools' ) ?></p>
        <p><?php echo esc_html_x( "In the fields below, enter their email address and a 'Display Name' which they can change later.", 'Make user modal', 'disciple_tools' ) ?></p>

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
                       placeholder="<?php esc_html_e( "Display Name", 'disciple_tools' ) ?>">
            </label>

            <div class="grid-x">
                <p id="create-user-errors" style="color: red"></p>
            </div>
            <div class="grid-x">
                <button class="button button-cancel clear" data-close aria-label="Close reveal" type="button">
                    <?php echo esc_html__( 'Cancel', 'disciple_tools' )?>
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


    <div class="reveal" id="link-to-user-modal" data-reveal data-reset-on-close style="min-height:500px">

        <h3><?php esc_html_e( "Link this contact to an existing user", 'disciple_tools' )?></h3>

        <?php if ( isset( $contact['corresponds_to_user'] ) ) : ?>
            <p><?php esc_html_e( "This contact already represents a user.", 'disciple_tools' ) ?></p>
        <?php else : ?>


        <p><?php echo esc_html_x( "To link to an existing user, first, find the user using the field below.", 'Step 1 of link user', 'disciple_tools' ) ?></p>

        <div class="user-select details">
            <var id="user-select-result-container" class="result-container user-select-result-container"></var>
            <div id="user-select_t" name="form-user-select">
                <div class="typeahead__container">
                    <div class="typeahead__field">
                        <span class="typeahead__query">
                            <input class="js-typeahead-user-select input-height"
                                   name="user-select[query]" placeholder="<?php echo esc_html_x( "Search Users", 'input field placeholder', 'disciple_tools' ) ?>"
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
            <p><?php echo esc_html_x( "To finish the linking, merge this contact with the existing user details.", 'Step 2 of link user', 'disciple_tools' ) ?></p>
        </div>

        <?php endif; ?>

        <div class="grid-x">
            <button class="button button-cancel clear" data-close aria-label="Close reveal" type="button">
                <?php echo esc_html__( 'Cancel', 'disciple_tools' )?>
            </button>
            <form action='<?php echo esc_url( site_url() );?>/contacts/mergedetails' method='get'>
                <input type='hidden' name='currentid' value='<?php echo esc_html( $contact["ID"] );?>'/>
                <input id="confirm-merge-with-user-dupe-id" type='hidden' name='dupeid' value=''/>
                <button type='submit' class="button confirm-merge-with-user" style="display: none">
                    <?php echo esc_html__( 'Merge', 'disciple_tools' )?>
                </button>
            </form>
            <button class="close-button" data-close aria-label="Close modal" type="button">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    </div>

    <div class="reveal" id="merge-with-contact-modal" data-reveal style="min-height:500px">
        <h3><?php esc_html_e( "Merge Contact", 'disciple_tools' )?></h3>
        <p><?php esc_html_e( "Merge this contact with another contact.", 'disciple_tools' )?></p>

            <div class="merge_with details">
                <var id="merge_with-result-container" class="result-container merge_with-result-container"></var>
                <div id="merge_with_t" name="form-merge_with">
                    <div class="typeahead__container">
                        <div class="typeahead__field">
                            <span class="typeahead__query">
                                <input class="js-typeahead-merge_with input-height"
                                       name="merge_with[query]" placeholder="<?php echo esc_html_x( "Search multipliers and contacts", 'input field placeholder', 'disciple_tools' ) ?>"
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
                <p><span  id="name-of-contact-to-merge"></span> <?php echo esc_html_x( "selected.", 'added to the end of a sentence', 'disciple_tools' ) ?></p>
                <p><?php esc_html_e( "Click merge to continue.", 'disciple_tools' ) ?></p>
            </div>

            <div class="grid-x">
                <button class="button button-cancel clear" data-close aria-label="Close reveal" type="button">
                    <?php echo esc_html__( 'Cancel', 'disciple_tools' )?>
                </button>
                <form action='<?php echo esc_url( site_url() );?>/contacts/mergedetails' method='get'>
                    <input type='hidden' name='currentid' value='<?php echo esc_html( $contact["ID"] );?>'/>
                    <input id="confirm-merge-with-contact-id" type='hidden' name='dupeid' value=''/>
                    <button type='submit' class="button confirm-merge-with-contact" style="display: none">
                        <?php echo esc_html__( 'Merge', 'disciple_tools' )?>
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
