<?php
/**
 * Disciple_Tools_Location_Tools_Menu
 *
 * @class   Disciple_Tools_Location_Tools_Menu
 * @version 0.1.0
 * @since   0.1.0
 * @package Disciple_Tools_Tabs
 * @author  Chasm.Solutions
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * Class Disciple_Tools_Location_Tools_Menu
 */
class Disciple_Tools_Location_Tools_Menu
{

    public $path;

    /**
     * Disciple_Tools The single instance of Disciple_Tools.
     *
     * @var    object
     * @access private
     * @since  0.1.0
     */
    private static $_instance = null;

    /**
     * Main Disciple_Tools_Tabs Instance
     * Ensures only one instance of Disciple_Tools_Tabs is loaded or can be loaded.
     *
     * @since  0.1.0
     * @static
     * @see    disciple_tools()
     * @return Disciple_Tools_Location_Tools_Menu instance
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
        $this->path = plugin_dir_path( __DIR__ );
        add_action( 'admin_menu', [ $this, 'load_admin_menu_item' ] );
    } // End __construct()

    /**
     * Load Admin menu into Settings
     */
    public function load_admin_menu_item()
    {
        add_submenu_page( 'edit.php?post_type=locations', __( 'Import', 'disciple_tools' ), __( 'Import', 'disciple_tools' ), 'manage_dt', 'disciple_tools_locations', [ $this, 'page_content' ] );
    }

    /**
     * Builds the tab bar
     *
     * @since 0.1.0
     */
    public function page_content()
    {

        if ( !current_user_can( 'manage_dt' ) ) {
            wp_die( esc_html__( 'You do not have sufficient permissions to access this page.' ) );
        }

        /**
         * Begin Header & Tab Bar
         */
        if ( isset( $_GET["tab"] ) ) {
            $tab = sanitize_text_field( wp_unslash( $_GET["tab"] ) );
        } else {
            $tab = 'global';
        }

        echo '<div class="wrap">
            <h2>Import Locations</h2>
            <h2 class="nav-tab-wrapper">';

        echo '<a href="edit.php?post_type=locations&page=disciple_tools_locations&tab=global" class="nav-tab ';

        if ( $tab == 'global' ) {
            echo 'nav-tab-active';
        }
        echo '">Global</a>';

        echo '<a href="edit.php?post_type=locations&page=disciple_tools_locations&tab=usa" class="nav-tab ';
        if ( $tab == 'usa' ) {
            echo 'nav-tab-active';
        }
        echo '">USA</a>';

        echo '</h2>';

        // End Tab Bar

        /**
         * Begin Page Content
         */
        switch ( $tab ) {

            case "global":

                echo '<div class="wrap"><div id="poststuff"><div id="post-body" class="metabox-holder columns-2">';
                echo '<div id="post-body-content">';

                /* BOX */
                echo '<table class="widefat striped"><thead><th>Install</th></thead><tbody><tr><td>';

                /* Build content of box */
                require_once( 'admin-tab-global.php' );
                $object = new Disciple_Tools_Locations_Tab_Global();
                $object->process_install_country();
                $object->install_country(); // prints
                /* End build */

                echo '</td></tr></tbody></table>';

                echo '</div><!-- end post-body-content --><div id="postbox-container-1" class="postbox-container">';

                /* BOX */
                echo '<table class="widefat striped"><thead><th>Source</th></thead><tbody><tr><td>';
                $this->get_import_config_dropdown( 'mm_hosts' ); // prints
                echo '</td></tr></tbody></table><br>';
                $this->locations_currently_installed(); // prints

                echo '</div><!-- postbox-container 1 --><div id="postbox-container-2" class="postbox-container">';
                echo '</div><!-- postbox-container 2 --></div><!-- post-body meta box container --></div><!--poststuff end --></div><!-- wrap end -->';
                break;

            case "usa":
                echo '<div class="wrap"><div id="poststuff"><div id="post-body" class="metabox-holder columns-2">';
                echo '<div id="post-body-content">';

                /* BOX */
                echo '<table class="widefat striped"><thead><th>Install by State</th></thead><tbody><tr><td>';

                require_once( 'admin-tab-usa.php' );
                $object = new Disciple_Tools_Locations_Tab_USA(); // create object
                $object->process_install_us_state();
                $object->install_us_state(); // prints

                echo '</td></tr></tbody></table><br>';
                echo '</div><!-- end post-body-content --><div id="postbox-container-1" class="postbox-container">';

                /* BOX */
                echo '<table class="widefat striped"><thead><th>Instructions</th></thead><tbody><tr><td>';

                echo '</td></tr></tbody></table><br>';

                $this->usa_states_currently_installed(); // prints

                echo '</div><!-- postbox-container 1 --><div id="postbox-container-2" class="postbox-container">';
                echo '</div><!-- postbox-container 2 --></div><!-- post-body meta box container --></div><!--poststuff end --></div><!-- wrap end -->';
                break;

            default:
                break;
        }

        echo '</div>'; // end div class wrap

    }

