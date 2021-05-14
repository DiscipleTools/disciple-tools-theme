<?php
/**
 * Handles custom functionality on the edit user screen, such as multiple user roles.
 *
 * @package    Members
 * @subpackage Admin
 * @author     Justin Tadlock <justin@justintadlock.com>
 * @copyright  Copyright (c) 2009 - 2016, Justin Tadlock
 * @link       http://themehybrid.com/plugins/members
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

/**
 * Edit user screen class.
 *
 * @since  0.1.0
 * @access public
 */
final class Disciple_Tools_Admin_User_Edit {

    /**
     * Holds the instances of this class.
     *
     * @since  0.1.0
     * @access private
     * @var    object
     */
    private static $instance;

    /**
     * Sets up needed actions/filters for the admin to initialize.
     *
     * @since  0.1.0
     * @access public
     * @return void
     */
    public function __construct() {

        // Only run our customization on the 'user-edit.php' page in the admin.
        add_action( 'load-user-edit.php', [ $this, 'load_user_edit' ] );
        add_action( 'load-profile.php', [ $this, 'load_user_edit' ] );
    }

    /**
     * Adds actions/filters on load.
     *
     * @since  0.1.0
     * @access public
     * @return void
     */
    public function load_user_edit() {

        add_action( 'admin_head', [ $this, 'print_styles' ] );

        add_action( 'show_user_profile', [ $this, 'profile_fields' ] );
        add_action( 'edit_user_profile', [ $this, 'profile_fields' ] );

        // Must use `profile_update` to change role. Otherwise, WP will wipe it out.
        add_action( 'profile_update', [ $this, 'role_update' ], 0 );
    }

    /**
     * Adds custom profile fields.
     *
     * @since  0.1.0
     * @access public
     * @param  object  $user
     * @return void
     */
    public function profile_fields( $user ) {
        global $wp_roles;

        if ( ! current_user_can( 'promote_users' ) || ! current_user_can( 'edit_user', $user->ID ) ) {
            return;
        }

        $user_roles = (array) $user->roles;

        $editable_roles = dt_multi_role_get_editable_role_names();

        $can_not_promote_to_roles = [];
        if ( !is_super_admin() && !dt_current_user_has_role( 'administrator' ) ){
            $can_not_promote_to_roles = array_merge( $can_not_promote_to_roles, [ "administrator" ] );
        }
        if ( !current_user_can( 'manage_dt' ) ){
            $can_not_promote_to_roles = array_merge( $can_not_promote_to_roles, dt_multi_role_get_cap_roles( 'manage_dt' ) );
        }


//        asort( $editable_roles );

        wp_nonce_field( 'new_user_roles', 'dt_multi_role_new_user_roles_nonce' ); ?>

        <h3><?php esc_html_e( 'Roles', 'members' ); ?></h3>
        <p>For a description of each of the roles, please see the <a href="https://disciple.tools/user-docs/getting-started-info/roles/" target="_blank">Roles Documentation</a>  </p>

        <table class="form-table">

            <tr>
                <th><?php esc_html_e( 'User Roles', 'members' ); ?></th>

                <td>
                    <ul>
                    <?php
                    $expected_roles = apply_filters( 'dt_set_roles_and_permissions', [] );
                    foreach ( $editable_roles as $role => $name ) : ?>
                        <li>
                            <label>
                                <input type="checkbox" name="dt_multi_role_user_roles[]"
                                       value="<?php echo esc_attr( $role ); ?>"
                                       <?php checked( in_array( $role, $user_roles ) ); ?>
                                       <?php echo esc_html( in_array( $role, $can_not_promote_to_roles ) ? 'disabled' : '' ) ?>/>
                                <strong>
                                <?php
                                if ( isset( $expected_roles[$role]["label"] ) && !empty( $expected_roles[$role]["label"] ) ){
                                    echo esc_html( $expected_roles[$role]["label"] );
                                } else {
                                    echo esc_html( $name );
                                }
                                ?>
                                </strong>
                                <?php
                                if ( isset( $expected_roles[$role]["description"] ) ){
                                    echo ' - ' . esc_html( $expected_roles[$role]["description"] );
                                }
                                ?>
                            </label>
                        </li>
                    <?php endforeach; ?>
                    </ul>
                </td>
            </tr>

        </table>
    <?php }

    /**
     * Callback function for handling user role changes.  Note that we needed to execute this function
     * on a different hook, `profile_update`.  Using the normal hooks on the edit user screen won't work
     * because WP will wipe out the role.
     *
     * @since  0.1.0
     * @access public
     * @param  int    $user_id
     * @return void
     */
    public function role_update( $user_id ) {

        // If the current user can't promote users or edit this particular user, bail.
        if ( ! current_user_can( 'promote_users' ) || ! current_user_can( 'edit_user', $user_id ) ) {
            return;
        }

        // Is this a role change?
        if ( ! isset( $_POST['dt_multi_role_new_user_roles_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['dt_multi_role_new_user_roles_nonce'] ) ), 'new_user_roles' ) ) {
            return;
        }

        // Create a new user object.
        $user = new WP_User( $user_id );

        $can_not_promote_to_roles = [];
        if ( !is_super_admin() && !dt_current_user_has_role( 'administrator' ) ){
            $can_not_promote_to_roles = array_merge( $can_not_promote_to_roles, [ "administrator" ] );
        }
        if ( !current_user_can( 'manage_dt' ) ){
            $can_not_promote_to_roles = array_merge( $can_not_promote_to_roles, dt_multi_role_get_cap_roles( 'manage_dt' ) );
        }
        // If we have an array of roles.
        if ( ! empty( $_POST['dt_multi_role_user_roles'] ) ) {

            // Get the current user roles.
            $old_roles = (array) $user->roles;

            // Sanitize the posted roles.
            $new_roles = array_map( 'dt_multi_role_sanitize_role', array_map( 'sanitize_text_field', wp_unslash( $_POST['dt_multi_role_user_roles'] ) ) );

            // Loop through the posted roles.
            foreach ( $new_roles as $new_role ) {

                // If the user doesn't already have the role, add it.
                if ( dt_multi_role_is_role_editable( $new_role ) && ! in_array( $new_role, (array) $user->roles ) ) {
                    if ( !in_array( $new_role, $can_not_promote_to_roles ) ){
                        $user->add_role( $new_role );
                    }
                }
            }

            // Loop through the current user roles.
            foreach ( $old_roles as $old_role ) {

                // If the role is editable and not in the new roles array, remove it.
                if ( dt_multi_role_is_role_editable( $old_role ) && ! in_array( $old_role, $new_roles ) ) {
                    if ( !in_array( $old_role, $can_not_promote_to_roles ) ){
                        $user->remove_role( $old_role );
                    }
                }
            }

            // If the posted roles are empty.
        } else {

            // Loop through the current user roles.
            foreach ( (array) $user->roles as $old_role ) {

                // Remove the role if it is editable.
                if ( dt_multi_role_is_role_editable( $old_role ) ) {
                    if ( !in_array( $old_role, $can_not_promote_to_roles ) ){
                        $user->remove_role( $old_role );
                    }
                }
            }
        }
    }

    /**
     * Enqueue the plugin admin CSS.
     *
     * @since  0.1.0
     * @access public
     * @return void
     */
    public function print_styles() { ?>

        <style type="text/css">.user-role-wrap{ display: none !important; }</style>

    <?php }

    /**
     * Returns the instance.
     *
     * @since  0.1.0
     * @access public
     * @return object
     */
    public static function get_instance() {

        if ( ! self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}

Disciple_Tools_Admin_User_Edit::get_instance();
