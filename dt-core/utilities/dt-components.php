<?php

class DT_Components
{
    public static function shared_attributes( $field_key, $fields, $post, $params = [] ) {
        $disabled = 'disabled';
        if ( isset( $post['post_type'] ) && isset( $post['ID'] ) ) {
            $can_update = DT_Posts::can_update( $post['post_type'], $post['ID'] );
        } else {
            $can_update = true;
        }
        $field_disabled = !empty( $fields[$field_key]['readonly'] );
        if ( !$field_disabled && ( $can_update || ( isset( $post['assigned_to']['id'] ) && $post['assigned_to']['id'] == get_current_user_id() ) ) ) {
            $disabled = '';
        }

        $required_tag = ( isset( $fields[$field_key]['required'] ) && $fields[$field_key]['required'] === true ) ? 'required' : '';
        $field_type = $fields[$field_key]['type'] ?? null;
        $is_private = isset( $fields[$field_key]['private'] ) && $fields[$field_key]['private'] === true;
        $display_field_id = ( isset( $params['field_id_prefix'] ) ? $params['field_id_prefix'] : '' ) . $field_key;

        $allowed_types = apply_filters( 'dt_render_field_for_display_allowed_types', [
            'boolean',
            'key_select',
            'multi_select',
            'date',
            'datetime',
            'text',
            'textarea',
            'number',
            'link',
            'connection',
            'location',
            'location_meta',
            'communication_channel',
            'tags',
            'user_select',
            'health_metrics',
        ] );
        if ( !in_array( $field_type, $allowed_types ) ){
            return;
        }
        if ( !dt_field_enabled_for_record_type( $fields[$field_key], $post ) ){
            return;
        }

        $icon = null;
        if ( isset( $fields[$field_key]['icon'] ) && !empty( $fields[$field_key]['icon'] ) ) {
            $icon = 'icon=' . esc_attr( $fields[$field_key]['icon'] );
        }
        if ( isset( $fields[$field_key]['post_type'] ) ) {
            $post_type = 'postType=' . esc_attr( $fields[$field_key]['post_type'] );
        } else if ( isset( $post ) && isset( $post['post_type'] ) ) {
            $post_type = 'postType=' . esc_attr( $post['post_type'] );
        }

        $shared_attributes = '
              id="' . esc_attr( $display_field_id ) . '"
              name="' . esc_attr( $field_key ) . '"
              label="' . esc_attr( $fields[$field_key]['name'] ) . '"
              ' . esc_html( $post_type ?? '' ) . '
              ' . esc_html( $icon ) . '
              ' . esc_html( $required_tag ) . '
              ' . esc_html( $disabled ) . '
              ' . ( $is_private ? 'private' : null ) . '
        ';

        return $shared_attributes;
    }

    public static function render_communication_channel( $field_key, $fields, $post, $params = [] ) {
        $shared_attributes = self::shared_attributes( $field_key, $fields, $post, $params );
        ?>
        <dt-multi-text <?php echo wp_kses_post( $shared_attributes ) ?>
            value="<?php echo esc_attr( isset( $post[$field_key] ) ? json_encode( $post[$field_key] ) : '' ) ?>">
            <?php dt_render_icon_slot( $fields[$field_key] ) ?>
        </dt-multi-text>
        <?php
    }

    public static function render_connection( $field_key, $fields, $post, $params = [] ) {
        $shared_attributes = self::shared_attributes( $field_key, $fields, $post, $params );

        $allow_add = true;
        if ( isset( $params['allow_add'] ) ) {
            $allow_add = $params['allow_add'];
        } else if ( isset( $params['connection']['allow_add'] ) ) {
            $allow_add = $params['connection']['allow_add'];
        }

        $value = array_map(function ( $value ) {
            return [
                'id' => $value['ID'],
                'label' => $value['post_title'],
                'link' => $value['permalink'],
                'status' => $value['status'],
            ];
        }, $post[$field_key] ?? []);
        ?>
        <dt-connection <?php echo wp_kses_post( $shared_attributes ) ?>
            value="<?php echo esc_attr( json_encode( $value ) ) ?>"
            <?php echo $allow_add ? 'allowAdd' : null ?>
        ><?php dt_render_icon_slot( $fields[$field_key] ) ?>
        </dt-connection>
        <?php
    }

    public static function render_date( $field_key, $fields, $post, $params = [] ) {
        $shared_attributes = self::shared_attributes( $field_key, $fields, $post, $params );
        ?>
        <dt-date <?php echo wp_kses_post( $shared_attributes ) ?>
            timestamp="<?php echo esc_html( $post[$field_key]['timestamp'] ?? '' ) ?>">
            <?php dt_render_icon_slot( $fields[$field_key] ) ?>
        </dt-date>
        <?php
    }

