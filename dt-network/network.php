<?php
/**
 * Core functions to power the network features of Disciple Tools
 *
 * @class      Disciple_Tools_Notifications
 * @version    0.1.0
 * @since      0.1.0
 * @package    Disciple_Tools
 */

if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Disciple_Tools_Network {


    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {

        if ( get_option( 'dt_network_enabled' ) ) {

            add_filter( 'site_link_type', [ $this, 'site_link_type' ], 10, 1 );
            add_filter( 'site_link_type_capabilities', [ $this, 'site_link_capabilities' ], 10, 1 );

        }

        if ( is_admin() ) {

            // set partner details
            if ( ! get_option( 'dt_site_partner_profile' ) ) {
                self::create_partner_profile();
            }
        }
    }

    public static function create_partner_profile() {
        $partner_profile = [
            'partner_name' => get_option( 'blogname' ),
            'partner_description' => get_option( 'blogdescription' ),
            'partner_id' => Site_Link_System::generate_token( 40 ),
            'partner_url' => site_url(),
        ];
        update_option( 'dt_site_partner_profile', $partner_profile, true );
        return $partner_profile;
    }

    /**
     * @see /dt-core/admin/menu/tabs/tab-network.php for the page shell
     */
    public static function admin_network_enable_box() {
        if ( isset( $_POST['network_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['network_nonce'] ) ), 'network'.get_current_user_id() ) && isset( $_POST['network_feature'] )) {
            update_option( 'dt_network_enabled', (int) sanitize_text_field( wp_unslash( $_POST['network_feature'] ) ), true );
        }
        $enabled = get_option( 'dt_network_enabled' );
        ?>

        <form method="post">
            <?php wp_nonce_field( 'network'.get_current_user_id(), 'network_nonce', false, true ) ?>
            <label for="network_feature">
                <?php esc_html_e( 'Network Extension' ) ?>
            </label>
            <select name="network_feature" id="network_feature">
                <option value="0" <?php echo $enabled ? '' : 'selected' ?>><?php esc_html_e( 'Disabled' ) ?></option>
                <option value="1" <?php echo $enabled ? 'selected' : '' ?>><?php esc_html_e( 'Enabled' ) ?></option>
            </select>
            <button type="submit" class="button"><?php esc_html_e( 'Save' ) ?></button>
        </form>

        <?php

    }

    public static function admin_site_link_box() {
        global $wpdb;

        $site_links = $wpdb->get_results( "
        SELECT p.ID, p.post_title, pm.meta_value as type
            FROM $wpdb->posts as p
              LEFT JOIN $wpdb->postmeta as pm
              ON p.ID=pm.post_id
              AND pm.meta_key = 'type'
            WHERE p.post_type = 'site_link_system'
              AND p.post_status = 'publish'
        ", ARRAY_A );

        if ( ! is_array( $site_links ) ) {
            echo 'No site links found. Go to <a href="'. esc_url( admin_url() ).'edit.php?post_type=site_link_system">Site Links</a> and create a site link, and then select "Network Report" as the type."';
        }

        echo '<h2>You are reporting to these Network Dashboards</h2>';
        foreach ( $site_links as $site ) {
            if ( 'network_dashboard_sending' === $site['type'] ) {
                echo '<dd><a href="'. esc_url( admin_url() ) .'post.php?post='. esc_attr( $site['ID'] ).'&action=edit">' . esc_html( $site['post_title'] ) . '</a></dd>';
            }
        }

        echo '<h2>Other System Site-to-Site Links</h2>';
        foreach ( $site_links as $site ) {
            if ( ! ( 'network_dashboard_sending' === $site['type'] ) ) {
                echo '<dd><a href="'. esc_url( admin_url() ) .'post.php?post='. esc_attr( $site['ID'] ).'&action=edit">' . esc_html( $site['post_title'] ) . '</a></dd>';
            }
        }

        echo '<hr><p style="font-size:.8em;">Note: Network Dashboards are Site Links that have the "Connection Type" of "Network Dashboard Sending".</p>';
    }

    public static function admin_test_send_box() {
        if ( isset( $_POST['test_send_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['test_send_nonce'] ) ), 'test_send_'.get_current_user_id() ) ) {
            dt_write_log( 'Test Send' );
        }
        ?>

        <form method="post">
            <?php wp_nonce_field( 'test_send_'.get_current_user_id(), 'test_send_nonce', false, true ) ?>
            <button type="submit" name="send_test" class="button"><?php esc_html_e( 'Send Test' ) ?></button>
        </form>
        <?php
    }

    public static function admin_partner_profile_box() {
        // process post action
        if ( isset( $_POST['partner_profile_form'] )
            && isset( $_POST['_wpnonce'] )
            && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'partner_profile'.get_current_user_id() )
            && isset( $_POST['partner_name'] )
            && isset( $_POST['partner_description'] )
            && isset( $_POST['partner_id'] )
        ) {
            $partner_profile = [
                'partner_name' => sanitize_text_field( wp_unslash( $_POST['partner_name'] ) ) ?: get_option( 'blogname' ),
                'partner_description' => sanitize_text_field( wp_unslash( $_POST['partner_description'] ) ) ?: get_option( 'blogdescription' ),
                'partner_id' => sanitize_text_field( wp_unslash( $_POST['partner_id'] ) ) ?: Site_Link_System::generate_token( 40 ),
            ];

            update_option( 'dt_site_partner_profile', $partner_profile, true );
        }
        $partner_profile = get_option( 'dt_site_partner_profile' );

        ?>
        <!-- Box -->
        <form method="post">
            <?php wp_nonce_field( 'partner_profile'.get_current_user_id() ); ?>
            <table class="widefat striped">
                <thead>
                <th>Network Profile</th>
                </thead>
                <tbody>
                <tr>
                    <td>
                        <table class="widefat">
                            <tbody>
                            <tr>
                                <td><label for="partner_name">Your Group Name</label></td>
                                <td><input type="text" class="regular-text" name="partner_name"
                                           id="partner_name" value="<?php echo esc_html( $partner_profile['partner_name'] ) ?>" /></td>
                            </tr>
                            <tr>
                                <td><label for="partner_description">Your Group Description</label></td>
                                <td><input type="text" class="regular-text" name="partner_description"
                                           id="partner_description" value="<?php echo esc_html( $partner_profile['partner_description'] ) ?>" /></td>
                            </tr>
                            <tr>
                                <td><label for="partner_id">Site ID</label></td>
                                <td><?php echo esc_attr( $partner_profile['partner_id'] ) ?>
                                    <input type="hidden" class="regular-text" name="partner_id"
                                           id="partner_id" value="<?php echo esc_attr( $partner_profile['partner_id'] ) ?>" /></td>
                            </tr>
                            </tbody>
                        </table>

                        <p><br>
                            <button type="submit" id="partner_profile_form" name="partner_profile_form" class="button">Update</button>
                        </p>
                    </td>
                </tr>
                </tbody>
            </table>
        </form>
        <br>
        <!-- End Box -->
        <?php
    }


    public function site_link_type( $type ) {
        $type['network_dashboard_sending'] = __( 'Network Dashboard Sending' );
        return $type;
    }

    public function site_link_capabilities( $args ) {
        if ( 'network_dashboard_sending' == $args['connection_type'] ) {
            $args['capabilities'][] = 'network_dashboard_transfer';
        }
        return $args;
    }

    /**
     * Returns array of locations and counts of groups
     * This does not distinguish between types of groups.
     * The array contains 'location' and 'count' fields.
     *
     * @return array|null|object
     */
    public function get_child_groups() {
        // get the groups and child groups of the location
        global $wpdb;

        // @todo move this query to the central query class
        return $wpdb->get_results( "SELECT p2p_to as location, count(p2p_id) as count FROM $wpdb->p2p WHERE p2p_type = 'groups_to_locations' GROUP BY p2p_to", ARRAY_A );
    }

    public function get_child_populations() {
        global $post_id;

        if ( empty( $post_id ) ) {
            return 0;
        }

        // @todo pull these populations from the new locations system
        // Set up the objects needed
        $my_wp_query = new WP_Query();
        $all_wp_pages = $my_wp_query->query( array(
            'post_type' => 'locations',
            'posts_per_page' => '-1'
        ) );

        $children = get_page_children( $post_id, $all_wp_pages );

        return $children;
    }



    public static function api_set_location_attributes( $collection ) {
        if ( ! current_user_can( 'network_dashboard_transfer' ) ) {
            return new WP_Error( __METHOD__, 'Network report permission error.' );
        }

        // @todo finish response
        // $collection is a list of location ids with updated geonameids and populations.


        if ( true ) {
            return $collection;
        } else {
            return new WP_Error( __METHOD__, 'Failed to get report' );
        }
    }

    public static function send_project_totals( $site_post_id ) {

        if ( ! current_user_can( 'network_dashboard_transfer' ) ) {
            return new WP_Error( __METHOD__, 'Network report permission error.' );
        }

        // Trigger Remote Report from Site
        $site = Site_Link_System::get_site_connection_vars( $site_post_id );
        if ( is_wp_error( $site ) ) {
            return new WP_Error( __METHOD__, 'Error creating site connection details.' );
        }
        $args = [
            'method' => 'POST',
            'body' => [
                'transfer_token' => $site['transfer_token'],
                'report_data' => self::get_project_totals(),
            ]
        ];
        $result = wp_remote_post( 'https://' . $site['url'] . '/wp-json/dt-public/v1/network/collect/project_totals', $args );
        if ( is_wp_error( $result ) ) {
            return new WP_Error( 'failed_remote_post', $result->get_error_message() );
        } else {
            return $result['body'];
        }

    }

    public static function get_project_totals() {
        return [
            'partner_id' => dt_get_partner_profile_id(),
            'total_contacts' => 200,// @todo add real data
            'total_groups' => 10,// @todo add real data
            'total_users' => 5,// @todo add real data
            'date' => current_time( 'mysql' ),
        ];
    }

    public static function send_site_profile( $site_post_id ) {

        if ( ! current_user_can( 'network_dashboard_transfer' ) ) {
            return new WP_Error( __METHOD__, 'Network report permission error.' );
        }

        $site_profile = self::get_site_profile();

        // Trigger Remote Report from Site
        $site = Site_Link_System::get_site_connection_vars( $site_post_id );
        if ( is_wp_error( $site ) ) {
            return new WP_Error( __METHOD__, 'Error creating site connection details.' );
        }
        $args = [
            'method' => 'POST',
            'body' => [
                'transfer_token' => $site['transfer_token'],
                'report_data' => $site_profile,
            ]
        ];
        $result = wp_remote_post( 'https://' . $site['url'] . '/wp-json/dt-public/v1/network/collect/site_profile', $args );
        if ( is_wp_error( $result ) ) {
            return new WP_Error( 'failed_remote_post', $result->get_error_message() );
        } else {
            return $result['body'];
        }
    }

    public static function get_site_profile() {
        $site_profile = get_option( 'dt_site_partner_profile' );
        $site_profile['check_sum'] = md5( serialize( $site_profile ) );
        return $site_profile;
    }

    public static function send_site_locations( $site_post_id ) {

        if ( ! current_user_can( 'network_dashboard_transfer' ) ) {
            return new WP_Error( __METHOD__, 'Network report permission error.' );
        }

        // Trigger Remote Report from Site
        $site = Site_Link_System::get_site_connection_vars( $site_post_id );
        if ( is_wp_error( $site ) ) {
            return new WP_Error( __METHOD__, 'Error creating site connection details.' );
        }
        $args = [
            'method' => 'POST',
            'body' => [
                'transfer_token' => $site['transfer_token'],
                'partner_id' => dt_get_partner_profile_id(),
                'report_data' => self::get_site_locations(),
            ]
        ];

        $result = wp_remote_post( 'https://' . $site['url'] . '/wp-json/dt-public/v1/network/collect/site_locations', $args );
        if ( is_wp_error( $result ) ) {
            return new WP_Error( 'failed_remote_post', $result->get_error_message() );
        } else {
            return $result['body'];
        }

    }

    public static function get_site_locations() {
        global $wpdb;

        $query_results = $wpdb->get_results( "
            SELECT a.ID as id, a.post_parent as parent_id, a.post_title as name, b.meta_value as raw, c.meta_value as address
            FROM $wpdb->posts as a
            JOIN $wpdb->postmeta as b
                ON a.ID=b.post_id
                AND b.meta_key = 'raw'
            JOIN $wpdb->postmeta as c
                ON a.ID=c.post_id
                AND c.meta_key = 'location_address'
            WHERE post_type = 'locations' AND post_status = 'publish'
        ", ARRAY_A );

        $locations = [
            'locations' => $query_results,
            'check_sum' => md5( serialize( $query_results ) ),
        ];

        return $locations;
    }



}
Disciple_Tools_Network::instance();


/**
 * Helper function to get the partner profile id.
 * @return mixed
 */
function dt_get_partner_profile_id() {
    $partner_profile = get_option( 'dt_site_partner_profile' );
    if ( ! isset( $partner_profile['partner_id'] ) ) {
        $partner_profile = Disciple_Tools_Network::create_partner_profile();
    }
    return $partner_profile['partner_id'];
}

/**
 * Helper function to get the partner profile id.
 * @return mixed
 */
function dt_get_partner_profile() {
    $partner_profile = get_option( 'dt_site_partner_profile' );
    if ( empty( $partner_profile ) ) {
        $partner_profile = Disciple_Tools_Network::create_partner_profile();
    }
    return $partner_profile;
}


// Begin Schedule daily cron build
class Disciple_Tools_Cron_Snapshot_Scheduler {

    public function __construct() {
        if ( ! wp_next_scheduled( 'load-snapshot-report' ) ) {
            wp_schedule_event( strtotime( 'tomorrow 1am' ), 'daily', 'load-snapshot-report' );
        }
        add_action( 'load-snapshot-report', [ $this, 'action' ] );
    }

    public static function action(){
        do_action( "dt_load_snapshot_report" );
    }
}

class Disciple_Tools_Cron_Snapshot_Async extends Disciple_Tools_Async_Task {

    protected $action = 'dt_load_snapshot_report';

    protected function prepare_data( $data ) {
        return $data;
    }

    protected function run_action() {
        Disciple_Tools_Snapshot_Report::snapshot_report();
    }
}
new Disciple_Tools_Cron_Snapshot_Scheduler();
try {
    new Disciple_Tools_Cron_Snapshot_Async();
} catch ( Exception $e ) {
    dt_write_log( $e );
}
// End Schedule daily cron build


class Disciple_Tools_Snapshot_Report {
    public static function snapshot_report( $force_refresh = false ) {

        //        $force_refresh = true; // @todo @development mode. remove line for production

        if ( $force_refresh ) {
            delete_transient( 'dt_snapshot_report' );
        }
        if ( get_transient( 'dt_snapshot_report' ) ) {
            return get_transient( 'dt_snapshot_report' );
        }

        $profile = dt_get_partner_profile();

        $report_data = [
            'partner_id' => $profile['partner_id'],
            'profile'    => $profile,
            'contacts'   => [
                'current_state'    => self::contacts_current_state(),
                'added'            => [
                    'sixty_days'         => self::counted_by_day(),
                    'twenty_four_months' => self::counted_by_month(),
                ],
                'baptisms'         => [
                    'current_state' => [
                        'all_baptisms' => Disciple_Tools_Network_Queries::total_baptisms(),
                    ],
                    'added'         => [
                        'sixty_days'         => self::counted_by_day( 'baptisms' ),
                        'twenty_four_months' => self::counted_by_month( 'baptisms' ),
                    ],
                    'generations'   => self::generations( 'baptisms' ),
                ],
                'follow_up_funnel' => [
                    'funnel'           => self::funnel(),
                    'ongoing_meetings' => self::ongoing_meetings(),
                    'coaching'         => self::coaching(),
                ],
            ],
            'groups'     => [
                'current_state'      => self::groups_current_state(),
                'by_types'           => self::groups_by_type(),
                'added'              => [
                    'sixty_days'         => self::counted_by_day( 'groups' ),
                    'twenty_four_months' => self::counted_by_month( 'groups' ),
                ],
                'health'             => self::group_health(),
                'church_generations' => self::generations( 'church' ),
                'group_generations'  => self::generations( 'groups' ),
            ],
            'users'      => [
                'current_state'              => self::users_current_state(),
                'login_activity'             => [
                    'sixty_days'         => self::counted_by_day( 'logged_in' ),
                    'twenty_four_months' => self::counted_by_month( 'logged_in' ),
                ],
                'last_thirty_day_engagement' => self::user_logins_last_thirty_days(),
            ],
            'locations'  => [
                'data_types'    => self::location_data_types(),
                'countries'     => self::get_locations_list( true ),
                'current_state' => self::get_locations_current_state(),
                'list'          => self::get_locations_list(),
            ],
            'date'       => current_time( 'timestamp' ),
            'status'     => 'OK',
        ];

        if ( $report_data ) {
            set_transient( 'dt_snapshot_report', $report_data, 60 * 60 * 24 );

            return $report_data;
        } else {
            return new WP_Error( __METHOD__, 'Failed to get report' );
        }
    }

    public static function contacts_current_state() {
        $data = [
            'all_contacts'  => 0,
            'critical_path' => [],
        ];

        // Add critical path

        if ( ! class_exists( 'Disciple_Tools_Metrics_Hooks_Base' ) ) {
            require_once( get_template_directory() . '/dt-metrics/metrics.php' );
        }

        $critical_path = Disciple_Tools_Metrics_Hooks_Base::query_project_contacts_progress();
        foreach ( $critical_path as $path ) {
            $data['critical_path'][ $path['key'] ] = $path;
        }

        // Add
        $data['status'] = self::get_contacts_status();

        $data['all_contacts'] = Disciple_Tools_Network_Queries::all_contacts();

        return $data;
    }

    /**
     * Gets an array list of all contacts current status.
     * [new] => 0
     * [unassignable] => 0
     * [unassigned] => 0
     * [assigned] => 6
     * [active] => 38
     * [paused] => 5
     * [closed] => 5
     *
     * @return array
     */
    public static function get_contacts_status(): array {
        $data            = [];
        $contact_fields  = Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings();
        $status_defaults = $contact_fields['overall_status']['default'];
        $current_state   = Disciple_Tools_Network_Queries::contacts_current_state();
        foreach ( $status_defaults as $key => $status ) {
            $data[ $key ] = 0;
            foreach ( $current_state as $state ) {
                if ( $state['status'] === $key ) {
                    $data[ $key ] = (int) $state['count'];
                }
            }
        }

        return $data;
    }

    public static function counted_by_day( $type = null ) {
        $data1 = [];
        $data2 = [];
        $data3 = [];

        switch ( $type ) {
            case 'groups':
                $dates = Disciple_Tools_Network_Queries::counted_by_day( 'created', 'groups' );
                break;
            case 'logged_in':
                $dates = Disciple_Tools_Network_Queries::counted_by_day( 'logged_in', 'user' );
                break;
            case 'baptisms':
                $dates = Disciple_Tools_Network_Queries::baptisms_counted_by_day();
                break;
            default: // contacts
                $dates = Disciple_Tools_Network_Queries::counted_by_day( 'created', 'contacts' );
                break;
        }

        foreach ( $dates as $date ) {
            $date['value']          = (int) $date['value'];
            $data1[ $date['date'] ] = $date;
        }

        $day_list = self::get_day_list( 60 );
        foreach ( $day_list as $day ) {
            if ( isset( $data1[ $day ] ) ) {
                $data2[] = [
                    'date'  => $data1[ $day ]['date'],
                    'value' => $data1[ $day ]['value'],
                ];
            } else {
                $data2[] = [
                    'date'  => $day,
                    'value' => 0,
                ];
            }
        }

        arsort( $data2 );

        foreach ( $data2 as $d ) {
            $data3[] = $d;
        }

        return $data3;
    }

    public static function counted_by_month( $type = null ) {
        $data1 = [];
        $data2 = [];
        $data3 = [];

        switch ( $type ) {
            case 'groups':
                $dates = Disciple_Tools_Network_Queries::counted_by_month( 'created', 'groups' );
                break;
            case 'logged_in':
                $dates = Disciple_Tools_Network_Queries::counted_by_month( 'logged_in', 'user' );
                break;
            case 'baptisms':
                $dates = Disciple_Tools_Network_Queries::baptisms_counted_by_month();
                break;
            default: // contacts
                $dates = Disciple_Tools_Network_Queries::counted_by_month( 'created', 'contacts' );
                break;
        }

        foreach ( $dates as $date ) {
            $date['value']          = (int) $date['value'];
            $data1[ $date['date'] ] = $date;
        }

        $list = self::get_month_list( 25 );
        foreach ( $list as $month ) {
            if ( isset( $data1[ $month ] ) ) {
                $data2[] = [
                    'date'  => $data1[ $month ]['date'] . '-01',
                    'value' => $data1[ $month ]['value'],
                ];
            } else {
                $data2[] = [
                    'date'  => $month . '-01',
                    'value' => 0,
                ];
            }
        }

        arsort( $data2 );

        foreach ( $data2 as $d ) {
            $data3[] = $d;
        }

        return $data3;
    }

    public static function user_logins_last_thirty_days() {

        $active = Disciple_Tools_Network_Queries::user_logins_last_thirty_days();

        $total_users = count_users();

        $inactive = $total_users['total_users'] - $active;
        if ( $inactive < 1 ) {
            $inactive = 0;
        }

        $data = [
            [
                'label' => 'Active',
                'value' => $active,
            ],
            [
                'label' => 'Inactive',
                'value' => $inactive,
            ]
        ];

        return $data;
    }

    /**
     * Gets an array of the last number of days.
     *
     * @param int $number_of_days
     *
     * @return array
     */
    public static function get_day_list( $number_of_days = 60 ) {
        $d = [];
        for ( $i = 0; $i < $number_of_days; $i ++ ) {
            $d[] = date( "Y-m-d", strtotime( '-' . $i . ' days' ) );
        }

        return $d;
    }

    /**
     * Gets an array of last 25 months.
     *
     * @note 25 months allows you to get 3 years to compare of this month.
     *
     * @param int $number_of_months
     *
     * @return array
     */
    public static function get_month_list( $number_of_months = 25 ) {
        $d = [];
        for ( $i = 0; $i < $number_of_months; $i ++ ) {
            $d[] = date( "Y-m", strtotime( '-' . $i . ' months' ) );
        }

        return $d;
    }

    /**
     * Gets an array of the current state of groups
     * [active] => Array
     * (
     * [pre_group] => 3
     * [group] => 0
     * [church] => 3
     * )
     * [inactive] => Array
     * (
     * [pre_group] => 0
     * [group] => 0
     * [church] => 0
     * )
     * [total_active] => 6
     * [all] => 6
     *
     * @return array
     */
    public static function groups_current_state() {
        $data = [
            'active'       => [
                'pre_group' => 0,
                'group'     => 0,
                'church'    => 0,
            ],
            'inactive'     => [
                'pre_group' => 0,
                'group'     => 0,
                'church'    => 0,
            ],
            'total_active' => 0, // all non-duplicate groups in the system active or inactive.
            'all'          => 0,
        ];

        // Add types and status
        $types_and_status = Disciple_Tools_Network_Queries::groups_types_and_status();
        foreach ( $types_and_status as $value ) {
            $value['type'] = str_replace( '-', '_', $value['type'] );

            $data[ $value['status'] ][ $value['type'] ] = (int) $value['count'];

            if ( 'active' === $value['status'] ) {
                $data ['total_active'] = $data['total_active'] + (int) $value['count'];
            }
        }

        $data['all'] = Disciple_Tools_Network_Queries::all_groups();

        return $data;
    }

    public static function groups_by_type() {
        $data = [];

        $types_and_status = Disciple_Tools_Network_Queries::groups_types_and_status();

        $keyed = [];
        foreach ( $types_and_status as $status ) {
            if ( 'active' === $status['status'] ) {
                $keyed[ $status['type'] ] = $status;
            }
        }

        if ( isset( $keyed['pre-group'] ) ) {
            $data[] = [
                'name'  => 'Pre-Group',
                'value' => $keyed['pre-group']['count'],
            ];
        } else {
            $data[] = [
                'name'  => 'Pre-Group',
                'value' => 0,
            ];
        }

        if ( isset( $keyed['group'] ) ) {
            $data[] = [
                'name'  => 'Group',
                'value' => $keyed['group']['count'],
            ];
        } else {
            $data[] = [
                'name'  => 'Group',
                'value' => 0,
            ];
        }

        if ( isset( $keyed['church'] ) ) {
            $data[] = [
                'name'  => 'Church',
                'value' => $keyed['church']['count'],
            ];
        } else {
            $data[] = [
                'name'  => 'Church',
                'value' => 0,
            ];
        }

        return $data;
    }

    public static function group_health() {
        $data             = [];
        $labels           = [];
        $keyed_practicing = [];

        // Make key list
        $group_fields = Disciple_Tools_Groups_Post_Type::instance()->get_custom_fields_settings();
        foreach ( $group_fields["health_metrics"]["default"] as $key => $option ) {
            $labels[ $key ] = $option["label"];
        }

        // get results
        $practicing = Disciple_Tools_Network_Queries::group_health();

        // build keyed practicing
        foreach ( $practicing as $value ) {
            $keyed_practicing[ $value['category'] ] = $value['practicing'];
        }

        // get total number
        $total_groups = Disciple_Tools_Network_Queries::groups_churches_total(); // total groups and churches

        // add real numbers and prepare array
        foreach ( $labels as $key => $label ) {
            if ( isset( $keyed_practicing[ $key ] ) ) {
                $not_practicing = (int) $total_groups - $keyed_practicing[ $key ];
                if ( $not_practicing < 1 ) {
                    $not_practicing = 0;
                }
                $data[] = [
                    'category'       => $label,
                    'not_practicing' => $not_practicing,
                    'practicing'     => $keyed_practicing[ $key ],
                ];
            } else {
                $data[] = [
                    'category'       => $label,
                    'not_practicing' => $total_groups,
                    'practicing'     => 0,
                ];
            }
        }

        return $data;
    }

    public static function users_current_state() {
        $data = [
            'total_users' => 0,
            'roles'       => [
                'responders'  => 0,
                'dispatchers' => 0,
                'multipliers' => 0,
                'strategists' => 0,
                'admins'      => 0,
            ],
        ];

        // Add types and status
        $users = count_users();

        $data['total_users'] = (int) $users['total_users'];

        foreach ( $users['avail_roles'] as $role => $count ) {
            if ( $role === 'marketer' ) {
                $data['roles']['responders'] = $data['roles']['responders'] + $count;
            }
            if ( $role === 'dispatcher' ) {
                $data['roles']['dispatchers'] = $data['roles']['dispatchers'] + $count;
            }
            if ( $role === 'multiplier' ) {
                $data['roles']['multipliers'] = $data['roles']['multipliers'] + $count;
            }
            if ( $role === 'administrator' || $role === 'dt_admin' ) {
                $data['roles']['admins'] = $data['roles']['admins'] + $count;
            }
            if ( $role === 'strategist' ) {
                $data['roles']['strategists'] = $data['roles']['strategists'] + $count;
            }
        }

        return $data;
    }

    public static function follow_up_funnel() {
        $data         = [];
        $labels       = [];
        $keyed_result = [];

        $contact_fields = Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings();

        foreach ( $contact_fields['seeker_path']['default'] as $key => $value ) {
            $labels[ $key ] = $value['label'];
        }

        require_once( get_template_directory() . '/dt-metrics/metrics.php' );
        $results = Disciple_Tools_Metrics_Hooks_Base::query_project_contacts_progress();
        if ( empty( $results ) || is_wp_error( $results ) ) {
            $results = [];
        }

        foreach ( $results as $result ) {
            $keyed_result[ $result['key'] ] = $result;
        }

        foreach ( $labels as $key => $label ) {
            if ( isset( $keyed_result[ $key ] ) ) {
                $data[] = [
                    "name"  => $label,
                    "value" => (int) $keyed_result[ $key ]['value']
                ];
            } else {
                $data[] = [
                    "name"  => $label,
                    "value" => 0
                ];
            }
        }

        return $data;
    }

    public static function funnel() {
        return array_slice( self::follow_up_funnel(), 0, 5 );
    }

    public static function ongoing_meetings() {
        $data = self::follow_up_funnel();
        if ( isset( $data[5] ) ) {
            return (int) $data[5]['value'];
        }

        return 0; // returns 0 if fail
    }

    /**
     * Selects single value from query.
     *
     * @return int
     */
    public static function coaching() {
        $data = self::follow_up_funnel();
        if ( isset( $data[6] ) ) {
            return (int) $data[6]['value'];
        }

        return 0; // returns 0 if fail
    }

    public static function generations( $type = null ) {

        $data = [];

        switch ( $type ) {
            case 'groups':
                $generation = Disciple_Tools_Counter::critical_path( 'all_group_generations', 0, PHP_INT_MAX );
                $item       = 'group';
                break;
            case 'baptisms':
                $baptisms = Disciple_Tools_Counter::critical_path( 'baptism_generations', 0, PHP_INT_MAX );
                if ( empty( $baptisms ) ) {
                    $generation = [];
                } else {
                    foreach ( $baptisms as $key => $value ) {
                        $generation[] = [
                            'generation' => $key,
                            'value'      => $value,
                        ];
                    }
                }
                $item = 'value';
                break;
            default: // returns churches
                $generation = Disciple_Tools_Counter::critical_path( 'all_group_generations', 0, PHP_INT_MAX );
                $item       = 'church';
                break;
        }

        if ( empty( $generation ) ) {
            return [
                [
                    'label' => 'Gen 1',
                    'value' => 0,
                ]
            ];
        }

        $end = false;
        foreach ( $generation as $gen ) {
            if ( $end ) { // this makes sure the last generation is zero but no more.
                break;
            }

            $data[] = [
                'label' => 'Gen ' . $gen['generation'],
                'value' => $gen[ $item ]
            ];

            if ( $gen[ $item ] === 0 ) {
                $end = true;
            }
        }

        return $data;
    }

    public static function location_data_types( $preset = false ) {
        if ( $preset ) {
            return [
                'contacts' => 0,
                'groups'   => 0,
                'churches' => 0,
                'users'    => 0,
            ];
        } else {
            return [
                'contacts',
                'groups',
                'churches',
                'users',
            ];
        }
    }

    public static function get_locations_list( $countries_only = false ) {

        $data = [];

        if ( $countries_only ) {
            $results = Disciple_Tools_Mapping_Queries::get_geoname_totals_for_countries();
        } else {
            $results = Disciple_Tools_Mapping_Queries::get_geoname_totals();
        }

        if ( ! empty( $results ) ) {
            foreach ( $results as $item ) {
                // skip custom geonames. Their totals are represented in the standard parents.
                if ( $item['geonameid'] > 1000000000 ) {
                    continue;
                }
                // set array, if not set
                if ( ! isset( $data[ $item['geonameid'] ] ) ) {
                    $data[ $item['geonameid'] ] = self::location_data_types( true );
                }
                // increment existing item type or add new
                if ( isset( $data[ $item['geonameid'] ][ $item['type'] ] ) ) {
                    $data[ $item['geonameid'] ][ $item['type'] ] = (int) $data[ $item['geonameid'] ][ $item['type'] ] + (int) $item['count'];
                } else {
                    $data[ $item['geonameid'] ][ $item['type'] ] = (int) $item['count'];
                }
            }
        }

        return $data;
    }

    public static function get_locations_current_state() {
        $data = [
            'active_countries'          => 0,
            'active_countries_geonames' => [],
            'active_admin1'             => 0,
            'active_admin1_geonames'    => [],
            'active_admin2'             => 0,
            'active_admin2_geonames'    => [],
        ];

        $results = Disciple_Tools_Network_Queries::locations_current_state();
        if ( ! empty( $results['active_countries'] ) ) {
            $data['active_countries'] = (int) $results['active_countries'];
        }
        if ( ! empty( $results['active_countries'] ) ) {
            $data['active_admin1'] = (int) $results['active_admin1'];
        }
        if ( ! empty( $results['active_countries'] ) ) {
            $data['active_admin2'] = (int) $results['active_admin2'];
        }

        $active_countries_geonames = Disciple_Tools_Mapping_Queries::active_countries_geonames();
        if ( ! empty( $active_countries_geonames ) ) {
            foreach ( $active_countries_geonames as $geonameid ) {
                $data['active_countries_geonames'][] = (int) $geonameid;
            }
        }
        $active_admin1_geonames = Disciple_Tools_Mapping_Queries::active_admin1_geonames();
        if ( ! empty( $active_admin1_geonames ) ) {
            foreach ( $active_admin1_geonames as $geonameid ) {
                $data['active_admin1_geonames'][] = (int) $geonameid;
            }
        }
        $active_admin2_geonames = Disciple_Tools_Mapping_Queries::active_admin2_geonames();
        if ( ! empty( $active_admin2_geonames ) ) {
            foreach ( $active_admin2_geonames as $geonameid ) {
                $data['active_admin2_geonames'][] = (int) $geonameid;
            }
        }

        return $data;
    }
}
