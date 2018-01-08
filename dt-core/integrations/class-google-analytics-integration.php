<?php
/**
 * Google Analytics Integration Class
 *
 * @since 0.1.0
 */


/**
 * Class DT_Ga_Admin
 */

class DT_Ga_Admin
{

    //stores the selected account id
    const GA_VERSION_OPTION_NAME = 'googleanalytics_version';
    const GA_OAUTH_AUTH_CODE_OPTION_NAME = 'googleanalytics_oauth_auth_code';
    //stores the access token and the refresh token
    const GA_OAUTH_AUTH_TOKEN_OPTION_NAME = 'googleanalytics_oauth_auth_token';
    //manually not used'
    const MIN_WP_VERSION = '3.8';
    const NOTICE_SUCCESS = 'success';
    const NOTICE_WARNING = 'warning';
    const NOTICE_ERROR = 'error';
    const GA_HEARTBEAT_API_CACHE_UPDATE = false;

    const GA_ACCOUNT_AND_DATA_ARRAY = 'googleanalytics_accounts_and_data';
    const GA_SELECTED_VIEWS = 'googleanalytics_selected_views';


    private static $_instance = null;

    /**
     * @return \DT_Ga_Admin|null
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    /**
     * Instantiate API client.
     *
     * @param string $type
     *
     * @return \Ga_Lib_Api_Client|null
     */
    public static function api_client( $type = '' )
    {
        return Ga_Lib_Google_Api_Client::get_instance();
    }

    /**
     * Initializes plugin's options during plugin activation process.
     */
    public static function activate_googleanalytics()
    {
        add_option( self::GA_ACCOUNT_AND_DATA_ARRAY, wp_json_encode( [] ) );
        add_option( self::GA_SELECTED_VIEWS, wp_json_encode( [] ) );
        add_option( self::GA_VERSION_OPTION_NAME );
        add_option( self::GA_OAUTH_AUTH_CODE_OPTION_NAME );
        add_option( self::GA_OAUTH_AUTH_TOKEN_OPTION_NAME );
        Ga_Cache::add_cache_options();
    }

    /**
     * Deletes plugin's options during plugin activation process.
     */

    public static function deactivate_googleanalytics()
    {
        delete_option( self::GA_ACCOUNT_AND_DATA_ARRAY );
        delete_option( self::GA_SELECTED_VIEWS );
        delete_option( self::GA_OAUTH_AUTH_CODE_OPTION_NAME );
        delete_option( self::GA_OAUTH_AUTH_TOKEN_OPTION_NAME );
        Ga_Cache::delete_cache_options();
    }

    /**
     * Deletes plugin's options during plugin uninstallation process.
     */
    public static function uninstall_googleanalytics()
    {
        delete_option( self::GA_VERSION_OPTION_NAME );
    }


    /**
     * Init plugin actions.
     */
    public function init_admin() {

        if ( is_admin() ) {
            self::add_filters();
            self::add_actions();

            self::init_oauth();

        }
    }

    /**
     * DT_Ga_Admin constructor.
     */
    public function __construct() {
        $this->init_admin();
    }

    /**
     * @param $new_value
     * @param $old_value
     *
     * @return false|mixed|string
     */
    public static function preupdate_selected_views( $new_value, $old_value )
    {
        $data = json_decode( get_option( self::GA_ACCOUNT_AND_DATA_ARRAY, [] ) );
        foreach ($data as $account_email => $account){
            foreach ($account->account_summaries as $account_summary){
                foreach ($account_summary->webProperties as $property){
                    foreach ($property->profiles as $profile){
                        if (array_key_exists( $profile->id, $new_value )){
                            $profile->include_in_stats = true;
                        } else {
                            $profile->include_in_stats = false;
                        }
                    }
                }
            }
        }
        update_option( self::GA_ACCOUNT_AND_DATA_ARRAY, json_encode( $data ) );


        return wp_json_encode( $new_value );
    }



