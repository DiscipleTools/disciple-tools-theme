<?php

/**
 * Disciple Tools
 *
 * @class      Disciple_Tools_
 * @version    0.1.0
 * @since      0.1.0
 * @package    Disciple_Tools
 * @author     Chasm.Solutions & Kingdom.Training
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

            /* Worker Profile */
            $this->box( 'top', 'User (Worker) Contact Profile' );
            $this->process_user_profile_box();
            $this->user_profile_box(); // prints
            $this->box( 'bottom' );
            /* end Worker Profile */


            /* Channels */
            $this->box( 'top', __( 'Contact Communication Channels', 'disciple_tools' ) );
            $this->process_channels_box();
            $this->channels_box(); // prints
            $this->box( 'bottom' );
            /* end Channels */

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
        echo '<button type="submit" class="button-like-link" name="user_fields_reset" value="1">' . esc_html( __( "reset", 'disciple_tools' ) ) . '</button>';
        echo '<p>' . esc_html( __( "You can add or remove types of contact fields for worker profiles.", 'disciple_tools' ) ) . '</p>';
        echo '<input type="hidden" name="user_fields_nonce" id="user_fields_nonce" value="' . esc_attr( wp_create_nonce( 'user_fields' ) ) . '" />';
        echo '<table class="widefat">';
        echo '<thead><tr><td>Label</td><td>Type</td><td>Description</td><td>Enabled</td><td>' . esc_html( __( "Delete", 'disciple_tools' ) ) . '</td></tr></thead><tbody>';

        // custom list block
        $site_custom_lists = dt_get_option( 'dt_site_custom_lists' );
        if ( ! $site_custom_lists ) {
            wp_die( 'Failed to get dt_site_custom_lists() from options table.' );
        }
        $user_fields = $site_custom_lists['user_fields'];
        foreach ( $user_fields as $field ) {
            echo '<tr>
                        <td>' . esc_attr( $field['label'] ) . '</td>
                        <td>' . esc_attr( $field['type'] ) . '</td>
                        <td>' . esc_attr( $field['description'] ) . ' </td>
                        <td><input name="user_fields[' . esc_attr( $field['key'] ) . ']" type="checkbox" ' . ( $field['enabled'] ? "checked" : "" ) . ' /></td>
                        <td><button type="submit" name="delete_field" value="' . esc_attr( $field['key'] ) . '" class="button small" >' . esc_html( __( "Delete", 'disciple_tools' ) ) . '</button> </td>
                      </tr>';
        }
        // end list block

        echo '</table>';
        echo '<br><button type="button" onclick="jQuery(\'#add_user\').toggle();" class="button">' . esc_html( __( "Add", 'disciple_tools' ) ) . '</button>
                        <button type="submit" style="float:right;" class="button">' . esc_html( __( "Save", 'disciple_tools' ) ) . '</button>';
        echo '<div id="add_user" style="display:none;">';
        echo '<table width="100%"><tr><td><hr><br>
                    <input type="text" name="add_input_field[label]" placeholder="label" />&nbsp;';
        echo '<select name="add_input_field[type]" id="add_input_field_type">';
        // Iterate the options
        $user_fields_types = $site_custom_lists['user_fields_types'];
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


    public function channels_box(){
        $channels = Disciple_Tools_Contact_Post_Type::instance()->get_channels_list();
        ?>
        <form method="post" name="channels_box">
            <input type="hidden" name="channels_box_nonce" value="<?php echo esc_attr( wp_create_nonce( 'channels_box' ) ) ?>" />
            <table class="widefat">
                <thead>
                    <tr>
                        <td><?php esc_html_e( "Label", 'disciple_tools' ) ?></td>
                        <td><?php esc_html_e( "Key", 'disciple_tools' ) ?></td>
                        <td><?php esc_html_e( "Enabled", 'disciple_tools' ) ?></td>
                        <td><?php esc_html_e( "Hide domain if a url", 'disciple_tools' ) ?></td>
                        <td><?php esc_html_e( "Icon link (must be https)", 'disciple_tools' ) ?></td>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $channels as $channel_key => $channel_option ) :
                        $enabled = !isset( $channel_option['enabled'] ) || $channel_option['enabled'] !== false;
                        $hide_domain = isset( $channel_option['hide_domain'] ) && $channel_option['hide_domain'] == true;
                        if ( $channel_key == 'phone' || $channel_key == 'email' || $channel_key == 'address' ){
                            continue;
                        } ?>

                    <tr>
                        <td><input type="text" name="channel_label[<?php echo esc_html( $channel_key ) ?>]" value="<?php echo esc_html( $channel_option["label"] ?? $channel_key ) ?>"></td>
                        <td><?php echo esc_html( $channel_key ) ?></td>
                        <td>
                            <input name="channel_enabled[<?php echo esc_html( $channel_key ) ?>]"
                                   type="checkbox" <?php echo esc_html( $enabled ? "checked" : "" ) ?> />
                        </td>
                        <td>
                            <input name="channel_hide_domain[<?php echo esc_html( $channel_key ) ?>]"
                                   type="checkbox" <?php echo esc_html( $hide_domain ? "checked" : "" ) ?> />
                        </td>
                        <td>
                            <input type="text" name="channel_icon[<?php echo esc_html( $channel_key ) ?>]" value="<?php echo esc_html( $channel_option["icon"] ?? "" ) ?>">
                            <?php if ( !empty( $channel_option["icon"] ) ) : ?>
                            <button type="submit" class="button" name="channel_reset_icon[<?php echo esc_html( $channel_key ) ?>]"><?php esc_html_e( "Reset link", 'disciple_tools' ) ?></button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <br><button type="button" onclick="jQuery('#add_channel').toggle();" class="button">
                <?php echo esc_html_x( "Add new channel", 'communication channel (Phone, Email, Facebook)', 'disciple_tools' ) ?></button>
            <button type="submit" class="button" style="float:right;">
                <?php esc_html_e( "Save", 'disciple_tools' ) ?>
            </button>
            <div id="add_channel" style="display:none;">
                <hr>
                <input type="text" name="add_channel" placeholder="channel" />
                <button type="submit"><?php esc_html_e( "Add", 'disciple_tools' ) ?></button>
            </div>
        </form>
        <?php
    }

    public function process_channels_box(){

        if ( isset( $_POST["channels_box_nonce"] ) ){
            $channels = Disciple_Tools_Contact_Post_Type::instance()->get_channels_list();
            $custom_channels = dt_get_option( "dt_custom_channels" );
            if ( !wp_verify_nonce( sanitize_key( $_POST['channels_box_nonce'] ), 'channels_box' ) ){
                self::admin_notice( __( "Something went wrong", 'disciple_tools' ), "error" );
                return;
            }


            foreach ( $channels as $channel_key => $channel_options ){
                if ( !isset( $custom_channels[$channel_key] ) ){
                    $custom_channels[$channel_key] = [];
                }
                if ( isset( $_POST["channel_label"][$channel_key] ) ){
                    $label = sanitize_text_field( wp_unslash( $_POST["channel_label"][$channel_key] ) );
                    if ( $channel_options["label"] != $label ){
                        $custom_channels[$channel_key]["label"] = $label;
                    }
                }
                if ( isset( $_POST["channel_icon"][$channel_key] ) ){
                    $icon = sanitize_text_field( wp_unslash( $_POST["channel_icon"][$channel_key] ) );
                    if ( !isset( $channel_options["icon"] ) || $channel_options["icon"] != $icon ){
                        $custom_channels[$channel_key]["icon"] = $icon;
                    }
                }
                if ( isset( $_POST["channel_enabled"][$channel_key] ) ){
                    $custom_channels[$channel_key]["enabled"] = true;
                } else {
                    $custom_channels[$channel_key]["enabled"] = false;
                }
                if ( isset( $_POST["channel_hide_domain"][$channel_key] ) ){
                    $custom_channels[$channel_key]["hide_domain"] = true;
                } else {
                    $custom_channels[$channel_key]["hide_domain"] = false;
                }
                if ( isset( $_POST["channel_reset_icon"][$channel_key] ) ){
                    unset( $custom_channels[$channel_key]["icon"] );
                }
            }
            if ( !empty( $_POST["add_channel"] ) ){
                $label = sanitize_text_field( wp_unslash( $_POST["add_channel"] ) );
                $key = dt_create_field_key( $label );
                if ( !empty( $key ) ){
                    if ( isset( $channels[$key] ) ){
                        self::admin_notice( __( "This channel already exists", 'disciple_tools' ), "error" );
                    } else {
                        $custom_channels[$key] = [
                            "label" => $label,
                            "enabled" => true
                        ];
                    }
                }
            }


            update_option( "dt_custom_channels", $custom_channels );
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
}
Disciple_Tools_Tab_Custom_Lists::instance();


