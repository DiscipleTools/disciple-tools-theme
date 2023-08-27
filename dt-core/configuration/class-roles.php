<?php
/**
 * Disciple_Tools Post to Post Metabox for Locations
 *
 * @class   Disciple_Tools_Roles
 * @version 0.1.0
 * @since   0.1.0
 * @package Disciple.Tools
 *
 */

/**
 * Class Disciple_Tools_Roles
 */
class Disciple_Tools_Roles
{

    /**
     * The version number of this roles file. Every time the roles are
     * modified, we should increment this by one, so that the roles will get
     * reset upon plugin update. See the code that calls set_roles_if_needed in
     * disciple-tools.php
     *
     * @var int
     */
    private static $target_roles_version_number = 25;

    /**
     * The single instance of Disciple_Tools_Roles
     *
     * @var    object
     * @access private
     * @since  0.1.0
     */
    private static $_instance = null;

    /**
     * Main Disciple_Tools_P2P_Metabox Instance
     * Ensures only one instance of Disciple_Tools_P2P_Metabox is loaded or can be loaded.
     *
     * @since  0.1.0
     * @static
     * @return Disciple_Tools_Roles instance
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    } // End instance()

    /**
     * Constructor function.
     *
     * @access public
     * @since  0.1.0
     */
    public function __construct() {
        add_filter( 'dt_set_roles_and_permissions', array( $this, 'dt_setup_custom_roles_and_permissions' ), 5, 1 );
        add_filter( 'dt_set_roles_and_permissions', array( $this, 'dt_setup_permissions' ), 100, 1 );
    } // End __construct()

    public static function get_dt_roles_and_permissions( $use_cache = true ){
        $cache = wp_cache_get( 'dt_roles_and_permissions' );
        if ( $cache && $use_cache ){
            return $cache;
        }
        $cache = apply_filters( 'dt_set_roles_and_permissions', array() );
        wp_cache_set( 'dt_roles_and_permissions', $cache );
        return $cache;
    }

    /**
     * Add custom roles to the roles array.
     * @param $expected_roles
     * @return mixed
     */
    public static function dt_setup_custom_roles_and_permissions( $expected_roles ) {
        $expected_roles['registered'] = array(
            'label' => __( 'Registered', 'disciple_tools' ),
            'description' => 'Has no permissions',
            'permissions' => array(),
            'order' => 4,
        );

        $all_user_caps = self::default_user_caps();
        $user_management_caps = self::default_user_management_caps();
        $manage_dt_caps = self::default_manage_dt_caps();
        $metrics_caps = self::default_all_metrics_caps();
        $manage_role_caps = array_reduce(dt_multi_role_get_plugin_capabilities(), function ( $caps, $slug ) {
            $caps[$slug] = true;
            return $caps;
        }, array());

        $expected_roles['multiplier'] = array(
            'label' => __( 'Multiplier', 'disciple_tools' ),
            'description' => 'Interacts with Contacts and Groups',
            'permissions' => array_merge( $all_user_caps ),
            'type' => array( 'base' ),
            'order' => 5,
        );
        $expected_roles['strategist'] = array(
            'label' => __( 'Strategist', 'disciple_tools' ),
            'description' => 'View project metrics',
            'permissions' => array_merge( array( 'access_disciple_tools' => true ), $metrics_caps ),
            'type' => array( 'support' ),
            'order' => 40,
        );
        $expected_roles['user_manager'] = array(
            'label' => __( 'User Manager', 'disciple_tools' ),
            'description' => 'List, invite, promote and demote users',
            'permissions' => array_merge( $all_user_caps, $user_management_caps ),
            'type' => array( 'base' ),
            'order' => 95,
        );
        $expected_roles['dt_admin'] = array(
            'label' => __( 'Disciple.Tools Admin', 'disciple_tools' ),
            'description' => 'All D.T permissions',
            'permissions' => array_merge( $all_user_caps, $user_management_caps, $manage_dt_caps, $metrics_caps ),
            'type' => array( 'base' ),
            'order' => 98,
        );
        $expected_roles['administrator'] = array(
            'label' => __( 'Administrator', 'disciple_tools' ),
            'description' => 'All D.T permissions plus the ability to manage plugins.',
            'permissions' => array_merge( $all_user_caps, $user_management_caps, $manage_dt_caps, $metrics_caps, $manage_role_caps ),
            'type' => array( 'base' ),
            'order' => 100,
            'is_editable' => false,
        );


        /**
         * Add custom roles to the roles array.
         */
        $custom_roles = get_option( 'dt_custom_roles', array() );
        foreach ( $custom_roles as $role ) {
            $permissions = is_array( $role['capabilities'] ) ? $role['capabilities'] : array();
            if ( !isset( $expected_roles[$role['slug']] ) ){
                $expected_roles[$role['slug']] = array(
                    'label' => $role['label'],
                    'permissions' => $permissions,
                    'description' => $role['description'],
                    'is_editable' => $role['is_editable'] ?? true,
                    'custom' => $role['custom'] ?? true,
                    'type' => array( 'custom' ),
                );
            }
        }

        return $expected_roles;
    }

    /**
     * Add custom permissions to the roles array.
     * @param $roles
     * @return array
     */
    public function dt_setup_permissions( $roles ) {
        $custom_roles = get_option( 'dt_custom_roles', array() );
        foreach ( $custom_roles as $custom_role ) {
            $custom_permissions = is_array( $custom_role['capabilities'] ) ? $custom_role['capabilities'] : array();
            $roles[$custom_role['slug']]['permissions'] = array_merge( $roles[$custom_role['slug']]['permissions'], $custom_permissions );
        }

        return $roles;
    }

