<?php
/**
 * Presenter template for theme support
 *
 * @package  Disciple_Tools
 * @category Plugin
 * @author   Chasm.Solutions & Kingdom.Training
 * @since    0.1.0
 */
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

/** Functions to output data for the theme.   */

/**
 * Helper function to decide which metrics a user can see.
 */
function dt_metrics_visibility( $item ) : bool {

    switch ( $item ) {
        case 'tab':
            return ( user_can( get_current_user_id(), 'manage_options' ) ) ? true : false;
            break;
        default:
            return false;
            break;
    }
}

