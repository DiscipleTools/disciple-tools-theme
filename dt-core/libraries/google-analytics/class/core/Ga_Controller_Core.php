<?php

/**
 * Created by PhpStorm.
 * User: mdn
 * Date: 2017-02-01
 * Time: 09:46
 */
class Ga_Controller_Core {

	const GA_NONCE_FIELD_NAME = '_gawpnonce';
	const ACTION_PARAM_NAME = 'ga_action';

	/**
	 * Runs particular action.
	 */
	public function handle_actions() {
		$action = !empty( $_REQUEST[ self::ACTION_PARAM_NAME ] ) ? $_REQUEST[ self::ACTION_PARAM_NAME ] : null;

		if ( $action ) {
			$class = get_class( $this );
			if ( is_callable( array(
				$class,
				$action
			) ) ) {
				call_user_func( $class . '::' . $action );
			}
		}
	}

	/**
	 * Verifies nonce for given acction.
	 *
	 * @param $action
	 * @return bool
	 */
	protected static function verify_nonce( $action ) {
		return !isset( $_POST[ self::GA_NONCE_FIELD_NAME ] ) || !wp_verify_nonce( $_POST[ self::GA_NONCE_FIELD_NAME ], $action );
	}
}