    /**
     * Registers plugin's settings.
     */
    public static function admin_init_googleanalytics()
    {
        register_setting( GA_NAME, self::GA_SELECTED_VIEWS );
        register_setting( GA_NAME, self::GA_OAUTH_AUTH_CODE_OPTION_NAME );
    }

    /**
     * Prepares and displays plugin's settings page.
     *
     * @return bool
     */
    public static function options_page_googleanalytics()
    {

        if ( !Ga_Helper::is_wp_version_valid() || !Ga_Helper::is_php_version_valid()) {
            return false;
        }

        /**
         * Keeps data to be extracted as variables in the view.
         *
         * @var array $data
         */
        $data = [];
        $data[ self::GA_ACCOUNT_AND_DATA_ARRAY ] = json_decode( get_option( self::GA_ACCOUNT_AND_DATA_ARRAY, "[]" ), true );

        foreach ($data[ self::GA_ACCOUNT_AND_DATA_ARRAY ] as $account_email => $account){
            if ( !Ga_Helper::is_authorized( $account['token'] )){
                foreach ($account['account_summaries'] as $account_summary){
                    $account_summary['reauth'] = true;
                    //                    foreach ($account_summary->webProperties as $property){
                    //                        foreach ($property->profiles as $profile){
                    //                        }
                    //                    }
                }
            };
        }
        $data['popup_url'] = self::get_auth_popup_url();

        if ( !empty( $_GET['err'] )) {
            switch ($_GET['err']) {
                case 1:
                    $data['error_message'] = Ga_Helper::ga_oauth_notice( 'There was a problem with Google Oauth2 authentication process.' );
                    break;
            }
        }
        Ga_View_Core::load(
            'page', [
            'data' => $data,
            'tooltip' => ''
            ]
        );

        self::display_api_errors();

        return true;
    }

    /**
     * Prepares and returns a plugin's URL to be opened in a popup window
     * during Google authentication process.
     *
     * @return mixed
     */
    public static function get_auth_popup_url()
    {
        return admin_url( Ga_Helper::create_url( Ga_Helper::GA_SETTINGS_PAGE_URL, [Ga_Controller_Core::ACTION_PARAM_NAME => 'ga_action_auth'] ) );
    }


    /**
     * Adds JS scripts for the settings page.
     */
    public static function enqueue_ga_scripts()
    {
        wp_register_script(
            GA_NAME . '-page-js', GA_PLUGIN_URL . '/js/' . GA_NAME . '_page.js', [
            'jquery'
            ]
        );
        wp_enqueue_script( GA_NAME . '-page-js' );
    }

    /**
     * Adds CSS plugin's scripts.
     */
    public static function enqueue_ga_css()
    {
        wp_register_style( GA_NAME . '-css', GA_PLUGIN_URL . '/css/' . GA_NAME . '.css', false, null, 'all' );
        wp_register_style( GA_NAME . '-additional-css', GA_PLUGIN_URL . '/css/ga_additional.css', false, null, 'all' );
        wp_enqueue_style( GA_NAME . '-css' );
        wp_enqueue_style( GA_NAME . '-additional-css' );
        if (Ga_Helper::is_wp_old()) {
            wp_register_style( GA_NAME . '-old-wp-support-css', GA_PLUGIN_URL . '/css/ga_old_wp_support.css', false, null, 'all' );
            wp_enqueue_style( GA_NAME . '-old-wp-support-css' );
        }
        wp_register_style( GA_NAME . '-modal-css', GA_PLUGIN_URL . '/css/ga_modal.css', false, null, 'all' );
        wp_enqueue_style( GA_NAME . '-modal-css' );
    }


