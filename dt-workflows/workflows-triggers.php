<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

add_action( 'dt_post_created', 'dt_post_created_workflows_trigger', 100, 3 );
function dt_post_created_workflows_trigger( $post_type, $post_id, $initial_fields ) {
    // Process incoming post id, based on created trigger
    $post = DT_Posts::get_post( $post_type, $post_id, true, false, true );
    process_trigger( 'created', ! is_wp_error( $post ) ? $post : null, $initial_fields );
}

add_action( 'dt_post_updated', 'dt_post_updated_workflows_trigger', 100, 5 );
function dt_post_updated_workflows_trigger( $post_type, $post_id, $initial_fields, $existing_post, $post ) {
    // Process incoming post, based on updated trigger
    process_trigger( 'updated', ! is_wp_error( $post ) ? $post : null, $initial_fields );
}

function process_trigger( $trigger_id, $post, $initial_fields ) {

    if ( ! empty( $post ) ) {

        // Fetch all enabled workflows for given post_type
        $workflows = Disciple_Tools_Workflows_Execution_Handler::get_workflows( $post['post_type'], true, true );
        if ( ! empty( $workflows ) ) {

            // Fetch post type settings
            $post_type_settings = DT_Posts::get_post_settings( $post['post_type'] );
            $post_type_settings['fields']['comments'] = [
                'id' => 'comments',
                'name' => 'Comments',
                'type' => 'comments',
            ];

            // Iterate over returned workflows; evaluating and executing accordingly
            foreach ( $workflows as $workflow ) {
                if ( ! empty( $workflow ) && isset( $workflow->trigger ) && ( $workflow->trigger === $trigger_id ) && Disciple_Tools_Workflows_Execution_Handler::triggered_by_condition_field( $workflow, $trigger_id, $post, $initial_fields ) ) {

                    // If all conditions evaluate to true...
                    if ( Disciple_Tools_Workflows_Execution_Handler::eval_conditions( $workflow, $post, $post_type_settings ) ) {

                        // ...execute actions!
                        Disciple_Tools_Workflows_Execution_Handler::exec_actions( $workflow, $post, $post_type_settings );
                    }
                }
            }
        }
    }
}

add_filter( 'dt_format_post_activity', 'dt_format_post_activity', 10, 2 );
function dt_format_post_activity( $activity_obj, $activity ): array {
    if ( isset( $activity->user_caps ) && strpos( $activity->user_caps, 'dt_workflow:' ) === 0 ) {
        $workflow_id   = substr( $activity->user_caps, strlen( 'dt_workflow:' ) );
        $workflow_name = null;

        // Fetch all enabled workflows for given post_type and attempt to locate corresponding workflow
        $workflows = Disciple_Tools_Workflows_Execution_Handler::get_workflows( $activity->object_type, true, true );
        if ( ! empty( $workflows ) ) {
            foreach ( $workflows as $workflow ) {
                if ( ! empty( $workflow ) && isset( $workflow->id, $workflow->name ) && ( $workflow->id === $workflow_id ) ) {
                    $workflow_name = $workflow->name;
                }
            }
        }

        $activity_obj['name'] = isset( $workflow_name ) ? wp_specialchars_decode( $workflow_name ) : __( 'D.T Workflow', 'disciple_tools' );
    }

    return $activity_obj;
}
