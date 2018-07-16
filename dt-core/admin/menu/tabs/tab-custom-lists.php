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
     *
     * @param $tab
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

            /* status */
            $this->box( 'top', 'Status' );
            $this->process_status_box();
            $this->status_box(); // prints
            $this->box( 'bottom' );
            /* end status */

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

            /* reason paused */
            $this->box( 'top', 'Reason Paused' );
            $this->process_reason_paused_box();
            $this->reason_paused_box(); // prints
            $this->box( 'bottom' );
            /* end reason paused */

            /* reason unassignable */
            $this->box( 'top', 'Reason Unassignable' );
            $this->process_reason_unassignable_box();
            $this->reason_unassignable_box(); // prints
            $this->box( 'bottom' );
            /* end reason unassignable */

            /* health  */
            $this->box( 'top', 'Health' );
            $this->process_health_box();
            $this->health_box(); // prints
            $this->box( 'bottom' );
            /* end health */

            /* custom paths  */
            $this->box( 'top', 'Custom Dropdown Field' );
            $this->process_custom_dropdown_field_box();
            $this->custom_dropdown_field_box(); // prints
            $this->box( 'bottom' );
            /* end custo paths */


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

            //process a field edit
            // for each custom object with the start of milestone_ make sure name is up to date
            if ( isset( $_POST['sources_label'] ) ) {
                $sources_label = $_POST['sources_label']; // phpcs:ignore
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
     * Prints the status settings box.
     */
    public function status_box()
    {
        echo '<form method="post" name="status_form">';
        echo '<button type="submit" class="button-like-link" name="enter_bug_fix" value="&nasb"></button>';
        echo '<p>' . esc_html( __( "Add or remove custom status for new contacts.", 'disciple_tools' ) ) . '</p>';
        echo '<input type="hidden" name="status_nonce" id="status_nonce" value="' . esc_attr( wp_create_nonce( 'status' ) ) . '" />';
        echo '<table class="widefat">';
        echo '<thead><tr><td>'. esc_html( __( "Label", 'disciple_tools' ) ) . '</td><td>'. esc_html( __( "Delete", 'disciple_tools' ) ) . '</td></tr></thead><tbody>';

        // get the list of custom lists
        $site_custom_lists = dt_get_option( 'dt_site_custom_lists' );
        //empty check
        if ( ! $site_custom_lists ) {
            wp_die( 'Failed to get custom list from options table.' );
        }
        $site_custom_lists = $site_custom_lists["custom_status"];
        //for each status put it on the list
        foreach ( $site_custom_lists as $status => $value) {
            echo '<tr>
                        <td><input type="text" name="status[' . esc_attr( $status ) . ']" value = "' . esc_html( $value ) . '"></input></td>
                        <td><button type="submit" name="delete_field" value="' . esc_html( $status ) . '" class="button small" >' . esc_html( __( "Delete", 'disciple_tools' ) ) . '</button> </td>
                    </tr>';
        }

        // end list block
        echo '</table>';
        echo '<br><button type="button" onclick="jQuery(\'#add_status\').toggle();" class="button">' . esc_html( __( "Add", 'disciple_tools' ) ) . '</button>
                        <button type="submit" style="float:right;" class="button">' . esc_html( __( "Save", 'disciple_tools' ) ) . '</button>';
        echo '<div id="add_status" style="display:none;">';
        echo '<table width="100%"><tr><td><hr><br>
                    <input type="text" name="add_input_field[label]" placeholder="label" />&nbsp;';
        echo '<button type="submit">' . esc_html( __( 'Add', 'disciple_tools' ) ) . '</button>
                    </td></tr></table></div>';
        echo '</tbody></form>';
    }

    /**
     * Process status status settings
     */
    public function process_status_box()
    {
        if ( isset( $_POST['status_nonce'] ) ) {
            $delete = true;  //for the bug where you press enter and it deltes a key
            if ( !wp_verify_nonce( sanitize_key( $_POST['status_nonce'] ), 'status' ) ) {
                return;
            }

            //get the custom list of lists
            $site_custom_lists = dt_get_option( 'dt_site_custom_lists' );
            // Process current fields submitted
            if ( ! $site_custom_lists ) {
                wp_die( 'Failed to get dt_site_custom_lists() from options table.' );
            }
            //make a new status object
            if ( !empty( $_POST['add_input_field']['label'] ) ) {
                $delete = false; //for the enter bug
                //make the label
                $label = sanitize_text_field( wp_unslash( $_POST['add_input_field']['label'] ) );
                //for the key add the _ for spaces
                $key = str_replace( " ", "_", $label );
                //set all the values note for right now the default is ALWAYS NO
                $site_custom_lists["custom_status"][$key] = $label;
            }
            //edit name
            // for each custom object with the start of status_ make sure name is up to date
            if ( isset( $_POST["status"] ) ) {
                $sanitized_post_status = $_POST["status"]; // phpcs:ignore
                foreach ( $sanitized_post_status as $status => $value ) {
                    $status = sanitize_text_field( wp_unslash( $status ) );
                    $value = sanitize_text_field( wp_unslash( $value ) );
                    //set new label value
                    $label = $value;
                    //set all the values note for right now the default is ALWAYS NO
                    $site_custom_lists["custom_status"][ $status ] = $label;
                }
            }
            // Process a field to delete.
            if ( isset( $_POST['delete_field'] ) && $delete ) {
                $delete_key = sanitize_text_field( wp_unslash( $_POST['delete_field'] ) );
                unset( $site_custom_lists["custom_status"][ $delete_key ] );
                //TODO: Consider adding a database query to delete all instances of this key from usermeta

            }
            // Process reset request
            if ( isset( $_POST['status_reset'] ) ) {
                unset( $site_custom_lists["custom_status"] );
                $site_custom_lists["custom_status"] = [];
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
        //echo '<button type="submit" class="button-like-link" name="milestones_reset" value="1">' . esc_html( __( "reset", 'disciple_tools' ) ) . '</button>';
        echo '<p>' . esc_html( __( "Add or remove custom milestones for new contacts.", 'disciple_tools' ) ) . '</p>';
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
                            <td><button type="submit" name="delete_field" value="' . esc_html( $milestone ) . '" class="button small" >' . esc_html( __( "Delete", 'disciple_tools' ) ) . '</button> </td>
                        </tr>';
            }
        }

        // end list block
        echo '</table>';
        echo '<br><button type="button" onclick="jQuery(\'#add_milestone\').toggle();" class="button">' . esc_html( __( "Add", 'disciple_tools' ) ) . '</button>
                        <button type="submit" style="float:right;" class="button">' . esc_html( __( "Save", 'disciple_tools' ) ) . '</button>';
        echo '<div id="add_milestone" style="display:none;">';
        echo '<table width="100%"><tr><td><hr><br>
                    <input type="text" name="add_input_field[label]" placeholder="label" />&nbsp;';
        echo '<button type="submit">' . esc_html( __( 'Add', 'disciple_tools' ) ) . '</button>
                    </td></tr></table></div>';
        echo '</tbody></form>';
    }

    /**
     * Process milestones milestones settings
     */
    public function process_milestones_box()
    {
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
                $value = sanitize_text_field( wp_unslash( $value ) );
                if ( strpos( $milestone, "milestone_" ) === 0 && $milestone != 'milestones_nonce' ) {
                    //delete key
                    $key = $value;
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
            if ( isset( $_POST["seeker_path"] ) ) {
                $seeker_path = sanitize_text_field( wp_unslash( $_POST["seeker_path"] ) );
                foreach ( $seeker_path as $key => $val) {
                    $site_custom_lists["seeker_path"][$key] = $val;
                }
            }
            // Process move up request
            if ( isset( $_POST['seeker_path_move_up'] ) ) {
                $new_seekers = [];
                $previous_key = null;
                reset( $site_custom_lists["seeker_path"] );
                $first_key = key( $site_custom_lists["seeker_path"] );
                $key = sanitize_text_field( wp_unslash( $_POST['seeker_path_move_up'] ) );
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
                $key = sanitize_text_field( wp_unslash( $_POST['seeker_path_move_down'] ) );
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
            <button type="submit" class="button-like-link" name="seeker_path_reset" value="1"><?php echo esc_html( __( 'reset', 'disciple_tools' ) ) ?></button>

            <p><?php esc_html_e( "Add or remove seeker_path for new contacts.", 'disciple_tools' ) ?></p>

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
            <button type="button" onclick="jQuery('#add_seeker_path').toggle();" class="button"><?php esc_html_e( "Add", 'disciple_tools' ) ?></button>
            <button type="submit" style="float:right;" class="button"><?php esc_html_e( "Save", 'disciple_tools' ) ?></button>

            <div id="add_seeker_path" style="display:none;">
            <table width="100%">
                <tr>
                    <td><hr><br>
                        <input type="text" name="add_input_field[label]" placeholder="label" />&nbsp;
                    <button type="submit"><?php echo esc_html( __( 'Add', 'disciple_tools' ) ) ?></button>
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
            $site_custom_lists = dt_get_option( 'dt_site_custom_lists' );
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
            if ( isset( $_POST["reason_closed"] ) ) {
                foreach ( sanitize_text_field( wp_unslash( $_POST["reason_closed"] ) ) as $key => $val) {
                    $site_custom_lists["custom_reason_closed"][$key] = $val;
                }
            }
            // Process a field to delete.
            if ( isset( $_POST['delete_field'] ) && $delete ) {
                $delete_key = sanitize_text_field( wp_unslash( $_POST['delete_field'] ) );
                unset( $site_custom_lists["custom_reason_closed"][ $delete_key ] );
                //TODO: Consider adding a database query to delete all instances of this key from usermeta
            }
            // Process reset request
            else if ( isset( $_POST['reason_closed_reset'] ) ) {
                //for each custom object with the start of seeker_ delete
                unset( $site_custom_lists["custom_reason_closed"] );
                $site_custom_lists["custom_reason_closed"] = dt_get_site_custom_lists( "custom_reason_closed" ); //the standard ones;
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
        $reason_closed = dt_get_option( 'dt_site_custom_lists' );
        $reason_closed = $reason_closed['custom_reason_closed'];
        $default = dt_get_site_custom_lists( "custom_reason_closed" ); //the standard ones

        $first = true;
        if ( ! $reason_closed ) {
            wp_die( 'Failed to get dt_site_custom_lists() from options table.' );
        }

        ?>
        <form method="post" name="reason_closed_form">
            <input type="hidden" name="reason_closed_nonce" id="reason_closed_nonce" value="<?php echo esc_attr( wp_create_nonce( 'reason_closed' ) ) ?>" />
            <button type="submit" class="button-like-link" name="reason_closed_reset_bug_fix" value="&nasb"></button>
            <button type="submit" class="button-like-link" name="reason_closed_reset" value="1"><?php esc_html_e( "reset", 'disciple_tools' ) ?></button>

            <p><?php esc_html_e( "Add or remove reason_closed for new contacts.", 'disciple_tools' ) ?></p>

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
            <button type="button" onclick="jQuery('#add_reason_closed').toggle();" class="button"><?php esc_html_e( "Add", 'disciple_tools' ) ?></button>
            <button type="submit" style="float:right;" class="button"><?php esc_html_e( "Save", 'disciple_tools' ) ?></button>

            <div id="add_reason_closed" style="display:none;">
            <table width="100%">
                <tr>
                    <td><hr><br>
                        <input type="text" name="add_input_field[label]" placeholder="label" />&nbsp;
                    <button type="submit"><?php echo esc_html( __( 'Add', 'disciple_tools' ) ) ?></button>
                </td></tr>
            </table>
            </div>

        </form>
        <?php
    }

    /**
     * Process contact reason paused settings
     */
    public function process_reason_paused_box()
    {
        if ( isset( $_POST['reason_paused_nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['reason_paused_nonce'] ) ), 'reason_paused' ) ) {
            $delete = true;
            $site_custom_lists = dt_get_option( 'dt_site_custom_lists' );
            if ( !$site_custom_lists ) {
                wp_die( 'Failed to get dt_site_custom_lists() from options table.' );
            }
            //make a new seeker object
            if ( !empty( $_POST['add_input_field']['label'] ) ) {
                $delete = false; //for the enter bug
                //make the label
                $label = sanitize_text_field( wp_unslash( $_POST['add_input_field']['label'] ) );
                //set label and name to same thing
                $site_custom_lists["custom_reason_paused"][$label] = $label;
            }
            //edit name
            if ( isset( $_POST["reason_paused"] ) ) {
                foreach ( sanitize_text_field( wp_unslash( $_POST["reason_paused"] ) ) as $key => $val) {
                    $site_custom_lists["custom_reason_paused"][$key] = $val;
                }
            }
            // Process a field to delete.
            if ( isset( $_POST['delete_field'] ) && $delete ) {
                $delete_key = sanitize_text_field( wp_unslash( $_POST['delete_field'] ) );
                unset( $site_custom_lists["custom_reason_paused"][ $delete_key ] );
                //TODO: Consider adding a database query to delete all instances of this key from usermeta
            }
            // Process reset request
            else if ( isset( $_POST['reason_paused_reset'] ) ) {
                //for each custom object with the start of seeker_ delete
                unset( $site_custom_lists["custom_reason_paused"] );
                $site_custom_lists["custom_reason_paused"] = dt_get_site_custom_lists( "custom_reason_paused" ); //the standard ones;
            }
            // Update the site option
            update_option( 'dt_site_custom_lists', $site_custom_lists, true );
            dt_write_log( $_POST );
        }
    }

    /**
     * Prints the reason settings box.
     */
    public function reason_paused_box()
    {
        //$default = Disciple_Tools_Contact_Post_Type::get_custom_fields_settings( "reason_paused" ); //the standard ones
        $reason_paused = dt_get_option( 'dt_site_custom_lists' );
        $reason_paused = $reason_paused['custom_reason_paused'];
        $default = dt_get_site_custom_lists( "custom_reason_paused" ); //the standard ones

        $first = true;
        if ( ! $reason_paused ) {
            wp_die( 'Failed to get dt_site_custom_lists() from options table.' );
        }

        ?>
        <form method="post" name="reason_paused_form">
            <input type="hidden" name="reason_paused_nonce" id="reason_paused_nonce" value="<?php echo esc_attr( wp_create_nonce( 'reason_paused' ) ) ?>" />
            <button type="submit" class="button-like-link" name="reason_paused_reset_bug_fix" value="&nasb"></button>
            <button type="submit" class="button-like-link" name="reason_paused_reset" value="1"><?php esc_html_e( "reset", 'disciple_tools' ) ?></button>

            <p><?php esc_html_e( "Add or remove reason_paused for new contacts.", 'disciple_tools' ) ?></p>

            <input type="hidden" name="reason_paused_nonce" id="reason_paused_nonce" value="<?php echo esc_attr( wp_create_nonce( 'reason_paused' ) ) ?>" />
            <table class="widefat">
                <thead>
                    <tr>
                        <td><?php esc_html_e( "Label", 'disciple_tools' ) ?></td>
                        <td><?php esc_html_e( "Delete", 'disciple_tools' ) ?></td>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $reason_paused as $key => $label ) : ?>
                        <?php if ( $label != '' && $label !== false) : ?>
                            <tr>
                                <td><input name="reason_paused[<?php echo esc_html( $key ) ?>]" type="text" value="<?php echo esc_html( $label ) ?>"/></td>
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
            <button type="button" onclick="jQuery('#add_reason_paused').toggle();" class="button"><?php esc_html_e( "Add", 'disciple_tools' ) ?></button>
            <button type="submit" style="float:right;" class="button"> <?php esc_html_e( "Save", 'disciple_tools' ) ?> </button>

            <div id="add_reason_paused" style="display:none;">
            <table width="100%">
                <tr>
                    <td><hr><br>
                        <input type="text" name="add_input_field[label]" placeholder="label" />&nbsp;
                    <button type="submit"><?php echo esc_html( __( 'Add', 'disciple_tools' ) ) ?></button>
                </td></tr>
            </table>
            </div>

        </form>
        <?php
    }


    /**
     * Process contact reason unassignable settings
     */
    public function process_reason_unassignable_box()
    {
        if ( isset( $_POST['reason_unassignable_nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['reason_unassignable_nonce'] ) ), 'reason_unassignable' ) ) {
            $remove = true;
            $site_custom_lists = dt_get_option( 'dt_site_custom_lists' );
            if ( !$site_custom_lists ) {
                wp_die( 'Failed to get dt_site_custom_lists() from options table.' );
            }
            //make a new seeker object
            if ( !empty( $_POST['add_input_field']['label'] ) ) {
                $remove = false; //for the enter bug
                //make the label
                $label = sanitize_text_field( wp_unslash( $_POST['add_input_field']['label'] ) );
                //set label and name to same thing
                $site_custom_lists["custom_reason_unassignable"][$label] = $label;
            }
            //edit name
            if (isset( $_POST["reason_unassignable"] )) {
                $reason_unassignable = $_POST["reason_unassignable"]; // phpcs:ignore
                foreach ( $reason_unassignable as $key => $val) {
                    $status = sanitize_text_field( wp_unslash( $key ) );
                    $value = sanitize_text_field( wp_unslash( $val ) );
                    $site_custom_lists["custom_reason_unassignable"][$status] = $value;
                }
            }
            // Process a field to delete.
            if ( isset( $_POST['delete_field'] ) && $remove ) {
                $delete_key = sanitize_text_field( wp_unslash( $_POST['delete_field'] ) );
                unset( $site_custom_lists["custom_reason_unassignable"][ $delete_key ] );
                //TODO: Consider adding a database query to delete all instances of this key from usermeta
            }
            // Process reset request
            else if ( isset( $_POST['reason_unassignable_reset'] ) ) {
                //for each custom object with the start of seeker_ delete
                unset( $site_custom_lists["custom_reason_unassignable"] );
                $site_custom_lists["custom_reason_unassignable"] = dt_get_site_custom_lists( "custom_reason_unassignable" ); //the standard ones;
            }
            // Update the site option
            update_option( 'dt_site_custom_lists', $site_custom_lists, true );
            dt_write_log( $_POST );
        }
    }

    /**
     * Prints the reason settings box.
     */
    public function reason_unassignable_box()
    {
        //$default = Disciple_Tools_Contact_Post_Type::get_custom_fields_settings( "reason_unassignable" ); //the standard ones
        $reason_unassignable = dt_get_option( 'dt_site_custom_lists' );
        $reason_unassignable = $reason_unassignable['custom_reason_unassignable'];
        $default = dt_get_site_custom_lists( "custom_reason_unassignable" ); //the standard ones

        $first = true;
        if ( ! $reason_unassignable ) {
            wp_die( 'Failed to get dt_site_custom_lists() from options table.' );
        }

        ?>
        <form method="post" name="reason_unassignable_form">
            <input type="hidden" name="reason_unassignable_nonce" id="reason_unassignable_nonce" value="<?php echo esc_attr( wp_create_nonce( 'reason_unassignable' ) ) ?>" />
            <button type="submit" class="button-like-link" name="enter_bug_fix" value="&nasb"></button>
            <button type="submit" class="button-like-link" name="reason_unassignable_reset" value="1"><?php esc_html_e( "reset", 'disciple_tools' ) ?></button>

            <p><?php esc_html_e( "Add or remove reason_unassignable for new contacts.", 'disciple_tools' ) ?></p>

            <input type="hidden" name="reason_unassignable_nonce" id="reason_unassignable_nonce" value="<?php echo esc_attr( wp_create_nonce( 'reason_unassignable' ) ) ?>" />
            <table class="widefat">
                <thead>
                    <tr>
                        <td><?php esc_html_e( "Label", 'disciple_tools' ) ?></td>
                        <td><?php esc_html_e( "Delete", 'disciple_tools' ) ?></td>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $reason_unassignable as $key => $label ) : ?>
                        <?php if ( $label != '' && $label !== false) : ?>
                            <tr>
                                <td><input name="reason_unassignable[<?php echo esc_html( $key ) ?>]" type="text" value="<?php echo esc_html( $label ) ?>"/></td>
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
            <button type="button" onclick="jQuery('#add_reason_unassignable').toggle();" class="button"><?php esc_html_e( "Add", 'disciple_tools' ) ?></button>
            <button type="submit" style="float:right;" class="button"> <?php esc_html_e( "Save", 'disciple_tools' ) ?> </button>

            <div id="add_reason_unassignable" style="display:none;">
            <table width="100%">
                <tr>
                    <td><hr><br>
                        <input type="text" name="add_input_field[label]" placeholder="label" />&nbsp;
                    <button type="submit"><?php echo esc_html( __( 'Add', 'disciple_tools' ) ) ?></button>
                </td></tr>
            </table>
            </div>

        </form>
        <?php
    }

    /**
     * Prints the health settings box.
     */
    public function health_box()
    {
        echo '<form method="post" name="health_form">';
        //echo '<button type="submit" class="button-like-link" name="health_reset" value="1">' . esc_html( __( "reset", 'disciple_tools' ) ) . '</button>';
        echo '<p>'. esc_html( __( "Add or remove custom health for new contacts.", 'disciple_tools' ) ) .'</p>';
        echo '<input type="hidden" name="health_nonce" id="health_nonce" value="' . esc_attr( wp_create_nonce( 'health' ) ) . '" />';
        echo '<table class="widefat">';
        echo '<thead><tr><td>'. esc_html( __( "Label", 'disciple_tools' ) ) . '</td><td>'. esc_html( __( "Delete", 'disciple_tools' ) ) . '</td></tr></thead><tbody>';

        // get the list of custom lists
        $site_custom_lists = dt_get_option( 'dt_site_custom_lists' );
        //empty check
        if ( ! $site_custom_lists ) {
            wp_die( 'Failed to get custom list from options table.' );
        }
        $site_custom_lists = $site_custom_lists["custom_church"];
        //for each health put it on the list
        foreach ( $site_custom_lists as $health => $value) {
            if ( strpos( $health, "church_custom_" ) === 0 ) {
                //get the first value
                reset( $value["default"] );
                $first_key = key( $value["default"] );
                //parse the name into pretty format
                $name = $value["name"];
                //echo $first_key;
                echo '<tr>
                            <td><input type="text" name=' . esc_html( $health ) . ' value = "' . esc_html( $name ) . '"></input></td>
                            <td><button type="submit" name="delete_field" value="' . esc_html( wp_unslash( $health ) ) . '" class="button small" >' . esc_html( __( "Delete", 'disciple_tools' ) ) . '</button> </td>
                        </tr>';
            }
        }

        // end list block
        echo '</table>';
        echo '<br><button type="button" onclick="jQuery(\'#add_health\').toggle();" class="button">' . esc_html( __( "Add", 'disciple_tools' ) ) . '</button>
                        <button type="submit" style="float:right;" class="button">' . esc_html( __( "Save", 'disciple_tools' ) ) . '</button>';
        echo '<div id="add_health" style="display:none;">';
        echo '<table width="100%"><tr><td><hr><br>
                    <input type="text" name="add_input_field[label]" placeholder="label" />&nbsp;';
        echo '<button type="submit">' . esc_html( __( 'Add', 'disciple_tools' ) ) . '</button>
                    </td></tr></table></div>';
        echo '</tbody></form>';
    }

    /**
     * Process health health settings
     */
    public function process_health_box()
    {
        if ( isset( $_POST['health_nonce'] ) ) {
            $delete = true;  //for the bug where you press enter and it deltes a key
            if ( !wp_verify_nonce( sanitize_key( $_POST['health_nonce'] ), 'health' ) ) {
                return;
            }

            //get the custom list of lists
            $site_custom_lists = dt_get_option( 'dt_site_custom_lists' );
            // Process current fields submitted
            if ( ! $site_custom_lists ) {
                wp_die( 'Failed to get dt_site_custom_lists() from options table.' );
            }
            //make a new health object
            if ( !empty( $_POST['add_input_field']['label'] ) ) {
                $delete = false; //for the enter bug
                //make the label
                $label = sanitize_text_field( wp_unslash( $_POST['add_input_field']['label'] ) );
                //for the key add the _ for spaces
                $key = "church_custom_".str_replace( " ", "_", $label );
                //set all the values note for right now the default is ALWAYS NO
                $site_custom_lists["custom_church"][$key] = [
                    'name'        => $label,
                    'description' => '',
                    'type'        => 'key_select',
                    'default'     => [
                    '0' => __( 'No', 'disciple_tools' ),
                    '1' => __( 'Yes', 'disciple_tools' )
                    ],
                    'section'     => 'church_hidden',
                ];
            }
            //edit name
            // for each custom object with the start of health_ make sure name is up to date
            foreach ( sanitize_text_field( wp_unslash( $_POST ) ) as $health => $value ) {
                if ( strpos( $health, "church_custom_" ) === 0 && $health != 'health_nonce' ) {
                    //delete key
                    $key = $value;
                    if ( $site_custom_lists["custom_church"][$health]['name'] != $key ) {
                        $delete = false; //for the enter bug
                        //set new label value
                        $label = sanitize_text_field( wp_unslash( $value ) );
                        //set all the values note for right now the default is ALWAYS NO
                        $site_custom_lists["custom_church"][$health]['name'] = $label;
                    }
                }
            }
            // Process a field to delete.
            if ( isset( $_POST['delete_field'] ) && $delete ) {
                $delete_key = sanitize_text_field( wp_unslash( $_POST['delete_field'] ) );
                unset( $site_custom_lists["custom_church"][ $delete_key ] );
                //TODO: Consider adding a database query to delete all instances of this key from usermeta

            }
            // Process reset request
            if ( isset( $_POST['health_reset'] ) ) {
                unset( $site_custom_lists["custom_church"] );
                $site_custom_lists["custom_church"] = [];
            }
            // Update the site option
            update_option( 'dt_site_custom_lists', $site_custom_lists, true );
        }
    }

    /**
     * Process custom path settings
     */

    public function sanatize_all( $s ){
        return esc_html( sanitize_text_field( str_replace( "\\", "", str_replace( " ", "_", preg_replace( '/>|<|\)|\(|]|\[|"| |\'|`/', "", $s ) ) ) ) );
    }

    public function custom_dropdown_field_dub_check( $lookup, $k, $t = 'k' ){
        if ( empty( $lookup ) ) {
            return "";
        }
        $key = $k;
        $num = 0;
        if ( $t == 'k' ) {
            while ( true ) {
                if ( in_array( $key, array_keys( $lookup ) ) === false ){
                    if ( $num == 0 ){
                        return "";
                    }
                    return "-" . (string) $num;
                }
                $num++;
                $key = $k . "-" . (string) $num;
            }
        }
        else if ( $t == 'v' ) {
            $num = 0;
            while ( true ) {
                if ( in_array( $key, $lookup ) === false ) {
                    if ( $num == 0 ){
                        return "";
                    }
                    return "-" . (string) $num;
                }
                $num++;
                $key = $k . "-" . (string) $num;
            }
        }
    }

    public function process_custom_dropdown_field_box()
    {
        if ( isset( $_POST['custom_dropdown_field_nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['custom_dropdown_field_nonce'] ) ), 'custom_dropdown_field' ) ) {
            $delete = true;
            $site_custom_lists = dt_get_option( 'dt_site_custom_lists' );
            //get the custom list of lists
            //$site_custom_lists = dt_get_option( 'dt_site_custom_lists' );
            //checks if default optiions in custom
            $seek = dt_get_site_custom_lists( "custom_dropdown_contact_options" ); //the standard ones
            if ( !$site_custom_lists ) {
                wp_die( 'Failed to get dt_site_custom_lists() from options table.' );
            }
            //make a new path
            if ( !empty( $_POST['add_input_field_path'] ) ) {
                // @codingStandardsIgnoreLine
                $key =  $this->sanatize_all( $_POST['add_input_field_path'] );
                if ( !empty( $key ) ){
                    // @codingStandardsIgnoreLine
                    $key = $key . $this->custom_dropdown_field_dub_check( $site_custom_lists["custom_dropdown_contact_options"], $key, 'k' );
                    // @codingStandardsIgnoreLine
                    $site_custom_lists["custom_dropdown_contact_options"][$key]["label"] = sanitize_text_field( wp_unslash( $_POST['add_input_field_path'] . $this->custom_dropdown_field_dub_check( $site_custom_lists["custom_dropdown_contact_options"], $this->sanatize_all( $_POST['add_input_field_path'] ), 'k' ) ) );
                }
            }
            //make a new field for a path
            else if ( !empty( $_POST['add_input_field_option'] ) ) {
                // @codingStandardsIgnoreLine
                foreach ( $_POST['add_input_field_option'] as $k => $v ){
                    if ( $v["label"] != "" ) {
                        $k = $this->sanatize_all( $k );
                        $v = sanitize_text_field( wp_unslash( $v["label"] ) );
                        $v = $v . $this->custom_dropdown_field_dub_check( $site_custom_lists["custom_dropdown_contact_options"][$k], $v, 'v' );
                        if ( !empty( $k ) ) {
                            $site_custom_lists["custom_dropdown_contact_options"][$k][ str_replace( " ", "_", $v ) ] = $v;
                        }
                    }
                }
            }
            //edit values
            if ( isset( $_POST["custom_dropdown_field"] ) ) {
                // @codingStandardsIgnoreLine
                foreach ( $_POST["custom_dropdown_field"] as $key => $val) {
                    $key = $this->sanatize_all( $key );
                    if ( !empty( $key ) ) {
                        // @codingStandardsIgnoreLine
                        foreach ( $val as $k => $v ){
                            $v = wp_unslash( $v );
                            $k = $this->sanatize_all( $k );
                            $ret = $this->custom_dropdown_field_dub_check( $site_custom_lists["custom_dropdown_contact_options"][$key], $v, 'v' );
                            if ( $ret == "" ) {
                                $site_custom_lists["custom_dropdown_contact_options"][$key][$k] = $v;
                            }
                        }
                    }
                }
            }
            // Process move up request
            if ( isset( $_POST['custom_dropdown_field_move_up'] ) ) {
                $new_path = [];
                $previous_key = null;
                // @codingStandardsIgnoreLine
                $key = sanitize_text_field( str_replace(" ","_", wp_unslash( $_POST['custom_dropdown_field_move_up'][key( $_POST['custom_dropdown_field_move_up'] )] ) ) );
                // @codingStandardsIgnoreLine
                $path = sanitize_text_field( wp_unslash( key( $_POST['custom_dropdown_field_move_up'] ) ) );
                $label = $site_custom_lists["custom_dropdown_contact_options"][$path]["label"];
                unset( $site_custom_lists["custom_dropdown_contact_options"][$path]["label"] );
                reset( $site_custom_lists["custom_dropdown_contact_options"][$path] );
                $first_key = key( $site_custom_lists["custom_dropdown_contact_options"][$path] );
                $item = array( $key => $site_custom_lists["custom_dropdown_contact_options"][$path][$key] );
                foreach ( $site_custom_lists["custom_dropdown_contact_options"][$path] as $h_key => $h_val ) {
                    if ( $h_key == $key && $previous_key != null ) {
                            $previous = $new_path[$previous_key];
                            unset( $new_path[$previous_key] );
                            unset( $new_path[$h_key] );
                            $new_path += $item;
                            $new_path += array( $previous_key => $previous );
                    }
                    else {
                            $previous_key = $h_key;
                            $new_path += array( $h_key => $h_val );
                    }
                }
                $new_path += array( "label" => $label );
                $site_custom_lists["custom_dropdown_contact_options"][$path] = $new_path;
            }
            // Process move down request
            else if ( isset( $_POST['custom_dropdown_field_move_down'] ) ) {
                //reverse move up and reverser again this makes it go down
                // @codingStandardsIgnoreLine
                $key = sanitize_text_field( str_replace(" ","_", wp_unslash( $_POST['custom_dropdown_field_move_down'][key( $_POST['custom_dropdown_field_move_down'] )] ) ) );
                // @codingStandardsIgnoreLine
                $path = sanitize_text_field( wp_unslash( key( $_POST['custom_dropdown_field_move_down'] ) ) );
                $site_custom_lists["custom_dropdown_contact_options"][$path] = array_reverse( $site_custom_lists["custom_dropdown_contact_options"][$path] );
                $new_path = [];
                $previous_key = null;
                $label = $site_custom_lists["custom_dropdown_contact_options"][$path]["label"];
                unset( $site_custom_lists["custom_dropdown_contact_options"][$path]["label"] );
                reset( $site_custom_lists["custom_dropdown_contact_options"][$path] );
                $first_key = key( $site_custom_lists["custom_dropdown_contact_options"][$path] );
                $item = array( $key => $site_custom_lists["custom_dropdown_contact_options"][$path][$key] );
                foreach ( $site_custom_lists["custom_dropdown_contact_options"][$path] as $h_key => $h_val ) {
                    if ( $h_key == $key && $previous_key != null ) {
                        $previous = $new_path[$previous_key];
                        unset( $new_path[$previous_key] );
                        unset( $new_path[$h_key] );
                        $new_path += $item;
                        $new_path += array( $previous_key => $previous );
                    }
                    else {
                        $previous_key = $h_key;
                        $new_path += array( $h_key => $h_val );
                    }
                }
                $new_path += array( "label" => $label );
                $site_custom_lists["custom_dropdown_contact_options"][$path] = array_reverse( $new_path );
            }
            // Process a path to delete.
            //@codingStandardsIgnoreLine
            else if ( isset( $_POST['delete_path'] ) && $delete ) {
                // @codingStandardsIgnoreLine
                $delete_key = $this->sanatize_all( $_POST['delete_path'] );
                unset( $site_custom_lists["custom_dropdown_contact_options"][ $delete_key ] );
            }
            // Process a field to a path.
            else if ( isset( $_POST['delete_field'] ) && $delete ) {
                // @codingStandardsIgnoreLine
                foreach ( $_POST['delete_field'] as $k => $v ){
                    if ( $v["field"] != "" ){
                        $delete_key = $this->sanatize_all( $k );
                        $delete_field  = str_replace( " ", "_", sanitize_text_field( wp_unslash( $v["field"] ) ) );
                        unset( $site_custom_lists["custom_dropdown_contact_options"][ $delete_key ][ $delete_field ] );
                    }
                }
            }
            // Update the site option
            update_option( 'dt_site_custom_lists', $site_custom_lists, true );
            dt_write_log( $_POST );
        }
    }

    /**
     * Prints the custom_dropdown_field settings box.
     */
    public function custom_dropdown_field_box()
    {
        $default = [];
        $custom_dropdown_field = dt_get_option( 'dt_site_custom_lists' );
        $custom_dropdown_field = $custom_dropdown_field["custom_dropdown_contact_options"];
        $first = true;
        if ( !isset( $custom_dropdown_field ) ) {
            wp_die( 'Failed to get dt_site_custom_lists() from options table.' );
        }

        ?>
        <form method="post" name="custom_dropdown_field_form">
            <input type="hidden" name="custom_dropdown_field_nonce" id="custom_dropdown_field_nonce" value="<?php echo esc_attr( wp_create_nonce( 'custom_dropdown_field' ) ) ?>" />
            <button type="submit" class="button-like-link" name="custom_dropdown_field_reset_bug_fix" value="&nasb"></button>

            <p><?php esc_html_e( "Add or remove custom_dropdown_field for new contacts.", 'disciple_tools' ) ?></p>

            <input type="hidden" name="custom_dropdown_field_nonce" id="custom_dropdown_field_nonce" value="<?php echo esc_attr( wp_create_nonce( 'custom_dropdown_field' ) ) ?>" />
                <?php foreach ( $custom_dropdown_field as $key => $value ) : ?>
                    <?php $first = true; ?>
                    <table class="widefat">
                    <thead>
                        <tr>
                            <td><?php esc_html_e( "Move", 'disciple_tools' ) ?></td>
                            <td><?php esc_html_e( "Label", 'disciple_tools' ) ?></td>
                            <td><?php esc_html_e( "Delete", 'disciple_tools' ) ?></td>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ( $value as $i => $label ) : ?>
                        <?php if ( $first ) { ?>
                            <input name="custom_dropdown_field[<?php echo esc_html( $this->sanatize_all( $key ) ); ?>][label]" type="text" value="<?php echo esc_html( $value["label"] ) ?>"/>
                            <button type="submit" name="delete_path" value="<?php echo esc_html( $this->sanatize_all( $key ) ) ?>" class="button small" ><?php esc_html_e( "delete", 'disciple_tools' ) ?></button>
                    <?php } if ( $i !== "label" ) { ?>
                        <tr>
                            <td>
                                <button type="submit" name="custom_dropdown_field_move_up[<?php echo esc_html( $this->sanatize_all( $key ) ) ?>]" value="<?php echo esc_html( sanitize_text_field( $label ) ) ?>" class="button small" >↑</button>
                                <button type="submit" name="custom_dropdown_field_move_down[<?php echo esc_html( $this->sanatize_all( $key ) ) ?>]" value="<?php echo esc_html( sanitize_text_field( $label ) )?>" class="button small" >↓</button>
                            </td>
                            <td><input name="custom_dropdown_field[<?php echo esc_html( $this->sanatize_all( $key ) ); ?>][<?php echo esc_html( sanitize_text_field( $label ) ) ?>]" type="text" value="<?php echo esc_html( $label ) ?>"/></td>
                            <?php if ( !in_array( $key, array_keys( $default ), true ) ) { ?>
                                <td><button type="submit" name="delete_field[<?php echo esc_html( $this->sanatize_all( $key ) ); ?>][field]" value="<?php echo esc_html( sanitize_text_field( $label ) ) ?>" class="button small" ><?php esc_html_e( "delete", 'disciple_tools' ) ?></button> </td>
                            <?php } ?>
                        </tr>
                            <?php } ?>
                        <?php $first = false; ?>
                    <?php endforeach; ?>
                            </tbody>
                    </table>
                    <button type="button" onclick="jQuery('#add_custom_field_<?php echo esc_html( $this->sanatize_all( $key ) ); ?>').toggle();" class="button"><?php esc_html_e( "Add Option", 'disciple_tools' ) ?></button>
                    <button type="submit" style="float:right;" class="button"><?php esc_html_e( "Save", 'disciple_tools' ) ?></button>
                    <div id="add_custom_field_<?php echo esc_html( $this->sanatize_all( $key ) ); ?>" style="display:none;">
                    <table width="100%">
                        <tr>
                            <td><hr><br>
                                <input type="text" name="add_input_field_option[<?php echo esc_html( $this->sanatize_all( $key ) ); ?>][label]" placeholder="label" />&nbsp;
                            <button type="submit"><?php echo esc_html( __( 'Add', 'disciple_tools' ) ) ?></button>
                        </td></tr>
                    </table>
                    </div>
                    <br>
                    <br>
                <?php endforeach; ?>
            <br>
            <br>
            <button type="button" onclick="jQuery('#add_custom_dropdown_field').toggle();" class="button"><?php esc_html_e( "Add Dropdown Field", 'disciple_tools' ) ?></button>
            <button type="submit" style="float:right;" class="button"><?php esc_html_e( "Save", 'disciple_tools' ) ?></button>
            <div id="add_custom_dropdown_field" style="display:none;">
            <table width="100%">
                <tr>
                    <td><hr><br>
                        <input type="text" name="add_input_field_path" placeholder="label" />&nbsp;
                    <button type="submit"><?php echo esc_html( __( 'Add', 'disciple_tools' ) ) ?></button>
                </td></tr>
            </table>
            </div>

        </form>
        <?php
    }
}
Disciple_Tools_Tab_Custom_Lists::instance();
