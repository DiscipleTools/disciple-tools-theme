<?php
/**
 * Custom endpoints file
 *
 * @package  Disciple_Tools
 * @author   Chasm.Solutions & Kingdom.Training
 * @since    0.1.0
 */
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

/**
 * Class Disciple_Tools_Assetmapping_Endpoints
 */
class Disciple_Tools_Assetmapping_Endpoints {

    /**
     * Disciple_Tools_Admin_Menus The single instance of Disciple_Tools_Admin_Menus.
     * @var     object
     * @access  private
     * @since   0.1.0
     */
    private static $_instance = null;

    /**
     * Main Disciple_Tools_Assetmapping_Endpoints Instance
     *
     * Ensures only one instance of Disciple_Tools_Assetmapping_Endpoints is loaded or can be loaded.
     *
     * @since 0.1
     * @static
     * @return Disciple_Tools_Assetmapping_Endpoints instance
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    /**
     * Constructor function.
     * @access  public
     * @since   0.1.0
     */
    public function __construct() {

    } // End __construct()

}
