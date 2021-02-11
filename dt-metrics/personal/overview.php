<?php

class Disciple_Tools_Metrics_Personal_Overview extends DT_Metrics_Chart_Base
{

    //slug and title of the top menu folder
    public $base_slug = 'personal'; // lowercase
    public $slug = 'overview'; // lowercase
    public $base_title;

    public $title;
    public $js_object_name = 'wp_js_object'; // This object will be loaded into the metrics.js file by the wp_localize_script.
    public $js_file_name = '/dt-metrics/personal/overview.js'; // should be full file name plus extension
    public $permissions = [ 'access_contacts' ];
    public $namespace = null;

    public function __construct() {
        if ( !$this->has_permission() ){
            return;
        }
        parent::__construct();
        $this->title = __( 'Overview', 'disciple_tools' );
        $this->base_title = __( 'Personal', 'disciple_tools' );

        $url_path = dt_get_url_path();
        if ( "metrics/$this->base_slug/$this->slug" === $url_path || "metrics" === $url_path ) {
            add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ], 10 );
        }
    }

    public function scripts() {
        wp_register_script( 'amcharts-core', 'https://www.amcharts.com/lib/4/core.js', false, '4' );
        wp_register_script( 'amcharts-charts', 'https://www.amcharts.com/lib/4/charts.js', false, '4' );
        wp_register_script( 'amcharts-animated', 'https://www.amcharts.com/lib/4/themes/animated.js', [ 'amcharts-core' ], '4' );

        wp_enqueue_script( 'dt_metrics_personal_script', get_template_directory_uri() . $this->js_file_name, [
            'jquery',
            'jquery-ui-core',
            'amcharts-core',
            'amcharts-charts',
            'lodash'
        ], filemtime( get_theme_file_path() .  $this->js_file_name ), true );

        wp_localize_script(
            'dt_metrics_personal_script', 'dtMetricsPersonal', [
                'root' => esc_url_raw( rest_url() ),
                'theme_uri' => get_template_directory_uri(),
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'current_user_login' => wp_get_current_user()->user_login,
                'current_user_id' => get_current_user_id(),
                'data' => $this->overview(),
            ]
        );
    }

    public function overview() {
        $data = [
            'translations' => [
                'title' => __( 'My Overview', 'disciple_tools' ),
                'title_waiting_on_accept' => __( 'Waiting on Accept', 'disciple_tools' ),
                'title_waiting_on_update' => __( 'Waiting on Update', 'disciple_tools' ),
                'title_contacts' => __( 'Contacts', 'disciple_tools' ),
                'title_groups' => __( 'Groups', 'disciple_tools' ),
                'title_total_groups' => __( 'Total Groups', 'disciple_tools' ),
                'title_group_types' => __( 'Group Types', 'disciple_tools' ),
                'title_generations' => __( 'Group and Church Generations', 'disciple_tools' ),
                'title_teams' => __( 'Lead Teams', 'disciple_tools' ),
                'label_active_contacts'  => __( 'Active Contacts', 'disciple_tools' ),
                'total_groups'    => __( 'Total Groups', 'disciple_tools' ),
                'label_my_follow_up_progress' => __( 'Follow-up of my active contacts', 'disciple_tools' ),
                'label_group_needing_training' => __( 'Active Group Health Metrics', 'disciple_tools' ),
                'label_stats_as_of' => strtolower( __( 'stats as of', 'disciple_tools' ) ),
                'label_pre_group' => __( 'Pre-Group', 'disciple_tools' ),
                'label_group' => __( 'Group', 'disciple_tools' ),
                'label_church' => __( 'Church', 'disciple_tools' ),
                'label_generation' => __( 'Generation', 'disciple_tools' ),
            ],
            'preferences'       => $this->preferences(),
            'hero_stats'        => $this->chart_my_hero_stats(),
            'group_types'       => $this->chart_group_types(),
            'group_health'      => $this->chart_group_health(),
            'group_generations' => $this->chart_group_generations(),
        ];
        $modules = dt_get_option( "dt_post_type_modules" );
        if ( !empty( $modules["access_module"]["enabled"] ) ){
            $data['contacts_progress'] = $this->chart_contacts_progress();
        }

        return apply_filters( 'dt_my_metrics', $data );
    }

    public function preferences() {
        $data = [];

        /* Add group preferences*/
        $group_preferences = dt_get_option( 'group_preferences' );
        $data['groups'] = [
            'church_metrics' => $group_preferences['church_metrics'] ?? false,
            'four_fields' => $group_preferences['four_fields'] ?? false,
        ];

        /* Add other preferences. Please, categorize by section, i.e. contacts, groups, etc */

        return $data;
    }



    public function chart_my_hero_stats( $user_id = null ) {
        if ( is_null( $user_id ) || empty( $user_id ) ) {
            $user_id = get_current_user_id();
        }

        $results = self::query_my_hero_stats( $user_id );

        $group_health = self::query_my_group_health();
        $needs_training = 0;

        if ( ! empty( $group_health ) ) {
            foreach ( $group_health as $value ) {
                $count = intval( $value['out_of'] ) - intval( $value['count'] );
                if ( $count > $needs_training ) {

                    $needs_training = $count;
                }
            }
        }

        $chart = [
            'contacts' => $results['contacts'],
            'needs_accept' => $results['needs_accept'],
            'needs_update' => $results['needs_update'],
            'groups' => $results['groups'],
            'needs_training' => $needs_training,
            'fully_practicing' => (int) $results['groups'] - (int) $needs_training,
            'teams' => (int) $results['teams'],
        ];

        return $chart;
    }

    public static function chart_group_generations( $type = 'personal' ) {
        $user_id = get_current_user_id();
        $generation_tree  = Disciple_Tools_Counter::critical_path( 'all_group_generations', 0, PHP_INT_MAX, [ 'assigned_to' => $user_id ] );
        return $generation_tree;
    }

    public function chart_contacts_progress( $type = 'personal' ) {
        $chart = [];

        $results = $this->query_my_contacts_progress( get_current_user_id() );
        foreach ( $results as $value ) {
            $chart[] = [
                'label' => $value['label'],
                'value' => $value['count']
            ];
        }

        return $chart;
    }

    public function chart_group_types( $type = 'personal' ) {

        $chart = [];

        $group_fields = DT_Posts::get_post_field_settings( "groups" );
        $types = $group_fields["group_type"]["default"];

        $results = $this->query_my_group_types();
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

        $results = $this->query_my_group_health();

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

    public function query_my_hero_stats( $user_id = null ) {
        global $wpdb;
        $numbers = [];

        if ( is_null( $user_id ) ) {
            $user_id = get_current_user_id();
        }
        $personal_counts = $wpdb->get_results( $wpdb->prepare( "
            SELECT (
              SELECT count(a.ID)
                FROM $wpdb->posts as a
                  JOIN $wpdb->postmeta as d
                    ON a.ID=d.post_id
                      AND d.meta_key = 'overall_status'
                      AND d.meta_value = 'active'
                  JOIN $wpdb->postmeta as b
                    ON a.ID=b.post_id
                      AND b.meta_key = 'assigned_to'
                      AND b.meta_value = CONCAT( 'user-', %s )
                WHERE a.post_status = 'publish'
                AND a.post_type = 'contacts'
                AND a.ID NOT IN (
                    SELECT post_id FROM $wpdb->postmeta
                    WHERE meta_key = 'type' AND meta_value = 'user'
                    GROUP BY post_id
                ))
              as contacts,

              (SELECT count(a.ID)
                FROM $wpdb->posts as a
                  JOIN $wpdb->postmeta as b
                    ON a.ID=b.post_id
                      AND b.meta_key = 'accepted'
                      AND b.meta_value = ''
                  JOIN $wpdb->postmeta as c
                    ON a.ID=c.post_id
                      AND c.meta_key = 'assigned_to'
                      AND c.meta_value = CONCAT( 'user-', %s )
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
              ) as needs_accept,

              (SELECT count(a.ID)
                FROM $wpdb->posts as a
                  JOIN $wpdb->postmeta as b
                    ON a.ID=b.post_id
                      AND b.meta_key = 'requires_update'
                      AND b.meta_value = '1'
                  JOIN $wpdb->postmeta as c
                    ON a.ID=c.post_id
                      AND c.meta_key = 'assigned_to'
                      AND c.meta_value = CONCAT( 'user-', %s )
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
              ) as needs_update,

              (SELECT count(a.ID)
                FROM $wpdb->posts as a
                  JOIN $wpdb->postmeta as c
                    ON a.ID=c.post_id
                      AND c.meta_key = 'assigned_to'
                      AND c.meta_value = CONCAT( 'user-', %s )
                  JOIN $wpdb->postmeta as d
                    ON a.ID=d.post_id
                      AND d.meta_key = 'group_status'
                      AND d.meta_value = 'active'
                WHERE a.post_status = 'publish'
                  AND a.post_type = 'groups')
                as `groups`,

                (SELECT count(a.ID)
                FROM $wpdb->posts as a
                  JOIN $wpdb->postmeta as c
                    ON a.ID=c.post_id
                      AND c.meta_key = 'assigned_to'
                      AND c.meta_value = CONCAT( 'user-', %s )
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
            $user_id,
            $user_id,
            $user_id,
            $user_id,
            $user_id
        ), ARRAY_A );

        if ( empty( $personal_counts ) ) {
            return new WP_Error( __METHOD__, 'No results from the personal count query' );
        }

        foreach ( $personal_counts[0] as $key => $value ) {
            $numbers[$key] = $value;
        }

        return $numbers;
    }

    public function query_my_group_types( $user_id = null ) {
        global $wpdb;

        if ( is_null( $user_id ) ) {
            $user_id = get_current_user_id();
        }

        $results = $wpdb->get_results( $wpdb->prepare( "
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
            JOIN $wpdb->postmeta as d
                 ON a.ID=d.post_id
                    AND d.meta_key = 'assigned_to'
                    AND d.meta_value = %s
            WHERE a.post_status = 'publish'
                  AND a.post_type = 'groups'
            GROUP BY type
            ORDER BY type DESC
        ",
        'user-' . $user_id ), ARRAY_A );

        return $results;
    }

    public function query_my_group_health( $user_id = null ) {
        global $wpdb;

        if ( is_null( $user_id ) ) {
            $user_id = get_current_user_id();
        }

        $results = $wpdb->get_results( $wpdb->prepare( "
            SELECT d.meta_value as health_key,
              count(distinct(a.ID)) as count,
              ( SELECT count(*)
              FROM $wpdb->posts as a
                JOIN $wpdb->postmeta as b
                  ON a.ID=b.post_id
                     AND b.meta_key = 'assigned_to'
                     AND b.meta_value = %s
                JOIN $wpdb->postmeta as c
                  ON a.ID=c.post_id
                     AND c.meta_key = 'group_status'
                     AND c.meta_value = 'active'
                JOIN $wpdb->postmeta as e
                  ON a.ID=e.post_id
                     AND e.meta_key = 'group_type'
                     AND ( e.meta_value = 'group' OR e.meta_value = 'church' )
              WHERE a.post_status = 'publish'
                    AND a.post_type = 'groups'
              ) as out_of
              FROM $wpdb->posts as a
                JOIN $wpdb->postmeta as b
                  ON a.ID=b.post_id
                     AND b.meta_key = 'assigned_to'
                     AND b.meta_value = %s
                JOIN $wpdb->postmeta as c
                  ON a.ID=c.post_id
                     AND c.meta_key = 'group_status'
                     AND c.meta_value = 'active'
                JOIN $wpdb->postmeta as e
                  ON a.ID=e.post_id
                     AND e.meta_key = 'group_type'
                     AND ( e.meta_value = 'group' OR e.meta_value = 'church' )
                LEFT JOIN $wpdb->postmeta as d
                  ON ( a.ID=d.post_id
                  AND d.meta_key = 'health_metrics' )
              WHERE a.post_status = 'publish'
                  AND a.post_type = 'groups'
              GROUP BY d.meta_value
        ",
            'user-' . $user_id,
            'user-' . $user_id
        ), ARRAY_A );

        return $results;
    }

    public function query_my_group_generations( $user_id = null ) {
        global $wpdb;

        if ( is_null( $user_id ) ) {
            $user_id = get_current_user_id();
        }

        $results = $wpdb->get_results( $wpdb->prepare( "
            SELECT
              a.ID         as id,
              0            as parent_id,
              d.meta_value as group_type,
              c.meta_value as group_status
            FROM $wpdb->posts as a
              JOIN $wpdb->postmeta as b
                  ON a.ID=b.post_id
                     AND b.meta_key = 'assigned_to'
                     AND b.meta_value = %s
              JOIN $wpdb->postmeta as c
                ON a.ID = c.post_id
                   AND c.meta_key = 'group_status'
              JOIN $wpdb->postmeta as d
                ON a.ID = d.post_id
                   AND d.meta_key = 'group_type'
                   AND d.meta_value != 'team'
            WHERE a.post_status = 'publish'
                  AND a.post_type = 'groups'
                  AND a.ID NOT IN (
                      SELECT DISTINCT (p2p_from)
                      FROM $wpdb->p2p
                      WHERE p2p_type = 'groups_to_groups'
                      GROUP BY p2p_from)
            UNION
            SELECT
              p.p2p_from  as id,
              p.p2p_to    as parent_id,
              (SELECT meta_value
               FROM $wpdb->postmeta
               WHERE post_id = p.p2p_from
                     AND meta_key = 'group_type')
                     as group_type,
               (SELECT meta_value
               FROM $wpdb->postmeta
               WHERE post_id = p.p2p_from
                     AND meta_key = 'group_status') as group_status
            FROM $wpdb->p2p as p
            WHERE p.p2p_type = 'groups_to_groups'
        ", 'user-' . $user_id ), ARRAY_A );

        return dt_queries()->check_tree_health( $results );
    }

    public function query_my_contacts_progress( $user_id = null ) {
        global $wpdb;
        if ( empty( $user_id ) ) {
            $user_id = get_current_user_id();
        }

        $defaults = [];
        $contact_fields = DT_Posts::get_post_field_settings( "contacts" );
        $seeker_path_options = $contact_fields["seeker_path"]["default"];
        foreach ( $seeker_path_options as $key => $option ) {
            $defaults[$key] = [
                'label' => $option["label"],
                'count' => 0,
            ];
        }

        $results = $wpdb->get_results( $wpdb->prepare( "
            SELECT b.meta_value as seeker_path, count( a.ID ) as count
             FROM $wpdb->posts as a
               JOIN $wpdb->postmeta as b
                 ON a.ID=b.post_id
                    AND b.meta_key = 'seeker_path'
               JOIN $wpdb->postmeta as c
                 ON a.ID=c.post_id
                    AND c.meta_key = 'assigned_to'
                    AND c.meta_value = %s
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
             GROUP BY b.meta_value
        ",
        'user-'. $user_id ), ARRAY_A );

        $query_results = [];

        if ( ! empty( $results ) ) {
            foreach ( $results as $result ) {
                if ( isset( $defaults[$result['seeker_path']] ) ) {
                    $query_results[$result['seeker_path']] = [
                        'label' => $defaults[$result['seeker_path']]['label'],
                        'count' => intval( $result['count'] ),
                    ];
                }
            }
        }

        return wp_parse_args( $query_results, $defaults );
    }
}
new Disciple_Tools_Metrics_Personal_Overview();
