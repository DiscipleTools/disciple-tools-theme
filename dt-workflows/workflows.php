<?php

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Class Disciple_Tools_Workflows
 *
 * @since  1.0.0
 */
class Disciple_Tools_Workflows {

    /**
     * Disciple_Tools_Workflows The single instance of Disciple_Tools_Workflows.
     *
     * @var    object
     * @access private
     * @since  1.0.0
     */
    private static $_instance = null;

    /**
     * Main Disciple_Tools_Workflows Instance
     *
     * Ensures only one instance of Disciple_Tools_Workflows is loaded or can be loaded.
     *
     * @since  1.0.0
     * @return Disciple_Tools_Workflows instance
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Disciple_Tools_Workflows constructor.
     */
    public function __construct() {
        include( 'update-required.php' );
        new Disciple_Tools_Update_Needed();
        new Disciple_Tools_Update_Needed_Async();
        include( 'tasks.php' );

    }
}