    /**
     * Enqueues plugin's JS and CSS scripts.
     */
    public static function enqueue_scripts()
    {
        if (Ga_Helper::is_plugin_page()) {
            wp_register_script(
                GA_NAME . '-js', GA_PLUGIN_URL . '/js/' . GA_NAME . '.js', [
                'jquery'
                ]
            );
            wp_enqueue_script( GA_NAME . '-js' );

            self::enqueue_ga_css();
        }

        if (Ga_Helper::is_plugin_page()) {
            self::enqueue_ga_scripts();
        }
    }


    /**
     * Shows plugin's notice on the admin area.
     */
    public static function admin_notice_googleanalytics()
    {
        if ( !empty( $_GET['settings-updated'] ) && Ga_Helper::is_plugin_page()) {
            // @codingStandardsIgnoreLine
            echo Ga_Helper::ga_wp_notice( _( 'Settings saved' ), self::NOTICE_SUCCESS );
        }
    }


    /**
     * Adds plugin's actions
     */
    public static function add_actions()
    {
        add_action( 'admin_init', 'DT_Ga_Admin::admin_init_googleanalytics' );
        add_action( 'admin_enqueue_scripts', 'DT_Ga_Admin::enqueue_scripts' );
        add_action( 'wp_ajax_ga_ajax_data_change', 'DT_Ga_Admin::ga_ajax_data_change' );
        add_action( 'heartbeat_tick', 'DT_Ga_Admin::run_heartbeat_jobs' );
    }

    /**
     * Runs jobs
     *
     * @param $response
     * @param $screen_id
     */
    public static function run_heartbeat_jobs( $response, $screen_id = '' )
    {

        if (DT_Ga_Admin::GA_HEARTBEAT_API_CACHE_UPDATE) {
            // Disable cache for ajax request
            self::api_client()->set_disable_cache( true );

            // Try to regenerate cache if needed
            //            self::generate_stats_data();
        }
    }

    /**
     * Adds plugin's filters
     */
    public static function add_filters()
    {
        add_filter( 'plugin_action_links', 'DT_Ga_Admin::ga_action_links', 10, 5 );
    }

    /**
     * Adds new action links on the plugin list.
     *
     * @param $actions
     * @param $plugin_file
     *
     * @return mixed
     */
    public static function ga_action_links( $actions, $plugin_file )
    {

        if (basename( $plugin_file ) == GA_NAME . '.php') {
            array_unshift( $actions, '<a href="' . esc_url( get_admin_url( null, Ga_Helper::GA_SETTINGS_PAGE_URL ) ) . '">' . esc_html__( 'Settings' ) . '</a>' );
        }

        return $actions;
    }

    /**
     * @return bool
     */
    public static function init_oauth()
    {

        $code = Ga_Helper::get_option( self::GA_OAUTH_AUTH_CODE_OPTION_NAME );

        if ( !empty( $code )) {
            Ga_Helper::update_option( self::GA_OAUTH_AUTH_CODE_OPTION_NAME, "" );

            // Get access token
            $response = self::api_client()->call( 'ga_auth_get_access_token', $code );
            if (empty( $response )) {
                return false;
            }
            $param = '';

            $token = self::parse_access_token( $response );
            if (empty( $token )) {
                $param = '&err=1';
            } else {
                $account_summaries = self::api_client()->call( 'ga_api_account_summaries', [ $token ] );

                self::save_accounts( $token, $account_summaries->getData() );
            }

            wp_redirect( admin_url( Ga_Helper::GA_SETTINGS_PAGE_URL . $param ) );
        } else {
            return false;
        }

    }

