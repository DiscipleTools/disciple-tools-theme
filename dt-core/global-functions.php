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
         * @author matzeeable
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
     * The the base site url with, including the subfolder if wp is installed in a subfolder.
     * @return string
     */
    function dt_get_url_path() {
        if ( isset( $_SERVER["HTTP_HOST"] ) ) {
            $url  = ( !isset( $_SERVER["HTTPS"] ) || @( $_SERVER["HTTPS"] != 'on' ) ) ? 'http://'. sanitize_text_field( wp_unslash( $_SERVER["HTTP_HOST"] ) ) : 'https://'. sanitize_text_field( wp_unslash( $_SERVER["HTTP_HOST"] ) );
            if ( isset( $_SERVER["REQUEST_URI"] ) ) {
                $url .= sanitize_text_field( wp_unslash( $_SERVER["REQUEST_URI"] ) );
            }
            return trim( str_replace( get_site_url(), "", $url ), '/' );
        }
        return '';
    }

    function dt_array_to_sql( $values ) {
        if ( empty( $values ) ){
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


    /**
     * @param $date
     * @param string $format  options are short, long, or [custom]
     *
     * @return bool|int|string
     */
    function dt_format_date( $date, $format = 'short' ){
        $date_format = get_option( 'date_format' );
        $time_format = get_option( 'time_format' );
        if ( $format === 'short' ){
            $format = $date_format;
        } else if ( $format === 'long') {
            $format = $date_format . ' ' . $time_format;
        }
        if ( is_numeric( $date ) ){
            $formatted = date_i18n( $format, $date );
        } else {
            $formatted = mysql2date( $format, $date );
        }
        return $formatted;
    }

    function dt_date_start_of_year(){
        $this_year = date( 'Y' );
        $timestamp = strtotime( $this_year . '-01-01' );
        return $timestamp;
    }
    function dt_date_end_of_year(){
        $this_year = (int) date( 'Y' );
        return strtotime( ( $this_year + 1 ) . '-01-01' );
    }

    function dt_get_year_from_timestamp( int $time ){
        return date( "Y", $time );
    }
    function dt_sanitize_array_html( $array ) {
        array_walk_recursive( $array, function ( &$v ) {
            $v = filter_var( trim( $v ), FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES );
        } );
        return $array;
    }

    /**
     * This is the modifiable url for downloading the location_grid and people groups source files for the DT system.
     * The filter can be used to override the default GitHub location and move this to a custom mirror or fork.
     * @return string
     */
    function dt_get_theme_data_url() {
        return apply_filters( 'disciple_tools_theme_data_url', 'https://raw.githubusercontent.com/DiscipleTools/disciple-tools-theme-data/master/' );
    }
    /**
     * All code above here.
     */
} // end if ( ! defined( 'DT_FUNCTIONS_READY' ) )

