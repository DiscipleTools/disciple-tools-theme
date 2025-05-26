<?php

/**
 * Disciple.Tools
 *
 * @class      Disciple_Tools_
 * @version    0.1.0
 * @since      0.1.0
 * @package    Disciple.Tools
 * @author     Disciple.Tools
 */


/**
 * @todo First custom quick action gets erased on ENTER key down if icon input text is focused
 */

if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class Disciple_Tools_Tab_Custom_Lists
 */
class Disciple_Tools_Tab_Custom_Lists extends Disciple_Tools_Abstract_Menu_Base
{

    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    /**
     * Constructor function.
     *
     * @access  public
     * @since   0.1.0
     */
    public function __construct() {
        add_action( 'admin_menu', [ $this, 'add_submenu' ], 99 );
        add_action( 'dt_settings_tab_menu', [ $this, 'add_tab' ], 10, 1 );
        add_action( 'dt_settings_tab_content', [ $this, 'content' ], 99, 1 );

        parent::__construct();
    } // End __construct()

    public function add_submenu() {
        add_submenu_page( 'dt_options', __( 'Custom Lists', 'disciple_tools' ), __( 'Custom Lists', 'disciple_tools' ), 'manage_dt', 'dt_options&tab=custom-lists', [ 'Disciple_Tools_Settings_Menu', 'content' ] );
    }

    public function add_tab( $tab ) {
        echo '<a href="'. esc_url( admin_url() ).'admin.php?page=dt_options&tab=custom-lists" class="nav-tab ';
        if ( $tab == 'custom-lists' ) {
            echo 'nav-tab-active';
        }
        echo '">'. esc_html__( 'Custom Lists' ) . '</a>';
    }

    /**
     * Packages and prints tab page
     *
     * @param $tab
     */
    public function content( $tab ) {
        if ( 'custom-lists' == $tab ) :

            $this->template( 'begin' );

            /* Translation Dialog */
            dt_display_translation_dialog();

            /* Icon Selector Dialog */
            include 'dialog-icon-selector.php';

            /* Worker Profile */
            $this->box( 'top', 'User (Worker) Contact Profile' );
            $this->process_user_profile_box();
            $this->user_profile_box(); // prints
            $this->box( 'bottom' );
            /* end Worker Profile */

            /* Comment Types */
            $this->box( 'top', __( 'Contact Comment Types', 'disciple_tools' ) );
            $this->process_comment_types_box();
            $this->comment_types_box(); // prints
            $this->box( 'bottom' );
            /* end Comment Types */

            /* Channels */
            $this->box( 'top', __( 'Contact Communication Channels', 'disciple_tools' ) );
            $this->process_channels_box();
            $this->channels_box(); // prints
            $this->box( 'bottom' );
            /* end Channels */

            /* Quick Actions */
            $this->box( 'top', __( 'Quick Actions', 'disciple_tools' ) );
            $this->process_quick_actions_box();
            $this->quick_actions_box();
            $this->box( 'bottom' );
            /* end Quick Actions */

            /* Languages */
            $this->box( 'top', __( 'Language Options', 'disciple_tools' ) );
            $this->process_languages_box();
            $this->languages_box(); // prints
            $this->box( 'bottom' );
            /* end Languages */

            $this->template( 'right_column' );

            $this->template( 'end' );

        endif;
    }

    /**
     * Print the contact settings box.
     */
    public function user_profile_box() {
        echo '<form method="post" name="user_fields_form">';
        echo '<button type="submit" class="button-like-link" name="enter_bug_fix" value="&nasb"></button>';
        echo '<button type="submit" class="button-like-link" name="user_fields_reset" value="1">' . esc_html( __( 'reset', 'disciple_tools' ) ) . '</button>';
        echo '<p>' . esc_html( __( 'You can add or remove types of contact fields for user profiles.', 'disciple_tools' ) ) . '</p>';
        echo '<input type="hidden" name="user_fields_nonce" id="user_fields_nonce" value="' . esc_attr( wp_create_nonce( 'user_fields' ) ) . '" />';
        echo '<table class="widefat">';
        echo '<thead><tr><td>Label</td><td>Type</td><td>Description</td><td>Enabled</td><td>' . esc_html( __( 'Delete', 'disciple_tools' ) ) . '</td></tr></thead><tbody>';

        // custom list block
        $site_custom_lists = dt_get_option( 'dt_site_custom_lists' );
        if ( ! $site_custom_lists ) {
            wp_die( 'Failed to get dt_site_custom_lists() from options table.' );
        }
        $user_fields = $site_custom_lists['user_fields'];
        $user_fields_types = $site_custom_lists['user_fields_types'] ?? [];
        foreach ( $user_fields as $field ) {
            echo '<tr>
                        <td>' . esc_attr( $field['label'] ) . '</td>
                        <td>' . esc_attr( isset( $user_fields_types[$field['type']]['label'] ) ? $user_fields_types[$field['type']]['label'] : $field['type'] ) . '</td>
                        <td>' . esc_attr( $field['description'] ) . ' </td>
                        <td><input name="user_fields[' . esc_attr( $field['key'] ) . ']" type="checkbox" ' . ( $field['enabled'] ? 'checked' : '' ) . ' /></td>
                        <td><button type="submit" name="delete_field" value="' . esc_attr( $field['key'] ) . '" class="button small" >' . esc_html( __( 'Delete', 'disciple_tools' ) ) . '</button> </td>
                      </tr>';
        }
        // end list block

        echo '</table>';
        echo '<br><button type="button" onclick="jQuery(\'#add_user\').toggle();" class="button">' . esc_html( __( 'Add', 'disciple_tools' ) ) . '</button>
                        <button type="submit" style="float:right;" class="button">' . esc_html( __( 'Save', 'disciple_tools' ) ) . '</button>';
        echo '<div id="add_user" style="display:none;">';
        echo '<table width="100%"><tr><td><hr><br>
                    <input type="text" name="add_input_field[label]" placeholder="label" />&nbsp;';
        echo '<select name="add_input_field[type]" id="add_input_field_type">';
        // Iterate the options
        foreach ( $user_fields_types as $value ) {
            echo '<option value="' . esc_attr( $value['key'] ) . '" >' . esc_attr( $value['label'] ) . '</option>';
        }
        echo '</select>' . "\n";

        echo '<input type="text" name="add_input_field[description]" placeholder="description" />&nbsp;
                    <button type="submit">' . esc_html( __( 'Add', 'disciple_tools' ) ) . '</button>
                    </td></tr></table></div>';

        echo '</tbody></form>';
    }

