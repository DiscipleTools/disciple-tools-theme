<?php

if ( ! defined( 'DT_FUNCTIONS_READY' ) ){
    define( 'DT_FUNCTIONS_READY', true );


    /**
     * A simple function to assist with development and non-disruptive debugging.
     * -----------
     * -----------
     * REQUIREMENT:
     * WP Debug logging must be set to true in the wp-config.php file.
     * Add these definitions above the "That's all, stop editing! Happy blogging." line in wp-config.php
     * -----------
     * define( 'WP_DEBUG', true ); // Enable WP_DEBUG mode
     * define( 'WP_DEBUG_LOG', true ); // Enable Debug logging to the /wp-content/debug.log file
     * define( 'WP_DEBUG_DISPLAY', false ); // Disable display of errors and warnings
     * @ini_set( 'display_errors', 0 );
     * -----------
     * -----------
     * EXAMPLE USAGE:
     * (string)
     * write_log('THIS IS THE START OF MY CUSTOM DEBUG');
     * -----------
     * (array)
     * $an_array_of_things = ['an', 'array', 'of', 'things'];
     * write_log($an_array_of_things);
     * -----------
     * (object)
     * $an_object = new An_Object
     * write_log($an_object);
     */
    if ( ! function_exists( 'dt_write_log' ) ) {
        /**
         * A function to assist development only.
         * This function allows you to post a string, array, or object to the WP_DEBUG log.
         * It also prints elapsed time since the last call.
         *
         * @param $log
         */
        function dt_write_log( $log ) {
            if ( true === WP_DEBUG ) {
                global $dt_write_log_microtime;
                $now = microtime( true );
                if ( $dt_write_log_microtime > 0 ) {
                    $elapsed_log = sprintf( '[elapsed:%5dms]', ( $now - $dt_write_log_microtime ) * 1000 );
                } else {
                    $elapsed_log = '[elapsed:-------]';
                }
                $dt_write_log_microtime = $now;
                if ( is_array( $log ) || is_object( $log ) ) {
                    error_log( $elapsed_log . ' ' . print_r( $log, true ) );
                } else {
                    error_log( "$elapsed_log $log" );
                }
            }
        }
    }

    if ( !function_exists( 'dt_is_rest' ) ) {
        /**
         * Checks if the current request is a WP REST API request.
         *
         * Case #1: After WP_REST_Request initialisation
         * Case #2: Support "plain" permalink settings
         * Case #3: URL Path begins with wp-json/ (your REST prefix)
         *          Also supports WP installations in subfolders
         *
         * @returns boolean
         */
        function dt_is_rest( $namespace = null ) {
            $prefix = rest_get_url_prefix();
            if ( defined( 'REST_REQUEST' ) && REST_REQUEST
                 || isset( $_GET['rest_route'] )
                    && strpos( trim( sanitize_text_field( wp_unslash( $_GET['rest_route'] ) ), '\\/' ), $prefix, 0 ) === 0 ) {
                return true;
            }
            $rest_url    = wp_parse_url( site_url( $prefix ) );
            $current_url = wp_parse_url( add_query_arg( array() ) );
            $is_rest = strpos( $current_url['path'], $rest_url['path'], 0 ) === 0;
            if ( $namespace ){
                return $is_rest && strpos( $current_url['path'], $namespace ) != false;
            } else {
                return $is_rest;
            }
        }
    }

    /**
     * The path of the url excluding the subfolder if wp is installed in a subfolder.
     * https://example.com/sub/contacts/3/?param=true
     * will return contacts/3/?param=true
     * @return string
     */
    if ( ! function_exists( 'dt_get_url_path' ) ) {
        function dt_get_url_path( $ignore_query_parameters = false ) {
            if ( isset( $_SERVER['HTTP_HOST'] ) ) {
                $url  = ( !isset( $_SERVER['HTTPS'] ) || @( $_SERVER['HTTPS'] != 'on' ) ) ? 'http://'. sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : 'https://'. sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) );
                if ( isset( $_SERVER['REQUEST_URI'] ) ) {
                    $url .= esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) );
                }
                //remove the domain part. Ex: https://example.com/
                $url = trim( str_replace( get_site_url(), '', $url ), '/' );

                //remove query parameters
                if ( $ignore_query_parameters ){
                    $url = strtok( $url, '?' ); //allow get parameters
                }
                //remove trailing '?'
                if ( substr( $url, -1 ) === '?' ){
                    $url = substr( $url, 0, -1 );
                }
                // remove trailing '/'
                $url = untrailingslashit( $url );

                return $url;
            }
            return '';
        }
    }

    if ( ! function_exists( 'dt_get_post_type' ) ) {
        /**
         * The post type as found in the url returned by dt_get_url_path
         * https://example.com/sub/contacts/3/?param=true
         * will return 'contacts'
         * @return string
         */
        function dt_get_post_type() {
            $url_path = dt_get_url_path();
            $url_path_with_no_query_string = explode( '?', $url_path )[0];
            return explode( '/', $url_path_with_no_query_string )[0];
        }
    }

    if ( ! function_exists( 'dt_array_to_sql' ) ) {
        function dt_array_to_sql( $values ) {
            if ( empty( $values ) ) {
                return 'NULL';
            }
            foreach ( $values as &$val ) {
                if ( '\N' === $val || empty( $val ) ) {
                    $val = 'NULL';
                } else {
                    $val = "'" . esc_sql( trim( $val ) ) . "'";
                }
            }
            return implode( ',', $values );
        }
    }


    /**
     * @param $date
     * @param string $format  options are short, long, or [custom]
     *
     * @return bool|int|string
     */
    if ( ! function_exists( 'dt_format_date' ) ) {
        function dt_format_date( $date, $format = 'short' ) {
            $date_format = get_option( 'date_format' );
            $time_format = get_option( 'time_format' );
            if ( $format === 'short' ) {
                // $format = $date_format;
                // formatting it with internationally understood date, as there was a
                // struggle getting dates to show in user's selected language and not
                // in the site language.
                $format = 'Y-m-d';
            } else if ( $format === 'long' ) {
                $format = $date_format . ' ' . $time_format;
            }
            if ( is_numeric( $date ) ) {
                $formatted = date_i18n( $format, $date );
            } else {
                $formatted = mysql2date( $format, $date );
            }
            return $formatted;
        }
    }

    if ( ! function_exists( 'dt_date_start_of_year' ) ) {
        function dt_date_start_of_year() {
            $this_year = gmdate( 'Y' );
            $timestamp = strtotime( $this_year . '-01-01' );
            return $timestamp;
        }
    }
    if ( ! function_exists( 'dt_date_end_of_year' ) ) {
        function dt_date_end_of_year() {
            $this_year = (int) gmdate( 'Y' );
            return strtotime( ( $this_year + 1 ) . '-01-01' );
        }
    }
    if ( ! function_exists( 'dt_get_year_from_timestamp' ) ) {
        function dt_get_year_from_timestamp( int $time ) {
            return gmdate( 'Y', $time );
        }
    }

    if ( ! function_exists( 'dt_sanitize_array_html' ) ) {
        function dt_sanitize_array_html( $array ) {
            array_walk_recursive($array, function ( &$v ) {
                $v = filter_var( trim( $v ), FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES );
            });
            return $array;
        }
    }

    if ( ! function_exists( 'dt_recursive_sanitize_array' ) ) {
        function dt_recursive_sanitize_array( array $array ) : array {
            foreach ( $array as $key => &$value ) {
                if ( is_array( $value ) ) {
                    $value = dt_recursive_sanitize_array( $value );
                }
                else {
                    $value = sanitize_text_field( wp_unslash( $value ) );
                }
            }
            return $array;
        }
    }

    /**
     * Deprecated function, use dt_get_available_languages()
     */
    if ( ! function_exists( 'dt_get_translations' ) ) {
        function dt_get_translations() {
            require_once( ABSPATH . 'wp-admin/includes/translation-install.php' );
            $translations = wp_get_available_translations(); // @todo throwing errors if wp.org connection isn't established
            return $translations;
        }
    }

    if ( ! function_exists( 'dt_get_available_languages' ) ) {
        /**
         * Return the list of available languages. Defaults to all translations in the theme.
         *
         * If an array of available language codes is given, then the function will return the language info for
         * these language codes. Useful if you want to get the language info for your plugin's translated languages
         *
         * If $all is set to true, then the function will return the unfiltered complete language information array.
         *
         * @param bool $code_as_key Do we want to return an assosciative array with the codes as the keys
         * @param bool $all Returns all possible languages in the world ( or at least those we have in our system :)
         * @param array $available_language_codes The list of language codes that have been translated ( if you want to filter the list by languages in your plugin for example)
         *
         * @return array
         */
        function dt_get_available_languages( $code_as_key = false, $all = false, $available_language_codes = [] ) {
            $translations = dt_get_global_languages_list();

            if ( true === $all ) {
                return $translations;
            }

            if ( empty( $available_language_codes ) ) {
                $available_language_codes = get_available_languages( get_template_directory() .'/dt-assets/translation' );
            }

            array_unshift( $available_language_codes, 'en_US' );
            $available_translations = [];
            $site_default_locale = get_option( 'WPLANG' );

            foreach ( $available_language_codes as $code ){
                if ( isset( $translations[$code] ) ){
                    $translations[$code]['site_default'] = $site_default_locale === $code;
                    $translations[$code]['english_name'] = $translations[$code]['label'];
                    $translations[$code]['language'] = $code;
                    if ( !$code_as_key ){
                        $available_translations[] = $translations[$code];
                    } else {
                        $available_translations[$code] = $translations[$code];
                    }
                }
            }
            return $available_translations;
        }
    }

    if ( !function_exists( 'dt_language_select' ) ){
        function dt_language_select( $user_id = null ){
            if ( $user_id === null ){
                $user_id = get_current_user_id();
            }
            $languages = dt_get_available_languages();
            $dt_user_locale = get_user_locale( $user_id );
            ?>
            <select name="locale">
                <?php foreach ( $languages as $language ){ ?>
                    <option
                        value="<?php echo esc_html( $language['language'] ); ?>" <?php selected( $dt_user_locale === $language['language'] ) ?>>
                        <?php echo esc_html( ! empty( $language['flag'] ) ? $language['flag'] . ' ' : '' ); ?> <?php echo esc_html( $language['native_name'] ); ?>
                    </option>
                <?php } ?>
            </select>
            <?php
        }
    }

    if ( !function_exists( 'dt_create_field_key' ) ){
        function dt_create_field_key( $s, $with_hash = false ){
            //note we don't limit to alhpa_numeric because it would strip out all non latin based languages
            $s = str_replace( ' ', '_', $s ); // Replaces all spaces with hyphens.
            $s = sanitize_key( $s );
            if ( $with_hash === true ){
                $s .= '_' . substr( md5( rand( 10000, 100000 ) ), 0, 3 ); // create a unique 3 digit key
            }
            if ( empty( $s ) ){
                $s .= 'key_' . substr( md5( rand( 10000, 100000 ) ), 0, 3 );
            }
            return $s;
        }
    }
    if ( !function_exists( 'dt_render_field_icon' ) ){
        function dt_render_field_icon( $field, $class = 'dt-icon', $default_to_name = false ){
            $icon_rendered = false;
            if ( isset( $field['icon'] ) && !empty( $field['icon'] ) ){
                $icon_rendered = true;
                if ( isset( $field['name'] ) ) {
                    $alt_tag = $field['name'];
                } else if ( isset( $field['label'] ) ) {
                    $alt_tag = $field['label'];
                } else {
                    $alt_tag = '';
                }
                ?>

                <img class="<?php echo esc_html( $class ); ?>" src="<?php echo esc_url( $field['icon'] ) ?>" alt="<?php echo esc_html( $alt_tag ) ?>">

                <?php
            } else if ( isset( $field['font-icon'] ) && !empty( $field['font-icon'] ) ){
                $icon_rendered = true;
                ?>

                <i class="<?php echo esc_html( $field['font-icon'] ); ?> <?php echo esc_html( $class ); ?>"></i>

                <?php
            } else if ( $default_to_name && !empty( $field['name'] ) ){
                ?>

                <strong class="snippet-field-name"><?php echo esc_html( $field['name'] ); ?></strong>

                <?php
            }
            return $icon_rendered;
        }
    }

    if ( ! function_exists( 'dt_has_permissions' ) ) {
        function dt_has_permissions( array $permissions ) : bool {
            if ( count( $permissions ) > 0 ) {
                foreach ( $permissions as $permission ){
                    if ( current_user_can( $permission ) ){
                        return true;
                    }
                }
            }
            return false;
        }
    }


    /**
     * Prints the name of the Group or User
     * Used in the loop to get a friendly name of the 'assigned_to' field of the contact
     *
     * If $return is true, then return the name instead of printing it. (Similar to
     * the $return argument in var_export.)
     *
     * @param  int  $contact_id
     * @param  bool $return
     * @return string
     */
    function dt_get_assigned_name( int $contact_id, bool $return = false ) {

        $metadata = get_post_meta( $contact_id, $key = 'assigned_to', true );

        if ( !empty( $metadata ) ) {
            $meta_array = explode( '-', $metadata ); // Separate the type and id
            $type = $meta_array[0];
            $id = $meta_array[1];

            if ( $type == 'user' ) {
                $value = get_user_by( 'id', $id );
                $rv = $value->display_name;
            } else {
                $value = get_term( $id );
                $rv = $value->name;
            }
            if ( $return ) {
                return $rv;
            } else {
                echo esc_html( $rv );
            }
        }
    }


    function is_associative_array( array $arr ){
        if ( array() === $arr ){
            return false;
        }
        return array_keys( $arr ) !== range( 0, count( $arr ) - 1 );
    }
    /**
     * Recursively merge array2 on to array1
     *
     * @param array $array1
     * @param array $array2
     * @return array
     */
    function dt_array_merge_recursive_distinct( array &$array1, array &$array2 ){
        $merged = $array1;
        if ( !is_associative_array( $array2 ) && !is_associative_array( $merged ) ){
            return array_unique( array_merge( $merged, $array2 ), SORT_REGULAR );
        }
        foreach ( $array2 as $key => &$value ){
            if ( is_array( $value ) && isset( $merged[$key] ) && is_array( $merged[$key] ) ){
                $merged[$key] = dt_array_merge_recursive_distinct( $merged[$key], $value );
            } else {
                $merged[$key] = $value;
            }
        }
        return $merged;
    }

    function dt_field_enabled_for_record_type( $field, $post ){
        if ( !isset( $post['type']['key'] ) ){
            return true;
        }
        // if only_for_type is not set, then the field is available on all types
        if ( !isset( $field['only_for_types'] ) ){
            return true;
        } else if ( $field['only_for_types'] === true ){
            return true;
        } else if ( is_array( $field['only_for_types'] ) && in_array( $post['type']['key'], $field['only_for_types'], true ) ){
            //if the type is in the "only_for_types"
            return true;
        }
        return false;
    }

    function render_new_bulk_record_fields( $dt_post_type ) {
        $post_settings = DT_Posts::get_post_settings( $dt_post_type );
        $selected_type = null;

        foreach ( $post_settings['fields'] as $field_key => $field_settings ) {
            if ( ! empty( $field_settings['hidden'] ) && empty( $field_settings['custom_display'] ) ) {
                continue;
            }
            if ( isset( $field_settings['in_create_form'] ) && $field_settings['in_create_form'] === false ) {
                continue;
            }
            if ( ! isset( $field_settings['tile'] ) ) {
                continue;
            }
            $classes    = '';
            $show_field = false;
            //add types the field should show up on as classes
            if ( ! empty( $field_settings['in_create_form'] ) ) {
                if ( is_array( $field_settings['in_create_form'] ) ) {
                    foreach ( $field_settings['in_create_form'] as $type_key ) {
                        $classes .= $type_key . ' ';
                        if ( $type_key === $selected_type ) {
                            $show_field = true;
                        }
                    }
                } elseif ( $field_settings['in_create_form'] === true ) {
                    $classes    = 'all';
                    $show_field = true;
                }
            } else {
                $classes = 'other-fields';
            }

            ?>
            <!-- hide the fields that were not selected to be displayed by default in the create form -->
            <div <?php echo esc_html( ! $show_field ? 'style=display:none' : '' ); ?>
                class="form-field <?php echo esc_html( $classes ); ?>">
                <?php
                render_field_for_display( $field_key, $post_settings['fields'], [] );
                if ( isset( $field_settings['required'] ) && $field_settings['required'] === true ) { ?>
                    <p class="help-text"
                       id="name-help-text"><?php esc_html_e( 'This is required', 'disciple_tools' ); ?></p>
                <?php } ?>
            </div>
            <?php
        }
    }

    /**
     * Accepts types: key_select, multi_select, text, textarea, number, date, connection, location, communication_channel, tags, user_select, link
     *
     * breadcrumb: new-field-type
     *
     * @param $field_key
     * @param $fields
     * @param $post
     * @param bool $show_extra_controls // show typeahead create button
     * @param bool $show_hidden // show hidden select options
     * @param string $field_id_prefix // add a prefix to avoid fields with duplicate ids.
     */
    function render_field_for_display( $field_key, $fields, $post, $show_extra_controls = false, $show_hidden = false, $field_id_prefix = '' ){
        $disabled = 'disabled';
        if ( isset( $post['post_type'] ) && isset( $post['ID'] ) ) {
            $can_update = DT_Posts::can_update( $post['post_type'], $post['ID'] );
        } else {
            $can_update = true;
        }
        if ( $can_update || isset( $post['assigned_to']['id'] ) && $post['assigned_to']['id'] == get_current_user_id() ) {
            $disabled = '';
        }
        $required_tag = ( isset( $fields[$field_key]['required'] ) && $fields[$field_key]['required'] === true ) ? 'required' : '';
        $field_type = isset( $fields[$field_key]['type'] ) ? $fields[$field_key]['type'] : null;
        $is_private = isset( $fields[$field_key]['private'] ) && $fields[$field_key]['private'] === true;
        $display_field_id = $field_key;
        if ( !empty( $field_id_prefix ) ) {
            $display_field_id = $field_id_prefix . $field_key;
        }
        if ( isset( $fields[$field_key]['type'] ) && empty( $fields[$field_key]['custom_display'] ) && empty( $fields[$field_key]['hidden'] ) ) {
            /* breadrcrumb: new-field-type Add allowed field types */
            $allowed_types = apply_filters( 'dt_render_field_for_display_allowed_types', [ 'key_select', 'multi_select', 'date', 'datetime', 'text', 'textarea', 'number', 'link', 'connection', 'location', 'location_meta', 'communication_channel', 'tags', 'user_select' ] );
            if ( !in_array( $field_type, $allowed_types ) ){
                return;
            }
            if ( !dt_field_enabled_for_record_type( $fields[$field_key], $post ) ){
                return;
            }


            ?>
            <div class="section-subheader">
                <?php dt_render_field_icon( $fields[$field_key] );

                echo esc_html( $fields[$field_key]['name'] );
                ?> <span id="<?php echo esc_html( $display_field_id ); ?>-spinner" class="loading-spinner"></span>
                <?php if ( $is_private ) : ?>
                    <i class="fi-lock small" title="<?php _x( "Private Field: Only I can see it's content", 'disciple_tools' )?>"></i>
                <?php endif;
                if ( $field_type === 'communication_channel' ) : ?>
                    <button data-field-type="<?php echo esc_html( $field_type ) ?>" data-list-class="<?php echo esc_html( $display_field_id ); ?>" class="add-button" type="button" <?php echo esc_html( $disabled ); ?>>
                        <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/small-add.svg' ) ?>"/>
                    </button>
                <?php endif ?>
                <?php if ( $field_type === 'link' ) : ?>

                    <?php $only_one_option = count( $fields[$field_key]['default'] ) === 1 ? esc_attr( array_keys( $fields[$field_key]['default'] )[0] ) : '' ?>

                    <div class="add-link-dropdown"
                        <?php echo !empty( $only_one_option ) ? 'data-only-one-option' : '' ?>
                        data-link-type="<?php echo esc_attr( $only_one_option ) ?>"
                        data-field-key="<?php echo esc_attr( $field_key ) ?>">
                        <button
                            class="add-button add-link-dropdown__button"
                            type="button"
                            data-field-type="<?php echo esc_html( $field_type ) ?>"
                            data-list-class="<?php echo esc_html( $display_field_id ); ?>"
                            <?php echo esc_html( $disabled ); ?>
                        >
                            <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/small-add.svg' ) ?>"/>
                        </button>

                        <div class="add-link-dropdown__content add-link-<?php echo esc_attr( $display_field_id ) ?>"
                            style="<?php echo count( $fields[$field_key]['default'] ) < 2 ? 'display: none' : '' ?>">
                            <?php foreach ( $fields[$field_key]['default'] as $option_key => $option_value ): ?>

                                <?php if ( isset( $option_value['deleted'] ) && $option_value['deleted'] === true ) {
                                    continue;
                                } ?>

                                <div
                                    class="add-link__option"
                                    <?php echo !empty( $only_one_option ) ? 'data-only-one-option' : '' ?>
                                    data-link-type="<?php echo esc_attr( $option_key ) ?>"
                                    data-field-key="<?php echo esc_attr( $field_key ) ?>"
                                >
                                    <span style="margin: 0 5px 1rem 0;"><?php dt_render_field_icon( $option_value ) ?></span>
                                    <?php echo esc_html( $option_value['label'] ) ?>
                                </div>

                            <?php endforeach; ?>
                        </div>
                    </div>

                <?php endif; ?>
                <!-- location add -->
                <?php if ( ( $field_type === 'location' || 'location_meta' === $field_type ) && DT_Mapbox_API::get_key() && ! empty( $post ) ) : ?>
                    <button data-list-class="<?php echo esc_html( $field_key ) ?>" class="add-button" id="new-mapbox-search" type="button" <?php echo esc_html( $disabled ); ?>>
                        <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/small-add.svg' ) ?>"/>
                    </button>
                <?php endif ?>
            </div>
            <?php
            if ( $field_type === 'key_select' ) :
                $color_select = false;
                $active_color = '';
                if ( isset( $fields[$field_key]['default_color'] ) ) {
                    $color_select = true;
                    $active_color = $fields[$field_key]['default_color'];
                    $current_key = $post[$field_key]['key'] ?? '';
                    if ( isset( $fields[$field_key]['default'][ $current_key ]['color'] ) ){
                        $active_color = $fields[$field_key]['default'][ $current_key ]['color'];
                    }
                }
                ?>
                <select class="select-field <?php echo esc_html( $color_select ? 'color-select' : '' ); ?>" id="<?php echo esc_html( $display_field_id ); ?>" style="<?php echo esc_html( $color_select ? ( 'background-color: ' . $active_color ) : '' ); ?>" <?php echo esc_html( $required_tag ) ?> <?php echo esc_html( $disabled ); ?>>
                    <?php if ( !isset( $fields[$field_key]['default']['none'] ) && empty( $fields[$field_key]['select_cannot_be_empty'] ) ) : ?>
                        <option value="" <?php echo esc_html( !isset( $post[$field_key] ) ?: 'selected' ) ?>></option>
                    <?php endif; ?>
                    <?php foreach ( $fields[$field_key]['default'] as $option_key => $option_value ):
                        if ( !$show_hidden && isset( $option_value['hidden'] ) && $option_value['hidden'] === true ){
                            continue;
                        }
                        $selected = isset( $post[$field_key]['key'] ) && $post[$field_key]['key'] === strval( $option_key ); ?>
                        <option value="<?php echo esc_html( $option_key )?>" <?php echo esc_html( $selected ? 'selected' : '' )?>>
                            <?php echo esc_html( $option_value['label'] ) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php elseif ( $field_type === 'tags' ) : ?>
                <div id="<?php echo esc_html( $display_field_id ); ?>" class="tags">
                    <var id="<?php echo esc_html( $display_field_id ); ?>-result-container" class="result-container"></var>
                    <div id="<?php echo esc_html( $display_field_id ); ?>_t" name="form-tags" class="scrollable-typeahead typeahead-margin-when-active">
                        <div class="typeahead__container">
                            <div class="typeahead__field">
                                <span class="typeahead__query">
                                    <input class="js-typeahead-<?php echo esc_html( $display_field_id ); ?> input-height"
                                           data-field="<?php echo esc_html( $field_key );?>"
                                           name="<?php echo esc_html( $display_field_id ); ?>[query]"
                                           placeholder="<?php echo esc_html( sprintf( _x( 'Search %s', "Search 'something'", 'disciple_tools' ), $fields[$field_key]['name'] ) )?>"
                                           autocomplete="off"
                                           data-add-new-tag-text="<?php echo esc_html( __( 'Add new tag "%s"', 'disciple_tools' ) )?>"
                                           data-tag-exists-text="<?php echo esc_html( __( 'Tag "%s" is already being used', 'disciple_tools' ) )?>" <?php echo esc_html( $disabled ); ?>>
                                </span>
                                <?php if ( $show_extra_controls ) : ?>
                                <span class="typeahead__button">
                                    <button type="button" data-open="create-tag-modal" class="create-new-tag typeahead__image_button input-height" data-field="<?php echo esc_html( $field_key );?>" <?php echo esc_html( $disabled ); ?>>
                                        <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/tag-add.svg' ) ?>"/>
                                    </button>
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php elseif ( $field_type === 'multi_select' ) :
                if ( isset( $fields[$field_key]['display'] ) && $fields[$field_key]['display'] === 'typeahead' ){
                    ?>
                    <div class="multi_select" id="<?php echo esc_html( $display_field_id ); ?>" >
                        <var id="<?php echo esc_html( $display_field_id ); ?>-result-container" class="result-container"></var>
                        <div id="<?php echo esc_html( $display_field_id ); ?>_t" name="form-multi_select" class="scrollable-typeahead typeahead-margin-when-active">
                            <div class="typeahead__container">
                                <div class="typeahead__field">
                                    <span class="typeahead__query">
                                        <input class="js-typeahead-<?php echo esc_html( $display_field_id ); ?> input-height"
                                               data-field="<?php echo esc_html( $field_key );?>"
                                               name="<?php echo esc_html( $display_field_id ); ?>[query]"
                                               placeholder="<?php echo esc_html( sprintf( _x( 'Search %s', "Search 'something'", 'disciple_tools' ), $fields[$field_key]['name'] ) )?>"
                                               autocomplete="off" <?php echo esc_html( $disabled ); ?>>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } else { ?>
                    <div class="small button-group" style="display: inline-block">
                        <?php foreach ( $fields[$field_key]['default'] as $option_key => $option_value ): ?>
                            <?php
                            $haystack = $post[ $field_key ] ?? [];
                            if ( ! is_array( $haystack ) ) {
                                $haystack = explode( ' ', $haystack );
                            }
                            $class = ( in_array( $option_key, $haystack ) ) ?
                                'selected-select-button' : 'empty-select-button'; ?>
                            <button id="<?php echo esc_html( $option_key ) ?>" type="button" data-field-key="<?php echo esc_html( $field_key ); ?>"
                                    class="dt_multi_select <?php echo esc_html( $class ) ?> select-button button" <?php echo esc_html( $disabled ); ?>>
                                <?php
                                dt_render_field_icon( $option_value );
                                echo esc_html( $option_value['label'] ?? $option_key );
                                ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                <?php } ?>
            <?php elseif ( $field_type === 'text' ) :?>
                <input id="<?php echo esc_html( $display_field_id ); ?>" type="text" <?php echo esc_html( $required_tag ) ?>
                       class="text-input"
                       value="<?php echo esc_html( $post[$field_key] ?? '' ) ?>" <?php echo esc_html( $disabled ); ?>/>
            <?php elseif ( $field_type === 'textarea' ) :?>
                <textarea id="<?php echo esc_html( $display_field_id ); ?>" <?php echo esc_html( $required_tag ) ?>
                       class="textarea dt_textarea" <?php echo esc_html( $disabled ); ?>><?php echo esc_html( $post[$field_key] ?? '' ) ?></textarea>
            <?php elseif ( $field_type === 'number' ) :?>
                <input id="<?php echo esc_html( $display_field_id ); ?>" type="number" <?php echo esc_html( $required_tag ) ?>
                       class="text-input"
                       value="<?php echo esc_html( $post[$field_key] ?? '' ) ?>" <?php echo esc_html( $disabled ); ?>
                       min="<?php echo esc_html( $fields[$field_key]['min_option'] ?? '' ) ?>"
                       max="<?php echo esc_html( $fields[$field_key]['max_option'] ?? '' ) ?>"
                       onwheel="return false;"
                />
            <?php elseif ( $field_type === 'link' ) : ?>

                <div class="link-group">

                    <div class="link-list-<?php echo esc_attr( $field_key ) ?>">

                        <?php
                        $links_sorted_by_type = [];
                        foreach ( $post[$field_key] ?? [] as $link_item ) {
                            if ( !isset( $link_item['type'] ) ) {
                                continue;
                            }
                            $link_type = $link_item['type'];
                            if ( !isset( $links_sorted_by_type[$link_type] ) ) {
                                $links_sorted_by_type[$link_type] = [];
                            }
                            $links_sorted_by_type[$link_type][] = $link_item;
                        }

                        $only_one_option = count( $fields[$field_key]['default'] ) === 1;
                        foreach ( $fields[$field_key]['default'] as $link_type => $link_value ) : ?>

                            <div class="link-section link-section--<?php echo esc_attr( $link_type ) ?>">
                                <div class="section-subheader" <?php echo $only_one_option ? 'style="display: none"' : '' ?> >
                                    <?php dt_render_field_icon( $link_value ) ?>
                                    <?php echo esc_html( $link_value['label'] ); ?>
                                </div>

                                    <?php if ( isset( $links_sorted_by_type[$link_type] ) ) : ?>
                                        <?php foreach ( $links_sorted_by_type[$link_type] as $link_item ) : ?>

                                            <?php render_link_field( $field_key, $link_type, $link_item['value'], $display_field_id, $link_item['meta_id'], $required_tag, $disabled ); ?>

                                        <?php endforeach; ?>
                                    <?php endif; ?>

                            </div>

                        <?php endforeach; ?>

                    </div>

                    <?php foreach ( $fields[$field_key]['default'] as $option_key => $option_value ): ?>

                        <?php if ( isset( $option_value['deleted'] ) && $option_value['deleted'] === true ) {
                            continue;
                        } ?>

                        <div style="display: none" id="link-template-<?php echo esc_html( $field_key ) ?>-<?php echo esc_html( $option_key ) ?>">
                            <?php render_link_field( $field_key, $option_key, '', $display_field_id, '', $required_tag, $disabled ) ?>
                        </div>

                    <?php endforeach; ?>

                </div>

                <?php elseif ( $field_type === 'date' ) :?>
                <div class="<?php echo esc_html( $display_field_id ); ?> input-group">
                    <input id="<?php echo esc_html( $display_field_id ); ?>" class="input-group-field dt_date_picker" type="text" autocomplete="off" <?php echo esc_html( $required_tag ) ?>
                           value="<?php echo esc_html( $post[$field_key]['timestamp'] ?? '' ) ?>" <?php echo esc_html( $disabled ); ?> >
                    <div class="input-group-button">
                        <button id="<?php echo esc_html( $display_field_id ); ?>-clear-button" class="button alert clear-date-button" data-inputid="<?php echo esc_html( $display_field_id ); ?>" title="Delete Date" type="button" <?php echo esc_html( $disabled ); ?>>x</button>
                    </div>
                </div>
            <?php elseif ( $field_type === 'connection' ) :?>
                <div id="<?php echo esc_attr( $display_field_id . '_connection' ) ?>" class="dt_typeahead <?php echo esc_html( $disabled ) ?>">
                    <span id="<?php echo esc_html( $display_field_id ); ?>-result-container" class="result-container"></span>
                    <div id="<?php echo esc_html( $display_field_id ); ?>_t" name="form-<?php echo esc_html( $display_field_id ); ?>" class="scrollable-typeahead typeahead-margin-when-active">
                        <div class="typeahead__container">
                            <div class="typeahead__field">
                                <span class="typeahead__query">
                                    <input class="js-typeahead-<?php echo esc_html( $display_field_id ); ?> input-height"
                                           data-field="<?php echo esc_html( $field_key ); ?>"
                                           data-post_type="<?php echo esc_html( $fields[$field_key]['post_type'] ) ?>"
                                           data-field_type="connection"
                                           name="<?php echo esc_html( $display_field_id ); ?>[query]"
                                           placeholder="<?php echo esc_html( sprintf( _x( 'Search %s', "Search 'something'", 'disciple_tools' ), $fields[$field_key]['name'] ) )?>"
                                           autocomplete="off" <?php echo esc_html( $disabled ); ?>>
                                </span>
                                <?php if ( $show_extra_controls ) : ?>
                                <span class="typeahead__button">
                                    <button type="button" data-connection-key="<?php echo esc_html( $display_field_id ); ?>" class="create-new-record typeahead__image_button input-height" <?php echo esc_html( $disabled ); ?>>
                                        <?php $icon = isset( $fields[$field_key]['create-icon'] ) ? $fields[$field_key]['create-icon'] : get_template_directory_uri() . '/dt-assets/images/add-contact.svg'; ?>
                                        <img src="<?php echo esc_html( $icon ) ?>"/>
                                    </button>
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php elseif ( $field_type === 'location_meta' ) : ?>
                <?php if ( DT_Mapbox_API::get_key() && empty( $post ) ) : // test if Mapbox key is present ?>
                    <div id="mapbox-autocomplete" class="mapbox-autocomplete input-group" data-autosubmit="false">
                        <input id="mapbox-search" type="text" class="input-group-field" name="mapbox_search" placeholder="Search Location" autocomplete="off" dir="auto" <?php echo esc_html( $disabled ); ?>/>
                        <div class="input-group-button">
                            <button id="mapbox-spinner-button" class="button hollow" style="display:none;" <?php echo esc_html( $disabled ); ?>><span class="loading-spinner active"></span></button>
                            <button id="mapbox-clear-autocomplete" class="button alert input-height delete-button-style mapbox-delete-button" style="display:none;" type="button" <?php echo esc_html( $disabled ); ?>>&times;</button>
                        </div>
                        <div id="mapbox-autocomplete-list" class="mapbox-autocomplete-items"></div>
                    </div>
                    <script>
                        jQuery(document).ready(function(){
                            write_input_widget()
                        })
                    </script>
                <?php elseif ( DT_Mapbox_API::get_key() ) : // test if Mapbox key is present ?>
                    <div id="mapbox-wrapper"></div>
                <?php endif; ?>
            <?php elseif ( $field_type === 'location' ) :?>
                <div class="dt_location_grid" data-id="<?php echo esc_html( $field_key ); ?>">
                    <var id="<?php echo esc_html( $field_key ); ?>-result-container" class="result-container"></var>
                    <div id="<?php echo esc_html( $field_key ); ?>_t" name="form-<?php echo esc_html( $field_key ); ?>" class="scrollable-typeahead typeahead-margin-when-active">
                        <div class="typeahead__container">
                            <div class="typeahead__field">
                                <span class="typeahead__query">
                                    <input class="js-typeahead-<?php echo esc_html( $display_field_id ); ?> input-height"
                                           data-field="<?php echo esc_html( $field_key ); ?>"
                                           data-field_type="location"
                                           name="<?php echo esc_html( $field_key ); ?>[query]"
                                           placeholder="<?php echo esc_html( sprintf( _x( 'Search %s', "Search 'something'", 'disciple_tools' ), $fields[$field_key]['name'] ) )?>"
                                           autocomplete="off" <?php echo esc_html( $disabled ); ?>/>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php elseif ( $field_type === 'communication_channel' ) : ?>
                <div id="edit-<?php echo esc_html( $field_key ) ?>" >
                    <?php foreach ( $post[$field_key] ?? [] as $field_value ) : ?>
                        <div class="input-group">
                            <input id="<?php echo esc_html( $field_value['key'] ) ?>"
                                   type="text"
                                   data-field="<?php echo esc_html( $field_key ); ?>"
                                   value="<?php echo esc_html( $field_value['value'] ) ?>"
                                   class="dt-communication-channel input-group-field" dir="auto"<?php echo esc_html( $disabled ); ?>/>
                            <div class="input-group-button">
                                <button class="button alert input-height delete-button-style channel-delete-button delete-button new-<?php echo esc_html( $field_key ); ?>" data-field="<?php echo esc_html( $field_key ); ?>" data-key="<?php echo esc_html( $field_value['key'] ); ?>" <?php echo esc_html( $disabled ); ?>>&times;</button>
                            </div>
                        </div>
                    <?php endforeach;
                    if ( empty( $post[$field_key] ) ?? [] ): ?>
                        <div class="input-group">
                            <input type="text"
                                    <?php echo esc_html( $required_tag ) ?>
                                   data-field="<?php echo esc_html( $field_key ) ?>"
                                   class="dt-communication-channel input-group-field" dir="auto" <?php echo esc_html( $disabled ); ?>/>
                        </div>
                    <?php endif ?>
                </div>
            <?php elseif ( $field_type === 'user_select' ) : ?>
                <div id="<?php echo esc_html( $field_key ); ?>" class="<?php echo esc_html( $display_field_id ); ?> dt_user_select">
                    <var id="<?php echo esc_html( $display_field_id ); ?>-result-container" class="result-container <?php echo esc_html( $display_field_id ); ?>-result-container"></var>
                    <div id="<?php echo esc_html( $display_field_id ); ?>_t" name="form-<?php echo esc_html( $display_field_id ); ?>" class="scrollable-typeahead">
                        <div class="typeahead__container" style="margin-bottom: 0">
                            <div class="typeahead__field">
                                <span class="typeahead__query">
                                    <input class="js-typeahead-<?php echo esc_html( $display_field_id ); ?> input-height" dir="auto"
                                           name="<?php echo esc_html( $display_field_id ); ?>[query]" placeholder="<?php echo esc_html_x( 'Search Users', 'input field placeholder', 'disciple_tools' ) ?>"
                                           data-field_type="user_select"
                                           data-field="<?php echo esc_html( $field_key ); ?>"
                                           autocomplete="off" <?php echo esc_html( $disabled ); ?>>
                                </span>
                                <span class="typeahead__button">
                                    <button type="button" class="search_<?php echo esc_html( $field_key ); ?> typeahead__image_button input-height" data-id="<?php echo esc_html( $field_key ); ?>" <?php echo esc_html( $disabled ); ?>>
                                        <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/chevron_down.svg' ) ?>"/>
                                    </button>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif;
        }
        do_action( 'dt_render_field_for_display_template', $post, $field_type, $field_key, $required_tag, $display_field_id );
    }

    function render_link_field( $field_key, $option_key, $value, $display_field_id, $meta_id, $required_tag, $disabled ) {
        ?>

        <div class="input-group">
            <input
                type="text"
                class="link-input input-group-field"
                value="<?php echo esc_html( $value ) ?>"
                data-meta-id="<?php echo esc_html( $meta_id ) ?>"
                data-field-key="<?php echo esc_html( $display_field_id ) ?>"
                data-type="<?php echo esc_html( $option_key ) ?>"
                <?php echo esc_html( $required_tag ) ?>
                <?php echo esc_html( $disabled ) ?>
            >
            <div class="input-group-button">
                <button
                    class="button alert delete-button-style input-height link-delete-button delete-button"
                    data-meta-id="<?php echo esc_html( $meta_id ) ?>"
                    data-field-key="<?php echo esc_html( $field_key ) ?>"
                >
                    &times;
                </button>
            </div>
        </div>

        <?php
    }

    function dt_increment( &$var, $val ){
        if ( !isset( $var ) ){
            $var = 0;
        }
        $var += (int) $val;
    }

    function dt_get_keys_map( $array, $key = 'ID' ){
        return array_map(  function ( $a ) use ( $key ) {
            if ( isset( $a[$key] ) ){
                return $a[$key];
            } else {
                return null;
            }
        }, $array );
    }

    /**
     * Test if module is enabled
     */
    if ( ! function_exists( 'dt_is_module_enabled' ) ) {
        function dt_is_module_enabled( string $module_key, $check_prereqs = false ) : bool {
            $modules = dt_get_option( 'dt_post_type_modules' );
            $module_enabled = isset( $modules[$module_key]['enabled'] ) && !empty( $modules[$module_key]['enabled'] );
            if ( $module_enabled && $check_prereqs ){
                foreach ( $modules[$module_key]['prerequisites'] as $prereq ){
                    $prereq_enabled = isset( $modules[$prereq]['enabled'] ) ? $modules[$prereq]['enabled'] : false;
                    if ( !$prereq_enabled ){
                        return false;
                    }
                }
            }
            return $module_enabled;
        }
    }

    /**
     * Returns a completely unique 64 bit hashed key
     * @since 1.1
     */
    if ( ! function_exists( 'dt_create_unique_key' ) ) {
        function dt_create_unique_key() : string {
            try {
                $hash = hash( 'sha256', bin2hex( random_bytes( 256 ) ) );
            } catch ( Exception $exception ) {
                $hash = hash( 'sha256', bin2hex( rand( 0, 1234567891234567890 ) . microtime() ) );
            }
            return $hash;
        }
    }

    /**
     * Validate specified date format
     */
    if ( !function_exists( 'dt_validate_date' ) ){
        function dt_validate_date( string $date ): bool{
            $formats = [ 'Y-m-d', 'Y-m-d H:i:s', 'Y-m-d H:i:s.u', DateTimeInterface::ISO8601, DateTimeInterface::RFC3339 ];
            foreach ( $formats as $format ){
                $date_time = DateTime::createFromFormat( $format, $date );
                if ( $date_time && $date_time->format( $format ) === $date ){
                    return true;
                }
            }
            return false;
        }
    }

    /**
     * Dump and die
     */
    if ( !function_exists( 'dd' ) ) {
        function dd( ...$params ) {
            foreach ( $params as $param ) {
                var_dump( $param );
            }

            exit;
        }
    }

    /**
     * Convert a slug like 'name_or_title' to a label like 'Name or Title'
     */
    if ( !function_exists( 'dt_label_from_slug' ) ) {
        function dt_label_from_slug( $slug ) {
            $string = preg_replace( '/^' . preg_quote( 'dt_', '/' ) . '/', '', $slug );
            $string = str_replace( '_', ' ', $string );

            /* Words that should be entirely lower-case */
            $articles_conjunctions_prepositions = [
                'a',
                'an',
                'the',
                'and',
                'but',
                'or',
                'nor',
                'if',
                'then',
                'else',
                'when',
                'at',
                'by',
                'from',
                'for',
                'in',
                'off',
                'on',
                'out',
                'over',
                'to',
                'into',
                'with'
            ];
            /* Words that should be entirely upper-case (need to be lower-case in this list!) */
            $acronyms_and_such = [
                'asap',
                'unhcr',
                'wpse',
                'dt'
            ];
            /* split title string into array of words */
            $words = explode( ' ', strtolower( $string ) );
            /* iterate over words */
            foreach ( $words as $position => $word ) {
                /* re-capitalize acronyms */
                if ( in_array( $word, $acronyms_and_such ) ) {
                    $words[ $position ] = strtoupper( $word );
                    /* capitalize first letter of all other words, if... */
                } elseif (
                    /* ...first word of the title string... */
                    0 === $position ||
                    /* ...or not in above lower-case list*/
                    !in_array( $word, $articles_conjunctions_prepositions )
                ) {
                    $words[ $position ] = ucwords( $word );
                }
            }
            /* re-combine word array */
            $string = implode( ' ', $words );
            /* return title string in title case */
            return $string;
        }
    }

    if ( !function_exists( 'dt_get_global_languages_list' ) ){
        function dt_get_global_languages_list(){
            /* You can find flags with country codes here https://unpkg.com/country-flag-emoji@1.0.3/dist/country-flag-emoji.umd.js */
            /* Then you should be able to search for the country code e.g. af_NA NA -> Namibia to get the necessary flags */
            $global_languages_list = [
                'af' => [ 'label' => 'Afrikaans', 'native_name' => 'Afrikaans', 'flag' => '🇿🇦', 'rtl' => false ],
                'af_NA' => [ 'label' => 'Afrikaans (Namibia)', 'native_name' => 'Afrikáans Namibië', 'flag' => '🇳🇦', 'rtl' => false ],
                'af_ZA' => [ 'label' => 'Afrikaans (South Africa)', 'native_name' => 'Afrikaans Suid-Afrika', 'flag' => '🇿🇦', 'rtl' => false ],
                'ak' => [ 'label' => 'Akan', 'native_name' => 'Akan', 'flag' => '🇬🇭', 'rtl' => false ],
                'ak_GH' => [ 'label' => 'Akan (Ghana)', 'native_name' => 'Akan (Ghana)', 'flag' => '🇬🇭', 'rtl' => false ],
                'am' => [ 'label' => 'Amharic', 'native_name' => 'አማርኛ (AmarəÑña)', 'flag' => '🇪🇹', 'rtl' => false ],
                'am_ET' => [ 'label' => 'Amharic (Ethiopia)', 'native_name' => 'አማርኛ (AmarəÑña)', 'flag' => '🇪🇹', 'rtl' => false ],
                'ar' => [ 'label' => 'Arabic', 'native_name' => 'العربية', 'flag' => '🇦🇪', 'rtl' => true ],
                'ar_AE' => [ 'label' => 'Arabic (United Arab Emirates)', 'native_name' => 'العربية‎ / Al-ʻArabiyyah, ʻArabī الإمارات العربية المتحدة', 'flag' => '🇦🇪', 'rtl' => true ],
                'ar_BH' => [ 'label' => 'Arabic (Bahrain)', 'native_name' => 'العربية البحرانية', 'flag' => '🇧🇭', 'rtl' => true ],
                'ar_DZ' => [ 'label' => 'Arabic (Algeria)', 'native_name' => 'دزيريةالجزائر', 'flag' => '🇩🇿', 'rtl' => true ],
                'ar_EG' => [ 'label' => 'Arabic (Egypt)', 'native_name' => 'مصرى', 'flag' => '🇪🇬', 'rtl' => true ],
                'ar_IQ' => [ 'label' => 'Arabic (Iraq)', 'native_name' => 'اللهجة العراقية', 'flag' => '🇮🇶', 'rtl' => true ],
                'ar_JO' => [ 'label' => 'Arabic (Jordan)', 'native_name' => 'اللهجة الأردنية', 'flag' => '🇯🇴', 'rtl' => true ],
                'ar_KW' => [ 'label' => 'Arabic (Kuwait)', 'native_name' => 'كويتي', 'flag' => '🇰🇼', 'rtl' => true ],
                'ar_LB' => [ 'label' => 'Arabic (Lebanon)', 'native_name' => 'اللهجة اللبنانية', 'flag' => '🇱🇧', 'rtl' => true ],
                'ar_LY' => [ 'label' => 'Arabic (Libya)', 'native_name' => 'ليبي', 'flag' => '🇱🇾', 'rtl' => true ],
                'ar_MA' => [ 'label' => 'Arabic (Morocco)', 'native_name' => 'الدارجة اللهجة المغربية', 'flag' => '🇲🇦', 'rtl' => true ],
                'ar_OM' => [ 'label' => 'Arabic (Oman)', 'native_name' => 'اللهجة العمانية', 'flag' => '🇴🇲', 'rtl' => true ],
                'ar_QA' => [ 'label' => 'Arabic (Qatar)', 'native_name' => 'العربية (قطر)', 'flag' => '🇶🇦', 'rtl' => true ],
                'ar_SA' => [ 'label' => 'Arabic (Saudi Arabia)', 'native_name' => "شبه جزيرة 'العربية", 'flag' => '🇸🇦', 'rtl' => true ],
                'ar_SD' => [ 'label' => 'Arabic (Sudan)', 'native_name' => 'لهجة سودانية', 'flag' => '🇸🇩', 'rtl' => true ],
                'ar_SY' => [ 'label' => 'Arabic (Syria)', 'native_name' => 'شامي', 'flag' => '🇸🇾', 'rtl' => true ],
                'ar_TN' => [ 'label' => 'Arabic (Tunisia)', 'native_name' => 'تونسي', 'flag' => '🇹🇳', 'rtl' => true ],
                'ar_YE' => [ 'label' => 'Arabic (Yemen)', 'native_name' => 'لهجة يمنية', 'flag' => '🇾🇪', 'rtl' => true ],
                'as' => [ 'label' => 'Assamese', 'native_name' => 'অসমীয়া / Ôxômiya', 'flag' => '🇮🇳', 'rtl' => false ],
                'as_IN' => [ 'label' => 'Assamese (India)', 'native_name' => 'অসমীয়া / Ôxômiya (India)', 'flag' => '🇮🇳', 'rtl' => false ],
                'asa' => [ 'label' => 'Asu', 'native_name' => 'Kipare, Casu', 'flag' => '🇹🇿', 'rtl' => false ],
                'asa_TZ' => [ 'label' => 'Asu (Tanzania)', 'native_name' => 'Kipare, Casu (Tanzania)', 'flag' => '🇹🇿', 'rtl' => false ],
                'az' => [ 'label' => 'Azerbaijani', 'native_name' => 'AzəRbaycan Dili', 'flag' => '🇦🇿', 'rtl' => true ],
                'az_Cyrl' => [ 'label' => 'Azerbaijani (Cyrillic)', 'native_name' => 'Азәрбајҹан Дили (Kiril)', 'flag' => '🇷🇺', 'rtl' => false ],
                'az_Cyrl_AZ' => [ 'label' => 'Azerbaijani (Cyrillic, Azerbaijan)', 'native_name' => 'Азәрбајҹан Дили (Kiril)', 'flag' => '🇦🇿', 'rtl' => false ],
                'az_Latn' => [ 'label' => 'Azerbaijani (Latin)', 'native_name' => 'AzəRbaycan (Latın) (Latın Dili)', 'flag' => '🇦🇿', 'rtl' => false ],
                'az_Latn_AZ' => [ 'label' => 'Azerbaijani (Latin, Azerbaijan)', 'native_name' => 'AzəRbaycan (Latın, AzəRbaycan) ()', 'flag' => '🇦🇿', 'rtl' => false ],
                'be' => [ 'label' => 'Belarusian', 'native_name' => 'Беларуская Мова', 'flag' => '🇧🇾', 'rtl' => false ],
                'be_BY' => [ 'label' => 'Belarusian (Belarus)', 'native_name' => 'Беларуская (Беларусь) (Беларус)', 'flag' => '🇧🇾', 'rtl' => false ],
                'bem' => [ 'label' => 'Bemba', 'native_name' => 'Βemba', 'flag' => '🇿🇲', 'rtl' => false ],
                'bem_ZM' => [ 'label' => 'Bemba (Zambia)', 'native_name' => 'Βemba (Zambia)', 'flag' => '🇿🇲', 'rtl' => false ],
                'bez' => [ 'label' => 'Bena', 'native_name' => 'Ekibena', 'flag' => '🇹🇿', 'rtl' => false ],
                'bez_TZ' => [ 'label' => 'Bena (Tanzania)', 'native_name' => 'Ekibena (Tanzania)', 'flag' => '🇹🇿', 'rtl' => false ],
                'bg' => [ 'label' => 'Bulgarian', 'native_name' => 'Български', 'flag' => '🇧🇬', 'rtl' => false ],
                'bg_BG' => [ 'label' => 'Bulgarian (Bulgaria)', 'native_name' => 'Български (България)', 'flag' => '🇧🇬', 'rtl' => false ],
                'bm' => [ 'label' => 'Bambara', 'native_name' => 'Bamanankan', 'flag' => '🇲🇱', 'rtl' => false ],
                'bm_ML' => [ 'label' => 'Bambara (Mali)', 'native_name' => 'Bamanankan (Mali)', 'flag' => '🇲🇱', 'rtl' => false ],
                'bn' => [ 'label' => 'Bengali', 'native_name' => 'বাংলা, Bangla', 'flag' => '🇧🇩', 'rtl' => false ],
                'bn_BD' => [ 'label' => 'Bengali (Bangladesh)', 'native_name' => 'বাংলা, Bangla (বাংলাদেশ)', 'flag' => '🇧🇩', 'rtl' => false ],
                'bn_IN' => [ 'label' => 'Bengali (India)', 'native_name' => 'বাংলা Bānlā (ভারত)', 'flag' => '🇮🇳', 'rtl' => false ],
                'bo' => [ 'label' => 'Tibetan', 'native_name' => 'བོད་སྐད་', 'flag' => '🏳️', 'rtl' => false ],
                'bo_CN' => [ 'label' => 'Tibetan (China)', 'native_name' => 'བོད་སྐད (China)', 'flag' => '🇨🇳', 'rtl' => false ],
                'bo_IN' => [ 'label' => 'Tibetan (India)', 'native_name' => 'བོད་སྐད་ (India)', 'flag' => '🇮🇳', 'rtl' => false ],
                'bs' => [ 'label' => 'Bosnian', 'native_name' => 'Bosanski', 'flag' => '🇧🇦', 'rtl' => false ],
                'bs_BA' => [ 'label' => 'Bosnian (Bosnia and Herzegovina)', 'native_name' => 'Bosanski (Bosna I Hercegovina)', 'flag' => '🇧🇦', 'rtl' => false ],
                'ca' => [ 'label' => 'Catalan', 'native_name' => 'Català', 'flag' => '🇪🇸', 'rtl' => false ],
                'ca_ES' => [ 'label' => 'Catalan (Spain)', 'native_name' => 'Català (Espanyola)', 'flag' => '🇪🇸', 'rtl' => false ],
                'cgg' => [ 'label' => 'Chiga', 'native_name' => 'Orukiga', 'flag' => '🇺🇬', 'rtl' => false ],
                'cgg_UG' => [ 'label' => 'Chiga (Uganda)', 'native_name' => 'Orukiga (Uganda)', 'flag' => '🇺🇬', 'rtl' => false ],
                'chr' => [ 'label' => 'Cherokee', 'native_name' => 'ᏣᎳᎩ ᎦᏬᏂᎯᏍᏗ', 'flag' => '🇺🇸', 'rtl' => false ],
                'chr_US' => [ 'label' => 'Cherokee (United States)', 'native_name' => 'ᏣᎳᎩ ᎦᏬᏂᎯᏍᏗ (United States)', 'flag' => '🇺🇸', 'rtl' => false ],
                'ckb_IR' => [ 'label' => 'Sorani (Iran)', 'native_name' => 'سۆرانی', 'flag' => '🇮🇷', 'rtl' => true, ],
                'ckb_IQ' => [ 'label' => 'Sorani (Iraq)', 'native_name' => 'سۆرانی', 'flag' => '🇮🇶', 'rtl' => true, ],
                'cs' => [ 'label' => 'Czech', 'native_name' => 'Český Jazyk', 'flag' => '🇨🇿', 'rtl' => false ],
                'cs_CZ' => [ 'label' => 'Czech (Czech Republic)', 'native_name' => 'Čeština (Česká Republika)', 'flag' => '🇨🇿', 'rtl' => false ],
                'cy' => [ 'label' => 'Welsh', 'native_name' => 'Gymraeg', 'flag' => '🏴󠁧󠁢󠁥󠁮󠁧󠁿', 'rtl' => false ],
                'cy_GB' => [ 'label' => 'Welsh (United Kingdom)', 'native_name' => 'Gymraeg (Y Deyrnas Unedig)', 'flag' => '🇬🇧', 'rtl' => false ],
                'da' => [ 'label' => 'Danish', 'native_name' => 'Dansk', 'flag' => '🇩🇰', 'rtl' => false ],
                'da_DK' => [ 'label' => 'Danish (Denmark)', 'native_name' => 'Dansk (Danmark)', 'flag' => '🇩🇰', 'rtl' => false ],
                'dav' => [ 'label' => 'Taita', 'native_name' => 'Taita', 'flag' => '🇰🇪', 'rtl' => false ],
                'dav_KE' => [ 'label' => 'Taita (Kenya)', 'native_name' => 'Taita (Kenya)', 'flag' => '🇰🇪', 'rtl' => false ],
                'de' => [ 'label' => 'German', 'native_name' => 'Deutsch', 'flag' => '🇩🇪', 'rtl' => false ],
                'de_AT' => [ 'label' => 'German (Austria)', 'native_name' => 'Österreichisches (Österreich)', 'flag' => '🇦🇹', 'rtl' => false ],
                'de_BE' => [ 'label' => 'German (Belgium)', 'native_name' => 'Deutschsprachige (Belgien)', 'flag' => '🇧🇪', 'rtl' => false ],
                'de_CH' => [ 'label' => 'German (Switzerland)', 'native_name' => 'Schwiizerdütsch (Schweiz)', 'flag' => '🇨🇭', 'rtl' => false ],
                'de_DE' => [ 'label' => 'German (Germany)', 'native_name' => 'Deutsch (Deutschland)', 'flag' => '🇩🇪', 'rtl' => false ],
                'de_LI' => [ 'label' => 'German (Liechtenstein)', 'native_name' => 'Alemannisch (Liechtenstein)', 'flag' => '🇱🇮', 'rtl' => false ],
                'de_LU' => [ 'label' => 'German (Luxembourg)', 'native_name' => 'Lëtzebuergesch (Luxemburg)', 'flag' => '🇱🇺', 'rtl' => false ],
                'ebu' => [ 'label' => 'Embu', 'native_name' => 'Kiembu', 'flag' => '🇰🇪', 'rtl' => false ],
                'ebu_KE' => [ 'label' => 'Embu (Kenya)', 'native_name' => 'Kiembu (Kenya)', 'flag' => '🇰🇪', 'rtl' => false ],
                'ee' => [ 'label' => 'Ewe', 'native_name' => 'EʋEgbe', 'flag' => '🇹🇬', 'rtl' => false ],
                'ee_GH' => [ 'label' => 'Ewe (Ghana)', 'native_name' => 'EʋEgbe (Ghana)', 'flag' => '🇬🇭', 'rtl' => false ],
                'ee_TG' => [ 'label' => 'Ewe (Togo)', 'native_name' => 'EʋEgbe (Togo)', 'flag' => '🇹🇬', 'rtl' => false ],
                'el' => [ 'label' => 'Greek', 'native_name' => 'Ελληνικά', 'flag' => '🇬🇷', 'rtl' => false ],
                'el_CY' => [ 'label' => 'Greek (Cyprus)', 'native_name' => 'Ελληνοκύπριοι (Κύπρος)', 'flag' => '🇨🇾', 'rtl' => false ],
                'el_GR' => [ 'label' => 'Greek (Greece)', 'native_name' => 'Ελληνικά (Ελλάδα) (Ελλάδα)', 'flag' => '🇬🇷', 'rtl' => false ],
                'en' => [ 'label' => 'English', 'native_name' => 'English', 'flag' => '🇺🇸', 'rtl' => false ],
                'en_AS' => [ 'label' => 'English (American Samoa)', 'native_name' => 'English (American Samoa)', 'flag' => '🇦🇸', 'rtl' => false ],
                'en_AU' => [ 'label' => 'English (Australia)', 'native_name' => 'English (Australia)', 'flag' => '🇦🇺', 'rtl' => false ],
                'en_BE' => [ 'label' => 'English (Belgium)', 'native_name' => 'English (Belgium)', 'flag' => '🇧🇪', 'rtl' => false ],
                'en_BW' => [ 'label' => 'English (Botswana)', 'native_name' => 'English (Botswana)', 'flag' => '🇧🇼', 'rtl' => false ],
                'en_BZ' => [ 'label' => 'English (Belize)', 'native_name' => 'English (Belize)', 'flag' => '🇧🇿', 'rtl' => false ],
                'en_CA' => [ 'label' => 'English (Canada)', 'native_name' => 'English (Canada)', 'flag' => '🇨🇦', 'rtl' => false ],
                'en_GB' => [ 'label' => 'English (United Kingdom)', 'native_name' => 'English (United Kingdom)', 'flag' => '🇬🇧', 'rtl' => false ],
                'en_GU' => [ 'label' => 'English (Guam)', 'native_name' => 'English (Guam)', 'flag' => '🇬🇺', 'rtl' => false ],
                'en_HK' => [ 'label' => 'English (Hong Kong SAR China)', 'native_name' => 'English (Hong Kong Sar China)', 'flag' => '🇭🇰', 'rtl' => false ],
                'en_IE' => [ 'label' => 'English (Ireland)', 'native_name' => 'English (Ireland)', 'flag' => '🇮🇪', 'rtl' => false ],
                'en_IL' => [ 'label' => 'English (Israel)', 'native_name' => 'English (Israel)', 'flag' => '🇮🇱', 'rtl' => false ],
                'en_IN' => [ 'label' => 'English (India)', 'native_name' => 'English (India)', 'flag' => '🇮🇳', 'rtl' => false ],
                'en_JM' => [ 'label' => 'English (Jamaica)', 'native_name' => 'English (Jamaica)', 'flag' => '🇯🇲', 'rtl' => false ],
                'en_MH' => [ 'label' => 'English (Marshall Islands)', 'native_name' => 'English (Marshall Islands)', 'flag' => '🇲🇭', 'rtl' => false ],
                'en_MP' => [ 'label' => 'English (Northern Mariana Islands)', 'native_name' => 'English (Northern Mariana Islands)', 'flag' => '🇲🇵', 'rtl' => false ],
                'en_MT' => [ 'label' => 'English (Malta)', 'native_name' => 'English (Malta)', 'flag' => '🇲🇹', 'rtl' => false ],
                'en_MU' => [ 'label' => 'English (Mauritius)', 'native_name' => 'English (Mauritius)', 'flag' => '🇲🇺', 'rtl' => false ],
                'en_NA' => [ 'label' => 'English (Namibia)', 'native_name' => 'English (Namibia)', 'flag' => '🇳🇦', 'rtl' => false ],
                'en_NZ' => [ 'label' => 'English (New Zealand)', 'native_name' => 'English (New Zealand)', 'flag' => '🇳🇿', 'rtl' => false ],
                'en_PH' => [ 'label' => 'English (Philippines)', 'native_name' => 'English (Philippines)', 'flag' => '🇵🇭', 'rtl' => false ],
                'en_PK' => [ 'label' => 'English (Pakistan)', 'native_name' => 'English (Pakistan)', 'flag' => '🇵🇰', 'rtl' => false ],
                'en_SG' => [ 'label' => 'English (Singapore)', 'native_name' => 'English (Singapore)', 'flag' => '🇸🇬', 'rtl' => false ],
                'en_TT' => [ 'label' => 'English (Trinidad and Tobago)', 'native_name' => 'English (Trinidad And Tobago)', 'flag' => '🇹🇹', 'rtl' => false ],
                'en_UM' => [ 'label' => 'English (U.S. Minor Outlying Islands)', 'native_name' => 'English (U.S. Minor Outlying Islands)', 'flag' => '🇺🇸', 'rtl' => false ],
                'en_US' => [ 'label' => 'English (United States)', 'native_name' => 'English (United States)', 'flag' => '🇺🇸', 'rtl' => false ],
                'en_VI' => [ 'label' => 'English (U.S. Virgin Islands)', 'native_name' => 'English (U.S. Virgin Islands)', 'flag' => '🇻🇮', 'rtl' => false ],
                'en_ZA' => [ 'label' => 'English (South Africa)', 'native_name' => 'English (South Africa)', 'flag' => '🇿🇦', 'rtl' => false ],
                'en_ZW' => [ 'label' => 'English (Zimbabwe)', 'native_name' => 'English (Zimbabwe)', 'flag' => '🇿🇼', 'rtl' => false ],
                'eo' => [ 'label' => 'Esperanto', 'native_name' => 'Esperanto', 'flag' => '🇪🇺', 'rtl' => false ],
                'es' => [ 'label' => 'Spanish', 'native_name' => 'Español', 'flag' => '🇪🇸', 'rtl' => false ],
                'es_419' => [ 'label' => 'Spanish (Latin America)', 'native_name' => 'Español (America Latina)', 'flag' => '🇨🇴', 'rtl' => false ],
                'es_AR' => [ 'label' => 'Spanish (Argentina)', 'native_name' => 'Español (Argentina)', 'flag' => '🇦🇷', 'rtl' => false ],
                'es_BO' => [ 'label' => 'Spanish (Bolivia)', 'native_name' => 'Español (Bolivia)', 'flag' => '🇧🇴', 'rtl' => false ],
                'es_CL' => [ 'label' => 'Spanish (Chile)', 'native_name' => 'Español (Chile)', 'flag' => '🇨🇱', 'rtl' => false ],
                'es_CO' => [ 'label' => 'Spanish (Colombia)', 'native_name' => 'Español (Colombia)', 'flag' => '🇨🇴', 'rtl' => false ],
                'es_CR' => [ 'label' => 'Spanish (Costa Rica)', 'native_name' => 'Español (Costa Rica)', 'flag' => '🇨🇷', 'rtl' => false ],
                'es_DO' => [ 'label' => 'Spanish (Dominican Republic)', 'native_name' => 'Español (República Dominicana)', 'flag' => '🇩🇴', 'rtl' => false ],
                'es_EC' => [ 'label' => 'Spanish (Ecuador)', 'native_name' => 'Español (Ecuador)', 'flag' => '🇪🇨', 'rtl' => false ],
                'es_ES' => [ 'label' => 'Spanish (Spain)', 'native_name' => 'Español (España)', 'flag' => '🇪🇸', 'rtl' => false ],
                'es_GQ' => [ 'label' => 'Spanish (Equatorial Guinea)', 'native_name' => 'Español (Guinea Ecuatorial)', 'flag' => '🇬🇶', 'rtl' => false ],
                'es_GT' => [ 'label' => 'Spanish (Guatemala)', 'native_name' => 'Español (Guatemala)', 'flag' => '🇬🇹', 'rtl' => false ],
                'es_HN' => [ 'label' => 'Spanish (Honduras)', 'native_name' => 'Español (Honduras)', 'flag' => '🇭🇳', 'rtl' => false ],
                'es_MX' => [ 'label' => 'Spanish (Mexico)', 'native_name' => 'Español (México)', 'flag' => '🇲🇽', 'rtl' => false ],
                'es_NI' => [ 'label' => 'Spanish (Nicaragua)', 'native_name' => 'Español (Nicaragua)', 'flag' => '🇳🇮', 'rtl' => false ],
                'es_PA' => [ 'label' => 'Spanish (Panama)', 'native_name' => 'Español (Panamá)', 'flag' => '🇵🇦', 'rtl' => false ],
                'es_PE' => [ 'label' => 'Spanish (Peru)', 'native_name' => 'Español (Perú)', 'flag' => '🇵🇪', 'rtl' => false ],
                'es_PR' => [ 'label' => 'Spanish (Puerto Rico)', 'native_name' => 'Español (Puerto Rico)', 'flag' => '🇵🇷', 'rtl' => false ],
                'es_PY' => [ 'label' => 'Spanish (Paraguay)', 'native_name' => 'Español (Paraguay)', 'flag' => '🇵🇾', 'rtl' => false ],
                'es_SV' => [ 'label' => 'Spanish (El Salvador)', 'native_name' => 'Español (El Salvador)', 'flag' => '🇸🇻', 'rtl' => false ],
                'es_US' => [ 'label' => 'Spanish (United States)', 'native_name' => 'Español (Estados Unidos)', 'flag' => '🇺🇸', 'rtl' => false ],
                'es_UY' => [ 'label' => 'Spanish (Uruguay)', 'native_name' => 'Español (Uruguay)', 'flag' => '🇺🇾', 'rtl' => false ],
                'es_VE' => [ 'label' => 'Spanish (Venezuela)', 'native_name' => 'Español (Venezuela)', 'flag' => '🇻🇪', 'rtl' => false ],
                'et' => [ 'label' => 'Estonian', 'native_name' => 'Eesti Keel', 'flag' => '🇪🇪', 'rtl' => false ],
                'et_EE' => [ 'label' => 'Estonian (Estonia)', 'native_name' => 'Eesti Keel (Eesti)', 'flag' => '🇪🇪', 'rtl' => false ],
                'eu' => [ 'label' => 'Basque', 'native_name' => 'Euskara', 'flag' => '🏳️', 'rtl' => false ],
                'eu_ES' => [ 'label' => 'Basque (Spain)', 'native_name' => 'Euskara (Jaio)', 'flag' => '🏳️', 'rtl' => false ],
                'fa' => [ 'label' => 'Persian', 'native_name' => 'فارسی (Fārsi)', 'flag' => '🇮🇷', 'rtl' => true ],
                'fa_AF' => [ 'label' => 'Persian (Afghanistan)', 'native_name' => 'فارسی دری (افغانستان)', 'flag' => '🇦🇫', 'rtl' => true ],
                'fa_IR' => [ 'label' => 'Persian (Iran)', 'native_name' => 'فارسی (Fārsi) (ایران)', 'flag' => '🇮🇷', 'rtl' => true ],
                'ff' => [ 'label' => 'Fulah', 'native_name' => 'الفولاني', 'flag' => '🇸🇳', 'rtl' => true ],
                'ff_SN' => [ 'label' => 'Fulah (Senegal)', 'native_name' => '𞤆𞤵𞥄𞤼𞤢', 'flag' => '🇸🇳', 'rtl' => true ],
                'fi' => [ 'label' => 'Finnish', 'native_name' => 'Suomen Kieli', 'flag' => '🇫🇮', 'rtl' => false ],
                'fi_FI' => [ 'label' => 'Finnish (Finland)', 'native_name' => 'Suomen Kieli (Suomi)', 'flag' => '🇫🇮', 'rtl' => false ],
                'fil' => [ 'label' => 'Filipino', 'native_name' => 'Wikang Filipino', 'flag' => '🇵🇭', 'rtl' => false ],
                'fil_PH' => [ 'label' => 'Filipino (Philippines)', 'native_name' => 'Wikang Filipino (Pilipinas)', 'flag' => '🇵🇭', 'rtl' => false ],
                'fo' => [ 'label' => 'Faroese', 'native_name' => 'Føroyskt Mál', 'flag' => '🇫🇴', 'rtl' => false ],
                'fo_FO' => [ 'label' => 'Faroese (Faroe Islands)', 'native_name' => 'Føroyskt Mál (Faroe Islands)', 'flag' => '🇫🇴', 'rtl' => false ],
                'fr' => [ 'label' => 'French', 'native_name' => 'Français', 'flag' => '🇫🇷', 'rtl' => false ],
                'fr_BE' => [ 'label' => 'French (Belgium)', 'native_name' => 'Français (Belgique)', 'flag' => '🇧🇪', 'rtl' => false ],
                'fr_BF' => [ 'label' => 'French (Burkina Faso)', 'native_name' => 'Français (Burkina Faso)', 'flag' => '🇧🇫', 'rtl' => false ],
                'fr_BI' => [ 'label' => 'French (Burundi)', 'native_name' => 'Français (Burundi)', 'flag' => '🇧🇮', 'rtl' => false ],
                'fr_BJ' => [ 'label' => 'French (Benin)', 'native_name' => 'Français (Bénin)', 'flag' => '🇧🇯', 'rtl' => false ],
                'fr_BL' => [ 'label' => 'French (Saint Barthélemy)', 'native_name' => 'Français (Saint Barthélemy)', 'flag' => '🇧🇱', 'rtl' => false ],
                'fr_CA' => [ 'label' => 'French (Canada)', 'native_name' => 'Français (Canada)', 'flag' => '🇨🇦', 'rtl' => false ],
                'fr_CD' => [ 'label' => 'French (Congo - Kinshasa)', 'native_name' => 'Français (Congo - Kinshasa)', 'flag' => '🇨🇩', 'rtl' => false ],
                'fr_CF' => [ 'label' => 'French (Central African Republic)', 'native_name' => 'Français (République Centrafricaine)', 'flag' => '🇨🇫', 'rtl' => false ],
                'fr_CG' => [ 'label' => 'French (Congo - Brazzaville)', 'native_name' => 'Français (Congo - Brazzaville)', 'flag' => '🇨🇬', 'rtl' => false ],
                'fr_CH' => [ 'label' => 'French (Switzerland)', 'native_name' => 'Français (Suisse)', 'flag' => '🇨🇭', 'rtl' => false ],
                'fr_CI' => [ 'label' => "French (Côte d'Ivoire)", 'native_name' => "Français (Côte D'Ivoire)", 'flag' => '🇨🇮', 'rtl' => false ],
                'fr_CM' => [ 'label' => 'French (Cameroon)', 'native_name' => 'Français (Cameroun)', 'flag' => '🇨🇲', 'rtl' => false ],
                'fr_DJ' => [ 'label' => 'French (Djibouti)', 'native_name' => 'Français (Djibouti)', 'flag' => '🇩🇯', 'rtl' => false ],
                'fr_FR' => [ 'label' => 'French (France)', 'native_name' => 'Français (France)', 'flag' => '🇫🇷', 'rtl' => false ],
                'fr_GA' => [ 'label' => 'French (Gabon)', 'native_name' => 'Français (Gabon)', 'flag' => '🇬🇦', 'rtl' => false ],
                'fr_GN' => [ 'label' => 'French (Guinea)', 'native_name' => 'Français (Guinée)', 'flag' => '🇬🇳', 'rtl' => false ],
                'fr_GP' => [ 'label' => 'French (Guadeloupe)', 'native_name' => 'Français (Guadeloup)', 'flag' => '🇬🇵', 'rtl' => false ],
                'fr_GQ' => [ 'label' => 'French (Equatorial Guinea)', 'native_name' => 'Français (Guinée Équatoriale)', 'flag' => '🇬🇶', 'rtl' => false ],
                'fr_KM' => [ 'label' => 'French (Comoros)', 'native_name' => 'Français (Comores)', 'flag' => '🇰🇲', 'rtl' => false ],
                'fr_LU' => [ 'label' => 'French (Luxembourg)', 'native_name' => 'Français (Luxembourg)', 'flag' => '🇱🇺', 'rtl' => false ],
                'fr_MC' => [ 'label' => 'French (Monaco)', 'native_name' => 'Français (Monaco)', 'flag' => '🇲🇨', 'rtl' => false ],
                'fr_MF' => [ 'label' => 'French (Saint Martin)', 'native_name' => 'Français (Saint Martin)', 'flag' => '🇲🇫', 'rtl' => false ],
                'fr_MG' => [ 'label' => 'French (Madagascar)', 'native_name' => 'Français (Madagascar)', 'flag' => '🇲🇬', 'rtl' => false ],
                'fr_ML' => [ 'label' => 'French (Mali)', 'native_name' => 'Français (Mali)', 'flag' => '🇲🇱', 'rtl' => false ],
                'fr_MQ' => [ 'label' => 'French (Martinique)', 'native_name' => 'Français (Martinique)', 'flag' => '🇲🇶', 'rtl' => false ],
                'fr_NE' => [ 'label' => 'French (Niger)', 'native_name' => 'Français (Niger)', 'flag' => '🇳🇪', 'rtl' => false ],
                'fr_RE' => [ 'label' => 'French (Réunion)', 'native_name' => 'Français (Réunion)', 'flag' => '🇷🇪', 'rtl' => false ],
                'fr_RW' => [ 'label' => 'French (Rwanda)', 'native_name' => 'Français (Rwanda)', 'flag' => '🇷🇼', 'rtl' => false ],
                'fr_SN' => [ 'label' => 'French (Senegal)', 'native_name' => 'Français (Sénégal)', 'flag' => '🇸🇳', 'rtl' => false ],
                'fr_TD' => [ 'label' => 'French (Chad)', 'native_name' => 'Français (Tchad)', 'flag' => '🇹🇩', 'rtl' => false ],
                'fr_TG' => [ 'label' => 'French (Togo)', 'native_name' => 'Français (Aller)', 'flag' => '🇹🇬', 'rtl' => false ],
                'ga' => [ 'label' => 'Irish', 'native_name' => 'Gaeilge', 'flag' => '🇮🇪', 'rtl' => false ],
                'ga_IE' => [ 'label' => 'Irish (Ireland)', 'native_name' => 'Gaeilge (Éireann)', 'flag' => '🇮🇪', 'rtl' => false ],
                'gl' => [ 'label' => 'Galician', 'native_name' => 'Galego', 'flag' => '🇪🇸', 'rtl' => false ],
                'gl_ES' => [ 'label' => 'Galician (Spain)', 'native_name' => 'Galego (España)', 'flag' => '🇪🇸', 'rtl' => false ],
                'gsw' => [ 'label' => 'Swiss German', 'native_name' => 'Schwiizerdütsch', 'flag' => '🇨🇭', 'rtl' => false ],
                'gsw_CH' => [ 'label' => 'Swiss German (Switzerland)', 'native_name' => 'Schwiizerdütsch', 'flag' => '🇨🇭', 'rtl' => false ],
                'gu' => [ 'label' => 'Gujarati', 'native_name' => 'ગુજરાતી', 'flag' => '🇮🇳', 'rtl' => false ],
                'gu_IN' => [ 'label' => 'Gujarati (India)', 'native_name' => 'ગુજરાતી (ભારત)', 'flag' => '🇮🇳', 'rtl' => false ],
                'guz' => [ 'label' => 'Gusii', 'native_name' => 'Ekegusii', 'flag' => '🇰🇪', 'rtl' => false ],
                'guz_KE' => [ 'label' => 'Gusii (Kenya)', 'native_name' => 'Ekegusii (Kenya)', 'flag' => '🇰🇪', 'rtl' => false ],
                'gv' => [ 'label' => 'Manx', 'native_name' => 'Gaelg, Gailck', 'flag' => '🇮🇲', 'rtl' => false ],
                'gv_GB' => [ 'label' => 'Manx (United Kingdom)', 'native_name' => 'Gaelg, Gailck (United Kingdom)', 'flag' => '🇬🇧', 'rtl' => false ],
                'ha' => [ 'label' => 'Hausa', 'native_name' => 'هَرْشَن هَوْسَ', 'flag' => '🇳🇬', 'rtl' => true ],
                'ha_Latn' => [ 'label' => 'Hausa (Latin)', 'native_name' => 'Halshen Hausa (Na Latin)', 'flag' => '🇳🇬', 'rtl' => false ],
                'ha_Latn_GH' => [ 'label' => 'Hausa (Latin, Ghana)', 'native_name' => 'Halshen Hausa (Latin Ghana)', 'flag' => '🇬🇭', 'rtl' => false ],
                'ha_Latn_NE' => [ 'label' => 'Hausa (Latin, Niger)', 'native_name' => 'Halshen Hausa (Latin Niger)', 'flag' => '🇳🇪', 'rtl' => false ],
                'ha_Latn_NG' => [ 'label' => 'Hausa (Latin, Nigeria)', 'native_name' => 'Halshen Hausa (Latin Nigeria)', 'flag' => '🇳🇬', 'rtl' => false ],
                'haw' => [ 'label' => 'Hawaiian', 'native_name' => 'ʻŌlelo HawaiʻI', 'flag' => '🇺🇸', 'rtl' => false ],
                'haw_US' => [ 'label' => 'Hawaiian (United States)', 'native_name' => 'ʻŌlelo HawaiʻI (ʻAmelika Hui Pū ʻIa)', 'flag' => '🇺🇸', 'rtl' => false ],
                'he' => [ 'label' => 'Hebrew', 'native_name' => 'עִבְרִית', 'flag' => '🇮🇱', 'rtl' => true ],
                'he_IL' => [ 'label' => 'Hebrew (Israel)', 'native_name' => 'עברית (ישראל)', 'flag' => '🇮🇱', 'rtl' => true ],
                'hi' => [ 'label' => 'Hindi', 'native_name' => 'हिन्दी', 'flag' => '🇮🇳', 'rtl' => false ],
                'hi_IN' => [ 'label' => 'Hindi (India)', 'native_name' => 'हिन्दी (भारत)', 'flag' => '🇮🇳', 'rtl' => false ],
                'hr' => [ 'label' => 'Croatian', 'native_name' => 'Hrvatski', 'flag' => '🇭🇷', 'rtl' => false ],
                'hr_HR' => [ 'label' => 'Croatian (Croatia)', 'native_name' => 'Hrvatski (Hrvatska)', 'flag' => '🇭🇷', 'rtl' => false ],
                'hu' => [ 'label' => 'Hungarian', 'native_name' => 'Magyar Nyelv', 'flag' => '🇭🇺', 'rtl' => false ],
                'hu_HU' => [ 'label' => 'Hungarian (Hungary)', 'native_name' => 'Magyar Nyelv (Magyarország)', 'flag' => '🇭🇺', 'rtl' => false ],
                'hy' => [ 'label' => 'Armenian', 'native_name' => 'Հայերէն/Հայերեն', 'flag' => '🇦🇲', 'rtl' => false ],
                'hy_AM' => [ 'label' => 'Armenian (Armenia)', 'native_name' => 'Հայերէն/Հայերեն (Հայաստան)', 'flag' => '🇦🇲', 'rtl' => false ],
                'id' => [ 'label' => 'Indonesian', 'native_name' => 'Bahasa Indonesia', 'flag' => '🇮🇩', 'rtl' => false ],
                'id_ID' => [ 'label' => 'Indonesian (Indonesia)', 'native_name' => 'Bahasa Indonesia (Indonesia)', 'flag' => '🇮🇩', 'rtl' => false ],
                'ig' => [ 'label' => 'Igbo', 'native_name' => 'Ásụ̀Sụ́ Ìgbò', 'flag' => '🇳🇬', 'rtl' => false ],
                'ig_NG' => [ 'label' => 'Igbo (Nigeria)', 'native_name' => 'Ásụ̀Sụ́ Ìgbò (Nigeria)', 'flag' => '🇳🇬', 'rtl' => false ],
                'ii' => [ 'label' => 'Sichuan Yi', 'native_name' => 'ꆈꌠꉙ', 'flag' => '🇨🇳', 'rtl' => false ],
                'ii_CN' => [ 'label' => 'Sichuan Yi (China)', 'native_name' => 'ꆈꌠꉙ (China)', 'flag' => '🇨🇳', 'rtl' => false ],
                'is' => [ 'label' => 'Icelandic', 'native_name' => 'Íslenska', 'flag' => '🇮🇸', 'rtl' => false ],
                'is_IS' => [ 'label' => 'Icelandic (Iceland)', 'native_name' => 'Íslenska (Ísland)', 'flag' => '🇮🇸', 'rtl' => false ],
                'it' => [ 'label' => 'Italian', 'native_name' => 'Italiano', 'flag' => '🇮🇹', 'rtl' => false ],
                'it_CH' => [ 'label' => 'Italian (Switzerland)', 'native_name' => 'Italiano (Svizzera)', 'flag' => '🇨🇭', 'rtl' => false ],
                'it_IT' => [ 'label' => 'Italian (Italy)', 'native_name' => 'Italiano (Italia)', 'flag' => '🇮🇹', 'rtl' => false ],
                'ja' => [ 'label' => 'Japanese', 'native_name' => '日本語', 'flag' => '🇯🇵', 'rtl' => false ],
                'ja_JP' => [ 'label' => 'Japanese (Japan)', 'native_name' => '日本語 (日本)', 'flag' => '🇯🇵', 'rtl' => false ],
                'jmc' => [ 'label' => 'Machame', 'native_name' => 'West Chaga', 'flag' => '🇹🇿', 'rtl' => false ],
                'jmc_TZ' => [ 'label' => 'Machame (Tanzania)', 'native_name' => 'West Chaga (Tanzania)', 'flag' => '🇹🇿', 'rtl' => false ],
                'ka' => [ 'label' => 'Georgian', 'native_name' => 'ᲥᲐᲠᲗᲣᲚᲘ ᲔᲜᲐ', 'flag' => '🇬🇪', 'rtl' => false ],
                'ka_GE' => [ 'label' => 'Georgian (Georgia)', 'native_name' => 'ᲥᲐᲠᲗᲣᲚᲘ ᲔᲜᲐ (ᲡᲐᲥᲐᲠᲗᲕᲔᲚᲝ)', 'flag' => '🇬🇪', 'rtl' => false ],
                'kab' => [ 'label' => 'Kabyle', 'native_name' => 'ⵜⴰⵇⴱⴰⵢⵍⵉⵜ', 'flag' => '🇩🇿', 'rtl' => false ],
                'kab_DZ' => [ 'label' => 'Kabyle (Algeria)', 'native_name' => 'ⵜⴰⵇⴱⴰⵢⵍⵉⵜ (Algeria)', 'flag' => '🇩🇿', 'rtl' => false ],
                'kam' => [ 'label' => 'Kamba', 'native_name' => 'Kikamba', 'flag' => '🇰🇪', 'rtl' => false ],
                'kam_KE' => [ 'label' => 'Kamba (Kenya)', 'native_name' => 'Kikamba (Kenya)', 'flag' => '🇰🇪', 'rtl' => false ],
                'kde' => [ 'label' => 'Makonde', 'native_name' => 'Chi(Ni)Makonde', 'flag' => '🇹🇿', 'rtl' => false ],
                'kde_TZ' => [ 'label' => 'Makonde (Tanzania)', 'native_name' => 'Chi(Ni)Makonde (Tanzania)', 'flag' => '🇹🇿', 'rtl' => false ],
                'kea' => [ 'label' => 'Kabuverdianu', 'native_name' => 'Kriolu, Kriol', 'flag' => '🇨🇻', 'rtl' => false ],
                'kea_CV' => [ 'label' => 'Kabuverdianu (Cape Verde)', 'native_name' => 'Kriolu, Kriol (Cape Verde)', 'flag' => '🇨🇻', 'rtl' => false ],
                'khq' => [ 'label' => 'Koyra Chiini', 'native_name' => 'Koyra Chiini', 'flag' => '🇲🇱', 'rtl' => false ],
                'khq_ML' => [ 'label' => 'Koyra Chiini (Mali)', 'native_name' => 'Koyra Chiini (Mali)', 'flag' => '🇲🇱', 'rtl' => false ],
                'ki' => [ 'label' => 'Kikuyu', 'native_name' => 'Gĩkũyũ', 'flag' => '🇰🇪', 'rtl' => false ],
                'ki_KE' => [ 'label' => 'Kikuyu (Kenya)', 'native_name' => 'Gĩkũyũ (Kenya)', 'flag' => '🇰🇪', 'rtl' => false ],
                'kk' => [ 'label' => 'Kazakh', 'native_name' => 'قازاقشا Or قازاق ٴتىلى', 'flag' => '🇰🇿', 'rtl' => true ],
                'kk_Cyrl' => [ 'label' => 'Kazakh (Cyrillic)', 'native_name' => 'Қазақша Or Қазақ Тілі (Кириллица)', 'flag' => '🇷🇺', 'rtl' => false ],
                'kk_Cyrl_KZ' => [ 'label' => 'Kazakh (Cyrillic, Kazakhstan)', 'native_name' => 'Қазақша Or Қазақ Тілі (Кириллица)', 'flag' => '🇰🇿', 'rtl' => false ],
                'kl' => [ 'label' => 'Kalaallisut', 'native_name' => 'Kalaallisut', 'flag' => '🇬🇱', 'rtl' => false ],
                'kl_GL' => [ 'label' => 'Kalaallisut (Greenland)', 'native_name' => 'Kalaallisut (Greenland)', 'flag' => '🇬🇱', 'rtl' => false ],
                'kln' => [ 'label' => 'Kalenjin', 'native_name' => 'Kalenjin', 'flag' => '🇰🇪', 'rtl' => false ],
                'kln_KE' => [ 'label' => 'Kalenjin (Kenya)', 'native_name' => 'Kalenjin (Kenya)', 'flag' => '🇰🇪', 'rtl' => false ],
                'km' => [ 'label' => 'Khmer', 'native_name' => 'ភាសាខ្មែរ', 'flag' => '🇰🇭', 'rtl' => false ],
                'km_KH' => [ 'label' => 'Khmer (Cambodia)', 'native_name' => 'ភាសាខ្មែរ (ខេសកម្ពុជា)', 'flag' => '🇰🇭', 'rtl' => false ],
                'kn' => [ 'label' => 'Kannada', 'native_name' => 'ಕನ್ನಡ', 'flag' => '🇮🇳', 'rtl' => false ],
                'kn_IN' => [ 'label' => 'Kannada (India)', 'native_name' => 'ಕನ್ನಡ (ಭಾರತ)', 'flag' => '🇮🇳', 'rtl' => false ],
                'ko' => [ 'label' => 'Korean', 'native_name' => '한국어', 'flag' => '🇰🇷', 'rtl' => false ],
                'ko_KR' => [ 'label' => 'Korean (South Korea)', 'native_name' => '한국어 (대한민국)', 'flag' => '🇰🇷', 'rtl' => false ],
                'kok' => [ 'label' => 'Konkani', 'native_name' => 'कोंकणी', 'flag' => '🇮🇳', 'rtl' => false ],
                'kok_IN' => [ 'label' => 'Konkani (India)', 'native_name' => 'कोंकणी (India)', 'flag' => '🇮🇳', 'rtl' => false ],
                'ku' => [ 'label' => 'Kurdish (Kurmanji)', 'native_name' => 'کورمانجی', 'flag' => '🏳️', 'rtl' => true, ],
                'kw' => [ 'label' => 'Cornish', 'native_name' => 'Kernewek, Kernowek', 'flag' => '🇬🇧', 'rtl' => false ],
                'kw_GB' => [ 'label' => 'Cornish (United Kingdom)', 'native_name' => 'Kernewek, Kernowek (United Kingdom)', 'flag' => '🇬🇧', 'rtl' => false ],
                'lag' => [ 'label' => 'Langi', 'native_name' => 'Lëblaŋo', 'flag' => '🇺🇬', 'rtl' => false ],
                'lag_TZ' => [ 'label' => 'Langi (Tanzania)', 'native_name' => 'Kilaangi (Tanzania)', 'flag' => '🇹🇿', 'rtl' => false ],
                'lg' => [ 'label' => 'Ganda', 'native_name' => 'Ganda', 'flag' => '🇺🇬', 'rtl' => false ],
                'lg_UG' => [ 'label' => 'Ganda (Uganda)', 'native_name' => 'Ganda (Uganda)', 'flag' => '🇺🇬', 'rtl' => false ],
                'lki_IR' => [ 'label' => 'Laki (Iran)', 'native_name' => 'له‌کی', 'flag' => '🇮🇷', 'rtl' => true, ],
                'lki_TR' => [ 'label' => 'Laki (Turkey)', 'native_name' => 'له‌کی', 'flag' => '🇹🇷', 'rtl' => true, ],
                'lt' => [ 'label' => 'Lithuanian', 'native_name' => 'Lietuvių Kalba', 'flag' => '🇱🇹', 'rtl' => false ],
                'lt_LT' => [ 'label' => 'Lithuanian (Lithuania)', 'native_name' => 'Lietuvių Kalba (Lietuva)', 'flag' => '🇱🇹', 'rtl' => false ],
                'luo' => [ 'label' => 'Luo', 'native_name' => 'Lwo', 'flag' => '🇰🇪', 'rtl' => false ],
                'luo_KE' => [ 'label' => 'Luo (Kenya)', 'native_name' => 'Dholuo (Kenya)', 'flag' => '🇰🇪', 'rtl' => false ],
                'luy' => [ 'label' => 'Luyia', 'native_name' => 'Oluluhya', 'flag' => '🇰🇪', 'rtl' => false ],
                'luy_KE' => [ 'label' => 'Luyia (Kenya)', 'native_name' => 'Oluluhya (Kenya)', 'flag' => '🇰🇪', 'rtl' => false ],
                'lv' => [ 'label' => 'Latvian', 'native_name' => 'Latviešu Valoda', 'flag' => '🇱🇻', 'rtl' => false ],
                'lv_LV' => [ 'label' => 'Latvian (Latvia)', 'native_name' => 'Latviešu Valoda (Latvija)', 'flag' => '🇱🇻', 'rtl' => false ],
                'mas' => [ 'label' => 'Masai', 'native_name' => 'ƆL Maa', 'flag' => '🇰🇪', 'rtl' => false ],
                'mas_KE' => [ 'label' => 'Masai (Kenya)', 'native_name' => 'ƆL Maa (Kenya)', 'flag' => '🇰🇪', 'rtl' => false ],
                'mas_TZ' => [ 'label' => 'Masai (Tanzania)', 'native_name' => 'ƆL Maa (Tanzania)', 'flag' => '🇹🇿', 'rtl' => false ],
                'mer' => [ 'label' => 'Meru', 'native_name' => 'Kĩmĩĩrũ', 'flag' => '🇰🇪', 'rtl' => false ],
                'mer_KE' => [ 'label' => 'Meru (Kenya)', 'native_name' => 'Kĩmĩĩrũ (Kenya)', 'flag' => '🇰🇪', 'rtl' => false ],
                'mfe' => [ 'label' => 'Morisyen', 'native_name' => 'Kreol Morisien', 'flag' => '🇲🇺', 'rtl' => false ],
                'mfe_MU' => [ 'label' => 'Morisyen (Mauritius)', 'native_name' => 'Kreol Morisien (Mauritius)', 'flag' => '🇲🇺', 'rtl' => false ],
                'mg' => [ 'label' => 'Malagasy', 'native_name' => 'Malagasy', 'flag' => '🇲🇬', 'rtl' => false ],
                'mg_MG' => [ 'label' => 'Malagasy (Madagascar)', 'native_name' => 'Malagasy (Madagaskar)', 'flag' => '🇲🇬', 'rtl' => false ],
                'mk' => [ 'label' => 'Macedonian', 'native_name' => 'Македонски', 'flag' => '🇲🇰', 'rtl' => false ],
                'mk_MK' => [ 'label' => 'Macedonian (Macedonia)', 'native_name' => 'Македонски, Makedonski (Македонија)', 'flag' => '🇲🇰', 'rtl' => false ],
                'ml' => [ 'label' => 'Malayalam', 'native_name' => 'മലയാളം', 'flag' => '🇮🇳', 'rtl' => false ],
                'ml_IN' => [ 'label' => 'Malayalam (India)', 'native_name' => 'മലയാളം (ഇന്ത്യ)', 'flag' => '🇮🇳', 'rtl' => false ],
                'mr' => [ 'label' => 'Marathi', 'native_name' => 'मराठी', 'flag' => '🇮🇳', 'rtl' => false ],
                'mr_IN' => [ 'label' => 'Marathi (India)', 'native_name' => 'मराठी (भारत)', 'flag' => '🇮🇳', 'rtl' => false ],
                'ms' => [ 'label' => 'Malay', 'native_name' => 'Bahasa Melayu', 'flag' => '🇲🇾', 'rtl' => false ],
                'ms_BN' => [ 'label' => 'Malay (Brunei)', 'native_name' => 'Bahasa Melayu Brunei', 'flag' => '🇧🇳', 'rtl' => false ],
                'ms_MY' => [ 'label' => 'Malay (Malaysia)', 'native_name' => 'Bahasa Melayu (Malaysia)', 'flag' => '🇲🇾', 'rtl' => false ],
                'mt' => [ 'label' => 'Maltese', 'native_name' => 'Malti', 'flag' => '🇲🇹', 'rtl' => false ],
                'mt_MT' => [ 'label' => 'Maltese (Malta)', 'native_name' => 'Malti (Malta)', 'flag' => '🇲🇹', 'rtl' => false ],
                'my' => [ 'label' => 'Burmese', 'native_name' => 'မြန်မာစာ', 'flag' => '🇲🇲', 'rtl' => false ],
                'my_MM' => [ 'label' => 'Burmese (Myanmar [Burma])', 'native_name' => 'မြန်မာစာ (မြန်မာ [Burma])', 'flag' => '🇲🇲', 'rtl' => false ],
                'naq' => [ 'label' => 'Nama', 'native_name' => 'Khoekhoegowab', 'flag' => '🇳🇦', 'rtl' => false ],
                'naq_NA' => [ 'label' => 'Nama (Namibia)', 'native_name' => 'Khoekhoegowab (Nambia)', 'flag' => '🇳🇦', 'rtl' => false ],
                'nb' => [ 'label' => 'Norwegian Bokmål', 'native_name' => 'Bokmål', 'flag' => '🇳🇴', 'rtl' => false ],
                'nb_NO' => [ 'label' => 'Norwegian Bokmål (Norway)', 'native_name' => 'Bokmål (Norge)', 'flag' => '🇳🇴', 'rtl' => false ],
                'nd' => [ 'label' => 'North Ndebele', 'native_name' => 'Isindebele Sasenyakatho', 'flag' => '🇿🇼', 'rtl' => false ],
                'nd_ZW' => [ 'label' => 'North Ndebele (Zimbabwe)', 'native_name' => 'Isindebele Sasenyakatho (Zimbawe)', 'flag' => '🇿🇼', 'rtl' => false ],
                'ne' => [ 'label' => 'Nepali', 'native_name' => 'नेपाली', 'flag' => '🇳🇵', 'rtl' => false ],
                'ne_IN' => [ 'label' => 'Nepali (India)', 'native_name' => 'नेपाली (भारत)', 'flag' => '🇮🇳', 'rtl' => false ],
                'ne_NP' => [ 'label' => 'Nepali (Nepal)', 'native_name' => 'नेपाली (नेपाल)', 'flag' => '🇳🇵', 'rtl' => false ],
                'nl' => [ 'label' => 'Dutch', 'native_name' => 'Nederlands', 'flag' => '🇧🇶', 'rtl' => false ],
                'nl_BE' => [ 'label' => 'Dutch (Belgium)', 'native_name' => 'Nederlands (België)', 'flag' => '🇧🇪', 'rtl' => false ],
                'nl_NL' => [ 'label' => 'Dutch (Netherlands)', 'native_name' => 'Nederlands (Nederland)', 'flag' => '🇧🇶', 'rtl' => false ],
                'nn' => [ 'label' => 'Norwegian Nynorsk', 'native_name' => 'Norsk', 'flag' => '🇳🇴', 'rtl' => false ],
                'nn_NO' => [ 'label' => 'Norwegian Nynorsk (Norway)', 'native_name' => 'Norsk (Norway)', 'flag' => '🇳🇴', 'rtl' => false ],
                'nyn' => [ 'label' => 'Nyankole', 'native_name' => 'Orunyankore', 'flag' => '🇺🇬', 'rtl' => false ],
                'nyn_UG' => [ 'label' => 'Nyankole (Uganda)', 'native_name' => 'Orunyankore (Uganda)', 'flag' => '🇺🇬', 'rtl' => false ],
                'om' => [ 'label' => 'Oromo', 'native_name' => 'Afaan Oromoo', 'flag' => '🇪🇹', 'rtl' => false ],
                'om_ET' => [ 'label' => 'Oromo (Ethiopia)', 'native_name' => 'Afaan Oromoo (Ethiopia)', 'flag' => '🇪🇹', 'rtl' => false ],
                'om_KE' => [ 'label' => 'Oromo (Kenya)', 'native_name' => 'Afaan Oromoo (Kenya)', 'flag' => '🇰🇪', 'rtl' => false ],
                'or' => [ 'label' => 'Oriya', 'native_name' => 'ଓଡ଼ିଆ', 'flag' => '🇮🇳', 'rtl' => false ],
                'or_IN' => [ 'label' => 'Oriya (India)', 'native_name' => 'ଓଡ଼ିଆ (ଭାରତ)', 'flag' => '🇮🇳', 'rtl' => false ],
                'pa' => [ 'label' => 'Punjabi', 'native_name' => 'ਪੰਜਾਬੀ', 'flag' => '🇮🇳', 'rtl' => true ],
                'pa_Arab' => [ 'label' => 'Punjabi (Arabic)', 'native_name' => 'پن٘جابی (ਅਰਬੀ)', 'flag' => '🇶🇦', 'rtl' => true ],
                'pa_Arab_PK' => [ 'label' => 'Punjabi (Arabic, Pakistan)', 'native_name' => 'پن٘جابی(Arabic, Pakistan)', 'flag' => '🇵🇰', 'rtl' => true ],
                'pa_Guru' => [ 'label' => 'Punjabi (Gurmukhi)', 'native_name' => 'ਪੰਜਾਬੀ (ਗੁਰਮੁਖੀ)', 'flag' => '🇵🇰', 'rtl' => false ],
                'pa_Guru_IN' => [ 'label' => 'Punjabi (Gurmukhi, India)', 'native_name' => 'ਪੰਜਾਬੀ (Gurmukhi, India)', 'flag' => '🇮🇳', 'rtl' => false ],
                'pa_IN' => [ 'label' => 'Punjabi (India)', 'native_name' => 'ਪੰਜਾਬੀ (India)', 'flag' => '🇮🇳', 'rtl' => false ],
                'pl' => [ 'label' => 'Polish', 'native_name' => 'Polski', 'flag' => '🇵🇱', 'rtl' => false ],
                'pl_PL' => [ 'label' => 'Polish (Poland)', 'native_name' => 'Polski (Polska)', 'flag' => '🇵🇱', 'rtl' => false ],
                'ps' => [ 'label' => 'Pashto', 'native_name' => 'پښتو', 'flag' => '🇦🇫', 'rtl' => true ],
                'ps_AF' => [ 'label' => 'Pashto (Afghanistan)', 'native_name' => 'پښتو (افغانستان)', 'flag' => '🇦🇫', 'rtl' => true ],
                'pt' => [ 'label' => 'Portuguese', 'native_name' => 'Português', 'flag' => '🇧🇷', 'rtl' => false ],
                'pt_BR' => [ 'label' => 'Portuguese (Brazil)', 'native_name' => 'Português (Brasil)', 'flag' => '🇧🇷', 'rtl' => false ],
                'pt_GW' => [ 'label' => 'Portuguese (Guinea-Bissau)', 'native_name' => 'Português (Guiné-Bissau)', 'flag' => '🇬🇼', 'rtl' => false ],
                'pt_MZ' => [ 'label' => 'Portuguese (Mozambique)', 'native_name' => 'Português (Moçambique)', 'flag' => '🇲🇿', 'rtl' => false ],
                'pt_PT' => [ 'label' => 'Portuguese (Portugal)', 'native_name' => 'Português (Portugal)', 'flag' => '🇵🇹', 'rtl' => false ],
                'rm' => [ 'label' => 'Romansh', 'native_name' => 'Romontsch', 'flag' => '🇨🇭', 'rtl' => false ],
                'rm_CH' => [ 'label' => 'Romansh (Switzerland)', 'native_name' => 'Romontsch (Switzerland)', 'flag' => '🇨🇭', 'rtl' => false ],
                'ro' => [ 'label' => 'Romanian', 'native_name' => 'Limba Română', 'flag' => '🇷🇴', 'rtl' => false ],
                'ro_MD' => [ 'label' => 'Romanian (Moldova)', 'native_name' => 'Лимба Молдовеняскэ (Moldova)', 'flag' => '🇲🇩', 'rtl' => false ],
                'ro_RO' => [ 'label' => 'Romanian (Romania)', 'native_name' => 'Română', 'flag' => '🇷🇴', 'rtl' => false ],
                'rof' => [ 'label' => 'Rombo', 'native_name' => 'Kirombo', 'flag' => '🇹🇿', 'rtl' => false ],
                'rof_TZ' => [ 'label' => 'Rombo (Tanzania)', 'native_name' => 'Kirombo (Tanzania)', 'flag' => '🇹🇿', 'rtl' => false ],
                'ru' => [ 'label' => 'Russian', 'native_name' => 'Русский Язык', 'flag' => '🇷🇺', 'rtl' => false ],
                'ru_MD' => [ 'label' => 'Russian (Moldova)', 'native_name' => 'Русский Язык (Молдова)', 'flag' => '🇲🇩', 'rtl' => false ],
                'ru_RU' => [ 'label' => 'Russian (Russia)', 'native_name' => 'Русский Язык (Россия)', 'flag' => '🇷🇺', 'rtl' => false ],
                'ru_UA' => [ 'label' => 'Russian (Ukraine)', 'native_name' => 'Російська Мова (Украина)', 'flag' => '🇺🇦', 'rtl' => false ],
                'rw' => [ 'label' => 'Kinyarwanda', 'native_name' => 'Ikinyarwanda', 'flag' => '🇷🇼', 'rtl' => false ],
                'rw_RW' => [ 'label' => 'Kinyarwanda (Rwanda)', 'native_name' => 'Ikinyarwanda (U Rwanda)', 'flag' => '🇷🇼', 'rtl' => false ],
                'rwk' => [ 'label' => 'Rwa', 'native_name' => 'Rwa', 'flag' => '🇷🇼', 'rtl' => false ],
                'rwk_TZ' => [ 'label' => 'Rwa (Tanzania)', 'native_name' => 'Rwa', 'flag' => '🇹🇿', 'rtl' => false ],
                'saq' => [ 'label' => 'Samburu', 'native_name' => 'Sampur, ƆL Maa', 'flag' => '🇰🇪', 'rtl' => false ],
                'saq_KE' => [ 'label' => 'Samburu (Kenya)', 'native_name' => 'Sampur, ƆL Maa (Kenya)', 'flag' => '🇰🇪', 'rtl' => false ],
                'sdh_IR' => [ 'label' => 'Southern Kurdish (Iran)', 'native_name' => 'کوردی خوارگ', 'flag' => '🇮🇷', 'rtl' => true, ],
                'sdh_IQ' => [ 'label' => 'Southern Kurdish (Iran)', 'native_name' => 'کوردی خوارگ', 'flag' => '🇮🇶', 'rtl' => true, ],
                'seh' => [ 'label' => 'Sena', 'native_name' => 'Sena', 'flag' => '🇲🇿', 'rtl' => false ],
                'seh_MZ' => [ 'label' => 'Sena (Mozambique)', 'native_name' => 'Sena (Mozambique)', 'flag' => '🇲🇿', 'rtl' => false ],
                'ses' => [ 'label' => 'Koyraboro Senni', 'native_name' => 'Koyraboro Senni', 'flag' => '🇲🇱', 'rtl' => false ],
                'ses_ML' => [ 'label' => 'Koyraboro Senni (Mali)', 'native_name' => 'Koyraboro Senni (Mali)', 'flag' => '🇲🇱', 'rtl' => false ],
                'sg' => [ 'label' => 'Sango', 'native_name' => 'Yângâ Tî Sängö', 'flag' => '🇨🇫', 'rtl' => false ],
                'sg_CF' => [ 'label' => 'Sango (Central African Republic)', 'native_name' => 'Yângâ Tî Sängö (Central African Republic)', 'flag' => '🇨🇫', 'rtl' => false ],
                'shi' => [ 'label' => 'Tachelhit', 'native_name' => 'TacelḥIt', 'flag' => '🇲🇦', 'rtl' => false ],
                'shi_Latn' => [ 'label' => 'Tachelhit (Latin)', 'native_name' => 'TacelḥIt (Latin)', 'flag' => '🇲🇦', 'rtl' => false ],
                'shi_Latn_MA' => [ 'label' => 'Tachelhit (Latin, Morocco)', 'native_name' => 'TaclḥIyt (Latin, Morocco)', 'flag' => '🇲🇦', 'rtl' => false ],
                'shi_Tfng' => [ 'label' => 'Tachelhit (Tifinagh)', 'native_name' => 'ⵜⴰⵛⵍⵃⵉⵜ (Tifinagh)', 'flag' => '🇲🇦', 'rtl' => false ],
                'shi_Tfng_MA' => [ 'label' => 'Tachelhit (Tifinagh, Morocco)', 'native_name' => 'ⵜⴰⵛⵍⵃⵉⵜ (Tifinagh, Morocco)', 'flag' => '🇲🇦', 'rtl' => false ],
                'si' => [ 'label' => 'Sinhala', 'native_name' => 'සිංහල', 'flag' => '🇱🇰', 'rtl' => false ],
                'si_LK' => [ 'label' => 'Sinhala (Sri Lanka)', 'native_name' => 'සිංහල (ශ්රී ලංකාව)', 'flag' => '🇱🇰', 'rtl' => false ],
                'sk' => [ 'label' => 'Slovak', 'native_name' => 'Slovenčina, Slovenský Jazyk', 'flag' => '🇸🇰', 'rtl' => false ],
                'sk_SK' => [ 'label' => 'Slovak (Slovakia)', 'native_name' => 'Slovenčina, Slovenský Jazyk (Slovensko)', 'flag' => '🇸🇰', 'rtl' => false ],
                'sl' => [ 'label' => 'Slovenian', 'native_name' => 'Slovenščina', 'flag' => '🇸🇮', 'rtl' => false ],
                'sl_SI' => [ 'label' => 'Slovenian (Slovenia)', 'native_name' => 'Slovenščina (Slovenija)', 'flag' => '🇸🇮', 'rtl' => false ],
                'sn' => [ 'label' => 'Shona', 'native_name' => 'Chishona', 'flag' => '🇿🇼', 'rtl' => false ],
                'sn_ZW' => [ 'label' => 'Shona (Zimbabwe)', 'native_name' => 'Chishona (Zimbabwe)', 'flag' => '🇿🇼', 'rtl' => false ],
                'so' => [ 'label' => 'Somali', 'native_name' => 'Af Soomaali', 'flag' => '🇸🇴', 'rtl' => false ],
                'so_DJ' => [ 'label' => 'Somali (Djibouti)', 'native_name' => 'اف صومالي (Jabuuti)', 'flag' => '🇩🇯', 'rtl' => true ],
                'so_ET' => [ 'label' => 'Somali (Ethiopia)', 'native_name' => '𐒖𐒍 𐒈𐒝𐒑𐒛𐒐𐒘, 𐒈𐒝𐒑𐒛𐒐𐒘 (Ethiopia)', 'flag' => '🇪🇹', 'rtl' => false ],
                'so_KE' => [ 'label' => 'Somali (Kenya)', 'native_name' => 'Af Soomaali (Kenya)', 'flag' => '🇰🇪', 'rtl' => false ],
                'so_SO' => [ 'label' => 'Somali (Somalia)', 'native_name' => 'Af Soomaali (Soomaaliya)', 'flag' => '🇸🇴', 'rtl' => false ],
                'sq' => [ 'label' => 'Albanian', 'native_name' => 'Gjuha Shqipe', 'flag' => '🇦🇱', 'rtl' => false ],
                'sq_AL' => [ 'label' => 'Albanian (Albania)', 'native_name' => 'Gjuha Shqipe (Shqipëri)', 'flag' => '🇦🇱', 'rtl' => false ],
                'sr' => [ 'label' => 'Serbian', 'native_name' => 'Srpski Jezik', 'flag' => '🇷🇸', 'rtl' => false ],
                'sr_BA' => [ 'label' => 'Serbian (Cyrillic)', 'native_name' => 'Cрпски Језик (Ћирилица)', 'flag' => '🇷🇸', 'rtl' => false ],
                'sr_Cyrl' => [ 'label' => 'Serbian (Cyrillic)', 'native_name' => 'Cрпски Језик (Ћирилица)', 'flag' => '🇷🇺', 'rtl' => false ],
                'sr_Cyrl_BA' => [ 'label' => 'Serbian (Cyrillic, Bosnia and Herzegovina)', 'native_name' => 'Cрпски Језик (Cyrillic, Bosnia And Herzegovina)', 'flag' => '🇧🇦', 'rtl' => false ],
                'sr_Cyrl_ME' => [ 'label' => 'Serbian (Cyrillic, Montenegro)', 'native_name' => 'Cрпски Језик (Cyrillic, Montenegro)', 'flag' => '🇲🇪', 'rtl' => false ],
                'sr_Cyrl_RS' => [ 'label' => 'Serbian (Cyrillic, Serbia)', 'native_name' => 'Cрпски Језик (Cyrillic, Serbia)', 'flag' => '🇷🇸', 'rtl' => false ],
                'sr_Latn' => [ 'label' => 'Serbian (Latin)', 'native_name' => 'Srpski Jezik (Латински Језик)', 'flag' => '🇷🇸', 'rtl' => false ],
                'sr_Latn_BA' => [ 'label' => 'Serbian (Latin, Bosnia and Herzegovina)', 'native_name' => 'Srpski Jezik (Latin, Bosnia And Herzegovina)', 'flag' => '🇧🇦', 'rtl' => false ],
                'sr_Latn_ME' => [ 'label' => 'Serbian (Latin, Montenegro)', 'native_name' => 'Srpski Jezik (Latin, Montenegro)', 'flag' => '🇲🇪', 'rtl' => false ],
                'sr_Latn_RS' => [ 'label' => 'Serbian (Latin, Serbia)', 'native_name' => 'Srpski Jezik (Latin, Serbia)', 'flag' => '🇷🇸', 'rtl' => false ],
                'sv' => [ 'label' => 'Swedish', 'native_name' => 'Svenska', 'flag' => '🇸🇪', 'rtl' => false ],
                'sv_FI' => [ 'label' => 'Swedish (Finland)', 'native_name' => 'Finlandssvenska (Finland)', 'flag' => '🇫🇮', 'rtl' => false ],
                'sv_SE' => [ 'label' => 'Swedish (Sweden)', 'native_name' => 'Svenska (Sverige)', 'flag' => '🇸🇪', 'rtl' => false ],
                'sw' => [ 'label' => 'Swahili', 'native_name' => 'Kiswahili', 'flag' => '🇹🇿', 'rtl' => false ],
                'sw_KE' => [ 'label' => 'Swahili (Kenya)', 'native_name' => 'Kiswahili (Kenya)', 'flag' => '🇰🇪', 'rtl' => false ],
                'sw_TZ' => [ 'label' => 'Swahili (Tanzania)', 'native_name' => 'Kiswahili (Tanzania)', 'flag' => '🇹🇿', 'rtl' => false ],
                'ta' => [ 'label' => 'Tamil', 'native_name' => 'தமிழ்', 'flag' => '🇮🇳', 'rtl' => false ],
                'ta_IN' => [ 'label' => 'Tamil (India)', 'native_name' => 'தமிழ் (இந்தியா)', 'flag' => '🇮🇳', 'rtl' => false ],
                'ta_LK' => [ 'label' => 'Tamil (Sri Lanka)', 'native_name' => 'ஈழத் தமிழ் (இலங்கை)', 'flag' => '🇱🇰', 'rtl' => false ],
                'te' => [ 'label' => 'Telugu', 'native_name' => 'తెలుగు', 'flag' => '🇮🇳', 'rtl' => false ],
                'te_IN' => [ 'label' => 'Telugu (India)', 'native_name' => 'తెలుగు (భారతదేశం)', 'flag' => '🇮🇳', 'rtl' => false ],
                'teo' => [ 'label' => 'Teso', 'native_name' => 'Ateso', 'flag' => '🇺🇬', 'rtl' => false ],
                'teo_KE' => [ 'label' => 'Teso (Kenya)', 'native_name' => 'Ateso (Kenya)', 'flag' => '🇰🇪', 'rtl' => false ],
                'teo_UG' => [ 'label' => 'Teso (Uganda)', 'native_name' => 'Ateso (Uganda)', 'flag' => '🇺🇬', 'rtl' => false ],
                'th' => [ 'label' => 'Thai', 'native_name' => 'ภาษาไทย', 'flag' => '🇹🇭', 'rtl' => false ],
                'th_TH' => [ 'label' => 'Thai (Thailand)', 'native_name' => 'ภาษาไทย (ประเทศไทย)', 'flag' => '🇹🇭', 'rtl' => false ],
                'ti' => [ 'label' => 'Tigrinya', 'native_name' => 'ትግርኛ', 'flag' => '🇪🇹', 'rtl' => false ],
                'ti_ER' => [ 'label' => 'Tigrinya (Eritrea)', 'native_name' => 'ትግርኛ (Eritrea)', 'flag' => '🇪🇷', 'rtl' => false ],
                'ti_ET' => [ 'label' => 'Tigrinya (Ethiopia)', 'native_name' => 'ትግርኛ (Ethiopia)', 'flag' => '🇪🇹', 'rtl' => false ],
                'tl' => [ 'label' => 'Tagalog', 'native_name' => 'Tagalog', 'flag' => '🇵🇭', 'rtl' => false ],
                'to' => [ 'label' => 'Tonga', 'native_name' => 'Lea Faka', 'flag' => '🇹🇴', 'rtl' => false ],
                'to_TO' => [ 'label' => 'Tonga (Tonga)', 'native_name' => 'Lea Faka (Tonga)', 'flag' => '🇹🇴', 'rtl' => false ],
                'tr' => [ 'label' => 'Turkish', 'native_name' => 'Türkçe', 'flag' => '🇹🇷', 'rtl' => false ],
                'tr_TR' => [ 'label' => 'Turkish (Türkiye)', 'native_name' => 'Türkçe (Türkiye)', 'flag' => '🇹🇷', 'rtl' => false ],
                'tzm' => [ 'label' => 'Central Morocco Tamazight', 'native_name' => 'ⵜⴰⵎⴰⵣⵉⵖⵜ', 'flag' => '🇲🇦', 'rtl' => false ],
                'tzm_Latn' => [ 'label' => 'Central Morocco Tamazight (Latin)', 'native_name' => 'TamaziɣT (Latin)', 'flag' => '🇲🇦', 'rtl' => false ],
                'tzm_Latn_MA' => [ 'label' => 'Central Morocco Tamazight (Latin, Morocco)', 'native_name' => 'TamaziɣT (Latin, Morocco)', 'flag' => '🇲🇦', 'rtl' => false ],
                'uk' => [ 'label' => 'Ukrainian', 'native_name' => 'Українська Мова', 'flag' => '🇺🇦', 'rtl' => false ],
                'uk_UA' => [ 'label' => 'Ukrainian (Ukraine)', 'native_name' => 'Українська Мова (Україна)', 'flag' => '🇺🇦', 'rtl' => false ],
                'ur' => [ 'label' => 'Urdu', 'native_name' => 'اُردُو', 'flag' => '🇵🇰', 'rtl' => true ],
                'ur_IN' => [ 'label' => 'Urdu (India)', 'native_name' => 'اُردُو (ہندوستان)', 'flag' => '🇮🇳', 'rtl' => true ],
                'ur_PK' => [ 'label' => 'Urdu (Pakistan)', 'native_name' => 'اُردُو (پاکستان)', 'flag' => '🇵🇰', 'rtl' => true ],
                'uz' => [ 'label' => 'Uzbek', 'native_name' => 'اۉزبېکچه, اۉزبېک تیلی', 'flag' => '🇺🇿', 'rtl' => true ],
                'uz_Arab' => [ 'label' => 'Uzbek (Arabic)', 'native_name' => 'اۉزبېکچه, اۉزبېک تیلی (Arabparast)', 'flag' => '🇶🇦', 'rtl' => true ],
                'uz_Arab_AF' => [ 'label' => 'Uzbek (Arabic, Afghanistan)', 'native_name' => 'اۉزبېکچه, اۉزبېک تیلی (Arabic, Afghanistan)', 'flag' => '🇦🇫', 'rtl' => true ],
                'uz_Cyrl' => [ 'label' => 'Uzbek (Cyrillic)', 'native_name' => 'Ўзбекча, Ўзбек Тили (Kirillcha)', 'flag' => '🇷🇺', 'rtl' => false ],
                'uz_Cyrl_UZ' => [ 'label' => 'Uzbek (Cyrillic, Uzbekistan)', 'native_name' => 'Ўзбекча, Ўзбек Тили (Kirillcha Uzbekistan)', 'flag' => '🇺🇿', 'rtl' => false ],
                'uz_Latn' => [ 'label' => 'Uzbek (Latin)', 'native_name' => 'OʻZbekcha, OʻZbek Tili, (Lotin)', 'flag' => '🇺🇿', 'rtl' => false ],
                'uz_Latn_UZ' => [ 'label' => 'Uzbek (Latin, Uzbekistan)', 'native_name' => 'OʻZbekcha, OʻZbek Tili, (Lotin Uzbekistan)', 'flag' => '🇺🇿', 'rtl' => false ],
                'vi' => [ 'label' => 'Vietlabelse', 'native_name' => 'OʻZbekcha, OʻZbek Tili,', 'flag' => '🇻🇳', 'rtl' => false ],
                'vi_VN' => [ 'label' => 'Vietlabelse (Vietnam)', 'native_name' => 'TiếNg ViệT (ViệT Nam)', 'flag' => '🇻🇳', 'rtl' => false ],
                'vun' => [ 'label' => 'Vunjo', 'native_name' => 'Wunjo', 'flag' => '🇹🇿', 'rtl' => false ],
                'vun_TZ' => [ 'label' => 'Vunjo (Tanzania)', 'native_name' => 'Wunjo (Tanzania)', 'flag' => '🇹🇿', 'rtl' => false ],
                'xog' => [ 'label' => 'Soga', 'native_name' => 'Lusoga', 'flag' => '🇺🇬', 'rtl' => false ],
                'xog_UG' => [ 'label' => 'Soga (Uganda)', 'native_name' => 'Lusoga (Uganda)', 'flag' => '🇺🇬', 'rtl' => false ],
                'yo' => [ 'label' => 'Yoruba', 'native_name' => 'Èdè Yorùbá', 'flag' => '🇳🇬', 'rtl' => false ],
                'yo_NG' => [ 'label' => 'Yoruba (Nigeria)', 'native_name' => 'Èdè Yorùbá (Orilẹ-Ede Nigeria)', 'flag' => '🇳🇬', 'rtl' => false ],
                'yue_Hant_HK' => [ 'label' => 'Cantonese (Traditional, Hong Kong SAR China)', 'native_name' => '香港粵語', 'flag' => '🇭🇰', 'rtl' => false ],
                'zh' => [ 'label' => 'Chinese', 'native_name' => '中文简体', 'flag' => '🇨🇳', 'rtl' => false ],
                'zh_Hans' => [ 'label' => 'Chinese (Simplified Han)', 'native_name' => '中文简体 (简化的汉)', 'flag' => '🇨🇳', 'rtl' => false ],
                'zh_CN' => [ 'label' => 'Chinese (Simplified Han, China)', 'native_name' => '中文简体 (简化的汉，中国)', 'flag' => '🇨🇳', 'rtl' => false ],
                'zh_Hans_CN' => [ 'label' => 'Chinese (Simplified Han, China)', 'native_name' => '中文简体 (简化的汉，中国)', 'flag' => '🇨🇳', 'rtl' => false ],
                'zh_Hans_HK' => [ 'label' => 'Chinese (Simplified Han, Hong Kong SAR China)', 'native_name' => '簡體中文（香港） (简化的汉，香港中国)', 'flag' => '🇭🇰', 'rtl' => false ],
                'zh_Hans_MO' => [ 'label' => 'Chinese (Simplified Han, Macau SAR China)', 'native_name' => '简体中文 (澳门) (简化的汉，澳门)', 'flag' => '🇲🇴', 'rtl' => false ],
                'zh_Hans_SG' => [ 'label' => 'Chinese (Simplified Han, Singapore)', 'native_name' => '简体中文(新加坡） (简化的汉，新加坡)', 'flag' => '🇸🇬', 'rtl' => false ],
                'zh_Hant' => [ 'label' => 'Chinese (Traditional Han)', 'native_name' => '中文（繁體） (传统汉)', 'flag' => '🇹🇼', 'rtl' => false ],
                'zh_Hant_HK' => [ 'label' => 'Chinese (Traditional Han, Hong Kong SAR China)', 'native_name' => '中國繁體漢，（香港） (傳統的漢，香港中國)', 'flag' => '🇭🇰', 'rtl' => false ],
                'zh_Hant_MO' => [ 'label' => 'Chinese (Traditional Han, Macau SAR China)', 'native_name' => '中文（繁體漢、澳門） (傳統漢，澳門)', 'flag' => '🇲🇴', 'rtl' => false ],
                'zh_TW' => [ 'label' => 'Chinese (Traditional Han, Taiwan)', 'native_name' => '中文（繁體漢，台灣） (台灣傳統漢)', 'flag' => '🇹🇼', 'rtl' => false ],
                'zh_Hant_TW' => [ 'label' => 'Chinese (Traditional Han, Taiwan)', 'native_name' => '中文（繁體漢，台灣） (台灣傳統漢)', 'flag' => '🇹🇼', 'rtl' => false ],
                'zu' => [ 'label' => 'Zulu', 'native_name' => 'Isizulu', 'flag' => '🇿🇦', 'rtl' => false ],
                'zu_ZA' => [ 'label' => 'Zulu (South Africa)', 'native_name' => 'Isizulu (Iningizimu Afrika)', 'flag' => '🇿🇦', 'rtl' => false ],
            ];
            return apply_filters( 'dt_global_languages_list', $global_languages_list );
        }
    }

    /**
     * All code above here.
     */
} // end if ( ! defined( 'DT_FUNCTIONS_READY' ) )

