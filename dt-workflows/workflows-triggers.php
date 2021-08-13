<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

add_action( 'dt_post_created', 'dt_post_created_workflows_trigger', 100, 3 );
function dt_post_created_workflows_trigger( $post_type, $post_id, $initial_fields ) {
    // Process incoming post id, based on created trigger
    $post = DT_Posts::get_post( $post_type, $post_id, false, false, true );
    process_trigger( 'created', ! is_wp_error( $post ) ? $post : null );
}

add_action( 'dt_post_updated', 'dt_post_updated_workflows_trigger', 100, 5 );
function dt_post_updated_workflows_trigger( $post_type, $post_id, $initial_fields, $existing_post, $post ) {
    // Process incoming post, based on updated trigger
    process_trigger( 'updated', ! is_wp_error( $post ) ? $post : null );
}

function process_trigger( $trigger_id, $post ) {

    if ( ! empty( $post ) ) {

        // Fetch all enabled workflows for given post_type
        $workflows = Disciple_Tools_Workflows_Execution_Handler::get_workflows( $post['post_type'], true, true );
        if ( ! empty( $workflows ) ) {

            // Fetch post type settings
            $post_type_settings = DT_Posts::get_post_settings( $post['post_type'] );

            // Iterate over returned workflows; evaluating and executing accordingly
            foreach ( $workflows as $workflow ) {
                if ( ! empty( $workflow ) && isset( $workflow->trigger ) && $workflow->trigger === $trigger_id ) {

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
