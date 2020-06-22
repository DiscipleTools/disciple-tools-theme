<?php

class DT_Posts_Hooks {
    public function __construct() {
        add_filter( 'dt_custom_fields_settings_after_combine', [ $this, 'dt_get_custom_fields_translation' ], 10, 1 );
        add_filter( 'dt_custom_channels', [ $this, 'dt_get_custom_channels_translation' ] );
    }

    /**
     * Replace field names and field option labels with custom translations
     * if they are available
     *
     * @param $fields
     * @return mixed
     */
    public function dt_get_custom_fields_translation( $fields ) {
        if (is_admin()) {
            return $fields;
        } else {
            $user_locale = get_user_locale();
            foreach ( $fields as $field => $value ) {
                if ( $value["type"] == "key_select" || $value["type"] == "multi_select" ) {
                    foreach ( $value["default"] as $option_key => $option_value ) {
                        if ( !empty( $option_value["translations"][$user_locale] ) ) {
                            $fields[$field]["default"][$option_key]["label"] = $option_value["translations"][$user_locale];
                        }
                    }
                }
                if ( !empty( $value["translations"][$user_locale] ) ) {
                    $fields[$field]["name"] = $value["translations"][$user_locale];
                }
            }
            return $fields;
        }
    }

    /**
     * Replace channel labels with custom translations
     * if they are available
     *
     * @param $channel_list
     * @return mixed
     */
    public static function dt_get_custom_channels_translation( $channel_list ) {
        if (is_admin()) {
            return $channel_list;
        }
        $user_locale = get_user_locale();
        foreach ( $channel_list as $channel_key => $channel_value ) {
            if ( !empty( $channel_value["translations"][$user_locale] ) ) {
                $channel_list[$channel_key]["label"] = $channel_value["translations"][$user_locale];
            }
        }
        return $channel_list;
    }

}