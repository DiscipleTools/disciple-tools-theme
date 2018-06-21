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
    public static function instance()
    {
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
    public function __construct()
    {
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
        echo '">Custom Lists</a>';
    }

    /**
     * Packages and prints tab page
     */
    public function content( $tab )
    {
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

            /* milestones */
            $this->box( 'top', 'Milestones' );
            $this->process_milestones_box();
            $this->milestones_box(); // prints
            $this->box( 'bottom' );
            /* end milestones */

            /* seeker path */
            $this->box( 'top', 'Seeker Path' );
            $this->process_seeker_path_box();
            $this->seeker_path_box(); // prints

            $this->box( 'bottom' );
            /* end seeker path */

            /* reason closed */
            $this->box( 'top', 'Reason Closed' );
            $this->process_reason_closed_box();
            $this->reason_closed_box(); // prints
            $this->box( 'bottom' );
            /* end reason closed */

            $this->template( 'right_column' );

            $this->template( 'end' );

        endif;
    }

    /**
     * Print the contact settings box.
     */
    public function user_profile_box()
    {
        echo '<form method="post" name="user_fields_form">';
        echo '<button type="submit" class="button-like-link" name="user_fields_reset" value="1">reset</button>';
        echo '<p>You can add or remove types of contact fields for worker profiles.</p>';
        echo '<input type="hidden" name="user_fields_nonce" id="user_fields_nonce" value="' . esc_attr( wp_create_nonce( 'user_fields' ) ) . '" />';
        echo '<table class="widefat">';
        echo '<thead><tr><td>Label</td><td>Type</td><td>Description</td><td>Enabled</td><td>Delete</td></tr></thead><tbody>';

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
                        <td><button type="submit" name="delete_field" value="' . esc_attr( $field['key'] ) . '" class="button small" >delete</button> </td>
                      </tr>';
        }
        // end list block

        echo '</table>';
        echo '<br><button type="button" onclick="jQuery(\'#add_user\').toggle();" class="button">Add</button>
                        <button type="submit" style="float:right;" class="button">Save</button>';
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
                    <button type="submit">Add</button>
                    </td></tr></table></div>';

        echo '</tbody></form>';
    }

    /**
     * Process user profile settings
     */
    public function process_user_profile_box()
    {

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
    public function sources_box()
    {
        echo '<form method="post" name="sources_form">';
        echo '<button type="submit" class="button-like-link" name="sources_reset" value="1">reset</button>';
        echo '<p>Add or remove sources for new contacts.</p>';
        echo '<input type="hidden" name="sources_nonce" id="sources_nonce" value="' . esc_attr( wp_create_nonce( 'sources' ) ) . '" />';
        echo '<table class="widefat">';
        echo '<thead><tr><td>Label</td><td>Enabled</td><td>Delete</td></tr></thead><tbody>';

        // custom list block
        $site_custom_lists = dt_get_option( 'dt_site_custom_lists' );
        if ( ! $site_custom_lists ) {
            wp_die( 'Failed to get dt_site_custom_lists() from options table.' );
        }
        $sources = $site_custom_lists['sources'];
        foreach ( $sources as $source ) {
            echo '<tr>
                        <td>' . esc_attr( $source['label'] ) . '</td>
                        <td><input name="sources[' . esc_attr( $source['key'] ) . ']" type="checkbox" ' . ( $source['enabled'] ? "checked" : "" ) . ' /></td>
                        <td><button type="submit" name="delete_field" value="' . esc_attr( $source['key'] ) . '" class="button small" >delete</button> </td>
                      </tr>';
        }
        // end list block

        echo '</table>';
        echo '<br><button type="button" onclick="jQuery(\'#add_source\').toggle();" class="button">Add</button>
                        <button type="submit" style="float:right;" class="button">Save</button>';
        echo '<div id="add_source" style="display:none;">';
        echo '<table width="100%"><tr><td><hr><br>
                    <input type="text" name="add_input_field[label]" placeholder="label" />&nbsp;';
        echo '<button type="submit">Add</button>
                    </td></tr></table></div>';

        echo '</tbody></form>';
    }

    /**
     * Process contact sources settings
     */
    public function process_sources_box()
    {

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
     * Prints the mielsteons settings box.
     */
    public function milestones_box()
    {
        echo '<form method="post" name="milestones_form">';
        //echo '<button type="submit" class="button-like-link" name="milestones_reset" value="1">reset</button>';
        echo '<p>Add or remove custom milestones for new contacts.</p>';
        echo '<input type="hidden" name="milestones_nonce" id="milestones_nonce" value="' . esc_attr( wp_create_nonce( 'milestones' ) ) . '" />';
        echo '<table class="widefat">';
        echo '<thead><tr><td>'. esc_html( __( "Label", 'disciple_tools' ) ) . '</td><td>'. esc_html( __( "Delete", 'disciple_tools' ) ) . '</td></tr></thead><tbody>';

        // get the list of custom lists
        $site_custom_lists = dt_get_option( 'dt_site_custom_lists' );
        //empty check
        if ( ! $site_custom_lists ) {
            wp_die( 'Failed to get custom list from options table.' );
        }
        $site_custom_lists = $site_custom_lists["custom_milestones"];
        //for each milestone put it on the list
        foreach ( $site_custom_lists as $milestone => $value) {
            if ( strpos( $milestone, "milestone_" ) === 0 ) {
                //get the first value
                reset( $value["default"] );
                $first_key = key( $value["default"] );
                //parse the name into pretty format
                $name = $value["name"];
                //echo $first_key;
                echo '<tr>
                            <td><input type="text" name=' . esc_html( $milestone ) . ' value = "' . esc_html( $name ) . '"></input></td>
                            <td><button type="submit" name="delete_field" value="' . esc_html( $milestone ) . '" class="button small" >delete</button> </td>
                        </tr>';
            }
        }

        // end list block
        echo '</table>';
        echo '<br><button type="button" onclick="jQuery(\'#add_milestone\').toggle();" class="button">Add</button>
                        <button type="submit" style="float:right;" class="button">Save</button>';
        echo '<div id="add_milestone" style="display:none;">';
        echo '<table width="100%"><tr><td><hr><br>
                    <input type="text" name="add_input_field[label]" placeholder="label" />&nbsp;';
        echo '<button type="submit">Add</button>
                    </td></tr></table></div>';
        echo '</tbody></form>';
    }

    /**
     * Process milestones milestones settings
     */
    public function process_milestones_box()
    {
        global $wpdb;

        if ( isset( $_POST['milestones_nonce'] ) ) {
            $delete = true;  //for the bug where you press enter and it deltes a key
            if ( !wp_verify_nonce( sanitize_key( $_POST['milestones_nonce'] ), 'milestones' ) ) {
                return;
            }

            //get the custom list of lists
            $site_custom_lists = dt_get_option( 'dt_site_custom_lists' );
            // Process current fields submitted
            if ( ! $site_custom_lists ) {
                wp_die( 'Failed to get dt_site_custom_lists() from options table.' );
            }
            //make a new milestone object
            if ( !empty( $_POST['add_input_field']['label'] ) ) {
                $delete = false; //for the enter bug
                //make the label
                $label = sanitize_text_field( wp_unslash( $_POST['add_input_field']['label'] ) );
                //for the key add the _ for spaces
                $key = "milestone_".str_replace( " ", "_", $label );
                //set all the values note for right now the default is ALWAYS NO
                $site_custom_lists["custom_milestones"][$key] = [
                        'name'        => $label,
                        'description' => '',
                        'type'        => 'key_select',
                        'default'     => [
                            'no' => __( 'No', 'disciple_tools' ),
                            'yes' => __( 'Yes', 'disciple_tools' )
                        ],
                        'section'     => 'milestone',
                    ];
            }
            //edit name
            // for each custom object with the start of milestone_ make sure name is up to date
            foreach ( $_POST as $milestone => $value ) {
                if ( strpos( $milestone, "milestone_" ) === 0 && $milestone != 'milestones_nonce' ) {
                    //delete key
                    $key = $_POST[$milestone];
                    if ( $site_custom_lists["custom_milestones"][$milestone]['name'] != $key ) {
                        $delete = false; //for the enter bug
                        //set new label value
                        $label = sanitize_text_field( wp_unslash( $value ) );
                        //set all the values note for right now the default is ALWAYS NO
                        $site_custom_lists["custom_milestones"][$milestone]['name'] = $label;
                    }
                }
            }
            // Process a field to delete.
            if ( isset( $_POST['delete_field'] ) && $delete ) {
                $delete_key = sanitize_text_field( wp_unslash( $_POST['delete_field'] ) );
                unset( $site_custom_lists["custom_milestones"][ $delete_key ] );
                //TODO: Consider adding a database query to delete all instances of this key from usermeta

            }
            // Process reset request
            if ( isset( $_POST['milestones_reset'] ) ) {
                unset( $site_custom_lists["custom_milestones"] );
                $site_custom_lists["custom_milestones"] = [];
            }
            // Update the site option
            update_option( 'dt_site_custom_lists', $site_custom_lists, true );
        }
    }

    /**
     * Process contact seeker_path settings
     */
    public function process_seeker_path_box()
    {
        if ( isset( $_POST['seeker_path_nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['seeker_path_nonce'] ) ), 'seeker_path' ) ) {
            $delete = true;
            $site_custom_lists = dt_get_option( 'dt_site_custom_lists' );
            //get the custom list of lists
            //$site_custom_lists = dt_get_option( 'dt_site_custom_lists' );
            //checks if default optiions in custom
            $seek = dt_get_site_custom_lists( "seeker_path" ); //the standard ones
            if ( !$site_custom_lists ) {
                wp_die( 'Failed to get dt_site_custom_lists() from options table.' );
            }
            //make a new seeker object
            if ( !empty( $_POST['add_input_field']['label'] ) ) {
                $delete = false; //for the enter bug
                //make the label
                $label = sanitize_text_field( wp_unslash( $_POST['add_input_field']['label'] ) );
                //set label and name to same thing
                $site_custom_lists["seeker_path"][$label] = $label;
            }
            //edit name
            foreach ( $_POST["seeker_path"] as $key => $val) {
                $site_custom_lists["seeker_path"][$key] = $val;
            }
            // Process move up request
            if ( isset( $_POST['seeker_path_move_up'] ) ) {
                $new_seekers = [];
                $previous_key = null;
                reset( $site_custom_lists["seeker_path"] );
                $first_key = key( $site_custom_lists["seeker_path"] );
                $key = sanitize_text_field( $_POST['seeker_path_move_up'] );
                $item = array( $key => $site_custom_lists["seeker_path"][$key] );
                foreach ( $site_custom_lists["seeker_path"] as $h_key => $h_val ) {
                    if ( $h_key == $key && $previous_key != null && $previous_key != $first_key ) {
                        $previous = $new_seekers[$previous_key];
                        unset( $new_seekers[$previous_key] );
                        unset( $new_seekers[$h_key] );
                        $new_seekers += $item;
                        $new_seekers += array( $previous_key => $previous );
                    }
                    else {
                        $previous_key = $h_key;
                        $new_seekers += array( $h_key => $h_val );
                    }
                }
                $site_custom_lists["seeker_path"] = $new_seekers;
            }
            // Process move down request
            else if ( isset( $_POST['seeker_path_move_down'] ) ) {
                //reverse move up and reverser again this makes it go down
                $site_custom_lists["seeker_path"] = array_reverse( $site_custom_lists["seeker_path"] );
                $new_seekers = [];
                $previous_key = null;
                $key = sanitize_text_field( $_POST['seeker_path_move_down'] );
                $item = array( $key => $site_custom_lists["seeker_path"][$key] );
                foreach ( $site_custom_lists["seeker_path"] as $h_key => $h_val ) {
                    if ( $h_key == $key && $previous_key != null ) {
                        $previous = $new_seekers[$previous_key];
                        unset( $new_seekers[$previous_key] );
                        unset( $new_seekers[$h_key] );
                        $new_seekers += $item;
                        $new_seekers += array( $previous_key => $previous );
                    }
                    else {
                        $previous_key = $h_key;
                        $new_seekers += array( $h_key => $h_val );
                    }
                }
                $site_custom_lists["seeker_path"] = array_reverse( $new_seekers );
            }
            // Process a field to delete.
            else if ( isset( $_POST['delete_field'] ) && $delete ) {
                $delete_key = sanitize_text_field( wp_unslash( $_POST['delete_field'] ) );
                unset( $site_custom_lists["seeker_path"][ $delete_key ] );
                //TODO: Consider adding a database query to delete all instances of this key from usermeta
            }
            // Process reset request
            else if ( isset( $_POST['seeker_path_reset'] ) ) {
                // for each custom object with the start of seeker_ delete
                foreach ( $site_custom_lists["seeker_path"] as $seeker => $value ) {
                        unset( $site_custom_lists["seeker_path"] );
                        $site_custom_lists["seeker_path"] = $seek;
                }
            }
            // Update the site option
            update_option( 'dt_site_custom_lists', $site_custom_lists, true );
            dt_write_log( $_POST );
        }
    }

    /**
     * Prints the seeker_path settings box.
     */
    public function seeker_path_box()
    {
        $default = dt_get_site_custom_lists( "seeker_path" ); //the standard ones
        $seeker_path = dt_get_option( 'dt_site_custom_lists' );
        $seeker_path = $seeker_path["seeker_path"];
        $first = true;
        if ( ! $seeker_path ) {
            wp_die( 'Failed to get dt_site_custom_lists() from options table.' );
        }

        ?>
        <form method="post" name="seeker_path_form">
            <input type="hidden" name="seeker_path_nonce" id="seeker_path_nonce" value="<?php echo esc_attr( wp_create_nonce( 'seeker_path' ) ) ?>" />
            <button type="submit" class="button-like-link" name="seeker_path_reset_bug_fix" value="&nasb"></button>
            <button type="submit" class="button-like-link" name="seeker_path_reset" value="1">reset</button>

            <p>Add or remove seeker_path for new contacts.</p>

            <input type="hidden" name="seeker_path_nonce" id="seeker_path_nonce" value="<?php echo esc_attr( wp_create_nonce( 'seeker_path' ) ) ?>" />
            <table class="widefat">
                <thead>
                    <tr>
                        <td><?php esc_html_e( "Move", 'disciple_tools' ) ?></td>
                        <td><?php esc_html_e( "Label", 'disciple_tools' ) ?></td>
                        <td><?php esc_html_e( "Delete", 'disciple_tools' ) ?></td>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ( $seeker_path as $key => $label ) : ?>
                    <tr>
                        <td>
                        <?php if ( !$first ) : ?>
                            <button type="submit" name="seeker_path_move_up" value="<?php echo esc_html( $key ) ?>" class="button small" >↑</button>
                            <button type="submit" name="seeker_path_move_down" value="<?php echo esc_html( $key ) ?>" class="button small" >↓</button>
                        <?php endif; ?>
                        </td>
                        <td><input name="seeker_path[<?php echo esc_html( $key ) ?>]" type="text" value="<?php echo esc_html( $label ) ?>"/></td>
                        <?php if ( !in_array( $key, array_keys( $default ), true ) ) { ?>
                            <td><button type="submit" name="delete_field" value="<?php echo esc_html( $key ) ?>" class="button small" ><?php esc_html_e( "delete", 'disciple_tools' ) ?></button> </td>
                        <?php } ?>
                    </tr>
                    <?php $first = false; ?>
                <?php endforeach; ?>
                </tbody>
            </table>

            <br>
            <button type="button" onclick="jQuery('#add_seeker_path').toggle();" class="button">Add</button>
            <button type="submit" style="float:right;" class="button">Save</button>

            <div id="add_seeker_path" style="display:none;">
            <table width="100%">
                <tr>
                    <td><hr><br>
                        <input type="text" name="add_input_field[label]" placeholder="label" />&nbsp;
                    <button type="submit">Add</button>
                </td></tr>
            </table>
            </div>

        </form>
        <?php
    }

    /**
     * Process contact reason closed settings
     */
    public function process_reason_closed_box()
    {
        if ( isset( $_POST['reason_closed_nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['reason_closed_nonce'] ) ), 'reason_closed' ) ) {
            $delete = true;
            $site_custom_lists = Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings();
            $site_custom_lists = dt_get_option( 'dt_site_custom_lists' );
            $seek = dt_get_site_custom_lists( "seeker_path" ); //the standard ones
            $default = $site_custom_lists['default'];
            if ( !$site_custom_lists ) {
                wp_die( 'Failed to get dt_site_custom_lists() from options table.' );
            }
            //make a new seeker object
            if ( !empty( $_POST['add_input_field']['label'] ) ) {
                $delete = false; //for the enter bug
                //make the label
                $label = sanitize_text_field( wp_unslash( $_POST['add_input_field']['label'] ) );
                //set label and name to same thing
                $site_custom_lists["custom_reason_closed"][$label] = $label;
            }
            //edit name
            foreach ( $_POST["reason_closed"] as $key => $val) {
                $site_custom_lists["custom_reason_closed"][$key] = $val;
            }
            // Process a field to delete.
            if ( isset( $_POST['delete_field'] ) && $delete ) {
                $delete_key = sanitize_text_field( wp_unslash( $_POST['delete_field'] ) );
                unset( $site_custom_lists["custom_reason_closed"][ $delete_key ] );
                //TODO: Consider adding a database query to delete all instances of this key from usermeta
            }
            // Process reset request
            else if ( isset( $_POST['reason_closed_reset'] ) ) {
                // for each custom object with the start of seeker_ delete
                foreach ( $site_custom_lists["reason_closed"] as $seeker => $value ) {
                        unset( $site_custom_lists["custom_reason_closed"] );
                        $site_custom_lists["custom_reason_closed"] = $default;
                }
            }
            // Update the site option
            update_option( 'dt_site_custom_lists', $site_custom_lists, true );
            dt_write_log( $_POST );
        }
    }

    /**
     * Prints the reason settings box.
     */
    public function reason_closed_box()
    {
        //$default = Disciple_Tools_Contact_Post_Type::get_custom_fields_settings( "reason_closed" ); //the standard ones
        $reason_closed = Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings();
        $reason_closed = $reason_closed['reason_closed'];
        $default = $reason_closed['save'];
        $reason_closed = $reason_closed['default'];

        $first = true;
        if ( ! $reason_closed ) {
            wp_die( 'Failed to get dt_site_custom_lists() from options table.' );
        }

        ?>
        <form method="post" name="reason_closed_form">
            <input type="hidden" name="reason_closed_nonce" id="reason_closed_nonce" value="<?php echo esc_attr( wp_create_nonce( 'reason_closed' ) ) ?>" />
            <button type="submit" class="button-like-link" name="reason_closed_reset_bug_fix" value="&nasb"></button>
            <button type="submit" class="button-like-link" name="reason_closed_reset" value="1">reset</button>

            <p>Add or remove reason_closed for new contacts.</p>

            <input type="hidden" name="reason_closed_nonce" id="reason_closed_nonce" value="<?php echo esc_attr( wp_create_nonce( 'reason_closed' ) ) ?>" />
            <table class="widefat">
                <thead>
                    <tr>
                        <td><?php esc_html_e( "Label", 'disciple_tools' ) ?></td>
                        <td><?php esc_html_e( "Delete", 'disciple_tools' ) ?></td>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $reason_closed as $key => $label ) : ?>
                        <?php if ( $label != '' && $label !== false) : ?>
                            <tr>
                                <td><input name="reason_closed[<?php echo esc_html( $key ) ?>]" type="text" value="<?php echo esc_html( $label ) ?>"/></td>
                                <?php if ( !in_array( $key, array_keys( $default ) ) ) { ?>
                                    <td><button type="submit" name="delete_field" value="<?php echo esc_html( $key ) ?>" class="button small" ><?php esc_html_e( "delete", 'disciple_tools' ) ?></button> </td>
                                <?php } ?>
                            </tr>
                            <?php $first = false; ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <br>
            <button type="button" onclick="jQuery('#add_reason_closed').toggle();" class="button">Add</button>
            <button type="submit" style="float:right;" class="button">Save</button>

            <div id="add_reason_closed" style="display:none;">
            <table width="100%">
                <tr>
                    <td><hr><br>
                        <input type="text" name="add_input_field[label]" placeholder="label" />&nbsp;
                    <button type="submit">Add</button>
                </td></tr>
            </table>
            </div>

        </form>
        <?php
    }
}
Disciple_Tools_Tab_Custom_Lists::instance();
