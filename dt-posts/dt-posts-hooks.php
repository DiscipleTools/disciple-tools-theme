<?php

class DT_Posts_Hooks {
    public function __construct() {
        add_filter( 'dt_custom_fields_settings_after_combine', [ $this, 'dt_get_custom_fields_translation' ], 10, 1 );
        add_filter( 'options_dt_custom_tiles', [ $this, 'dt_get_custom_tile_translations' ], 10, 1 );
        add_action( 'post_connection_removed', [ $this, 'post_connection_removed' ], 10, 4 );
        add_action( 'post_connection_added', [ $this, 'post_connection_added' ], 10, 4 );
        add_filter( 'dt_create_check_for_duplicate_posts', [ $this, 'dt_create_check_for_duplicate_posts' ], 10, 5 );
        add_filter( 'dt_ignore_duplicated_post_fields', [ $this, 'dt_ignore_duplicated_post_fields' ], 10, 4 );
    }

    /**
     * Replace field names and field option labels with custom translations
     * if they are available
     *
     * @param $fields
     * @return mixed
     */
    public static function dt_get_custom_fields_translation( $fields ) {
        if ( is_admin() ) {
            return $fields;
        } else {
            $user_locale = get_user_locale();
            foreach ( $fields as $field => $value ) {
                if ( isset( $value['type'] ) && ( $value['type'] == 'key_select' || $value['type'] == 'multi_select' ) ) {
                    $fields[$field]['default'] = self::dt_get_custom_fields_translation( $value['default'] );
                }
                if ( !empty( $value['translations'][$user_locale] ) ) {
                    if ( !isset( $fields['type'] ) ){
                        $fields[$field]['label'] = $value['translations'][$user_locale];
                    } else {
                        $fields[$field]['name'] = $value['translations'][$user_locale];
                    }
                }
                if ( !empty( $value['description_translations'][$user_locale] ) ) {
                    $fields[$field]['description'] = $value['description_translations'][$user_locale];
                }
            }
            return $fields;
        }
    }

    /**
     * Replace field option labels with custom translations
     * if they are available
     *
     * @param $field_options
     * @return mixed
     */
    public static function dt_get_field_options_translation( $field_options ) {
        if ( is_admin() ) {
            return $field_options;
        }
        $user_locale = get_user_locale();
        foreach ( $field_options as $option_key => $option_value ) {
            if ( !empty( $option_value['translations'][$user_locale] ) ) {
                $field_options[$option_key]['label'] = $option_value['translations'][$user_locale];
            }
        }
        return $field_options;
    }

    public static function dt_get_custom_tile_translations( $custom_tiles ) {
        if ( is_admin() ) {
            return $custom_tiles;
        } else {
            $user_locale = get_user_locale();
            foreach ( $custom_tiles as $post_type => $tile_keys ) {
                foreach ( $tile_keys as $key => $value ) {
                    if ( isset( $custom_tiles[$post_type][$key]['translations'][$user_locale] ) && !empty( $custom_tiles[$post_type][$key]['translations'][$user_locale] ) ) {
                        $custom_tiles[$post_type][$key]['label'] = $custom_tiles[$post_type][$key]['translations'][$user_locale];
                    }
                }
            }
            return $custom_tiles;
        }
    }


    //action when a post connection is added during create or update
    public function post_connection_added( $post_type, $post_id, $field_key, $value ){
        $post_settings = DT_Posts::get_post_field_settings( $post_type );

        /**
         * Update counts for a connection field when a connection is added
         * requires connection_count_field to be set on the connection fields
         */
        if ( isset( $post_settings[$field_key]['connection_count_field'], $post_settings[$field_key]['p2p_direction'], $post_settings[$field_key]['p2p_key'] ) ){
            if ( $post_type === $post_settings[$field_key]['connection_count_field']['post_type'] ){
                self::update_connection_count( $post_id, $post_settings[$field_key] );
            } else {
                $target_post_settings = DT_Posts::get_post_field_settings( $post_settings[$field_key]['connection_count_field']['post_type'] );
                self::update_connection_count( $value, $target_post_settings[$post_settings[$field_key]['connection_count_field']['connection_field'] ?? $field_key] );
            }
        }
    }

