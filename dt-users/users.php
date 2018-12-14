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
    public function __construct() {
        add_action( 'wp_login', [ &$this, 'user_login_hook' ], 10, 2 );
        add_action( 'user_register', [ &$this, 'user_register_hook' ] );
        add_action( 'wpmu_new_user', [ &$this, 'user_register_hook' ] );
        add_action( 'add_user_to_blog', [ &$this, 'user_register_hook' ] );
        add_action( 'profile_update', [ &$this, 'profile_update_hook' ], 99 );
        add_action( "after_switch_theme", [ &$this, "create_contacts_for_existing_users" ] );
        add_action( "wpmu_new_blog", [ &$this, "create_contacts_for_existing_users" ] );

        add_action( "user_new_form", [ &$this, "custom_user_profile_fields" ] );
        add_action( "show_user_profile", [ &$this, "custom_user_profile_fields" ] );
        add_action( "edit_user_profile", [ &$this, "custom_user_profile_fields" ] );
        add_action( "edit_user_created_user", [ $this, "edit_user_created_user" ] );
        add_action( "dt_contact_merged", [ $this, "dt_contact_merged" ], 10, 2 );
        add_filter( 'user_row_actions', [ $this, 'dt_edit_user_row_actions' ], 10, 2 );
        add_filter( 'manage_users_columns', [ $this, 'new_modify_user_table' ] );
        add_filter( 'manage_users_custom_column', [ $this, 'new_modify_user_table_row' ], 10, 3 );
        add_action( 'delete_user', [ $this, 'user_deleted' ], 10, 1 );
        add_action( 'remove_user_from_blog', [ $this, 'user_deleted' ], 10, 2 );

    }

    /**
     * Get assignable users
     *
     * @param  $search_string
     *
     * @return array|\WP_Error
     */

    public static function get_assignable_users_compact( string $search_string = null ) {
        if ( !current_user_can( "access_contacts" ) ) {
            return new WP_Error( __FUNCTION__, __( "No permissions to assign" ), [ 'status' => 403 ] );
        }

        global $wpdb;
        $user_id = get_current_user_id();
        $users = [];
        if ( !user_can( get_current_user_id(), 'view_any_contacts' ) ){
            // users that are shared posts that are shared with me
            $users_ids = $wpdb->get_results( $wpdb->prepare("
                SELECT user_id
                FROM $wpdb->dt_share
                WHERE post_id IN (
                      SELECT post_id
                      FROM $wpdb->dt_share
                      WHERE user_id = %1\$s
                )
                GROUP BY user_id
                ",
                $user_id
            ), ARRAY_N );

            $dispatchers = $wpdb->get_results("SELECT user_id FROM $wpdb->usermeta WHERE meta_key =
            'wp_capabilities' AND meta_value LIKE '%dispatcher%'");

            $assure_unique = [];
            foreach ( $dispatchers as $index ){
                $id = $index->user_id;
                if ( $id && !in_array( $id, $assure_unique )){
                    $assure_unique[] = $id;
                    $users[] = get_user_by( "ID", $id );
                }
            }
            foreach ( $users_ids as $index ){
                $id = $index[0];
                if ( $id && !in_array( $id, $assure_unique )){
                    $assure_unique[] = $id;
                    $users[] = get_user_by( "ID", $id );
                }
            }
        } else {

            $search_string = esc_attr( $search_string );
            $user_query = new WP_User_Query( [
                'search'         => '*' . $search_string . '*',
                'search_columns' => [
                    'user_login',
                    'user_nicename',
                    'user_email',
                    'user_url',
                    'display_name'
                ],
                'number' => 10
            ] );

            $users = $user_query->get_results();
        }
        $list = [];

        foreach ( $users as $user ) {
            if ( user_can( $user, "access_contacts" ) ) {
                $list[] = [
                    "name" => $user->display_name,
                    "ID"   => $user->ID,
                    "user" => $user->user_login,
                    "avatar" => get_avatar_url( $user->ID, [ 'size' => '16' ] )
                ];
            }
        }

        function asc_meth( $a, $b ) {
            $a["name"] = strtolower( $a["name"] );
            $b["name"] = strtolower( $b["name"] );
            return strcmp( $a["name"], $b["name"] );
        }

        usort( $list, "asc_meth" );
        return $list;
    }

    /**
     * Switch user preference for notifications and availability meta fields.
     *
     * @param int $user_id
     * @param string $preference_key
     *
     * @param string|null $type
     *
     * @return array
     */
    public static function switch_preference( int $user_id, string $preference_key, string $type = null ) {

        $value = get_user_meta( $user_id, $preference_key, true );

        $label = '';
        $default = false;
        if ( $type === "notifications" ){
            $default = true;
        }

        if ( $value === '' ){
            $status = update_metadata( 'user', $user_id, $preference_key, $default ? '0' : '1' );
            $label = $default ? "false" : "true";
        } elseif ( $value === '0'){
            $status = update_metadata( 'user', $user_id, $preference_key, "1" );
            $label = "true";
        } else {
            $status = update_metadata( 'user', $user_id, $preference_key, '0' );
            $label = "false";
        }

        if ( $status ) {
            return [
                'status'   => true,
                'response' => $status,
            ];
        } else {
            return [
                'status'  => false,
                'message' => 'Unable to update_user_option ' . $preference_key . ' to ' . $label
            ];
        }
    }

    /**
     * Processes updates posted for current user details.
     */
    public static function update_user_contact_info() {
        global $wpdb;
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
        if ( isset( $_POST['user_email'] ) && !empty( $_POST['user_email'] ) ) {
            $args['user_email'] = sanitize_email( wp_unslash( $_POST['user_email'] ) );
        }
        if ( isset( $_POST['description'] ) ) {
            $args['description'] = sanitize_text_field( wp_unslash( $_POST['description'] ) );
        }
        if ( isset( $_POST['nickname'] ) ) {
            $args['nickname'] = sanitize_text_field( wp_unslash( $_POST['nickname'] ) );
        }
        if ( isset( $_POST['locale'] ) ) {
            $args['locale'] = sanitize_text_field( wp_unslash( $_POST['locale'] ) );
        }
        if ( isset( $_POST['display_name'] ) && !empty( $_POST['display_name'] ) ) {
            $args['display_name'] = $args['nickname'];
        }
        //locale
        if ( isset( $_POST['locale'] ) ) {
            $args['locale'] = sanitize_text_field( wp_unslash( $_POST['locale'] ) );
        } else {
            $args['locale'] = "en_US";
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

        //check that display name is not null and is a new name
        if ( !empty( $args['nickname'] ) && $current_user->display_name != $args['nickname'] ) {
            //set display name to nickname
            $user_id = wp_update_user( array(
                'ID' => $args['ID'],
                'display_name' => $args['nickname']
                )
            );

        }

        return wp_redirect( get_site_url() ."/settings" );
    }


    public static function get_contact_for_user( $user_id ){
        if ( !current_user_can( "access_contacts" )){
            return new WP_Error( 'no_permission', __( "Insufficient permissions" ), [ 'status' => 403 ] );
        }
        $contact_id = get_user_option( "corresponds_to_contact", $user_id );
        if ( !empty( $contact_id )){
            return $contact_id;
        }
        $args = [
            'post_type'  => 'contacts',
            'relation'   => 'AND',
            'meta_query' => [
                [
                    'key' => "corresponds_to_user",
                    "value" => $user_id
                ],
                [
                    'key' => "type",
                    "value" => "user"
                ],
            ],
        ];
        $contacts = new WP_Query( $args );
        if ( isset( $contacts->post->ID ) ){
            return $contacts->post->ID;
        } else {
            return null;
        }
    }

    /**
     * Create a Contact for each user that registers
     *
     * @param $user_id
     */
    public static function create_contact_for_user( $user_id ) {
        $user = get_user_by( 'id', $user_id );
        $corresponds_to_contact = get_user_option( "corresponds_to_contact", $user_id );
        if ( $user && $user->has_cap( 'access_contacts' ) ) {
            if ( empty( $corresponds_to_contact )){
                $args = [
                    'post_type'  => 'contacts',
                    'relation'   => 'AND',
                    'meta_query' => [
                        [
                            'key' => "corresponds_to_user",
                            "value" => $user_id
                        ],
                        [
                            'key' => "type",
                            "value" => "user"
                        ],
                    ],
                ];
                $contacts = new WP_Query( $args );
                if ( isset( $contacts->post->ID ) ){
                    $corresponds_to_contact = $contacts->post->ID;
                    update_user_option( $user_id, "corresponds_to_contact", $corresponds_to_contact );
                }
            }

            if ( empty( $corresponds_to_contact ) ) {
                $new_id = Disciple_Tools_Contacts::create_contact( [
                    "title"               => $user->display_name,
                    "assigned_to"         => "user-" . $user_id,
                    "type"                => "user",
                    "overall_status"      => "assigned",
                    "corresponds_to_user" => $user_id,
                ], false );
                if ( !is_wp_error( $new_id )){
                    update_user_option( $user_id, "corresponds_to_contact", $new_id );
                }
            } else {
                $contact = get_post( $corresponds_to_contact );
                if ( $contact && $contact->post_title != $user->display_name ){
                    Disciple_Tools_Contacts::update_contact( $corresponds_to_contact, [
                        "title" => $user->display_name
                    ], false, true );
                }
            }
        }
    }

    /**
     * User register hook
     *
     * @param $user_id
     */
    public static function user_register_hook( $user_id ) {
        $corresponds_to_contact = get_user_option( "corresponds_to_contact", $user_id );
        if ( empty( $corresponds_to_contact ) ){
            self::create_contact_for_user( $user_id );
        }
    }

    public static function user_login_hook( $user_name, $user ){
        $corresponds_to_contact = get_user_option( "corresponds_to_contact", $user->ID );
        if ( empty( $corresponds_to_contact ) ){
            self::create_contact_for_user( $user->ID );
        }
    }

    /**
     * Profile update hook
     *
     * @param $user_id
     */
    public static function profile_update_hook( $user_id ) {
        self::create_contact_for_user( $user_id );
    }

    public static function create_contacts_for_existing_users(){
        if ( is_user_logged_in() ){
            $users = get_users();
            foreach ( $users as $user ){
                self::create_contact_for_user( $user->ID );
            }
        }
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

    public static function get_user_filters(){
        $current_user_id = get_current_user_id();
        $filters = [];
        if ( $current_user_id ){
            $filters = maybe_unserialize( get_user_meta( $current_user_id, "saved_filters", true ) );
            if ( empty( $filters )){
                $filters = [];
            }
        }
        return $filters;
    }

    public static function save_user_filters( $filters ){
        $current_user_id = get_current_user_id();
        if ( $current_user_id ){
            $filters = update_user_meta( $current_user_id, "saved_filters", $filters );
        }
        return $filters;
    }


    public function edit_user_created_user( $user_id ){
        if ( isset( $_REQUEST['action'] ) && 'createuser' == $_REQUEST['action'] ) {
            check_admin_referer( 'create-user', '_wpnonce_create-user' );
        } else {
            check_admin_referer( 'update-user_' . $user_id );
        }
        if ( isset( $_POST["corresponds_to_contact_id"] )){
            $corresponds_to_contact = sanitize_text_field( wp_unslash( $_POST["corresponds_to_contact_id"] ) );
            update_user_option( $user_id, "corresponds_to_contact", $corresponds_to_contact );
            Disciple_Tools_Contacts::update_contact( (int) $corresponds_to_contact, [
                "corresponds_to_user" => $user_id
            ], false, true );
        }
    }

    public function custom_user_profile_fields( $user ){
        $contact_id = "";
        $contact_title = "";
        if ( $user != "add-new-user" && $user != "add-existing-user" && isset( $user->ID ) ) {
            $contact_id   = get_user_option( "corresponds_to_contact", $user->ID );
            if ( $contact_id ){
                $contact = get_post( $contact_id );
                if ( $contact ){
                    $contact_title = $contact->post_title;
                }
            }
        }
        if ( empty( $contact_title ) ) : ?>
        <script type="application/javascript">
            jQuery(document).ready(function($) {
                jQuery("#corresponds_to_contact").autocomplete({
                    source: function (request, response) {
                        jQuery.ajax({
                            url: '<?php echo esc_html( rest_url() ) ?>dt/v1/contacts/compact',
                            data: {
                                s: request.term
                            },
                            beforeSend: function (xhr) {
                                xhr.setRequestHeader('X-WP-Nonce',
                                    "<?php echo esc_html( wp_create_nonce( 'wp_rest' ) ) ?>");
                            }
                        }).then(data=>{
                            response(data.posts);
                        })
                    },
                    minLength: 2,
                    select: function (event, ui) {
                        $( "#corresponds_to_contact" ).val( ui.item.name );
                        $( "#corresponds_to_contact_id" ).val( ui.item.ID );
                        return false;
                    }
                }).autocomplete( "instance" )._renderItem = function( ul, item ) {
                    return $( "<li>" )
                        .append( `<div>${item.name} (${item.ID})</div>` )
                        .appendTo( ul );
                };
            });
        </script>
        <?php endif; ?>
        <h3><?php esc_html_e( "Extra D.T Information", 'disciple_tools' ) ?></h3>
        <table class="form-table">
            <tr>
                <th><label for="contact"><?php esc_html_e( "Corresponds to Contact", 'disciple_tools' ) ?></label></th>
                <td>
                    <?php if ( !empty( $contact_title ) ) : ?>
                        <a href="<?php echo esc_html( get_permalink( $contact_id ) ) ?>"><?php echo esc_html( $contact_title ) ?></a>
                    <?php else : ?>
                        <input type="text" class="regular-text" name="corresponds_to_contact" value="<?php echo esc_html( $contact_title )?>" id="corresponds_to_contact" /><br />
                        <input type="hidden" class="regular-text" name="corresponds_to_contact_id" value="<?php echo esc_html( $contact_id )?>" id="corresponds_to_contact_id" />
                        <?php if ( $contact_id ) : ?>
                            <span class="description"><a href="<?php echo esc_html( get_site_url() . '/contacts/' . $contact_id )?>" target="_blank"><?php esc_html_e( "View contact", 'disciple_tools' ) ?></a></span>
                        <?php else :?>
                            <span class="description"><?php esc_html_e( "Is this user already a contact in D.T?", 'disciple_tools' ) ?></span>
                        <?php endif; ?>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
        <?php
    }


    public function dt_contact_merged( $master_id, $non_master_id){
        //check to make sure both contacts don't point to a user
        $corresponds_to_user = get_post_meta( $master_id, "corresponds_to_user", true );
        if ( $corresponds_to_user ){
            $contact_id = get_user_option( "corresponds_to_contact", $corresponds_to_user );
            //make sure the user points to the right contact
            if ( $contact_id != $master_id ){
                update_user_option( $corresponds_to_user, "corresponds_to_contact", $master_id );
            }
            $dup_corresponds_to_contact = get_post_meta( $non_master_id, "corresponds_to_user", true );
            if ( $dup_corresponds_to_contact ){
                delete_post_meta( $non_master_id, "corresponds_to_user" );
            }
        }

        $master_contact_type = get_post_meta( $master_id, "type", true );
        if ( $master_contact_type === "user"){
            $non_master_contact_type = get_post_meta( $non_master_id, "type", true );
            if ( !empty( $non_master_contact_type ) && $non_master_contact_type != "user" ){
                update_post_meta( $master_id, "type", $non_master_contact_type );
            }
        }
    }


    public static function create_user( $user_name, $user_email, $display_name, $corresponds_to_contact = null ){
        if ( !current_user_can( "create_users" ) ){
            return new WP_Error( "create_user", "You don't have permissions to create users", [ 'status', 401 ] );
        }

        $user_id = username_exists( $user_name );
        if ( $user_id ){
            return new WP_Error( "create_user", __( "Username already exists", 'disciple_tools' ), [ 'status', 403 ] );
        }
        $email_exists = email_exists( $user_email );
        if ( $email_exists ){
            return new WP_Error( "create_user", __( "Email already exists", 'disciple_tools' ), [ 'status', 403 ] );
        }

        $random_password = wp_generate_password( $length = 12, $include_standard_special_chars = false );
        $user_id = wp_create_user( $user_name, $random_password, $user_email );
        if ( is_wp_error( $user_id )){
            return $user_id;
        }
        $user = get_user_by( 'id', $user_id );
        $user->display_name = $display_name;
        $user->set_role( "multiplier" );
        wp_update_user( $user );
        if ( $corresponds_to_contact ){
            update_user_option( $user_id, "corresponds_to_contact", $corresponds_to_contact );
            update_post_meta( $corresponds_to_contact, "corresponds_to_user", $user_id );
        }
        return $user_id;
    }


    public function dt_edit_user_row_actions( $actions, $user ){
        $contact_id = self::get_contact_for_user( $user->ID );
        if ( $contact_id ){
            $link = get_permalink( $contact_id );
            $actions["view"] = '<a href="' . $link . '" aria-label="View contact">' . __( "View contact record", "Disciple Tools" ) . '</a>';
        }
        return $actions;
    }
    public function new_modify_user_table( $column ) {
        $column = array_slice( $column, 0, 3, true ) +
        array( "display_name" => "Display Name" ) +
        array_slice( $column, 3, null, true );
        return $column;
    }

    public function new_modify_user_table_row( $val, $column_name, $user_id ) {
        switch ( $column_name ) {
            case 'display_name' :
                return dt_get_user_display_name( $user_id );
                break;
            default:
        }
        return $val;
    }


    public function user_deleted( $user_id, $blog_id = null ){
        $corresponds_to_contact = self::get_contact_for_user( $user_id );
        if ( $corresponds_to_contact ){
            delete_post_meta( $corresponds_to_contact, "corresponds_to_user" );
        }
    }
}
