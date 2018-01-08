<?php

class Ga_Lib_Google_Api_Client extends Ga_Lib_Api_Client {

	static $instance = null;

	const OAUTH2_REVOKE_ENDPOINT                    = 'https://accounts.google.com/o/oauth2/revoke';
	const OAUTH2_TOKEN_ENDPOINT                     = 'https://accounts.google.com/o/oauth2/token';
	const OAUTH2_AUTH_ENDPOINT                      = 'https://accounts.google.com/o/oauth2/auth';
	const OAUTH2_FEDERATED_SIGNON_CERTS_ENDPOINT    = 'https://www.googleapis.com/oauth2/v1/certs';
	const GA_ACCOUNT_SUMMARIES_ENDPOINT             = 'https://www.googleapis.com/analytics/v3/management/accountSummaries';
    const GA_DATA_ENDPOINT                          = 'https://analyticsreporting.googleapis.com/v4/reports:batchGet';
	const OAUTH2_CALLBACK_URI                       = 'urn:ietf:wg:oauth:2.0:oob';

	const USE_CACHE = true;/**
 * Created by IntelliJ IDEA.
 * User: jd
 * Date: 4/17/17
 * Time: 3:56 PM
 */

	private $disable_cache = false;

	/**
	 * Pre-defined API credentials.
	 *
	 * @var array
	 */
//	private $config = array(
//		'access_type'      => 'offline',
//		'application_name' => 'Google Analytics',
//		'client_id'        => '207216681371-433ldmujuv4l0743c1j7g8sci57cb51r.apps.googleusercontent.com',
//		'client_secret'    => 'y0B-K-ODB1KZOam50aMEDhyc',
//		'scopes'           => array( 'https://www.googleapis.com/auth/analytics.readonly' ),
//		'approval_prompt'  => 'force'
//	);
    private $config = array(
        'access_type'      => 'offline',
        'application_name' => 'disciple-tools-analytics',
        'client_id'        => '2811303664-nah7tp76gcag3a8tqmpbjpi2uclnu9s3.apps.googleusercontent.com',
        'client_secret'    => '0SIt_In76P3SM8RznEEVPcod',
        'scopes'           => array( 'https://www.googleapis.com/auth/analytics.readonly' ),
        'approval_prompt'  => 'force'
    );

	/**
	 * Keeps Access Token information.
	 *
	 * @var array
	 */
//	private $token;

	private function __construct() {
	}

	/**
	 * Returns API client instance.
	 *
	 * @return Ga_Lib_Api_Client|null
	 */
	public static function get_instance() {
		if ( self::$instance === null ) {
			self::$instance = new Ga_Lib_Google_Api_Client();
		}

		return self::$instance;
	}

	public function set_disable_cache($value) {
		$this->disable_cache = $value;
	}

	function call_api_method( $callback, $args ) {
		$callback = array( get_class( $this ), $callback );
		if ( is_callable( $callback ) ) {
			try {
				if ( ! empty( $args ) ) {
					if ( is_array( $args ) ) {
						return call_user_func_array( $callback, $args );
					} else {
						return call_user_func_array( $callback, array( $args ) );
					}
				} else {
					return call_user_func( $callback );
				}

			} catch ( Ga_Lib_Api_Request_Exception $e ) {
				throw new Ga_Lib_Google_Api_Client_Exception( $e->getMessage() );
			}
		} else {
			throw new Ga_Lib_Google_Api_Client_Exception( '[' . get_class( $this ) . ']Unknown method: ' . print_r( $callback,
					true ) );
		}
	}

//	/**
//	 * Sets access token.
//	 *
//	 * @param $token
//	 */
//	public function set_access_token( $token ) {
//		$this->token = $token;
//	}

	/**
	 * Returns Google Oauth2 redirect URL.
	 *
	 * @return string
	 */
	private function get_redirect_uri() {
		return self::OAUTH2_CALLBACK_URI;
	}

	/**
	 * Creates Google Oauth2 authorization URL.
	 *
	 * @return string
	 */
	public function create_auth_url() {
		$params = array(
			'response_type'   => 'code',
			'redirect_uri'    => $this->get_redirect_uri(),
			'client_id'       => urlencode( $this->config['client_id'] ),
			'scope'           => implode( " ", $this->config['scopes'] ),
			'access_type'     => urlencode( $this->config['access_type'] ),
			'approval_prompt' => urlencode( $this->config['approval_prompt'] )
		);

		return self::OAUTH2_AUTH_ENDPOINT . "?" . http_build_query( $params );
	}

