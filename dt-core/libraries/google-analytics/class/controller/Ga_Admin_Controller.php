<?php

/**
 * Manages actions in the admin area.
 *
 * Created by PhpStorm.
 * User: mdn
 * Date: 2017-01-25
 * Time: 09:50
 */
class Ga_Admin_Controller extends Ga_Controller_Core {

	/**
	 * Redirects to Google oauth authentication endpoint.
	 */
	public static function ga_action_auth() {
		if ( Ga_Helper::are_features_enabled() ) {
			header( 'Location:' . DT_Ga_Admin::api_client()->create_auth_url() );
		} else {
			wp_die( Ga_Helper::ga_oauth_notice( __( 'Please accept the terms to use this feature' ) ) );
		}
	}

}
