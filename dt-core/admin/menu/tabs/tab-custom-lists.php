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

            /* Sources */
            $this->box( 'top', 'Sources' );
            $this->process_sources_box();
            $this->sources_box(); // prints
            $this->box( 'bottom' );
            /* end Sources */

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

    /**
     * Prints the sources settings box.
     */
    public function sources_box() {
        echo '<form method="post" name="sources_form">';
        echo '<button type="submit" class="button-like-link" name="enter_bug_fix" value="&nasb"></button>';
        echo '<button type="submit" class="button-like-link" name="sources_reset" value="1">' . esc_html( __( "reset", 'disciple_tools' ) ) . '</button>';
        echo '<p>' . esc_html( __( "Add or remove sources for new contacts.", 'disciple_tools' ) ) . '</p>';
        echo '<input type="hidden" name="sources_nonce" id="sources_nonce" value="' . esc_attr( wp_create_nonce( 'sources' ) ) . '" />';
        echo '<table class="widefat">';
        echo '<thead><tr><td>Label</td><td>Enabled</td><td>' . esc_html( __( "Delete", 'disciple_tools' ) ) . '</td></tr></thead><tbody>';

        // custom list block
        $site_custom_lists = dt_get_option( 'dt_site_custom_lists' );
        if ( ! $site_custom_lists ) {
            wp_die( 'Failed to get dt_site_custom_lists() from options table.' );
        }
        $sources = $site_custom_lists['sources'];
        foreach ( $sources as $source ) {
            echo '<tr>
                        <td><input type="text" name="sources_label[' . esc_attr( $source['key'] ) . ']" value = "' . esc_attr( $source['label'] ) . '"></input></td>
                        <td><input name="sources[' . esc_attr( $source['key'] ) . ']" type="checkbox" ' . ( $source['enabled'] ? "checked" : "" ) . ' /></td>
                        <td><button type="submit" name="delete_field" value="' . esc_attr( $source['key'] ) . '" class="button small" >' . esc_html( __( "Delete", 'disciple_tools' ) ) . '</button> </td>
                      </tr>';
        }
        // end list block

        echo '</table>';
        echo '<br><button type="button" onclick="jQuery(\'#add_source\').toggle();" class="button">' . esc_html( __( "Add", 'disciple_tools' ) ) . '</button>
                        <button type="submit" style="float:right;" class="button">' . esc_html( __( "Save", 'disciple_tools' ) ) . '</button>';
        echo '<div id="add_source" style="display:none;">';
        echo '<table width="100%"><tr><td><hr><br>
                    <input type="text" name="add_input_field[label]" placeholder="label" />&nbsp;';
        echo '<button type="submit">' . esc_html( __( 'Add', 'disciple_tools' ) ) . '</button>
                    </td></tr></table></div>';

        echo '</tbody></form>';
    }

    /**
     * Process contact sources settings
     */
    public function process_sources_box() {

        if ( isset( $_POST['sources_nonce'] ) ) {

            if ( !wp_verify_nonce( sanitize_key( $_POST['sources_nonce'] ), 'sources' ) ) {
                return;
            }

            // Process current fields submitted
            $site_custom_lists = dt_get_option( 'dt_site_custom_lists' );
            if ( ! $site_custom_lists ) {
                wp_die( 'Failed to get dt_site_custom_lists() from options table.' );
            }

            foreach ( $site_custom_lists['sources'] as $key => $value ) {
                if ( isset( $_POST['sources'][ $key ] ) ) {
                    $site_custom_lists['sources'][ $key ]['enabled'] = true;
                } else {
                    $site_custom_lists['sources'][ $key ]['enabled'] = false;
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

                $key = sanitize_key( strtolower( str_replace( ' ', '_', $label ) ) );
                $enabled = true;

                // strip and make lowercase process
                $site_custom_lists['sources'][ $key ] = [
                    'label'       => $label,
                    'key'         => $key,
                    'type'        => $type,
                    'description' => $description,
                    'enabled'     => $enabled,
                ];
            }

            //process a field edit
            // for each custom object with the start of milestone_ make sure name is up to date
            if ( isset( $_POST['sources_label'] ) ) {
                $sources_label = $_POST['sources_label']; // @codingStandardsIgnoreLine
                foreach ( $sources_label as $source => $value ) {
                    $source = sanitize_text_field( wp_unslash( $source ) );
                    $value = sanitize_text_field( wp_unslash( $value ) );
                    //set new label value
                    $label = $value;
                    //set all the values
                    $site_custom_lists['sources'][ $source ]['label'] = $label;
                }
            }

            // Process a field to delete.
            if ( isset( $_POST['delete_field'] ) ) {

                $delete_key = sanitize_text_field( wp_unslash( $_POST['delete_field'] ) );

                unset( $site_custom_lists['sources'][ $delete_key ] );
                //TODO: Consider adding a database query to delete all instances of this key from usermeta

            }

            // Process reset request
            if ( isset( $_POST['sources_reset'] ) ) {

                unset( $site_custom_lists['sources'] );

                $site_custom_lists['sources'] = dt_get_site_custom_lists( 'sources' );
            }

            // Update the site option
            update_option( 'dt_site_custom_lists', $site_custom_lists, true );
        }
    }


    /**
     * Process custom path settings
     */

    public function sanatize_all( $s ){
        $string = str_replace( ' ', '_', $s ); // Replaces all spaces with hyphens.
        $ret = preg_replace( '/[^A-Za-z0-9\-_]/', '', $string ); // Removes special chars.
        return $ret;
    }

}
Disciple_Tools_Tab_Custom_Lists::instance();


