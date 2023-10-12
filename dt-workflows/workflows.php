<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

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
     * @return Disciple_Tools_Workflows instance
     * @since  1.0.0
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
        include( 'activity-log-delete-old-viewed-actions.php' );
        include( 'error-log-dispatch-email.php' );
        include( 'error-log-retention-enforcer.php' );
        include( 'workflows-execution-handler.php' );
        include( 'workflows-triggers.php' );
        include( 'workflows-defaults.php' );
        new Disciple_Tools_Workflows_Defaults();
    }
}