    /**
     * @param $host string  Can be either 'kml_hosts' or 'mm_hosts'
     */
    public function get_import_config_dropdown( $host )
    {
        // get vars
        $option = $this->get_config_option();

        // update from post
        if ( isset( $_POST['_wpnonce'] ) ) {
            if ( wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), "get_import_config_dropdown_locations" ) ) {
                if ( isset( $_POST['change_host_source'] ) ) {
                    if ( isset( $_POST[ $host ] ) ) {
                        $option[ 'selected_' . $host ] = sanitize_text_field( wp_unslash( $_POST[ $host ] ) );
                        update_option( '_dt_locations_import_config', $option, false );
                    }
                }
            } else {
                wp_die( esc_html__( "Nonce could not be verified" ) );
            }
        }

        // create dropdown
        echo '<form method="post"><select name="' . esc_attr( $host ) . '" >';
        foreach ( $option[ $host ] as $key => $value ) {
            echo '<option value="' . esc_attr( $key ) . '" ';
            if ( $option[ 'selected_' . $host ] == $key ) {
                echo ' selected';
            }
            echo '>' . esc_html( $key ) . '</option>';
            wp_nonce_field( "get_import_config_dropdown_locations", "_wpnonce", true );
        }
        echo '</select> <button type="submit" name="change_host_source" value="true">Save</button></form>';
    }

    /**
     * @return mixed
     */
    public static function get_config_option()
    {
        $option = get_option( '_dt_locations_import_config' );
        $config = json_decode( file_get_contents( plugin_dir_path( __FILE__ ) . 'config.json' ), true );
        // check on option status
        if ( empty( $option ) || $option['version'] < $config['version'] ) { // check if option exists
            update_option( '_dt_locations_import_config', $config, false );
            $option = get_option( '_dt_locations_import_config' );
        }

        return $option;
    }

    /**
     * Prints
     */
    public function locations_currently_installed()
    {
        global $wpdb;
        $count = [];

        // Search for currently installed locations

        echo '<table class="widefat ">
                    <thead><th>Currently Installed</th></thead>
                    <tbody>
                        <tr>
                            <td>';
        // Total number of locations in database
        echo 'Total number of locations: <br>' . esc_html( wp_count_posts( 'locations' )->publish ) . '<br>';

        // Total number of countries
        $count['countries'] = $wpdb->get_var( "SELECT count(*) FROM $wpdb->posts WHERE post_type = 'locations' AND post_name LIKE '___'" );
        echo 'Total number of countries (admin0): <br>' . intval( $count['countries'] ) . '<br>';

        // Total number of admin1
        $count['admin1'] = $wpdb->get_var( "SELECT count(*) FROM $wpdb->posts WHERE post_type = 'locations' AND post_name LIKE '___-___'" );
        echo 'Total number of Admin1: <br>' . intval( $count['admin1'] ) . '<br>';

        // Total number of admin2
        $count['admin2'] = $wpdb->get_var( "SELECT count(*) FROM $wpdb->posts WHERE post_type = 'locations' AND post_name LIKE '___-___-___'" );
        echo 'Total number of Admin2: <br>' . intval( $count['admin2'] ) . '<br>';

        // Total number of admin3
        $count['admin3'] = $wpdb->get_var( "SELECT count(*) FROM $wpdb->posts WHERE post_type = 'locations' AND post_name LIKE '___-___-___-___'" );
        echo 'Total number of Admin3: <br>' . intval( $count['admin3'] ) . '<br>';

        // Total number of admin4
        $count['admin4'] = $wpdb->get_var( "SELECT count(*) FROM $wpdb->posts WHERE post_type = 'locations' AND post_name LIKE '___-___-___-___-___'" );
        echo 'Total number of Admin4: <br>' . intval( $count['admin4'] ) . '<br>';

        echo '      </td>
                        </tr>';

        echo '</tbody>
                </table>';
    }

    /**
     * Prints
     */
    public function usa_states_currently_installed()
    {
        global $wpdb;
        $count = [];

        // Search for currently installed locations

        echo '<table class="widefat ">
                    <thead><th>Currently Installed</th></thead>
                    <tbody>
                        <tr>
                            <td>';
        // Total number of locations in database
        echo 'Total number of locations: <br>' . esc_html( wp_count_posts( 'locations' )->publish ) . '<br>';

        // Total number of admin1
        $count['admin1'] = $wpdb->get_var( "SELECT count(*) FROM $wpdb->posts WHERE post_type = 'locations' AND post_name LIKE 'USA-___'" );
        echo 'Total number of States: <br>' . intval( $count['admin1'] ) . '<br>';

        // Total number of admin2
        $count['admin2'] = $wpdb->get_var( "SELECT count(*) FROM $wpdb->posts WHERE post_type = 'locations' AND post_name LIKE 'USA-___-___'" );
        echo 'Total number of Counties: <br>' . intval( $count['admin2'] ) . '<br>';

        // Total number of admin3
        $count['admin3'] = $wpdb->get_var( "SELECT count(*) FROM $wpdb->posts WHERE post_type = 'locations' AND post_name LIKE 'USA-___-___-%'" );
        echo 'Total number of Tracts: <br>' . intval( $count['admin3'] ) . '<br>';

        echo '      </td>
                        </tr>';

        echo '</tbody>
                </table>';
    }
}

