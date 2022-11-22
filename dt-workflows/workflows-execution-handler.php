<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * Class Disciple_Tools_Workflows_Execution_Handler
 */
class Disciple_Tools_Workflows_Execution_Handler {

    public function __construct() {
    }

    public static function get_workflows( $post_type, $enabled_only, $include_defaults ): array {
        $option                     = get_option( 'dt_workflows_post_types' );
        $option_post_type_workflows = ( ! empty( $option ) ) ? json_decode( $option ) : (object) [];

        $workflows = [];
        if ( isset( $option_post_type_workflows->{$post_type} ) && isset( $option_post_type_workflows->{$post_type}->workflows ) ) {

            // Iterate over identified workflows, selecting accordingly, based on flag!
            foreach ( $option_post_type_workflows->{$post_type}->workflows as $id => $workflow ) {
                if ( $enabled_only ) {
                    if ( $workflow->enabled ) {
                        $workflows[] = $workflow;
                    }
                } else {
                    $workflows[] = $workflow;
                }
            }
        }

        // If requested, also include any available default workflows
        if ( $include_defaults ) {

            // Fetch any available default workflow configs
            $option                    = get_option( 'dt_workflows_defaults' );
            $option_default_workflows  = ( ! empty( $option ) ) ? json_decode( $option ) : (object) [];
            $default_workflows_configs = ( isset( $option_default_workflows->{$post_type} ) && isset( $option_default_workflows->{$post_type}->workflows ) ) ? $option_default_workflows->{$post_type}->workflows : (object) [];

            // Iterate over all filtered default workflows
            foreach ( apply_filters( 'dt_workflows', [], $post_type ) ?? [] as $workflow ) {

                // Ensure default workflow states are updated accordingly, based on current configs!
                if ( ! empty( $workflow ) ) {
                    $workflow = self::update_default_workflow_state( $workflow, $default_workflows_configs );

                    // Determine if workflow is to be returned based on enabled_only flag!
                    if ( $enabled_only ) {
                        if ( $workflow->enabled ) {
                            $workflows[] = $workflow;
                        }
                    } else {
                        $workflows[] = $workflow;
                    }
                }
            }
        }

        return $workflows;
    }

    private static function update_default_workflow_state( $workflow, $default_workflows_configs ) {
        if ( isset( $default_workflows_configs->{$workflow->id} ) ) {
            $workflow->enabled = $default_workflows_configs->{$workflow->id}->enabled;
        }

        return $workflow;
    }

    public static function eval_conditions( $workflow, $post, $post_type_settings ): bool {

        if ( ! empty( $workflow ) && isset( $workflow->conditions ) ) {

            // Iterate through each condition, ensuring it evaluates as true!
            foreach ( $workflow->conditions as $condition ) {
                if ( ! self::process_condition( $condition->field_id, $condition->id, $condition->value, $post, $post_type_settings ) ) {
                    return false;
                }
            }
        }

        return true;
    }

    public static function triggered_by_condition_field( $workflow, $initial_fields ): bool {

        // Extract initial field keys to be checked
        $initial_field_keys = ! empty( $initial_fields ) ? array_keys( $initial_fields ) : [];

        // Determine if trigger owner also doubles up as condition field
        $triggered_by_condition = false;
        foreach ( $workflow->conditions as $condition ) {
            if ( in_array( $condition->field_id, $initial_field_keys ) ) {
                $triggered_by_condition = true;
            }
        }

        return $triggered_by_condition;
    }

    private static function process_condition( $field_id, $condition, $value, $post, $post_type_settings ): bool {

        if ( ! empty( $field_id ) && ! empty( $condition ) && isset( $post_type_settings['fields'][ $field_id ]['type'] ) ) {

            $field_type = $post_type_settings['fields'][ $field_id ]['type'];

            if ( isset( $post[ $field_id ] ) ) {
                $field = $post[ $field_id ];

                switch ( $condition ) {
                    case 'equals':
                        return self::condition_equals( $field_type, $field, $value );
                    case 'not_equals':
                        return self::condition_not_equals( $field_type, $field, $value );
                    case 'greater':
                        return self::condition_greater( $field_type, $field, $value );
                    case 'less':
                        return self::condition_less( $field_type, $field, $value );
                    case 'greater_equals':
                        return self::condition_greater_equals( $field_type, $field, $value );
                    case 'less_equals':
                        return self::condition_less_equals( $field_type, $field, $value );
                    case 'contains':
                        return self::condition_contains( $field_type, $field, $value );
                    case 'not_contain':
                        return self::condition_not_contains( $field_type, $field, $value );
                    case 'is_set':
                        return self::condition_is_set( $field_type, $field );
                    case 'not_set':
                        return self::condition_not_set( $field_type, $field );
                }
            } else {

                // Process not_set states...
                switch ( $condition ) {
                    case 'not_set':
                        return self::condition_not_set( $field_type, null );
                }
            }
        }

        return false;
    }

