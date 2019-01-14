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

        add_filter( 'site_link_type', [ $this, 'site_link_type' ], 10, 1 );
        add_filter( 'site_link_type_capabilities', [ $this, 'site_link_capabilities' ], 10, 1 );

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

    public static function api_report_by_date( $date ) {
        if ( ! current_user_can( 'network_dashboard_transfer' ) ) {
            return new WP_Error( __METHOD__, 'Network report permission error.' );
        }
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
        if ( true ) {
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