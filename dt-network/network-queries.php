<?php

class Disciple_Tools_Network_Queries {

    public static function contacts_current_state() : array  {
        global $wpdb;
        /**
         * Returns status and count of contacts according to the overall status
         * return array
         */
        $results = $wpdb->get_results("
                SELECT
                  b.meta_value as status,
                  count(a.ID) as count
                FROM $wpdb->posts as a
                  JOIN $wpdb->postmeta as b
                    ON a.ID = b.post_id
                       AND b.meta_key = 'overall_status'
                WHERE a.post_status = 'publish'
                      AND a.post_type = 'contacts'
                      AND a.ID NOT IN (
                  SELECT bb.post_id
                  FROM $wpdb->postmeta as bb
                  WHERE meta_key = 'corresponds_to_user'
                        AND meta_value != 0
                  GROUP BY bb.post_id )
                GROUP BY b.meta_value
            ", ARRAY_A );

        if ( empty( $results ) ) {
            $results = [];
        }

        return $results;
    }

    public static function all_contacts() : int {
        global $wpdb;
        /**
         * Returns single digit count of all contacts in the system.
         * return int
         */
        $results = $wpdb->get_var("
                    SELECT
                      count(a.ID) as count
                    FROM $wpdb->posts as a
                    WHERE a.post_status = 'publish'
                          AND a.post_type = 'contacts'
                          AND a.ID NOT IN (
                      SELECT bb.post_id
                      FROM $wpdb->postmeta as bb
                      WHERE meta_key = 'corresponds_to_user'
                            AND meta_value != 0
                      GROUP BY bb.post_id )
                ");
        if ( empty( $results ) ) {
            $results = 0;
        }
        return $results;
    }

    public static function all_groups() : int {
        global $wpdb;
        /**
         * Returns single digit count of all pre-groups, groups, and churches in the system.
         * return int
         */
        $results = $wpdb->get_var("
                    SELECT
                      count(a.ID) as count
                    FROM $wpdb->posts as a
                    WHERE a.post_status = 'publish'
                          AND a.post_type = 'groups'
                ");
        if ( empty( $results ) ) {
            $results = 0;
        }
        return $results;
    }

    public static function group_health() : array {
        global $wpdb;
        /**
         * Returns health numbers for groups and churches but not pre-groups
         *
         *  category            practicing
         *  church_baptism      4
         *  church_bible        5
         *  church_commitment   1
         *  church_communion    2
         *  church_fellowship   2
         *  church_giving       1
         *  church_leaders      1
         *  church_praise       1
         *  church_prayer       4
         *  church_sharing      2
         *
         */
        $results = $wpdb->get_results( "
                    SELECT
                      d.meta_value           as category,
                      count(distinct (a.ID)) as practicing
                    FROM $wpdb->posts as a
                      JOIN $wpdb->postmeta as c
                        ON a.ID = c.post_id
                           AND c.meta_key = 'group_status'
                           AND c.meta_value = 'active'
                      JOIN $wpdb->postmeta as d
                        ON a.ID = d.post_id
                            AND d.meta_key = 'health_metrics'
                      JOIN $wpdb->postmeta as e
                        ON a.ID = e.post_id
                           AND e.meta_key = 'group_type'
                            AND ( e.meta_value = 'group' OR e.meta_value = 'church')
                    WHERE a.post_status = 'publish'
                          AND a.post_type = 'groups'
                    GROUP BY d.meta_value;
                ", ARRAY_A );

        if ( empty( $results ) ) {
            $results = [];
        }

        return $results;
    }

    public static function user_logins_last_thirty_days() : int {
        global $wpdb;

        /**
         * Returns count for number of unique users signed in within the last month.
         */
        $results = $wpdb->get_var("
                    SELECT
                      COUNT( DISTINCT object_id ) as value
                    FROM $wpdb->dt_activity_log
                    WHERE
                      object_type = 'user'
                      AND action = 'logged_in'
                      AND hist_time >= UNIX_TIMESTAMP(CURDATE() - INTERVAL 1 MONTH );
                ");

        if ( empty( $results ) ) {
            $results = 0;
        }

        return $results;
    }

    public static function counted_by_month( $action, $object_type ) : array {
        global $wpdb;

        /**
         * Can collect various events just by specifying object type and action.
         *
         * Returns list grouped by timestamp
         *
         *   2019-01        9
         *   2018-12        11
         *   2018-11        9
         *   2018-10        39
         *
         */
        $results = $wpdb->get_results( $wpdb->prepare( "
                    SELECT
                      from_unixtime( hist_time , '%%Y-%%m') as date,
                      count( DISTINCT object_id) as value
                    FROM $wpdb->dt_activity_log
                    WHERE object_type = %s
                      AND action = %s
                      AND hist_time != ''
                      AND hist_time REGEXP ('^[0-9][0-9][0-9][0-9][0-9][0-9][0-9]')
                    GROUP BY date
                    ORDER BY date DESC
                    LIMIT 25;
                ",
            $object_type,
            $action
        ), ARRAY_A );

        if ( empty( $results ) ) {
            $results = [];
        }

        return $results;
    }

    public static function counted_by_day( $action, $object_type ) : array {
        global $wpdb;

        /**
         * Can collect various events just by specifying object type and action.
         *
         * Returns list grouped by timestamp
         *
         *   2019-01        9
         *   2018-12        11
         *   2018-11        9
         *   2018-10        39
         *
         */
        $results = $wpdb->get_results( $wpdb->prepare( "
                    SELECT
                      from_unixtime( hist_time , '%%Y-%%m-%%d') as date,
                      count( DISTINCT object_id) as value
                    FROM $wpdb->dt_activity_log
                    WHERE object_type = %s
                          AND action = %s
                          AND hist_time != ''
                          AND hist_time REGEXP ('^[0-9][0-9][0-9][0-9][0-9][0-9][0-9]')
                    GROUP BY date
                    ORDER BY date DESC
                    LIMIT 60;
                ",
            $object_type,
            $action
        ), ARRAY_A );

        if ( empty( $results ) ) {
            $results = [];
        }

        return $results;
    }

    public static function total_baptisms() : int {
        global $wpdb;

        /**
         * Returns the count for baptisms in the system
         *
         *   2018-04-30     9
         *   2018-04-29     11
         *   2018-04-28     9
         *   2018-04-27     39
         */
        $results = $wpdb->get_var( "
                   SELECT
                      count( DISTINCT object_id) as value
                    FROM $wpdb->dt_activity_log
                    WHERE 
                        object_type = 'contacts'
                        AND object_subtype = 'baptism_date'
                        AND meta_value != ''
                        AND meta_value REGEXP ('^[0-9][0-9][0-9][0-9][0-9][0-9][0-9]')
                " );
        if ( empty( $results ) ) {
            $results = 0;
        } else {
            $results = (int) $results;
        }

        return $results;
    }

    public static function baptisms_counted_by_month() : array {
        global $wpdb;

        /**
         * Can collect various events just by specifying object type and action.
         *
         * Returns list grouped by timestamp
         *
         *   2019-01        9
         *   2018-12        11
         *   2018-11        9
         *   2018-10        39
         *
         */
        $results = $wpdb->get_results( "
                    SELECT
                      from_unixtime( meta_value , '%Y-%m') as date,
                      count( DISTINCT object_id) as value
                    FROM $wpdb->dt_activity_log
                    WHERE object_type = 'contacts'
                      AND object_subtype = 'baptism_date'
                      AND meta_value != ''
                      AND meta_value REGEXP ('^[0-9][0-9][0-9][0-9][0-9][0-9][0-9]')
                    GROUP BY meta_value
                    ORDER BY date DESC
                    LIMIT 25;
                ", ARRAY_A );

        if ( empty( $results ) ) {
            $results = [];
        }

        return $results;
    }

    public static function baptisms_counted_by_day() : array {
        global $wpdb;

        /**
         * Returns list grouped by timestamp
         *
         *   2018-04-30     9
         *   2018-04-29     11
         *   2018-04-28     9
         *   2018-04-27     39
         */
        $results = $wpdb->get_results( "
               SELECT
                  from_unixtime( meta_value , '%Y-%m-%d') as date,
                  count( DISTINCT object_id) as value
                FROM $wpdb->dt_activity_log
                WHERE object_type = 'contacts'
                AND object_subtype = 'baptism_date'
                AND meta_value != ''
                AND meta_value REGEXP ('^[0-9][0-9][0-9][0-9][0-9][0-9][0-9]')
                GROUP BY meta_value
                ORDER BY date DESC
                LIMIT 60;
            ", ARRAY_A );

        if ( empty( $results ) ) {
            $results = [];
        }

        return $results;
    }

    public static function groups_types_and_status() : array {
        global $wpdb;

        /**
         * Returns the different types of groups and their count
         *
         *  pre-group   active      5
        pre-group   inactive    7
        group       active      2
        group       inactive    1
        church      active      9
        church      inactive    2
         */
        $results = $wpdb->get_results( "
                    SELECT
                      c.meta_value as type,
                      b.meta_value as status,
                      count(a.ID)  as count
                    FROM $wpdb->posts as a
                      JOIN $wpdb->postmeta as b
                        ON a.ID = b.post_id
                           AND b.meta_key = 'group_status'
                      JOIN $wpdb->postmeta as c
                        ON a.ID = c.post_id
                           AND c.meta_key = 'group_type'
                    WHERE a.post_status = 'publish'
                          AND a.post_type = 'groups'
                    GROUP BY type, status
                    ORDER BY type ASC
                ", ARRAY_A );

        if ( empty( $results ) ) {
            $results = [];
        }

        return $results;
    }

    public static function groups_churches_total() : int {
        global $wpdb;

        /**
         * Returns single digit count of all groups and churches in the system.
         * return int
         */
        $results = $wpdb->get_var("
                    SELECT
                      count(a.ID) as count
                    FROM $wpdb->posts as a
                    JOIN $wpdb->postmeta as c
                        ON a.ID = c.post_id
                           AND c.meta_key = 'group_status'
                           AND c.meta_value = 'active'
                    JOIN $wpdb->postmeta as b 
                      ON a.ID=b.post_id
                      AND b.meta_key = 'group_type'
                      AND ( b.meta_value = 'group' OR b.meta_value = 'church' )
                    WHERE a.post_status = 'publish'
                      AND a.post_type = 'groups'
                ");

        if ( empty( $results ) ) {
            $results = 0;
        }

        return $results;
    }

    public static function locations_current_state() : array {

        $results['active_countries'] = (int) count( Disciple_Tools_Mapping_Queries::active_admin0_grid_ids() );
        $results['active_admin1'] = (int) count( Disciple_Tools_Mapping_Queries::active_admin1_grid_ids() );
        $results['active_admin2'] = (int) count( Disciple_Tools_Mapping_Queries::active_admin2_grid_ids() );

        return $results;
    }
}
