<?php

class Ga_Lib_Api_Request {

	static $instance = null;

	const HEADER_CONTENT_TYPE		    = "application/x-www-form-urlencoded";
	const HEADER_CONTENT_TYPE_JSON      = "Content-type: application/json";
	const HEADER_ACCEPT			        = "Accept: application/json, text/javascript, */*; q=0.01";
	const TIMEOUT					    = 5;
	const USER_AGENT				    = 'googleanalytics-wordpress-plugin';

	private $headers = array();

	// Whether to cache or not
	private $cache = false;
		
	private $appendix = '';

	private function __construct( $cache = false, $appendix = '' ) {
		$this->cache = $cache;
		$this->appendix = $appendix;
	}

	/**
	 * Returns API client instance.
	 *
	 * @return Ga_Lib_Api_Request|null
	 */
	public static function get_instance( $cache = false, $appendix = '' ) {
		if ( self::$instance === null ) {
			self::$instance = new Ga_Lib_Api_Request( $cache, $appendix );
		}

		return self::$instance;
	}

	/**
	 * Sets request headers.
	 *
	 * @param $headers
	 */
	public function set_request_headers( $headers ) {
		if ( is_array( $headers ) ) {
			$this->headers = array_merge( $this->headers, $headers );
		} else {
			$this->headers[] = $headers;
		}
	}

	/**
	 * Perform HTTP request.
	 *
	 * @param string $url URL address
	 * @param string $rawPostBody
	 * @param boolean $json Whether to append JSON content type
	 * @param boolean $force_no_cache Whether to force not to cache response data even if cache property is set to true
	 *
	 * @return string Response
	 * @throws Exception
	 */
	public function make_request( $url, $rawPostBody = null, $json = false, $force_no_cache = false) {

		// Return cached data if exist
		$wp_transient_name = Ga_Cache::get_transient_name( $url, $rawPostBody, $this->appendix );
		if ( ! $force_no_cache ) {
			if ( $this->cache ) {
				if ( $cached = Ga_Cache::get_cached_result( $wp_transient_name ) ) {
					if ( ! Ga_Cache::is_data_cache_outdated( $wp_transient_name, $this->appendix ) ) {
						return $cached;
					}
				}

				// Check if the next request after error is allowed
				if ( false === Ga_Cache::is_next_request_allowed( $wp_transient_name ) ) {
					throw new Ga_Lib_Api_Client_Exception( _( 'There are temporary connection issues, please try again later.' ) );
				}
			}
		}


		if ( !function_exists( 'curl_init' ) ) {
			throw new Ga_Lib_Api_Client_Exception( _( 'cURL functions are not available' ) );
		}

		// Set default headers
		$this->set_request_headers( array(
			( $json ? self::HEADER_CONTENT_TYPE_JSON : self::HEADER_CONTENT_TYPE ),
			self::HEADER_ACCEPT
		) );

		$ch		 = curl_init( $url );
		$headers = $this->headers;
		curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );

		$curl_timeout		 = self::TIMEOUT;
		$php_execution_time	 = ini_get( 'max_execution_time' );
		if ( !empty( $php_execution_time ) && is_numeric( $php_execution_time ) ) {
			if ( $php_execution_time < 36 && $php_execution_time > 9 ) {
				$curl_timeout = $php_execution_time - 5;
			} elseif ( $php_execution_time < 10 ) {
				$curl_timeout = 5;
			}
		}

		// Set the proxy configuration. The user can provide this in wp-config.php
		if ( defined( 'WP_PROXY_HOST' ) ) {
			curl_setopt( $ch, CURLOPT_PROXY, WP_PROXY_HOST );
		}
		if ( defined( 'WP_PROXY_PORT' ) ) {
			curl_setopt( $ch, CURLOPT_PROXYPORT, WP_PROXY_PORT );
		}
		if ( defined( 'WP_PROXY_USERNAME' ) ) {
			$auth = WP_PROXY_USERNAME;
			if ( defined( 'WP_PROXY_PASSWORD' ) ) {
				$auth .= ':' . WP_PROXY_PASSWORD;
			}
			curl_setopt( $ch, CURLOPT_PROXYUSERPWD, $auth );
		}

		curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, $curl_timeout );
		curl_setopt( $ch, CURLOPT_TIMEOUT, $curl_timeout );
		curl_setopt( $ch, CURLOPT_HEADER, true );
		curl_setopt( $ch, CURLINFO_HEADER_OUT, true );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_USERAGENT, self::USER_AGENT );
		if ( defined( 'CURLOPT_IPRESOLVE' ) && defined( 'CURL_IPRESOLVE_V4' ) ) {
			curl_setopt( $ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
		}

		// POST body
		if ( !empty( $rawPostBody ) ) {
			curl_setopt( $ch, CURLOPT_POST, true );
			curl_setopt( $ch, CURLOPT_POSTFIELDS, ( $json ? $rawPostBody : http_build_query( $rawPostBody ) ) );
		}

		// Execute request
		$response = curl_exec( $ch );

		if ( $error = curl_error( $ch ) ) {
			$errno = curl_errno( $ch );
			curl_close( $ch );

			// Store last cache time when unsuccessful
			if ( false === $force_no_cache ) {
				if ( true === $this->cache ) {
			Ga_Cache::set_last_cache_time( $wp_transient_name );
			Ga_Cache::set_last_time_attempt();
				}
			}

			throw new Ga_Lib_Api_Client_Exception( $error . ' (' . $errno . ')' );
		} else {
			$httpCode	 = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
			$headerSize	 = curl_getinfo( $ch, CURLINFO_HEADER_SIZE );
			$header		 = substr( $response, 0, $headerSize );
			$body		 = substr( $response, $headerSize, strlen( $response ) );
			if ( preg_match( '/^(4|5)[0-9]{2}/', $httpCode ) ) {

				// Store last cache time when unsuccessful
				if ( false === $force_no_cache ) {
					if ( true === $this->cache ) {
				Ga_Cache::set_last_cache_time( $wp_transient_name );
				Ga_Cache::set_last_time_attempt();
					}
				}

				throw new Ga_Lib_Api_Request_Exception( ( $httpCode == 404 ? _( 'Requested URL doesn\'t exists: ' . $url ) : $body ) );
			}

			curl_close( $ch );

			$response_data = array( $header, $body );

			// Cache result
			if ( false === $force_no_cache ) {
				if (true === $this->cache) {
				Ga_Cache::set_cache( $wp_transient_name, $response_data );
			}
			}


			return $response_data;
		}
	}

}

class Ga_Lib_Api_Request_Exception extends Exception {

	public function __construct( $message ) {
		parent::__construct( $message );
	}

}
