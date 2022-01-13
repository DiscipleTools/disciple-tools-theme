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
    private function __construct() {
    }

    /**
     * Default descriptions of capabilities for capabilities that may not be registered using the filter
     * @return array
     */
    private function default_descriptions() {
        return [
            'access_contacts'         => __( 'In Disciple.Tools, allows user to access contacts.', 'disciple-tools' ),
            'access_disciple_tools'   => __( 'In Disicple.Tools, allows user to login to Disciple.Tools.', 'disciple-tools' ),
            'access_groups'           => __( 'In Disciple.Tools, allows user to access groups.', 'disciple-tools' ),
            'access_peoplegroups'     => __( 'In WordPress, allows user to access People Groups to add or edit imported people groups.', 'disciple-tools' ),
            'access_specific_sources' => __( '', 'disciple-tools' ),
            'activate_plugins'        => __( 'In WordPress, allows user to activate installed plugins.', 'disciple-tools' ),
            'add_users'               => __( 'In WordPress, allows user to change the role on an existing user.', 'disciple-tools' ),
            'assign_any_contacts'     => __( 'In Disciple.Tools, allows user to assign any contact to another user.', 'disciple-tools' ),
            'create_contacts'         => __( 'In Disciple.Tools, allows user to create new contacts.', 'disciple-tools' ),
            'create_groups'           => __( 'In Disciple.Tools, allows user to create new groups.', 'disciple-tools' ),
            'create_roles'            => __( 'In Disciple.Tools, allows user to create new roles.', 'disciple-tools' ),
            'create_users'            => __( 'In WordPress, allows user to create new users.', 'disciple-tools' ),
            'delete_others_pages'     => __( '', 'disciple-tools' ),
            'delete_others_posts'     => __( '', 'disciple-tools' ),
            'delete_pages'            => __( '', 'disciple-tools' ),
            'delete_plugins'          => __( '', 'disciple-tools' ),
            'delete_posts'            => __( '', 'disciple-tools' ),
            'delete_private_pages'    => __( '', 'disciple-tools' ),
            'delete_private_posts'    => __( '', 'disciple-tools' ),
            'delete_published_pages'  => __( '', 'disciple-tools' ),
            'delete_published_posts'  => __( '', 'disciple-tools' ),
            'delete_roles'            => __( 'In Disciple.Tools, allows user to remove roles', 'disciple-tools' ),
            'delete_themes'           => __( '', 'disciple-tools' ),
            'delete_users'            => __( '', 'disciple-tools' ),
            'dt_all_access_contacts'  => __( 'In Disciple.Tools, allows user to view all contacts regardless of who they are assigned to', 'disciple-tools' ),
            'dt_all_admin_contacts'   => __( 'In Disciple.Tools, allows user to edit all contacts regardless of who they are assigned to', 'disciple-tools' ),
            'dt_all_admin_groups'     => __( 'In Disciple.Tools, allows user to edit all contacts regardless of who they are assigned to', 'disciple-tools' ),
            'dt_list_users'           => __( 'In Disciple.Tools, allows user to see list of users', 'disciple-tools' ),
            'edit_dashboard'          => __( '', 'disciple-tools' ),
            'edit_files'              => __( '', 'disciple-tools' ),
            'edit_others_pages'       => __( '', 'disciple-tools' ),
            'edit_others_posts'       => __( '', 'disciple-tools' ),
            'edit_pages'              => __( '', 'disciple-tools' ),
            'edit_peoplegroups'       => __( 'In Wordpress, allows user to edit People Groups', 'disciple-tools' ),
            'edit_plugins'            => __( '', 'disciple-tools' ),
            'edit_posts'              => __( '', 'disciple-tools' ),
            'edit_private_pages'      => __( '', 'disciple-tools' ),
            'edit_private_posts'      => __( '', 'disciple-tools' ),
            'edit_published_pages'    => __( '', 'disciple-tools' ),
            'edit_published_posts'    => __( '', 'disciple-tools' ),
            'edit_roles'              => __( 'In Disciple.Tools,  allows user to make changes to roles', 'disciple-tools' ),
            'edit_theme_options'      => __( '', 'disciple-tools' ),
            'edit_themes'             => __( '', 'disciple-tools' ),
            'edit_users'              => __( '', 'disciple-tools' ),
            'export'                  => __( '', 'disciple-tools' ),
            'import'                  => __( '', 'disciple-tools' ),
            'install_plugins'         => __( '', 'disciple-tools' ),
            'install_themes'          => __( '', 'disciple-tools' ),
            'level_0'                 => __( '', 'disciple-tools' ),
            'level_1'                 => __( '', 'disciple-tools' ),
            'level_10'                => __( '', 'disciple-tools' ),
            'level_2'                 => __( '', 'disciple-tools' ),
            'level_3'                 => __( '', 'disciple-tools' ),
            'level_4'                 => __( '', 'disciple-tools' ),
            'level_5'                 => __( '', 'disciple-tools' ),
            'level_6'                 => __( '', 'disciple-tools' ),
            'level_7'                 => __( '', 'disciple-tools' ),
            'level_8'                 => __( '', 'disciple-tools' ),
            'level_9'                 => __( '', 'disciple-tools' ),
            'list_peoplegroups'       => __( '', 'disciple-tools' ),
            'list_roles'              => __( 'In Disciple.Tools, allows user to see a list of the roles', 'disciple-tools' ),
            'list_users'              => __( '', 'disciple-tools' ),
            'manage_categories'       => __( '', 'disciple-tools' ),
            'manage_dt'               => __( 'In Disciple.Tools, allows user to administer Disciple.Tools application', 'disciple-tools' ),
            'manage_links'            => __( '', 'disciple-tools' ),
            'manage_options'          => __( '', 'disciple-tools' ),
            'moderate_comments'       => __( '', 'disciple-tools' ),
            'promote_users'           => __( '', 'disciple-tools' ),
            'publish_pages'           => __( '', 'disciple-tools' ),
            'publish_posts'           => __( '', 'disciple-tools' ),
            'read'                    => __( '', 'disciple-tools' ),
            'read_location'           => __( 'In Disciple.Tools, allows user to read a users location', 'disciple-tools' ),
            'read_private_pages'      => __( '', 'disciple-tools' ),
            'read_private_posts'      => __( '', 'disciple-tools' ),
            'remove_users'            => __( '', 'disciple-tools' ),
            'restrict_content'        => __( '', 'disciple-tools' ),
            'switch_themes'           => __( '', 'disciple-tools' ),
            'unfiltered_html'         => __( '', 'disciple-tools' ),
            'unfiltered_upload'       => __( '', 'disciple-tools' ),
            'update_any_groups'       => __( '', 'disciple-tools' ),
            'update_core'             => __( '', 'disciple-tools' ),
            'update_plugins'          => __( '', 'disciple-tools' ),
            'update_themes'           => __( '', 'disciple-tools' ),
            'upload_files'            => __( '', 'disciple-tools' ),
            'view_any_groups'         => __( '', 'disciple-tools' ),
            'view_project_metrics'    => __( '', 'disciple-tools' )
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
        $source = $options[ 'source' ];
        $name = isset( $options[ 'name' ] ) ? $options[ 'name' ] : $this->name_from_slug( $slug );
        $description = isset( $options[ 'description' ] ) ? $options[ 'description' ] : '';

        $capability = $this->get_capability( $slug );

        if (!$this->get_capability( $slug )) {
            //Handle the case that we've already registered this capability
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

        if (isset( $this->capabilities[ $capability ] )) {
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
        if (!count( $capabilities )) {
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

        if (is_null( $instance )) {
            $instance = new Disciple_Tools_Capability_Factory();
            $instance->setup_capabilities();
        }

        return $instance;
    }


    private function name_from_slug( $capability ) {
        $string = str_replace( "_", ' ', $capability );

        /* Words that should be entirely lower-case */
        $articles_conjunctions_prepositions = [
            'a', 'an', 'the',
            'and', 'but', 'or', 'nor',
            'if', 'then', 'else', 'when',
            'at', 'by', 'from', 'for', 'in',
            'off', 'on', 'out', 'over', 'to', 'into', 'with'
        ];
        /* Words that should be entirely upper-case (need to be lower-case in this list!) */
        $acronyms_and_such = [
            'asap', 'unhcr', 'wpse', 'wtf'
        ];
        /* split title string into array of words */
        $words = explode( ' ', mb_strtolower( $string ) );
        /* iterate over words */
        foreach ($words as $position => $word) {
            /* re-capitalize acronyms */
            if (in_array( $word, $acronyms_and_such )) {
                $words[ $position ] = mb_strtoupper( $word );
                /* capitalize first letter of all other words, if... */
            } elseif (
                /* ...first word of the title string... */
                0 === $position ||
                /* ...or not in above lower-case list*/
                !in_array( $word, $articles_conjunctions_prepositions )
            ) {
                $words[ $position ] = ucwords( $word );
            }
        }
        /* re-combine word array */
        $string = implode( ' ', $words );
        /* return title string in title case */
        return $string;
    }


    public function setup_capabilities() {
        $capabilities = [];

        $dt_capabilities = array_merge(
            dt_multi_role_get_role_capabilities(),
            dt_multi_role_get_plugin_capabilities()
        );

        foreach ($dt_capabilities as $capability) {
            $capabilities[ $capability ] = [
                'source'      => __( 'Disciple Tools', 'disciple_tools' ),
                'description' => isset( $this->default_descriptions()[ $capability ] ) ? $this->default_descriptions()[ $capability ] : ''
            ];
        }

        $wordpress_capabilities = dt_multi_role_get_wp_capabilities();
        foreach ($wordpress_capabilities as $capability) {
            $capabilities[ $capability ] = [
                'source'      => __( 'WordPress', 'disciple_tools' ),
                'description' => isset( $this->default_descriptions()[ $capability ] ) ? $this->default_descriptions()[ $capability ] : ''
            ];
        }

        $capabilities = apply_filters( 'dt_capabilities', $capabilities );


        foreach ($capabilities as $capability => $options) {
            $this->add_capability( $capability, $options );
        }
    }
}
