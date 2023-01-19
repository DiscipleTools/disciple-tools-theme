<?php

if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

if ( class_exists( 'DT_Contacts_Utils' ) ) { return; }

class DT_Contacts_Utils {

    public static function erase_data( $contact_id, $requester_email ) {
        global $wpdb;

        // build log
        $log = [
            'requester' => $requester_email,
            'contact_id' => $contact_id,
            'erased_by_id' => get_current_user_id(),
            'erased_by_name' => dt_get_user_display_name( get_current_user_id() ),
            'time' => time(),
        ];
        $options_log = get_option( 'dt_gdpr_log' );
        $options_log[] = $log;
        update_option( 'dt_gdpr_log', $options_log, false );

        // get all ids
        $results = [];
        $record = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->posts WHERE ID = %d", $contact_id ), ARRAY_A );
        if ( empty( $record ) ) {
            return false;
        }
        else {
            $results[$record['ID']] = [
            'post' => [],
            'meta' => [],
            'notifications' => [],
            'activity' => [],
            'comments' => []
            ];
            $results[$record['ID']]['post'] = $record;
        }
        $duplicates = $wpdb->get_results( $wpdb->prepare( "SELECT p.* FROM $wpdb->posts as p WHERE p.ID IN ( SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'duplicate_of' AND meta_value = %d )", $contact_id ), ARRAY_A );
        if ( ! empty( $duplicates ) ) {
            foreach ( $duplicates as $item ) {
                $results[$item['ID']]['post'] = $item;
            }
        }

        foreach ( $results as $id => $item ) {
            // delete extra
            $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->postmeta WHERE post_id = %d", $id ) );
            $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->dt_notifications WHERE post_id = %d", $id ) );
            $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->dt_location_grid_meta WHERE post_id = %d", $id ) );
            $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->dt_activity_log WHERE object_id = %d", $id ) );
            $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->comments WHERE comment_post_id = %d", $id ) );
            $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->p2p WHERE p2p_to = %d AND p2p_type = 'contacts_to_subassigned'", $id ) );

            $key = hash( 'sha256', time() . rand( 0, 100000 ) );
            $key = str_replace( '0', '', $key );
            $key = str_replace( 'O', '', $key );
            $key = str_replace( 'o', '', $key );
            $key = strtoupper( substr( $key, 0, 5 ) );

            // redact
            $wpdb->query( $wpdb->prepare( "UPDATE $wpdb->posts SET post_title = %s WHERE ID = %d", 'REDACTED ' . $key, $id ) );
            $wpdb->query( $wpdb->prepare( "UPDATE $wpdb->posts SET post_name = %s WHERE ID = %d", strtolower( $key ), $id ) );
            $wpdb->insert( $wpdb->postmeta, [
                'post_id' => $id,
                'meta_key' => 'overall_status',
                'meta_value' => 'closed'
            ] );
            $wpdb->insert( $wpdb->postmeta, [
                'post_id' => $id,
                'meta_key' => 'reason_closed',
                'meta_value' => 'gdpr'
            ] );
            $wpdb->insert( $wpdb->postmeta, [
                'post_id' => $id,
                'meta_key' => 'requires_update',
                'meta_value' => ''
            ] );
            $wpdb->insert( $wpdb->postmeta, [
                'post_id' => $id,
                'meta_key' => 'last_modified',
                'meta_value' => time()
            ] );

        }

        return true;
    }
}