    /**
     * Process user profile settings
     */
    public function process_user_profile_box() {

        if ( isset( $_POST['user_fields_nonce'] ) ) {

            if ( !wp_verify_nonce( sanitize_key( $_POST['user_fields_nonce'] ), 'user_fields' ) ) {
                return;
            }

            // Process current fields submitted
            $site_custom_lists = dt_get_option( 'dt_site_custom_lists' );
            if ( ! $site_custom_lists ) {
                wp_die( 'Failed to get dt_site_custom_lists() from options table.' );
            }

            foreach ( $site_custom_lists['user_fields'] as $key => $value ) {
                if ( isset( $_POST['user_fields'][ $key ] ) ) {
                    $site_custom_lists['user_fields'][ $key ]['enabled'] = true;
                } else {
                    $site_custom_lists['user_fields'][ $key ]['enabled'] = false;
                }
            }

            // Process new field submitted
            if ( !empty( $_POST['add_input_field']['label'] ) ) {

                $label = sanitize_text_field( wp_unslash( $_POST['add_input_field']['label'] ) );
                if ( empty( $label ) ) {
                    return;
                }

                if ( !empty( $_POST['add_input_field']['description'] ) ) {
                    $description = sanitize_text_field( wp_unslash( $_POST['add_input_field']['description'] ) );
                } else {
                    $description = '';
                }

                if ( !empty( $_POST['add_input_field']['type'] ) ) {
                    $type = sanitize_text_field( wp_unslash( $_POST['add_input_field']['type'] ) );
                } else {
                    $type = 'other';
                }

                $key = 'dt_user_' . sanitize_key( strtolower( str_replace( ' ', '_', $label ) ) );
                $enabled = true;

                // strip and make lowercase process
                $site_custom_lists['user_fields'][ $key ] = [
                    'label'       => $label,
                    'key'         => $key,
                    'type'        => $type,
                    'description' => $description,
                    'enabled'     => $enabled,
                ];
            }

            // Process a field to delete.
            if ( isset( $_POST['delete_field'] ) ) {

                $delete_key = sanitize_text_field( wp_unslash( $_POST['delete_field'] ) );

                unset( $site_custom_lists['user_fields'][ $delete_key ] );
                //TODO: Consider adding a database query to delete all instances of this key from usermeta

            }

            // Process reset request
            if ( isset( $_POST['user_fields_reset'] ) ) {

                unset( $site_custom_lists['user_fields'] );

                $site_custom_lists['user_fields'] = dt_get_site_custom_lists( 'user_fields' );
            }

            // Update the site option
            update_option( 'dt_site_custom_lists', $site_custom_lists, true );
        }
    }

    public function process_comment_types_box() {
        if ( isset( $_POST['comment_types_box_nonce'] ) ) {
            $custom_key_prefix = 'cmt_type_';

            if ( ! wp_verify_nonce( sanitize_key( $_POST['comment_types_box_nonce'] ), 'comment_types_box' ) ) {
                self::admin_notice( __( 'Something went wrong', 'disciple_tools' ), 'error' );

                return;
            }

            $langs                 = dt_get_available_languages();
            $comment_type_options  = dt_get_option( 'dt_comment_types' );
            $comment_type_fields = $comment_type_options['contacts'] ?? [];
            $comment_types         = isset( $_POST['type_keys'] ) ? array_keys( dt_recursive_sanitize_array( $_POST['type_keys'] ) ) : [];

            // Handle general updates.
            foreach ( $comment_types as $type ){

                // Ensure 3rd party custom comment type extra parameters are also persisted.
                if ( !isset( $comment_type_fields[$type] ) ){
                    $filtered_types = apply_filters( 'dt_comments_additional_sections', [], 'contacts' );
                    foreach ( $filtered_types ?? [] as $filtered_type ){
                        if ( $filtered_type['key'] == $type ){
                            $comment_type_fields[$type] = $filtered_type;
                            $comment_type_fields[$type]['is_comment_type'] = true;
                        }
                    }
                }

                // Proceed with type updating.
                if ( isset( $comment_type_fields[$type] ) ){

                    // Type Name
                    if ( isset( $_POST['type_labels'][$type]['default'] ) ){
                        $comment_type_fields[$type]['name'] = sanitize_text_field( wp_unslash( $_POST['type_labels'][$type]['default'] ) );
                    }

                    // Type Enabled - Only apply if it's an original custom type.
                    if ( substr( $type, 0, strlen( $custom_key_prefix ) ) === $custom_key_prefix ){
                        $comment_type_fields[$type]['enabled'] = isset( $_POST['type_enabled'][$type] );
                    }

                    // Type Translations
                    foreach ( $langs as $lang => $val ){
                        $langcode = $val['language'];
                        if ( isset( $_POST['type_labels'][$type][$langcode] ) ){
                            $translated_label = sanitize_text_field( wp_unslash( $_POST['type_labels'][$type][$langcode] ) );
                            if ( ( empty( $translated_label ) && !empty( $comment_type_fields[$type]['translations'][$langcode] ) ) || !empty( $translated_label ) ){
                                $comment_type_fields[$type]['translations'][$langcode] = $translated_label;
                            }
                        }
                    }

                    // Persist key prefix.
                    $comment_type_fields[$type]['key_prefix'] = $custom_key_prefix;
                }
            }

            // Handle the adding of new comment types.
            if ( ! empty( $_POST['add_type'] ) ) {
                $label = sanitize_text_field( wp_unslash( $_POST['add_type'] ) );
                $key   = substr( dt_create_field_key( $custom_key_prefix . $label ), 0, 20 );
                if ( ! empty( $key ) ) {
                    if ( isset( $comment_type_fields[ $key ] ) ) {
                        self::admin_notice( __( 'This comment type already exists', 'disciple_tools' ), 'error' );
                    } else {
                        $comment_type_fields[$key] = [
                            'name' => $label,
                            'enabled' => true,
                            'is_comment_type' => true,
                            'key_prefix' => $custom_key_prefix
                        ];
                    }
                }
            }

            // Update and persist custom comment type options.
            $comment_type_options['contacts'] = $comment_type_fields;
            update_option( 'dt_comment_types', $comment_type_options );
        }
    }