    private static function condition_equals( $field_type, $field, $value ): bool {
        switch ( $field_type ) {
            case 'text':
            case 'number':
                return strval( $field ) === strval( $value );
            case 'boolean':
                return boolval( $field ) === boolval( $value );
            case 'date':
                // $value to be of epoch timestamp int type
                return isset( $field['timestamp'] ) && intval( $field['timestamp'] ) === intval( $value );
        }

        return false;
    }

    private static function condition_not_equals( $field_type, $field, $value ): bool {
        switch ( $field_type ) {
            case 'text':
            case 'number':
                return strval( $field ) !== strval( $value );
            case 'boolean':
                return boolval( $field ) !== boolval( $value );
            case 'date':
                return isset( $field['timestamp'] ) && intval( $field['timestamp'] ) !== intval( $value );
        }

        return false;
    }

    private static function condition_greater( $field_type, $field, $value ): bool {
        switch ( $field_type ) {
            case 'number':
                return floatval( $field ) > floatval( $value );
            case 'date':
                return isset( $field['timestamp'] ) && intval( $field['timestamp'] ) > intval( $value );
        }

        return false;
    }

    private static function condition_less( $field_type, $field, $value ): bool {
        switch ( $field_type ) {
            case 'number':
                return floatval( $field ) < floatval( $value );
            case 'date':
                return isset( $field['timestamp'] ) && intval( $field['timestamp'] ) < intval( $value );
        }

        return false;
    }

    private static function condition_greater_equals( $field_type, $field, $value ): bool {
        switch ( $field_type ) {
            case 'number':
                return floatval( $field ) >= floatval( $value );
            case 'date':
                return isset( $field['timestamp'] ) && intval( $field['timestamp'] ) >= intval( $value );
        }

        return false;
    }

    private static function condition_less_equals( $field_type, $field, $value ): bool {
        switch ( $field_type ) {
            case 'number':
                return floatval( $field ) <= floatval( $value );
            case 'date':
                return isset( $field['timestamp'] ) && intval( $field['timestamp'] ) <= intval( $value );
        }

        return false;
    }

    private static function condition_contains( $field_type, $field, $value ): bool {
        switch ( $field_type ) {
            case 'text':
                return strpos( $field, strval( $value ) ) !== false;
            case 'tags':
            case 'multi_select':
                return is_array( $field ) && in_array( strval( $value ), $field );
            case 'key_select':
                return isset( $field['key'] ) && strval( $field['key'] ) === strval( $value );
            case 'communication_channel':
                $found = false;
                if ( is_array( $field ) ) {
                    foreach ( $field as $item ) {
                        if ( ! $found && isset( $item['value'] ) && strval( $item['value'] ) === strval( $value ) ) {
                            $found = true;
                        }
                    }
                }

                return $found;
            case 'location':
                $found = false;
                if ( is_array( $field ) ) {
                    foreach ( $field as $item ) {
                        if ( ! $found && isset( $item['id'] ) && strval( $item['id'] ) === strval( $value ) ) {
                            $found = true;
                        }
                    }
                }

                return $found;
            case 'location_meta':
                $found = false;
                if ( is_array( $field ) ) {
                    foreach ( $field as $item ) {
                        if ( ! $found && isset( $item['label'] ) && strval( $item['label'] ) === strval( $value ) ) {
                            $found = true;
                        }
                    }
                }

                return $found;
            case 'connection':
                $found = false;
                if ( is_array( $field ) ) {
                    foreach ( $field as $item ) {
                        if ( ! $found && isset( $item['ID'] ) && strval( $item['ID'] ) === strval( $value ) ) {
                            $found = true;
                        }
                    }
                }

                return $found;
            case 'user_select':
                return isset( $field['assigned-to'] ) && strval( $field['assigned-to'] ) === strval( $value );
            case 'array':
            case 'task':
            case 'post_user_meta':
            case 'datetime_series':
            case 'hash':
                break;
        }

        return false;
    }

