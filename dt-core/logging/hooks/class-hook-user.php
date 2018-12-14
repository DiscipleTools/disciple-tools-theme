<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

class Disciple_Tools_Hook_User extends Disciple_Tools_Hook_Base {

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

    public function __construct() {
        add_action( 'wp_login', [ &$this, 'hooks_wp_login' ], 10, 2 );

        parent::__construct();
    }

}
