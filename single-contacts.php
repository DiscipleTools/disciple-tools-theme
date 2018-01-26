<?php
declare( strict_types=1 );

( function () {

    Disciple_Tools_Notifications::process_new_notifications( get_the_ID() ); // removes new notifications for this post

    if ( !Disciple_Tools_Contacts::can_view( 'contacts', get_the_ID() )) {
        get_template_part( "403" );
        die();
    }
    $contact = Disciple_Tools_Contacts::get_contact( get_the_ID(), true );
    $contact_fields = Disciple_Tools_Contacts::get_contact_fields();

    $shared_with = Disciple_Tools_Contacts::get_shared_with_on_contact( get_the_ID() );
    $users = Disciple_Tools_Users::get_assignable_users_compact();
    get_header(); ?>

    <?php
    dt_print_breadcrumbs(
        [
            [ home_url( '/' ), __( "Dashboard" ) ],
            [ home_url( '/' ) . "contacts/", __( "Contacts" ) ],
        ],
        get_the_title(),
        true,
        true,
        current_user_can( "assign_any_contacts" ),
        isset( $contact->fields["requires_update"] ) && $contact->fields["requires_update"]["key"] === "yes"
    ); ?>

    <div id="content">
        <span id="contact-id" style="display: none"><?php echo get_the_ID()?></span>
        <span id="post-id" style="display: none"><?php echo get_the_ID()?></span>
        <span id="post-type" style="display: none">contact</span>

        <div id="inner-content" class="grid-x grid-margin-x grid-margin-y">

            <div class="small-12 cell bordered-box grid-x grid-margin-x">
                <div class="cell shrink center-items">
                    <i class="fi-torso large"></i>
                </div>
                <div class="cell shrink center-items">
                    <span class="item-details-header title" ><?php the_title_attribute(); ?></span>
                </div>
                <div class="shrink cell">
                    <label for="overall_status"><strong><?php esc_html_e( "Status", 'disciple_tools' ) ?></strong></label>
                    <select id="overall_status" class="select-field" style="margin-bottom:0px;">
                    <?php foreach ($contact_fields["overall_status"]["default"] as $key => $value){
                        if ( $contact->fields["overall_status"]["key"] === $key ) {
                            ?>
                            <option value="<?php echo esc_html( $key ) ?>" selected><?php echo esc_html( $value ); ?></option>
                        <?php } else { ?>
                            <option value="<?php echo esc_html( $key ) ?>"><?php echo esc_html( $value ); ?></option>
                        <?php } ?>
                    <?php } ?>
                    </select>
                    <span id="reason">
                        <?php
                        if ( $contact->fields["overall_status"]["key"] === "paused" &&
                             isset( $contact->fields["reason_paused"] )){
                            echo '(' . esc_html( $contact->fields["reason_paused"]["label"] ) . ')';
                        } else if ( $contact->fields["overall_status"]["key"] === "closed" &&
                                    isset( $contact->fields["reason_closed"] )){
                            echo '(' . esc_html( $contact->fields["reason_closed"]["label"] ) . ')';
                        } else if ( $contact->fields["overall_status"]["key"] === "unassignable" &&
                                    isset( $contact->fields["reason_unassignable"] )){
                            echo '(' . esc_html( $contact->fields["reason_unassignable"]["label"] ) . ')';
                        }
                        ?>
                    </span>

                </div>
                <div class="cell auto center-items show-for-large">
                    <?php get_template_part( 'dt-assets/parts/contact', 'quick-buttons' ); ?>
                </div>

            </div>

            <main id="main" class="xlarge-7 large-7 medium-12 small-12 cell" role="main" style="padding:0">
                <div id="errors"></div>
                <div class="grid-x  grid-margin-x grid-margin-y">
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

                    <?php get_template_part( 'dt-assets/parts/contact', 'details' ); ?>

                    <section id="relationships" class="xlarge-6 large-12 medium-6 cell">
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

                    <section id="faith" class="xlarge-6 large-12 medium-6 cell">
                    <div class="bordered-box">
                        <label class="section-header"><?php esc_html_e( 'Progress', 'disciple_tools' )?></label>
                        <div class="section-subheader"><?php esc_html_e( 'Seeker Path', 'disciple_tools' )?></div>

                        <select class="select-field" id="seeker_path" style="margin-bottom: 0px">
                        <?php

                        foreach ($contact_fields["seeker_path"]["default"] as $key => $value){
                            if ( $contact->fields["seeker_path"]["key"] === $key ) {
                                ?>
                                <option value="<?php echo esc_html( $key ) ?>" selected><?php echo esc_html( $value ); ?></option>
                            <?php } else { ?>
                                <option value="<?php echo esc_html( $key ) ?>"><?php echo esc_html( $value ); ?></option>
                            <?php }
                        }
                        $keys = array_keys( $contact_fields["seeker_path"]["default"] );
                        $path_index = array_search( $contact->fields["seeker_path"]["key"], $keys ) ?? 0;
                        $percentage = $path_index / ( sizeof( $keys ) -1 ) *100
                        ?>
                        </select>
                        <div class="progress" role="progressbar" tabindex="0" aria-valuenow="<?php echo 4 ?>" aria-valuemin="0" aria-valuetext="50 percent" aria-valuemax="100">
                            <div id="seeker-progress" class="progress-meter" style="width: <?php echo esc_html( $percentage ) ?>%"></div>
                        </div>

                        <div class="section-subheader"><?php esc_html_e( 'Faith Milestones', 'disciple_tools' )?></div>
                        <div class="small button-group" style="display: inline-block">

                            <?php foreach ( $contact_fields as $field => $val ): ?>
                                <?php
                                if (strpos( $field, "milestone_" ) === 0) {
                                    $class = ( isset( $contact->fields[ $field ] ) && $contact->fields[ $field ]['key'] === 'yes' ) ?
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
                            <div class="baptism_date"><input type="text" data-date-format='yy-mm-dd' value="<?php echo esc_html( $contact->fields["baptism_date"] ?? '' )?>" id="baptism-date-picker"></div>
                        </div>

                        <div class="section-subheader"><?php echo esc_html( $contact_fields["bible_mailing"]["name"] ) ?></div>
                        <select id="bible_mailing" class="select-field">
                            <?php
                            foreach ( $contact_fields["bible_mailing"]["default"] as $key => $value ) {
                                if ( isset( $contact->fields["bible_mailing"] ) &&
                                    $contact->fields["bible_mailing"]["key"] === $key ){
                                    echo '<option value="'. esc_html( $key ) . '" selected>' . esc_html( $value ) . '</option>';
                                } else {
                                    echo '<option value="'. esc_html( $key ) . '">' . esc_html( $value ). '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                </section>
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
                ?>
                <option value="<?php echo esc_attr( $reason_key )?>"> <?php echo esc_html( $reason_label )?></option>
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
                    <?php if ( ( $contact->fields["reason_paused"]["key"] ?? "" ) === $reason_key ){echo "selected";} ?>>
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
                    <?php if ( ( $contact->fields["unassignable_paused"]["key"] ?? "" ) === $reason_key ){echo "selected";} ?>>
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

    <?php
} )();

get_footer();