    private static function condition_not_contains( $field_type, $field, $value ): bool {
        switch ( $field_type ) {
            case 'text':
                return strpos( $field, strval( $value ) ) === false;
            case 'tags':
            case 'multi_select':
                return is_array( $field ) && ! in_array( strval( $value ), $field );
            case 'key_select':
                return isset( $field['key'] ) && strval( $field['key'] ) !== strval( $value );
            case 'communication_channel':
                $found = false;
                if ( is_array( $field ) ) {
                    foreach ( $field as $item ) {
                        if ( ! $found && isset( $item['value'] ) && strval( $item['value'] ) === strval( $value ) ) {
                            $found = true;
                        }
                    }
                }

                return ! $found;
            case 'location':
                $found = false;
                if ( is_array( $field ) ) {
                    foreach ( $field as $item ) {
                        if ( ! $found && isset( $item['id'] ) && strval( $item['id'] ) === strval( $value ) ) {
                            $found = true;
                        }
                    }
                }

                return ! $found;
            case 'location_meta':
                $found = false;
                if ( is_array( $field ) ) {
                    foreach ( $field as $item ) {
                        if ( ! $found && isset( $item['label'] ) && strval( $item['label'] ) === strval( $value ) ) {
                            $found = true;
                        }
                    }
                }

                return ! $found;
            case 'connection':
                $found = false;
                if ( is_array( $field ) ) {
                    foreach ( $field as $item ) {
                        if ( ! $found && isset( $item['ID'] ) && strval( $item['ID'] ) === strval( $value ) ) {
                            $found = true;
                        }
                    }
                }

                return ! $found;
            case 'user_select':
                return isset( $field['assigned-to'] ) && strval( $field['assigned-to'] ) !== strval( $value );
            case 'array':
            case 'task':
            case 'post_user_meta':
            case 'datetime_series':
            case 'hash':
                break;
        }

        return false;
    }

    private static function condition_is_set( $field_type, $field ): bool {
        switch ( $field_type ) {
            case 'text':
            case 'number':
            case 'boolean':
            case 'tags':
            case 'multi_select':
            case 'communication_channel':
            case 'location':
            case 'location_meta':
            case 'connection':
                return isset( $field ) && ! empty( $field );
            case 'date':
                return isset( $field['timestamp'] ) && ! empty( $field['timestamp'] );
            case 'key_select':
                return isset( $field['key'] ) && ! empty( $field['key'] );
            case 'user_select':
                return isset( $field['assigned-to'] ) && ! empty( $field['assigned-to'] );
            case 'array':
            case 'task':
            case 'post_user_meta':
            case 'datetime_series':
            case 'hash':
                break;
        }

        return false;
    }

    private static function condition_not_set( $field_type, $field ): bool {
        switch ( $field_type ) {
            case 'text':
            case 'number':
            case 'boolean':
            case 'tags':
            case 'multi_select':
            case 'communication_channel':
            case 'location':
            case 'location_meta':
            case 'connection':
                return empty( $field );
            case 'date':
                return empty( $field['timestamp'] );
            case 'key_select':
                return empty( $field['key'] );
            case 'user_select':
                return empty( $field['assigned-to'] );
            case 'array':
            case 'task':
            case 'post_user_meta':
            case 'datetime_series':
            case 'hash':
                break;
        }

        return false;
    }

    public static function exec_actions( $workflow, $post, $post_type_settings ) {
        // Ensure to check action updates have not already been executed, as part of infinite post update loops!
        if ( ! empty( $workflow ) && isset( $workflow->actions ) && ! self::already_executed_actions( $workflow->actions, $post, $post_type_settings ) ) {

            // Ensure updates occur under corresponding workflow alias
            $previous_user_id = get_current_user_id();

            wp_set_current_user( 0 );
            $current_user = wp_get_current_user();
            $current_user->add_cap( 'dt_workflow:' . $workflow->id );
            $current_user->add_cap( 'access_' . $post['post_type'] );

            // Iterate through each condition, ensuring it evaluates as true!
            foreach ( $workflow->actions as $action ) {
                self::process_action( $action->field_id, $action->id, $action->value, $post, $post_type_settings );
            }

            // Switch back to previous user
            wp_set_current_user( $previous_user_id );
        }
    }

