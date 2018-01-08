<?php
/**
 * Created by PhpStorm.
 * User: mdn
 * Date: 2016-12-08
 * Time: 09:15
 */

if ( ! function_exists( 'wp_json_encode' ) ) {
	function wp_json_encode( $data ) {
		return json_encode( $data );
	}
}
