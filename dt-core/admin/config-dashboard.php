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

    public function dt_dashboard_tile() {
        if ( ! user_can( get_current_user_id(), 'manage_dt' ) ) {
            return;
        }
        // Check for a dismissed item button click
        if ( ! empty( $_POST['dismiss'] ) && ! empty( $_POST['setup_wizard_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['setup_wizard_nonce'] ) ), 'update_setup_wizard_items' ) ) {
            $item_key = sanitize_text_field( wp_unslash( $_POST['dismiss'] ) );
            $setup_options = get_option( 'dt_setup_wizard_options', [] );

            // Create the option and populate it if it doesn't exist and/or is empty
            if ( ! isset( $setup_options[$item_key] ) ) {
                $setup_options[$item_key] = [ 'dismissed' => true ];
            } else {
                $setup_options[$item_key]['dismissed'] = true;
            }
            update_option( 'dt_setup_wizard_options', $setup_options );
        }

        // Check for an un-dismissed item button click
        else if ( ! empty( $_POST['undismiss'] ) && ! empty( $_POST['setup_wizard_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['setup_wizard_nonce'] ) ), 'update_setup_wizard_items' ) ) {
            $item_key = sanitize_text_field( wp_unslash( $_POST['undismiss'] ) );
            $setup_options = get_option( 'dt_setup_wizard_options', [] );
            if ( ! isset( $setup_options[$item_key] ) ) {
                $setup_options[$item_key] = [ 'dismissed' => false ];
            } else {
                $setup_options[$item_key]['dismissed'] = false;
            }
            update_option( 'dt_setup_wizard_options', $setup_options );
        }

        function dt_show_news_widget() {
            include_once( ABSPATH . WPINC . '/feed.php' );

            if ( function_exists( 'fetch_feed' ) ) {
                $news_feed = fetch_feed( 'https://disciple.tools/news/feed/' );
            }

            if ( is_wp_error( $news_feed ) ) {
                ?>
                <p>
                    <i><?php echo esc_html( $news_feed->get_error_message() ); ?></i>
                </p>
                <?php
                return;
            }

            $news_feed->init();
            $news_feed->set_output_encoding( 'UTF-8' );
            $news_feed->handle_content_type();
            $news_feed->set_cache_duration( 86400 );
            $news_feed_items = $news_feed->get_items( 0, 1 );

            if ( empty( $news_feed_items ) || !is_array( $news_feed_items ) ) {
                ?>
                <p align="center">
                    <i><?php echo esc_html_e( 'no news found', 'disciple_tools' ); ?></i>
                </p>
                <?php
                return;
            }

            ?>
                <style>
                    .news-feed img {
                        max-width: 100%;
                        height: auto;
                    }
                    .news-feed-title {
                        color: #0073aa;
                        font-weight: bold;
                    }
                    .news-feed-heading {
                        display: flex;
                        justify-content: space-between;
                        margin-bottom: 5%;
                    }
                    #dashboard-widgets .news-feed h3 {
                        font-size: 1.3em;
                        font-weight: 600;
                    }
                    #dashboard-widgets .news-feed ul {
                        list-style: disc;
                        padding-inline-start: 40px;
                    }
                </style>
            <?php
            foreach ( $news_feed_items as $news ) {
                ?>
                <div class="news-feed">
                    <div class="news-feed-heading" >
                        <a href="<?php echo esc_attr( esc_url( $news->get_permalink() ) ); ?>" target="_blank" class="news-feed-title"><?php echo esc_html( $news->get_title() ); ?></a>
                        <i><?php echo esc_html( $news->get_date( 'm/d/Y' ) ); ?></i>
                    </div>
                    <?php echo wp_kses_post( $news->get_content() ); ?>
                    <div align="right"><small>powered by <a href="https://disciple.tools/news?source=dt_dashboard_news_feed" target="_blank">Disciple.Tools News</a></small></div>
                </div>
                <?php
            }
        }

        add_meta_box( 'dt_news_feed', esc_html__( 'Disciple.Tools News Feed', 'disciple_tools' ), 'dt_show_news_widget', 'dashboard', 'side', 'high' );

        wp_add_dashboard_widget( 'dt_setup_wizard', 'Disciple.Tools Setup Wizard', function (){

            $setup_options = get_option( "dt_setup_wizard_options", [] );
            $default = [
                "base_email" => [
                    "label" => "Base User",
                    "complete" => false,
                    "link" => admin_url( "admin.php?page=dt_options&tab=general" ),
                    "description" => "Default Assigned to for new contacts"
                ],
            ];

            $dt_setup_wizard_items = apply_filters( 'dt_setup_wizard_items', $default, $setup_options );

            $completed = 0;

            foreach ( $dt_setup_wizard_items as $item_key => $item_value ){
                // Treat dismissed items as complete
                if ( isset( $setup_options[$item_key]["dismissed"] ) && ! empty( $setup_options[$item_key]["dismissed"] ) ) {
                    $dt_setup_wizard_items[$item_key]['complete'] = true;
                }

                if ( $dt_setup_wizard_items[$item_key]['complete'] === true ) {
                    $completed ++;
                }
            }

            // Order array by complete status
            uasort( $dt_setup_wizard_items, function ( $a, $b ) {
                return $a['complete'] <=> $b['complete'];
            } );

            ?><p>Completed <?php echo esc_html( $completed ); ?> of <?php echo esc_html( sizeof( $dt_setup_wizard_items ) ); ?> tasks</p>
            <style>
                .wizard_chevron_open {
                    position: relative;
                    width: 7px;
                    height: 7px;
                    border-width: 0 2px 2px 0;
                    border-style: solid;
                    transform: rotate(45deg);
                    margin: auto;
                }
                .wizard_chevron_close {
                    position: relative;
                    width: 7px;
                    height: 7px;
                    border-width: 0 2px 2px 0;
                    border-style: solid;
                    transform: rotate(225deg);
                    margin: auto;
                }
                .toggle_chevron{
                    vertical-align: middle !important;
                    cursor:pointer;
                }
                .wizard_description{
                    position: relative;
                    height: 200px;
                }
            </style>
            <form method="POST">
                <?php wp_nonce_field( 'update_setup_wizard_items', 'setup_wizard_nonce' ); ?>
                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Name</th>
                            <th>Link</th>
                            <th>Complete</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $row_count = 0;
                    foreach ( $dt_setup_wizard_items as $item_key => $item_value ) : ?>
                        <tr>
                            <td><?php echo esc_html( array_search( $item_key, array_keys( $dt_setup_wizard_items ) ) +1 ); ?>.</td>
                            <td><?php echo esc_html( $item_value["label"] ); ?></td>
                            <td>Update <a href="<?php echo esc_html( $item_value["link"] ); ?>">here</a></td>
                            <td>
                                <?php
                                if ( $item_value['complete'] ) {
                                    ?>
                                <img class="dt-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/verified.svg' ) ?>"/>
                                    <?php
                                }
                                // Logic for displaying the 'dismiss' button
                                if ( !isset( $item_value["hide_mark_done"] ) || empty( $item_value["hide_mark_done"] ) ){
                                    if ( !isset( $item_value["complete"] ) || empty( $item_value["complete"] ) ) {
                                        ?>
                                            <button name="dismiss" value="<?php echo esc_attr( $item_key ); ?>">Dismiss</button>
                                        <?php
                                    }
                                }
                                ?>
                            </td>
                            <td class="toggle_chevron" data-cell="<?php echo esc_attr( $row_count ); ?>">
                                <div class="wizard_chevron_open"></div>
                            </td>
                        </tr>
                        <tr class="wizard_description" data-row="<?php echo esc_attr( $row_count ); ?>" hidden>
                            <td colspan="5">
                                <p>
                                    <?php
                                    //replace urls with links
                                    $url = '@(http)?(s)?(://)?(([a-zA-Z])([-\w]+\.)+([^\s\.]+[^\s]*)+[^,.\s])@';
                                    $item_value['description'] = preg_replace( $url, '<a href="http$2://$4" target="_blank" title="$0">$0</a>', $item_value['description'] );
                                    echo nl2br( wp_kses_post( $item_value['description'] ) ); ?>
                                </p>
                                <?php
                                // Logic for displaying the 'un-dismiss' button
                                if ( !isset( $item_value["hide_mark_done"] ) || empty( $item_value["hide_mark_done"] ) ){

                                    if ( isset( $item_value["complete"] ) && !empty( $item_value["complete"] ) ) {
                                        ?>
                                        <button name="undismiss" value="<?php echo esc_attr( $item_key ); ?>">Un-dismiss</button>
                                        <?php
                                    }
                                }
                                ?>
                            </td>
                        </tr>
                            <?php $row_count++; ?>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </form>
            <script>
                jQuery( '.toggle_chevron' ).on( 'click', function() {
                    let class_name = jQuery( this ).children()[0].className;
                    let cell_number = jQuery( this ).data('cell');

                    // Toggle chevron arrow class names
                    if ( class_name == 'wizard_chevron_open' ) {
                        jQuery( this ).children().attr('class', 'wizard_chevron_close');
                    } else {
                        jQuery( this ).children().attr('class', 'wizard_chevron_open');
                    }

                    // Toggle description row visibility
                    let row = jQuery( "*[data-row='" + cell_number + "']" )[0];
                    if ( row.hidden == true ) {
                        row.hidden = false;
                    } else {
                        row.hidden = true;
                    }
                });
            </script>
            <?php
        });
    }
}


add_filter( 'dt_setup_wizard_items', function ( $items, $setup_options ){
    $mapbox_key = DT_Mapbox_API::get_key();

    $items["https_check"] = [
        "label" => "Upgrade HTTP to HTTPS",
        "description" => "Encrypt your traffic from network sniffers",
        "link" => esc_url( "https://wordpress.org/support/article/https-for-wordpress/" ),
        "complete" => is_ssl(),
        "hide_mark_done" => true
    ];
    $items["mapbox_key"] = [
        "label" => "Upgrade Mapping",
        "description" => "Better results when search locations and better mapping",
        "link" => admin_url( "admin.php?page=dt_mapping_module&tab=geocoding" ),
        "complete" => (bool) $mapbox_key,
    ];
    if ( $mapbox_key ) {
        $mapbox_upgraded = DT_Mapbox_API::are_records_and_users_upgraded_with_mapbox();
        $items["upgraded_mapbox_records"] = [
            "label" => "Upgrade Users and Record Mapping",
            "description" => " Please upgrade Users, Contacts and Groups for the Locations to show up on maps and charts.",
            "link" => admin_url( "admin.php?page=dt_mapping_module&tab=geocoding" ),
            "complete" => $mapbox_upgraded,
            "hide_mark_done" => true
        ];
    }

    $items['explore_user_invite'] = [
        'label' => 'Explore User Invite Area',
        'description' => 'Navigate the user invite area and have a friend or co-worker start using Disciple.Tools.',
        'link' => admin_url( 'user-new.php' ),
        'complete' => false,
        'hide_mark_done' => false
    ];
    $items['explore_plugins'] = [
        'label' => 'Explore Recommended Plugins',
        'description' => "Navigate the recommended plugins section to see different ways to extend your Disciple.Tools experience.\r\n Also see https://disciple.tools/plugins/",
        'link' => admin_url( 'admin.php?page=dt_extensions' ),
        'complete' => false,
        'hide_mark_done' => false
    ];
    $items['explore_custom_fields'] = [
        'label' => 'Explore Custom Fields',
        'description' => 'Explore the custom fields section and unlock its full potential.',
        'link' => admin_url( 'admin.php?page=dt_options&tab=custom-fields' ),
        'complete' => false,
        'hide_mark_done' => false
    ];
    $items['explore_custom_tiles'] = [
        'label' => 'Explore Custom Tiles',
        'description' => 'Explore the custom tiles section and personalize your Disicple.Tools instance.',
        'link' => admin_url( 'admin.php?page=dt_options&tab=custom-tiles' ),
        'complete' => false,
        'hide_mark_done' => false
    ];
    $items['explore_site_link'] = [
        'label' => 'Explore Site Links',
        'description' => 'Did you know that you can link up several Disciple.Tools instances in a single place? Navigate the Site Link section to find out more!',
        'link' => 'https://disciple.tools/user-docs/getting-started-info/admin/site-links/',
        'complete' => false,
        'hide_mark_done' => false
    ];
    $items['explore_subscribe_dt_new'] = [
        'label' => 'Subscribe to D.T News',
        'description' => 'Stay up to date with the latest features and news for all things Disciple.Tools',
        'link' => esc_url( 'https://disciple.tools/news/' ),
        'complete' => false,
        'hide_mark_done' => false
    ];

    $items['non_wp_cron'] = [
        'label' => 'Disable WP Cron',
        'description' => "By disabling the built in WP Cron and enabling an alternate solution this system will be able to rely on a better scheduler to send out notifications and scheduled tasks. \r\n See https://developers.disciple.tools/hosting/cron for more details.",
        'link' => esc_url( 'https://developers.disciple.tools/hosting/cron' ),
        'complete' => defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON === true,
        'hide_mark_done' => true
    ];

    return $items;
}, 10, 2);
