<?php

class DT_Posts_Hooks {
    public function __construct() {
        add_filter( 'dt_custom_fields_settings_after_combine', [ $this, 'dt_get_custom_fields_translation' ], 10, 1 );
        add_filter( 'options_dt_custom_tiles', [ $this, 'dt_get_custom_tile_translations' ], 10, 1 );
    }

    /**
     * Replace field names and field option labels with custom translations
     * if they are available
     *
     * @param $fields
     * @return mixed
     */
    public static function dt_get_custom_fields_translation( $fields ) {
        if (is_admin()) {
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
        if (is_admin()) {
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
                foreach ( $tile_keys as $key => $value) {
                    if ( !empty( $custom_tiles[$post_type][$key][$user_locale] ) ) {
                        $custom_tiles[$post_type][$key]['label'] = $custom_tiles[$post_type][$key][$user_locale];
                    }
                }
            }
            return $custom_tiles;
        }
    }

}
