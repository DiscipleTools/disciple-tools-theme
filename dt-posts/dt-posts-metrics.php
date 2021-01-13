<?php

class DT_Posts_Metrics {
    /**
     * Get counts of posts share with me, split the values of the meta_key
     * Excludes archived contacts
     *
     * @param $post_type
     * @param $meta_key
     * @return array
     */
    public static function get_shared_with_meta_field_counts( $post_type, $meta_key ){
        global $wpdb;
        $key_query = $wpdb->get_results( $wpdb->prepare( "
            SELECT COUNT(pm.meta_value) as count, pm.meta_value as 'key'
            FROM $wpdb->dt_share as s
            INNER JOIN $wpdb->posts p ON ( p.ID = s.post_id && p.post_type = %s AND p.post_status = 'publish' )
            INNER JOIN $wpdb->postmeta pm ON ( pm.post_ID = p.ID AND pm.meta_key = %s )
            LEFT JOIN $wpdb->postmeta archive_meta ON ( archive_meta.post_id = p.ID AND archive_meta.meta_key = 'overall_status' )
            WHERE s.user_id = %s
            AND ( archive_meta.meta_value != 'closed' OR archive_meta.meta_value IS NULL )
            GROUP BY pm.meta_value
        ", esc_sql( $post_type ), esc_sql( $meta_key ), esc_sql( get_current_user_id() ) ), ARRAY_A );
        $counts = [
            'total' => 0,
            'keys' => []
        ];
        foreach ( $key_query as $key ){
            $counts['total'] += $key['count'];
            $counts['keys'][$key['key']] = $key['count'];
        }

        return $counts;
    }

}
