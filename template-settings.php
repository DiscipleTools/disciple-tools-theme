<?php
/*
Template Name: Settings
*/
dt_please_log_in();
/* Process $_POST content */
// We're not checking the nonce here because update_user_contact_info will
// phpcs:ignore
if ( isset( $_POST['user_update_nonce'] ) ) {
    Disciple_Tools_Users::update_user_contact_info();
}

/* Build variables for page */
$dt_user = wp_get_current_user(); // Returns WP_User object
$dt_user_meta = get_user_meta( $dt_user->ID ); // Full array of user meta data
$dt_user_contact_id = dt_get_associated_user_id( $dt_user->ID, 'user' );

$dt_user_fields = dt_build_user_fields_display( $dt_user_meta ); // Compares the site settings in the config area with the fields available in the user meta table.
$dt_site_notification_defaults = dt_get_site_notification_defaults(); // Array of site default settings
$dt_available_languages = get_available_languages( get_template_directory() .'/dt-assets/translation' )

?>

<?php get_header(); ?>

<div id="content" class="template-settings">

    <div id="inner-content" class="grid-x grid-margin-x grid-margin-y">

        <div class="large-3 medium-3 small-12 cell " data-sticky-container>

            <section id="" class="small-12 cell sticky" data-sticky data-top-anchor="settings" data-sticky-on="medium" data-margin-top="5">

                <div class="bordered-box">

                    <ul class="menu vertical expanded" data-smooth-scroll data-offset="100">
                        <li><a href="#profile"><?php esc_html_e( 'Profile', 'disciple_tools' )?></a></li>
                        <li><a href="#locations"><?php esc_html_e( 'Locations', 'disciple_tools' )?></a></li>
                        <li><a href="#notifications"><?php esc_html_e( 'Notifications', 'disciple_tools' )?></a></li>
                        <li><a href="#availability"><?php esc_html_e( 'Availability', 'disciple_tools' )?></a></li>
                    </ul>

                </div>

                <br>

            </section>

        </div>

        <div class="large-9 medium-9 small-12 cell ">

            <div class="grid-margin-x grid-x grid-margin-y" id="settings">

                <div class="small-12 cell">

                    <div class="bordered-box cell" id="profile" data-magellan-target="profile">

                        <button class="help-button float-right" data-section="profile-help-text">
                            <img class="help-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>"/>
                        </button>

                        <span class="section-header"><?php esc_html_e( 'Your Profile', 'disciple_tools' )?></span>

                        <button class="float-right" data-open="edit-profile"><i class="fi-pencil"></i> <?php esc_html_e( 'Edit', 'disciple_tools' )?></button>

                        <hr size="1" style="max-width:100%"/>


                        <div class="grid-x grid-margin-x grid-padding-x grid-padding-y ">

                            <div class="small-12 medium-4 cell">

                                <p><?php echo get_avatar( $dt_user->ID, '150' ); ?></p>

                                <p>
                                    <strong><?php esc_html_e( 'Username', 'disciple_tools' )?></strong><br>
                                    <?php echo esc_html( $dt_user->user_login ); ?>
                                </p>

                                <?php if ( ! empty( $dt_user->first_name ) || ! empty( $dt_user->last_name ) ) : ?>
                                    <p>
                                        <strong><?php esc_html_e( 'Name', 'disciple_tools' )?></strong><br>
                                        <?php echo esc_html( $dt_user->first_name ); ?> <?php echo esc_html( $dt_user->last_name ); ?>
                                    </p>
                                <?php endif; ?>

                                <?php if ( ! empty( $dt_user->nickname ) ) : ?>
                                    <p>
                                        <strong dir="auto"><?php esc_html_e( 'Nickname (Display Name)', 'disciple_tools' )?></strong><br>
                                        <span dir="auto"><?php echo esc_html( $dt_user->nickname ); ?></span>
                                    </p>
                                <?php endif; ?>

                                <p>
                                    <strong><?php esc_html_e( 'Roles', 'disciple_tools' ); ?></strong><br>
                                    <?php echo esc_html( implode( ", ", wp_get_current_user()->roles ) ); ?>
                                </p>

                            </div>
                            <div class="small-12 medium-4 cell" style="border-left: 1px solid lightgrey; padding-left: 1em;">

                                <p><strong><?php esc_html_e( 'Email', 'disciple_tools' )?></strong></p>
                                <ul>
                                    <?php
                                    echo '<li><a href="mailto:' . esc_attr( $dt_user->user_email ) . '">' . esc_html( $dt_user->user_email ) . '</a><br><span class="text-small"> (System Email)</span></li>';
                                    foreach ( $dt_user_fields as $dt_field ) {
                                        if ( $dt_field['type'] == 'email' && !empty( $dt_field['value'] ) ) {
                                            echo '<li><a href="mailto:' . esc_html( $dt_field['value'] ) . '" target="_blank">' . esc_html( $dt_field['value'] ) . '</a> <br><span class="text-small">(' . esc_html( $dt_field['label'] ) . ')</span></li>';
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

                            </div>
                            <div class="small-12 medium-4 cell" style="border-left: 1px solid lightgrey; padding-left: 1em;">

                                <strong><?php esc_html_e( 'Language', 'disciple_tools' )?></strong>
                                <p>
                                <?php
                                if ( !empty( $dt_user->locale ) ){
                                    echo esc_html( $dt_user->locale );
                                } else {
                                    echo esc_html__( 'English', 'disciple_tools' );
                                }
                                ?></p>

                                <strong><?php esc_html_e( 'Biography', 'disciple_tools' )?></strong>
                                <p><?php echo esc_html( $dt_user->user_description ); ?></p>

                            </div>
                        </div>

                    </div> <!-- End Profile -->
                </div>

                <div class="small-12 cell">
                    <div class="bordered-box cell" id="locations" data-magellan-target="locations">
                        <button class="help-button float-right" data-section="locations-help-text">
                            <img class="help-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>"/>
                        </button>
                        <span class="section-header"><?php esc_html_e( 'Locations', 'disciple_tools' )?></span>
                        <hr size="1" style="max-width:100%"/>
                        <!-- Geocoding -->

                        <div id="manage_locations_section"></div>

                    </div>
                </div>

                <!-- Begin Notification-->
                <div class="small-12 cell">

                    <div class="bordered-box cell" id="notifications" data-magellan-target="notifications">
                        <button class="help-button float-right" data-section="notifications-help-text">
                            <img class="help-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>"/>
                        </button>
                        <span class="section-header"><?php esc_html_e( 'Notifications', 'disciple_tools' )?></span>
                        <hr size="1" style="max-width:100%"/>

                        <table class="form-table">
                            <thead>
                            <tr>
                                <td><?php esc_html_e( 'Type of Notification', 'disciple_tools' )?></td>
                                <?php foreach ( $dt_site_notification_defaults["channels"] as $channel_key => $channel_value ) : ?>
                                    <td><?php echo esc_html( $channel_value["label"] )?></td>
                                <?php endforeach; ?>
                            </tr>
                            </thead>
                            <tbody>
                                <?php foreach ( $dt_site_notification_defaults["types"] as $dt_notification_key => $dt_notification_default ) : ?>
                                <tr>
                                    <td class="tall-4"><?php echo esc_html( $dt_notification_default["label"] )?></td>
                                    <?php foreach ( $dt_site_notification_defaults["channels"] as $channel_key => $channel_value ) : ?>
                                    <td>
                                        <?php if ( $dt_notification_default[$channel_key] ) : ?>
                                            <div style="height:2em;"><?php esc_html_e( "required", 'disciple_tools' ) ?></div>
                                        <?php else :
                                            $channel_notification_key = $dt_notification_key . '_' . $channel_key;
                                            ?>
                                            <div class="switch">
                                                <input class="switch-input" id="<?php echo esc_html( $channel_notification_key ) ?>" type="checkbox"
                                                       name="<?php echo esc_html( $channel_notification_key ) ?>"
                                                       onclick="switch_preference( '<?php echo esc_html( $channel_notification_key ) ?>', 'notifications' );"
                                                    <?php ( isset( $dt_user_meta[$channel_notification_key ] ) && $dt_user_meta[$channel_notification_key ][0] == false ) ?
                                                        print esc_attr( '', 'disciple_tools' ) : print esc_attr( 'checked', 'disciple_tools' ); ?>>
                                                <label class="switch-paddle inactive" for="<?php echo esc_html( $channel_notification_key ) ?>">
                                                    <span class="show-for-sr"><?php echo esc_html( $dt_notification_default['label'] ) ?></span>
                                                </label>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <?php endforeach; ?>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <?php if ( current_user_can( "view_any_contacts" ) ): ?>
                        <p>
                            <strong><?php esc_html_e( 'Follow all contacts', 'disciple_tools' )?></strong>
                        </p>
                        <p><?php esc_html_e( "You will receive a notification for any update that happens in the system.", 'disciple_tools' ) ?></p>
                        <div class="switch large">
                            <input class="switch-input" id="follow_all" type="checkbox" name="follow_all"
                                   onclick="switch_preference('dt_follow_all');" <?php ( isset( $dt_user_meta['dt_follow_all'] ) && $dt_user_meta['dt_follow_all'][0] == true ) ? print esc_attr( 'checked' ) : print esc_attr( '' ); ?> />
                            <label class="switch-paddle" for="follow_all">
                                <span class="show-for-sr"><?php esc_html_e( 'Enable', 'disciple_tools' )?></span>
                                <span class="switch-active" aria-hidden="true"><?php esc_html_e( 'Yes', 'disciple_tools' )?></span>
                                <span class="switch-inactive" aria-hidden="false"><?php esc_html_e( 'No', 'disciple_tools' )?></span>
                            </label>
                        </div>
                        <?php endif; ?>


                    </div> <!-- End Notifications -->

                </div>

                <!-- Begin Availability-->
                <div class="small-12 cell">

                    <div class="bordered-box cell" id="availability" data-magellan-target="availability">
<!--                      <button class="help-button float-right" data-section="availability-help-text">-->
<!--                          <img class="help-icon" src="--><?php //echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?><!--"/>-->
<!--                      </button>-->

                        <!-- section header-->
                        <span class="section-header"><?php esc_html_e( 'Availability', 'disciple_tools' )?></span>
                        <hr size="1" style="max-width:100%"/>

                        <p><?php esc_html_e( "Set the dates you will be traveling or unavailable so the Dispatcher will know your availability to receive new contacts", 'disciple_tools' ) ?></p>

                        <p>
                            <strong>
                                <?php esc_html_e( "Schedule Travel or Dates Unavailable", 'disciple_tools' ) ?>
                            </strong>
                        </p>
                        <div style="display: flex; justify-content: flex-start; align-items: flex-end">
                            <div style="flex-shrink: 1">
                                <div class="section-subheader cell">
                                    <?php esc_html_e( 'Start Date', 'disciple_tools' )?>
                                </div>
                                <div class="start_date"><input type="text" class="date-picker" id="start_date"></div>
                            </div>
                            <div style="margin: 0 20px">
                                <div class="section-subheader cell">
                                    <?php esc_html_e( 'End Date', 'disciple_tools' )?>
                                </div>
                                <div class="end_date"><input type="text" class="date-picker" id="end_date"></div>
                            </div>
                            <div>
                                <button id="add_unavailable_dates" class="button" disabled><?php esc_html_e( "Add Unavailable dates", 'disciple_tools' ) ?></button>
                                <div id="add_unavailable_dates_spinner" style="display: inline-block" class="loading-spinner"></div>
                            </div>
                        </div>
                        <div >
                            <table class="hover stack-for-small striped">
                                <thead>
                                <tr>
                                    <th><?php esc_html_e( "Start Date", 'disciple_tools' ) ?></th>
                                    <th><?php esc_html_e( "End Date", 'disciple_tools' ) ?></th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody id="unavailable-list">
                                    <tr><td><?php esc_html_e( "No Travel Scheduled", 'disciple_tools' ) ?></td></tr>
                                </tbody>

                            </table>
                        </div>
                    </div> <!-- End Availability -->
                </div>


                <div class="reveal" id="edit-profile" data-reveal>
                    <button class="close-button" data-close aria-label="Close modal" type="button">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h2><?php esc_html_e( 'Edit', 'disciple_tools' )?></h2>

                    <div class="row column medium-12">

                        <form method="post">

                            <?php wp_nonce_field( "user_" . $dt_user->ID . "_update", "user_update_nonce", false, true ); ?>

                            <table class="table">

                                <tr>
                                    <td><?php echo get_avatar( $dt_user->ID, '32' ); ?></td>
                                    <td>
                                        <span data-tooltip data-click-open="true" class="top" tabindex="1"
                                              title="<?php esc_html_e( 'Disciple Tools System does not store images. For profile images we use Gravatar (Globally Recognized Avatar). If you have security concerns, we suggest not using a personal photo, but instead choose a cartoon, abstract, or alias photo to represent you.' ) ?>">
                                            <a href="http://gravatar.com" class="small"><?php esc_html_e( 'edit image on gravatar.com', 'disciple_tools' ) ?> <i class="fi-link"></i></a>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><?php esc_html_e( 'User Name', 'disciple_tools' )?> </td>
                                    <td><span data-tooltip data-click-open="true" class="top" tabindex="2" title="<?php esc_html_e( 'Username cannot be changed' ) ?>"><?php echo esc_html( $dt_user->user_login ); ?> <i class="fi-info primary-color" onclick="jQuery('#username-message').toggle()"></i></span>
                                        <span id="username-message" style="display: none; font-size: .7em;"><br><?php esc_html_e( 'Username cannot be changed' ) ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><?php esc_html_e( 'System Email', 'disciple_tools' )?></td>
                                    <td><span data-tooltip data-click-open="true" class="top" tabindex="3" title="<?php esc_html_e( 'User email can be changed by site administrator.' ) ?>">
                                        <input type="text" class="profile-input" id="user_email"
                                            name="user_email"
                                            value="<?php echo esc_html( $dt_user->user_email ); ?>"/></td>
                                    </td>
                                </tr>
                                <tr>
                                    <td><?php esc_html_e( 'Password', 'disciple_tools' )?></td>
                                    <td><span data-tooltip data-click-open="true" class="top" tabindex="1" title="<?php esc_html_e( 'Use this email reset form to create a new password.' ) ?>">
                                            <a href="/wp-login.php?action=lostpassword" target="_blank" rel="nofollow noopener">
                                                <?php esc_html_e( 'go to password change form' ) ?> <i class="fi-link"></i>
                                            </a>
                                        </span>
                                    </td>
                                </tr>

                                <tr>
                                    <td><label for="first_name"><?php esc_html_e( 'First Name', 'disciple_tools' )?></label></td>
                                    <td><input type="text" class="profile-input" id="first_name"
                                               name="first_name"
                                               value="<?php echo esc_html( $dt_user->first_name ); ?>"/></td>
                                </tr>
                                <tr>
                                    <td><label for="last_name"><?php esc_html_e( 'Last Name', 'disciple_tools' )?></label></td>
                                    <td><input type="text" class="profile-input" id="last_name" name="last_name"
                                               value="<?php echo esc_html( $dt_user->last_name ); ?>"/></td>
                                </tr>
                                <tr>
                                    <td><label for="nickname"><span dir="auto"><?php esc_html_e( 'Nickname (Display Name)', 'disciple_tools' )?></span></label></td>
                                    <td><input type="text" class="profile-input" id="nickname" name="nickname" dir="auto"
                                               value=" <?php echo esc_html( $dt_user->nickname ); ?>"/></td>
                                </tr>
                                <tr>
                                    <td><label for="nickname"><?php esc_html_e( 'Locations', 'disciple_tools' )?></label></td>
                                    <td><?php esc_html_e( '(Edit on contact record)', 'disciple_tools' )?>
                                        <a href="/contacts/<?php //@todo  ?>"><i class="fi-link"></i></a>
                                    </td>
                                </tr>

                                <?php // site defined fields
                                foreach ( $dt_user_fields as $dt_field ) {
                                    ?>
                                    <tr>
                                        <td>
                                            <label for="<?php echo esc_attr( $dt_field['key'] ) ?>"><?php echo esc_html( $dt_field['label'] ) ?></label>
                                        </td>
                                        <td><input type="text"
                                                   class="profile-input"
                                                   id="<?php echo esc_attr( $dt_field['key'], 'disciple_tools' ) ?>"
                                                   name="<?php echo esc_attr( $dt_field['key'], 'disciple_tools' ) ?>"
                                                   value="<?php echo esc_html( $dt_field['value'] ) ?>"/>
                                        </td>
                                    </tr>
                                    <?php
                                } // end foreach
                                ?>

                                <tr>
                                    <td><label for="description"><?php esc_html_e( 'Description', 'disciple_tools' )?></label></td>
                                    <td><textarea type="text" class="profile-input" id="description"
                                                  name="description"
                                                  rows="5"><?php echo esc_html( $dt_user->description ); ?></textarea>
                                    </td>
                                </tr>
                                <tr>
                                    <td><label for="description"><?php esc_html_e( 'Language', 'disciple_tools' )?></label></td>
                                    <td dir="auto">
                                        <?php
                                        $translations = dt_get_translations();
                                        wp_dropdown_languages( array(
                                            'name'                        => 'locale',
                                            'id'                          => 'locale',
                                            'selected'                    => esc_html( $dt_user->locale ),
                                            'languages'                   => $dt_available_languages,
                                            'show_available_translations' => false,
                                            'show_option_site_default'    => false,
                                            'translations'                => $translations
                                        ) );
                                        ?>
                                    </td>
                                </tr>

                            </table>

                            <div class="alert alert-box" style="display:none;" id="alert">
                                <strong><?php echo esc_html( 'Oh snap!' ) ?></strong>
                            </div>

                            <button class="button" type="submit"><?php esc_html_e( 'Save', 'disciple_tools' )?></button>

                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div> <!-- end #inner-content -->
</div> <!-- end #content -->

<?php get_footer(); ?>
