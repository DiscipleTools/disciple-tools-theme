<?php

class Ga_Helper {

	const GA_DEFAULT_WEB_ID				 = "UA-0000000-0";
	const GA_STATISTICS_PAGE_URL			 = "admin.php?page=googleanalytics";
	const GA_SETTINGS_PAGE_URL			 = "admin.php?page=googleanalytics/settings";
	const GA_TRENDING_PAGE_URL			 = 'admin.php?page=googleanalytics/trending';
	const DASHBOARD_PAGE_NAME				 = "dashboard";
	const PHP_VERSION_REQUIRED			 = "5.2.17";
	const GA_WP_MODERN_VERSION			 = "4.1";
	const GA_TOOLTIP_TERMS_NOT_ACCEPTED	 = 'Please accept the terms to use this feature.';
	const GA_TOOLTIP_FEATURES_DISABLED	 = 'Click the Enable button at the top to start using this feature.';
	const GA_DEBUG_MODE					 = false;

	/**
	 * Init plugin actions.
	 *
	 */
	public static function init() {

		// Displays errors related to required PHP version
		if ( !self::is_php_version_valid() ) {
			add_action( 'admin_notices', 'DT_Ga_Admin::admin_notice_googleanalytics_php_version' );

			return false;
		}

		// Displays errors related to required WP version
		if ( !self::is_wp_version_valid() ) {
			add_action( 'admin_notices', 'DT_Ga_Admin::admin_notice_googleanalytics_wp_version' );

			return false;
		}

		if ( is_admin() ) {
			$admin_controller = new Ga_Admin_Controller();
			$admin_controller->handle_actions();
		}
	}

	/**
	 * Checks if current page is a WordPress dashboard.
	 * @return int
	 */
	public static function is_plugin_page() {
		$site = get_current_screen();

		return preg_match( '/' . GA_NAME . '/i', $site->base ) || preg_match( '/' . GA_NAME . '/i', $_SERVER[ 'REQUEST_URI' ] );
	}

	/**
	 * Checks if current page is a WordPress dashboard.
	 * @return number
	 */
	public static function is_dashboard_page() {
		$site = get_current_screen();

		return preg_match( '/' . self::DASHBOARD_PAGE_NAME . '/', $site->base );
	}


	/**
	 * Check whether the plugin is configured.
	 *
	 * @param String $web_id
	 *
	 * @return boolean
	 */
	public static function is_configured( $web_id ) {
		return ( $web_id !== self::GA_DEFAULT_WEB_ID ) && !empty( $web_id );
	}

	/**
	 * Checks whether users is authorized with Google.
	 *
	 * @return boolean
	 */
	public static function is_authorized($token) {
		return DT_Ga_Admin::api_client()->get_instance()->is_authorized($token);
	}

	/**
	 * Wrapper for WordPress method get_option
	 *
	 * @param string $name Option name
	 *
	 * @return NULL|mixed|boolean
	 */
	public static function get_option( $name ) {
		$opt = get_option( $name );

		return !empty( $opt ) ? $opt : null;
	}

	/**
	 * Wrapper for WordPress method update_option
	 *
	 * @param string $name
	 * @param mixed $value
	 *
	 * @return NULL|boolean
	 */
	public static function update_option( $name, $value ) {
		$opt = update_option( $name, $value );

		return !empty( $opt ) ? $opt : null;
	}

	/**
	 * Loads ga notice HTML code with given message included.
	 *
	 * @param string $message
	 * $param bool $cannot_activate Whether the plugin cannot be activated
	 *
	 * @return string
	 */
	public static function ga_oauth_notice( $message ) {
		return Ga_View_Core::load( 'ga_oauth_notice', array(
			'msg' => $message
		), true );
	}

	/**
	 * Displays notice following the WP style.
	 *
	 * @param $message
	 * @param string $type
	 * @param $is_dismissable
	 * @param $action
	 *
	 * @return string
	 */
	public static function ga_wp_notice( $message, $type = '', $is_dismissable = false, $action = array() ) {
		return Ga_View_Core::load( 'ga_wp_notice', array(
			'type'			 => empty( $type ) ? DT_Ga_Admin::NOTICE_WARNING : $type,
			'msg'			 => $message,
			'is_dismissable' => $is_dismissable,
			'action'		 => $action
		), true );
	}

	/**
	 * Adds percent sign to the given text.
	 *
	 * @param $text
	 *
	 * @return string
	 */
	public static function format_percent( $text ) {
		$text = self::add_plus( $text );

		return $text . '%';
	}

	/**
	 * Adds plus sign before number.
	 *
	 * @param $number
	 *
	 * @return string
	 */
	public static function add_plus( $number ) {
		if ( $number > 0 ) {
			return '+' . $number;
		}

		return $number;
	}

	/**
	 * Check whether current user has administrator privileges.
	 *
	 * @return bool
	 */
	public static function is_administrator() {
		if ( current_user_can( 'administrator' ) ) {
			return true;
		}

		return false;
	}

	public static function is_wp_version_valid() {
		$wp_version = get_bloginfo( 'version' );

		return version_compare( $wp_version, DT_Ga_Admin::MIN_WP_VERSION, 'ge' );
	}


	/**
	 * @return mixed
	 */
	public static function is_php_version_valid() {
		$p			 = '#(\.0+)+($|-)#';
		$ver1		 = preg_replace( $p, '', phpversion() );
		$ver2		 = preg_replace( $p, '', self::PHP_VERSION_REQUIRED );
		$operator	 = 'ge';
		$compare	 = isset( $operator ) ?
		version_compare( $ver1, $ver2, $operator ) :
		version_compare( $ver1, $ver2 );

		return $compare;
	}

	public static function get_current_url() {
		return $_SERVER[ 'REQUEST_URI' ];
	}

	public static function create_url( $url, $data = array() ) {
		return !empty( $data ) ? ( strstr( $url, '?' ) ? ( $url . '&' ) : ( $url . '?' ) ) . http_build_query( $data ) : $url;
	}

	public static function handle_url_message( $data ) {
		if ( !empty( $_GET[ 'ga_msg' ] ) ) {
			$invite_result = json_decode( base64_decode( $_GET[ 'ga_msg' ] ), true );
			if ( !empty( $invite_result[ 'status' ] ) && !empty( $invite_result[ 'message' ] ) ) {
				$data[ 'ga_msg' ] = Ga_Helper::ga_wp_notice( $invite_result[ 'message' ], $invite_result[ 'status' ], true );
			}
		}

		return $data;
	}

	/**
	 * Create base64 url message
	 *
	 * @param $msg
	 * @param $status
	 *
	 * @return string
	 */
	public static function create_url_msg( $msg, $status ) {
		$msg = array( 'status' => $status, 'message' => $msg );

		return base64_encode( json_encode( $msg ) );
	}

	public static function is_all_feature_disabled() {
		return false;
	}

	public static function are_features_enabled() {
		return true;
	}

	public static function is_wp_old() {
		return version_compare( get_bloginfo( 'version' ), self::GA_WP_MODERN_VERSION, 'lt' );
	}

	public static function should_load_ga_javascript( $web_property_id ) {
		return ( self::is_configured( $web_property_id ) && ( self::can_add_ga_code() || self::is_all_feature_disabled() ) );
	}


}