    public function comment_types_box() {
        $form_name = 'comment_types_box';
        ?>
        <form method="post" name="<?php echo esc_html( $form_name ) ?>">
            <input type="hidden" name="comment_types_box_nonce"
                   value="<?php echo esc_attr( wp_create_nonce( 'comment_types_box' ) ) ?>"/>
            <table class="widefat">
                <thead>
                <tr>
                    <td><?php esc_html_e( 'Name', 'disciple_tools' ) ?></td>
                    <td><?php esc_html_e( 'Key', 'disciple_tools' ) ?></td>
                    <td><?php esc_html_e( 'Enabled', 'disciple_tools' ) ?></td>
                    <td><?php esc_html_e( 'Translation', 'disciple_tools' ) ?></td>
                </tr>
                </thead>
                <tbody>
                <?php
                // Display comment types, ignoring contact_ communication channel fields.
                $comment_types  = apply_filters( 'dt_comments_additional_sections', [], 'contacts' );
                foreach ( $comment_types ?? [] as $type ) {
                    if ( ( ( isset( $type['is_comment_type'] ) && $type['is_comment_type'] ) || ( strpos( $type['key'], 'contact_' ) === false ) ) && !in_array( $type['key'], [ 'activity' ] ) ){
                        $enabled = ! isset( $type['enabled'] ) || $type['enabled'] !== false;
                        $supported_key_prefixes = isset( $type['key_prefix'] ) && substr( $type['key'], 0, strlen( $type['key_prefix'] ) ) === $type['key_prefix'];
                        ?>
                        <tr>
                            <input type="hidden" name="type_keys[<?php echo esc_html( $type['key'] ) ?>]">
                            <td><input type="text" name="type_labels[<?php echo esc_html( $type['key'] ) ?>][default]"
                                       value="<?php echo esc_html( $type['name'] ?? ( $type['label'] ?? $type['key'] ) ) ?>">
                            </td>
                            <td><?php echo esc_html( $type['key'] ) ?></td>
                            <td>
                                <input name="type_enabled[<?php echo esc_html( $type['key'] ) ?>]"
                                       type="checkbox" <?php echo esc_html( $enabled ? 'checked' : '' ) ?>
                                    <?php echo esc_html( !$supported_key_prefixes ? 'disabled' : '' ) ?>
                                />
                            </td>
                            <td>
                                <?php $langs = dt_get_available_languages(); ?>
                                <button class="button small expand_translations"
                                        data-form_name="<?php echo esc_html( $form_name ) ?>"
                                        data-source="lists">
                                    <?php
                                    $number_of_translations = 0;
                                    foreach ( $langs as $lang => $val ) {
                                        if ( ! empty( $type['translations'][ $val['language'] ] ) ) {
                                            $number_of_translations++;
                                        }
                                    }
                                    ?>
                                    <img style="height: 15px; vertical-align: middle"
                                         src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/languages.svg' ); ?>">
                                    (<?php echo esc_html( $number_of_translations ); ?>)
                                </button>
                                <div class="translation_container hide">
                                    <table>
                                        <?php foreach ( $langs as $lang => $val ) : ?>
                                            <tr>
                                                <td><label
                                                        for="type_labels[<?php echo esc_html( $type['key'] ) ?>][<?php echo esc_html( $val['language'] ) ?>]"><?php echo esc_html( $val['native_name'] ) ?></label>
                                                </td>
                                                <td><input
                                                        name="type_labels[<?php echo esc_html( $type['key'] ) ?>][<?php echo esc_html( $val['language'] ) ?>]"
                                                        type="text"
                                                        value="<?php echo esc_html( $type['translations'][ $val['language'] ] ?? '' ); ?>"/>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </table>
                                </div>
                            </td>
                        </tr>
                        <?php
                    }
                }
                ?>
                </tbody>
            </table>
            <br>
            <button type="button" onclick="jQuery('#add_type').toggle();" class="button">
                <?php echo esc_html_x( 'Add new type', 'comment types', 'disciple_tools' ) ?></button>
            <button type="submit" class="button" style="float:right;">
                <?php esc_html_e( 'Save', 'disciple_tools' ) ?>
            </button>
            <div id="add_type" style="display:none;">
                <hr>
                <input type="text" name="add_type" placeholder="type"/>
                <button type="submit"><?php esc_html_e( 'Add', 'disciple_tools' ) ?></button>
            </div>
        </form>
        <?php
    }

