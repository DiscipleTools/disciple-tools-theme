<?php

/**
 * Disciple_Tools_Dashboard Class
 *
 * @class   Disciple_Tools_Dashboard
 * @version 0.1.0
 * @since   0.1.0
 * @package Disciple.Tools
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
        if ( is_admin() ) {
            /* Add dashboard widgets */
            add_action( 'wp_dashboard_setup', [ $this, 'add_widgets' ] );

            add_action( 'wp_dashboard_setup', [ $this, 'dt_dashboard_tile' ] );

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
    public function add_widgets() {
        add_filter( 'dashboard_recent_posts_query_args', [ $this, 'add_page_to_dashboard_activity' ] );
    }


    /**
     * Remove default dashboard widgets
     *
     * @since  0.1.0
     * @access public
     */
    public function remove_dashboard_meta() {

        remove_meta_box( 'dashboard_incoming_links', 'dashboard', 'normal' );
        remove_meta_box( 'dashboard_plugins', 'dashboard', 'normal' );
        remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
        remove_meta_box( 'dashboard_secondary', 'dashboard', 'normal' );
        remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
        remove_meta_box( 'dashboard_recent_drafts', 'dashboard', 'side' );
        remove_meta_box( 'dashboard_right_now', 'dashboard', 'normal' );

        remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
        remove_meta_box( 'dashboard_activity', 'dashboard', 'normal' );

        // Remove_meta_box('dashboard_right_now', 'dashboard', 'core');    // Right Now Widget
        remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'core' ); // Comments Widget
        remove_meta_box( 'dashboard_incoming_links', 'dashboard', 'core' );  // Incoming Links Widget
        remove_meta_box( 'dashboard_plugins', 'dashboard', 'core' );         // Plugins Widget

        // Remove_meta_box('dashboard_quick_press', 'dashboard', 'core');  // Quick Press Widget
        remove_meta_box( 'dashboard_recent_drafts', 'dashboard', 'core' );   // Recent Drafts Widget
        remove_meta_box( 'dashboard_primary', 'dashboard', 'core' );
        remove_meta_box( 'dashboard_secondary', 'dashboard', 'core' );
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
    public function add_page_to_dashboard_activity( $query_args ) {
        if ( is_array( $query_args['post_type'] ) ) {
            //Set your post type
            $query_args['post_type'][] = 'contacts';
        } else {
            $temp = [ $query_args['post_type'], 'contacts' ];
            $query_args['post_type'] = $temp;
        }

        return $query_args;
    }


    public function dt_dashboard_tile(){
        wp_add_dashboard_widget('dt_setup_wizard', 'Disciple.Tools Setup Wizard', function (){

            $setup_options = get_option( "dt_setup_options", [] );
            $default = [
                "base_email" => [
                    "label" => "Base User",
                    "complete" => !empty( $setup_options["base_email"] ),
                    "link" => admin_url( "admin.php?page=dt_options&tab=general" ),
                    "description" => "Default Assigned to for new contacts"
                ],
            ];

            $dt_setup_wizard_items = apply_filters( 'dt_setup_wizard_items', $default, $setup_options );

            $completed = 0;
            foreach ( $dt_setup_wizard_items as $item_key => $item_value ){
                if ( $item_value["complete"] === true ){
                    $completed ++;
                }
            }

            ?><p>Completed <?php echo esc_html( $completed ); ?> of <?php echo esc_html( sizeof( $dt_setup_wizard_items ) ); ?> tasks</p>

            <table class="widefat striped">
                <thead>
                    <tr>
                        <th></th>
                        <th>Name</th>
                        <th>Link</th>
                        <th>Complete</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                foreach ( $dt_setup_wizard_items as $item_key => $item_value ) :?>
                    <tr>
                        <td><?php echo esc_html( array_search( $item_key, array_keys( $dt_setup_wizard_items ) ) +1 ); ?>.</td>
                        <td><?php echo esc_html( $item_value["label"] ); ?></td>
                        <td>Update <a href="<?php echo esc_html( $item_value["link"] ); ?>">here</a></td>
                        <td>
                            <?php if ( $item_value["complete"] ) :?>
                                <img class="dt-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/verified.svg' ) ?>"/>
                            <?php elseif ( empty( $item_value["hide_mark_done"] ) ) : ?>
                                <button>Mark Done</button></td>
                            <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php
        });
    }
}

/**
 * @todo move to mapping file
 */
add_filter( 'dt_setup_wizard_items', function ( $items, $setup_options ){
    $mapbox_key = DT_Mapbox_API::get_key();
    $mapbox_upgraded = DT_Mapbox_API::are_records_and_users_upgraded_with_mapbox();
    $items["mapbox_key"] = [
        "label" => "Upgrade Mapping",
        "description" => "Better results when search locations and better mapping",
        "link" => admin_url( "admin.php?page=dt_mapping_module&tab=geocoding" ),
        "complete" => $mapbox_key ? true : false
    ];
    $items["upgraded_mapbox_records"] = [
        "label" => "Upgrade Users and Record Mapping",
        "description" => " Please upgrade Users, Contacts and Groups for the Locations to show up on maps and charts.",
        "link" => admin_url( "admin.php?page=dt_mapping_module&tab=geocoding" ),
        "complete" => $mapbox_upgraded,
        "hide_mark_done" => true
    ];
    return $items;
}, 10, 2);