    /**
     * Save analytics accounts data
     *
     * @param $token
     * @param $account_summaries
     */
    public static function save_accounts( $token, $account_summaries ){
        $array = json_decode( get_option( self::GA_ACCOUNT_AND_DATA_ARRAY, [] ), true );
        $return = [];
        $return['token'] = $token;
        $return['account_summaries'] = [];

        if ( !empty( $account_summaries['items'] )) {
            foreach ($account_summaries['items'] as $item) {
                $tmp = [];
                $tmp['id'] = $item['id'];
                $tmp['name'] = $item['name'];
                if (is_array( $item['webProperties'] )) {
                    foreach ($item['webProperties'] as $property) {
                        $profiles = [];
                        if (is_array( $property['profiles'] )) {
                            foreach ($property['profiles'] as $profile) {
                                $profiles[] = [
                                    'id' => $profile['id'],
                                    'name' => $profile['name']
                                ];
                            }
                        }

                        $tmp['webProperties'][] = [
                            'webPropertyId' => $property['id'],
                            'name' => $property['name'],
                            'profiles' => $profiles
                        ];
                    }
                }
                $return['account_summaries'][] = $tmp;
            }

            $array[ $account_summaries['username'] ] = $return;
            update_option( self::GA_ACCOUNT_AND_DATA_ARRAY, json_encode( $array ) );
        }

    }

    /**
     * @param        $response
     * @param string $refresh_token
     *
     * @return bool
     */
    public static function parse_access_token( $response, $refresh_token = '' )
    {
        $access_token = $response->getData();
        if ( !empty( $access_token )) {
            $access_token['created'] = time();
        } else {
            return false;
        }

        if ( !empty( $refresh_token )) {
            $access_token['refresh_token'] = $refresh_token;
        }
        return $access_token;

    }

    /**
     * @param $response
     * @param $token
     *
     * @return bool
     */
    public static function save_access_token( $response, $token ){
        if (isset( $token['account_id'] )){
            $new_token = self::parse_access_token( $response );
            $array = json_decode( get_option( self::GA_ACCOUNT_AND_DATA_ARRAY, [] ), true );
            foreach ($array as $email => $account){
                foreach ($account['account_summaries'] as $account_summary){
                    if ($account_summary['id'] === $token["account_id"]){
                        $account['token'] = $new_token;
                    }
                }
            }
            update_option( self::GA_ACCOUNT_AND_DATA_ARRAY, json_encode( $array ) );
            return $new_token;
        }
    }

    /**
     * Displays API error messages.
     */
    public static function display_api_errors( $alias = '' )
    {
        $errors = self::api_client( $alias )->get_errors();
        if ( !empty( $errors )) {
            foreach ($errors as $error) {
                // @codingStandardsIgnoreLine
                echo Ga_Notice::get_message( $error );
            }
        }
    }

    /**
     * @param $last_report
     *
     * @return array
     */
    public static function get_report_data( $last_report ){

        $data = json_decode( get_option( self::GA_ACCOUNT_AND_DATA_ARRAY, "[]" ), true );
        $selected_views = [];

        $website_unique_visits = [];
        foreach ($data as $account_email => $account){
            foreach ($account['account_summaries'] as $account_summary){
                $account_summary['reauth'] = true;
                foreach ($account_summary['webProperties'] as $property){
                    foreach ($property['profiles'] as $profile){
                        if (isset( $profile['include_in_stats'] ) && $profile['include_in_stats'] ==true){
                            $selected_views[] = [
                                'account_id'        => $account_summary['id'],
                                'web_property_id'    => $property['webPropertyId'],
                                'view_id'            => $profile['id'],
                                'token'             => $account['token'],
                                'url'               => $property['name']
                            ];
                        }
                    }
                }
            };
        }

        $last_report = new DateTime( $last_report );
        $today = new DateTime();
        $interval = $last_report->diff( $today );

        $datys_ago = $interval->format( '%adaysago' );

        foreach ($selected_views as $selected){
            $query_params = Ga_Stats::get_query( 'report', $selected['view_id'], $datys_ago );
            $query_params['token'] = $selected['token'];
            $query_params['token']['account_id'] = $selected['account_id'];
            $stats_data = self::api_client()->call(
                'ga_api_data', [
                $query_params
                ]
            );
            $report = !empty( $stats_data ) ? Ga_Stats::get_report( $stats_data->getData() ) : [];
            $website_unique_visits[ $selected['url'] ] = $report;
        }

        return $website_unique_visits;
    }
}
