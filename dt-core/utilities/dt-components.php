<?php

class DT_Components
{
    public static function shared_attributes( $field_key, $fields, $post ) {
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
        $display_field_id = $field_key;

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
            'user_select'
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
        } else {
            $post_type = 'postType=' . esc_attr( $post['post_type'] );
        }

        $shared_attributes = '
              id="' . esc_attr( $display_field_id ) . '"
              name="' . esc_attr( $field_key ) . '"
              label="' . esc_attr( $fields[$field_key]['name'] ) . '"
              ' . esc_html( $post_type ) . '
              ' . esc_html( $icon ) . '
              ' . esc_html( $required_tag ) . '
              ' . esc_html( $disabled ) . '
              ' . ( $is_private ? 'private' : null ) . '
        ';

        return $shared_attributes;
    }

    public static function render_icon_slot( $field ) {
        if ( isset( $field['font-icon'] ) && !empty( $field['font-icon'] ) ): ?>
            <span slot="icon-start">
                <i class="dt-icon <?php echo esc_html( $field['font-icon'] ) ?>"></i>
            </span>
        <?php endif;
    }

    public static function render_communication_channel( $field_key, $fields, $post ) {
        $shared_attributes = self::shared_attributes( $field_key, $fields, $post );
        ?>
        <dt-comm-channel <?php echo wp_kses_post( $shared_attributes ) ?>
            value="<?php echo esc_attr( isset( $post[$field_key] ) ? json_encode( $post[$field_key] ) : '' ) ?>"
            <?php self::render_icon_slot( $fields[$field_key] ) ?>
            >
        </dt-comm-channel>
        <?php
    }

    public static function render_connection( $field_key, $fields, $post ) {
        $shared_attributes = self::shared_attributes( $field_key, $fields, $post );

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
            allowAdd>
            <?php dt_render_icon_slot( $fields[$field_key] ) ?>
        </dt-connection>
        <?php
    }

    public static function render_date( $field_key, $fields, $post ) {
        $shared_attributes = self::shared_attributes( $field_key, $fields, $post );
        ?>
        <dt-date <?php echo wp_kses_post( $shared_attributes ) ?>
            timestamp="<?php echo esc_html( $post[$field_key]['timestamp'] ?? '' ) ?>">
            <?php dt_render_icon_slot( $fields[$field_key] ) ?>
        </dt-date>
        <?php
    }

    public static function render_key_select( $field_key, $fields, $post ) {
        $shared_attributes = self::shared_attributes( $field_key, $fields, $post );

        $options_array = $fields[$field_key]['default'];
        $options_array = array_map(function ( $key, $value ) {
            return [
                'id' => $key,
                'label' => $value['label'],
                'color' => $value['color'] ?? null,
            ];
        }, array_keys( $options_array ), $options_array);
        ?>
        <dt-single-select <?php echo wp_kses_post( $shared_attributes ) ?>
            options="<?php echo esc_attr( json_encode( $options_array ) ) ?>"
            value="<?php echo esc_attr( isset( $post[$field_key] ) ? $post[$field_key]['key'] : '' ) ?>">
            <?php dt_render_icon_slot( $fields[$field_key] ) ?>
        </dt-single-select>
        <?php
    }

    public static function render_location_meta( $field_key, $fields, $post ) {
        $shared_attributes = self::shared_attributes( $field_key, $fields, $post );
        ?>
        <dt-location <?php echo wp_kses_post( $shared_attributes ) ?>
            value="<?php echo esc_html( $post[$field_key] ?? '' ) ?>"
            <?php dt_render_icon_slot( $fields[$field_key] ) ?>
            >
        </dt-location>
        <?php
    }

    public static function render_multi_select( $field_key, $fields, $post ) {
        $shared_attributes = self::shared_attributes( $field_key, $fields, $post );
        $options_array = $fields[$field_key]['default'];
        $options_array = array_map(function ( $key, $value ) {
            return [
                'id' => $key,
                'label' => $value['label'],
                'color' => $value['color'] ?? null,
                'icon' => $value['icon'] ?? null,
            ];
        }, array_keys( $options_array ), $options_array);

        if ( isset( $fields[$field_key]['display'] ) && $fields[$field_key]['display'] === 'typeahead' ) {
            // typeahead
            ?>
            <dt-multi-select <?php echo wp_kses_post( $shared_attributes ) ?>
                options="<?php echo esc_attr( json_encode( $options_array ) ) ?>"
                value="<?php echo esc_attr( isset( $post[$field_key] ) ? json_encode( $post[$field_key] ) : '' ) ?>">
                <?php dt_render_icon_slot( $fields[$field_key] ) ?>
            </dt-multi-select>
            <?php
        } else {
            // button-group, non-typeahead
            $faith_milestone = array( $fields[$field_key]['default'] );
            $faith_milestone_json = json_encode( $faith_milestone );

            // $is_modal_array = [ 'Baptized' ];
            // $is_modal_json = json_encode( $is_modal_array );?>
            <?php /* isModal='<?php echo esc_attr( $is_modal_json ); ?>' */ ?>
            <dt-multi-select-button-group <?php echo wp_kses_post( $shared_attributes ) ?>
                options="<?php echo esc_attr( json_encode( $options_array ) ) ?>"
                value="<?php echo esc_attr( isset( $post[$field_key] ) ? json_encode( $post[$field_key] ) : '' ) ?>">
                <?php dt_render_icon_slot( $fields[$field_key] ) ?>
            </dt-multi-select-button-group>
            <?php
        }
    }

    public static function render_tags( $field_key, $fields, $post ) {
        $shared_attributes = self::shared_attributes( $field_key, $fields, $post );

        $value = array_map(function ( $value ) {
            return $value;
        }, $post[$field_key] ?? []);
        ?>
        <dt-tags <?php echo wp_kses_post( $shared_attributes ) ?>
            value="<?php echo esc_attr( json_encode( $value ) ) ?>"
            placeholder="<?php echo esc_html( sprintf( _x( 'Search %s', "Search 'something'", 'disciple_tools' ), $fields[$field_key]['name'] ) ) ?>"
            allowAdd>
            <?php dt_render_icon_slot( $fields[$field_key] ) ?>
        </dt-tags>
        <?php
    }

    public static function render_text( $field_key, $fields, $post ) {
        $shared_attributes = self::shared_attributes( $field_key, $fields, $post );
        ?>
        <dt-text <?php echo wp_kses_post( $shared_attributes ) ?>
            value="<?php echo esc_html( $post[$field_key] ?? '' ) ?>">
            <?php dt_render_icon_slot( $fields[$field_key] ) ?>
        </dt-text>
        <?php
    }

    public static function render_textarea( $field_key, $fields, $post ) {
        $shared_attributes = self::shared_attributes( $field_key, $fields, $post );
        ?>
        <dt-textarea <?php echo wp_kses_post( $shared_attributes ) ?>
            value="<?php echo esc_html( $post[$field_key] ?? '' ) ?>"
        >
            <?php dt_render_icon_slot( $fields[$field_key] ) ?>
        </dt-textarea>
        <?php
    }
}
