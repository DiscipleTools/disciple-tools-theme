<?php
/**
 * Disciple_Tools Post to Post Metabox for Locations
 *
 * @class   Disciple_Tools_Roles
 * @version 0.1.0
 * @since   0.1.0
 * @package Disciple_Tools
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
    public function __construct() {} // End __construct()


    public static function default_multiplier_caps(){
        return [
            'read' => true,  //allow access to wp-admin to set 2nd factor auth settings per user.
            'access_contacts' => true,
            'create_contacts' => true,
            'read_location' => true,
            'access_peoplegroups' => true,
            'list_peoplegroups' => true,
            'access_groups' => true,
            'create_groups' => true,
        ];
    }

    public static function default_user_management_caps(){
        return [
            'promote_users' => true,
            'edit_users' => true,
            'create_users' => true,
            'delete_users' => true,
            'list_users' => true,
            'dt_list_users' => true,
        ];
    }


    /**
     * Call set_roles(), only if the roles version number stored in options is
     * too low.
     *
     * @return void
     */
    public function set_roles_if_needed() {
        $current_number = get_option( 'dt_roles_number' );
        if ($current_number === false || intval( $current_number ) < self::$target_roles_version_number) {
            $this->set_roles();
        }
    }

    /**
     * Install Disciple Tools Roles
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
            [
                // No capabilities to this role. Must be moved to another role for permission.
            ]
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
        return "complete";
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
            [
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
            ]
        );

        add_role(
            'editor', 'Editor',
            [
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
            ]
        );
        add_role(
            'author', 'Author',
            [
                'delete_posts'           => true,
                'delete_published_posts' => true,
                'edit_posts'             => true,
                'edit_published_posts'   => true,
                'publish_posts'          => true,
                'read'                   => true,
                'upload_files'           => true,
            ]
        );

        add_role(
            'contributor', 'Contributor',
            [
                'delete_posts' => true,
                'edit_posts'   => true,
                'read'         => true,
            ]
        );

        add_filter(
            'pre_option_default_role', function() {
                return 'subscriber';
            }
        );

        delete_option( 'dt_roles_number' );
    }
}
