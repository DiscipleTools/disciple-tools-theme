<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Disciple_Tools_Activity_Log_API
 *
 * @see Disciple_Tools_Activity_Log_API::insert
 *
 * @since 0.1.0
 * @param array $args
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
     * Get real address
     *
     * @since 0.1.0
     *
     * @return string real address IP
     */
    protected function _get_ip_address() {
        $server_ip_keys = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR',
        ];

        foreach ( $server_ip_keys as $key ) {
            if ( isset( $_SERVER[ $key ] ) && filter_var( sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) ), FILTER_VALIDATE_IP ) ) {
                return sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) );
            }
        }

        // Fallback local ip.
        return '127.0.0.1';
    }

    /**
     * @since 0.1.0
     * @return void
     */
    public function erase_all_items() {
        global $wpdb;

        $wpdb->query(
            "TRUNCATE `$wpdb->dt_activity_log`"
        );
    }

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
                'object_type'    => '',
                'object_subtype' => '',
                'object_name'    => '',
                'object_id'      => '',
                'hist_ip'        => $this->_get_ip_address(),
                'hist_time'      => time(),
                'object_note'    => '',
                'meta_id'        => '',
                'meta_key'       => '',
                'meta_value'     => '',
                'meta_parent'     => '',
            ]
        );
        $user = get_user_by( 'id', get_current_user_id() );
        if ( $user ) {
            $args['user_caps'] = strtolower( key( $user->caps ) );
            if ( empty( $args['user_id'] ) ) {
                $args['user_id'] = $user->ID;
            }
        } else {
            $args['user_caps'] = 'guest';
            if ( empty( $args['user_id'] ) ) {
                $args['user_id'] = 0;
            }
        }

        // Make sure for non duplicate.
        $check_duplicate = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT
                     `histid`
                FROM
                    `$wpdb->dt_activity_log`
                WHERE
                    `user_caps` = %s
                    AND `action` = %s
                    AND `object_type` = %s
                    AND `object_subtype` = %s
                    AND `object_name` = %s
                    AND `user_id` = %s
                    AND `hist_ip` = %s
                    AND `hist_time` = %s
                    AND `object_note` = %s
                    AND `meta_id` = %s
                    AND `meta_key` = %s
                    AND `meta_value` = %s
                    AND `meta_parent` = %s
				;",
                $args['user_caps'],
                $args['action'],
                $args['object_type'],
                $args['object_subtype'],
                $args['object_name'],
                $args['user_id'],
                $args['hist_ip'],
                $args['hist_time'],
                $args['object_note'],
                $args['meta_id'],
                $args['meta_key'],
                $args['meta_value'],
                $args['meta_parent']
            )
        );

        if ( $check_duplicate ) {
            return;
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
                'meta_value'       => $args['meta_value'],
                'meta_parent'       => $args['meta_parent'],
            ],
            [ '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%s', '%d', '%s', '%d', '%s', '%s', '%d' ]
        );

        if ( isset( $args["object_id"] ) && isset( $args["object_subtype"] ) && $args["object_subtype"] !== "last_modified" ){
            update_post_meta( $args["object_id"], "last_modified", time() );
        }

        // Final action on insert.
        do_action( 'dt_insert_activity', $args );
    }
}


