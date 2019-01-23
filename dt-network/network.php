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

            add_action( 'admin_menu', [ $this, 'meta_box_setup' ], 20 );
            add_filter( "dt_custom_fields_settings", [ $this, 'saturation_field_filter' ], 1, 2 );

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

    public function meta_box_setup() {
        add_meta_box( 'location_network_box', __( 'Network Dashboard Fields', 'disciple_tools' ), [ $this, 'load_mapping_meta_box' ], 'locations', 'normal', 'high' );
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
        // @todo testing
        Disciple_Tools_Snapshot_Report::groups_by_type();
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

    public static function admin_locations_gname_installed_box() {
        // @codingStandardsIgnoreLine
        echo self::load_current_locations();
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

    public static function load_current_locations() {
        global $wpdb;

        $query = $wpdb->get_results("
            SELECT
                  a.ID as id,
                  a.post_parent as parent_id,
                  a.post_title as name
                FROM $wpdb->posts as a
                WHERE a.post_status = 'publish'
                AND a.post_type = 'locations'
            ", ARRAY_A );


        // prepare special array with parent-child relations
        $menu_data = array(
            'items' => array(),
            'parents' => array()
        );

        foreach ( $query as $menu_item )
        {
            $menu_data['items'][$menu_item['id']] = $menu_item;
            $menu_data['parents'][$menu_item['parent_id']][] = $menu_item['id'];
        }

        // output the menu
        return self::build_tree( 0, $menu_data, -1 );

    }

    public static function build_tree( $parent_id, $menu_data, $gen) {
        $html = '';

        if (isset( $menu_data['parents'][$parent_id] ))
        {
            $gen++;
            foreach ($menu_data['parents'][$parent_id] as $item_id)
            {
                if ( $gen >= 1 ) {
                    for ($i = 0; $i < $gen; $i++ ) {
                        $html .= '-- ';
                    }
                }
                $html .= '<a href="'. esc_url( admin_url() ) . 'post.php?post=' . esc_attr( $menu_data['items'][$item_id]['id'] ) .'&action=edit">' . esc_attr( $menu_data['items'][$item_id]['name'] ) . '</a><br>';

                // find childitems recursively
                $html .= self::build_tree( $item_id, $menu_data, $gen );
            }
        }
        return $html;
    }

    public function saturation_field_filter( $fields, $post_type ) {
        if ( 'locations' === $post_type ) {
            $fields['gn_geonameid'] = [
                'name'        => 'GeoNames ID ',
                'description' => __( 'Geoname ID is the unique global id for this location or its nearest known location. This is usually supplied by the Network Dashboard, but can be overwritten by clicking "edit"' ),
                'type'        => 'locked',
                'default'     => '',
                'section'     => 'saturation_mapping',
            ];
            $fields['gn_population'] = [
                'name'        => 'Population',
                'description' => __( 'Population for this location' ),
                'type'        => 'number',
                'default'     => 0,
                'section'     => 'saturation_mapping',
            ];
        }
        return $fields;
    }

    public function load_mapping_meta_box() {
        Disciple_Tools_Location_Post_Type::instance()->meta_box_content( 'saturation_mapping' );
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
        return $wpdb->get_results( "SELECT p2p_to as location, count(p2p_id) as count FROM $wpdb->p2p WHERE p2p_type = 'groups_to_locations' GROUP BY p2p_to", ARRAY_A );
    }

    public function get_child_populations() {
        global $post_id;

        if ( empty( $post_id ) ) {
            return 0;
        }

        // Set up the objects needed
        $my_wp_query = new WP_Query();
        $all_wp_pages = $my_wp_query->query( array(
            'post_type' => 'locations',
            'posts_per_page' => '-1'
        ) );

        $children = get_page_children( $post_id, $all_wp_pages );

        return $children;
    }

    public static function api_report_by_date( $force_refresh = false ) {


        $report_data = [];

        $report_data['partner_id'] = dt_get_partner_profile_id();

        // @todo add real data to response
        $report_data = [
            'partner_id' => dt_get_partner_profile_id(),
            'total_contacts' => 0,
            'total_groups' => 0,
            'total_users' => 0,
            'new_contacts' => 0,
            'new_groups' => 0,
            'new_users' => 0,
            'total_baptisms' => 0,
            'new_baptisms' => 0,
            'baptism_generations' => 0,
            'church_generations' => 0,
            'locations' => [
                [
                    'location_name' => '',
                    'location_id' => '',
                    'parent_id' => '',
                    'geonameid' => '',
                    'longitude' => '',
                    'latitude' => '',
                    'total_contacts' => 0,
                    'total_groups' => 0,
                    'total_users' => 0,
                    'new_contacts' => 0,
                    'new_groups' => 0,
                    'new_users' => 0,
                ],
                [
                    'location_name' => '',
                    'location_id' => '',
                    'parent_id' => '',
                    'geonameid' => '',
                    'longitude' => '',
                    'latitude' => '',
                    'total_contacts' => 0,
                    'total_groups' => 0,
                    'total_users' => 0,
                    'new_contacts' => 0,
                    'new_groups' => 0,
                    'new_users' => 0,
                ],
                [
                    'location_name' => '',
                    'location_id' => '',
                    'parent_id' => '',
                    'geonameid' => '',
                    'longitude' => '',
                    'latitude' => '',
                    'total_contacts' => 0,
                    'total_groups' => 0,
                    'total_users' => 0,
                    'new_contacts' => 0,
                    'new_groups' => 0,
                    'new_users' => 0,
                ],
            ],
            'critical_path' => [
                'new_inquirers' => 0,
                'first_meetings' => 0,
                'ongoing_meetings' => 0,
                'total_baptisms' => 0,
                'baptism_generations' => [
                    1 => 0,
                    2 => 0,
                    3 => 0,
                ],
                'baptizers' => 0,
                'total_churches_and_groups' => 0,
                'active_groups' => 0,
                'active_churches' => 0,
                'church_generations' => [
                    1 => 0,
                    2 => 0,
                    3 => 0,
                ],
                'church_planters' => 0,
                'people_groups' => 0,
            ],
            'date' => current_time( 'mysql' ),
        ];

        if ( $report_data ) {
            set_transient( 'dt_snapshot_report', $report_data, strtotime( 'tomorrow midnight') );
            return $report_data;
        } else {
            return new WP_Error( __METHOD__, 'Failed to get report' );
        }
    }



    /**
     * @return array|\WP_Error
     */
    public static function api_report_project_total() {
        if ( ! current_user_can( 'network_dashboard_transfer' ) ) {
            return new WP_Error( __METHOD__, 'Network report permission error.' );
        }

        $report_data['partner_id'] = dt_get_partner_profile_id();


        // @todo add real data to response
        $report_data = [
            'partner_id' => dt_get_partner_profile_id(),
            'total_contacts' => 0,
            'total_groups' => 0,
            'total_users' => 0,
            'new_contacts' => 0,
            'new_groups' => 0,
            'new_users' => 0,
            'total_baptisms' => 0,
            'new_baptisms' => 0,
            'baptism_generations' => 0,
            'church_generations' => 0,
            'locations' => [
                [
                    'location_name' => '',
                    'location_id' => '',
                    'parent_id' => '',
                    'geonameid' => '',
                    'longitude' => '',
                    'latitude' => '',
                    'total_contacts' => 0,
                    'total_groups' => 0,
                    'total_users' => 0,
                    'new_contacts' => 0,
                    'new_groups' => 0,
                    'new_users' => 0,
                ],
                [
                    'location_name' => '',
                    'location_id' => '',
                    'parent_id' => '',
                    'geonameid' => '',
                    'longitude' => '',
                    'latitude' => '',
                    'total_contacts' => 0,
                    'total_groups' => 0,
                    'total_users' => 0,
                    'new_contacts' => 0,
                    'new_groups' => 0,
                    'new_users' => 0,
                ],
                [
                    'location_name' => '',
                    'location_id' => '',
                    'parent_id' => '',
                    'geonameid' => '',
                    'longitude' => '',
                    'latitude' => '',
                    'total_contacts' => 0,
                    'total_groups' => 0,
                    'total_users' => 0,
                    'new_contacts' => 0,
                    'new_groups' => 0,
                    'new_users' => 0,
                ],
            ],
            'critical_path' => [
                'new_inquirers' => 0,
                'first_meetings' => 0,
                'ongoing_meetings' => 0,
                'total_baptisms' => 0,
                'baptism_generations' => [
                    1 => 0,
                    2 => 0,
                    3 => 0,
                ],
                'baptizers' => 0,
                'total_churches_and_groups' => 0,
                'active_groups' => 0,
                'active_churches' => 0,
                'church_generations' => [
                    1 => 0,
                    2 => 0,
                    3 => 0,
                ],
                'church_planters' => 0,
                'people_groups' => 0,
            ],
            'date' => current_time( 'mysql' ),
        ];
        if ( true ) {
            return $report_data;
        } else {
            return new WP_Error( __METHOD__, 'Failed to get report' );
        }
    }

    /**
     * @param $check_sum
     *
     * @return \WP_Error
     */
    public static function api_get_locations( $check_sum ) {
        if ( ! current_user_can( 'network_dashboard_transfer' ) ) {
            return new WP_Error( __METHOD__, 'Network report permission error.' );
        }

        // @todo finish response
        // test if the check_sum matches current locations

        // if it does not match, then return a new array of locations for the site to be stored and referred to in the network dashboard.


        if ( true ) {
            return $check_sum;
        } else {
            return new WP_Error( __METHOD__, 'Failed to get report' );
        }
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
try
{
    new Disciple_Tools_Cron_Snapshot_Async();
} catch ( Exception $e ) {
    dt_write_log( $e );
}
// End Schedule daily cron build


class Disciple_Tools_Snapshot_Report
{
    public static function snapshot_report( $force_refresh = false ) {
        $force_refresh = true; // @development mode

        if ( $force_refresh ) {
            delete_transient( 'dt_snapshot_report' );
        }
        if ( get_transient( 'dt_snapshot_report' ) ) {
            return get_transient( 'dt_snapshot_report' );
        }

        $location_list = [
            ['id' => 'AF', 'name' => 'Afganistan'],
            ['id' => 'US', 'name' => 'United States'],
            ['id' => 'TN', 'name' => 'Tunisia'],
        ];
        $location_id = rand(0,2);

        $profile = dt_get_partner_profile();

        $report_data =  [
            'partner_id' => $profile['partner_id'],
            'profile' => $profile,
            'contacts' => [
                'current_state' => self::contacts_current_state(),
                'added' => [
                    'sixty_days' => [
                        [
                            'date' => '2018-12-15',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-12-14',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-12-13',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-12-12',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-12-11',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-12-10',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-12-09',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-12-08',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-12-07',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-12-06',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-12-05',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-12-04',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-12-03',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-12-02',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-12-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-30',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-29',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-28',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-27',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-26',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-25',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-24',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-23',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-22',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-21',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-20',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-19',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-18',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-17',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-16',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-15',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-14',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-13',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-12',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-11',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-10',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-09',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-08',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-07',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-06',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-05',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-04',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-03',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-02',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-10-31',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-10-30',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-10-29',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-10-28',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-10-27',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-10-26',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-10-25',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-10-24',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-10-23',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-10-22',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-10-21',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-10-20',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-10-19',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-10-18',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-10-17',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-10-16',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-10-15',
                            'value' => rand(300, 1000),
                        ],
                    ],
                    'twenty_four_months' => [
                        [
                            'date' => '2018-12-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-10-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-09-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-08-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-07-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-06-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-05-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-04-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-03-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-02-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-01-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2017-12-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2017-11-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2017-10-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2017-09-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2017-08-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2017-07-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2017-06-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2017-05-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2017-04-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2017-03-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2017-02-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2017-01-01',
                            'value' => rand(300, 1000),
                        ],
                    ],
                ],
                'baptisms' => [
                    'current_state' => [
                        'active_baptisms' => rand(300, 1000),
                        'all_baptisms' => rand(300, 1000),
                        'multiplying' => rand(300, 1000),
                    ],
                    'added' => [
                        'sixty_days' => [
                            [
                                'date' => '2018-12-15',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-12-14',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-12-13',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-12-12',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-12-11',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-12-10',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-12-09',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-12-08',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-12-07',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-12-06',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-12-05',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-12-04',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-12-03',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-12-02',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-12-01',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-11-30',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-11-29',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-11-28',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-11-27',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-11-26',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-11-25',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-11-24',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-11-23',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-11-22',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-11-21',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-11-20',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-11-19',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-11-18',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-11-17',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-11-16',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-11-15',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-11-14',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-11-13',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-11-12',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-11-11',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-11-10',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-11-09',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-11-08',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-11-07',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-11-06',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-11-05',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-11-04',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-11-03',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-11-02',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-11-01',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-10-31',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-10-30',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-10-29',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-10-28',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-10-27',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-10-26',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-10-25',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-10-24',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-10-23',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-10-22',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-10-21',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-10-20',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-10-19',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-10-18',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-10-17',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-10-16',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-10-15',
                                'value' => rand(300, 1000),
                            ],
                        ],
                        'twenty_four_months' => [
                            [
                                'date' => '2018-12-01',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-11-01',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-10-01',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-09-01',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-08-01',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-07-01',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-06-01',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-05-01',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-04-01',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-03-01',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-02-01',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-01-01',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2017-12-01',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2017-11-01',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2017-10-01',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2017-09-01',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2017-08-01',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2017-07-01',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2017-06-01',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2017-05-01',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2017-04-01',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2017-03-01',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2017-02-01',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2017-01-01',
                                'value' => rand(300, 1000),
                            ],
                        ],
                    ],
                    'highest_generation' => 6,
                    'generations' => [
                        [
                            'label' => 'Gen 1',
                            'value' => rand(300, 1000)
                        ],
                        [
                            'label' => 'Gen 2',
                            'value' => rand(300, 1000)
                        ],
                        [
                            'label' => 'Gen 3',
                            'value' => rand(300, 1000)
                        ],
                        [
                            'label' => 'Gen 4',
                            'value' => rand(300, 1000)
                        ],
                        [
                            'label' => 'Gen 5',
                            'value' => rand(300, 1000)
                        ],
                        [
                            'label' => 'Gen 6',
                            'value' => rand(300, 1000)
                        ]
                    ],
                ],
                'coaching' => [
                    'highest_generation' => 3,
                    'generations' => [
                        [
                            'label' => 'Gen 1',
                            'value' => rand(300, 1000)
                        ],
                        [
                            'label' => 'Gen 2',
                            'value' => rand(300, 1000)
                        ],
                        [
                            'label' => 'Gen 3',
                            'value' => rand(300, 1000)
                        ]
                    ],
                ],
            ],
            'groups' => [
                'current_state' => self::groups_current_state(),
                'by_types' => self::groups_by_type(),
                'added' => [ // measure the addition of groups over time
                             'sixty_days' => [
                                 [
                                     'date' => '2018-12-15',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-12-14',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-12-13',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-12-12',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-12-11',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-12-10',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-12-09',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-12-08',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-12-07',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-12-06',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-12-05',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-12-04',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-12-03',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-12-02',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-12-01',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-11-30',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-11-29',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-11-28',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-11-27',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-11-26',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-11-25',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-11-24',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-11-23',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-11-22',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-11-21',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-11-20',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-11-19',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-11-18',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-11-17',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-11-16',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-11-15',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-11-14',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-11-13',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-11-12',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-11-11',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-11-10',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-11-09',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-11-08',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-11-07',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-11-06',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-11-05',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-11-04',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-11-03',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-11-02',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-11-01',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-10-31',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-10-30',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-10-29',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-10-28',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-10-27',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-10-26',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-10-25',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-10-24',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-10-23',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-10-22',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-10-21',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-10-20',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-10-19',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-10-18',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-10-17',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-10-16',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-10-15',
                                     'value' => rand(300, 1000),
                                 ],
                             ],
                             'twenty_four_months' => [
                                 [
                                     'date' => '2018-12-01',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-11-01',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-10-01',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-09-01',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-08-01',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-07-01',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-06-01',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-05-01',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-04-01',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-03-01',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-02-01',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-01-01',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2017-12-01',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2017-11-01',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2017-10-01',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2017-09-01',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2017-08-01',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2017-07-01',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2017-06-01',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2017-05-01',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2017-04-01',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2017-03-01',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2017-02-01',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2017-01-01',
                                     'value' => rand(300, 1000),
                                 ],
                             ],
                ],
                'health' => [
                    [
                        'category' => 'Baptism',
                        'practicing' => rand(300, 1000),
                        'not_practicing' => rand(300, 1000),
                    ],
                    [
                        'category' => 'Bible Study',
                        'practicing' => rand(300, 1000),
                        'not_practicing' => rand(300, 1000),
                    ],
                    [
                        'category' => 'Communion',
                        'practicing' => rand(300, 1000),
                        'not_practicing' => rand(300, 1000),
                    ],
                    [
                        'category' => 'Fellowship',
                        'practicing' => rand(300, 1000),
                        'not_practicing' => rand(300, 1000),
                    ],
                    [
                        'category' => 'Giving',
                        'practicing' => rand(300, 1000),
                        'not_practicing' => rand(300, 1000),
                    ],
                    [
                        'category' => 'Prayer',
                        'practicing' => rand(300, 1000),
                        'not_practicing' => rand(300, 1000),
                    ],
                    [
                        'category' => 'Praise',
                        'practicing' => rand(300, 1000),
                        'not_practicing' => rand(300, 1000),
                    ],
                    [
                        'category' => 'Sharing',
                        'practicing' => rand(300, 1000),
                        'not_practicing' => rand(300, 1000),
                    ],
                    [
                        'category' => 'Leaders',
                        'practicing' => rand(300, 1000),
                        'not_practicing' => rand(300, 1000),
                    ],
                    [
                        'category' => 'Commitment',
                        'practicing' => rand(300, 1000),
                        'not_practicing' => rand(300, 1000),
                    ]
                ],
                'church_generations' => [
                    'highest_generation' => 4,
                    'generations' => [
                        [
                            'label' => 'Gen 1',
                            'value' => rand(300, 1000)
                        ],
                        [
                            'label' => 'Gen 2',
                            'value' => rand(300, 1000)
                        ],
                        [
                            'label' => 'Gen 3',
                            'value' => rand(300, 1000)
                        ],
                        [
                            'label' => 'Gen 4',
                            'value' => rand(300, 1000)
                        ]
                    ],
                ],
                'all_generations' => [
                    'highest_generation' => 7,
                    'generations' => [
                        [
                            'label' => 'Gen 1',
                            'value' => rand(300, 1000)
                        ],
                        [
                            'label' => 'Gen 2',
                            'value' => rand(300, 1000)
                        ],
                        [
                            'label' => 'Gen 3',
                            'value' => rand(300, 1000)
                        ],
                        [
                            'label' => 'Gen 4',
                            'value' => rand(300, 1000)
                        ],
                        [
                            'label' => 'Gen 5',
                            'value' => rand(300, 1000)
                        ],
                        [
                            'label' => 'Gen 6',
                            'value' => rand(300, 1000)
                        ],
                        [
                            'label' => 'Gen 7',
                            'value' => rand(300, 1000)
                        ],
                    ],
                ]
            ],
            'users' => [
                'current_state' => self::users_current_state(),
                'login_activity' => [
                    'sixty_days' => [
                        [
                            'date' => '2018-12-15',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-12-14',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-12-13',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-12-12',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-12-11',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-12-10',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-12-09',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-12-08',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-12-07',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-12-06',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-12-05',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-12-04',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-12-03',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-12-02',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-12-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-30',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-29',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-28',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-27',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-26',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-25',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-24',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-23',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-22',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-21',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-20',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-19',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-18',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-17',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-16',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-15',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-14',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-13',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-12',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-11',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-10',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-09',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-08',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-07',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-06',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-05',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-04',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-03',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-02',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-10-31',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-10-30',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-10-29',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-10-28',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-10-27',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-10-26',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-10-25',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-10-24',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-10-23',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-10-22',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-10-21',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-10-20',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-10-19',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-10-18',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-10-17',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-10-16',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-10-15',
                            'value' => rand(300, 1000),
                        ],
                    ],
                    'twenty_four_months' => [
                        [
                            'date' => '2018-12-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-10-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-09-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-08-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-07-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-06-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-05-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-04-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-03-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-02-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-01-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2017-12-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2017-11-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2017-10-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2017-09-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2017-08-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2017-07-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2017-06-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2017-05-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2017-04-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2017-03-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2017-02-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2017-01-01',
                            'value' => rand(300, 1000),
                        ],
                    ],
                ],
                'last_thirty_day_engagement' => [
                    [
                        'label' => 'Active',
                        'value' => rand(300, 1000),
                    ],
                    [
                        'label' => 'Inactive',
                        'value' => rand(300, 1000),
                    ]
                ]
            ],
            'locations' => [
                'countries' => [
                    [
                        'id' => $location_list[$location_id]['id'],
                        'name' => $location_list[$location_id]['name'],
                        'site_name' => $profile['partner_name'],
                        'contacts' => rand(300, 1000),
                        'groups' => rand(300, 1000),
                        'value' => 100,
                        'color' => 'red'
                    ]
                ],
                'current_state' => [
                    'active_locations' => rand(300, 1000),
                    'inactive_locations' => rand(300, 1000),
                    'all_locations' => rand(300, 1000),
                ],
                'list' => [
                    [
                        'location_name' => '',
                        'location_id' => '',
                        'parent_id' => '',
                        'geonameid' => '',
                        'longitude' => '',
                        'latitude' => '',
                        'total_contacts' => 0,
                        'total_groups' => 0,
                        'total_users' => 0,
                        'new_contacts' => 0,
                        'new_groups' => 0,
                        'new_users' => 0,
                    ],
                    [
                        'location_name' => '',
                        'location_id' => '',
                        'parent_id' => '',
                        'geonameid' => '',
                        'longitude' => '',
                        'latitude' => '',
                        'total_contacts' => 0,
                        'total_groups' => 0,
                        'total_users' => 0,
                        'new_contacts' => 0,
                        'new_groups' => 0,
                        'new_users' => 0,
                    ],
                    [
                        'location_name' => '',
                        'location_id' => '',
                        'parent_id' => '',
                        'geonameid' => '',
                        'longitude' => '',
                        'latitude' => '',
                        'total_contacts' => 0,
                        'total_groups' => 0,
                        'total_users' => 0,
                        'new_contacts' => 0,
                        'new_groups' => 0,
                        'new_users' => 0,
                    ],
                ],
            ],
            'date' => current_time( 'timestamp' ),
            'status' => 'OK',
        ];

        if ( $report_data ) {
            set_transient( 'dt_snapshot_report', $report_data, strtotime( 'tomorrow') );
            return $report_data;
        } else {
            return new WP_Error( __METHOD__, 'Failed to get report' );
        }
    }

    public static function contacts_current_state() {
        $data = [
            'all_contacts' => 0,
            'critical_path' => [],
        ];

        // Add critical path
        $critical_path = Disciple_Tools_Metrics_Hooks_Base::query_project_contacts_progress();
        foreach ( $critical_path as $path ) {
            $data['critical_path'][$path['key']] = $path;
        }

        // Add
        $data['status'] = self::get_contacts_status();

        $data['all_contacts'] = self::query( 'all_contacts' );

        return $data;
    }

    /**
     * Gets an array list of all contacts current status.
        [new] => 0
        [unassignable] => 0
        [unassigned] => 0
        [assigned] => 6
        [active] => 38
        [paused] => 5
        [closed] => 5
     *
     * @return array
     */
    public static function get_contacts_status() :array {
        $data = [];
        $contact_fields = Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings();
        $status_defaults = $contact_fields['overall_status']['default'];
        $current_state = self::query( 'contacts_current_state' );
        foreach( $status_defaults as $key => $status ) {
            $data[$key] = 0;
            foreach( $current_state as $state ) {
                if ( $state['status'] === $key ) {
                    $data[$key] = (int) $state['count'];
                }
            }
        }
        return $data;
    }

    /**
     * Gets an array of the current state of groups
     *
     * [active] => Array
            (
            [pre_group] => 3
            [group] => 0
            [church] => 3
            )

        [inactive] => Array
            (
            [pre_group] => 0
            [group] => 0
            [church] => 0
            )

        [total_active] => 6
        [all] => 6
     *
     * @return array
     */
    public static function groups_current_state() {
        $data = [
            'active' => [
                'pre_group' => 0,
                'group' => 0,
                'church' => 0,
            ],
            'inactive' => [
                'pre_group' => 0,
                'group' => 0,
                'church' => 0,
            ],
            'total_active' => 0, // all non-duplicate groups in the system active or inactive.
            'all' => 0,
        ];

        // Add types and status
        $types_and_status = self::query( 'groups_types_and_status' );
        foreach ( $types_and_status as $value ) {
            $value['type'] = str_replace( '-', '_', $value['type']);

            $data[$value['status']][$value['type']] = (int) $value['count'];

            if ( 'active' === $value['status'] ) {
                $data ['total_active'] = $data['total_active'] + (int) $value['count'];
            }
        }

        $data['all'] = self::query( 'all_groups' );

        return $data;
    }

    public static function groups_by_type() {
        $data = [];
        $items = ['pre-group', 'group', 'church'];

        $types_and_status = self::query( 'groups_types_and_status' );

        $keyed = [];
        foreach ( $types_and_status as $status ) {
            if ( 'active' === $status['status'] ) {
                $keyed[$status['type']] = $status;
            }
        }

        if ( isset( $keyed['pre-group'] ) ) {
            $data[] = [
                'name' => 'Pre-Group',
                'value' => $keyed['pre-group']['count'],
            ];
        } else {
            $data[] = [
                'name' => 'Pre-Group',
                'value' => 0,
            ];
        }

        if ( isset( $keyed['group'] ) ) {
            $data[] = [
                'name' => 'Group',
                'value' => $keyed['group']['count'],
            ];
        } else {
            $data[] = [
                'name' => 'Group',
                'value' => 0,
            ];
        }

        if ( isset( $keyed['church'] ) ) {
            $data[] = [
                'name' => 'Church',
                'value' => $keyed['church']['count'],
            ];
        } else {
            $data[] = [
                'name' => 'Church',
                'value' => 0,
            ];
        }

        return $data;
    }

    public static function users_current_state() {
        $data = [
            'total_users' => 0,
            'roles' => [
                'responders' => 0,
                'dispatchers' => 0,
                'multipliers' => 0,
                'strategists' => 0,
                'admins' => 0,
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


    public static function query( $type, $args = [] ) {
        global $wpdb;

        if ( empty( $type ) ) {
            return new WP_Error( __METHOD__, 'Required type is missing.' );
        }

        switch ( $type ) {

            case 'contacts_current_state':
                /**
                 * Returns status and count of contacts according to the overall status
                 * return array
                 */
                $results = $wpdb->get_results("
                SELECT
                  b.meta_value as status,
                  count(a.ID) as count
                FROM $wpdb->posts as a
                  JOIN $wpdb->postmeta as b
                    ON a.ID = b.post_id
                       AND b.meta_key = 'overall_status'
                WHERE a.post_status = 'publish'
                      AND a.post_type = 'contacts'
                      AND a.ID NOT IN (
                  SELECT bb.post_id
                  FROM $wpdb->postmeta as bb
                  WHERE meta_key = 'corresponds_to_user'
                        AND meta_value != 0
                  GROUP BY bb.post_id )
                GROUP BY b.meta_value
            ", ARRAY_A );
                break;

            case 'all_contacts':
                /**
                 * Returns single digit count of all contacts in the system.
                 * return int
                 */
                $results = $wpdb->get_var("
                    SELECT
                      count(a.ID) as count
                    FROM $wpdb->posts as a
                    WHERE a.post_status = 'publish'
                          AND a.post_type = 'contacts'
                          AND a.ID NOT IN (
                      SELECT bb.post_id
                      FROM $wpdb->postmeta as bb
                      WHERE meta_key = 'corresponds_to_user'
                            AND meta_value != 0
                      GROUP BY bb.post_id )
                ");
                if ( empty( $results ) ) {
                    $results = 0;
                }
                break;

            case 'all_groups':
                /**
                 * Returns single digit count of all groups in the system.
                 * return int
                 */
                $results = $wpdb->get_var("
                    SELECT
                      count(a.ID) as count
                    FROM $wpdb->posts as a
                    WHERE a.post_status = 'publish'
                          AND a.post_type = 'groups'
                ");
                if ( empty( $results ) ) {
                    $results = 0;
                }
                break;

            case 'groups_types_and_status':
                /**
                 * Returns the different types of groups and their count
                 *
                 *  pre-group	active	    5
                    pre-group	inactive	7
                    group	    active	    2
                    group	    inactive	1
                    church	    active	    9
                    church	    inactive	2
                 */
                $results = $wpdb->get_results( "
                    SELECT
                      c.meta_value as type,
                      b.meta_value as status,
                      count(a.ID)  as count
                    FROM $wpdb->posts as a
                      JOIN $wpdb->postmeta as b
                        ON a.ID = b.post_id
                           AND b.meta_key = 'group_status'
                      JOIN $wpdb->postmeta as c
                        ON a.ID = c.post_id
                           AND c.meta_key = 'group_type'
                    WHERE a.post_status = 'publish'
                          AND a.post_type = 'groups'
                    GROUP BY type, status
                    ORDER BY type DESC
                ", ARRAY_A );
                break;
        }

        return $results;
    }
}