    //action when a post connection is removed during create or update
    public function post_connection_removed( $post_type, $post_id, $field_key, $value ){
        $post_settings = DT_Posts::get_post_field_settings( $post_type );
        /**
         * Update counts for a connection field when a connection is removed
         * requires connection_count_field to be set on the connection fields
         */
        if ( isset( $post_settings[$field_key]['connection_count_field']['post_type'], $post_settings[$field_key]['p2p_direction'], $post_settings[$field_key]['p2p_key'] ) ){
            if ( $post_type === $post_settings[$field_key]['connection_count_field']['post_type'] ){
                self::update_connection_count( $post_id, $post_settings[$field_key], 'removed' );
            } else {
                $target_post_settings = DT_Posts::get_post_field_settings( $post_settings[$field_key]['connection_count_field']['post_type'] );
                self::update_connection_count( $value, $target_post_settings[$post_settings[$field_key]['connection_count_field']['connection_field'] ?? $field_key], 'removed' );
            }
        }
    }

    /**
     * Update the counts on a connection field
     * @param $post_id
     * @param $field_setting
     * @param string $action
     */
    public static function update_connection_count( $post_id, $field_setting, string $action = 'added' ){
        $args = [
            'connected_type'   => $field_setting['p2p_key'],
            'connected_direction' => $field_setting['p2p_direction'],
            'connected_items'  => $post_id,
            'nopaging'         => true,
            'suppress_filters' => false,
        ];
        $connect_posts = get_posts( $args );
        $connections_count = get_post_meta( $post_id, $field_setting['connection_count_field']['field_key'], true );
        if ( sizeof( $connect_posts ) > intval( $connections_count ) ){
            update_post_meta( $post_id, $field_setting['connection_count_field']['field_key'], sizeof( $connect_posts ) );
        } elseif ( $action === 'removed' ){
            update_post_meta( $post_id, $field_setting['connection_count_field']['field_key'], intval( $connections_count ) - 1 );
        }
    }

    /**
     * Search for duplicate posts by submitted field values.
     *
     * @param $duplicates
     * @param $post_type
     * @param $fields
     * @param $search_fields
     * @param $check_permissions
     */
    public static function dt_create_check_for_duplicate_posts( $duplicates, $post_type, $fields, $search_fields, $check_permissions ) {
        if ( ! empty( $fields ) && ! empty( $search_fields ) ) {

            // Extract search field values.
            $search_values       = [];
            $post_settings       = DT_Posts::get_post_settings( $post_type );
            $post_field_settings = $post_settings['fields'];
            foreach ( $search_fields as $search_field ) {
                if ( isset( $post_field_settings[ $search_field ], $fields[ $search_field ] ) ) {
                    $field_type = $post_field_settings[ $search_field ]['type'];
                    switch ( $field_type ) {
                        case 'text':
                        case 'textarea':
                            $search_values[ $search_field ] = [ '^' . $fields[ $search_field ] ];
                            break;
                        case 'boolean':
                        case 'key_select':
                        case 'date':
                        case 'user_select':
                            $search_values[ $search_field ] = [ $fields[ $search_field ] ];
                            break;
                        case 'multi_select':
                        case 'links':
                        case 'tags':
                        case 'location':
                        case 'location_meta':
                        case 'connection':
                        case 'communication_channel':
                            $values       = [];
                            $fields_array = ( $field_type == 'communication_channel' ) ? $fields[ $search_field ] : $fields[ $search_field ]['values'];
                            if ( isset( $fields_array['values'] ) ){
                                $fields_array = $fields_array['values'];
                            }
                            foreach ( $fields_array ?? [] as $value ) {
                                if ( isset( $value['value'] ) ) {
                                    $values[] = '^' . $value['value'];
                                }
                            }
                            if ( ! empty( $values ) ) {
                                $search_values[ $search_field ] = $values;
                            }
                            break;
                        case 'number':
                            //not supported
                            break;
                    }
                }
            }

            // Query system for duplicates, based on identified search values and status key.
            if ( ! empty( $search_values ) ) {
                $status_key = isset( $post_settings['status_field']['status_key'] ) ? $post_settings['status_field']['status_key'] : null;
                $fields = [
                    $search_values,
                ];
                if ( $post_type === 'contacts' ){
                    $fields[] = [ 'type' => [ '-personal', '-placeholder' ] ];
                }
                $search_result = DT_Posts::search_viewable_post( $post_type, [
                    'sort'             => !empty( $status_key ) ? $status_key : '-post_date',
                    'fields'           => $fields,
                    'fields_to_search' => array_keys( $search_values )
                ], false );

                // Package identified duplicates.
                if ( ! empty( $search_result ) && ! is_wp_error( $search_result ) && isset( $search_result['posts'] ) ) {
                    foreach ( $search_result['posts'] ?? [] as $post ) {
                        $duplicates[] = $post->ID;
                    }
                }
            }
        }

        return $duplicates;
    }

