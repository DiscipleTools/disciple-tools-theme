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

<div id="content">

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

                        <button class="float-right" data-open="edit-profile"><i class="fi-pencil"></i> <?php esc_html_e( 'Edit', 'disciple_tools' )?></button>

                        <span class="section-header"><?php esc_html_e( 'Your Profile', 'disciple_tools' )?></span>
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
                                    <strong><?php esc_html_e( "Roles" ); ?></strong><br>
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
                                <br>
                                <?php
                                if ( !empty( $dt_user->locale ) ){
                                    echo esc_html( $dt_user->locale );
                                } else {
                                    echo esc_html__( 'English', 'disciple_tools' );
                                }
                                ?>

                                <strong><?php esc_html_e( 'Biography', 'disciple_tools' )?></strong>
                                <p><?php echo esc_html( $dt_user->user_description ); ?></p>

                            </div>
                        </div>

                    </div> <!-- End Profile -->

                </div>

                <div class="small-12 cell">
                    <div class="bordered-box cell" id="locations" data-magellan-target="locations">
                        <span class="section-header"><?php esc_html_e( 'Locations', 'disciple_tools' )?></span>
                        <hr size="1" style="max-width:100%"/>
                        <!-- Geocoding -->

                        <div id="manage_locations_section"></div>

                    </div>
                </div>

                <!-- Begin Notification-->
                <div class="small-12 cell">

                    <div class="bordered-box cell" id="notifications" data-magellan-target="notifications">
                        <span class="section-header"><?php esc_html_e( 'Notifications', 'disciple_tools' )?></span>
                        <hr size="1" style="max-width:100%"/>

                        <table class="form-table">
                            <thead>
                            <tr>
                                <td><?php esc_html_e( 'Type of Notification', 'disciple_tools' )?></td>
                                <td><?php esc_html_e( 'Web', 'disciple_tools' )?></td>
                                <td><?php esc_html_e( 'Email', 'disciple_tools' )?></td>
                            </tr>
                            </thead>
                            <tbody>
                                <?php foreach ( $dt_site_notification_defaults as $dt_notification_key => $dt_notification_default ) : ?>
                                <tr>
                                    <td class="tall-4"><?php echo esc_html( $dt_notification_default["label"] )?></td>
                                    <td>
                                    <?php if ( $dt_notification_default["web"] ) : ?>
                                        <div style="height:2em;"><?php esc_html_e( "required", 'disciple_tools' ) ?></div>
                                    <?php else : ?>
                                        <div class="switch">
                                            <input class="switch-input" id="<?php echo esc_html( $dt_notification_key ) ?>_web" type="checkbox"
                                                   name="<?php echo esc_html( $dt_notification_key ) ?>_web"
                                                   onclick="switch_preference( '<?php echo esc_html( $dt_notification_key ) ?>_web', 'notifications' );"
                                                <?php ( isset( $dt_user_meta[$dt_notification_key . '_web'] ) && $dt_user_meta[$dt_notification_key . '_web'][0] == false ) ?
                                                    print esc_attr( '', 'disciple_tools' ) : print esc_attr( 'checked', 'disciple_tools' ); ?>>
                                            <label class="switch-paddle inactive" for="<?php echo esc_html( $dt_notification_key ) ?>_web">
                                                <span class="show-for-sr"><?php echo esc_html( $dt_notification_default['label'] ) ?></span>
                                            </label>
                                        </div>
                                    <?php endif; ?>
                                    </td>
                                    <td>
                                    <?php if ( $dt_notification_default["email"] ) : ?>
                                        <div style="height:2em;"><?php esc_html_e( "required", 'disciple_tools' ) ?></div>
                                    <?php else : ?>
                                        <div class="switch">
                                            <input class="switch-input" id="<?php echo esc_html( $dt_notification_key ) ?>_email" type="checkbox"
                                                   name="<?php echo esc_html( $dt_notification_key ) ?>_email"
                                                   onclick="switch_preference( '<?php echo esc_html( $dt_notification_key ) ?>_email', 'notifications' );"
                                                <?php ( isset( $dt_user_meta[$dt_notification_key . '_email'] ) && $dt_user_meta[$dt_notification_key . '_email'][0] == false ) ?
                                                    print esc_attr( '', 'disciple_tools' ) : print esc_attr( 'checked', 'disciple_tools' ); ?>>
                                            <label class="switch-paddle inactive" for="<?php echo esc_html( $dt_notification_key ) ?>_email">
                                                <span class="show-for-sr"><?php echo esc_html( $dt_notification_default['label'] ) ?></span>
                                            </label>
                                        </div>
                                    <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <?php if ( current_user_can( "view_any_contacts" ) ): ?>
                        <p>
                            <strong><?php esc_html_e( 'Follow all contacts', 'disciple_tools' )?>:</strong>
                        </p>
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

                <div class="small-12 cell">

                    <div class="bordered-box cell" id="availability" data-magellan-target="availability">

                        <!-- section header-->
                        <span class="section-header"><?php esc_html_e( 'Availability', 'disciple_tools' )?></span>
                        <hr size="1" style="max-width:100%"/>


                        <!-- Turn on Vacation Reminders -->
                        <p>
                            <strong><?php esc_html_e( 'Set Away', 'disciple_tools' )?>:</strong>
                        </p>
                        <div class="switch large">
                            <input class="switch-input" id="switch0vac" type="checkbox" name="switch0vac"
                                   onclick="switch_preference('dt_availability');" <?php ( isset( $dt_user_meta['dt_availability'] ) && $dt_user_meta['dt_availability'][0] == true ) ? print esc_attr( 'checked', 'disciple_tools' ) : print esc_attr( '', 'disciple_tools' ); ?> />
                            <label class="switch-paddle" for="switch0vac">
                                <span class="show-for-sr"><?php esc_html_e( 'Enable', 'disciple_tools' )?></span>
                                <span class="switch-active" aria-hidden="true"><?php esc_html_e( 'On', 'disciple_tools' )?></span>
                                <span class="switch-inactive" aria-hidden="false"><?php esc_html_e( 'Off', 'disciple_tools' )?></span>
                            </label>
                        </div>


                        <?php /**

                        <!-- List of past, present, and future vacations scheduled -->
                        <p>
                            <strong>Schedule Away: </strong>
                        </p>
                        <p>
                            <button class="button" onclick="" data-open="add-away"><i class="fi-pencil"></i>
                                Add
                            </button>
                        </p>
                        <table class="hover stack-for-small striped">
                            <thead>
                            <tr>
                                <td>Begin Date</td>
                                <td>End Date</td>
                                <td>Status</td>
                                <td></td>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>2017-02-24</td>
                                <td>2017-03-04</td>
                                <td>Scheduled</td>
                                <td>
                                    <button class="hollow button tiny alert"><i class="fi-x"></i> Delete</button>
                                </td>
                            </tr>
                            <tr>
                                <td>2017-02-24</td>
                                <td>2017-03-04</td>
                                <td>Active</td>
                                <td>
                                    <button class="hollow button tiny alert"><i class="fi-x"></i> Delete</button>
                                </td>
                            </tr>
                            <tr>
                                <td>2017-02-24</td>
                                <td>2017-03-04</td>
                                <td>Completed</td>
                                <td>
                                    <button class="hollow button tiny alert"><i class="fi-x"></i> Delete</button>
                                </td>
                            </tr>
                            </tbody>

                        </table>

                    </div> <!-- End Availability -->


                    </div>

                    <!-- Future development of availability -->
                <div class="reveal" id="add-away" data-reveal>
                    <button class="close-button" data-close aria-label="Close modal" type="button">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h2>Add</h2>

                    <div class="row column medium-12">


                        <table class="table">
                            <thead>
                            <tr>
                                <th>Start date&nbsp;
                                    <a href="#" class="button tiny" id="dp4" data-date-format="yyyy-mm-dd"
                                       data-date="2012-02-20">Change</a>
                                </th>
                                <th>End date&nbsp;
                                    <a href="#" class="button tiny" id="dp5" data-date-format="yyyy-mm-dd"
                                       data-date="2012-02-25">Change</a>
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td id="startDate">2012-02-20</td>
                                <td id="endDate">2012-02-25</td>
                            </tr>
                            </tbody>
                        </table>
                        <div class="alert alert-box" style="display:none;" id="alert"><strong>Oh snap!</strong>
                        </div>
                        <button class="button">Add New Vacation</button>
                    </div>

                </div>
                 <!--   End future development of availability -->

                */ ?>


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
                                                    <a href="http://gravatar.com" class="small"><?php esc_html_e( 'edit image on gravatar.com', 'zume' ) ?> <i class="fi-link"></i></a>
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
                                                wp_dropdown_languages( array(
                                                    'name'                        => 'locale',
                                                    'id'                          => 'locale',
                                                    'selected'                    => esc_html( $dt_user->locale ),
                                                    'languages'                   => $dt_available_languages,
                                                    'show_available_translations' => false,
                                                    'show_option_site_default'    => false
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

                    </div> <!-- end #inner-content -->

                </div>

            </div>

        </div>

    </div> <!-- end #content -->

    <?php get_footer(); ?>