/**
 * Creates a dropdown of the states with the state key as the value.
 *
 * @usage USA locations
 * @return string
 */
function dt_get_states_key_dropdown_not_installed()
{

    $dir_contents = dt_get_usa_meta();

    $dropdown = '<select name="states-dropdown">';

    // @codingStandardsIgnoreLine
    foreach( $dir_contents->USA_states as $value ) {
        $disabled = '';

        $dropdown .= '<option value="' . $value->key . '" ';
        if ( get_option( '_installed_us_county_' . $value->key ) ) {
            $dropdown .= ' disabled';
            $disabled = ' (Installed)';
        } elseif ( isset( $_POST['state_nonce'] ) ) {
            if ( wp_verify_nonce( sanitize_key( $_POST['state_nonce'] ), 'state_nonce_validate' ) ) {
                if ( isset( $_POST['states-dropdown'] ) && $_POST['states-dropdown'] == $value->key ) {
                    $dropdown .= ' selected';
                }
            } else {
                wp_die( esc_html__( "Nonce could not be validated" ) );
            }
        }
        $dropdown .= '>' . $value->name . $disabled;
        $dropdown .= '</option>';
    }
    $dropdown .= '</select>';

    return $dropdown;
}

/**
 * Creates a dropdown of the states with the state key as the value.
 *
 * @usage USA locations
 * @return string
 */
function dt_get_states_key_dropdown_installed()
{

    $dir_contents = dt_get_usa_meta(); // get directory & build dropdown

    $dropdown = '<select name="states-dropdown">';

    // @codingStandardsIgnoreLine
    foreach( $dir_contents->USA_states as $value ) {
        $disabled = '';

        $dropdown .= '<option value="' . $value->key . '" ';
        if ( !get_option( '_installed_us_county_' . $value->key ) ) {
            $dropdown .= ' disabled';
            $disabled = ' (Not Installed)';
        } elseif ( isset( $_POST['state_nonce'] ) ) {
            if ( wp_verify_nonce( sanitize_key( $_POST['state_nonce'] ), 'state_nonce_validate' ) ) {
                if ( isset( $_POST['states-dropdown'] ) && $_POST['states-dropdown'] == $value->key ) {
                    $dropdown .= ' selected';
                }
            } else {
                wp_die( esc_html__( "Nonce could not be validated" ) );
            }
        }
        $dropdown .= '>' . $value->name . $disabled;
        $dropdown .= '</option>';
    }
    $dropdown .= '</select>';

    return $dropdown;
}

/**
 * Get the master json file with USA states and counties names, ids, and file locations.
 *
 * @usage USA locations
 * @return array|mixed|object
 */
function dt_get_usa_meta()
{
    return json_decode( file_get_contents( plugin_dir_path( __FILE__ ) . 'json/usa-meta.json' ) );
}

/**
 * Get the master list of countries for omega zones including country abbreviation, country name, and zone.
 *
 * @param string $admin
 *
 * @return array
 */
