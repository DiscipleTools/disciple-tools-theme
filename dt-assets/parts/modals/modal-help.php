<div class="reveal" id="help-modal" data-reveal>
    <?php
    $current_post_type = get_post_type() ?: dt_get_post_type();
    $post_fields = DT_Posts::get_post_field_settings( $current_post_type );

    /**
     * Contact Record
     */
    if ( $current_post_type === 'contacts' ) :
        ?>
        <!--    Quick Actions   -->
        <div class="help-section" id="quick-action-help-text" style="display: none">
            <h3><?php esc_html_e( 'Quick Action Buttons', 'disciple_tools' ) ?></h3>
            <p><?php echo esc_html_x( 'These quick action buttons are here to aid you in updating the contact record faster and keep a count of how many times each action has be done.', 'Optional Documentation', 'disciple_tools' ) ?>
            <p><?php echo esc_html_x( "For example, If you click the 'No Answer' button 4 times, we will log that you attempted to reach this contact 4 times, but they did not answer. This action will also change the 'Seeker Path' to 'Contact Attempted'.", 'Optional Documentation', 'disciple_tools' ) ?>
            </p>
        </div>

    <?php endif;

    if ( !empty( $current_post_type ) && isset( $post_fields['assigned_to'] ) ) : ?>
        <!--    Assigned to -->
        <div class="help-section" id="assigned-to-help-text" style="display: none">
            <h3><?php echo esc_html( $post_fields['assigned_to']['name'] )?></h3>
            <p><?php echo esc_html( $post_fields['assigned_to']['description'] ) ?></p>
            <?php if ( current_user_can( 'dt_all_access_contacts' ) ) : ?>
                <p><strong><?php echo esc_html_x( 'User workload status icons legend:', 'Optional Documentation', 'disciple_tools' ) ?></strong></p>
                <ul style="list-style-type:none">
                    <?php $workload_status_options = Disciple_Tools_Users::get_users_fields()['workload_status']['options'] ?? [];
                    foreach ( $workload_status_options as $option_key => $option_val ): ?>
                        <li><span style="background-color: <?php echo esc_html( $option_val['color'] ) ?>; height:10px; padding: 0 5px; border-radius: 2px">&nbsp;</span> <?php echo esc_html( $option_val['label'] ) ?></li>
                    <?php endforeach ?>
                    <li><img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/broken.svg' )?>" /> 2: <?php esc_html_e( '2 contacts need an update', 'disciple_tools' ) ?> </li>
                </ul>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!--    Comments and Activity - contact & group  -->
    <div class="help-section" id="comments-activity-help-text" style="display: none">
        <h3><?php esc_html_e( 'Comments and Activity', 'disciple_tools' ) ?></h3>
        <p><?php echo esc_html_x( 'This is where you can record notes from meetings and conversations.', 'Optional Documentation', 'disciple_tools' ) ?></p>
        <p><?php echo esc_html_x( 'Type @ and the name of a user to mention them in a comment. This user will then receive a notification.', 'Optional Documentation', 'disciple_tools' ) ?></p>
        <p><?php echo esc_html_x( 'This section also includes the history of activity, such as when the contact or group status became active etc.', 'Optional Documentation', 'disciple_tools' ) ?></p>
        <p><?php echo esc_html_x( "You can filter this section either by 'All', 'Comments', or 'Activity'.", 'Optional Documentation', 'disciple_tools' ) ?></p>
    </div>


    <?php
    $url_path = dt_get_url_path();

    /**
     * Profile Settings Page
     */
    if ( 'settings' === $url_path ) : ?>
        <!--  Your Profile -->
        <div class="help-section" id="profile-help-text" style="display: none">
            <h3><?php echo esc_html__( 'Your Profile', 'disciple_tools' ) ?></h3>
            <p><?php echo esc_html_x( 'In this area you can see your user profile.', 'Optional Documentation', 'disciple_tools' ) ?></p>
            <p><?php echo esc_html_x( 'You are not required to fill out any of these profile fields. They are optional to meet your team’s needs.', 'Optional Documentation', 'disciple_tools' ) ?></p>
            <p><?php echo esc_html_x( "By clicking 'Edit', you will be able adjust things like the language that this system operates in.", 'Optional Documentation', 'disciple_tools' ) ?></p>
        </div>

        <!--  Your Locations  -->
        <div class="help-section" id="locations-help-text" style="display: none">
            <h3><?php esc_html_e( 'Locations', 'disciple_tools' ) ?></h3>
            <p><?php echo esc_html_x( "These are the areas you are responsible for. Clicking the 'Add' button will bring up a list of locations to choose from.", 'Optional Documentation', 'disciple_tools' ) ?></p>
        </div>

        <!--  Your notifications -->
        <div class="help-section" id="notifications-help-text" style="display: none">
            <h3><?php echo esc_html_x( 'Your Notifications', 'Optional Documentation', 'disciple_tools' ) ?></h3>
            <p><?php echo esc_html_x( 'You will receive web notifications and email notifications based on your notification preferences. To change your preference, click the toggle buttons.', 'Optional Documentation', 'disciple_tools' ) ?></p>
            <ul>
                <li><?php echo esc_html_x( 'Notifications Turned On: The toggle will appear blue.', 'Optional Documentation', 'disciple_tools' ) ?></li>
                <li><?php echo esc_html_x( 'Notifications Turned Off: The toggle will appear grey.', 'Optional Documentation', 'disciple_tools' ) ?></li>
            </ul>
            <p><?php echo esc_html_x( 'Some of the types of notifications cannot be adjusted because they are required by the system.', 'Optional Documentation', 'disciple_tools' ) ?></p>
            <p><?php echo esc_html_x( 'Adjust whether you want to see notifications about new comments, contact information changes, Contact Milestones and Group Health metrics, and whether to you will receive a notification for any update that happens in the system.', 'Optional Documentation', 'disciple_tools' ) ?></p>
        </div>


    <?php endif; ?>

    <!--   Notifications page -->
    <div class="help-section" id="notifications-template-help-text" style="display: none">
        <h3><?php echo esc_html_x( 'Notifications Page', 'Optional Documentation', 'disciple_tools' ) ?></h3>
        <p><?php echo esc_html_x( 'This Notifications Page is where you can read updates to users and groups. It displays notifications about activity on your records.', 'Optional Documentation', 'disciple_tools' ) ?></p>
        <h4><?php echo esc_html_x( 'All / Unread', 'Optional Documentation', 'disciple_tools' ) ?></h4>
        <p><?php echo esc_html_x( "Click the 'All' button to show the full list of all of your notifications.", 'Optional Documentation', 'disciple_tools' ) ?></p>
        <p><?php echo esc_html_x( "Click the 'Unread' button to show the list of all of your unread notifications.", 'Optional Documentation', 'disciple_tools' ) ?></p>
        <h4><?php esc_html_e( 'Mark all as read', 'disciple_tools' ) ?></h4>
        <p><?php echo esc_html_x( "If you don't want to click each filled in circle on the right side of each row to indicate the notification has been read, then click the 'Mark All as Read' link at the top to quickly adjust all the messages that they have all been read.", 'Optional Documentation', 'disciple_tools' ) ?></p>
        <h4><?php esc_html_e( 'Settings', 'disciple_tools' ) ?></h4>
        <p><?php echo esc_html_x( "Click the 'Settings' link to go to the notifications settings area to adjust whether you want to see notifications about new comments, contact information changes, Contact Milestones and Group Health metrics, and whether to you will receive a notification for any update that happens in the system.", 'Optional Documentation', 'disciple_tools' ) ?></p>
    </div>

    <!--   Duplicates view page -->
    <div class="help-section" id="duplicates-template-help-text" style="display: none">
        <h3><?php echo esc_html_x( 'Duplicate Contact page', 'Optional Documentation', 'disciple_tools' ) ?></h3>
        <p><?php echo esc_html_x( 'This Duplicate Contact Page is where you can review, decline or merge contacts that have been picked up by the system (checking against their name, email and phone) as possibly being duplicates of another already existing contact.', 'Optional Documentation', 'disciple_tools' ) ?></p>
    </div>

    <!-- App links help modal -->

    <?php
    /**
     * Add additional modal help text
     */
    do_action( 'dt_modal_help_text' )
    ?>

    <!-- close -->
    <div class="grid-x grid-padding-x">
        <div class="cell small-4">
            <h5>&nbsp;</h5>
            <button class="button small" data-close aria-label="Close reveal" type="button">
                <?php echo esc_html__( 'Close', 'disciple_tools' )?>
            </button>
        </div>
        <div class="cell small-8">
            <!-- documentation link -->
            <div class="help-more">
                <h5><?php echo esc_html_x( 'Need more help?', 'Optional Documentation', 'disciple_tools' ) ?></h5>
                <a class="button small" id="docslink" href="https://disciple.tools/docs" target="_blank"><?php echo esc_html_x( 'Read the documentation', 'Optional Documentation', 'disciple_tools' )?></a>
            </div>
        </div>
        <button class="close-button" data-close aria-label="<?php esc_html_e( 'Close', 'disciple_tools' ); ?>" type="button">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
