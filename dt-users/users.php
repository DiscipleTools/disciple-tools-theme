<?php
/**
 * Contains create, update and delete functions for users, wrapping access to the database
 *
 * @package  Disciple_Tools
 * @category Plugin
 * @author   Chasm.Solutions & Kingdom.Training
 * @since    0.1.0
 */
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly.

/**
 * Class Disciple_Tools_Users
 * Functions for creating, finding, updating or deleting contacts
 */
class Disciple_Tools_Users
{
    /**
     * Disciple_Tools_Users constructor.
     */
    public function __construct()
    {
        add_action( 'user_register', [ &$this, 'user_register_hook' ] );
        add_action( 'profile_update', [ &$this, 'profile_update_hook' ], 99 );
    }

    /**
     * Get assignable users
     *
     * @param  $search_string
     *
     * @return array|\WP_Error
     */
    public static function get_assignable_users_compact( string $search_string = null )
    {
        //        @todo better permissions?
        //        @todo return only the users the user has the permission to assign to
        if ( !current_user_can( "access_contacts" ) ) {
            return new WP_Error( __FUNCTION__, __( "No permissions to assign" ), [ 'status' => 403 ] );
        }

        $user_query = new WP_User_Query( [
            'search'         => '*' . esc_attr( $search_string ) . '*',
            'search_columns' => [
                'user_login',
                'user_nicename',
                'user_email',
                'user_url',
            ],
            'meta_query'     => [
                'relation' => 'OR',
                [
                    'key'     => 'first_name',
                    'value'   => $search_string,
                    'compare' => 'LIKE',
                ],
                [
                    'key'     => 'last_name',
                    'value'   => $search_string,
                    'compare' => 'LIKE',
                ],
            ],
        ] );
        $users = $user_query->get_results();
        $list = [];

        foreach ( $users as $user ) {
            if ( user_can( $user, "access_contacts" ) ) {
                $list[] = [
                    "name" => $user->display_name,
                    "ID"   => $user->ID,
                ];
            }
        }

        return $list;
    }

    /**
     * Switch user preference for notifications and availability meta fields.
     *
     * @param int    $user_id
     * @param string $preference_key
     *
     * @return array
     */
    public static function switch_preference( int $user_id, string $preference_key )
    {

        $value = get_user_meta( $user_id, $preference_key, true );

        if ( empty( $value ) ) {
            $status = update_metadata( 'user', $user_id, $preference_key, true );
            if ( $status ) {
                return [
                    'status'   => true,
                    'response' => $status,
                ];
            } else {
                return [
                    'status'  => false,
                    'message' => 'Unable to update_user_option ' . $preference_key . ' to true.',
                ];
            }
        } else {
            $status = update_metadata( 'user', $user_id, $preference_key, false );
            if ( $status ) {
                return [
                    'status'   => true,
                    'response' => $status,
                ];
            } else {
                return [
                    'status'  => false,
                    'message' => 'Unable to update_user_option ' . $preference_key . ' to false.',
                ];
            }
        }
    }