    public function channels_box(){
        $fields = DT_Posts::get_post_field_settings( 'contacts', false );
        $form_name = 'channels_box';
        ?>
        <form method="post" name="<?php echo esc_html( $form_name ) ?>">
            <input type="hidden" name="channels_box_nonce" value="<?php echo esc_attr( wp_create_nonce( 'channels_box' ) ) ?>" />
            <table class="widefat">
                <thead>
                    <tr>
                        <td><?php esc_html_e( 'Name', 'disciple_tools' ) ?></td>
                        <td><?php esc_html_e( 'Key', 'disciple_tools' ) ?></td>
                        <td><?php esc_html_e( 'Enabled', 'disciple_tools' ) ?></td>
                        <td><?php esc_html_e( 'Hide domain if a url', 'disciple_tools' ) ?></td>
                        <td><?php esc_html_e( 'Icon link (must be https or mdi webfont)', 'disciple_tools' ) ?></td>
                        <td><?php esc_html_e( 'Translation', 'disciple_tools' ) ?></td>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $fields as $channel_key => $channel_option ) :
                        if ( $channel_option['type'] !== 'communication_channel' ) {
                            continue;
                        }

                        $enabled = !isset( $channel_option['enabled'] ) || $channel_option['enabled'] !== false;
                        $hide_domain = isset( $channel_option['hide_domain'] ) && $channel_option['hide_domain'] == true; ?>

                    <tr>
                        <input type="hidden" name="channel_fields[<?php echo esc_html( $channel_key ) ?>]">
                        <td><input type="text" name="channel_label[<?php echo esc_html( $channel_key ) ?>][default]" value="<?php echo esc_html( $channel_option['name'] ?? $channel_key ) ?>"></td>
                        <td><?php echo esc_html( $channel_key ) ?></td>
                        <td>
                            <input name="channel_enabled[<?php echo esc_html( $channel_key ) ?>]"
                                   type="checkbox" <?php echo esc_html( $enabled ? 'checked' : '' ) ?> />
                        </td>
                        <td>
                            <input name="channel_hide_domain[<?php echo esc_html( $channel_key ) ?>]"
                                   type="checkbox" <?php echo esc_html( $hide_domain ? 'checked' : '' ) ?> />
                        </td>
                        <td>
                            <input type="text" name="channel_icon[<?php echo esc_html( $channel_key ) ?>]"
                                   value="<?php echo esc_html( $channel_option['icon'] ?? ( $channel_option['font-icon'] ?? '' ) ) ?>">
                            <?php if ( ! empty( $channel_option['icon'] ) || ! empty( $channel_option['font-icon'] ) ) : ?>
                                <button class="button change-icon-button" data-form="channels_box"
                                        data-icon-input="channel_icon[<?php echo esc_html( $channel_key ) ?>]"><?php esc_html_e( 'Change Icon', 'disciple_tools' ); ?></button>

