<?php

if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class Disciple_Tools_Security_Tab
 */
class Disciple_Tools_Security_Tab extends Disciple_Tools_Abstract_Menu_Base
{
    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    /**
     * Constructor function.
     *
     * @access  public
     * @since   0.1.0
     */
    public function __construct() {
        add_action( 'admin_menu', [ $this, 'add_submenu' ], 99 );
        add_action( 'dt_settings_tab_menu', [ $this, 'add_tab' ], 50, 1 ); // use the priority setting to control load order
        add_action( 'dt_settings_tab_content', [ $this, 'content' ], 99, 1 );


        parent::__construct();
    } // End __construct()


    public function add_submenu() {
        add_submenu_page( 'dt_options', __( 'Security', 'disciple_tools' ), __( 'Security', 'disciple_tools' ), 'manage_dt', 'dt_options&tab=security', [ 'Disciple_Tools_Settings_Menu', 'content' ] );
    }

    public function add_tab( $tab ) {
        echo '<a href="'. esc_url( admin_url() ).'admin.php?page=dt_options&tab=security" class="nav-tab ';
        if ( $tab == 'security' ) {
            echo 'nav-tab-active';
        }
        echo '">'. esc_attr__( 'Security', 'disciple_tools' ) .'</a>';
    }

    public function content( $tab ) {
        if ( 'security' == $tab ) {
            // security
            self::template( 'begin', 1 );

            $this->save_settings();
            $this->security_enable_box();


            self::template( 'end' );

            // usage
            self::template( 'begin', 1 );

            $this->security_usage_opt_in();


            self::template( 'end' );


            /* API Auth Whitelist  */
            self::template( 'begin', 1 );
            $this->process_dt_api_whitelist();
            $this->show_dt_api_whitelist();
            self::template( 'end' );
        }
    }

    public function security_enable_box() {
        $this->box( 'top', 'Enable and Configure Security Headers' );

        $xss_disabled = get_option( 'dt_disable_header_xss' );
        $referer_disabled = get_option( 'dt_disable_header_referer' );
        $content_type_disabled = get_option( 'dt_disable_header_content_type' );
        $strict_transport_disabled = get_option( 'dt_disable_header_strict_transport' );
        ?>
        <p>Here we set some security headers for the Theme. These are enabled by default. We recommend leaving them enabled unless you run into any issues.</p>

        <form method="POST" action="">
            <?php wp_nonce_field( 'security_headers', 'security_headers_nonce' ); ?>
            <table class="form-table">
                <tr>
                    <th>X-XSS-Protection</th>
                    <td><input name="xss" type="checkbox" value="1" <?php echo $xss_disabled ? '' : 'checked' ?>></td>
                    <td>Enable cross-site scripting filters.</td>
                </tr>
                <tr>
                    <th>Referrer-Policy</th>
                    <td><input name="referer" type="checkbox" value="1" <?php echo $referer_disabled ? '' : 'checked' ?>></td>
                    <td>Set Referrer Policy to "same-origin" to avoid leaking D.T activity</td>
                </tr>
                <tr>
                    <th>X-Content-Type-Options</th>
                    <td><input name="content_type" type="checkbox" value="1" <?php echo $content_type_disabled ? '' : 'checked' ?>></td>
                    <td>Stops a browser from trying to MIME-sniff the content type</td>
                </tr>
                <tr>
                    <th>Strict-Transport-Security</th>
                    <td><input name="strict_transport" type="checkbox" value="1" <?php echo $strict_transport_disabled ? '' : 'checked' ?>></td>
                    <td>Enforce the use of HTTPS.</td>
                </tr>


            </table>

            <button type="submit" class="button button-primary">Save Changes</button>
        </form>

        <?php
        $this->box( 'bottom' );
    }

