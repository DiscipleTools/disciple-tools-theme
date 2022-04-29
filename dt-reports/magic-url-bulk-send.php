<?php
/**
 * Action and filter to extend the contacts list bulk feature
 * It adds a link and expansion section to select contacts and bulk send magic links to available apps.
 * @note find the actions on the archive-template.php page
 */
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

if ( 'contacts' === dt_get_post_type() ) {

    /**
     * Adds link
     */
    add_action( 'dt_post_bulk_list_link', 'dt_post_bulk_list_link_apps', 20, 3 );
    function dt_post_bulk_list_link_apps( $post_type, $post_settings, $dt_magic_apps ) {
        if ( ! empty( $dt_magic_apps ) && 'contacts' === $post_settings['post_type'] ) : ?>
            <script>
                let bulkSendAppButton = `
                    <li>
                        <a href="#">
                            <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/connection.svg' ); ?>" class="dropdown-submenu-icon">
                            <?php esc_html_e( 'Bulk Send App', 'disciple_tools' ); ?>
                        </a>
                    </li>
                `;
                jQuery('#dropdown-submenu-items-more').append(bulkSendAppButton);
            </script>
        <?php endif;
    }

    /**
     * Adds hidden toggle body
     */
    add_action( 'dt_post_bulk_list_section', 'dt_post_bulk_list_section_apps', 20, 3 );
    function dt_post_bulk_list_section_apps( $post_type, $post_settings, $dt_magic_apps ){
        if ( ! empty( $dt_magic_apps ) && 'contacts' === $post_settings['post_type'] ) :  ?>
            <div id="bulk_send_app_picker" style="display:none; padding:20px; border-radius:5px; background-color:#ecf5fc; margin: 30px 0">
                <p style="font-weight:bold"><?php
                    echo sprintf( esc_html__( 'Select all the %1$s to whom you want to send app links.', 'disciple_tools' ), esc_html( $post_type ) );?></p>
                <div class="grid-x grid-margin-x">
                    <div class="cell">
                        <label for="bulk_send_app_note"><?php echo esc_html__( 'Add optional greeting', 'disciple_tools' ); ?></label>
                        <input type="text" id="bulk_send_app_note" placeholder="<?php echo esc_html__( 'Add short greeting to be added above the app link.', 'disciple_tools' ); ?>" />
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
                                <?php echo esc_html( __( "Make Selections Below", "disciple_tools" ) ); ?>
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
}

