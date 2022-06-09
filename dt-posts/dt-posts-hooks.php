<?php

class DT_Posts_Hooks {
    public function __construct() {
        add_filter( 'dt_custom_fields_settings_after_combine', [ $this, 'dt_get_custom_fields_translation' ], 10, 1 );
        add_filter( 'options_dt_custom_tiles', [ $this, 'dt_get_custom_tile_translations' ], 10, 1 );
        add_action( "post_connection_removed", [ $this, "post_connection_removed" ], 10, 4 );
        add_action( "post_connection_added", [ $this, "post_connection_added" ], 10, 4 );
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
                if ( isset( $value["type"] ) && ( $value["type"] == "key_select" || $value["type"] == "multi_select" ) ) {
                    $fields[$field]["default"] = self::dt_get_custom_fields_translation( $value["default"] );
                }
                if ( !empty( $value["translations"][$user_locale] ) ) {
                    if ( !isset( $fields["type"] ) ){
                        $fields[$field]["label"] = $value["translations"][$user_locale];
                    } else {
                        $fields[$field]["name"] = $value["translations"][$user_locale];
                    }
                }
                if ( !empty( $value["description_translations"][$user_locale] ) ) {
                    $fields[$field]["description"] = $value["description_translations"][$user_locale];
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
            if ( !empty( $option_value["translations"][$user_locale] ) ) {
                $field_options[$option_key]["label"] = $option_value["translations"][$user_locale];
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
                    if ( isset( $custom_tiles[$post_type][$key]["translations"][$user_locale] ) && !empty( $custom_tiles[$post_type][$key]["translations"][$user_locale] ) ) {
                        $custom_tiles[$post_type][$key]['label'] = $custom_tiles[$post_type][$key]["translations"][$user_locale];
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
        if ( isset( $post_settings[$field_key]["connection_count_field"], $post_settings[$field_key]["p2p_direction"], $post_settings[$field_key]["p2p_key"] ) ){
            if ( $post_type === $post_settings[$field_key]["connection_count_field"]["post_type"] ){
                self::update_connection_count( $post_id, $post_settings[$field_key] );
            } else {
                $target_post_settings = DT_Posts::get_post_field_settings( $post_settings[$field_key]["connection_count_field"]["post_type"] );
                self::update_connection_count( $value, $target_post_settings[$post_settings[$field_key]["connection_count_field"]["connection_field"] ?? $field_key] );
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
        if ( isset( $post_settings[$field_key]["connection_count_field"]["post_type"], $post_settings[$field_key]["p2p_direction"], $post_settings[$field_key]["p2p_key"] ) ){
            if ( $post_type === $post_settings[$field_key]["connection_count_field"]["post_type"] ){
                self::update_connection_count( $post_id, $post_settings[$field_key], "removed" );
            } else {
                $target_post_settings = DT_Posts::get_post_field_settings( $post_settings[$field_key]["connection_count_field"]["post_type"] );
                self::update_connection_count( $value, $target_post_settings[$post_settings[$field_key]["connection_count_field"]["connection_field"] ?? $field_key], "removed" );
            }
        }
    }

    /**
     * Update the counts on a connection field
     * @param $post_id
     * @param $field_setting
     * @param string $action
     */
    public static function update_connection_count( $post_id, $field_setting, string $action = "added" ){
        $args = [
            'connected_type'   => $field_setting["p2p_key"],
            'connected_direction' => $field_setting["p2p_direction"],
            'connected_items'  => $post_id,
            'nopaging'         => true,
            'suppress_filters' => false,
        ];
        $connect_posts = get_posts( $args );
        $connections_count = get_post_meta( $post_id, $field_setting['connection_count_field']["field_key"], true );
        if ( sizeof( $connect_posts ) > intval( $connections_count ) ){
            update_post_meta( $post_id, $field_setting['connection_count_field']["field_key"], sizeof( $connect_posts ) );
        } elseif ( $action === "removed" ){
            update_post_meta( $post_id, $field_setting['connection_count_field']["field_key"], intval( $connections_count ) - 1 );
        }
    }
}
