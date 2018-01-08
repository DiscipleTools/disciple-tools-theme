<?php

/**
 * Handles exception translations.
 *
 * Created by PhpStorm.
 * User: mdn
 * Date: 2017-01-25
 * Time: 14:37
 */
class Ga_Notice {

	public static function get_message( $error ) {
		$message = '';

		if ( Ga_Helper::GA_DEBUG_MODE ) {
			$message = Ga_Helper::ga_wp_notice( (!empty( $error[ 'class' ] ) ? _( '[' . $error[ 'class' ] . ']' ) : '' ) . ' ' . $error[ 'message' ], DT_Ga_Admin::NOTICE_ERROR );
		} elseif ( $error[ 'class' ] == 'Ga_Lib_Sharethis_Api_Client_InvalidDomain_Exception' ) {
			$message = Ga_Helper::ga_wp_notice( $error[ 'message' ], DT_Ga_Admin::NOTICE_ERROR );
		} elseif ( $error[ 'class' ] == 'Ga_Lib_Sharethis_Api_Client_Invite_Exception' ) {
			$message = Ga_Helper::ga_wp_notice( $error[ 'message' ], DT_Ga_Admin::NOTICE_ERROR );
		} /*elseif ( in_array( $error[ 'class' ], array( 'Ga_Lib_Sharethis_Api_Client_Verify_Exception', 'Ga_Lib_Sharethis_Api_Client_Alerts_Exception' ) ) ) {
			$message = Ga_Helper::ga_wp_notice( Ga_Sharethis::GA_SHARETHIS_ALERTS_ERROR, DT_Ga_Admin::NOTICE_ERROR );
		}*/ elseif ( $error[ 'class' ] == 'Ga_Data_Outdated_Exception' ) {
			$message = Ga_Helper::ga_wp_notice( $error[ 'message' ], DT_Ga_Admin::NOTICE_WARNING );
		} else {
			$message = Ga_Helper::ga_wp_notice( _( 'There are temporary connection issues, please try again later or go to Google Analytics website to see the dashboards' ), DT_Ga_Admin::NOTICE_ERROR );
		}

		return $message;
	}

}
