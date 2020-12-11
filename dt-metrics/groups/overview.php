<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly.


class DT_Metrics_Groups_Overview extends DT_Metrics_Chart_Base
{
    //slug and title of the top menu folder
    public $base_slug = 'groups'; // lowercase
    public $slug = 'overview'; // lowercase
    public $title;
    public $base_title;
    public $js_object_name = 'wp_js_object'; // This object will be loaded into the metrics.js file by the wp_localize_script.
    public $js_file_name = '/dt-metrics/groups/overview.js'; // should be full file name plus extension
    public $permissions = [ 'dt_all_access_contacts', 'view_project_metrics' ];

    public function __construct() {
        parent::__construct();
        if ( !$this->has_permission() ){
            return;
        }
        $this->base_title = __( 'Groups', 'disciple_tools' );
        $this->title = __( 'Overview', 'disciple_tools' );

        $url_path = dt_get_url_path();
        if ( "metrics/$this->base_slug/$this->slug" === $url_path ) {
            add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ], 99 );
        }
    }

    public function scripts() {
        wp_register_script( 'amcharts-core', 'https://www.amcharts.com/lib/4/core.js', false, '4' );
        wp_register_script( 'amcharts-charts', 'https://www.amcharts.com/lib/4/charts.js', false, '4' );
        wp_register_script( 'amcharts-animated', 'https://www.amcharts.com/lib/4/themes/animated.js', [ 'amcharts-core' ], '4' );

        wp_enqueue_script( 'dt_metrics_project_script', get_template_directory_uri() . $this->js_file_name, [
            'jquery',
            'jquery-ui-core',
            'amcharts-core',
            'amcharts-charts',
            'amcharts-animated',
            'lodash'
        ], filemtime( get_theme_file_path() . $this->js_file_name ), true );

        wp_localize_script(
            'dt_metrics_project_script', 'dtMetricsProject', [
                'root' => esc_url_raw( rest_url() ),
                'theme_uri' => get_template_directory_uri(),
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'current_user_login' => wp_get_current_user()->user_login,
                'current_user_id' => get_current_user_id(),
                'data' => $this->data(),
            ]
        );
    }

    public function data() {
        return [
            'translations' => [
                'title_overview' => __( 'Project Overview', 'disciple_tools' ),
                'title_groups_overview' => __( 'Groups Overview', 'disciple_tools' ),
                'title_contacts' => __( 'Contacts', 'disciple_tools' ),
                'title_groups' => __( 'Groups', 'disciple_tools' ),
                'title_all_contacts' => __( 'All Contacts', 'disciple_tools' ),
                'title_active_contacts' => __( 'Active Contacts', 'disciple_tools' ),
                'title_waiting_on_accept' => __( 'Waiting on Accept', 'disciple_tools' ),
                'title_waiting_on_update' => __( 'Waiting on Update', 'disciple_tools' ),
                'title_total_groups' => __( 'Total Groups', 'disciple_tools' ),
                'title_generations' => __( 'Group and Church Generations', 'disciple_tools' ),
                'title_group_types' => __( 'Group Types', 'disciple_tools' ),
                'title_teams' => __( 'Teams', 'disciple_tools' ),
                'label_follow_up_progress' => __( 'Follow-up of all active contacts', 'disciple_tools' ),
                'label_group_needs_training' => __( 'Active Group Health Metrics', 'disciple_tools' ),
                'label_groups' => strtolower( __( 'groups', 'disciple_tools' ) ),
                'label_generations' => strtolower( __( 'generations', 'disciple_tools' ) ),
                'label_generation' => __( 'Generation', 'disciple_tools' ),
                'label_group_types' => __( 'Group Types', 'disciple_tools' ),
                'label_pre_group' => __( 'Pre-Group', 'disciple_tools' ),
                'label_group' => __( 'Group', 'disciple_tools' ),
                'label_church' => __( 'Church', 'disciple_tools' ),
            ],
            'preferences' => $this->preferences(),
            'hero_stats' => $this->chart_project_hero_stats(),
            'group_types' => $this->chart_group_types(),
            'group_health' => $this->chart_group_health(),
            'group_generations' => Disciple_Tools_Counter::critical_path( 'all_group_generations', 0, PHP_INT_MAX ),
        ];
    }

    public function preferences() {
        $data = [];

        /* Add group preferences*/
        $group_preferences = dt_get_option( 'group_preferences' );
        $data['groups'] = [
            'church_metrics' => $group_preferences['church_metrics'] ?? false,
            'four_fields' => $group_preferences['four_fields'] ?? false,
        ];

        return $data;
    }

    public function chart_group_types() {
        $chart = [];

        $group_fields = DT_Posts::get_post_field_settings( "groups" );
        $types = $group_fields["group_type"]["default"];

        $results = $this->query_project_group_types();
        foreach ( $results as $result ) {
            $result["label"] = $types[$result['type']]["label"] ?? $result['type'];
            $chart[] = $result;
        }

        return $chart;
    }

    public function chart_group_health() {

        // Make key list
        $group_fields = DT_Posts::get_post_field_settings( "groups" );
        $labels = [];

        foreach ( $group_fields["health_metrics"]["default"] as $key => $option ) {
            $labels[$key] = $option["label"];
        }

        $chart = [];

        $results = self::query_project_group_health();

        if ( $results ) {
            $out_of = 0;
            if ( isset( $results[0]['out_of'] ) ) {
                $out_of = $results[0]['out_of'];
            }
            foreach ( $labels as $label_key => $label_value ) {
                $row = [
                    "label"      => $label_value,
                    "practicing" => 0,
                    "remaining"  => (int) $out_of
                ];
                foreach ( $results as $result ) {
                    if ( $result['health_key'] === $label_key ) {
                        $row["practicing"] = (int) $result["count"];
                        $row["remaining"]  = intval( $result['out_of'] ) - intval( $result['count'] );
                    }
                }
                $chart[] = $row;
            }
        }
        return $chart;
    }

    public function chart_project_hero_stats() {

        $stats = $this->query_project_hero_stats();
        $group_health = $this->query_project_group_health();
        $needs_training = 0; // @todo remove

        if ( ! empty( $group_health ) ) {
            foreach ( $group_health as $value ) {
                $count = intval( $value['out_of'] ) - intval( $value['count'] );
                if ( $count > $needs_training ) {
                    $needs_training = $count;
                }
            }
        }

        $results = [
            'total_contacts' => $stats["total_contacts"], // @todo remove
            'active_contacts' => $stats['active_contacts'], // @todo remove
            'needs_accepted' => $stats['needs_accept'], // @todo remove
            'updates_needed' => $stats['needs_update'], // @todo remove
            'total_groups' => $stats['groups'],
            'needs_training' => $needs_training,
            'fully_practicing' => (int) $stats['groups'] - (int) $needs_training, // @todo
            'teams' => $stats['teams'],
            'generations' => 0, // @todo remove
        ];

        return $results;
    }

    public function query_project_group_health() {
        global $wpdb;

        $results = $wpdb->get_results( "
            SELECT d.meta_value as health_key,
              count(distinct(a.ID)) as count,
              ( SELECT count(*)
              FROM $wpdb->posts as a
                JOIN $wpdb->postmeta as c
                  ON a.ID=c.post_id
                     AND c.meta_key = 'group_status'
                     AND c.meta_value = 'active'
                JOIN $wpdb->postmeta as d
                  ON a.ID=d.post_id
                     AND d.meta_key = 'group_type'
                     AND ( d.meta_value = 'group' OR d.meta_value = 'church' )
              WHERE a.post_status = 'publish'
                    AND a.post_type = 'groups'
              ) as out_of
              FROM $wpdb->posts as a
                JOIN $wpdb->postmeta as c
                  ON a.ID=c.post_id
                     AND c.meta_key = 'group_status'
                     AND c.meta_value = 'active'
                JOIN $wpdb->postmeta as d
                  ON ( a.ID=d.post_id
                    AND d.meta_key = 'health_metrics' )
                JOIN $wpdb->postmeta as e
                  ON a.ID=e.post_id
                     AND e.meta_key = 'group_type'
                     AND ( e.meta_value = 'group' OR e.meta_value = 'church' )
              WHERE a.post_status = 'publish'
                    AND a.post_type = 'groups'
              GROUP BY d.meta_value
        ", ARRAY_A );

        return $results;
    }

    public function query_project_group_types() {
        global $wpdb;

        $results = $wpdb->get_results( "
            SELECT c.meta_value as type, count( a.ID ) as count
                FROM $wpdb->posts as a
                JOIN $wpdb->postmeta as b
                    ON a.ID=b.post_id
                    AND b.meta_key = 'group_status'
                    AND b.meta_value = 'active'
                JOIN $wpdb->postmeta as c
                    ON a.ID=c.post_id
                    AND c.meta_key = 'group_type'
                    AND c.meta_value != 'team'
                WHERE a.post_status = 'publish'
                    AND a.post_type = 'groups'
                GROUP BY type
                ORDER BY type DESC
        ", ARRAY_A );

        return $results;
    }



    public function query_project_hero_stats() {
        global $wpdb;

        $numbers = [];
        $numbers["total_contacts"] = Disciple_Tools_Counter::critical_path( 'new_contacts' );

        $results = $wpdb->get_results( "
            SELECT (
                SELECT count(a.ID)
                FROM $wpdb->posts as a
                JOIN $wpdb->postmeta as b
                ON a.ID = b.post_id
                AND b.meta_key = 'overall_status'
                AND b.meta_value = 'active'
                WHERE a.post_status = 'publish'
                AND a.post_type = 'contacts'
                AND a.ID NOT IN (
                    SELECT post_id FROM $wpdb->postmeta
                    WHERE meta_key = 'type' AND meta_value = 'user'
                    GROUP BY post_id
                )
            )
            as active_contacts,
            (SELECT count(a.ID)
                FROM $wpdb->posts as a
                    JOIN $wpdb->postmeta as b
                    ON a.ID=b.post_id
                       AND b.meta_key = 'accepted'
                       AND (b.meta_value = '' OR b.meta_value = 'no')
                JOIN $wpdb->postmeta as d
                ON a.ID=d.post_id
                   AND d.meta_key = 'overall_status'
                   AND d.meta_value = 'assigned'
                WHERE a.post_status = 'publish'
                AND a.post_type = 'contacts'
                AND a.ID NOT IN (
                    SELECT post_id FROM $wpdb->postmeta
                    WHERE meta_key = 'type' AND meta_value = 'user'
                    GROUP BY post_id
                )
            )
            as needs_accept,
            (SELECT count(a.ID)
                FROM $wpdb->posts as a
                    JOIN $wpdb->postmeta as b
                    ON a.ID=b.post_id
                       AND b.meta_key = 'requires_update'
                       AND b.meta_value = '1'
                JOIN $wpdb->postmeta as d
                ON a.ID=d.post_id
                    AND d.meta_key = 'overall_status'
                AND d.meta_value = 'active'
                WHERE a.post_status = 'publish'
                AND a.post_type = 'contacts'
                AND a.ID NOT IN (
                    SELECT post_id FROM $wpdb->postmeta
                    WHERE meta_key = 'type' AND meta_value = 'user'
                    GROUP BY post_id
                )
            )
            as needs_update,
            (SELECT count(a.ID)
                FROM $wpdb->posts as a
                JOIN $wpdb->postmeta as d
                ON a.ID=d.post_id
                    AND d.meta_key = 'group_status'
                AND d.meta_value = 'active'
                JOIN $wpdb->postmeta as e
                  ON a.ID=e.post_id
                     AND e.meta_key = 'group_type'
                     AND e.meta_value != 'team'
                WHERE a.post_status = 'publish'
                AND a.post_type = 'groups')
            as `groups`,
            (SELECT count(a.ID)
                FROM $wpdb->posts as a
                JOIN $wpdb->postmeta as d
                ON a.ID=d.post_id
                    AND d.meta_key = 'group_status'
                AND d.meta_value = 'active'
                JOIN $wpdb->postmeta as e
                  ON a.ID=e.post_id
                     AND e.meta_key = 'group_type'
                     AND e.meta_value = 'team'
                WHERE a.post_status = 'publish'
                AND a.post_type = 'groups')
            as `teams`
        ",
        ARRAY_A );

        if ( empty( $results ) ) {
            return new WP_Error( __METHOD__, 'No results from the personal count query' );
        }

        foreach ( $results[0] as $key => $value ) {
            $numbers[$key] = $value;
        }

        return $numbers;
    }

}
new DT_Metrics_Groups_Overview();
