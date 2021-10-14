<?php

/**
 * Section getting metrics data
 * For users to see their own stats
 * For admin to view user stats
 */
class DT_User_Metrics {

    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
    }


    public function add_api_routes() {
        /*
         * Permission check if the current user can view the requested user
         */
        $can_view_user_stats = function ( WP_REST_Request $request ){
            $params = $request->get_params();
            $user_id = get_current_user_id();
            if ( isset( $params["user_id"] ) && !empty( $params["user_id"] ) ){
                $user_id = (int) $params["user_id"];
            }
            return Disciple_Tools_Users::can_view( $user_id );
        };

        $namespace = 'dt-users/v1';
        register_rest_route(
            $namespace, 'activity-log', [
                'methods'  => 'GET',
                'callback' => [ $this, 'get_activity_endpoint' ],
                'permission_callback' => $can_view_user_stats
            ]
        );
    }

    public function get_activity_endpoint( WP_REST_Request $request ){
        $params = $request->get_params();
        $user_id = get_current_user_id();
        if ( isset( $params["user_id"] ) && !empty( $params["user_id"] ) ){
            $user_id = (int) $params["user_id"];
        }
        $include = [ 'comment', 'field_update', 'created' ];
        return self::get_user_activity( $user_id, $include );

    }

    public static function get_user_activity( $user_id, $include = [] ) {
        global $wpdb;

        if ( empty( $include ) ) {
            $allowed_actions = [ 'comment', 'field_update', 'connected_to', 'logged_in', 'created', 'disconnected_from', 'decline', 'assignment_decline' ];
        } else {
            $allowed_actions = $include;
        }

        $allowed_actions_sql = dt_array_to_sql( $allowed_actions );
        $allowed_post_types = DT_Posts::get_post_types();
        array_push( $allowed_post_types, 'post' );
        $allowed_post_types_sql = dt_array_to_sql( $allowed_post_types );

        /**
         * This hard coded array has come from the filter dt_render_field_for_display_allowed_types
         * and needs to be refactored for both here and there.
         */
        $allowed_field_types = [ 'key_select', 'multi_select', 'date', 'datetime', 'text', 'textarea', 'number', 'connection', 'location', 'location_meta', 'communication_channel', 'tags', 'user_select' ];
        array_push( $allowed_field_types, '' );
        $allowed_field_types_sql = dt_array_to_sql( $allowed_field_types );

        //phpcs:disable
        $user_activity = $wpdb->get_results( $wpdb->prepare( "
            SELECT hist_time, action, object_name, meta_key, meta_value, object_type, object_id, object_subtype, object_note, p.post_type, a.field_type
            FROM $wpdb->dt_activity_log a
            LEFT JOIN $wpdb->posts p
            ON a.object_id = p.ID
            WHERE user_id = %s
            AND action IN ( $allowed_actions_sql )
            AND p.post_type IN ( $allowed_post_types_sql )
            AND a.field_type IN ( $allowed_field_types_sql )
            ORDER BY `hist_time` DESC
            LIMIT 100
        ", $user_id ) );
        //phpcs:enable

        if ( ! empty( $user_activity ) ) {

            foreach ( $user_activity as $a ) {
                $post_settings = DT_Posts::get_post_settings( $a->post_type );
                $post_fields = DT_Posts::get_post_field_settings( $a->post_type );
                $a->object_note = DT_Posts::format_activity_message( $a, $post_settings );

                $a->icon = apply_filters( 'dt_record_icon', null, $a->post_type, [] );
                $a->post_type_label = $a->post_type ? DT_Posts::get_label_for_post_type( $a->post_type ) : null;

                if ( $a->object_subtype === "title" ){
                    $a->object_subtype = "name";
                }

                if ( $a->action === 'field_update' || $a->action === 'connected to' || $a->action === 'disconnected from' ) {
                    $a->object_note_short = __( "Updated fields", 'disciple_tools' );
                    $a->field = $a->action === 'field_update' ? $post_fields[ $a->object_subtype ]["name"] : null;
                }
                if ( $a->action == 'comment' ) {
                    $a->object_note_short = __( "Made %n comments", "disciple_tools" );
                }
                if ( $a->action == 'created' ) {
                    $a->object_note = __( 'Created record', 'disciple_tools' );
                }
                if ( $a->action === "logged_in" ) {
                    $a->object_note_short = __( "Logged In %n times", 'disciple_tools' );
                    $a->object_note = __( "Logged In", 'disciple_tools' );
                }
                if ( $a->action === 'assignment_decline' ) {
                    $a->object_note = sprintf( _x( "Declined assignment on %s", 'Declined assignment on Bob', 'disciple_tools' ), $a->object_name );
                }
            }
        }

        return $user_activity;
    }

    /**
     * Get a summary of how many contacts have been assigned to a user
     * @param $user_id
     * @return array
     * returns [
     *  "this_month" => 9,
     *  "last_month" => 8,
     *  "this_year" => 20,
     *  "all_time" => 93
     * ]
     */
    public static function get_user_assigned_contacts_summary( $user_id ){
        global $wpdb;
        $month_start = strtotime( gmdate( 'Y-m-01' ) );
        $last_month_start = strtotime( 'first day of last month' );
        $this_year = strtotime( "first day of january this year" );
        return $wpdb->get_results($wpdb->prepare("
            SELECT
            COUNT( CASE WHEN date_assigned.hist_time >= %d THEN 1 END ) as this_month,
            COUNT( CASE WHEN date_assigned.hist_time >= %d AND date_assigned.hist_time < %d THEN 1 END ) as last_month,
            COUNT( CASE WHEN date_assigned.hist_time >= %d THEN 1 END ) as this_year,
            COUNT( date_assigned.histid ) as all_time
            FROM $wpdb->dt_activity_log as date_assigned
            INNER JOIN $wpdb->postmeta as type ON ( date_assigned.object_id = type.post_id AND type.meta_key = 'type' AND type.meta_value != 'user' )
            WHERE date_assigned.meta_key = 'assigned_to'
                AND date_assigned.object_type = 'contacts'
                AND date_assigned.meta_value = %s
        ", $month_start, $last_month_start, $month_start, $this_year, 'user-' . $user_id), ARRAY_A);
    }

    /**
     * Get the number of current active contacts for a user
     * @param $user_id
     * @return string|null
     */
    public static function get_user_active_contacts_count( $user_id ){
        global $wpdb;
        return $wpdb->get_var( $wpdb->prepare( "
            SELECT count(a.ID)
            FROM $wpdb->posts as a
            INNER JOIN $wpdb->postmeta as assigned_to
            ON a.ID=assigned_to.post_id
              AND assigned_to.meta_key = 'assigned_to'
              AND assigned_to.meta_value = CONCAT( 'user-', %s )
            JOIN $wpdb->postmeta as b
              ON a.ID=b.post_id
                 AND b.meta_key = 'overall_status'
                     AND b.meta_value = 'active'
            WHERE a.post_status = 'publish'
            AND post_type = 'contacts'
            AND a.ID NOT IN (
                SELECT post_id FROM $wpdb->postmeta
                WHERE meta_key = 'type' AND meta_value = 'user'
                GROUP BY post_id
            )
        ", $user_id ) );
    }

    /**
     * Get the number of unread notification for a user
     * @param $user_id
     * @return string|null
     */
    public static function get_user_unread_notifications_count( $user_id ){
        global $wpdb;
        return $wpdb->get_var($wpdb->prepare(
            "SELECT count(id)
                FROM `$wpdb->dt_notifications`
                WHERE
                user_id = %d
                AND is_new = '1'",
            $user_id
        ));
    }

    public static function get_user_last_activity(){

    }

    /**
     * Get 10 oldest active contact than have no seeker path progress recorded
     * @param $user_id
     * @return array
     */
    public static function get_user_oldest_active_contacts_with_no_seeker_path( $user_id ){
        global $wpdb;
        $user_assigned_to = 'user-' . esc_sql( $user_id );

        return $wpdb->get_results( $wpdb->prepare( "
            SELECT contacts.ID,
                MAX(date_assigned.hist_time) as date_assigned,
                %d - MAX(date_assigned.hist_time) as time,
                contacts.post_title as name
            from $wpdb->posts as contacts
            INNER JOIN $wpdb->postmeta as pm on ( contacts.ID = pm.post_id AND pm.meta_key = 'assigned_to' )
            INNER JOIN $wpdb->postmeta as pm1 on ( contacts.ID = pm1.post_id AND pm1.meta_key = 'seeker_path' and pm1.meta_value = 'none' )
            INNER JOIN $wpdb->postmeta as pm2 on ( contacts.ID = pm2.post_id AND pm2.meta_key = 'overall_status' and pm2.meta_value = 'active' )
            INNER JOIN $wpdb->dt_activity_log as date_assigned on (
                date_assigned.meta_key = 'assigned_to'
                AND date_assigned.object_type = 'contacts'
                AND date_assigned.object_id = contacts.ID
                AND date_assigned.meta_value = %s )
            WHERE pm.meta_value = %s
            AND contacts.ID NOT IN (
                SELECT post_id FROM $wpdb->postmeta
                WHERE meta_key = 'type' AND meta_value = 'user'
                GROUP BY post_id )
            GROUP by contacts.ID
            ORDER BY date_assigned asc
            LIMIT 10
        ", time(), $user_assigned_to, $user_assigned_to ), ARRAY_A);
    }

    /**
     * Get the last 10 times from contact assignment to contact accept for a user
     * @param $user_id
     * @return array|object|null
     */
    public static function get_user_time_to_contact_accept( $user_id ) {
        global $wpdb;
        $user_assigned_to = 'user-' . esc_sql( $user_id );

        return $wpdb->get_results( $wpdb->prepare( "
            SELECT contacts.ID,
                MAX(date_assigned.hist_time) as date_assigned,
                MIN(date_accepted.hist_time) as date_accepted,
                MIN(date_accepted.hist_time) - MAX(date_assigned.hist_time) as time,
                contacts.post_title as name
            from $wpdb->posts as contacts
            INNER JOIN $wpdb->postmeta as pm on ( contacts.ID = pm.post_id AND pm.meta_key = 'assigned_to' )
            INNER JOIN $wpdb->dt_activity_log as date_accepted on (
                date_accepted.meta_key = 'overall_status'
                AND date_accepted.object_type = 'contacts'
                AND date_accepted.object_id = contacts.ID
                AND date_accepted.meta_value = 'active' )
            INNER JOIN $wpdb->dt_activity_log as date_assigned on (
                date_assigned.meta_key = 'assigned_to'
                AND date_assigned.object_type = 'contacts'
                AND date_assigned.object_id = contacts.ID
                AND date_assigned.user_id != %d
                AND date_assigned.meta_value = %s )
            WHERE date_accepted.hist_time > date_assigned.hist_time
            AND pm.meta_value = %s
            AND date_assigned.hist_time = (
                SELECT MAX(hist_time) FROM $wpdb->dt_activity_log a WHERE
                a.meta_key = 'assigned_to'
                AND a.object_type = 'contacts'
                AND a.object_id = contacts.ID )
            AND contacts.ID NOT IN (
                SELECT post_id FROM $wpdb->postmeta
                WHERE meta_key = 'type' AND meta_value = 'user'
                GROUP BY post_id )
            GROUP by contacts.ID
            ORDER BY date_accepted desc
            LIMIT 10
        ", esc_sql( $user_id ), $user_assigned_to, $user_assigned_to ), ARRAY_A);
    }

    /**
     * Get the last 10 times from contact assignment to contact attempt for a user
     * @param $user_id
     * @return array
     */
    public static function get_user_time_to_contact_attempt( $user_id ) {
        global $wpdb;
        $user_assigned_to = 'user-' . esc_sql( $user_id );

        return $wpdb->get_results( $wpdb->prepare( "
            SELECT contacts.ID,
                MAX(date_assigned.hist_time) as date_assigned,
                MIN(date_attempted.hist_time) as date_attempted,
                MIN(date_attempted.hist_time) - MAX(date_assigned.hist_time) as time,
                contacts.post_title as name
            from $wpdb->posts as contacts
            INNER JOIN $wpdb->postmeta as pm on ( contacts.ID = pm.post_id AND pm.meta_key = 'assigned_to' )
            INNER JOIN $wpdb->dt_activity_log as date_attempted on (
                date_attempted.meta_key = 'seeker_path'
                AND date_attempted.object_type = 'contacts'
                AND date_attempted.object_id = contacts.ID
                AND date_attempted.meta_value ='attempted' )
            INNER JOIN $wpdb->dt_activity_log as date_assigned on (
                date_assigned.meta_key = 'assigned_to'
                AND date_assigned.object_type = 'contacts'
                AND date_assigned.object_id = contacts.ID
                AND date_assigned.meta_value = %s )
            WHERE date_attempted.hist_time > date_assigned.hist_time
            AND pm.meta_value = %s
            AND date_assigned.hist_time = (
                SELECT MAX(hist_time) FROM $wpdb->dt_activity_log a WHERE
                a.meta_key = 'assigned_to'
                AND a.object_type = 'contacts'
                AND a.object_id = contacts.ID )
            AND contacts.ID NOT IN (
                SELECT post_id FROM $wpdb->postmeta
                WHERE meta_key = 'type' AND meta_value = 'user'
                GROUP BY post_id )
            GROUP by contacts.ID
            ORDER BY date_attempted desc
            LIMIT 10
        ", $user_assigned_to, $user_assigned_to ), ARRAY_A);
    }

    /**
     * Get the older 10 unaccepted contacts for a user
     * @param $user_id
     * @return array
     */
    public static function get_user_oldest_unaccepted_contacts( $user_id ) {
        global $wpdb;
        $user_assigned_to = 'user-' . esc_sql( $user_id );

        return $wpdb->get_results( $wpdb->prepare( "
            SELECT contacts.ID,
                MAX(date_assigned.hist_time) as date_assigned,
                %d - MAX(date_assigned.hist_time) as time,
                contacts.post_title as name
            from $wpdb->posts as contacts
            INNER JOIN $wpdb->postmeta as pm on ( contacts.ID = pm.post_id AND pm.meta_key = 'assigned_to' )
            INNER JOIN $wpdb->postmeta as pm1 on ( contacts.ID = pm1.post_id AND pm1.meta_key = 'overall_status' and pm1.meta_value = 'assigned' )
            INNER JOIN $wpdb->dt_activity_log as date_assigned on (
                date_assigned.meta_key = 'assigned_to'
                AND date_assigned.object_type = 'contacts'
                AND date_assigned.object_id = contacts.ID
                AND date_assigned.meta_value = %s )
            WHERE pm.meta_value = %s
            AND contacts.ID NOT IN (
                SELECT post_id FROM $wpdb->postmeta
                WHERE meta_key = 'type' AND meta_value = 'user'
                GROUP BY post_id )
            GROUP by contacts.ID
            ORDER BY date_assigned asc
            LIMIT 10
        ", time(), $user_assigned_to, $user_assigned_to ), ARRAY_A);
    }

    public static function get_user_days_active_chart_data( $user_id ){
        global $wpdb;
        $one_year = time() - 3600 * 24 * 365;
        $days_active_results = $wpdb->get_results( $wpdb->prepare( "
            SELECT FROM_UNIXTIME(`hist_time`, '%%Y-%%m-%%d') as day,
            count(histid) as activity_count
            FROM $wpdb->dt_activity_log
            WHERE user_id = %s
            AND hist_time > %s
            group by day
            ORDER BY `day` ASC",
            $user_id,
            $one_year
        ), ARRAY_A );
        $days_active = [];
        foreach ( $days_active_results as $a ) {
            $days_active[$a["day"]] = $a;
        }
        $first = isset( $days_active_results[0]['day'] ) ? strtotime( $days_active_results[0]['day'] ) : time();
        $first_week_start = gmdate( 'Y-m-d', strtotime( '-' . gmdate( 'w', $first ) . ' days', $first ) );
        $current = strtotime( $first_week_start );
        $daily_activity = [];
        while ( $current < time() ) {

            $activity = $days_active[gmdate( 'Y-m-d', $current )]["activity_count"] ?? 0;

            $daily_activity[] = [
                "day" => dt_format_date( $current ),
                "weekday" => gmdate( 'l', $current ),
                "weekday_number" => gmdate( 'N', $current ),
                "week_start" => gmdate( 'Y-m-d', strtotime( '-' . gmdate( 'w', $current ) . ' days', $current ) ),
                "activity_count" => $activity,
                "activity" => $activity > 0 ? 1 : 0
            ];

            $current += 24 * 60 * 60;
        }
        return $daily_activity;
    }
}
