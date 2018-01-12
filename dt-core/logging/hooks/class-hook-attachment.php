<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

class Disciple_Tools_Hook_Attachment extends Disciple_Tools_Hook_Base {

    protected function _add_log_attachment( $action, $attachment_id ) {
        $post = get_post( $attachment_id );

        dt_activity_insert(
            [
            'action'         => $action,
            'object_type'    => 'Attachment',
            'object_subtype' => $post->post_type,
            'object_id'      => $attachment_id,
            'object_name'    => get_the_title( $post->ID ),
             ]
        );
    }

    public function hooks_delete_attachment( $attachment_id ) {
        $this->_add_log_attachment( 'deleted', $attachment_id );
    }

    public function hooks_edit_attachment( $attachment_id ) {
        $this->_add_log_attachment( 'updated', $attachment_id );
    }

    public function hooks_add_attachment( $attachment_id ) {
        $this->_add_log_attachment( 'added', $attachment_id );
    }

    public function __construct() {
        add_action( 'add_attachment', [ &$this, 'hooks_add_attachment' ] );
        add_action( 'edit_attachment', [ &$this, 'hooks_edit_attachment' ] );
        add_action( 'delete_attachment', [ &$this, 'hooks_delete_attachment' ] );

        parent::__construct();
    }

}
