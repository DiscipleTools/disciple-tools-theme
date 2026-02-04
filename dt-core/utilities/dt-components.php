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
            'file_upload'
        ] );
        if ( !in_array( $field_type, $allowed_types ) ){
            return;
        }
        if ( !dt_field_enabled_for_record_type( $fields[$field_key], $post ) ){
            return;
        }

        $icon = null;
        if ( isset( $fields[$field_key]['font-icon'] ) && !empty( $fields[$field_key]['font-icon'] ) ) {
            $icon = 'icon="' . esc_attr( $fields[$field_key]['font-icon'] ) . '"';
        } else if ( isset( $fields[$field_key]['icon'] ) && !empty( $fields[$field_key]['icon'] ) ) {
            $icon = 'icon="' . esc_attr( $fields[$field_key]['icon'] ) . '"';
        }
        if ( isset( $fields[$field_key]['post_type'] ) ) {
            $post_type = 'postType=' . esc_attr( $fields[$field_key]['post_type'] );
        } else if ( isset( $post ) && isset( $post['post_type'] ) ) {
            $post_type = 'postType=' . esc_attr( $post['post_type'] );
        }

        $hide_label = isset( $params['hide_label'] ) && $params['hide_label'] === true;
        $label_attr = $hide_label ? '' : 'label="' . esc_attr( $fields[$field_key]['name'] ) . '"';

        $shared_attributes = '
              id="' . esc_attr( $display_field_id ) . '"
              name="' . esc_attr( $field_key ) . '"
              ' . $label_attr . '
              ' . esc_html( $post_type ?? '' ) . '
              ' . $icon . '
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
        $default_options = $fields[$field_key]['default'];
        $options_array = array_map(function ( $key, $value ) {
            return [
                'id' => (string) $key,
                'label' => $value['label'] ?? $key,
                'color' => $value['color'] ?? null,
                'icon' => $value['icon'] ?? null,
            ];
        }, array_keys( $default_options ), $default_options);
        if ( isset( $fields[$field_key]['display'] ) && $fields[$field_key]['display'] === 'typeahead' ) {
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
                options='<?php echo esc_attr( json_encode( $default_options ) ) ?>'
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

    public static function render_user_select( $field_key, $fields, $post, $params = [] ) {
        $shared_attributes = self::shared_attributes( $field_key, $fields, $post, $params );

        $item = $post[$field_key] ?? null;

        if ( empty( $item ) ) {
            $value = [];
        } else {
            $value = [
                [
                    'id'    => $item['id'] ?? '',
                    'type'  => $item['type'] ?? '',
                    'label' => $item['display'] ?? '',
                ]
            ];
        }

        ?>
        <dt-users-connection <?php echo wp_kses_post( $shared_attributes ) ?>
            value="<?php echo esc_attr( json_encode( $value ) ) ?>"
        single><?php dt_render_icon_slot( $fields[$field_key] ) ?>
        </dt-users-connection>
        <?php
    }

    public static function render_file_upload( $field_key, $fields, $post, $params = [] ) {
        $shared_attributes = self::shared_attributes( $field_key, $fields, $post, $params );
        if ( empty( $shared_attributes ) ) {
            return;
        }

        // Get field settings for configuration
        $accepted_file_types = $fields[ $field_key ]['accepted_file_types'] ?? [ 'image/*', 'application/pdf' ];
        $max_file_size = $fields[$field_key]['max_file_size'] ?? null;
        $delete_enabled = isset( $fields[$field_key]['delete_enabled'] ) ? $fields[$field_key]['delete_enabled'] : true;
        $display_layout = $fields[$field_key]['display_layout'] ?? 'grid';
        $file_type_icon = $fields[$field_key]['file_type_icon'] ?? '';
        $auto_upload = isset( $fields[$field_key]['auto_upload'] ) ? $fields[$field_key]['auto_upload'] : true;
        $download_enabled = isset( $fields[$field_key]['download_enabled'] ) ? $fields[$field_key]['download_enabled'] : true;
        $rename_enabled = isset( $fields[$field_key]['rename_enabled'] ) ? $fields[$field_key]['rename_enabled'] : true;

        // Get post type and ID for API calls
        $post_type = $post['post_type'] ?? '';
        $post_id = $post['ID'] ?? '';

        // Get field value (array of file objects)
        $value = $post[$field_key] ?? [];
        if ( !is_array( $value ) ) {
            $value = [];
        }

        // Enhance file objects with URLs for preview
        $enhanced_value = array_map(function( $file ) {
            $file_key = is_array( $file ) && isset( $file['key'] ) ? $file['key'] : ( is_string( $file ) ? $file : '' );

            if ( empty( $file_key ) ) {
                return $file;
            }

            // If already an array with all needed data, enhance with URLs (always regenerate to refresh expired presigned URLs)
            if ( is_array( $file ) ) {
                $file_type = $file['type'] ?? '';

                // Add file URL
                if ( DT_Storage_API::is_enabled() ) {
                    $file['url'] = DT_Storage_API::get_file_url( $file_key );
                }

                // Add thumbnail URLs for images
                if ( strpos( $file_type, 'image/' ) === 0 && DT_Storage_API::is_enabled() ) {
                    // Use stored thumbnail_key if available, otherwise generate from original key
                    if ( !empty( $file['thumbnail_key'] ) ) {
                        $file['thumbnail_url'] = DT_Storage_API::get_file_url( $file['thumbnail_key'] );
                    } else {
                        $file['thumbnail_url'] = DT_Storage_API::get_thumbnail_url( $file_key );
                    }

                    // Use stored large_thumbnail_key if available, otherwise generate from original key
                    if ( !empty( $file['large_thumbnail_key'] ) ) {
                        $file['large_thumbnail_url'] = DT_Storage_API::get_file_url( $file['large_thumbnail_key'] );
                    } else {
                        $file['large_thumbnail_url'] = DT_Storage_API::get_large_thumbnail_url( $file_key );
                    }
                }
            } else {
                // Convert string key to array format
                $file = [
                    'key' => $file_key,
                    'name' => basename( $file_key ),
                    'type' => '',
                ];
                if ( DT_Storage_API::is_enabled() ) {
                    $file['url'] = DT_Storage_API::get_file_url( $file_key );
                }
            }

            return $file;
        }, $value);

        // Determine key prefix (use post type, post id and field key as prefix for better organization)
        $key_prefix = $post_type . '/' . $post_id . '/' . $field_key;

        // Output icon attribute directly to avoid wp_kses_post truncating font-icon values (e.g. "mdi mdi-file-arrow-up-down")
        $field_icon = $fields[ $field_key ]['font-icon'] ?? $fields[ $field_key ]['icon'] ?? '';
        ?>
        <dt-file-upload <?php echo wp_kses_post( $shared_attributes ) ?>
            <?php if ( !empty( $field_icon ) ) : ?>icon="<?php echo esc_attr( $field_icon ); ?>"
            <?php endif; ?>
            value="<?php echo esc_attr( json_encode( $enhanced_value ) ) ?>"
            accepted-file-types='<?php echo esc_attr( json_encode( $accepted_file_types ) ) ?>'
            <?php if ( $max_file_size ): ?>
                max-file-size="<?php echo esc_attr( $max_file_size ) ?>"
            <?php endif; ?>
            <?php if ( !$delete_enabled ): ?>
                delete-enabled="false"
            <?php endif; ?>
            display-layout="<?php echo esc_attr( $display_layout ) ?>"
            <?php if ( $file_type_icon ): ?>
                file-type-icon="<?php echo esc_attr( $file_type_icon ) ?>"
            <?php endif; ?>
            auto-upload="<?php echo $auto_upload ? 'true' : 'false' ?>"
            <?php if ( !$download_enabled ): ?>
                download-enabled="false"
            <?php endif; ?>
            <?php if ( !$rename_enabled ): ?>
                rename-enabled="false"
            <?php endif; ?>
            post-type="<?php echo esc_attr( $post_type ) ?>"
            post-id="<?php echo esc_attr( $post_id ) ?>"
            meta-key="<?php echo esc_attr( $field_key ) ?>"
            key-prefix="<?php echo esc_attr( $key_prefix ) ?>"
        >
        </dt-file-upload>
        <?php
    }
}
