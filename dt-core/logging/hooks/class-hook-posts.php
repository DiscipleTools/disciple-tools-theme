<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Class Disciple_Tools_Hook_Posts
 */
class Disciple_Tools_Hook_Posts extends Disciple_Tools_Hook_Base {

    public function __construct() {
        add_action( 'transition_post_status', [ &$this, 'hooks_transition_post_status' ], 10, 3 );
        add_action( 'delete_post', [ &$this, 'hooks_delete_post' ] );
        add_action( "added_post_meta", [ &$this, 'hooks_added_post_meta' ], 10, 4 );
        add_action( "updated_post_meta", [ &$this, 'hooks_updated_post_meta' ], 10, 4 );
        add_action( "delete_post_meta", [ &$this, 'post_meta_deleted' ], 10, 4 );
        add_action( 'p2p_created_connection', [ &$this, 'hooks_p2p_created' ], 10, 1 );
        add_action( 'p2p_delete_connections', [ &$this, 'hooks_p2p_deleted' ], 10, 1 );

        parent::__construct();
    }


    protected function _draft_or_post_title( $post = 0 ) {
        $title = get_the_title( $post );

        if ( empty( $title ) ) {
            $title = __( '(no title)', 'disciple-tools' );
        }

        return $title;
    }

    public function hooks_transition_post_status( $new_status, $old_status, $post ) {
        if ( 'auto-draft' === $old_status && ( 'auto-draft' !== $new_status && 'inherit' !== $new_status ) ) {
            // page created
            $action = 'created';
        }
        elseif ( 'auto-draft' === $new_status || ( 'new' === $old_status && 'inherit' === $new_status ) ) {
            // nvm.. ignore it.
            return;
        }
        elseif ( 'trash' === $new_status ) {
            // page was deleted.
            $action = 'trashed';
        }
        elseif ( 'trash' === $old_status ) {
            $action = 'restored';
        }
        elseif ( 'draft' === $old_status && 'published' == $new_status ) {
            $action = 'published';
        }
        else {
            return;
        }

        if ( wp_is_post_revision( $post->ID ) ) { // Skip for revision.
            return;
        }


        if ( 'nav_menu_item' === get_post_type( $post->ID ) ) { // Skip for menu items.
            return;
        }

        dt_activity_insert(
            [
                'action' => $action,
                'object_type' => 'Post',
                'object_subtype' => $post->post_type,
                'object_id' => $post->ID,
                'object_name' => $this->_draft_or_post_title( $post->ID ),
                'meta_id'           => ' ',
                'meta_key'          => ' ',
                'meta_value'        => ' ',
                'meta_parent'        => ' ',
                'object_note'       => ' ',
            ]
        );
    }

    public function hooks_delete_post( $post_id ) {
        if ( wp_is_post_revision( $post_id ) ) {
            return;
        }

        $post = get_post( $post_id );

        if ( in_array( $post->post_status, [ 'auto-draft', 'inherit' ] ) ) {
            return;
        }

        // Skip for menu items.
        if ( 'nav_menu_item' === get_post_type( $post->ID ) ) {
            return;
        }

        dt_activity_insert(
            [
                'action' => 'deleted',
                'object_type' => 'Post',
                'object_subtype' => $post->post_type,
                'object_id' => $post->ID,
                'object_name' => $this->_draft_or_post_title( $post->ID ),
                'meta_id'           => ' ',
                'meta_key'          => ' ',
                'meta_value'        => ' ',
                'meta_parent'        => ' ',
                'object_note'       => ' ',
            ]
        );
    }

    public function hooks_added_post_meta( $mid, $object_id, $meta_key, $meta_value ) {

        return $this->hooks_updated_post_meta( $mid, $object_id, $meta_key, $meta_value, true );

    }