    public static function render_datetime( $field_key, $fields, $post, $params = [] ) {
        $shared_attributes = self::shared_attributes( $field_key, $fields, $post, $params );
        ?>
        <dt-datetime <?php echo wp_kses_post( $shared_attributes ) ?>
            timestamp="<?php echo esc_html( $post[$field_key]['timestamp'] ?? '' ) ?>">
            <?php dt_render_icon_slot( $fields[$field_key] ) ?>
        </dt-datetime>
        <?php
    }

    public static function render_location( $field_key, $fields, $post, $params = [] ) {
        $shared_attributes = self::shared_attributes( $field_key, $fields, $post, $params );
        $value = array_map(function ( $value ) {
            return $value;
        }, $post[$field_key] ?? []);
        ?>
        <dt-location <?php echo wp_kses_post( $shared_attributes ) ?>
            value='<?php echo esc_attr( json_encode( $value ) ) ?>'
            placeholder="<?php echo esc_attr( __( 'Search Locations', 'disciple_tools' ) ) ?>"
            filters='[{"id": "focus", "label": "Region of Focus"},
            {"id": "all", "label": "All Locations"}]'>
            <?php dt_render_icon_slot( $fields[$field_key] ) ?>
        </dt-location>
        <?php
    }

    public static function render_number( $field_key, $fields, $post, $params = [] ) {
        $shared_attributes = self::shared_attributes( $field_key, $fields, $post, $params );
        ?>
        <dt-number <?php echo wp_kses_post( $shared_attributes ) ?>
            value="<?php echo esc_html( $post[$field_key] ?? '' ) ?>"
            <?php if ( isset( $fields[$field_key]['min_option'] ) && $fields[$field_key]['min_option'] != null ): ?>
                min="<?php echo esc_html( $fields[$field_key]['min_option'] ) ?>"
            <?php endif; ?>
            <?php if ( isset( $fields[$field_key]['max_option'] ) && $fields[$field_key]['max_option'] != null ): ?>
                max="<?php echo esc_html( $fields[$field_key]['max_option'] ) ?>"
            <?php endif; ?>>
            <?php dt_render_icon_slot( $fields[$field_key] ) ?>
        </dt-number>
        <?php
    }

    public static function render_key_select( $field_key, $fields, $post, $params = [] ) {
        $shared_attributes = self::shared_attributes( $field_key, $fields, $post, $params );

        $options_array = [];

        // if options don't have a 'none' key but can be empty, add an empty option first
        if ( !isset( $fields[$field_key]['default']['none'] ) && empty( $fields[$field_key]['select_cannot_be_empty'] ) ) {
            array_push( $options_array, [
                'id' => '',
                'label' => '',
            ]);
        }

        $options = array_map(function ( $key, $value ) use ( $params ) {
            $option = [
                'id' => (string) $key,
                'label' => $value['label'] ?? $key,
            ];
            if ( !isset( $params['key_select']['disable_color'] ) ) {
                $option['color'] = $value['color'] ?? null;
            }
            return $option;
        }, array_keys( $fields[$field_key]['default'] ), $fields[$field_key]['default']);

        $options_array = array_merge( $options_array, $options );
        ?>
        <dt-single-select <?php echo wp_kses_post( $shared_attributes ) ?>
            value="<?php echo esc_attr( isset( $post[$field_key] ) ? $post[$field_key]['key'] : '' ) ?>"
            options='<?php echo esc_attr( json_encode( $options_array ) ) ?>'>
            <?php dt_render_icon_slot( $fields[$field_key] ) ?>
        </dt-single-select>
        <?php
    }

    public static function render_location_meta( $field_key, $fields, $post, $params = [] ) {
        $shared_attributes = self::shared_attributes( $field_key, $fields, $post, $params );
        ?>
        <dt-location-map <?php echo wp_kses_post( $shared_attributes ) ?>
            value='<?php echo esc_html( isset( $post[$field_key] ) ? json_encode( $post[$field_key] ) : '' ) ?>'
            mapbox-token='<?php echo esc_html( DT_Mapbox_API::get_key() ?? '' ) ?>'
            google-token='<?php echo esc_html( Disciple_Tools_Google_Geocode_API::get_key() ?? '' ) ?>'>
            <?php dt_render_icon_slot( $fields[$field_key] ) ?>
        </dt-location-map>
        <?php
    }

    public static function render_multi_select( $field_key, $fields, $post, $params = [] ) {
        $shared_attributes = self::shared_attributes( $field_key, $fields, $post, $params );
        $options_array = $fields[$field_key]['default'];
        if ( isset( $fields[$field_key]['display'] ) && $fields[$field_key]['display'] === 'typeahead' ) {
            $options_array = array_map(function ( $key, $value ) {
                return [
                    'id' => (string) $key,
                    'label' => $value['label'] ?? $key,
                    'color' => $value['color'] ?? null,
                    'icon' => $value['icon'] ?? null,
                ];
            }, array_keys( $options_array ), $options_array);
            // typeahead
            ?>
            <dt-multi-select <?php echo wp_kses_post( $shared_attributes ) ?>
                options='<?php echo esc_attr( json_encode( $options_array ) ) ?>'
                value='<?php echo esc_attr( isset( $post[$field_key] ) ? json_encode( $post[$field_key] ) : '' ) ?>'>
                <?php dt_render_icon_slot( $fields[$field_key] ) ?>
            </dt-multi-select>
            <?php
        } else if ( isset( $fields[$field_key]['display'] ) && $fields[$field_key]['display'] === 'health-circle' ) {
            // health-circle
            ?>
            <dt-church-health-circle <?php echo wp_kses_post( $shared_attributes ) ?>
                options='<?php echo esc_attr( json_encode( $options_array ) ) ?>'
                value='<?php echo esc_attr( isset( $post[$field_key] ) ? json_encode( $post[$field_key] ) : '' ) ?>'>
            </dt-church-health-circle>
            <?php
        } else {
            // button-group, non-typeahead
            $faith_milestone = array( $fields[$field_key]['default'] );
            $faith_milestone_json = json_encode( $faith_milestone );

            // $is_modal_array = [ 'Baptized' ];
            // $is_modal_json = json_encode( $is_modal_array );?>
            <?php /* isModal='<?php echo esc_attr( $is_modal_json ); ?>' */ ?>
            <dt-multi-select-button-group <?php echo wp_kses_post( $shared_attributes ) ?>
                options='<?php echo esc_attr( json_encode( $options_array ) ) ?>'
                value='<?php echo esc_attr( isset( $post[$field_key] ) ? json_encode( $post[$field_key] ) : '' ) ?>'>
                <?php dt_render_icon_slot( $fields[$field_key] ) ?>
            </dt-multi-select-button-group>
            <?php
        }
    }

    public static function render_tags( $field_key, $fields, $post, $params = [] ) {
        $shared_attributes = self::shared_attributes( $field_key, $fields, $post, $params );

        $value = array_map(function ( $value ) {
            return $value;
        }, $post[$field_key] ?? []);
        ?>
        <dt-tags <?php echo wp_kses_post( $shared_attributes ) ?>
            value='<?php echo esc_attr( json_encode( $value ) ) ?>'
            placeholder="<?php echo esc_html( sprintf( _x( 'Search %s', "Search 'something'", 'disciple_tools' ), $fields[$field_key]['name'] ) ) ?>"
            allowAdd>
            <?php dt_render_icon_slot( $fields[$field_key] ) ?>
        </dt-tags>
        <?php
    }

    public static function render_text( $field_key, $fields, $post, $params = [] ) {
        $shared_attributes = self::shared_attributes( $field_key, $fields, $post, $params );
        ?>
        <dt-text <?php echo wp_kses_post( $shared_attributes ) ?>
            value="<?php echo esc_html( $post[$field_key] ?? '' ) ?>">
            <?php dt_render_icon_slot( $fields[$field_key] ) ?>
        </dt-text>
        <?php
    }

    public static function render_textarea( $field_key, $fields, $post, $params = [] ) {
        $shared_attributes = self::shared_attributes( $field_key, $fields, $post, $params );
        ?>
        <dt-textarea <?php echo wp_kses_post( $shared_attributes ) ?>
            value="<?php echo esc_html( $post[$field_key] ?? '' ) ?>"
        >
            <?php dt_render_icon_slot( $fields[$field_key] ) ?>
        </dt-textarea>
        <?php
    }

    public static function render_toggle( $field_key, $fields, $post, $params = [] ) {
        $shared_attributes = self::shared_attributes( $field_key, $fields, $post, $params );
        ?>
        <dt-toggle <?php echo wp_kses_post( $shared_attributes ) ?>
            <?php echo esc_html( checked( $post[$field_key], '1', false ) ) ?>>
            <?php dt_render_icon_slot( $fields[$field_key] ) ?>
        </dt-toggle>
        <?php
    }
}
