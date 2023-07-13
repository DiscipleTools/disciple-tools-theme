<?php

/**
 * WordPress' `WP_Roles` and the global `$wp_capabilities` array don't really cut it.  So, this is a
 * singleton factory class for storing capability objects and information that we need.
 *
 * @package    Members
 * @subpackage Includes
 * @author     Justin Tadlock <justin@justintadlock.com>
 * @copyright  Copyright (c) 2009 - 2016, Justin Tadlock
 * @link       http://themehybrid.com/plugins/members
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

/**
 * Role factory class.
 *
 * @since  0.1.0
 * @access public
 */
final class Disciple_Tools_Capability_Factory {

    /**
     * Array of capabilities added.
     *
     * @since  0.1.0
     * @access public
     * @var    array
     */
    public $capabilities = [];

    /**
     * Private constructor method to prevent a new instance of the object.
     *
     * @return void
     * @since  0.1.0
     * @access public
     */
    public function __construct() {
        add_action( 'init', [ $this, 'setup_capabilities' ] );
    }

    /**
     * Default descriptions of capabilities for capabilities that may not be registered using the filter
     * @return array
     */
    private function default_descriptions() {
        return [
            'access_contacts'         => __( 'In Disciple.Tools, allows user to access contacts.', 'disciple_tools' ),
            'access_disciple_tools'   => __( 'In Disicple.Tools, allows user to login to Disciple.Tools.', 'disciple_tools' ),
            'access_groups'           => __( 'In Disciple.Tools, allows user to access groups.', 'disciple_tools' ),
            'access_peoplegroups'     => __( 'In WordPress, allows user to access People Groups to add or edit imported people groups.', 'disciple_tools' ),
            'access_specific_sources' => '',
            'activate_plugins'        => __( 'In WordPress, allows user to activate installed plugins.', 'disciple_tools' ),
            'add_users'               => __( 'In WordPress, allows user to change the role on an existing user.', 'disciple_tools' ),
            'assign_any_contacts'     => __( 'In Disciple.Tools, allows user to assign any contact to another user.', 'disciple_tools' ),
            'create_contacts'         => __( 'In Disciple.Tools, allows user to create new contacts.', 'disciple_tools' ),
            'create_groups'           => __( 'In Disciple.Tools, allows user to create new groups.', 'disciple_tools' ),
            'create_roles'            => __( 'In Disciple.Tools, allows user to create new roles.', 'disciple_tools' ),
            'create_users'            => __( 'In WordPress, allows user to create new users.', 'disciple_tools' ),
            'delete_others_pages'     => '',
            'delete_others_posts'     => '',
            'delete_pages'            => '',
            'delete_plugins'          => '',
            'delete_posts'            => '',
            'delete_private_pages'    => '',
            'delete_private_posts'    => '',
            'delete_published_pages'  => '',
            'delete_published_posts'  => '',
            'delete_roles'            => __( 'In Disciple.Tools, allows user to remove roles', 'disciple_tools' ),
            'delete_themes'           => '',
            'delete_users'            => '',
            'dt_all_access_contacts'  => __( 'In Disciple.Tools, allows user to view all contacts regardless of who they are assigned to', 'disciple_tools' ),
            'dt_all_admin_contacts'   => __( 'In Disciple.Tools, allows user to edit all contacts regardless of who they are assigned to', 'disciple_tools' ),
            'dt_all_admin_groups'     => __( 'In Disciple.Tools, allows user to edit all contacts regardless of who they are assigned to', 'disciple_tools' ),
            'dt_list_users'           => __( 'In Disciple.Tools, allows user to see list of users', 'disciple_tools' ),
            'edit_dashboard'          => '',
            'edit_files'              => '',
            'edit_others_pages'       => '',
            'edit_others_posts'       => '',
            'edit_pages'              => '',
            'edit_peoplegroups'       => __( 'In Wordpress, allows user to edit People Groups', 'disciple_tools' ),
            'edit_plugins'            => '',
            'edit_posts'              => '',
            'edit_private_pages'      => '',
            'edit_private_posts'      => '',
            'edit_published_pages'    => '',
            'edit_published_posts'    => '',
            'edit_roles'              => __( 'In Disciple.Tools,  allows user to make changes to roles', 'disciple_tools' ),
            'edit_theme_options'      => '',
            'edit_themes'             => '',
            'edit_users'              => '',
            'export'                  => '',
            'import'                  => '',
            'install_plugins'         => '',
            'install_themes'          => '',
            'level_0'                 => '',
            'level_1'                 => '',
            'level_10'                => '',
            'level_2'                 => '',
            'level_3'                 => '',
            'level_4'                 => '',
            'level_5'                 => '',
            'level_6'                 => '',
            'level_7'                 => '',
            'level_8'                 => '',
            'level_9'                 => '',
            'list_peoplegroups'       => '',
            'list_roles'              => __( 'In Disciple.Tools, allows user to see a list of the roles', 'disciple_tools' ),
            'list_users'              => '',
            'manage_categories'       => '',
            'manage_dt'               => __( 'In Disciple.Tools, allows user to administer Disciple.Tools application', 'disciple_tools' ),
            'manage_links'            => '',
            'manage_options'          => '',
            'moderate_comments'       => '',
            'promote_users'           => '',
            'publish_pages'           => '',
            'publish_posts'           => '',
            'read'                    => '',
            'read_location'           => __( 'In Disciple.Tools, allows user to read a users location', 'disciple_tools' ),
            'read_private_pages'      => '',
            'read_private_posts'      => '',
            'remove_users'            => '',
            'restrict_content'        => '',
            'switch_themes'           => '',
            'unfiltered_html'         => '',
            'unfiltered_upload'       => '',
            'update_any_groups'       => '',
            'update_core'             => '',
            'update_plugins'          => '',
            'update_themes'           => '',
            'upload_files'            => '',
            'view_any_groups'         => '',
            'view_project_metrics'    => ''
        ];
    }

