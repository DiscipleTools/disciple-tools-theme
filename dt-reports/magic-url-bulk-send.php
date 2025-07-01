<?php
/**
 * Action and filter to extend the contacts list bulk feature
 * It adds a link and expansion section to select contacts and bulk send magic links to available apps.
 * @note find the actions on the archive-template.php page
 */
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

/**
 * Adds submenu item to 'more' nav menu
 */
add_filter( 'dt_list_action_menu_items', 'dt_post_bulk_list_link_apps', 10, 2 );
function dt_post_bulk_list_link_apps( $bulk_send_menu_items, $post_type ) {
    $dt_magic_apps = DT_Magic_URL::list_bulk_send( $post_type );
    if ( !empty( $dt_magic_apps ) && ( $post_type === 'contacts' || apply_filters( 'dt_bulk_email_connection_field', null, $post_type ) ) ) {
        $bulk_send_menu_items['bulk-send-app'] = [
            'label' => __( 'Bulk Send App', 'disciple_tools' ),
            'icon' => get_template_directory_uri() . '/dt-assets/images/connection.svg',
            'section_id' => 'bulk_send_app_picker',
            'show_list_checkboxes' => true,
        ];
    }
    return $bulk_send_menu_items;
}

/**
 * Adds hidden toggle body
 */
add_action( 'dt_list_action_section', 'dt_post_bulk_list_section_apps', 20, 3 );
function dt_post_bulk_list_section_apps( $post_type ){
    $dt_magic_apps = DT_Magic_URL::list_bulk_send( $post_type );
    if ( !empty( $dt_magic_apps ) && ( $post_type === 'contacts' || apply_filters( 'dt_bulk_email_connection_field', null, $post_type ) ) ) : ?>
        <div id="bulk_send_app_picker" class="list_action_section">
            <button class="close-button list-action-close-button" data-close="bulk_send_app_picker" aria-label="<?php esc_html_e( 'Close', 'disciple_tools' ); ?>" type="button">
                <span aria-hidden="true">×</span>
            </button>
            <p style="font-weight:bold"><?php
                echo sprintf( esc_html__( 'Select all the %1$s to whom you want to send app links.', 'disciple_tools' ), esc_html( $post_type ) );?></p>
            <div class="grid-x grid-margin-x">
                <div class="cell">
                    <?php
                    $default_subject = dt_get_option( 'dt_email_base_subject' ) . ': {{app}}';
                    $default_message = __( 'Hello {{name}},

Please click on the button below to access your app.

{{link}}

Thanks!', 'disciple_tools' );
                    ?>
                    <label for="bulk_send_app_subject"><?php echo esc_html__( 'Configure email subject', 'disciple_tools' ); ?></label>
                    <input type="text" id="bulk_send_app_subject" value="<?php echo esc_attr( $default_subject ); ?>" style="margin-bottom: 0"/>

                    <span><?php echo esc_html__( '{{app}} will be replaced with the selected app name.', 'disciple_tools' ); ?></span><br><br>

                    <label for="bulk_send_app_msg"><?php echo esc_html__( 'Configure email message', 'disciple_tools' ); ?></label>
                    <textarea type="text" id="bulk_send_app_msg" rows="10"><?php echo esc_textarea( $default_message ); ?></textarea>
                    <span><?php echo esc_html__( 'Message placeholders', 'disciple_tools' ); ?></span>
                    <ul>
                        <li><span
                                style="font-weight: bold;">{{app}}</span>: <?php echo esc_html__( 'Selected app name', 'disciple_tools' ); ?>
                        </li>
                        <li><span
                                style="font-weight: bold;">{{name}}</span>: <?php echo esc_html__( 'Name of the Record', 'disciple_tools' ); ?>
                        </li>
                        <li><span
                                style="font-weight: bold;">{{link}}</span>: <?php echo esc_html__( 'Unique link to access the app.', 'disciple_tools' ); ?>
                        </li>
                    </ul>
                </div>
                <div class="cell">
                    <label for="bulk_send_app_required_selection"><?php echo esc_html__( 'Select app to email', 'disciple_tools' ); ?></label>
                    <span id="bulk_send_app_required_selection" style="display:none;color:red;"><?php echo esc_html__( 'You must select an app', 'disciple_tools' ); ?></span>
                    <div class="bulk_send_app dt-radio button-group toggle ">
                         <?php
                            foreach ( $dt_magic_apps as $root ) {
                                foreach ( $root as $type ) {
                                    if ( isset( $type['show_bulk_send'], $type['post_type'] ) && $type['show_bulk_send'] && $type['post_type'] === $post_type ) {
                                        ?>
                                    <input type="radio" id="<?php echo esc_attr( $type['root'] . '_' . $type['type'] ) ?>" data-root="<?php echo esc_attr( $type['root'] ) ?>" data-type="<?php echo esc_attr( $type['type'] ) ?>" name="r-group">
                                    <label class="button" for="<?php echo esc_attr( $type['root'] . '_' . $type['type'] ) ?>"><?php echo esc_html( $type['name'] ) ?></label>
                                        <?php
                                    }
                                }
                            }
                            ?>
                    </div>
                </div>
                <div class="cell">
                    <label for="bulk_send_app_required_elements"><?php echo esc_html__( 'Send to selected records', 'disciple_tools' ); ?></label>
                    <span id="bulk_send_app_required_elements" style="display:none;color:red;"><?php echo esc_html__( 'You must select at least one record', 'disciple_tools' ); ?></span>
                    <div>
                    <button class="button dt-green" id="bulk_send_app_submit">
                        <span class="bulk_edit_submit_text" data-pretext="<?php echo esc_html__( 'Send', 'disciple_tools' ); ?>" data-posttext="<?php echo esc_html__( 'Links', 'disciple_tools' ); ?>" style="text-transform:capitalize;">
                            <?php echo esc_html( __( 'Make Selections Below', 'disciple_tools' ) ); ?>
                        </span>
                        <span id="bulk_send_app_submit-spinner" style="display: inline-block" class="loading-spinner"></span>
                    </button>

                    </div>
                    <span id="bulk_send_app_submit-message"></span>
                </div>
            </div>

        </div>
    <?php endif;
};
