<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Disciple_Tools_Activity_Log_API
 *
 * @see Disciple_Tools_Activity_Log_API::insert
 *
 * @since 0.1.0
 * @param array $args [
 * action: the action performed
 * object_type: post_type, user, tec
 * object_subtype: field, further designation
 * object_name: optional
 * object_id: the post, user or comment ID
 * hist_ip: unused
 * hist_time: unix epoch time stamp
 * object_note: the activity message. Optional. It is better to generated the message on read
 * meta_id: the id of the postmeta row if applicable
 * meta_key: the meta_key of the postmeta row, the
 * meta_value: the new value
 * meta_parent: unused
 * old_value: the previous value of the field, if applicable
 * field_type: the type of the postmeta field. Optional
 * ]
 * @return void
 */
function dt_activity_insert( $args = [] ) {
    disciple_tools()->logging_activity_api->insert( $args );
}

/**
 * Disciple_Tools_Activity_Log_API
 * This handles the insert and other functions for the table _dt_activity_log
 */
class Disciple_Tools_Activity_Log_API {

    /**
     * @since 0.1.0
     *
     * @param array $args
     * @return void
     */
    public function insert( $args ) {
        global $wpdb;

        $args = wp_parse_args(
            $args,
            [
                'action'         => '',
                'object_type'    => 'unknown',
                'object_subtype' => '',
                'object_name'    => 'unknown',
                'object_id'      => '0',
                'hist_ip'        => '0',
                'hist_time'      => time(),
                'object_note'    => '0',
                'meta_id'        => '0',
                'meta_key'       => '0',
                'meta_value'     => '0',
                'meta_parent'    => '0',
                'old_value'      => '',
                'field_type'     => ''
            ]
        );
        $user = wp_get_current_user();
        if ( $user ) {
            $args['user_caps'] = strtolower( key( $user->caps ) );
            if ( empty( $args['user_id'] ) ) {
                $args['user_id'] = $user->ID;
            }
        } else {
            $args['user_caps'] = 'system';
            if ( empty( $args['user_id'] ) ) {
                $args['user_id'] = 0;
            }
        }
        if ( isset( $user->site_key ) ){
            $args["user_caps"] = $user->site_key;
        }

        // trim values that are too long.
        $text_fields = [ 'object_note', 'meta_value', 'old_value', 'action', 'object_type', 'object_subtype', 'object_name' ];
        foreach ( $text_fields as $field ){
            if ( isset( $args[$field] ) && strlen( $args[$field] ) >= 250 ) {
                $args[$field] = is_serialized( $args[$field] ) ? "" : substr( $args[$field], 0, 250 ) . "...";
            }
        }
        if ( $args["object_name"] === null ){
            $args["object_name"] = "";
        }
        if ( $args["old_value"] === null ){
            $args["old_value"] = "";
        }

        $wpdb->insert(
            $wpdb->dt_activity_log,
            [
                'action'         => $args['action'],
                'object_type'    => $args['object_type'],
                'object_subtype' => $args['object_subtype'],
                'object_name'    => $args['object_name'],
                'object_id'      => $args['object_id'],
                'user_id'        => $args['user_id'],
                'user_caps'      => $args['user_caps'],
                'hist_ip'        => $args['hist_ip'],
                'hist_time'      => $args['hist_time'],
                'object_note'    => $args['object_note'],
                'meta_id'        => $args['meta_id'],
                'meta_key'       => $args['meta_key'],
                'meta_value'     => $args['meta_value'],
                'meta_parent'    => $args['meta_parent'],
                'old_value'      => $args['old_value'],
                'field_type'     => $args['field_type']
            ],
            [
                '%s',
                '%s',
                '%s',
                '%s',
                '%d',
                '%d',
                '%s',
                '%s',
                '%d',
                '%s',
                '%d',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s'
            ]
        );

        if ( isset( $args["object_id"] ) && isset( $args["object_subtype"] ) && $args["object_subtype"] !== "last_modified" && ( isset( $args["object_type"] ) && $args["object_type"] !== "User" ) ){
            update_post_meta( $args["object_id"], "last_modified", time() );
        }

        // Final action on insert.
        do_action( 'dt_insert_activity', $args );
    }
}