    private static function already_executed_actions( $actions, $post, $post_type_settings ): bool {

        $already_executed = [];
        foreach ( $actions as $action ) {

            // Determine current field state
            $current_state = false;
            if ( isset( $post[ $action->field_id ] ) && isset( $post_type_settings['fields'][ $action->field_id ]['type'] ) ) {
                $field      = $post[ $action->field_id ];
                $field_type = $post_type_settings['fields'][ $action->field_id ]['type'];

                switch ( $field_type ) {
                    case 'text':
                    case 'number':
                    case 'boolean':
                    case 'date':
                        $current_state = self::condition_equals( $field_type, $field, $action->value );
                        break;
                    case 'tags':
                    case 'multi_select':
                    case 'key_select':
                    case 'communication_channel':
                    case 'location':
                    case 'location_meta':
                    case 'connection':
                    case 'user_select':
                        $current_state = self::condition_contains( $field_type, $field, $action->value );
                        break;
                }
            }

            // Determine if field has already been worked on based on the current action to be carried out!
            switch ( $action->id ) {
                case 'update':
                case 'append':
                case 'connect':
                    $already_executed[] = $current_state;
                    break;
                case 'remove':
                    $already_executed[] = ! $current_state;
                    break;
            }
        }

        // Still continue with execution if any fields are still in need of updating.
        return ! in_array( false, $already_executed );
    }

    private static function process_action( $field_id, $action, $value, $post, $post_type_settings ) {
        if ( ! empty( $field_id ) && ! empty( $action ) && ! empty( $value ) ) {

            if ( isset( $post_type_settings['fields'][ $field_id ]['type'] ) ) {
                $field_type = $post_type_settings['fields'][ $field_id ]['type'];

                $updated_fields = [];
                switch ( $action ) {
                    case 'update':
                        $updated_fields = self::action_update( $field_type, $field_id, $value );
                        break;
                    case 'append':
                        $updated_fields = self::action_append( $field_type, $field_id, $value );
                        break;
                    case 'connect':
                        $updated_fields = self::action_connect( $field_type, $field_id, $value );
                        break;
                    case 'remove':
                        $updated_fields = self::action_remove( $field_type, $field_id, $value, $post );
                        break;
                    case 'unset':
                        $updated_fields = self::action_unset( $field_type, $field_id, $value );
                        break;
                    case 'custom':
                        do_action( strval( $value ), $post, $field_id, $value );
                        break;
                }

                // Assuming we have updated fields, proceed with post update!
                if ( ! empty( $updated_fields ) ) {
                    DT_Posts::update_post( $post['post_type'], $post['ID'], $updated_fields, false, false );
                }
            }
        }
    }

    private static function action_update( $field_type, $field_id, $value ): array {
        $updated = [];
        switch ( $field_type ) {
            case 'text':
            case 'number':
            case 'boolean':
            case 'key_select':
            case 'user_select':
                $updated[ $field_id ] = $value;
                break;
            case 'date': // $value to be of epoch timestamp int type
                $updated[ $field_id ] = $value === 'current' ? time() : $value;
                break;
        }

        return $updated;
    }

