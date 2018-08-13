<?php
/*
Template Name: Workers
*/

// Get teams and members connected to current user
$dt_user_team_members_list = dt_get_user_team_members_list( get_current_user_id() );

?>

<?php get_header(); ?>

<div id="content">

    <div id="inner-content" class="grid-x grid-margin-x align-center">

        <div class="medium-8 small-12 cell ">

            <?php
            if ( $dt_user_team_members_list ) {

                foreach ( $dt_user_team_members_list as $dt_team_list ) { ?>

                    <div class="bordered-box">

                        <span class="section-header"><?php echo esc_html( $dt_team_list['team_name'] )?></span>

                        <hr size="1" style="max-width:100%"/>

                        <div class="grid-container fluid">

                            <?php if ( !empty( $dt_team_list['team_members'] ) ) { ?>

                                <div class="grid-x grid-margin-x">

                                    <?php foreach ( $dt_team_list['team_members'] as $dt_member ) {
                                        // reset variables
                                        $dt_user = '';
                                        $dt_user_meta = [];
                                        $dt_user_fields = [];
                                        $dt_locations = [];

                                        // get member data
                                        $dt_user = get_user_by( 'id', $dt_member['ID'] ); // Returns WP_User object
                                        $dt_user_meta = get_user_meta( $dt_member['ID'] ); // Full array of user meta data
                                        $dt_user_fields = dt_build_user_fields_display( $dt_user_meta ); // Compares the site settings in the config area with the fields available in the user meta table.
                                        $dt_locations = dt_get_user_locations_list( $dt_member['ID'] ); // returns an array of locations for the member
                                        ?>

                                        <div class="cell small-3">

                                            <!-- Card -->
                                            <div class="card" data-open="reveal-<?php echo esc_html( $dt_member['ID'] )?>">

                                                <div class="card-image">

                                                    <?php echo get_avatar( $dt_member['ID'], '250' ); ?>

                                                </div>
                                                <div class="card-section center">

                                                    <a data-open="reveal-<?php echo esc_attr( $dt_member['ID'], 'disciple_tools' ); ?>">
                                                        <?php
                                                        if ( !empty( $dt_user->first_name ) ) {
                                                            echo esc_html( $dt_user->first_name ) . ' ' . esc_html( $dt_user->last_name );
                                                        } else {
                                                            echo esc_html( $dt_user->display_name );
                                                        } ?>
                                                    </a><br>

                                                    <?php
                                                    if ( isset( $dt_user_meta['dt_avalability'] ) && $dt_user_meta['dt_avalability'] == false ){
                                                        ?> <strong><?php esc_html_e( 'Not Available', 'disciple_tools' ) ?></strong>
                                                    <?php } else { ?>
                                                    <strong><?php esc_html_e( 'Not Available', 'disciple_tools' ) ?></strong>
                                                    <?php } ?>
                                                </div>

                                            </div>

                                            <!-- Reveal -->
                                            <div class="reveal" id="reveal-<?php echo esc_html( $dt_member['ID'] ); ?>" data-reveal>

                                                <h1>
                                                <?php
                                                if ( !empty( $dt_user->first_name ) ) {
                                                    echo esc_html( $dt_user->first_name ) . ' ' . esc_html( $dt_user->last_name );
                                                } else {
                                                    echo esc_html( $dt_user->display_name );
                                                }
                                                ?>
                                                </h1>

                                                <p><?php echo get_avatar( $dt_member['ID'], '150' ); ?></p>
                                                <p>
                                                    <strong>
                                                    <?php ( isset( $dt_user_meta['dt_avalability'] ) && $dt_user_meta['dt_avalability'] == false ) ? esc_html_e( 'Not Available', 'disciple_tools' ) : esc_html_e( 'Available', 'disciple_tools' );  ?>
                                                    </strong>
                                                </p>

                                                <p>
                                                    <strong><?php esc_html_e( 'Username', 'disciple_tools' )?></strong><br>
                                                    <?php echo esc_html( $dt_user->user_login )?>
                                                </p>

                                                <p>
                                                    <strong><?php esc_html_e( 'Nickname', 'disciple_tools' )?></strong><br>
                                                    <?php echo esc_html( $dt_user->nickname )?>
                                                </p>

                                                <p><strong><?php esc_html_e( 'Email', 'disciple_tools' )?></strong></p>
                                                <ul>
                                                    <?php
                                                    echo '<li><a href="mailto:' . esc_attr( $dt_user->user_email, 'disciple_tools' ) . '">' . esc_html( $dt_user->user_email ) . '</a> (System Email)</li>';
                                                    foreach ( $dt_user_fields as $dt_field ) {
                                                        if ( $dt_field['type'] == 'email' && !empty( $dt_field['value'] ) ) {
                                                            echo '<li><a href="mailto:' . esc_html( $dt_field['value'] ) . '" target="_blank">' . esc_html( $dt_field['value'] ) . '</a> (' . esc_html( $dt_field['label'] ) . ')</li>';
                                                        }
                                                    }
                                                    ?>
                                                </ul>

                                                <strong><?php esc_html_e( 'Phone', 'disciple_tools' )?></strong>
                                                <ul>
                                                    <?php
                                                    foreach ( $dt_user_fields as $dt_field ) {
                                                        if ( $dt_field['type'] == 'phone' && !empty( $dt_field['value'] ) ) {
                                                            echo '<li>' . esc_html( $dt_field['value'] ) . ' (' . esc_html( $dt_field['label'] ) . ')</li>';
                                                        }
                                                    }
                                                    ?>
                                                </ul>

                                                <strong><?php esc_html_e( 'Address', 'disciple_tools' )?></strong>
                                                <ul>
                                                    <?php
                                                    foreach ( $dt_user_fields as $dt_field ) {
                                                        if ( $dt_field['type'] == 'address' && !empty( $dt_field['value'] ) ) {
                                                            echo '<li>' . esc_html( $dt_field['value'] ) . ' (' . esc_html( $dt_field['label'] ) . ')</li>';
                                                        }
                                                    }
                                                    ?>
                                                </ul>

                                                <strong><?php esc_html_e( 'Social', 'disciple_tools' )?></strong>
                                                <ul>
                                                    <?php
                                                    foreach ( $dt_user_fields as $dt_field ) {
                                                        if ( $dt_field['type'] == 'social' && !empty( $dt_field['value'] ) ) {
                                                            echo '<li>' . esc_html( $dt_field['value'] ) . ' (' . esc_html( $dt_field['label'] ) . ')</li>';
                                                        }
                                                    }
                                                    ?>
                                                </ul>

                                                <strong><?php esc_html_e( 'Other', 'disciple_tools' )?></strong>
                                                <ul>
                                                    <?php
                                                    foreach ( $dt_user_fields as $dt_field ) {
                                                        if ( $dt_field['type'] == 'other' && !empty( $dt_field['value'] ) ) {
                                                            echo '<li>' . esc_html( $dt_field['value'] ) . ' (' . esc_html( $dt_field['label'] ) . ')</li>';
                                                        }
                                                    }
                                                    ?>
                                                </ul>

                                                <strong><?php esc_html_e( 'Locations', 'disciple_tools' )?></strong>
                                                <ul>
                                                    <?php foreach ( $dt_locations as $dt_location ) {
                                                        echo '<li>' .  esc_html( $dt_location->post_title ) . '</li>';
} ?>
                                                </ul>

                                                <strong><?php esc_html_e( 'Biography', 'disciple_tools' )?></strong>
                                                <p><?php echo esc_html( $dt_user->user_description ); ?></p>

                                                <button class="close-button" data-close aria-label="Close modal"
                                                        type="button">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>

                                        </div>

                                    <?php } // end member foreach loop ?>

                                </div>

                            <?php } else { // end if members exist ?>

                                <div class="grid-x grid-margin-x grid-margin-y align-center-middle">

                                    <?php esc_html_e( 'Have no other team members on this team', 'disciple_tools' )?>.

                                </div>


                            <?php } // end else members ?>

                        </div>

                    </div>

                <?php } // end team loop ?>

            <?php } else { // end if current user is member of any teams ?>

                <div class="bordered-box center">

                    <div class="grid-x grid-margin-x grid-margin-y align-center-middle">

                        <?php esc_html_e( 'You are not part of any teams', 'disciple_tools' )?>.

                    </div>

                </div>

            <?php } // end else ?>

        </div>

    </div> <!-- end #inner-content -->

</div> <!-- end #content -->


<?php get_footer(); ?>
