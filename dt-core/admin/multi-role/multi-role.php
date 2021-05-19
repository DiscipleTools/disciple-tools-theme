<?php

/**
 * Disciple_Tools_Multi_Roles
 *
 * @class   Disciple_Tools_Multi_Roles
 * @version 0.1.0
 * @since   0.1.0
 * @package Disciple.Tools
 *
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Class Disciple_Tools_Multi_Roles
 */
class Disciple_Tools_Multi_Roles {

    /**
     * Disciple_Tools_Admin_Menus The single instance of Disciple_Tools_Admin_Menus.
     *
     * @var    object
     * @access private
     * @since  0.1.0
     */
    private static $_instance = null;

    public $role_user_count = [];

    /**
     * Main Disciple_Tools_Multi_Roles Instance
     *
     * Ensures only one instance of Disciple_Tools_Multi_Roles is loaded or can be loaded.
     *
     * @since  0.1.0
     * @static
     * @return Disciple_Tools_Multi_Roles instance
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

        // Load class files.
        require_once( 'inc/class-role.php' );
        require_once( 'inc/class-role-factory.php' );

        // Load includes files.
        require_once( 'inc/functions.php' );
        require_once( 'inc/functions-capabilities.php' );
        require_once( 'inc/functions-options.php' );
        require_once( 'inc/functions-roles.php' );
        require_once( 'inc/functions-users.php' );

        if (is_admin()) {

            // General admin functions.
            require_once( 'functions-admin.php' );
            // require_once( 'functions-help.php'  );

            // Edit users.
            require_once( 'class-user-edit.php' );
        }

    } // End __construct()

}