function dt_get_oz_country_list( $admin = 'cnty' )
{

    switch ( $admin ) {
        case 'cnty':
            $result = json_decode( file_get_contents( plugin_dir_path( __FILE__ ) . 'json/oz/oz_cnty.json' ) );

            return $result->RECORDS; // @codingStandardsIgnoreLine
            break;
        case 'admin1':
            $result = json_decode( file_get_contents( plugin_dir_path( __FILE__ ) . 'json/oz/oz_admin1.json' ) );

            return $result->RECORDS; // @codingStandardsIgnoreLine
            break;
        case 'admin2':
            $result = json_decode( file_get_contents( plugin_dir_path( __FILE__ ) . 'json/oz/oz_admin2.json' ) );

            return $result->RECORDS; // @codingStandardsIgnoreLine
            break;
        case 'admin3':
            $result = json_decode( file_get_contents( plugin_dir_path( __FILE__ ) . 'json/oz/oz_admin3.json' ) );

            return $result->RECORDS; // @codingStandardsIgnoreLine
            break;
        case 'admin4':
            $result = json_decode( file_get_contents( plugin_dir_path( __FILE__ ) . 'json/oz/oz_admin4.json' ) );

            return $result->RECORDS; // @codingStandardsIgnoreLine
            break;
        default:
            return [];
            break;
    }
}

/**
 * Gets the meta information for a polygon or array of polygons
 *
 * @usage USA locations
 *
 * @param  $geoid (int) Can be full 9 digit geoid or 5 digit state/county code
 *
 * @return array
 */
function dt_get_coordinates_meta( $geoid )
{
    global $wpdb;

    //* query */
    $county_coords = $wpdb->get_results( $wpdb->prepare(
        "SELECT
            meta_value
        FROM
            `$wpdb->postmeta`
        WHERE
            meta_key LIKE %s",
        $wpdb->esc_like( "polygon_$geoid" ) . "%"
    ), ARRAY_A );

    /* build full json of coodinates*/
    $rows = count( $county_coords );
    $string = '[';
    $i = 0;
    foreach ( $county_coords as $value ) {
        $string .= $value['meta_value'];
        if ( $rows > $i + 1 ) {
            $string .= ',';
        }
        $i++;
    }
    $string .= ']';

    $coords_objects = json_decode( $string );

    /* set values */
    $high_lng_e = -9999999; //will hold max val
    $high_lat_n = -9999999; //will hold max val
    $low_lng_w = 9999999; //will hold max val
    $low_lat_s = 9999999; //will hold max val

    /* filter for high and lows*/
    foreach ( $coords_objects as $coords ) {
        foreach ( $coords as $k => $v ) {
            if ( $v->lng > $high_lng_e ) {
                $high_lng_e = $v->lng;
            }
            if ( $v->lng < $low_lng_w ) {
                $low_lng_w = $v->lng;
            }
            if ( $v->lat > $high_lat_n ) {
                $high_lat_n = $v->lat;
            }
            if ( $v->lat < $low_lat_s ) {
                $low_lat_s = $v->lat;
            }
        }
    }

    // calculate centers
    $lng_size = $high_lng_e - $low_lng_w;
    $half_lng_difference = $lng_size / 2;
    $center_lng = $high_lng_e - $half_lng_difference;

    $lat_size = $high_lat_n - $low_lat_s;
    $half_lat_difference = $lat_size / 2;
    $center_lat = $high_lat_n - $half_lat_difference;

    // get zoom level
    if ( $lat_size > 3 || $lng_size > 3 ) {
        $zoom = 6;
    } elseif ( $lat_size > 2 || $lng_size > 2 ) {
        $zoom = 7;
    } elseif ( $lat_size > 1 || $lng_size > 1 ) {
        $zoom = 8;
    } elseif ( $lat_size > .4 || $lng_size > .4 ) {
        $zoom = 9;
    } elseif ( $lat_size > .2 || $lng_size > .2 ) {
        $zoom = 10;
    } elseif ( $lat_size > .1 || $lng_size > .1 ) {
        $zoom = 11;
    } elseif ( $lat_size > .07 || $lng_size > .07 ) {
        $zoom = 12;
    } elseif ( $lat_size > .01 || $lng_size > .01 ) {
        $zoom = 13;
    } else {
        $zoom = 14;
    }

    $meta = [ "center_lng" => (float) $center_lng, "center_lat" => (float) $center_lat, "ne" => $high_lat_n . ',' . $high_lng_e, "sw" => $low_lat_s . ',' . $low_lng_w, "zoom" => (float) $zoom ];

    return $meta;
}

/**
 * Get the full country name from key
 *
 * @param $key
 *
 * @return mixed
 */
function dt_locations_match_country_to_key( $key )
{

    $countries = dt_get_oz_country_list();

    foreach ( $countries as $country ) {
        if( $country->CntyID == $key ) { // @codingStandardsIgnoreLine
            return $country->Cnty_Name;
        }
    }

    return false;
}
