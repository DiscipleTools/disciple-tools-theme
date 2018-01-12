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
    private static $target_roles_version_number = 7;

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
     * @access public
     * @since  0.1.0
     */
    public function __construct() {} // End __construct()


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
    public function set_roles()
    {
        /* IMPORTANT:
         *
         * If you modify the roles here, make sure to increment
         * $target_roles_version_number by one, set above.
         */

        /* TODO: Different capabilities are commented out below in the different roles as we configure usage in development, but should be removed for distribution. */

        if ( get_role( 'strategist' ) ) {
            remove_role( 'strategist' );
        }
        add_role(
            'strategist', __( 'Strategist' ),
            [
                /* Standard Capabilities */
                'list_users'                 => true,
                'delete_others_posts'        => true,
                'delete_pages'               => true,
                'delete_posts'               => true,
                'delete_private_pages'       => true,
                'delete_private_posts'       => true,
                'delete_published_pages'     => true,
                'delete_published_posts'     => true,
                'edit_others_pages'          => true,
                'edit_others_posts'          => true,
                'edit_pages'                 => true,
                'edit_posts'                 => true,
                'edit_private_pages'         => true,
                'edit_private_posts'         => true,
                'edit_published_pages'       => true,
                'edit_published_posts'       => true,
                'manage_categories'          => true,
                'manage_links'               => true,
                'moderate_comments'          => true,
                'publish_pages'              => true,
                'publish_posts'              => true,
                'read'                       => true,
                'read_private_pages'         => true,
                'read_private_posts'         => true,
                'upload_files'               => true,
                'level_0'                    => true,
                /* Manage DT Options */
                'manage_dt'                  => true,
                /* See all contacts */
                'manage_contacts'            => true,
                /* Add custom caps for contacts */
                'edit_contact'               => true,
                'read_contact'               => true,
                'delete_contact'             => true,
                'delete_others_contacts'     => true,
                'delete_contacts'            => true,
                'edit_contacts'              => true,
                'edit_team_contacts'         => true,
                'edit_others_contacts'       => true,
                'publish_contacts'           => true,
                'read_private_contacts'      => true,
                /* Add custom caps for groups */
                'edit_group'                 => true,
                'read_group'                 => true,
                'delete_group'               => true,
                'delete_others_groups'       => true,
                'delete_groups'              => true,
                'edit_groups'                => true,
                'edit_others_groups'         => true,
                'publish_groups'             => true,
                'read_private_groups'        => true,
                /* Add custom caps for prayer updates */
                'read_prayer'                => true,
                'edit_prayer'                => true,
                'delete_prayer'              => true,
                'delete_others_prayers'      => true,
                'delete_prayers'             => true,
                'edit_prayers'               => true,
                'edit_others_prayers'        => true,
                'publish_prayers'            => true,
                'read_private_prayers'       => true,
                /* Add custom caps for locations */
                'read_location'              => true,
                'edit_location'              => true,
                'delete_location'            => true,
                'delete_others_locations'    => true,
                'delete_locations'           => true,
                'edit_locations'             => true,
                'edit_others_locations'      => true,
                'publish_locations'          => true,
                'read_private_locations'     => true,
                /* Add custom caps for progresss */
                'read_progress'              => true,
                'edit_progress'              => true,
                'delete_progress'            => true,
                'delete_others_progresss'    => true,
                'delete_progresss'           => true,
                'edit_progresss'             => true,
                'edit_others_progresss'      => true,
                'publish_progresss'          => true,
                'read_private_progresss'     => true,
                /* Add custom caps for assets */
                'read_assetmapping'          => true,
                'edit_assetmapping'          => true,
                'delete_assetmapping'        => true,
                'delete_others_assetmapping' => true,
                'delete_assetmappings'       => true,
                'edit_assetmappings'         => true,
                'edit_others_assetmapping'   => true,
                'publish_assetmapping'       => true,
                'read_private_assetmappings' => true,
                /* Add custom caps for resources */
                'read_resource'              => true,
                'edit_resource'              => true,
                'delete_resource'            => true,
                'delete_others_resource'     => true,
                'delete_resources'           => true,
                'edit_resources'             => true,
                'edit_others_resource'       => true,
                'publish_resource'           => true,
                'read_private_resources'     => true,
                /* Add custom caps for people groups */
                'read_peoplegroup'           => true,
                'edit_peoplegroup'           => true,
                'delete_peoplegroup'         => true,
                'delete_others_peoplegroup'  => true,
                'delete_peoplegroups'        => true,
                'edit_peoplegroups'          => true,
                'edit_others_peoplegroup'    => true,
                'publish_peoplegroup'        => true,
                'read_private_peoplegroups'  => true,

            ]
        );

        if ( get_role( 'dispatcher' ) ) {
            remove_role( 'dispatcher' );
        }
        add_role(
            'dispatcher', __( 'Dispatcher' ),
            [
                /* Standard Capabilities */
                //'list_users' => false,
                //'delete_others_posts' => true,
                //'delete_pages' => true,
                //'delete_posts' => true,
                //'delete_private_pages' => true,
                //'delete_private_posts' => true,
                //'delete_published_pages' => true,
                //'delete_published_posts' => true,
                //'edit_others_pages' => true,
                //'edit_others_posts' => true,
                //'edit_pages' => false,
                //'edit_posts' => true,
                //'edit_private_pages' => true,
                //'edit_private_posts' => true,
                //'edit_published_pages' => true,
                //'edit_published_posts' => true,
                //'manage_categories' => true,
                //'manage_links' => true,
                //'moderate_comments' => true,
                //'publish_pages' => true,
                //'publish_posts' => true,
                //'read_private_pages' => true,
                //'read_private_posts' => true,
                //'upload_files' => true,
                'read'                      => true,
                'level_0'                   => true,

                /* Manage DT Options */
                'manage_dt'                 => true,

                /* Add custom caps for contacts */
                'access_contacts'           => true,
                'create_contacts'           => true,  //create a new contact
                'update_shared_contacts'    => true,
                'view_any_contacts'         => true,    //view any contacts
                'assign_any_contacts'       => true,  //assign contacts to others
                'update_any_contacts'       => true,  //update any contacts
                'delete_any_contacts'       => true,  //delete any contacts

                /* Add custom caps for groups */
                'access_groups'             => true,
                'create_groups'             => true,
                'view_any_groups'           => true,    //view any groups
                'assign_any_groups'         => true,  //assign groups to others
                'update_any_groups'         => true,  //update any groups
                'delete_any_groups'         => true,  //delete any groups

                /* Add custom caps for locations */
                'read_location'              => true,
                'edit_location'              => true,
                'delete_location'            => true,
                'delete_others_locations'    => true,
                'delete_locations'           => true,
                'edit_locations'             => true,
                'edit_others_locations'      => true,
                'publish_locations'          => true,
                'read_private_locations'     => true,

                /* Add custom caps for resources */
                'read_resource'             => true,
                'edit_resource'             => true,
                'delete_resource'           => true,
                'delete_others_resource'    => true,
                'delete_resources'          => true,
                'edit_resources'            => true,
                'edit_others_resource'      => true,
                'publish_resource'          => true,
                'read_private_resources'    => true,
                /* Add custom caps for people groups */
                'read_peoplegroup'          => true,
                'edit_peoplegroup'          => true,
                'delete_peoplegroup'        => true,
                'delete_others_peoplegroup' => true,
                'delete_peoplegroups'       => true,
                'edit_peoplegroups'         => true,
                'edit_others_peoplegroup'   => true,
                'publish_peoplegroup'       => true,
                'read_private_peoplegroups' => true,

            ]
        );

        if ( get_role( 'marketer' ) ) {
            remove_role( 'marketer' );
        }
        add_role(
            'marketer', __( 'Marketer' ),
            [
                'access_contacts'        => true,
                'create_contacts'        => true,
                'update_shared_contacts' => true,

                'access_groups' => true,
                'create_groups' => true,

                'read_location' => true
            ]
        );

        if ( get_role( 'marketer_leader' ) ) {
            remove_role( 'marketer_leader' );
        }
        add_role(
            'marketer_leader', __( 'Marketer Leader' ),
            [
                /* Standard Capabilities */
                'list_users'                 => true,
                'delete_others_posts'        => true,
                'delete_pages'               => true,
                'delete_posts'               => true,
                'delete_private_pages'       => true,
                'delete_private_posts'       => true,
                'delete_published_pages'     => true,
                'delete_published_posts'     => true,
                'edit_others_pages'          => true,
                'edit_others_posts'          => true,
                'edit_pages'                 => true,
                'edit_posts'                 => true,
                'edit_private_pages'         => true,
                'edit_private_posts'         => true,
                'edit_published_pages'       => true,
                'edit_published_posts'       => true,
                'manage_categories'          => true,
                'manage_links'               => true,
                'moderate_comments'          => true,
                'publish_pages'              => true,
                'publish_posts'              => true,
                'read'                       => true,
                'read_private_pages'         => true,
                'read_private_posts'         => true,
                'upload_files'               => true,
                'level_0'                    => true,
                /* See all contacts */
                'manage_contacts'            => true,
                /* Add custom caps for contacts */
                'edit_contact'               => true,
                'read_contact'               => true,
                'delete_contact'             => true,
                'delete_others_contacts'     => true,
                'delete_contacts'            => true,
                'edit_contacts'              => true,
                'edit_team_contacts'         => true,
                'edit_others_contacts'       => true,
                'publish_contacts'           => true,
                'read_private_contacts'      => true,
                /* Add custom caps for groups */
                'edit_group'                 => true,
                'read_group'                 => true,
                'delete_group'               => true,
                'delete_others_groups'       => true,
                'delete_groups'              => true,
                'edit_groups'                => true,
                'edit_others_groups'         => true,
                'publish_groups'             => true,
                'read_private_groups'        => true,
                /* Add custom caps for prayer updates */
                'read_prayer'                => true,
                'edit_prayer'                => true,
                'delete_prayer'              => true,
                'delete_others_prayers'      => true,
                'delete_prayers'             => true,
                'edit_prayers'               => true,
                'edit_others_prayers'        => true,
                'publish_prayers'            => true,
                'read_private_prayers'       => true,
                /* Add custom caps for locations */
                'read_location'              => true,
                'edit_location'              => true,
                'delete_location'            => true,
                'delete_others_locations'    => true,
                'delete_locations'           => true,
                'edit_locations'             => true,
                'edit_others_locations'      => true,
                'publish_locations'          => true,
                'read_private_locations'     => true,
                /* Add custom caps for progresss */
                'read_progress'              => true,
                'edit_progress'              => true,
                'delete_progress'            => true,
                'delete_others_progresss'    => true,
                'delete_progresss'           => true,
                'edit_progresss'             => true,
                'edit_others_progresss'      => true,
                'publish_progresss'          => true,
                'read_private_progresss'     => true,
                /* Add custom caps for assets */
                'read_assetmapping'          => true,
                'edit_assetmapping'          => true,
                'delete_assetmapping'        => true,
                'delete_others_assetmapping' => true,
                'delete_assetmappings'       => true,
                'edit_assetmappings'         => true,
                'edit_others_assetmapping'   => true,
                'publish_assetmapping'       => true,
                'read_private_assetmappings' => true,
                /* Add custom caps for resources */
                'read_resource'              => true,
                'edit_resource'              => true,
                'delete_resource'            => true,
                'delete_others_resource'     => true,
                'delete_resources'           => true,
                'edit_resources'             => true,
                'edit_others_resource'       => true,
                'publish_resource'           => true,
                'read_private_resources'     => true,
                /* Add custom caps for people groups */
                'read_peoplegroup'           => true,
                'edit_peoplegroup'           => true,
                'delete_peoplegroup'         => true,
                'delete_others_peoplegroup'  => true,
                'delete_peoplegroups'        => true,
                'edit_peoplegroups'          => true,
                'edit_others_peoplegroup'    => true,
                'publish_peoplegroup'        => true,
                'read_private_peoplegroups'  => true,
                'access_contacts'        => true,
                'create_contacts'        => true,
                'update_shared_contacts' => true,

                'access_groups' => true,
                'create_groups' => true,

            ]
        );

        if ( get_role( 'dt_admin' ) ) {
            remove_role( 'dt_admin' );
        }
        add_role(
            'dt_admin', __( 'DT Admin' ),
            [
                'read'                      => true,
                'level_0'                   => true,

                /* Manage DT Options */
                'manage_dt'                 => true, // key capability for wp-admin dt administration
                'edit_users'                => true,
                'create_users'              => true,
                'delete_users'              => true,
                'list_users'                => true,
                'moderate_comments'         => true,

                /* Add custom caps for contacts */
                'access_contacts'           => true,
                'create_contacts'           => true,  //create a new contact
                'update_shared_contacts'    => true,
                'view_any_contacts'         => true,    //view any contacts
                'assign_any_contacts'       => true,  //assign contacts to others
                'update_any_contacts'       => true,  //update any contacts
                'delete_any_contacts'       => true,  //delete any contacts

                /* Add wp-admin caps for contacts */
                'edit_contact'               => true,
                'read_contact'               => true,
                'delete_contact'             => true,
                'delete_others_contacts'     => true,
                'delete_contacts'            => true,
                'edit_contacts'              => true,
                'edit_team_contacts'         => true,
                'edit_others_contacts'       => true,
                'publish_contacts'           => true,
                'read_private_contacts'      => true,

                /* Add custom caps for groups */
                'access_groups'             => true,
                'create_groups'             => true,
                'view_any_groups'           => true,    //view any groups
                'assign_any_groups'         => true,  //assign groups to others
                'update_any_groups'         => true,  //update any groups
                'delete_any_groups'         => true,  //delete any groups

                /* Add wp-admin caps for groups */
                'edit_group'                 => true,
                'read_group'                 => true,
                'delete_group'               => true,
                'delete_others_groups'       => true,
                'delete_groups'              => true,
                'edit_groups'                => true,
                'edit_others_groups'         => true,
                'publish_groups'             => true,
                'read_private_groups'        => true,

                /* Add custom caps for locations */
                'read_location'             => true,
                'edit_location'             => true,
                'delete_location'           => true,
                'delete_others_locations'   => true,
                'delete_locations'          => true,
                'edit_locations'            => true,
                'edit_others_locations'     => true,
                'publish_locations'         => true,
                'read_private_locations'    => true,

                /* Add custom caps for people groups */
                'read_peoplegroup'          => true,
                'edit_peoplegroup'          => true,
                'delete_peoplegroup'        => true,
                'delete_others_peoplegroups' => true,
                'delete_peoplegroups'       => true,
                'edit_peoplegroups'         => true,
                'edit_others_peoplegroups'   => true,
                'publish_peoplegroups'       => true,
                'read_private_peoplegroups' => true,

            ]
        );

        if ( get_role( 'multiplier' ) ) {
            remove_role( 'multiplier' );
        }
        add_role(
            'multiplier', __( 'Multiplier' ),
            [
                'access_contacts'        => true,
                'create_contacts'        => true,
                'update_shared_contacts' => true,

                'access_groups' => true,
                'create_groups' => true,

                'read_location' => true
            ]
        );

        if ( get_role( 'project_supporter' ) ) {
            remove_role( 'project_supporter' );
        }
        add_role(
            'project_supporter', __( 'Project Supporter' ),
            [
                'project_supporter' => true,
                'read_prayer'       => true,
                'read_progress'     => true,
            ]
        );

        if ( get_role( 'prayer_supporter' ) ) {
            remove_role( 'prayer_supporter' );
        }
        add_role(
            'prayer_supporter', __( 'Prayer Supporter' ),
            [
                'prayer_supporter' => true,
                'read_prayer'      => true,
            ]
        );

        if ( get_role( 'registered' ) ) {
            remove_role( 'registered' );
        }
        add_role(
            'registered', __( 'Registered' ),
            [
                // No capabilities to this role. Must be moved to another role for permission.
            ]
        );

        /**
         * Default user role set to registered in /includes/drm-filters.php
         */
        //        remove_role( 'subscriber' ); // TODO: Removed these features because of multisite compatible
        remove_role( 'contributor' );
        remove_role( 'editor' );
        remove_role( 'author' );

        // Get the administrator role.
        $role = get_role( 'administrator' );

        // If the administrator role exists, add required capabilities for the plugin.
        if ( !empty( $role ) ) {

            /* Manage DT configuration */
            $role->add_cap( 'manage_dt' ); // gives access to dt plugin options
            /* Add contacts permissions */
            $role->add_cap( 'access_contacts' );
            $role->add_cap( 'create_contacts' );
            $role->add_cap( 'update_contacts' );
            $role->add_cap( 'update_shared_contacts' );
            $role->add_cap( 'view_any_contacts' );
            $role->add_cap( 'assign_any_contacts' );
            $role->add_cap( 'update_any_contacts' );
            $role->add_cap( 'delete_any_contacts' );

            /* Add WP-Admin caps for contacts */
            $role->add_cap( 'edit_contact' );
            $role->add_cap( 'read_contact' );
            $role->add_cap( 'delete_contact' );
            $role->add_cap( 'delete_others_contacts' );
            $role->add_cap( 'delete_contacts' );
            $role->add_cap( 'edit_contacts' );
            $role->add_cap( 'edit_team_contacts' );
            $role->add_cap( 'edit_others_contacts' );
            $role->add_cap( 'publish_contacts' );
            $role->add_cap( 'read_private_contacts' );

            /* Add Groups permissions */
            $role->add_cap( 'access_groups' );
            $role->add_cap( 'create_groups' );
            $role->add_cap( 'update_groups' );
            $role->add_cap( 'update_shared_groups' );
            $role->add_cap( 'view_any_groups' );
            $role->add_cap( 'assign_any_groups' );
            $role->add_cap( 'update_any_groups' );
            $role->add_cap( 'delete_any_groups' );

            /* Add WP-Admin caps for groups */
            $role->add_cap( 'edit_group' );
            $role->add_cap( 'read_group' );
            $role->add_cap( 'delete_group' );
            $role->add_cap( 'delete_others_groups' );
            $role->add_cap( 'delete_groups' );
            $role->add_cap( 'edit_groups' );
            $role->add_cap( 'edit_others_groups' );
            $role->add_cap( 'publish_groups' );
            $role->add_cap( 'read_private_groups' );

            /* Add Prayer permissions*/
            $role->add_cap( 'edit_prayer' );
            $role->add_cap( 'read_prayer' );
            $role->add_cap( 'delete_prayer' );
            $role->add_cap( 'delete_others_prayers' );
            $role->add_cap( 'delete_prayers' );
            $role->add_cap( 'edit_prayers' );
            $role->add_cap( 'edit_others_prayers' );
            $role->add_cap( 'publish_prayers' );
            $role->add_cap( 'read_private_prayers' );

            /* Add Progress permissions */
            $role->add_cap( 'edit_progress' );
            $role->add_cap( 'read_progress' );
            $role->add_cap( 'delete_progress' );
            $role->add_cap( 'delete_others_progresss' );
            $role->add_cap( 'delete_progresss' );
            $role->add_cap( 'edit_progresss' );
            $role->add_cap( 'edit_others_progresss' );
            $role->add_cap( 'publish_progresss' );
            $role->add_cap( 'read_private_progresss' );

            /* Add Location permissions */
            $role->add_cap( 'edit_location' );
            $role->add_cap( 'read_location' );
            $role->add_cap( 'delete_location' );
            $role->add_cap( 'delete_others_locations' );
            $role->add_cap( 'delete_locations' );
            $role->add_cap( 'edit_locations' );
            $role->add_cap( 'edit_others_locations' );
            $role->add_cap( 'publish_locations' );
            $role->add_cap( 'read_private_locations' );

            /* Add Asset permissions */
            $role->add_cap( 'edit_assetmapping' );
            $role->add_cap( 'read_assetmapping' );
            $role->add_cap( 'delete_assetmapping' );
            $role->add_cap( 'delete_others_assetmappings' );
            $role->add_cap( 'delete_assetmappings' );
            $role->add_cap( 'edit_assetmappings' );
            $role->add_cap( 'edit_others_assetmappings' );
            $role->add_cap( 'publish_assetmappings' );
            $role->add_cap( 'read_private_assetmappings' );

            /* Add Resource permissions */
            $role->add_cap( 'edit_resource' );
            $role->add_cap( 'read_resource' );
            $role->add_cap( 'delete_resource' );
            $role->add_cap( 'delete_others_resources' );
            $role->add_cap( 'delete_resources' );
            $role->add_cap( 'edit_resources' );
            $role->add_cap( 'edit_others_resources' );
            $role->add_cap( 'publish_resources' );
            $role->add_cap( 'read_private_resources' );

            /* Add People Group permissions */
            $role->add_cap( 'edit_peoplegroup' );
            $role->add_cap( 'read_peoplegroup' );
            $role->add_cap( 'delete_peoplegroup' );
            $role->add_cap( 'delete_others_peoplegroups' );
            $role->add_cap( 'delete_peoplegroups' );
            $role->add_cap( 'edit_peoplegroups' );
            $role->add_cap( 'edit_others_peoplegroups' );
            $role->add_cap( 'publish_peoplegroups' );
            $role->add_cap( 'read_private_peoplegroups' );
        }

        update_option( 'dt_roles_number', self::$target_roles_version_number );
        return "complete";
    }

    /*
    * Reset Roles on deactivation
    */
    public function reset_roles()
    {
        delete_option( 'run_once' );

        remove_role( 'dispatcher' );
        remove_role( 'multiplier' );
        remove_role( 'multiplier_leader' );
        remove_role( 'marketer' );
        remove_role( 'prayer_supporter' );
        remove_role( 'project_supporter' );

        add_role(
            'subscriber', __( 'Subscriber' ),
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
            'editor', __( 'Editor' ),
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
            'author', __( 'Author' ),
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
            'contributor', __( 'Contributor' ),
            [
                'delete_posts' => true,
                'edit_posts'   => true,
                'read'         => true,
            ]
        );

        add_filter(
            'pre_option_default_role', function( $default_role ) {
                return 'subscriber';
            }
        );

        delete_option( 'dt_roles_number' );
    }
}
