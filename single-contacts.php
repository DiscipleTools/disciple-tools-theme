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
                        <?php get_template_part( 'parts/contact', 'quick-buttons' ); ?>

                        <div style="text-align: center">
                            <a class="button small" href="#comment-activity-section" style="margin-bottom: 0">
                            	<?php esc_html_e( 'View Comments', 'disciple_tools' )?>
                            </a>
                        </div>
                    </div>
                </section>
                <?php get_template_part( 'parts/contact', 'details' ); ?>

                <section id="relationships" class="xlarge-6 large-12 medium-6 cell">
                    <div class="bordered-box last-typeahead-in-section">
                        <button class=" float-right" onclick="edit_connections()"><i class="fi-pencil"></i> Edit
                        </button>
                        <h3 class="section-header"><?php esc_html_e( 'Connections', 'disciple_tools' )?></h3>
                        <div class="section-subheader"><?php esc_html_e( 'Groups', 'disciple_tools' )?></div>
                        <ul class="groups-list">
                            <?php
                            $ids = [];
                            foreach ( $contact->fields["groups"] as $value ) {
                                $ids[] = $value->ID;
                                ?>
                                <li class="<?php esc_html_e( $value->ID, 'disciple_tools' )?>">
                                    <a href="<?php esc_html_e( $value->permalink, 'disciple_tools' )?>">
                                    	<?php esc_html_e( $value->post_title, 'disciple_tools' )?>
                                	</a>
                                    <button class="details-remove-button connections-edit"
                                            onclick="remove_item( <?php esc_html_e( get_the_ID(), 'disciple_tools' )?>,  'groups', <?php esc_html_e( $value->ID, 'disciple_tools' )?> )">
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
                            "baptized_by" => "Baptized By",
                            "baptized" => "Baptized",
                            "coached_by" => "Coached By",
                            "coaching" => "Coaching"
                        ];
                        foreach ( $connections as $connection => $connection_label ) {
                            ?>


                            <div class="section-subheader"><?php esc_html_e( $connection_label, 'disciple_tools' )?></div>
                            <ul class="<?php esc_html_e( $connection, 'disciple_tools' )?>-list">
                                <?php
                                $ids = [];
                                foreach ( $contact->fields[ $connection ] as $value ) {
                                    $ids[] = $value->ID;
                                    ?>
                                    <li class="<?php esc_html_e( $value->ID, 'disciple_tools' ) ?>">
                                        <a href="<?php esc_html_e( $value->permalink, 'disciple_tools' ) ?>"><?php esc_html_e( $value->post_title, 'disciple_tools' ) ?></a>
                                        <button class="details-remove-button connections-edit"
                                                onclick="remove_item(<?php esc_html_e( get_the_ID(), 'disciple_tools' )?>,  '<?php esc_html_e( $connection, 'disciple_tools' )?>', <?php esc_html_e( $value->ID, 'disciple_tools' )?>)">
                                            <?php esc_html_e( 'Remove', 'disciple_tools' )?>
                                        </button>
                                    </li>
                                <?php } ?>
                            </ul>
                            <div class="connections-edit">
                                <label for="<?php esc_html_e( $connection, 'disciple_tools' )?>"><?php esc_html_e( 'Add', 'disciple_tools' )?> <?php esc_html_e( $connection_label, 'disciple_tools' )?>
                                    :</label>
                                <div id="<?php esc_html_e( $connection, 'disciple_tools' )?>" class="scrollable-dropdown-menu">
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
                                <option value="<?php esc_html_e( $key, 'disciple_tools' )?>" selected><?php esc_html_e( $value, 'disciple_tools' )?></option>
                            <?php } else { ?>
                                <option value="<?php esc_html_e( $key, 'disciple_tools' )?>"><?php esc_html_e( $value, 'disciple_tools' )?></option>
                            <?php }
                        };
                        $keys = array_keys( $contact_fields["seeker_path"]["default"] );
                        $path_index = array_search( $contact->fields["seeker_path"]["key"], $keys ) ?? 0;
                        $percentage = $path_index / (sizeof( $keys ) -1) *100
                        ?>
                        </select>
                        <div class="progress" role="progressbar" tabindex="0" aria-valuenow="<?php echo 4 ?>" aria-valuemin="0" aria-valuetext="50 percent" aria-valuemax="100">
                          <div id="seeker-progress" class="progress-meter" style="width: <?php esc_html_e( $percentage, 'disciple_tools' )?>%"></div>
                        </div>

                        <div class="section-subheader"><?php esc_html_e( 'Faith Milestones', 'disciple_tools' )?></div>
                        <div class="small button-group" style="display: inline-block">

                            <?php foreach ( $contact_fields as $field => $val ): ?>
                                <?php
                                if (strpos( $field, "milestone_" ) === 0) {
                                    $class = ( isset( $contact->fields[ $field ] ) && $contact->fields[ $field ]['key'] === 'yes' ) ?
                                        "selected-select-button" : "empty-select-button";
                                ?>
                                    <button onclick="save_seeker_milestones( <?php esc_html_e( get_the_ID(), 'disciple_tools' )?> , '<?php esc_html_e( $field, 'disciple_tools' )?>')"
                                            id="<?php esc_html_e( $field, 'disciple_tools' )?>"
                                            class="<?php esc_html_e( $class, 'disciple_tools' )?> select-button button ">
                                        <?php esc_html_e( $contact_fields[ $field ]["name"], 'disciple_tools' )?>
                                    </button>
                                <?php }?>
                            <?php endforeach; ?>
                        </div>

                        <div class="baptism_date">
                            <div class="section-subheader"><?php esc_html_e( 'Baptism Date', 'disciple_tools' )?></div>
                            <div class="baptism_date"><input type="text" data-date-format='yy-mm-dd' value="<?php esc_html_e( $contact->fields["baptism_date"] ?? '', 'disciple_tools' )?>" id="baptism-date-picker"></div>
                        </div>

                        <div class="section-subheader"><?php esc_html_e( $contact_fields["bible_mailing"]["name"], 'disciple_tools' )?></div>
                        <select id="bible_mailing" class="select-field">
                            <?php
                            foreach ( $contact_fields["bible_mailing"]["default"] as $key => $value ) {
                                if ( isset( $contact->fields["bible_mailing"] ) &&
                                    $contact->fields["bible_mailing"]["key"] === $key ){
                                        echo '<option value="'. esc_html( $key, 'disciple_tools' ) . '" selected>' . esc_html( $value, 'disciple_tools' ) . '</option>';
                                } else {
                                    echo '<option value="'. esc_html( $key, 'disciple_tools' ) . '">' . esc_html( $value, 'disciple_tools' ). '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                </section>

                <section id="availability" class="medium-6 cell" style="display: none">
                    <div class="bordered-box">
                        <label class="section-header"><?php esc_html_e( 'Availability', 'disciple_tools' )?></label>
                        <div class="row" style="display: flex; justify-content: center">
                            <div style="flex: 0 1 13%"><?php esc_html_e( 'Sun', 'disciple_tools' )?></div>
                            <div style="flex: 0 1 13%"><?php esc_html_e( 'Mon', 'disciple_tools' )?></div>
                            <div style="flex: 0 1 13%"><?php esc_html_e( 'Tue', 'disciple_tools' )?></div>
                            <div style="flex: 0 1 13%"><?php esc_html_e( 'Wed', 'disciple_tools' )?></div>
                            <div style="flex: 0 1 13%"><?php esc_html_e( 'Thu', 'disciple_tools' )?></div>
                            <div style="flex: 0 1 13%"><?php esc_html_e( 'Fri', 'disciple_tools' )?></div>
                            <div style="flex: 0 1 13%"><?php esc_html_e( 'Sat', 'disciple_tools' )?></div>
                        </div>
                        <div class="row" style="display: flex; justify-content: center">
                            <div style="flex: 0 1 13%"><?php esc_html_e( 'Morn', 'disciple_tools' )?></div>
                            <div style="flex: 0 1 13%"><?php esc_html_e( 'Morn', 'disciple_tools' )?></div>
                            <div style="flex: 0 1 13%"><?php esc_html_e( 'Morn', 'disciple_tools' )?></div>
                            <div style="flex: 0 1 13%"><?php esc_html_e( 'Morn', 'disciple_tools' )?></div>
                            <div style="flex: 0 1 13%"><?php esc_html_e( 'Morn', 'disciple_tools' )?></div>
                            <div style="flex: 0 1 13%"><?php esc_html_e( 'Morn', 'disciple_tools' )?></div>
                            <div style="flex: 0 1 13%"><?php esc_html_e( 'Morn', 'disciple_tools' )?></div>
                        </div>
                        <div class="row" style="display: flex; justify-content: center">
                            <div style="flex: 0 1 13%"><?php esc_html_e( 'Lunch', 'disciple_tools' )?></div>
                            <div style="flex: 0 1 13%"><?php esc_html_e( 'Lunch', 'disciple_tools' )?></div>
                            <div style="flex: 0 1 13%"><?php esc_html_e( 'Lunch', 'disciple_tools' )?></div>
                            <div style="flex: 0 1 13%"><?php esc_html_e( 'Lunch', 'disciple_tools' )?></div>
                            <div style="flex: 0 1 13%"><?php esc_html_e( 'Lunch', 'disciple_tools' )?></div>
                            <div style="flex: 0 1 13%"><?php esc_html_e( 'Lunch', 'disciple_tools' )?></div>
                            <div style="flex: 0 1 13%"><?php esc_html_e( 'Lunch', 'disciple_tools' )?></div>
                        </div>
                        <div class="row" style="display: flex; justify-content: center">
                            <div style="flex: 0 1 13%"><?php esc_html_e( 'Aftr', 'disciple_tools' )?></div>
                            <div style="flex: 0 1 13%"><?php esc_html_e( 'Aftr', 'disciple_tools' )?></div>
                            <div style="flex: 0 1 13%"><?php esc_html_e( 'Aftr', 'disciple_tools' )?></div>
                            <div style="flex: 0 1 13%"><?php esc_html_e( 'Aftr', 'disciple_tools' )?></div>
                            <div style="flex: 0 1 13%"><?php esc_html_e( 'Aftr', 'disciple_tools' )?></div>
                            <div style="flex: 0 1 13%"><?php esc_html_e( 'Aftr', 'disciple_tools' )?></div>
                            <div style="flex: 0 1 13%"><?php esc_html_e( 'Aftr', 'disciple_tools' )?></div>
                        </div>
                        <div class="row" style="display: flex; justify-content: center">
                            <div style="flex: 0 1 13%"><?php esc_html_e( 'Night', 'disciple_tools' )?></div>
                            <div style="flex: 0 1 13%"><?php esc_html_e( 'Night', 'disciple_tools' )?></div>
                            <div style="flex: 0 1 13%"><?php esc_html_e( 'Night', 'disciple_tools' )?></div>
                            <div style="flex: 0 1 13%"><?php esc_html_e( 'Night', 'disciple_tools' )?></div>
                            <div style="flex: 0 1 13%"><?php esc_html_e( 'Night', 'disciple_tools' )?></div>
                            <div style="flex: 0 1 13%"><?php esc_html_e( 'Night', 'disciple_tools' )?></div>
                            <div style="flex: 0 1 13%"><?php esc_html_e( 'Night', 'disciple_tools' )?></div>
                        </div>
                    </div>
                </section>

            </main> <!-- end #main -->

            <aside class="auto cell grid-x grid-margin-x">
                <section class="xlarge-5 large-5 medium-12 small-12 cell bordered-box comment-activity-section"
                         id="comment-activity-section">
                    <?php get_template_part( 'parts/contact', 'quick-buttons' ); ?>
                    <?php get_template_part( 'parts/loop', 'activity-comment' ); ?>
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
                <li class="<?php esc_html_e( $user['user_id'], 'disciple_tools' )?>"> <?php esc_html_e( $user['display_name'], 'disciple_tools' )?>
                    <button class="details-remove-button share" data-id="<?php echo esc_attr( $user['user_id'], 'disciple_tools' ); ?>">
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
                        <option value="<?php esc_html_e( $user["ID"], 'disciple_tools' )?>"><?php esc_html_e( $user['name'], 'disciple_tools' )?></option>
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