	/**
	 * Sends request for Access Token during Oauth2 process.
	 *
	 * @param $access_code
	 *
	 * @return Ga_Lib_Api_Response Returns response object
	 */
	private function ga_auth_get_access_token( $access_code ) {
		$request = array(
			'code'          => $access_code,
			'grant_type'    => 'authorization_code',
			'redirect_uri'  => $this->get_redirect_uri(),
			'client_id'     => $this->config['client_id'],
			'client_secret' => $this->config['client_secret']
		);
		try {
			$response = Ga_Lib_Api_Request::get_instance()->make_request( self::OAUTH2_TOKEN_ENDPOINT,
				$request, false, true);
		} catch ( Ga_Lib_Api_Request_Exception $e ) {
			throw new Ga_Lib_Google_Api_Client_AuthCode_Exception( $e->getMessage() );
		}

		return new Ga_Lib_Api_Response( $response );
	}

	/**
	 * Sends request to refresh Access Token.
	 *
	 * @param $refresh_token
	 *
	 * @return Ga_Lib_Api_Response
	 */
	private function ga_auth_refresh_access_token( $refresh_token ) {
		$request = array(
			'refresh_token' => $refresh_token,
			'grant_type'    => 'refresh_token',
			'client_id'     => $this->config['client_id'],
			'client_secret' => $this->config['client_secret']
		);

		try {
		$response = Ga_Lib_Api_Request::get_instance()->make_request( self::OAUTH2_TOKEN_ENDPOINT,
			$request, false, true );
		} catch (Ga_Lib_Api_Request_Exception $e) {
			throw new Ga_Lib_Google_Api_Client_RefreshToken_Exception( $e->getMessage() );
		}

		return new Ga_Lib_Api_Response( $response );
	}

	/**
	 * Get list of the analytics accounts.
	 *
	 * @return Ga_Lib_Api_Response Returns response object
	 */

	/**
	 * @param $token1, array()
	 * @return Ga_Lib_Api_Response
	 * @throws Ga_Lib_Api_Client_Exception
	 * @throws Ga_Lib_Google_Api_Client_AccountSummaries_Exception
	 */
	private function ga_api_account_summaries($token) {
		$request  = Ga_Lib_Api_Request::get_instance();
		$request  = $this->sign( $request, $token );
		try {
		$response = $request->make_request( self::GA_ACCOUNT_SUMMARIES_ENDPOINT, null, false, true );
		} catch (Ga_Lib_Api_Request_Exception $e) {
			throw new Ga_Lib_Google_Api_Client_AccountSummaries_Exception( $e->getMessage() );
		}

		return new Ga_Lib_Api_Response( $response );
	}

	/**
	 * Sends request for Google Analytics data using given query parameters.
	 *
	 * @param $query_params
	 *
	 * @return Ga_Lib_Api_Response Returns response object
	 */
	private function ga_api_data( $query_params) {
		$token = $query_params['token'];
		$request           = Ga_Lib_Api_Request::get_instance( $this->is_cache_enabled(), $token['account_id'] );
		$request           = $this->sign( $request, $token );
		unset($query_params['token']);

		$current_user      = wp_get_current_user();
		$quota_user_string = '';
		if ( ! empty( $current_user ) ) {
			$blogname          = get_option( 'blogname' );
			$quota_user        = md5( $blogname . $current_user->user_login );
			$quota_user_string = '?quotaUser=' . $quota_user;
		}
		try {
			$response = $request->make_request( self::GA_DATA_ENDPOINT . $quota_user_string,
				wp_json_encode( $query_params ), true );
		} catch ( Ga_Lib_Api_Request_Exception $e ) {
			throw new Ga_Lib_Google_Api_Client_Data_Exception( $e->getMessage() );
		}
		return new Ga_Lib_Api_Response( $response );
	}

	/**
	 * Sign request with Access Token.
	 * Adds Access Token to the request's headers.
	 *
	 * @param Ga_Lib_Api_Request $request
	 *
	 * @return Ga_Lib_Api_Request Returns response object
	 * @throws Ga_Lib_Api_Client_Exception
	 */
	private function sign( Ga_Lib_Api_Request $request, $token ) {
		if ( empty( $token ) ) {
			throw new Ga_Lib_Api_Client_Exception( 'Access Token is not available. Please reauthenticate' );
		}

		// Check if the token is set to expire in the next 30 seconds
		// (or has already expired).
		$new_token = $this->check_access_token($token);
		if ($new_token){
			$token = $new_token;
		}
		// Add the OAuth2 header to the request
		$request->set_request_headers( array( 'Authorization: Bearer ' . $token['access_token'] ) );

		return $request;
	}

