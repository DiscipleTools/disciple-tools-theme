<?php

class DT_Admin_Endpoints {
    public $namespace = "dt-admin";
    public function __construct(){
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
    }

    public function add_api_routes(){
        register_rest_route(
            $this->namespace, '/scripts/reset_count_field', [
                "methods"  => "POST",
                "callback" => [ $this, 'reset_count_field' ],
                'permission_callback' => function(){
                    return current_user_can( "manage_dt" );
                },
            ]
        );
        register_rest_route(
            $this->namespace, '/scripts/reset_count_field_progress', [
                "methods"  => "GET",
                "callback" => [ $this, 'reset_count_field_progress' ],
                'permission_callback' => function(){
                    return current_user_can( "manage_dt" );
                },
            ]
        );
    }

    public function reset_count_field( WP_REST_Request $request ){
        $params = $request->get_params();
        if ( isset( $params["post_type"], $params["field_key"] ) ){
            $field_settings = DT_Posts::get_post_field_settings( $params["post_type"] );
            $field = $field_settings[$params["field_key"]];
            if ( isset( $field["connection_count_field"]["field_key"] ) ){
                global $wpdb;
                $posts_to_update = $wpdb->get_results( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type = %s", $params["post_type"] ), ARRAY_A );
                foreach ( $posts_to_update as $row ){
                    wp_queue()->push( new DT_Reset_Count_On_Field_Job( $params["post_type"], $row["ID"], $params["field_key"] ), 0, $params["post_type"] . '_' . $params["field_key"] );
                }
                return [
                    "count" => wp_queue_count_jobs( $params["post_type"] . '_' . $params["field_key"] )
                ];
            }
        } else {
            return new WP_Error( __FILE__, "Missing Params post_type or field_key" );
        }
    }

    public function reset_count_field_progress( WP_REST_Request $request ){
        $params = $request->get_params();
        if ( isset( $params["post_type"], $params["field_key"] ) ){
            if ( isset( $params["process"] ) && !empty( $params["process"] ) ){
                wp_queue()->cron()->cron_worker();
            }
            return [
                "count" => (int) wp_queue_count_jobs( $params["post_type"] . '_' . $params["field_key"] ),
            ];
        } else {
            return new WP_Error( __FILE__, "Missing Params post_type or field_key" );
        }
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
