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
                    $elapsed_log = sprintf( "[elapsed:%5dms]", ( $now - $dt_write_log_microtime ) * 1000 );
                } else {
                    $elapsed_log = "[elapsed:-------]";
                }
                $dt_write_log_microtime = $now;
                if ( is_array( $log ) || is_object( $log ) ) {
                    error_log( $elapsed_log . " " . print_r( $log, true ) );
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
            if ( isset( $_SERVER["HTTP_HOST"] ) ) {
                $url  = ( !isset( $_SERVER["HTTPS"] ) || @( $_SERVER["HTTPS"] != 'on' ) ) ? 'http://'. sanitize_text_field( wp_unslash( $_SERVER["HTTP_HOST"] ) ) : 'https://'. sanitize_text_field( wp_unslash( $_SERVER["HTTP_HOST"] ) );
                if ( isset( $_SERVER["REQUEST_URI"] ) ) {
                    $url .= sanitize_text_field( wp_unslash( $_SERVER["REQUEST_URI"] ) );
                }
                $url = trim( str_replace( get_site_url(), "", $url ), '/' );
                if ( $ignore_query_parameters ){
                    return strtok( $url, '?' ); //allow get parameters
                }
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
                if ( '\N' === $val ) {
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
            return gmdate( "Y", $time );
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
        function dt_get_available_languages( $code_as_key = false ) {
            $available_language_codes = get_available_languages( get_template_directory() .'/dt-assets/translation' );
            array_unshift( $available_language_codes, 'en_US' );
            $available_translations = [];
            $site_default_locale = get_option( 'WPLANG' );

            $translations = [
                'en_US' => [
                    'language' => 'en_US',
                    'english_name' => 'English (United States)',
                    'native_name' => 'English (United States)'
                ],
                'am_ET' => [
                    'language' => 'am_ET',
                    'native_name' => 'Amharic (Ethiopia)',
                    'english_name' => 'Amharic (Ethiopia)'
                ],
                'ar' => [
                    'language' => 'ar',
                    'english_name' => 'Arabic',
                    'native_name' => 'العربية'
                ],
                'ar_MA' => [
                    'language' => 'ar_MA',
                    'native_name' => 'العربية (المغرب)',
                    'english_name' => 'Arabic (Morocco)'
                ],
                'bg_BG' => [
                    'language' => 'bg_BG',
                    'english_name' => 'Bulgarian',
                    'native_name' => 'Български'
                ],
                'bn_BD' => [
                    'language' => 'bn_BD',
                    'english_name' => 'Bengali (Bangladesh)',
                    'native_name' => 'বাংলা'
                ],
                'bs_BA' => [
                    'language' => 'bs_BA',
                    'english_name' => 'Bosnian',
                    'native_name' => 'Bosanski'
                ],
                'de_DE' => [
                    'language' => 'de_DE',
                    'english_name' => 'German',
                    'native_name' => 'Deutsch'
                ],
                'es_419' => [
                    'language' => 'es_419',
                    'native_name' => 'Español (Latinoamérica) ',
                    'english_name' => 'Spanish (Latin America)'
                ],
                'es_AR' => [
                    'language' => 'es_AR',
                    'english_name' => 'Spanish (Argentina)',
                    'native_name' => 'Español de Argentina'
                ],
                'es_CO' => [
                    'language' => 'es_CO',
                    'english_name' => 'Spanish (Colombia)',
                    'native_name' => 'Español de Colombia'
                ],
                'es_ES' => [
                    'language' => 'es_ES',
                    'english_name' => 'Spanish (Spain)',
                    'native_name' => 'Español'
                ],
                'es_MX' => [
                    'language' => 'es_MX',
                    'english_name' => 'Spanish (Mexico)',
                    'native_name' => 'Español de México'
                ],
                'fa_IR' => [
                    'language' => 'fa_IR',
                    'english_name' => 'Persian',
                    'native_name' => 'فارسی'
                ],
                'fr_FR' => [
                    'language' => 'fr_FR',
                    'english_name' => 'French (France)',
                    'native_name' => 'Français'
                ],
                'hi_IN' => [
                    'language' => 'hi_IN',
                    'english_name' => 'Hindi',
                    'native_name' => 'हिन्दी'
                ],
                'hr' => [
                    'language' => 'hr',
                    'english_name' => 'Croatian',
                    'native_name' => 'Hrvatski'
                ],
                'hu_HU' => [
                    'language' => 'hu_HU',
                    'english_name' => 'Hungarian',
                    'native_name' => 'Magyar'
                ],
                'id_ID' => [
                    'language' => 'id_ID',
                    'english_name' => 'Indonesian',
                    'native_name' => 'Bahasa Indonesia'
                ],
                'it_IT' => [
                    'language' => 'it_IT',
                    'english_name' => 'Italian',
                    'native_name' => 'Italiano'
                ],
                'ja' => [
                    'language' => 'ja',
                    'english_name' => 'Japanese',
                    'native_name' => '日本語'
                ],
                'ko_KR' => [
                    'language' => 'ko_KR',
                    'english_name' => 'Korean',
                    'native_name' => '한국어'
                ],
                'mk_MK' => [
                    'language' => 'mk_MK',
                    'english_name' => 'Macedonian',
                    'native_name' => 'Македонски јазик'
                ],
                'mr' => [
                    'language' => 'mr',
                    'english_name' => 'Marathi',
                    'native_name' => 'मराठी'
                ],
                'my_MM' => [
                    'language' => 'my_MM',
                    'english_name' => 'Myanmar (Burmese)',
                    'native_name' => 'ဗမာစာ'
                ],
                'ne_NP' => [
                    'language' => 'ne_NP',
                    'english_name' => 'Nepali',
                    'native_name' => 'नेपाली'
                ],
                'nl_NL' => [
                    'language' => 'nl_NL',
                    'english_name' => 'Dutch',
                    'native_name' => 'Nederlands'
                ],
                'pa_IN' => [
                    'language' => 'pa_IN',
                    'english_name' => 'Punjabi',
                    'native_name' => 'ਪੰਜਾਬੀ'
                ],
                'pt_BR' => [
                    'language' => 'pt_BR',
                    'english_name' => 'Portuguese (Brazil)',
                    'native_name' => 'Português do Brasil'
                ],
                'ro_RO' => [
                    'language' => 'ro_RO',
                    'english_name' => 'Romanian',
                    'native_name' => 'Română'
                ],
                'ru_RU' => [
                    'language' => 'ru_RU',
                    'english_name' => 'Russian',
                    'native_name' => 'Русский'
                ],
                'sl_SI' => [
                    'language' => 'sl_SI',
                    'english_name' => 'Slovenian',
                    'native_name' => 'Slovenščina'
                ],
                'sr_BA' => [
                    'language' => 'sr_BA',
                    'native_name' => 'српски',
                    'english_name' => 'Serbian'
                ],
                'sw' => [
                    'language' => 'sw',
                    'native_name' => 'Kiswahili',
                    'english_name' => 'Swahili'
                ],
                'th' => [
                    'language' => 'th',
                    'english_name' => 'Thai',
                    'native_name' => 'ไทย'
                ],
                'tl' => [
                    'language' => 'tl',
                    'english_name' => 'Tagalog',
                    'native_name' => 'Tagalog'
                ],
                'tr_TR' => [
                    'language' => 'tr_TR',
                    'english_name' => 'Turkish',
                    'native_name' => 'Türkçe'
                ],
                'vi' => [
                    'language' => 'vi',
                    'english_name' => 'Vietnamese',
                    'native_name' => 'Tiếng Việt'
                ],
                'zh_CN' => [
                    'language' => 'zh_CN',
                    'english_name' => 'Chinese (China)',
                    'native_name' => '简体中文'
                ],
                'zh_TW' => [
                    'language' => 'zh_TW',
                    'english_name' => 'Chinese (Taiwan)',
                    'native_name' => '繁體中文'
                ],
            ];

            foreach ( $available_language_codes as $code ){
                if ( isset( $translations[$code] ) ){
                    $translations[$code]['site_default'] = $site_default_locale === $translations[$code]["language"];
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
                    <option value="<?php echo esc_html( $language["language"] ); ?>" <?php selected( $dt_user_locale === $language["language"] ) ?>>
                        <?php echo esc_html( $language["native_name"] ); ?> - <?php echo esc_html( $language["english_name"] ); ?>
                    </option>
                <?php } ?>
            </select>
            <?php
        }
    }

    if ( !function_exists( "dt_create_field_key" ) ){
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
        if ( !isset( $post["type"]["key"] ) ){
            return true;
        }
        // if only_for_type is not set, then the field is available on all types
        if ( !isset( $field["only_for_types"] ) ){
            return true;
        } else if ( $field["only_for_types"] === true ){
            return true;
        } else if ( is_array( $field["only_for_types"] ) && in_array( $post["type"]["key"], $field["only_for_types"], true ) ){
            //if the type is in the "only_for_types"
            return true;
        }
        return false;
    }

    /**
     * Accepts types: key_select, multi_select, text, textarea, number, date, connection, location, communication_channel, tags, user_select
     *
     * @param $field_key
     * @param $fields
     * @param $post
     * @param bool $show_extra_controls // show typeahead create button
     * @param bool $show_hidden // show hidden select options
     * @param string $field_id_prefix // add a prefix to avoid fields with duplicate ids.
     */
    function render_field_for_display( $field_key, $fields, $post, $show_extra_controls = false, $show_hidden = false, $field_id_prefix = '' ){
        $required_tag = ( isset( $fields[$field_key]["required"] ) && $fields[$field_key]["required"] === true ) ? 'required' : '';
        $field_type = isset( $fields[$field_key]["type"] ) ? $fields[$field_key]["type"] : null;
        $is_private = isset( $fields[$field_key]["private"] ) && $fields[$field_key]["private"] === true;
        $display_field_id = $field_key;
        if ( !empty( $field_id_prefix ) ) {
            $display_field_id = $field_id_prefix . $field_key;
        }
        if ( isset( $fields[$field_key]["type"] ) && empty( $fields[$field_key]["custom_display"] ) && empty( $fields[$field_key]["hidden"] ) ) {
            $allowed_types = apply_filters( 'dt_render_field_for_display_allowed_types', [ 'key_select', 'multi_select', 'date', 'datetime', 'text', 'textarea', 'number', 'connection', 'location', 'location_meta', 'communication_channel', 'tags', 'user_select' ] );
            if ( !in_array( $field_type, $allowed_types ) ){
                return;
            }
            if ( !dt_field_enabled_for_record_type( $fields[$field_key], $post ) ){
                return;
            }


            ?>
            <div class="section-subheader">
                <?php if ( isset( $fields[$field_key]["icon"] ) ) : ?>
                    <img class="dt-icon" src="<?php echo esc_url( $fields[$field_key]["icon"] ) ?>">
                <?php endif;
                echo esc_html( $fields[$field_key]["name"] );
                ?> <span id="<?php echo esc_html( $display_field_id ); ?>-spinner" class="loading-spinner"></span>
                <?php if ( $is_private ) : ?>
                    <i class="fi-lock small" title="<?php _x( "Private Field: Only I can see it's content", 'disciple_tools' )?>"></i>
                <?php endif;
                if ( $field_type === "communication_channel" ) : ?>
                    <button data-list-class="<?php echo esc_html( $display_field_id ); ?>" class="add-button" type="button">
                        <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/small-add.svg' ) ?>"/>
                    </button>
                <?php endif ?>
                <!-- location add -->
                <?php if ( ( $field_type === "location" || "location_meta" === $field_type ) && DT_Mapbox_API::get_key() && ! empty( $post ) ) : ?>
                    <button data-list-class="<?php echo esc_html( $field_key ) ?>" class="add-button" id="new-mapbox-search" type="button">
                        <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/small-add.svg' ) ?>"/>
                    </button>
                <?php endif ?>
            </div>
            <?php
            if ( $field_type === "key_select" ) :
                $color_select = false;
                $active_color = "";
                if ( isset( $fields[$field_key]["default_color"] ) ) {
                    $color_select = true;
                    $active_color = $fields[$field_key]["default_color"];
                    $current_key = $post[$field_key]["key"] ?? "";
                    if ( isset( $fields[$field_key]["default"][ $current_key ]["color"] ) ){
                        $active_color = $fields[$field_key]["default"][ $current_key ]["color"];
                    }
                }
                ?>
                <select class="select-field <?php echo esc_html( $color_select ? "color-select" : "" ); ?>" id="<?php echo esc_html( $display_field_id ); ?>" style="<?php echo esc_html( $color_select ? ( "background-color: " . $active_color ) : "" ); ?>" <?php echo esc_html( $required_tag ) ?>>
                    <?php if ( !isset( $fields[$field_key]["default"]["none"] ) && empty( $fields[$field_key]["select_cannot_be_empty"] ) ) : ?>
                        <option value="" <?php echo esc_html( !isset( $post[$field_key] ) ?: "selected" ) ?>></option>
                    <?php endif; ?>
                    <?php foreach ( $fields[$field_key]["default"] as $option_key => $option_value ):
                        if ( !$show_hidden && isset( $option_value["hidden"] ) && $option_value["hidden"] === true ){
                            continue;
                        }
                        $selected = isset( $post[$field_key]["key"] ) && $post[$field_key]["key"] === strval( $option_key ); ?>
                        <option value="<?php echo esc_html( $option_key )?>" <?php echo esc_html( $selected ? "selected" : "" )?>>
                            <?php echo esc_html( $option_value["label"] ) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php elseif ( $field_type === "tags" ) : ?>
                <div id="<?php echo esc_html( $display_field_id ); ?>" class="tags">
                    <var id="<?php echo esc_html( $display_field_id ); ?>-result-container" class="result-container"></var>
                    <div id="<?php echo esc_html( $display_field_id ); ?>_t" name="form-tags" class="scrollable-typeahead typeahead-margin-when-active">
                        <div class="typeahead__container">
                            <div class="typeahead__field">
                                <span class="typeahead__query">
                                    <input class="js-typeahead-<?php echo esc_html( $display_field_id ); ?> input-height"
                                           data-field="<?php echo esc_html( $field_key );?>"
                                           name="<?php echo esc_html( $display_field_id ); ?>[query]"
                                           placeholder="<?php echo esc_html( sprintf( _x( "Search %s", "Search 'something'", 'disciple_tools' ), $fields[$field_key]['name'] ) )?>"
                                           autocomplete="off"
                                           data-add-new-tag-text="<?php echo esc_html( __( 'Add new tag "%s"', 'disciple_tools' ) )?>"
                                           data-tag-exists-text="<?php echo esc_html( __( 'Tag "%s" is already being used', 'disciple_tools' ) )?>">
                                </span>
                                <?php if ( $show_extra_controls ) : ?>
                                <span class="typeahead__button">
                                    <button type="button" data-open="create-tag-modal" class="create-new-tag typeahead__image_button input-height" data-field="<?php echo esc_html( $field_key );?>">
                                        <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/tag-add.svg' ) ?>"/>
                                    </button>
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php elseif ( $field_type === "multi_select" ) :
                if ( isset( $fields[$field_key]["display"] ) && $fields[$field_key]["display"] === "typeahead" ){
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
                                               placeholder="<?php echo esc_html( sprintf( _x( "Search %s", "Search 'something'", 'disciple_tools' ), $fields[$field_key]['name'] ) )?>"
                                               autocomplete="off">
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } else { ?>
                    <div class="small button-group" style="display: inline-block">
                        <?php foreach ( $fields[$field_key]["default"] as $option_key => $option_value ): ?>
                            <?php
                            $class = ( in_array( $option_key, $post[$field_key] ?? [] ) ) ?
                                "selected-select-button" : "empty-select-button"; ?>
                            <button id="<?php echo esc_html( $option_key ) ?>" type="button" data-field-key="<?php echo esc_html( $field_key ); ?>"
                                    class="dt_multi_select <?php echo esc_html( $class ) ?> select-button button ">
                                <?php if ( !empty( $option_value["icon"] ) ) { ?>
                                    <img class="dt-icon" src="<?php echo esc_html( $option_value["icon"] ) ?>" >
                                <?php } ?>
                                <?php echo esc_html( $option_value["label"] ) ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                <?php } ?>
            <?php elseif ( $field_type === "text" ) :?>
                <input id="<?php echo esc_html( $display_field_id ); ?>" type="text" <?php echo esc_html( $required_tag ) ?>
                       class="text-input"
                       value="<?php echo esc_html( $post[$field_key] ?? "" ) ?>"/>
            <?php elseif ( $field_type === "textarea" ) :?>
                <textarea id="<?php echo esc_html( $display_field_id ); ?>" <?php echo esc_html( $required_tag ) ?>
                       class="textarea dt_textarea"><?php echo esc_html( $post[$field_key] ?? "" ) ?></textarea>
            <?php elseif ( $field_type === "number" ) :?>
                <input id="<?php echo esc_html( $display_field_id ); ?>" type="number" <?php echo esc_html( $required_tag ) ?>
                       class="text-input"
                       value="<?php echo esc_html( $post[$field_key] ?? "" ) ?>"/>
            <?php elseif ( $field_type === "date" ) :?>
                <div class="<?php echo esc_html( $display_field_id ); ?> input-group">
                    <input id="<?php echo esc_html( $display_field_id ); ?>" class="input-group-field dt_date_picker" type="text" autocomplete="off" <?php echo esc_html( $required_tag ) ?>
                           value="<?php echo esc_html( $post[$field_key]["timestamp"] ?? '' ) ?>" >
                    <div class="input-group-button">
                        <button id="<?php echo esc_html( $display_field_id ); ?>-clear-button" class="button alert clear-date-button" data-inputid="<?php echo esc_html( $display_field_id ); ?>" title="Delete Date" type="button">x</button>
                    </div>
                </div>
            <?php elseif ( $field_type === "connection" ) :?>
                <div id="<?php echo esc_attr( $display_field_id . '_connection' ) ?>" class="dt_typeahead">
                    <span id="<?php echo esc_html( $display_field_id ); ?>-result-container" class="result-container"></span>
                    <div id="<?php echo esc_html( $display_field_id ); ?>_t" name="form-<?php echo esc_html( $display_field_id ); ?>" class="scrollable-typeahead typeahead-margin-when-active">
                        <div class="typeahead__container">
                            <div class="typeahead__field">
                                <span class="typeahead__query">
                                    <input class="js-typeahead-<?php echo esc_html( $display_field_id ); ?> input-height"
                                           data-field="<?php echo esc_html( $field_key ); ?>"
                                           data-post_type="<?php echo esc_html( $fields[$field_key]["post_type"] ) ?>"
                                           data-field_type="connection"
                                           name="<?php echo esc_html( $display_field_id ); ?>[query]"
                                           placeholder="<?php echo esc_html( sprintf( _x( "Search %s", "Search 'something'", 'disciple_tools' ), $fields[$field_key]['name'] ) )?>"
                                           autocomplete="off">
                                </span>
                                <?php if ( $show_extra_controls ) : ?>
                                <span class="typeahead__button">
                                    <button type="button" data-connection-key="<?php echo esc_html( $display_field_id ); ?>" class="create-new-record typeahead__image_button input-height">
                                        <?php $icon = isset( $fields[$field_key]["create-icon"] ) ? $fields[$field_key]["create-icon"] : get_template_directory_uri() . '/dt-assets/images/add-contact.svg'; ?>
                                        <img src="<?php echo esc_html( $icon ) ?>"/>
                                    </button>
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php elseif ( $field_type === "location_meta" ) : ?>
                <?php if ( DT_Mapbox_API::get_key() && empty( $post ) ) : // test if Mapbox key is present ?>
                    <div id="mapbox-autocomplete" class="mapbox-autocomplete input-group" data-autosubmit="false">
                        <input id="mapbox-search" type="text" class="input-group-field" name="mapbox_search" placeholder="Search Location" autocomplete="off" dir="auto"/>
                        <div class="input-group-button">
                            <button id="mapbox-spinner-button" class="button hollow" style="display:none;"><span class="loading-spinner active"></span></button>
                            <button id="mapbox-clear-autocomplete" class="button alert input-height delete-button-style mapbox-delete-button" style="display:none;" type="button">&times;</button>
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
            <?php elseif ( $field_type === "location" ) :?>
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
                                           placeholder="<?php echo esc_html( sprintf( _x( "Search %s", "Search 'something'", 'disciple_tools' ), $fields[$field_key]['name'] ) )?>"
                                           autocomplete="off" />
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php elseif ( $field_type === "communication_channel" ) : ?>
                <div id="edit-<?php echo esc_html( $field_key ) ?>" >
                    <?php foreach ( $post[$field_key] ?? [] as $field_value ) : ?>
                        <div class="input-group">
                            <input id="<?php echo esc_html( $field_value["key"] ) ?>"
                                   type="text"
                                   data-field="<?php echo esc_html( $field_key ); ?>"
                                   value="<?php echo esc_html( $field_value["value"] ) ?>"
                                   class="dt-communication-channel input-group-field" dir="auto"/>
                            <div class="input-group-button">
                                <button class="button alert input-height delete-button-style channel-delete-button delete-button new-<?php echo esc_html( $field_key ); ?>" data-field="<?php echo esc_html( $field_key ); ?>" data-key="<?php echo esc_html( $field_value["key"] ); ?>">&times;</button>
                            </div>
                        </div>
                    <?php endforeach;
                    if ( empty( $post[$field_key] ) ?? [] ): ?>
                        <div class="input-group">
                            <input type="text"
                                    <?php echo esc_html( $required_tag ) ?>
                                   data-field="<?php echo esc_html( $field_key ) ?>"
                                   class="dt-communication-channel input-group-field" dir="auto" />
                        </div>
                    <?php endif ?>
                </div>
            <?php elseif ( $field_type === "user_select" ) : ?>
                <div id="<?php echo esc_html( $field_key ); ?>" class="<?php echo esc_html( $display_field_id ); ?> dt_user_select">
                    <var id="<?php echo esc_html( $display_field_id ); ?>-result-container" class="result-container <?php echo esc_html( $display_field_id ); ?>-result-container"></var>
                    <div id="<?php echo esc_html( $display_field_id ); ?>_t" name="form-<?php echo esc_html( $display_field_id ); ?>" class="scrollable-typeahead">
                        <div class="typeahead__container" style="margin-bottom: 0">
                            <div class="typeahead__field">
                                <span class="typeahead__query">
                                    <input class="js-typeahead-<?php echo esc_html( $display_field_id ); ?> input-height" dir="auto"
                                           name="<?php echo esc_html( $display_field_id ); ?>[query]" placeholder="<?php echo esc_html_x( "Search Users", 'input field placeholder', 'disciple_tools' ) ?>"
                                           data-field_type="user_select"
                                           data-field="<?php echo esc_html( $field_key ); ?>"
                                           autocomplete="off">
                                </span>
                                <span class="typeahead__button">
                                    <button type="button" class="search_<?php echo esc_html( $field_key ); ?> typeahead__image_button input-height" data-id="<?php echo esc_html( $field_key ); ?>">
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

    function dt_increment( &$var, $val ){
        if ( !isset( $var ) ){
            $var = 0;
        }
        $var += (int) $val;
    }

    function dt_get_keys_map( $array, $key = "ID" ){
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
            $modules = dt_get_option( "dt_post_type_modules" );
            $module_enabled = isset( $modules[$module_key]["enabled"] ) && !empty( $modules[$module_key]["enabled"] );
            if ( $module_enabled && $check_prereqs ){
                foreach ( $modules[$module_key]["prerequisites"] as $prereq ){
                    $prereq_enabled = isset( $modules[$prereq]["enabled"] ) ? $modules[$prereq]["enabled"] : false;
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
     * All code above here.
     */
} // end if ( ! defined( 'DT_FUNCTIONS_READY' ) )

