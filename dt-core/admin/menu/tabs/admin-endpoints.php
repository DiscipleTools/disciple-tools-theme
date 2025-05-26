<?php

class DT_Admin_Endpoints {
    public $namespace = 'dt-admin';
    public function __construct(){
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
    }

    public function add_api_routes(){
        register_rest_route(
            $this->namespace, '/scripts/reset_count_field', [
                'methods'  => 'POST',
                'callback' => [ $this, 'reset_count_field' ],
                'permission_callback' => function(){
                    return current_user_can( 'manage_dt' );
                },
            ]
        );
        register_rest_route(
            $this->namespace, '/scripts/reset_count_field_progress', [
                'methods'  => 'GET',
                'callback' => [ $this, 'reset_count_field_progress' ],
                'permission_callback' => function(){
                    return current_user_can( 'manage_dt' );
                },
            ]
        );
        register_rest_route(
            $this->namespace, '/scripts/update_custom_field_translations', [
                'methods'  => 'POST',
                'callback' => [ $this, 'update_custom_field_translations' ],
                'permission_callback' => function(){
                    return current_user_can( 'manage_dt' );
                },
            ]
        );
        register_rest_route(
            $this->namespace, '/scripts/process_jobs', [
                'methods'  => 'GET',
                'callback' => [ $this, 'process_jobs' ],
                'permission_callback' => function(){
                    return current_user_can( 'manage_dt' );
                },
            ]
        );
        register_rest_route(
            $this->namespace, '/scripts/data_clean_up', [
                'methods'  => 'POST',
                'callback' => [ $this, 'data_clean_up' ],
                'permission_callback' => function(){
                    return current_user_can( 'manage_dt' );
                },
            ]
        );
        register_rest_route(
            $this->namespace, '/scripts/locations_clean_up', [
                'methods'  => 'POST',
                'callback' => [ $this, 'locations_clean_up' ],
                'permission_callback' => function(){
                    return current_user_can( 'manage_dt' );
                },
            ]
        );
    }

