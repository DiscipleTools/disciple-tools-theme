<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

class Disciple_Tools_Hook_User extends Disciple_Tools_Hook_Base {

    public function __construct() {
        add_action( 'wp_login', [ &$this, 'hooks_wp_login' ], 10, 2 );
        add_action( 'rest_pre_echo_response', [ $this, 'better_user_login_tracking' ], 10, 3 );

        parent::__construct();
    }

    public function hooks_wp_login( $user_login, $user ) {
        dt_activity_insert(
            [
                'action' => 'logged_in',
                'object_type' => 'User',
                'object_subtype' => '',
                'object_id' => $user->ID,
                'object_name' => $user->user_nicename,
                'meta_id'           => ' ',
                'meta_key'          => ' ',
                'meta_value'        => ' ',
                'meta_parent'        => ' ',
                'object_note'       => ' ',
            ]
        );
    }

    public function better_user_login_tracking( $response, $object, $request ){
        if ( get_current_user_id() ){
            $user = wp_get_current_user();
            $today = gmdate( 'Y-m-d', time() );
            $last_call = get_user_option( 'last_rest_call', get_current_user_id() );
            if ( !$last_call || $last_call !== $today ){
                dt_activity_insert(
                    [
                        'action' => 'logged_in',
                        'object_type' => 'User',
                        'object_subtype' => '',
                        'object_id' => get_current_user_id(),
                        'object_name' => $user->display_name,
                        'meta_id'           => ' ',
                        'meta_key'          => ' ',
                        'meta_value'        => ' ',
                        'meta_parent'        => ' ',
                        'object_note'       => ' ',
                    ]
                );
                update_user_option( get_current_user_id(), 'last_rest_call', $today );
            }
        }

        return $response;
    }
}
