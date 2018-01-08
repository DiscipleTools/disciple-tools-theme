<?php
/**
 * WordPress' `WP_Roles` and the global `$wp_roles` array don't really cut it.  So, this is a
 * singleton factory class for storing role objects and information that we need.
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
final class Disciple_Tools_Multi_Role_Factory {

    /**
     * Array of roles added.
     *
     * @since  0.1.0
     * @access public
     * @var    array
     */
    public $roles = [];

    /**
     * Array of editable roles.
     *
     * @since  0.1.0
     * @access public
     * @var    array
     */
    public $editable = [];

    /**
     * Array of uneditable roles.
     *
     * @since  0.1.0
     * @access public
     * @var    array
     */
    public $uneditable = [];

    /**
     * Array of core WordPress roles.
     *
     * @since  0.1.0
     * @access public
     * @var    array
     */
    public $wordpress = [];

    /**
     * Private constructor method to prevent a new instance of the object.
     *
     * @since  0.1.0
     * @access public
     * @return void
     */
    private function __construct() {}

    /**
     * Adds a role object.
     *
     * @since  0.1.0
     * @access public
     * @param  string  $role
     */
    public function add_role( $role ) {

        // If the role exists with WP but hasn't been added.
        if ( dt_multi_role_role_exists( $role ) ) {

            // Get the role object.
            $this->roles[ $role ] = new Disciple_Tools_Multi_Role( $role );

            // Check if role is editable.
            if ( $this->roles[ $role ]->is_editable ) {
                $this->editable[ $role ] = $this->roles[ $role ];
            } else {
                $this->uneditable[ $role ] = $this->roles[ $role ];
            }

            // Is WP role?
            if ( dt_multi_role_is_wordpress_role( $role ) ) {
                $this->wordpress[ $role ] = $this->roles[ $role ];
            }
        }
    }

    /**
     * Returns a single role object.
     *
     * @since  0.1.0
     * @access public
     * @param  string  $role
     * @return object|bool
     */
    public function get_role( $role ) {

        return isset( $this->roles[ $role ] ) ? $this->roles[ $role ] : false;
    }

    /**
     * Removes a role object (doesn't remove from DB).
     *
     * @since  1.1.0
     * @access public
     * @param  string  $role
     * @return void
     */
    public function remove_role( $role ) {

        if ( isset( $this->roles[ $role ] ) ) {
            unset( $this->roles[ $role ] );
        }
    }

    /**
     * Returns an array of role objects.
     *
     * @since  0.1.0
     * @access public
     * @return array
     */
    public function get_roles() {
        return $this->roles;
    }

    /**
     * Adds all the WP roles as role objects.  Rather than running this elsewhere, we're just
     * going to call this directly within the class when it is first constructed.
     *
     * @since  0.1.0
     * @access public
     * @return void
     */
    protected function setup_roles() {

        foreach ( $GLOBALS['wp_roles']->role_names as $role => $name ) {
            $this->add_role( $role );
        }
    }

    /**
     * Returns the instance.
     *
     * @since  3.0.0
     * @access public
     * @return object
     */
    public static function get_instance() {

        static $instance = null;

        if ( is_null( $instance ) ) {
            $instance = new Disciple_Tools_Multi_Role_Factory();
            $instance->setup_roles();
        }

        return $instance;
    }
}