    private function restricted_capabilities() {
        return [
            'level_0',
            'level_1',
            'level_10',
            'level_2',
            'level_3',
            'level_4',
            'level_5',
            'level_6',
            'level_7',
            'level_8',
            'level_9',
        ];
    }

    /**
     * Adds a capability object.
     *
     * @param string $slug
     * @param array $options
     * @since  0.1.0
     * @access public
     */
    public function add_capability( $slug, $options ) {
        $source = $options['source'];
        if ( isset( $options['name'] ) ){
            $name = $options['name'];
        } elseif ( isset( $options['label'] ) ){
            $name = $options['label'];
        } else {
            $name = dt_label_from_slug( $slug );
        }
        $description = isset( $options['description'] ) ? $options['description'] : '';

        $capability = $this->get_capability( $slug );

        if ( !$capability ) {
            $capability = new Disciple_Tools_Capability(
                $slug,
                $source,
                $name,
                $description
            );
        } else {
            $capability->source = $source;
            $capability->slug = $slug;
            $capability->description = $description;
        }
        $this->capabilities[ $slug ] = $capability;

        ksort( $this->capabilities );
    }

    /**
     * Returns a single capability object.
     *
     * @param string $capability
     * @return object|bool
     * @since  0.1.0
     * @access public
     */
    public function get_capability( $capability ) {
        return isset( $this->capabilities[ $capability ] ) ? $this->capabilities[ $capability ] : false;
    }


    /**
     * Removes a capability object (doesn't remove from DB).
     *
     * @param string $capability
     * @return void
     * @since  1.1.0
     * @access public
     */
    public function remove_capability( $capability ) {

        if ( isset( $this->capabilities[ $capability ] ) ) {
            unset( $this->capabilities[ $capability ] );
        }
    }

    /**
     * Returns an array of capability objects.
     *
     * @return array
     * @since  0.1.0
     * @access public
     */
    public function get_capabilities( $capabilities = [] ) {
        if ( !count( $capabilities ) ) {
            return $this->capabilities;
        }

        return array_filter( $this->capabilities, function ( $capability ) use ( $capabilities ) {
            return in_array( $capability->slug, $capabilities );
        } );
    }

    /**
     * Returns the instance.
     *
     * @return object
     * @since  3.0.0
     * @access public
     */
    public static function get_instance() {

        static $instance = null;

        if ( is_null( $instance ) ) {
            $instance = new Disciple_Tools_Capability_Factory();
        }

        return $instance;
    }

    /**
     * Register all capabilities from multi-role, the dt_capabilities filter
     */
    public function setup_capabilities() {
        $capabilities = [];

        $dt_capabilities = array_merge(
            dt_multi_role_get_role_capabilities(),
            dt_multi_role_get_plugin_capabilities()
        );


        foreach ( $dt_capabilities as $capability ) {
            $capabilities[ $capability ] = [
                'source'      => __( 'Disciple Tools', 'disciple_tools' ),
                'description' => isset( $this->default_descriptions()[ $capability ] ) ? $this->default_descriptions()[ $capability ] : ''
            ];
        }

        $wordpress_capabilities = dt_multi_role_get_wp_capabilities();
        foreach ( $wordpress_capabilities as $key => $capability ) {
            $capabilities[ $capability ] = [
                'source'      => __( 'WordPress', 'disciple_tools' ),
                'description' => isset( $this->default_descriptions()[ $capability ] ) ? $this->default_descriptions()[ $capability ] : ''
            ];
        }

        //Filter our restricted capabilities
        $capabilities = array_filter($capabilities, function( $key ) {
            return !in_array( $key, $this->restricted_capabilities() );
        }, ARRAY_FILTER_USE_KEY);

        $capabilities = apply_filters( 'dt_capabilities', $capabilities );

        foreach ( $capabilities as $capability => $options ) {
            //There are some random capabilities registered that are just numbers?
            if ( !is_numeric( $capability ) ) {
                $this->add_capability( $capability, $options );
            }
        }
    }
}