    private static function action_append( $field_type, $field_id, $value ): array {
        $updated = [];
        switch ( $field_type ) {
            case 'tags':
            case 'multi_select':
            case 'communication_channel':
            case 'location': // $value to be location id
                $updated[ $field_id ]['values']   = [];
                $updated[ $field_id ]['values'][] = [ 'value' => $value ];
                break;
            case 'location_meta':
                $location = null;

                // Lookup location based on configured mapping api.
                if ( class_exists( 'Disciple_Tools_Google_Geocode_API' ) && Disciple_Tools_Google_Geocode_API::get_key() ) {
                    $query = Disciple_Tools_Google_Geocode_API::query_google_api( $value, 'coordinates_only' );
                    if ( ! empty( $query ) ) {
                        $location = [
                            'label' => $value,
                            'lng'   => $query['lng'],
                            'lat'   => $query['lat'],
                            'level' => Disciple_Tools_Google_Geocode_API::parse_raw_result( $query['raw'], 'types' )
                        ];
                    }
                } elseif ( class_exists( 'DT_Mapbox_API' ) && DT_Mapbox_API::get_key() ) {
                    $lookup = DT_Mapbox_API::lookup( $value );
                    if ( ! empty( $lookup ) && is_array( $lookup['features'] ) && isset( $lookup['features'][0]['center'] ) ) {
                        $center   = $lookup['features'][0]['center']; // Select top hit!
                        $location = [
                            'label' => $value,
                            'lng'   => $center[0],
                            'lat'   => $center[1],
                            'level' => ! empty( $lookup['features'][0]['place_type'] ) ? $lookup['features'][0]['place_type'][0] : null
                        ];
                    }
                }

                // If valid lookup, then continue with update.
                if ( ! empty( $location ) ) {
                    $updated[ $field_id ]['values']   = [];
                    $updated[ $field_id ]['values'][] = [
                        'label' => $location['label'],
                        'lng'   => $location['lng'],
                        'lat'   => $location['lat'],
                        'level' => $location['level'] ?? null
                    ];
                }
                break;
        }

        return $updated;
    }

    private static function action_connect( $field_type, $field_id, $value ): array {
        $updated = [];
        switch ( $field_type ) {
            case 'connection': // $value to be connection ID
                $updated[ $field_id ]['values']   = [];
                $updated[ $field_id ]['values'][] = [ 'value' => $value ];
                break;
        }

        return $updated;
    }

    private static function action_remove( $field_type, $field_id, $value, $post ): array {
        $updated = [];
        switch ( $field_type ) {
            case 'tags':
            case 'multi_select':
            case 'connection':
                $updated[ $field_id ]['values']   = [];
                $updated[ $field_id ]['values'][] = [
                    'value'  => $value,
                    'delete' => true
                ];
                break;
            case 'communication_channel':
                $updated_channels = [];

                // Attempt to locate corresponding value key.
                if ( isset( $post[ $field_id ] ) ) {
                    foreach ( $post[ $field_id ] ?? [] as $channel ) {
                        if ( isset( $channel['value'], $channel['key'] ) ) {
                            if ( $channel['value'] == $value ) {
                                $updated_channels[] = [
                                    'key'    => $channel['key'],
                                    'delete' => true
                                ];
                            }
                        }
                    }
                }

                // Package updates accordingly.
                if ( ! empty( $updated_channels ) ) {
                    $updated[ $field_id ] = $updated_channels;
                }
                break;
            case 'location':
                $updated_locations = [];

                // Attempt to locate corresponding value id.
                if ( isset( $post[ $field_id ] ) ) {
                    foreach ( $post[ $field_id ] ?? [] as $location ) {
                        if ( isset( $location['label'], $location['id'], $location['matched_search'] ) ) {
                            if ( in_array( $value, [ $location['label'], $location['matched_search'] ] ) ) {
                                $updated_locations[] = [
                                    'value'  => $location['id'],
                                    'delete' => true
                                ];
                            }
                        }
                    }
                }

                // Package updates accordingly.
                if ( ! empty( $updated_locations ) ) {
                    $updated[ $field_id ]['values'] = $updated_locations;
                }
                break;
            case 'location_meta':
                $updated_location_metas = [];

                // Attempt to locate corresponding label id.
                if ( isset( $post[ $field_id ] ) ) {
                    foreach ( $post[ $field_id ] ?? [] as $meta ) {
                        if ( isset( $meta['label'], $meta['grid_meta_id'] ) ) {
                            if ( $meta['label'] == $value ) {
                                $updated_location_metas[] = [
                                    'grid_meta_id' => $meta['grid_meta_id'],
                                    'delete'       => true
                                ];
                            }
                        }
                    }
                }

                // Package updates accordingly.
                if ( ! empty( $updated_location_metas ) ) {
                    $updated[ $field_id ]['values'] = $updated_location_metas;
                }
                break;

        }

        return $updated;
    }

    private static function action_unset( $field_type, $field_id, $value ): array {
        $updated = [];
        switch ( $field_type ) {
            case 'date':
                $updated[ $field_id ] = null;
                break;
        }

        return $updated;
    }
}
