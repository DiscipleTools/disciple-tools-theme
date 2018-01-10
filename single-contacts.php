<?php
declare( strict_types=1 );

( function () {

    Disciple_Tools_Notifications::process_new_notifications( get_the_ID() ); // removes new notifications for this post

    $contact = Disciple_Tools_Contacts::get_contact( get_the_ID(), true );
    $contact_fields = Disciple_Tools_Contacts::get_contact_fields();
    if ( !Disciple_Tools_Contacts::can_view( 'contacts', get_the_ID() )) {
        return wp_redirect( "not-found" );
    }

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
        true
    ); ?>

    <!-- I'm not sure why this is indented -->
    <div id="content">

        <div id="inner-content" class="grid-x grid-margin-x grid-margin-y">



            <main id="main" class="xlarge-7 large-7 medium-12 small-12 cell grid-x grid-margin-x grid-margin-y" role="main" style="padding:0">
                <div id="errors"></div>
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
                    <div class="bordered-box last-typeahead-in-section">
                        <button class=" float-right" onclick="edit_connections()"><i class="fi-pencil"></i>
                            <?php esc_html_e( "Edit", 'disciple_tools' ) ?>
                        </button>
                        <h3 class="section-header"><?php esc_html_e( 'Connections', 'disciple_tools' )?></h3>
                        <div class="section-subheader"><?php esc_html_e( 'Groups', 'disciple_tools' )?></div>
                        <ul class="groups-list">
                            <?php
                            $ids = [];
                            foreach ( $contact->fields["groups"] as $value ) {
                                $ids[] = $value->ID;
                                ?>
                                <li class="<?php echo esc_html( $value->ID ) ?>">
                                    <a href="<?php echo esc_html( $value->permalink ) ?>"><?php echo esc_html( $value->post_title ) ?></a>
                                    <button class="details-remove-button connections-edit"
                                            onclick="remove_item( <?php echo esc_html( get_the_ID() ) ?>,  'groups', <?php echo esc_html( $value->ID ) ?> )">
                                        <?php esc_html_e( 'Remove', 'disciple_tools' )?>
                                    </button>
                                </li>
                            <?php } ?>
                        </ul>
                        <div class="connections-edit">
                            <label for="groups"><?php esc_html_e( 'Add Group', 'disciple_tools' )?>:</label>
                            <div id="groups">
                                <input class="typeahead" type="text" placeholder="Type to search groups">
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
                            <ul class="<?php echo esc_html( $connection ) ?>-list">
                                <?php
                                $ids = [];
                                foreach ( $contact->fields[ $connection ] as $value ) {
                                    $ids[] = $value->ID;
                                    ?>
                                    <li class="<?php echo esc_html( $value->ID ) ?>">
                                        <a href="<?php echo esc_html( $value->permalink ) ?>"><?php echo esc_html( $value->post_title ) ?></a>
                                        <button class="details-remove-button connections-edit"
                                                onclick="remove_item(<?php echo esc_html( get_the_ID() ) ?>,  '<?php echo esc_html( $connection ) ?>', <?php echo esc_html( $value->ID ) ?>)">
                                            <?php esc_html_e( 'Remove', 'disciple_tools' )?>
                                        </button>
                                    </li>
                                <?php } ?>
                            </ul>
                            <div class="connections-edit">
                                <label for="<?php echo esc_html( $connection ) ?>"><?php esc_html_e( "Add", 'disciple_tools' ) ?> <?php echo esc_html( $connection_label ) ?>:</label>
                                <div id="<?php echo esc_html( $connection ) ?>" class="scrollable-dropdown-menu">
                                    <input class="typeahead" type="text"
                                           placeholder="Type to search contacts">
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
                        };
                        $keys = array_keys( $contact_fields["seeker_path"]["default"] );
                        $path_index = array_search( $contact->fields["seeker_path"]["key"], $keys ) ?? 0;
                        $percentage = $path_index / (sizeof( $keys ) -1) *100
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

            </main> <!-- end #main -->

            <aside class="auto cell grid-x grid-margin-x">
                <section class="xlarge-5 large-5 medium-12 small-12 cell bordered-box comment-activity-section"
                         id="comment-activity-section">
                    <?php get_template_part( 'dt-assets/parts/contact', 'quick-buttons' ); ?>
                    <?php get_template_part( 'dt-assets/parts/loop', 'activity-comment' ); ?>
                </section>

            </aside>

        </div> <!-- end #inner-content -->

    </div> <!-- end #content -->

    <div class="reveal" id="share-contact-modal" data-reveal>

        <p class="lead"><?php esc_html_e( 'Share settings', 'disciple_tools' )?></p>
        <h6><?php esc_html_e( 'Already sharing with', 'disciple_tools' )?></h6>

        <ul id="shared-with-list">
            <?php
            foreach ( $shared_with as $user ) { ?>
                <li class="<?php echo esc_html( $user['user_id'] ) ?>"> <?php echo esc_html( $user['display_name'] )?>
                    <button class="details-remove-button share" data-id="<?php echo esc_attr( $user['user_id'] ); ?>">
                        <?php esc_html_e( 'Unshare', 'disciple_tools' )?>
                    </button>
                </li>
            <?php } ?>
        </ul>

        <p>
            <label><?php esc_html_e( 'Share this contact with the following user', 'disciple_tools' )?>:
                <select class="share-with-select" id="share-with">
                    <option value="0"></option>
                    <?php foreach ( $users as $user ) { ?>
                        <option value="<?php echo esc_html( $user["ID"] ) ?>"><?php echo esc_html( $user['name'] ) ?></option>
                    <?php } ?>
                </select>
            </label>
        </p>

        <div class="grid-x">
            <button class="button button-cancel clear" data-close aria-label="Close reveal" type="button">
                <?php esc_html_e( 'Cancel', 'disciple_tools' )?>
            </button>
            <button class="button" type="button" id="add-shared-button">
                <?php esc_html_e( 'Share', 'disciple_tools' )?>
            </button>
            <button class="close-button" data-close aria-label="Close modal" type="button">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    </div>

    <?php
})();

get_footer();
