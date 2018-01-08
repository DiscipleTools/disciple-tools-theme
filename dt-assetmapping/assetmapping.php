<?php
/**
 * Contains create, update and delete functions for assets, wrapping access to
 * the database
 *
 * @package  Disciple_Tools
 * @author   Chasm.Solutions & Kingdom.Training
 * @since    0.1.0
 */
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

/**
 * Class Disciple_Tools_Assets
 */
class Disciple_Tools_Assetmapping {

    /**
     * Disciple_Tools_Assetmapping The single instance of Disciple_Tools_Assetmapping.
     * @var     object
     * @access  private
     * @since   0.1
     */
    private static $_instance = null;

    /**
     * Main Disciple_Tools_Assetmapping Instance
     *
     * Ensures only one instance of Disciple_Tools_Assetmapping is loaded or can be loaded.
     *
     * @since 0.1
     * @static
     * @return Disciple_Tools_Assetmapping instance
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
     * @since   0.1
     */
    public function __construct() {

    } // End __construct()

}