    public function save_settings(){
        if ( !empty( $_POST ) ){
            if ( isset( $_POST['security_headers_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['security_headers_nonce'] ), 'security_headers' ) ) {
                update_option( 'dt_disable_header_xss', isset( $_POST['xss'] ) ? '0' : '1' );
                update_option( 'dt_disable_header_referer', isset( $_POST['referer'] ) ? '0' : '1' );
                update_option( 'dt_disable_header_content_type', isset( $_POST['content_type'] ) ? '0' : '1' );
                update_option( 'dt_disable_header_strict_transport', isset( $_POST['strict_transport'] ) ? '0' : '1' );
            }

            if ( isset( $_POST['usage_data_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['usage_data_nonce'] ), 'usage_data' ) ) {
                update_option( 'dt_disable_usage_data', isset( $_POST['usage'] ) ? '1' : '0' );
            }
        }
    }

    public function security_usage_opt_in() {
        $this->box( 'top', 'Usage Data' );

        $usage_class = new Disciple_Tools_Usage();
        $telemetry = $usage_class->telemetry();
        $telemetry_string = print_r( $telemetry, true );

        $disable_usage = get_option( 'dt_disable_usage_data' );
        ?>
        <p>
            Disciple.Tools is a free, open source software created by a community of Jesus followers who want to see the Great Commission fulfilled.
            We aspire to create a secure and redistributable system that can serve disciple making movements in even difficult and dangerous locations.
            Among the many deep security protections we have engineered into Disciple.Tools, we have also included the "opt-out" below, which is the ability
            to disconnect all telemetry data from the core Disciple.Tools development team.
        </p>
        <p>
            Telemetry assists the core development team to make decisions on directions for the software into the future. We collect a fraction of the data a
            normal software collects, but these few details help us understand the application and impact of the tool. All the data we collect is high level totals
            and has no personally identifiable data included.
        </p>
        <p>
            The telemetry data collected is as follows: (SYSTEM INFO) time as sha256 hash, site_id as sha256 hash, usage version, PHP version, WordPress version, if debug is being used,
            WordPress database version, site url, theme version, active plugins list, usage of Mapbox, usage of Google Geocoding, timestamp of usage report, (USAGE INFO) active contact count (int),
            total contacts count (int), contacts countries list (array of ids for countries that have contacts),
            active groups count (int), total groups count (int), group countries list (array of ids for countries that have groups), active churches count (int),
            total churches (int), church countries list (array of ids for countries that have churches), active users count (int), total users count (int),
            user languages list (array of language codes and totals), presence of demo data, and region of targeted focus.
        </p>
        <p>
            <a href="javascript:void(0);" onclick="jQuery('#telemetry_report').toggle();">Show current telemetry usage report</a><br>
            <div id="telemetry_report" style="display:none;">
                <div>Notice the absence of personally identifiable information. Even the site url is no more specific than is available in a SSL TCP/IP transfer packet.</div>
                <?php echo '<pre>' . esc_html( $telemetry_string ) . '</pre>'; ?>
            </div>
        </p>

        <form method="POST" action="">
            <?php wp_nonce_field( 'usage_data', 'usage_data_nonce' ); ?>
            <table class="form-table">
                <tr>
                    <th>Usage Data</th>
                    <td style="width:5%;"><input name="usage" type="checkbox" value="1" <?php echo ( $disable_usage ) ? 'checked' : '' ?>></td>
                    <td>Disable usage data. (i.e. Thank you for the free software, but I must withhold technical system statistics.)</td>
                </tr>
            </table>

            <button type="submit" class="button button-primary">Save Changes</button>
        </form>

        <?php
        $this->box( 'bottom' );
    }

    /** API Whitelist */
    public function process_dt_api_whitelist(){

        if ( isset( $_POST['dt_api_whitelist'] ) && isset( $_POST['dt_api_whitelist_nonce'] ) &&
            wp_verify_nonce( sanitize_key( wp_unslash( $_POST['dt_api_whitelist_nonce'] ) ), 'dt_api_whitelist' . get_current_user_id() ) ) {

            $api_whitelist = sanitize_textarea_field( wp_unslash( $_POST['dt_api_whitelist'] ) );

            // normalize all URLs to start with "wp-json/"
            // Regex handles:
            // - /wp-json/endpoint/v1
            // - wp-json/endpoint/v1
            // - /endpoint/v1
            // - endpoint/v1
            $re = '/^[\/]?(?:wp-json)?[\/]?([^\r\n]*)[\r]?$/m';

            preg_match_all( $re, $api_whitelist, $matches, PREG_SET_ORDER, 0 );
            if ( isset( $matches ) && count( $matches ) ) {
                $api_whitelist = array_map( function( $match ) {
                    return 'wp-json/' . $match[1];
                }, $matches );
            }

            update_option( 'dt_api_whitelist', $api_whitelist, true );
        }

    }

    public function show_dt_api_whitelist(){
        $this->box( 'top', 'API Whitelist' );

        $api_whitelist = get_option( 'dt_api_whitelist', [] );
        $textarea_value = join( PHP_EOL, $api_whitelist );
        ?>
        <form method="post" >
            <p><?php esc_html_e( 'Add the API endpoints that should not require authentication (1 per line).', 'disciple_tools' ) ?></p>
            <p><?php esc_html_e( 'Use the * character as a wildcard if you need to whitelist endpoints that pass parameters in the path.', 'disciple_tools' ) ?></p>
            <textarea
                name="dt_api_whitelist"
                style="width: 100%"
                rows="10"
                placeholder="wp-json/my-plugin/v1/my-endpoint/*"
            ><?php echo esc_html( $textarea_value ) ?></textarea>
            <?php wp_nonce_field( 'dt_api_whitelist' . get_current_user_id(), 'dt_api_whitelist_nonce' )?>
            <br>
            <button type="submit" class="button button-primary"><?php esc_html_e( 'Save Changes', 'disciple_tools' ) ?></button>
        </form>
        <?php
        $this->box( 'bottom' );
    }


}
Disciple_Tools_Security_Tab::instance();