    public function hooks_updated_post_meta( $meta_id, $object_id, $meta_key, $meta_value, $new = false, $deleted = false ) {
        global $wpdb;
        $parent_post = get_post( $object_id, ARRAY_A ); // get object info

        if ($meta_key == '_edit_lock' || $meta_key == '_edit_last' || $meta_key == "last_modified") { // ignore edit lock
            return;
        }
        if ($parent_post["post_status"] === "auto-draft"){
            return;
        }

        if ( 'nav_menu_item' == $parent_post['post_type'] || 'attachment' == $parent_post['post_type'] ) { // ignore nav items
            return;
        }


        // get the previous value
        $prev = '';
        $prev_value = '';
        if ( !$new){
            $prev = $wpdb->get_results( $wpdb->prepare(
                "SELECT
                    *
                FROM
                    `$wpdb->dt_activity_log`
                WHERE
                    `object_type` = %s
                    AND `object_id` = %s
                    AND `meta_id` = %s
                ORDER BY
                    `hist_time` DESC
                LIMIT
                    0,1;",
                $parent_post['post_type'],
                $object_id,
                $meta_id
            ) );

        }
        if ( !empty( $prev )){
            if ( is_array( $meta_value )){
                $prev_value = maybe_unserialize( $prev[0]->meta_value );
            } else {
                $prev_value = $prev[0]->meta_value;
            }
        }
        $field_type = "";
        $object_note = '';
        switch ($parent_post['post_type']) { // get custom fields for post type. Else, skip object note.
            case 'contacts':
                $fields = Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings( true, $object_id );
                if (strpos( $meta_key, "quick_button" ) !== false ){
                    $object_note = $this->_key_name( $meta_key, $fields );
                }
                break;
            case 'groups':
                $fields = Disciple_Tools_Groups_Post_Type::instance()->get_custom_fields_settings();
                break;
            case 'locations':
                $fields = Disciple_Tools_Location_Post_Type::instance()->get_custom_fields_settings();
                break;
            default:
                $fields = '';
                break;
        }

        //build message for verifying and invalidating contact information fields.
        if (strpos( $meta_key, "_details" ) !== false && is_array( $meta_value )) {
            $original_key = str_replace( "_details", "", $meta_key );
            $original = get_post_meta( $object_id, $original_key, true );
            $object_note = $this->_key_name( $original_key, $fields ) . ' "'. $original .'" ';
            $field_type = "details";
            foreach ($meta_value as $k => $v){
                if (is_array( $prev_value ) && isset( $prev_value[ $k ] ) && $prev_value[ $k ] == $v){
                    continue;
                }
                if ($k === "verified") {
                    $object_note .= $v ? "verified" : "not verified";
                }
                if ($k === "invalid") {
                    $object_note .= $v ? "invalidated" : "not invalidated";
                }
                $object_note .= ', ';
            }
            $object_note = chop( $object_note, ', ' );
        }

        if ( $meta_key == "title" ){
            $object_note = "Name changed to: " . $meta_value;
        }
        if (strpos( $meta_key, "assigned_to" ) !== false ){
            $meta_array = explode( '-', $meta_value ); // Separate the type and id
            if ( isset( $meta_array[1] ) ) {
                $user = get_user_by( "ID", $meta_array[1] );
                $object_note = "Assigned to: " . ( $user ? $user->display_name : "Nobody" );
            }
        }


        if ( !empty( $fields ) && isset( $fields[$meta_key]["type"] ) ){
            $field_type = $fields[$meta_key]["type"];
        }

        if ( !empty( $fields ) && !$object_note) { // Build object note if contact, group, location, else ignore object note
            if ($new){
                $object_note = 'Added ' . $this->_key_name( $meta_key, $fields ) . ': ' . $this->_value_name( $meta_key, $meta_value, $fields );
            } else if ($deleted){
                $object_note = $this->_key_name( $meta_key, $fields ) . ' "' . $this->_value_name( $meta_key, $prev_value, $fields ) . '" deleted ';
            } else {
                $object_note = $this->_key_name( $meta_key, $fields ) . ' changed '  .
                    ( isset( $prev_value ) ? 'from "' . $this->_value_name( $meta_key, $prev_value, $fields ) .'"' : '' ) .
                    ' to "' . $this->_value_name( $meta_key, $meta_value, $fields ) . '"';

            }
        }

        if ( $deleted ){
            $meta_value = "value_deleted";
        }

        dt_activity_insert( // insert activity record
            [
                'action'            => 'field_update',
                'object_type'       => ( empty( $parent_post['post_type'] ) ) ? 'unknown' : $parent_post['post_type'],
                'object_subtype'    => $meta_key,
                'object_id'         => $object_id,
                'object_name'       => ( empty( $parent_post['post_title'] ) ) ? 'unknown' : $parent_post['post_title'],
                'meta_id'           => $meta_id,
                'meta_key'          => $meta_key,
                'meta_value'        => is_array( $meta_value ) ? serialize( $meta_value ) : $meta_value,
                'meta_parent'       => ( empty( $parent_post['post_parent'] ) ) ? 'unknown' : $parent_post['post_parent'],
                'object_note'       => $object_note,
                'old_value'         => is_array( $prev_value ) ? serialize( $prev_value ) : $prev_value,
                'field_type'        => $field_type,
            ]
        );
    }

