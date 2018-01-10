<?php

/**
 * Disciple_Tools_Dashboard Class
 *
 * @class   Disciple_Tools_Dashboard
 * @version 0.1.0
 * @since   0.1.0
 * @package Disciple_Tools
 *
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * Class Disciple_Tools_Dashboard
 */
final class Disciple_Tools_Dashboard
{

    /**
     * Disciple_Tools_Dashboard The single instance of Disciple_Tools_Dashboard.
     *
     * @var    object
     * @access private
     * @since  0.1.0
     */
    private static $_instance = null;

    /**
     * Main Disciple_Tools_Dashboard Instance
     * Ensures only one instance of Disciple_Tools_Dashboard is loaded or can be loaded.
     *
     * @since  0.1.0
     * @static
     * @return Disciple_Tools_Dashboard
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
    public function __construct()
    {
        if ( is_admin() ) {
            /* Add dashboard widgets */
            add_action( 'wp_dashboard_setup', [ $this, 'add_widgets' ] );

            /* Remove Dashboard defaults */
            add_action( 'admin_init', [ $this, 'remove_dashboard_meta' ] );
            remove_action( 'welcome_panel', 'wp_welcome_panel' );
        }
    } // End __construct()

    /**
     * Main action hooks
     *
     * @since  0.1.0
     * @access public
     */
    public function add_widgets()
    {
        add_meta_box( 'funnel_stats_widget', 'Funnel Stats', [ $this, 'funnel_stats_widget' ], 'dashboard', 'side', 'high' );
        add_filter( 'dashboard_recent_posts_query_args', [ $this, 'add_page_to_dashboard_activity' ] );
    }

    /**
     * Movement funnel path dashboard widget
     *
     * @since  0.1.0
     * @access public
     */
    public function funnel_stats_widget()
    {
        echo '<div id="chart"></div><!-- Container for charts -->';
    }

    protected function enqueue_funnel_chart() {

    }

    /**
     * Remove default dashboard widgets
     *
     * @since  0.1.0
     * @access public
     */
    public function remove_dashboard_meta()
    {

        remove_meta_box( 'dashboard_incoming_links', 'dashboard', 'normal' );
        remove_meta_box( 'dashboard_plugins', 'dashboard', 'normal' );
        remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
        remove_meta_box( 'dashboard_secondary', 'dashboard', 'normal' );
        remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
        remove_meta_box( 'dashboard_recent_drafts', 'dashboard', 'side' );
        remove_meta_box( 'dashboard_right_now', 'dashboard', 'normal' );

        //remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
        //remove_meta_box( 'dashboard_activity', 'dashboard', 'normal');

        // Remove_meta_box('dashboard_right_now', 'dashboard', 'core');    // Right Now Widget
        remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'core' ); // Comments Widget
        remove_meta_box( 'dashboard_incoming_links', 'dashboard', 'core' );  // Incoming Links Widget
        remove_meta_box( 'dashboard_plugins', 'dashboard', 'core' );         // Plugins Widget

        // Remove_meta_box('dashboard_quick_press', 'dashboard', 'core');  // Quick Press Widget
        remove_meta_box( 'dashboard_recent_drafts', 'dashboard', 'core' );   // Recent Drafts Widget
        remove_meta_box( 'dashboard_primary', 'dashboard', 'core' );         //
        remove_meta_box( 'dashboard_secondary', 'dashboard', 'core' );       //

        // Removing plugin dashboard boxes
        remove_meta_box( 'yoast_db_widget', 'dashboard', 'normal' );         // Yoast's SEO Plugin Widget
    }

    /**
     * Add custom post types to Activity feed on dashboard
     * @source https://gist.github.com/Mte90/708e54b21b1f7372b48a
     *
     * @since  0.1.0
     * @access public
     */
    public function add_page_to_dashboard_activity( $query_args )
    {
        if ( is_array( $query_args['post_type'] ) ) {
            //Set your post type
            $query_args['post_type'][] = 'contacts';
        } else {
            $temp = [ $query_args['post_type'], 'contacts' ];
            $query_args['post_type'] = $temp;
        }

        return $query_args;
    }

}
