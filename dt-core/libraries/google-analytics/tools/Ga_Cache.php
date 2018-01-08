<?php

/**
 * Handles request caching.
 *
 * Created by PhpStorm.
 * User: mdn
 * Date: 2017-01-27
 * Time: 10:31
 */
class Ga_Cache {

	/**
	 * Time until expiration in seconds
	 */
	const GA_DATA_EXPIRATION_TIME = 86400;// 60 * 60 * 24 = 86400; // 24h

	const GA_TRANSIENT_PREFIX = 'googleanalytics_cache_';

	const GA_LAST_CACHE_OPTION_NAME = 'googleanalytics_cache_last_cache_time';

	const GA_LAST_TIME_ATTEMPT_OPTION_NAME = 'googleanalytics_cache_last_time_attempt';

	const GA_BUFFER_CACHE_OPTION_NAME = 'googleanalytics_cache_buffer';

	const GA_WAIT_AFTER_ERROR_TIME = 300; // 60 * 5 = 5 min

	public static function add_cache_options() {
		add_option( self::GA_LAST_CACHE_OPTION_NAME );
		add_option( self::GA_BUFFER_CACHE_OPTION_NAME );
		add_option( self::GA_LAST_TIME_ATTEMPT_OPTION_NAME );
	}

	public static function delete_cache_options() {
		delete_option( self::GA_LAST_CACHE_OPTION_NAME );
		delete_option( self::GA_BUFFER_CACHE_OPTION_NAME );
		delete_option( self::GA_LAST_TIME_ATTEMPT_OPTION_NAME );
	}

	/**
	 * Generates transient name.
	 *
	 * @param $rest_url
	 * @param $query_params
	 * @param string $apendix
	 *
	 * @return string
	 */
	public static function get_transient_name( $rest_url, $query_params, $apendix = '' ) {

		if (is_array($query_params)) {
			$query_params = wp_json_encode($query_params);
		}

		$name = md5( $rest_url . $query_params );

		return self::GA_TRANSIENT_PREFIX . $name . '_' . $apendix;
	}

	/**
	 * Gets cached data.
	 *
	 * @param $name
	 *
	 * @return bool|mixed
	 */
	public static function get_cached_result( $name ) {

			$data = get_option( self::GA_BUFFER_CACHE_OPTION_NAME );

		// Check if cache exists
		if ( ! empty( $data[ $name ] ) ) { // Cache exists
			return $data[ $name ];
			} else {
				return false;
			}
		}

	/**
	 * Sets ne cache value.
	 *
	 * @param $name
	 * @param $result
	 */
	public static function set_cache( $name, $result ) {
		if ( ! empty( $result ) ) {
			self::set_last_cache_time( $name );
			self::set_cache_buffer( $name, $result );
			self::delete_last_time_attempt();
		}
	}

	/**
	 * Updates the time the response was cached.
	 *
	 * @param $name
	 */
	public static function set_last_cache_time( $name ) {
		$data = get_option( self::GA_LAST_CACHE_OPTION_NAME );

		if ( empty( $data ) ) {
			$data          = array();
			$data[ $name ] = time();
		} else {
			$data = array_merge( $data, array( $name => time() ) );
		}

		update_option( self::GA_LAST_CACHE_OPTION_NAME, $data );
	}

	/**
	 * Sets or update data cache.
	 *
	 * @param $name
	 * @param $result
	 */
	public static function set_cache_buffer( $name, $result ) {
		$data = get_option( self::GA_BUFFER_CACHE_OPTION_NAME );

		if ( empty( $data ) ) {
			$data          = array();
			$data[ $name ] = $result;
		} else {
			$data = array_merge( $data, array( $name => $result ) );
		}

		update_option( self::GA_BUFFER_CACHE_OPTION_NAME, $data );
	}

	/**
	 * Sets last time attempt option
	 */
	public static function set_last_time_attempt() {
		update_option( self::GA_LAST_TIME_ATTEMPT_OPTION_NAME, time());
	}

	/**
	 * Deletes last time attepmt option
	 */
	public static function delete_last_time_attempt() {
		delete_option( self::GA_LAST_TIME_ATTEMPT_OPTION_NAME );
	}

	/**
	 * Checks if the next rest API request is allowed.
	 *
	 * Next api request is allowed when there was an unsuccessful attempt and data_cache is empty.
	 *
	 * @param $name
	 *
	 * @return bool
	 */
	public static function is_next_request_allowed( $name ) {
		$last_time_attempt  = get_option( self::GA_LAST_TIME_ATTEMPT_OPTION_NAME );
		$outdated_last_attempt_time = true;

		if ( empty( $last_time_attempt ) ) {
			// If there is no last_time_attempt then return true
			return true;
		} elseif (!empty($last_time_attempt)) {
				$outdated_last_attempt_time = ( $last_time_attempt + self::GA_WAIT_AFTER_ERROR_TIME ) < time();
			}

		return ( ! self::get_cached_result( $name ) && $outdated_last_attempt_time );
	}

	/**
	 * Checks whether data cache is outdated.
	 * @param string $name
	 * @param string $appendix
	 *
	 * @return bool
	 */
	public static function is_data_cache_outdated($name = '', $appendix = '') {
		$last_time  = get_option( self::GA_LAST_CACHE_OPTION_NAME );
		$outdated = 0;
		if (!empty($last_time)) {

			// Validate cache for given rest name
			if ( ! empty( $name ) ) {

				// if appendix is set then check only that cache which concerns given appendix
				if ( ! empty( $appendix ) ) {
					return ( ! empty( $appendix ) && preg_match( '/' . $appendix . '/',
							$name ) && ( $last_time[ $name ] + self::GA_DATA_EXPIRATION_TIME ) < time() );
				} else {
					return ! empty( $last_time[ $name ] ) && ( $last_time[ $name ] + self::GA_DATA_EXPIRATION_TIME ) < time();
				}

			} else { // Validate cache for all requests

				// If any of existing caches is outdated
				foreach ( $last_time as $item => $time ) {
					// if appendix is set then check only entries concerns given appendix
					if ( ! empty( $appendix ) ) {
						if ( ! empty( $appendix ) && preg_match( '/' . $appendix . '/', $item ) && ( $time + self::GA_DATA_EXPIRATION_TIME ) < time() ) {
							$outdated ++;
						}
					} else {
					if ( ( $time + self::GA_DATA_EXPIRATION_TIME ) < time() ) {
						$outdated ++;
					}
				}
				}

				return $outdated > 0;
			}
		}

		return false;
	}

}