    /**
     * Remove duplicated field values from specified post record.
     *
     * @param $updated_fields
     * @param $fields
     * @param $post_type
     * @param $post_id
     */
    public static function dt_ignore_duplicated_post_fields( $updated_fields, $fields, $post_type, $post_id ) {
        $existing_fields = DT_Posts::get_post( $post_type, $post_id, true, false );
        if ( !empty( $existing_fields ) && !is_wp_error( $existing_fields ) ) {
            $field_settings = DT_Posts::get_post_field_settings( $post_type );
            foreach ( $fields as $field_key => $field_value ) {
                if ( isset( $existing_fields[ $field_key ], $field_settings[ $field_key ]['type'] ) ) {
                    $field_type = $field_settings[ $field_key ]['type'];
                    switch ( $field_type ) {
                        case 'text':
                        case 'textarea':
                        case 'boolean':
                            if ( $field_value != $existing_fields[ $field_key ] ) {
                                $updated_fields[ $field_key ] = $field_value;
                            }
                            break;
                        case 'date':
                        case 'key_select':
                            $key = 'key';
                            if ( $field_type == 'date' ) {
                                $key = 'formatted';
                            }
                            if ( !( isset( $existing_fields[ $field_key ][ $key ] ) && $existing_fields[ $field_key ][ $key ] == $field_value ) ) {
                                $updated_fields[ $field_key ] = $field_value;
                            }
                            break;
                        case 'multi_select':
                            $values = [];
                            foreach ( $field_value['values'] ?? [] as $value ) {
                                if ( !( isset( $value['value'] ) && in_array( $value['value'], $existing_fields[ $field_key ] ) ) ) {
                                    $values[] = $value;
                                }
                            }
                            if ( !empty( $values ) ) {
                                $updated_fields[ $field_key ] = [
                                    'values' => $values,
                                ];
                            }
                            break;
                        case 'location':
                        case 'location_meta':
                            $values = [];
                            foreach ( $field_value['values'] ?? [] as $value ) {
                                $key = 'label';
                                $found = array_filter( $existing_fields[ $field_key ], function ( $option ) use ( $value, $key ) {
                                    $hit = isset( $option[$key] ) && $option[$key] == $value['value'];

                                    if ( !$hit && isset( $option['matched_search'] ) && $option['matched_search'] == $value['value'] ) {
                                        $hit = true;
                                    }

                                    return $hit;
                                } );
                                if ( empty( $found ) || count( $found ) == 0 ) {
                                    $values[] = $value;
                                }
                            }
                            if ( !empty( $values ) ) {
                                $updated_fields[$field_key] = [
                                    'values' => $values,
                                ];
                            }
                            break;
                        case 'communication_channel':
                            $values = [];
                            $existing_field_values = $existing_fields[ $field_key ];

                            foreach ( $field_value ?? [] as $value ) {
                                $key = 'value';
                                $found = array_values( array_filter( $value, function ( $option ) use ( $existing_field_values, $key ) {

                                    $hit = false;
                                    foreach ( $existing_field_values as $existing_field_value ) {
                                        if ( !$hit && isset( $option[$key] ) && $option[$key] != $existing_field_value[$key] ) {
                                            $hit = true;
                                        }
                                    }
                                    return $hit;
                                } ) );
                                if ( !empty( $found ) ) {
                                    foreach ( $found as $found_value ) {
                                        $values[] = [
                                            'value' => $found_value[$key]
                                        ];
                                    }
                                }
                            }
                            if ( !empty( $values ) ) {
                                $updated_fields[ $field_key ] = $values;
                            }
                            break;
                        default:
                            $updated_fields[ $field_key ] = $field_value;
                            break;
                    }
                } else {
                    $updated_fields[ $field_key ] = $field_value;
                }
            }
        }

        return $updated_fields;
    }
}