</div>
<div class="reveal" id="help-modal-field" data-reveal>

    <h1 id="help-modal-field-title"><?php echo esc_html( 'Tile Documentation' ); ?></h1>
    <p id="help-modal-field-description" class="make-links-clickable" style="white-space: pre-line"></p>
    <div id="help-modal-field-body"></div>

    <div id="tile-help-section-apps" class="help-section help-modal-icon" style="display: none">
        <ul>
            <li>
                <img alt="show" src="<?php echo esc_url( get_template_directory_uri() . '/dt-assets/images/visibility.svg' ) ?>" />
                <strong><?php esc_html_e( 'Open the link', 'disciple_tools' ) ?></strong>
            </li>
            <li>
                <img alt="copy" src="<?php echo esc_url( get_template_directory_uri() . '/dt-assets/images/duplicate.svg' ) ?>"/>
                <strong><?php esc_html_e( 'Copy the link to the clipboard', 'disciple_tools' ) ?></strong>
            </li>
            <li>
                <img alt="send" src="<?php echo esc_url( get_template_directory_uri() . '/dt-assets/images/send.svg' ) ?>" />
                <strong><?php esc_html_e( 'Send the link via email.', 'disciple_tools' ) ?></strong>
            </li>
            <li>
                <img alt="qrcode" src="<?php echo esc_url( get_template_directory_uri() . '/dt-assets/images/qrcode-solid.svg' ) ?>" />
                <strong><?php esc_html_e( 'Scan the QR code to open the magic link on a mobile device.', 'disciple_tools' ) ?></strong>
            </li>
            <li>
                <img alt="undo" src="<?php echo esc_url( get_template_directory_uri() . '/dt-assets/images/undo.svg' ) ?>" />
                <strong><?php esc_html_e( 'Reset the security code. No data is removed. Only access. The previous link will be disabled and another one created.', 'disciple_tools' ) ?></strong>
            </li>
        </ul>
    </div>

    <!-- close -->
    <div class="grid-x grid-padding-x">
        <div class="cell small-4">
            <button class="button small" data-close aria-label="Close reveal" type="button">
                <?php echo esc_html__( 'Close', 'disciple_tools' )?>
            </button>
        </div>
        <div class="cell small-8">
            <!-- documentation link -->
            <div class="help-more">
                <h5><?php echo esc_html_x( 'Need more help?', 'Optional Documentation', 'disciple_tools' ) ?></h5>
                <a class="button small" id="docslink" href="https://disciple.tools/docs" target="_blank"><?php echo esc_html_x( 'Read the documentation', 'Optional Documentation', 'disciple_tools' )?></a>
            </div>
        </div>
        <button class="close-button" data-close aria-label="<?php esc_html_e( 'Close', 'disciple_tools' ); ?>" type="button">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
</div>

