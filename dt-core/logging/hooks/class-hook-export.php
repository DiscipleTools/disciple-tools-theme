<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

class Disciple_Tools_Hook_Export extends Disciple_Tools_Hook_Base {

    public function hooks_export_wp( $args ) {
        dt_activity_insert(
            [
                'action' => 'downloaded',
                'object_type' => 'Export',
                'object_id' => 0,
                'object_name' => isset( $args['content'] ) ? $args['content'] : 'all',
            ]
        );
    }

    public function __construct() {
        add_action( 'export_wp', [ &$this, 'hooks_export_wp' ] );

        parent::__construct();
    }

}