	/**
	 * Refresh and save refreshed Access Token.
	 *
	 * @param $refresh_token
	 */
	public function refresh_access_token( $token ) {
		// Request for a new Access Token
		$response = $this->call_api_method( 'ga_auth_refresh_access_token', array( $token['refresh_token'] ) );

		return DT_Ga_Admin::save_access_token( $response, $token );
	}



	/**
	 * Checks if Access Token is valid.
	 *
	 * @return bool
	 */
	public function is_authorized($token) {
		if ( ! empty( $token->access_token ) ) {
			try {
				$this->check_access_token($token);
			} catch ( Ga_Lib_Api_Client_Exception $e ) {
				$this->add_error( $e );
			} catch ( Exception $e ) {
				$this->add_error( $e );
			}
		}

		return ! empty( $token ) && ! $this->is_access_token_expired($token);
	}

	/**
	 * Returns if the access_token is expired.
	 * @return bool Returns True if the access_token is expired.
	 */
	public function is_access_token_expired($token) {
		if ( null == $token ) {
			return true;
		}
		if ( ! empty( $token->error ) ) {
			return true;
		}

		// Check if the token is expired in the next 30 seconds.
		$expired = ( $token['created'] + ( $token['expires_in'] - 30 ) ) < time();

		return $expired;
	}

	private function check_access_token($token) {
		if ( $this->is_access_token_expired($token) ) {
			if ( empty( $token['refresh_token'] ) ) {
				throw new Ga_Lib_Api_Client_Exception( _( 'Refresh token is not available. Please re-authenticate.' ) );
			} else {
				return $this->refresh_access_token( $token );
			}
		}
	}

	/**
	 * @return bool
	 */
	public function is_cache_enabled() {
		return self::USE_CACHE && ! $this->disable_cache;
	}

}

class Ga_Lib_Google_Api_Client_Exception extends Ga_Lib_Api_Client_Exception {

	private $google_error_response = null;

	function __construct( $msg ) {
		$this->set_google_error_response( $msg );
		$data = $this->get_error_response_data( $msg );
		parent::__construct( $data['error']['message'], $data['error']['code'] );
	}

	/**
	 * Sets google JSON response.
	 * Response structure:
	 * {
	 *"error": {
	 *"code": 403,
	 *"message": "User does not have sufficient permissions for this profile.",
	 *"status": "PERMISSION_DENIED",
	 *"details": [
	 *{
	 *"@type": "type.googleapis.com/google.rpc.DebugInfo",
	 *"detail": "[ORIGINAL ERROR] generic::permission_denied: User does not have sufficient permissions for this profile.
	 *  [google.rpc.error_details_ext] { message: \"User does not have sufficient permissions for this profile.\" }"
	 *}
	 *]
	 *}
	 *}
	 */
	public function set_google_error_response( $response ) {
		$this->google_error_response = $response;
	}

	public function get_google_error_response() {
		return $this->google_error_response;
	}

	/**
	 * Decodes JSON response
	 *
	 * @param $response
	 *
	 * @return array
	 */
	protected function get_error_response_data( $response ) {
		$data = json_decode( $response, true );
		if ( ! empty( $data['error'] ) && ! empty( $data['error']['message'] ) && ! empty( $data['error']['code'] ) ) {
			return $data;
		} else {
			return array(
				'error' => array(
					'message' => _( 'Google Reporting API - unknown error.' ),
					'code'    => 500
				)
			);
		}
	}

}

class Ga_Lib_Google_Api_Client_AuthCode_Exception extends Ga_Lib_Google_Api_Client_Exception {
	function __construct( $msg ) {
		parent::__construct( $msg );
	}

	protected function get_error_response_data( $response ) {
		$data = json_decode( $response, true );
		if ( ! empty( $data['error'] ) && ! empty( $data['error_description'] ) ) {
			return array(
				'error' => array(
					'message' => '[' . $data['error'] . ']' . $data['error_description'],
					'code'    => 500
				)
			);
		} else {
			return array(
				'error' => array(
					'message' => 'Google API - uknown error.',
					'code'    => 500
				)
			);
		}
	}
}

class Ga_Lib_Google_Api_Client_Data_Exception extends Ga_Lib_Google_Api_Client_Exception {
	function __construct( $msg ) {
		parent::__construct( $msg );
	}
}

class Ga_Lib_Google_Api_Client_RefreshToken_Exception extends Ga_Lib_Google_Api_Client_Exception {
	function __construct( $msg ) {
		parent::__construct( $msg );
	}
}

class Ga_Lib_Google_Api_Client_AccountSummaries_Exception extends Ga_Lib_Google_Api_Client_Exception {
	function __construct( $msg ) {
		parent::__construct( $msg );
	}
}