    public function reset_count_field( WP_REST_Request $request ){
        $params = $request->get_params();
        if ( isset( $params['post_type'], $params['field_key'] ) ){
            $field_settings = DT_Posts::get_post_field_settings( $params['post_type'] );
            $field = $field_settings[$params['field_key']];
            if ( isset( $field['connection_count_field']['field_key'] ) ){
                global $wpdb;
                $posts_to_update = $wpdb->get_results( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type = %s", $params['post_type'] ), ARRAY_A );
                foreach ( $posts_to_update as $row ){
                    wp_queue()->push( new DT_Reset_Count_On_Field_Job( $params['post_type'], $row['ID'], $params['field_key'] ), 0, $params['post_type'] . '_' . $params['field_key'] );
                }
                return [
                    'count' => wp_queue_count_jobs( $params['post_type'] . '_' . $params['field_key'] )
                ];
            }
        } else {
            return new WP_Error( __FILE__, 'Missing Params post_type or field_key' );
        }
    }

    public function reset_count_field_progress( WP_REST_Request $request ){
        $params = $request->get_params();
        if ( isset( $params['post_type'], $params['field_key'] ) ){
            if ( isset( $params['process'] ) && !empty( $params['process'] ) ){
                wp_queue()->cron()->cron_worker();
            }
            return [
                'count' => (int) wp_queue_count_jobs( $params['post_type'] . '_' . $params['field_key'] ),
            ];
        } else {
            return new WP_Error( __FILE__, 'Missing Params post_type or field_key' );
        }
    }

    public function update_custom_field_translations( WP_REST_Request $request ){
        $params = $request->get_params();
        if ( isset( $params['post_type'], $params['field_id'], $params['field_type'], $params['translations'] ) ){
            $post_type = $params['post_type'];
            $field_id = $params['field_id'];
            $field_type = $params['field_type'];
            $translations = $params['translations'];
            $option_translations = $params['option_translations'] ?? [];

            // Fetch existing field customizations and if needed, create relevant spaces!
            $field_customizations = dt_get_option( 'dt_field_customizations' );
            if ( !isset( $field_customizations[$post_type][$field_id] ) ){
                $field_customizations[$post_type][$field_id] = [];
            }
            $custom_field = $field_customizations[$post_type][$field_id];

            // Capture available field name translations.
            $custom_field['translations'] = [];
            foreach ( $translations['translations'] ?? [] as $translation ){
                if ( !empty( $translation['locale'] ) && !empty( $translation['value'] ) ){
                    $custom_field['translations'][$translation['locale']] = $translation['value'];
                }
            }

            // Capture available field description translations.
            $custom_field['description_translations'] = [];
            foreach ( $translations['description_translations'] ?? [] as $translation ){
                if ( !empty( $translation['locale'] ) && !empty( $translation['value'] ) ){
                    $custom_field['description_translations'][$translation['locale']] = $translation['value'];
                }
            }

            // If required, update custom field default options.
            if ( in_array( $field_type, [ 'key_select', 'multi_select', 'link' ] ) ){
                $defaults = [];
                foreach ( $option_translations as $option ){
                    $option_key = $option['option_key'];

                    if ( !empty( $option_key ) ){
                        $defaults[$option_key] = [];
                        if ( !empty( $custom_field['default'][$option_key]['label'] ) ) {
                            $defaults[$option_key]['label'] = $custom_field['default'][$option_key]['label'];
                        }
                        $defaults[$option_key]['translations'] = [];
                        $defaults[$option_key]['description_translations'] = [];

                        // Capture option translations.
                        foreach ( $option['option_translations'] ?? [] as $option_translation ){
                            if ( !empty( $option_translation['locale'] ) && !empty( $option_translation['value'] ) ){
                                $defaults[$option_key]['translations'][$option_translation['locale']] = $option_translation['value'];
                            }
                        }

                        // Capture option description translations.
                        foreach ( $option['option_description_translations'] ?? [] as $option_description_translations ){
                            if ( !empty( $option_description_translations['locale'] ) && !empty( $option_description_translations['value'] ) ){
                                $defaults[$option_key]['description_translations'][$option_description_translations['locale']] = $option_description_translations['value'];
                            }
                        }
                    }
                }
                $custom_field['default'] = $defaults;
            }

            // Persist updated custom field option translations.
            $field_customizations[$post_type][$field_id] = $custom_field;
            update_option( 'dt_field_customizations', $field_customizations );

            // For completeness, return updated shape!
            return [
                'translations' => $custom_field['translations'] ?? [],
                'description_translations' => $custom_field['description_translations'] ?? [],
                'defaults' => $custom_field['default'] ?? []
            ];

        } else {
            return new WP_Error( __FILE__, 'Missing required parameters.' );
        }
    }

    public function data_clean_up( WP_REST_Request $request ) {
        $params = $request->get_params();
        if ( isset( $params['post_type'] ) ) {
            $post_type = $params['post_type'];

            // Build list of existing field ids.
            $field_settings = DT_Posts::get_post_field_settings( $post_type, false );
            $field_ids = array_keys( $field_settings );

            // Identify deleted field activity logs.
            global $wpdb;

            $id_sql = [];
            foreach ( $field_ids as $id ) {
                $id_sql[] = "(log.object_subtype NOT LIKE '" . esc_sql( $id ) . "%')";
            }
            $all_id_sql = implode( ' AND ', $id_sql );

            // phpcs:disable
            $deleted_fields = $wpdb->get_results(
                    $wpdb->prepare("
                SELECT DISTINCT log.object_subtype AS deleted_field
                FROM $wpdb->dt_activity_log log
                WHERE log.action IN ('field_update', 'connected to', 'disconnected from')
                    AND log.object_type = %s
                    AND $all_id_sql
            ", $post_type ), ARRAY_A );
            // phpcs:enable

            // Delete activity logs for any identified fields.
            $deleted_field_count = count( $deleted_fields );
            if ( $deleted_field_count > 0 ) {
                $delete_field_ids = [];
                foreach ( $deleted_fields as $deleted ) {
                    $delete_field_ids[] = $deleted['deleted_field'];
                }
                $array_sql = dt_array_to_sql( $delete_field_ids );

                // phpcs:disable
                $wpdb->query( $wpdb->prepare( "
                    DELETE FROM $wpdb->dt_activity_log log
                    WHERE log.object_type = %s
                        AND log.object_subtype IN ( $array_sql )
                ", $post_type ) );
                // phpcs:enable
            }

            return [
                'deleted_field_count' => $deleted_field_count
            ];
        } else {
            return new WP_Error( __FILE__, 'Missing Params post_type' );
        }
    }

    public function locations_clean_up( WP_REST_Request $request ) {
        global $wpdb;
        $delete_query = $wpdb->query("
            DELETE lm
            FROM $wpdb->dt_location_grid_meta lm
            LEFT JOIN $wpdb->postmeta pm ON ( pm.meta_id = lm.postmeta_id_location_grid )
            WHERE pm.meta_id IS NULL
        ");
        return [
            'deleted_location_count' => $delete_query
        ];
    }

    public function process_jobs( WP_REST_Request $request ){
        //no apparent way for wp_queue to report an issue
        wp_queue()->cron()->cron_worker();
        return [
            'success' => (bool) true,
            'remaining' => wp_queue_count_jobs(),
        ];
    }
}

use WP_Queue\Job;
class DT_Reset_Count_On_Field_Job extends Job {
     /**
     * @var int
     */
    public $post_id;
    public $field_key;
    public $post_type;

    /**
     * Job constructor.
     */
    public function __construct( $post_type, $post_id, $field_key ){
        $this->post_type = $post_type;
        $this->post_id = $post_id;
        $this->field_key = $field_key;
    }

    /**
     * Handle job logic.
     */
    public function handle(){
        $field_settings = DT_Posts::get_post_field_settings( $this->post_type );
        if ( isset( $field_settings[$this->field_key] ) ){
            DT_Posts_Hooks::update_connection_count( $this->post_id, $field_settings[$this->field_key] );
        }
    }
}
new DT_Admin_Endpoints();