    /**
     * Processes updates posted for current user details.
     *
     * @return bool|\WP_Error
     */
    public static function update_user_contact_info()
    {
        $current_user = wp_get_current_user();

        // validate nonce
        if ( isset( $_POST['user_update_nonce'] ) && !wp_verify_nonce( sanitize_key( $_POST['user_update_nonce'] ), 'user_' . $current_user->ID . '_update' ) ) {
            return new WP_Error( 'fail_nonce_verification', 'The form requires a valid nonce, in order to process.' );
        }

        $args = [];
        $args['ID'] = $current_user->ID;

        // build user name variables
        if ( isset( $_POST['first_name'] ) ) {
            $args['first_name'] = sanitize_text_field( wp_unslash( $_POST['first_name'] ) );
        }
        if ( isset( $_POST['last_name'] ) ) {
            $args['last_name'] = sanitize_text_field( wp_unslash( $_POST['last_name'] ) );
        }
        if ( isset( $_POST['display_name'] ) && !empty( $_POST['display_name'] ) ) {
            $args['display_name'] = sanitize_text_field( wp_unslash( $_POST['display_name'] ) );
        }
        if ( isset( $_POST['user_email'] ) && !empty( $_POST['user_email'] ) ) {
            $args['user_email'] = sanitize_email( wp_unslash( $_POST['user_email'] ) );
        }
        if ( isset( $_POST['description'] ) ) {
            $args['description'] = sanitize_text_field( wp_unslash( $_POST['description'] ) );
        }
        if ( isset( $_POST['nickname'] ) ) {
            $args['nickname'] = sanitize_text_field( wp_unslash( $_POST['nickname'] ) );
        }

        // _user table defaults
        $result = wp_update_user( $args );

        if ( is_wp_error( $result ) ) {
            return new WP_Error( 'fail_update_user_data', 'Error while updating user data in user table.' );
        }

        // Update custom site fields
        $fields = array_keys( dt_get_site_default_user_fields() );

        foreach ( $fields as $f ) {

            if ( isset( $_POST[ $f ] ) ) {
                ${$f} = trim( sanitize_text_field( wp_unslash( $_POST[ $f ] ) ) );

                if ( get_user_meta( $current_user->ID, $f, true ) == '' ) {
                    update_user_meta( $current_user->ID, $f, ${$f} );
                } elseif ( ${$f} == '' ) {
                    delete_user_meta( $current_user->ID, $f, get_user_meta( $current_user->ID, $f, true ) );
                } elseif ( ${$f} != get_user_meta( $current_user->ID, $f, true ) ) {
                    update_user_meta( $current_user->ID, $f, ${$f} );
                }
            }
        }

        return true;
    }

    /**
     * Create a Contact for each user that registers
     *
     * @param $user_id
     */
    public static function create_contact_for_user( $user_id )
    {
        $user = get_user_by( 'id', $user_id );
        if ( $user->has_cap( 'access_contacts' ) ) {
            $args = [
                'post_type'  => 'contacts',
                'relation'   => 'AND',
                'meta_query' => [
                    [ 'key' => "corresponds_to_user", "value" => $user_id ],
                    [ 'key' => "is_a_user", "value" => "yes" ],
                ],
            ];
            $contacts = new WP_Query( $args );
            if ( empty( $contacts->posts ) ) {
                Disciple_Tools_Contacts::create_contact( [
                    "title"               => $user->display_name,
                    "assigned_to"         => "user-" . $user_id,
                    "overall_status"      => "assigned",
                    "is_a_user"           => "yes",
                    "corresponds_to_user" => $user_id,
                ], false );
            }
        }
    }

    /**
     * User register hook
     *
     * @param $user_id
     */
    public static function user_register_hook( $user_id )
    {
        self::create_contact_for_user( $user_id );
    }

    /**
     * Profile uppdate hook
     *
     * @param $user_id
     */
    public static function profile_update_hook( $user_id )
    {
        self::create_contact_for_user( $user_id );
    }

    /**
     * Get the base user for the system
     * You can call this function using dt_get_base_user( $id_only = false )
     *
     * @since 0.1.0
     *
     * @param bool $id_only     (optional) Default is false and function returns entire WP_User object.
     *
     * @return array|false|\WP_Error|\WP_User
     */
    public static function get_base_user( $id_only = false ) {

        // get base user id
        $base_user_id = dt_get_option( 'base_user' );
        if ( ! $base_user_id ) {
            return new WP_Error( 'failed_to_get_base_user', 'Failed to get base user. dt_get_option( base_user ) failed.' );
        }

        // get base user object and test if user exists
        $base_user = get_user_by( 'ID', $base_user_id );
        if ( empty( $base_user ) ) { // if base_user has been deleted.
            update_option( 'dt_base_user', false ); // clear current value
            $base_user_id = dt_get_option( 'base_user' ); // call the reset process to re-assign new base user.
            $base_user = get_user_by( 'ID', $base_user_id );
        }

        // test if id and object are populated
        if ( empty( $base_user ) || empty( $base_user_id ) ) {
            return new WP_Error( 'failed_to_get_base_user', 'Failed to get base user object or id using id: '. $base_user_id );
        }

        if ( $id_only ) {
            return $base_user_id;
        }

        return $base_user;
    }


}