    public static function default_user_caps(){
        return array(
            'read' => true,  //allow access to wp-admin to set 2nd factor auth settings per user.
            'access_disciple_tools' => true,
            'read_location' => true,
        );
    }

    public static function default_multiplier_caps(){
        return array(
            'read' => true,  //allow access to wp-admin to set 2nd factor auth settings per user.
            'access_disciple_tools' => true,
            'access_contacts' => true,
            'create_contacts' => true,
            'read_location' => true,
            'access_peoplegroups' => true,
            'access_groups' => true,
            'create_groups' => true,
        );
    }

    public static function default_user_management_caps(){
        return array(
            'promote_users' => true,
            'edit_users' => true,
            'create_users' => true,
            'delete_users' => true,
            'remove_users' => true,
            'list_users' => true,
            'dt_list_users' => true,
        );
    }

    public static function default_manage_dt_caps(){
        return array(
            'manage_dt' => true,
        );
    }

    public static function default_all_metrics_caps(){
        return array(
            'view_project_metrics' => true,
        );
    }


    /**
     * Call set_roles(), only if the roles version number stored in options is
     * too low.
     *
     * @return void
     */
    public function set_roles_if_needed() {
        $current_number = get_option( 'dt_roles_number' );
        if ( $current_number === false || intval( $current_number ) < self::$target_roles_version_number ) {
            $this->set_roles();
        }
    }

    /**
     * Install Disciple.Tools Roles
     *
     * @return string
     */
    public function set_roles() {
        /* IMPORTANT:
         *
         * If you modify the roles here, make sure to increment
         * $target_roles_version_number by one, set above.
         */

        if ( get_role( 'registered' ) ) {
            remove_role( 'registered' );
        }
        add_role(
            'registered', __( 'Registered', 'disciple_tools' ),
            array(
                // No capabilities to this role. Must be moved to another role for permission.
            )
        );

        /**
         * Default user role set to registered in /includes/drm-filters.php
         */
        remove_role( 'subscriber' );
        remove_role( 'contributor' );
        remove_role( 'editor' );
        remove_role( 'author' );
        //Remove old or un-implement roles so they don't confuse people
        remove_role( 'multiplier_leader' );
        remove_role( 'marketer_leader' );
        remove_role( 'prayer_supporter' );
        remove_role( 'project_supporter' );

        update_option( 'dt_roles_number', self::$target_roles_version_number );
        return 'complete';
    }

    /*
    * Reset Roles on deactivation
    */
    public function reset_roles() {
        delete_option( 'run_once' );

        remove_role( 'dispatcher' );
        remove_role( 'dt_admin' );
        remove_role( 'strategist' );
        remove_role( 'multiplier' );
        remove_role( 'marketer' );
        remove_role( 'partner' );
        remove_role( 'multiplier_leader' );
        remove_role( 'marketer_leader' );
        remove_role( 'prayer_supporter' );
        remove_role( 'project_supporter' );

        add_role(
            'subscriber', __( 'Subscriber', 'disciple_tools' ),
            array(
                'delete_others_posts'    => true,
                'delete_pages'           => true,
                'delete_posts'           => true,
                'delete_private_pages'   => true,
                'delete_private_posts'   => true,
                'delete_published_pages' => true,
                'delete_published_posts' => true,
                'edit_others_pages'      => true,
                'edit_others_posts'      => true,
                'edit_pages'             => true,
                'edit_posts'             => true,
                'edit_private_pages'     => true,
                'edit_private_posts'     => true,
                'edit_published_pages'   => true,
                'edit_published_posts'   => true,
                'manage_categories'      => true,
                'manage_links'           => true,
                'moderate_comments'      => true,
                'publish_pages'          => true,
                'publish_posts'          => true,
                'read'                   => true,
                'read_private_pages'     => true,
                'read_private_posts'     => true,
                'upload_files'           => true,
            )
        );

        add_role(
            'editor', 'Editor',
            array(
                'delete_others_posts'    => true,
                'delete_pages'           => true,
                'delete_posts'           => true,
                'delete_private_pages'   => true,
                'delete_private_posts'   => true,
                'delete_published_pages' => true,
                'delete_published_posts' => true,
                'edit_others_pages'      => true,
                'edit_others_posts'      => true,
                'edit_pages'             => true,
                'edit_posts'             => true,
                'edit_private_pages'     => true,
                'edit_private_posts'     => true,
                'edit_published_pages'   => true,
                'edit_published_posts'   => true,
                'manage_categories'      => true,
                'manage_links'           => true,
                'moderate_comments'      => true,
                'publish_pages'          => true,
                'publish_posts'          => true,
                'read'                   => true,
                'read_private_pages'     => true,
                'read_private_posts'     => true,
                'upload_files'           => true,
                'level_0'                => true,
            )
        );
        add_role(
            'author', 'Author',
            array(
                'delete_posts'           => true,
                'delete_published_posts' => true,
                'edit_posts'             => true,
                'edit_published_posts'   => true,
                'publish_posts'          => true,
                'read'                   => true,
                'upload_files'           => true,
            )
        );

        add_role(
            'contributor', 'Contributor',
            array(
                'delete_posts' => true,
                'edit_posts'   => true,
                'read'         => true,
            )
        );

        add_filter(
            'pre_option_default_role', function () {
                return 'subscriber';
            }
        );

        delete_option( 'dt_roles_number' );
    }
}