    /**
     * Extract the pretty key name, if available
     *
     * @param  $meta_key
     * @param  $fields
     * @return mixed
     */
    protected function _key_name( $meta_key, $fields ) {
        if (isset( $fields[ $meta_key ]['name'] )) { // test if field exists
            return $fields[ $meta_key ]['name'];
        } else {
            return $meta_key;
        }

    }

    /**
     * Extract the pretty value name, if available
     *
     * @param  $meta_key
     * @param  $meta_value
     * @param  $fields
     * @return mixed
     */
    protected function _value_name( $meta_key, $meta_value, $fields ) {
        if ( is_array( $meta_value )){
            return serialize( $meta_value );
        }

        if (isset( $fields[ $meta_key ]['default'][ $meta_value ] )) { // test if value exists

            if ( !is_array( $fields[ $meta_key ]['default'] )) { // test if array
                return $meta_value;
            } else {
                return $fields[ $meta_key ]['default'][ $meta_value ];
            }
        } else { // if field not set
            return $meta_value;
        }
    }

    public function hooks_p2p_created( $p2p_id, $action = 'connected to' ) { // I need to create two records. One for each end of the connection.
        // Get p2p record
        $p2p_record = p2p_get_connection( $p2p_id ); // returns object
        $p2p_from = get_post( $p2p_record->p2p_from, ARRAY_A );
        $p2p_to = get_post( $p2p_record->p2p_to, ARRAY_A );
        $p2p_type = $p2p_record->p2p_type;

        // Log for both records
        dt_activity_insert(
            [
                'action'            => $action,
                'object_type'       => $p2p_from['post_type'],
                'object_subtype'    => 'p2p',
                'object_id'         => $p2p_from['ID'],
                'object_name'       => $p2p_from['post_title'],
                'meta_id'           => $p2p_id,
                'meta_key'          => $p2p_type,
                'meta_value'        => $p2p_to['ID'], // i.e. the opposite record of the object in the p2p
                'meta_parent'        => '',
                'object_note'       => '',
                'field_type'        => "connection from"
            ]
        );

        dt_activity_insert(
            [
                'action'            => $action,
                'object_type'       => $p2p_to['post_type'],
                'object_subtype'    => 'p2p',
                'object_id'         => $p2p_to['ID'],
                'object_name'       => $p2p_to['post_title'],
                'meta_id'           => $p2p_id,
                'meta_key'          => $p2p_type,
                'meta_value'        => $p2p_from['ID'], // i.e. the opposite record of the object in the p2p
                'meta_parent'        => '',
                'object_note'       => '',
                'field_type'        => "connection to",
            ]
        );

    }

    //note: delete is given an array of ids
    public function hooks_p2p_deleted( $p2p_ids ) {
        $this->hooks_p2p_created( $p2p_ids[0], $action = 'disconnected from' );
    }

    public function post_meta_deleted( $meta_id, $object_id, $meta_key, $meta_value = '', $new = false ){
        if ( strpos( $meta_key, "_details" ) === false ){
            $this->hooks_updated_post_meta( $meta_id[0], $object_id, $meta_key, $meta_value, $new, true );
        }
    }

}
