<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * Class Disciple_Tools_Notification_Hooks
 */
class Disciple_Tools_Notification_Hooks
{

    /**
     * Disciple_Tools_Notification_Hooks The single instance of Disciple_Tools_Notification_Hooks.
     *
     * @var    object
     * @access private
     * @since  0.1.0
     */
    private static $_instance = null;

    /**
     * Main Disciple_Tools_Notification_Hooks Instance
     * Ensures only one instance of Disciple_Tools_Notification_Hooks is loaded or can be loaded.
     *
     * @since  0.1.0
     * @static
     * @return Disciple_Tools_Notification_Hooks instance
     */
    public static function instance()
    {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    } // End instance()

    /**
     * Build hook classes
     */
    public function __construct()
    {
        // Load abstract class.
        include( 'hooks/abstract-class-hook-base.php' );

        // Load all our hooks.
        include( 'hooks/class-hook-comments.php' );
        include( 'hooks/class-hook-field-updates.php' );

        new Disciple_Tools_Notifications_Hook_Comments();
        new Disciple_Tools_Notifications_Hook_Field_Updates();
    }
}