                                <button type="submit" class="button"
                                        name="channel_reset_icon[<?php echo esc_html( $channel_key ) ?>]"><?php esc_html_e( 'Reset link', 'disciple_tools' ) ?></button>
                            <?php endif; ?>
                        </td>
                        <td>
                        <?php $langs = dt_get_available_languages(); ?>
                        <button class="button small expand_translations"
                                data-form_name="<?php echo esc_html( $form_name ) ?>"
                                data-source="lists">
                            <?php
                            $number_of_translations = 0;
                            foreach ( $langs as $lang => $val ){
                                if ( !empty( $channel_option['translations'][$val['language']] ) ){
                                    $number_of_translations++;
                                }
                            }
                            ?>
                            <img style="height: 15px; vertical-align: middle" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/languages.svg' ); ?>">
                            (<?php echo esc_html( $number_of_translations ); ?>)
                        </button>
                        <div class="translation_container hide">
                            <table>
                            <?php foreach ( $langs as $lang => $val ) : ?>
                                <tr>
                                    <td><label for="channel_label[<?php echo esc_html( $channel_key ) ?>][<?php echo esc_html( $val['language'] )?>]"><?php echo esc_html( $val['native_name'] )?></label></td>
                                    <td><input name="channel_label[<?php echo esc_html( $channel_key ) ?>][<?php echo esc_html( $val['language'] )?>]" type="text" value="<?php echo esc_html( $channel_option['translations'][$val['language']] ?? '' );?>"/></td>
                                </tr>
                            <?php endforeach; ?>
                            </table>
                        </div>
                    </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <br><button type="button" onclick="jQuery('#add_channel').toggle();" class="button">
                <?php echo esc_html_x( 'Add new channel', 'communication channel (Phone, Email, Facebook)', 'disciple_tools' ) ?></button>
            <button type="submit" class="button" style="float:right;">
                <?php esc_html_e( 'Save', 'disciple_tools' ) ?>
            </button>
            <div id="add_channel" style="display:none;">
                <hr>
                <input type="text" name="add_channel" placeholder="channel" />
                <button type="submit"><?php esc_html_e( 'Add', 'disciple_tools' ) ?></button>
            </div>
        </form>
        <?php
    }

    public function process_channels_box(){
        if ( isset( $_POST['channels_box_nonce'] ) ){
            $fields = DT_Posts::get_post_field_settings( 'contacts' );
            if ( !wp_verify_nonce( sanitize_key( $_POST['channels_box_nonce'] ), 'channels_box' ) ){
                self::admin_notice( __( 'Something went wrong', 'disciple_tools' ), 'error' );
                return;
            }

            $channel_fields        = isset( $_POST['channel_fields'] ) ? array_keys( dt_recursive_sanitize_array( $_POST['channel_fields'] ) ) : [];
            $langs                 = dt_get_available_languages();
            $custom_field_options  = dt_get_option( 'dt_field_customizations' );
            $custom_contact_fields = $custom_field_options['contacts'];

            foreach ( $fields as $field_key => $field_settings ){
                if ( ! in_array( $field_key, $channel_fields ) ) {
                    continue;
                }
                if ( isset( $_POST['channel_label'][$field_key]['default'] ) ){
                    $label = sanitize_text_field( wp_unslash( $_POST['channel_label'][$field_key]['default'] ) );
                    if ( $field_settings['name'] != $label ){
                        $custom_contact_fields[$field_key]['name'] = $label;
                    }
                }
                foreach ( $langs as $lang => $val ){
                    $langcode = $val['language'];

                    if ( isset( $_POST['channel_label'][$field_key][$langcode] ) ) {
                        $translated_label = sanitize_text_field( wp_unslash( $_POST['channel_label'][$field_key][$langcode] ) );
                        if ( ( empty( $translated_label ) && !empty( $custom_contact_fields[$field_key]['translations'][$langcode] ) ) || !empty( $translated_label ) ) {
                            $custom_contact_fields[$field_key]['translations'][$langcode] = $translated_label;
                        }
                    }
                }
                if ( isset( $_POST['channel_icon'][ $field_key ] ) ) {

                    // Determine icon keys
                    $icon          = sanitize_text_field( wp_unslash( $_POST['channel_icon'][ $field_key ] ) );
                    $icon_key      = ( ! empty( $icon ) && strpos( $icon, 'mdi mdi-' ) === 0 ) ? 'font-icon' : 'icon';
                    $null_icon_key = ( $icon_key === 'font-icon' ) ? 'icon' : 'font-icon';

                    // Update icon accordingly and nullify alternative
                    if ( ! isset( $channel_options[ $icon_key ] ) || $channel_options[ $icon_key ] != $icon ) {
                        $custom_contact_fields[ $field_key ][ $icon_key ]      = $icon;
                        $custom_contact_fields[ $field_key ][ $null_icon_key ] = null;
                    }
                }
                if ( isset( $_POST['channel_enabled'][$field_key] ) ){
                    $custom_contact_fields[$field_key]['enabled'] = true;
                } else {
                    $custom_contact_fields[$field_key]['enabled'] = false;
                }
                if ( isset( $_POST['channel_hide_domain'][$field_key] ) ){
                    $custom_contact_fields[$field_key]['hide_domain'] = true;
                } else {
                    $custom_contact_fields[$field_key]['hide_domain'] = false;
                }
                if ( isset( $_POST['channel_reset_icon'][$field_key] ) ){
                    unset( $custom_contact_fields[$field_key]['icon'] );
                }
            }
            if ( !empty( $_POST['add_channel'] ) ){
                $label = sanitize_text_field( wp_unslash( $_POST['add_channel'] ) );
                $key = dt_create_field_key( 'contact_' . $label );
                if ( !empty( $key ) ){
                    if ( isset( $custom_contact_fields[$key] ) ){
                        self::admin_notice( __( 'This channel already exists', 'disciple_tools' ), 'error' );
                    } else {
                        $custom_contact_fields[ $key ] = [
                            'name'       => $label,
                            'type'       => 'communication_channel',
                            'tile'       => 'details',
                            'enabled'    => true
                        ];
                        wp_cache_delete( 'contacts_field_settings' );
                    }
                }
            }
            $custom_field_options['contacts'] = $custom_contact_fields;
            update_option( 'dt_field_customizations', $custom_field_options );
        }
    }


     /**
     * Display admin notice
     * @param $notice string
     * @param $type string error|success|warning
     */
    public static function admin_notice( string $notice, string $type ) {
        ?>
        <div class="notice notice-<?php echo esc_attr( $type ) ?> is-dismissible">
            <p><?php echo esc_html( $notice ) ?></p>
        </div>
        <?php
    }


    /**
     * UI for picking languages
     */
    private function languages_box(){
        $languages = dt_get_option( 'dt_working_languages' ) ?: [];
        $dt_global_languages_list = dt_get_global_languages_list();
        uasort($dt_global_languages_list, function( $a, $b ) {
            return strcmp( $a['label'], $b['label'] );
        });
        $form_name = 'languages_box';
        ?>
            <table class="widefat" id='language_table'>
                <thead>
                <tr>
                    <td><?php esc_html_e( 'Key', 'disciple_tools' ) ?></td>
                    <td><?php esc_html_e( 'Default Label', 'disciple_tools' ) ?></td>
                    <td><?php esc_html_e( 'Custom Label', 'disciple_tools' ) ?></td>
                    <td><?php esc_html_e( 'ISO 639-3 code', 'disciple_tools' ) ?></td>
                    <td><?php esc_html_e( 'Enabled', 'disciple_tools' ) ?></td>
                    <td><?php esc_html_e( 'Translation', 'disciple_tools' ) ?></td>
                </tr>
                </thead>
                <tbody>
                <?php foreach ( $languages as $language_key => $language_option ) :

                    $enabled = !isset( $language_option['deleted'] ) || $language_option['deleted'] == false; ?>
                    
                    <tr class="language-row" data-lang="<?php echo esc_html( $language_key ) ?>">
                        <td class='lang_key'><?php echo esc_html( $language_key ) ?></td>
                        <td class='default_label'><?php echo esc_html( isset( $dt_global_languages_list[$language_key] ) ? $dt_global_languages_list[$language_key]['label'] : '' ) ?></td>
                        <td class='custom_label'><input type="text" placeholder="Custom Label" name="language_label[<?php echo esc_html( $language_key ) ?>]" value="<?php echo esc_html( ( !isset( $dt_global_languages_list[$language_key] ) || ( isset( $dt_global_languages_list[$language_key] ) && $dt_global_languages_list[$language_key]['label'] != $language_option['label'] ) ) ? $language_option['label'] : '' ) ?>"></td>
                        <td class='iso_code'><input type="text" placeholder="ISO 639-3 code" maxlength="3" name="<?php echo esc_html( $language_option['iso_639-3'] ?? '' ) ?>" value="<?php echo esc_html( $language_option['iso_639-3'] ?? '' ) ?>"></td>

                        <td class='enabled'>
                            <input name="language_enabled[<?php echo esc_html( $language_key ) ?>]"
                                   type="checkbox" <?php echo esc_html( $enabled ? 'checked' : '' ) ?> />
                        </td>

                        <td class='translation_key'>
                            <?php $langs = dt_get_available_languages(); ?>
                            <button class="button small expand_translations"
                                    data-source="dt_languages"
                                    data-value="<?php echo esc_html( $language_key ); ?>"
                                    data-callback="update_language_translations"
                            >
                                <?php
                                $number_of_translations = 0;
                                foreach ( $langs as $lang => $val ){
                                    if ( !empty( $language_option['translations'][$val['language']] ) ){
                                        $number_of_translations++;
                                    }
                                }
                                ?>
                                <img style="height: 15px; vertical-align: middle" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/languages.svg' ); ?>">
                                (<?php echo esc_html( $number_of_translations ); ?>)
                            </button>
                            <div class="translation_container hide">
                                <table>
                                    <?php foreach ( $langs as $lang => $val ) : ?>
                                        <tr>
                                            <td><label for="language_label[<?php echo esc_html( $language_key ) ?>][<?php echo esc_html( $val['language'] )?>]"><?php echo esc_html( $val['native_name'] )?></label></td>
                                            <td><input
                                                class="language_label_translations"
                                                data-field="<?php echo esc_html( $language_key ); ?>"
                                                data-value="<?php echo esc_html( $val['language'] ) ?>"
                                                name="language_label[<?php echo esc_html( $language_key ) ?>][<?php echo esc_html( $val['language'] )?>]"
                                                type="text"
                                                value="<?php echo esc_html( $language_option['translations'][$val['language']] ?? '' );?>"/></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </table>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <br>
            <button id="save_lang_button" type="submit" class="button" style="float:right;">
                <?php esc_html_e( 'Save', 'disciple_tools' ) ?>
            </button>
            <form method="post" name="<?php echo esc_html( $form_name ) ?>" id="languages">
            <input type="hidden" name="languages_box_nonce" value="<?php echo esc_attr( wp_create_nonce( 'languages_box' ) ) ?>" />
            <br><button type="button" onclick="jQuery('#add_language').toggle();" class="button">
                <?php echo esc_html__( 'Add new language', 'disciple_tools' ) ?></button>
            <div id="add_language" style="display:none;">
                <hr>
                <p><?php esc_html_e( 'Select from the language list', 'disciple_tools' ); ?></p>
                <select name="new_lang_select" id="new_lang_select">
                    <option id></option>
                    <?php foreach ( $dt_global_languages_list as $lang_key => $lang_option ) : ?>
                        <option name="<?php echo esc_html( $lang_key ); ?>" value="<?php echo esc_html( $lang_key ); ?>"><?php echo esc_html( $lang_option['label'] ?? '' ); ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="button" id="add_lang_button"><?php esc_html_e( 'Add', 'disciple_tools' ) ?></button>
                <br>
                <br>
                <p><?php esc_html_e( 'If your language is not in the list, you can create it manually', 'disciple_tools' ); ?></p>
                <input name="create_custom_language" placeholder="Custom Language" type="text">
                <input name="create_custom_language_code" placeholder="ISO 639-3 code (optional)" maxlength="3" type="text">
                <button type="submit" class="button"><?php esc_html_e( 'Create', 'disciple_tools' ) ?></button>
            </div>
        </form>
        <?php
    }

    private function process_languages_box() {
        if ( !isset( $_POST['languages_box_nonce'] ) ) {
            return;
        }
        if ( !wp_verify_nonce( sanitize_key( $_POST['languages_box_nonce'] ), 'languages_box' ) ) {
            self::admin_notice( __( 'Something went wrong', 'disciple_tools' ), 'error' );
            return;
        }

        $languages = dt_get_option( 'dt_working_languages' ) ?: [];
        $dt_global_languages_list = dt_get_global_languages_list();

        if ( !empty( $_POST['new_lang_select'] ) ) {
            $lang_key = sanitize_text_field( wp_unslash( $_POST['new_lang_select'] ) );
            $lang = isset( $dt_global_languages_list[ $lang_key ] ) ? $dt_global_languages_list[ $lang_key ] : null;
            if ( $lang === null ) {
                return;
            };
            $languages[$lang_key] = $dt_global_languages_list[ $lang_key ];
            $languages[$lang_key]['enabled'] = true;
            $languages[$lang_key]['label'] = '';
        }
        if ( !empty( $_POST['create_custom_language'] ) ) {
            $language = sanitize_text_field( wp_unslash( $_POST['create_custom_language'] ) );
            $code = isset( $_POST['create_custom_language_code'] ) ?sanitize_text_field( wp_unslash( $_POST['create_custom_language_code'] ) ) : null;
            $lang_key = dt_create_field_key( $language );
            if ( isset( $dt_global_languages_list[$lang_key] ) || isset( $languages[$lang_key] ) ) {
                $lang_key = dt_create_field_key( $language, true );
            }
            $languages[$lang_key] = [
                'label' => $language,
                'enabled' => true
            ];
            if ( !empty( $code ) ){
                $languages[$lang_key]['iso_639-3'] = $code;
            }
        }

        update_option( 'dt_working_languages', $languages, false );
    }


    /**
     * UI for Quick Actions
     */
    private function quick_actions_box(){
        $fields = DT_Posts::get_post_settings( 'contacts' )['fields'];
        $default_fields = apply_filters( 'dt_custom_fields_settings', [], 'contacts' );
        $form_name = 'quick_actions_box';
        ?>
        <form method="post" name="<?php echo esc_html( $form_name ) ?>" id="quick-actions">
            <input type="hidden" name="quick_actions_box_nonce" value="<?php echo esc_attr( wp_create_nonce( 'quick_actions_box' ) ) ?>" />
            <table class="widefat">
                <thead>
                <tr>
                    <td></td>
                    <td><?php esc_html_e( 'Name', 'disciple_tools' ) ?></td>
                    <td><?php esc_html_e( 'Key', 'disciple_tools' ); ?></td>
                    <td><?php esc_html_e( 'Icon link (must be https or mdi webfont)', 'disciple_tools' ) ?></td>
                    <td></td>
                    <td><?php esc_html_e( 'Translation', 'disciple_tools' ) ?></td>
                    <td><?php esc_html_e( 'Delete', 'disciple_tools' ) ?></td>
                </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ( $fields as $field_key => $field_settings ) :
                        if ( ! isset( $field_settings['section'] ) || substr( $field_settings['section'], 0, 13 ) !== 'quick_buttons' ) {
                            continue;
                        }
                        ?>
                        <tr>
                            <td>
                                <?php if ( isset( $field_settings['icon'] ) && ! empty( $field_settings['icon'] ) ): ?>
                                    <img style="width: 20px; vertical-align: middle;"
                                         src="<?php echo esc_attr( $field_settings['icon'] ); ?>"
                                         class="quick-action-menu">

                                <?php elseif ( isset( $field_settings['font-icon'] ) && ! empty( $field_settings['font-icon'] ) ): ?>
                                    <i class="<?php echo esc_attr( $field_settings['font-icon'] ); ?>"
                                       style="font-size: 20px; vertical-align: middle;"></i>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                if ( !isset( $default_fields[$field_key] ) ) {
                                    echo '<input type="text" name="edit_field[' . esc_attr( $field_key ) . ']" value="'. esc_html( $field_settings['name'] ) . '">';
                                } else {
                                    echo esc_html( $field_settings['name'] );
                                    echo '<input type="hidden" name="edit_field[' . esc_attr( $field_key ) . ']" value="'. esc_html( $field_settings['name'] ) . '">';
                                } ?>
                            </td>
                            <td><?php echo esc_html( $field_key ); ?></td>
                            <td class="quick-action-menu"><input type="text"
                                                                 name="edit_field_icon[<?php echo esc_attr( $field_key ); ?>]"
                                                                 value="<?php echo esc_html( $field_settings['icon'] ?? ( $field_settings['font-icon'] ?? '' ) ) ?>">
                            </td>
                            <td>
                                <button class="button change-icon-button" data-form="quick_actions_box"
                                        data-icon-input="edit_field_icon[<?php echo esc_attr( $field_key ); ?>]"><?php esc_html_e( 'Change Icon', 'disciple_tools' ); ?></button>
                            </td>
                            <td>
                                <?php $langs = dt_get_available_languages(); ?>
                                <button class="button small expand_translations"
                                        data-form_name="<?php echo esc_html( $form_name ) ?>"
                                        data-source="lists">
                                    <?php
                                    $number_of_translations = 0;
                                    foreach ( $langs as $lang => $val ) {
                                        if ( !empty( $fields[$field_key]['translations'][$val['language']] ) ) {
                                            $number_of_translations++;
                                        }
                                    }
                                    ?>
                                    <img style="height: 15px; vertical-align: middle" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/languages.svg' ); ?>">
                                    (<?php echo esc_html( $number_of_translations ); ?>)
                                </button>
                                <div class="translation_container hide">
                                    <table>
                                    <?php foreach ( $langs as $lang => $val ) : ?>
                                        <tr>
                                            <td><label for="field_label[<?php echo esc_html( $field_key ) ?>][<?php echo esc_html( $val['language'] )?>]"><?php echo esc_html( $val['native_name'] )?></label></td>
                                            <td><input name="field_label[<?php echo esc_html( $field_key ) ?>][<?php echo esc_html( $val['language'] )?>]" type="text" value="<?php echo esc_html( $fields[$field_key]['translations'][$val['language']] ?? '' );?>"/></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </table>
                                </div>
                            </td>
                            <td>
                                <?php
                                if ( !isset( $default_fields[$field_key] ) ){
                                    echo '<button type="submit" name="delete_field" value="' . esc_attr( $field_key ) . '" class="button small">' . esc_html( __( 'Delete', 'disciple_tools' ) ) . '</button>';
                                } ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <br><button type="button" onclick="jQuery('#add_quick_action').toggle();" class="button">
                <?php echo esc_html__( 'Add new quick action', 'disciple_tools' ) ?></button>
            <button type="submit" style="float:right;" class="button"><?php echo esc_html( __( 'Save', 'disciple_tools' ) ) ?></button>
            <div id="add_quick_action" style="display:none;">
                <hr>
                <label for="add_custom_quick_action_label">Name:</label>
                <input name="add_custom_quick_action_label" placeholder="Custom Quick Action" type="text">
                <br>
                <br>
                <div class="menuitem">
                    <label for="default">Default Icon:</label>
                    <input type="radio" name="icon" value="default" checked><img src="<?php echo esc_html( get_template_directory_uri() ); ?>/dt-assets/images/contact.svg"></div>
                    <br>
                    <label for="custom">Custom Icon URL:</label>
                    <input type="radio" name="icon" value="custom">
                    <input name="add_custom_quick_action_icon" type="text">
                    <br>
                    <button type="submit" class="button"><?php esc_html_e( 'Add', 'disciple_tools' ) ?></button>
            </div>
        </form>
        <?php
    }

    public function process_quick_actions_box(){
        if ( !isset( $_POST['quick_actions_box_nonce'] ) ){
            return;
        }
        if ( !wp_verify_nonce( sanitize_key( $_POST['quick_actions_box_nonce'] ), 'quick_actions_box' ) ){
            self::admin_notice( __( 'Something went wrong', 'disciple_tools' ), 'error' );
            return;
        }

        $fields = DT_Posts::get_post_field_settings( 'contacts' );
        $langs = dt_get_available_languages();
        $custom_field_options = dt_get_option( 'dt_field_customizations' );
        $custom_contact_fields = $custom_field_options['contacts'];

        foreach ( $fields as $field_key => $field_settings ) {
            foreach ( $langs as $lang => $val ) {
                $langcode = $val['language'];
                if ( isset( $_POST['field_label'][$field_key][$langcode] ) ) {
                    $translated_label = sanitize_text_field( wp_unslash( $_POST['field_label'][$field_key][$langcode] ) );
                    // Add new translation
                    if ( !empty( $translated_label ) ) {
                        $custom_contact_fields[$field_key]['translations'][$langcode] = $translated_label;
                    }

                    // Remove translation
                    if ( ( empty( $translated_label ) && !empty( $custom_contact_fields[$field_key]['translations'][$langcode] ) ) ) {
                        $custom_contact_fields[$field_key]['translations'][$langcode] = $translated_label;
                    }
                }
            }
        }

        $custom_field_options['contacts'] = $custom_contact_fields;
        update_option( 'dt_field_customizations', $custom_field_options );


        // Add a new custom field
        if ( ! empty( $_POST['add_custom_quick_action_label'] ) ) {
            $label = sanitize_text_field( wp_unslash( $_POST['add_custom_quick_action_label'] ) );
            $key = dt_create_field_key( 'quick_button_' . $label );

            // Check quick action icon
            if ( ! empty( $_POST['add_custom_quick_action_icon'] ) ) {
                $icon_url = sanitize_text_field( wp_unslash( $_POST['add_custom_quick_action_icon'] ) );
            } else {
                $icon_url = get_template_directory_uri() . '/dt-assets/images/contact.svg';
            }

            if ( empty( $label ) ) {
                wp_die( 'Quick Action Update Error: Label is missing' );
            }

            if ( empty( $key ) ) {
                wp_die( 'Quick Action Update Error: Key is missing' );
            } else {
                // Add new Quick Action
                $key = dt_create_field_key( $key, true );
                $custom_field_options['contacts'][$key] = [
                    'name'        => $label,
                    'description' => '',
                    'type'        => 'number',
                    'default'     => 0,
                    'section'     => 'quick_buttons',
                    'icon'        => $icon_url,
                    'customizable' => false,
                ];

                update_option( 'dt_field_customizations', $custom_field_options, true );
                wp_cache_delete( 'contacts_field_settings' );

                self::admin_notice( __( 'Quick Action added successfully', 'disciple_tools' ), 'success' );
                return;
            }
        }

        // Delete Quick Action
        if ( ! empty( $_POST['delete_field'] ) ) {
            $delete_key = sanitize_text_field( wp_unslash( $_POST['delete_field'] ) );

            unset( $custom_field_options['contacts'][ $delete_key ] );
            update_option( 'dt_field_customizations', $custom_field_options, true );

            wp_cache_delete( 'contacts_field_settings' );
            self::admin_notice( __( 'Quick Action deleted successfully', 'disciple_tools' ), 'success' );
            return;
        }

        // Edit Quick Action
        if ( ! empty( $_POST['edit_field'] ) ) {

            $quick_action_edits = dt_recursive_sanitize_array( $_POST['edit_field'] );

            if ( isset( $_POST['edit_field_icon'] ) ) {
                $edit_field_icon = dt_recursive_sanitize_array( $_POST['edit_field_icon'] );
            } else {
                $edit_field_icon = get_template_directory_uri() . '/dt-assets/images/contact.svg';
            }

            foreach ( $quick_action_edits as $quick_action_key => $quick_action_new_name ) {
                $quick_action_key = sanitize_text_field( wp_unslash( $quick_action_key ) );
                $quick_action_new_name = sanitize_text_field( wp_unslash( $quick_action_new_name ) );
                $custom_field_options['contacts'][ $quick_action_key ]['name'] = $quick_action_new_name;
            }

            foreach ( $edit_field_icon as $key => $value ) {

                // Determine icon keys
                $icon_key      = ( ! empty( $value ) && strpos( $value, 'mdi mdi-' ) === 0 ) ? 'font-icon' : 'icon';
                $null_icon_key = ( $icon_key === 'font-icon' ) ? 'icon' : 'font-icon';

                // Update icon accordingly and nullify alternative
                $custom_field_options['contacts'][ $key ][ $icon_key ]      = $value;
                $custom_field_options['contacts'][ $key ][ $null_icon_key ] = null;
            }

            update_option( 'dt_field_customizations', $custom_field_options, true );
            wp_cache_delete( 'contacts_field_settings' );

            self::admin_notice( __( 'Quick Action edited successfully', 'disciple_tools' ), 'success' );
            return;
        }
    }
}
Disciple_Tools_Tab_Custom_Lists::instance();
